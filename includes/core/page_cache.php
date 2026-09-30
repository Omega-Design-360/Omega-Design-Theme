<?php
/**
 * Lightweight full-page HTML cache for anonymous front-end visitors.
 *
 * Measured cause (see conversation/commit history around this file): the
 * homepage alone renders 300+ nested blocks server-side (97 Group, 49
 * Column, 5 WooCommerce product blocks x30 products, ...) - ~535ms of pure
 * PHP block-rendering, on top of the ~300ms WP bootstrap, on EVERY single
 * visit, identical output or not, with no persistent object cache to even
 * soften the ~150 DB queries behind it. This skips that render entirely
 * for repeat anonymous visitors by saving the fully rendered HTML to a
 * flat file and serving it straight back on the next matching request,
 * before WordPress ever loads a template, parses a block, or runs a query
 * for that request.
 *
 * Deliberately narrow about what it caches - wrong here means serving one
 * visitor's cart/account page to another, which is worse than no cache at
 * all:
 *  - GET requests only, never logged in, no query string, no cart/session/
 *    comment-author cookie present.
 *  - Never the WooCommerce cart, checkout, or my-account pages.
 *  - Never wp-admin, REST, AJAX, cron, WP-CLI, search, or feed requests.
 *  - Only ever saves a response that actually looks like a complete,
 *    normal (200) page - never a redirect, and never a suspiciously short
 *    buffer (an early exit()/redirect mid-request would otherwise cache an
 *    empty file).
 *
 * Invalidation is deliberately blunt (clear everything) rather than
 * surgical (clear just the one affected URL): on save_post (covers pages,
 * posts, AND WooCommerce products - editing a product already fires
 * save_post_product, including indirectly via stock/price updates that
 * call $product->save()), switch_theme, and customize_save_after. A 12
 * hour TTL is a backstop in case some other, rarer path changes rendered
 * output without tripping one of those hooks.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

// Loaded here (not just by the autoloader) because serve_early() runs
// before the theme's autoloader exists and needs the visitor's language.
require_once __DIR__ . '/visitor_language.php';

class page_cache {

    private static $instance = null;

    const TTL = 12 * HOUR_IN_SECONDS;

    /**
     * Cookie name prefixes that mean "this visitor has personalized state" -
     * logged-in, an active cart/session, or has left a comment before (so a
     * generic cached page would lose their name/email prefill). Any of
     * these present means skip the cache entirely for this request.
     */
    const SKIP_COOKIE_PREFIXES = [
        'wordpress_logged_in_',
        'wordpress_sec_',
        'wp_woocommerce_session_',
        'woocommerce_items_in_cart',
        'comment_author',
    ];

    /**
     * Query-string keys that only carry analytics/ad attribution and never
     * change what a page renders - a visitor arriving from a newsletter or
     * ad link (?utm_source=..., ?fbclid=...) still gets the cached page,
     * instead of every tagged link forcing a full uncached render.
     */
    const IGNORED_QUERY_KEYS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id',
        'fbclid', 'gclid', 'gbraid', 'wbraid', 'msclkid', 'dclid', 'twclid', 'ttclid',
        'mc_cid', 'mc_eid', '_ga', '_gl',
    ];

    const WARM_HOOK = 'omega_page_cache_warm';

    /** Most URLs a single background warm-up run re-renders. */
    const WARM_LIMIT = 40;

    private $cache_file = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('template_redirect', [$this, 'maybe_serve_or_capture'], 0);

        add_action('save_post', [$this, 'flush']);
        add_action('deleted_post', [$this, 'flush']);
        add_action('switch_theme', [$this, 'flush']);
        add_action('customize_save_after', [$this, 'flush']);
        add_action('wp_update_nav_menu', [$this, 'flush']);
        add_action('update_option_sidebars_widgets', [$this, 'flush']);
        // WooCommerce "Coming soon" / store-visibility and other store
        // settings change what every anonymous visitor sees, but save no post.
        add_action('update_option_woocommerce_coming_soon', [$this, 'flush']);
        add_action('update_option_woocommerce_store_pages_only', [$this, 'flush']);
        add_action('woocommerce_settings_saved', [$this, 'flush']);

        add_action(self::WARM_HOOK, [$this, 'warm']);
        add_action('after_switch_theme', [$this, 'schedule_warm']);
    }

    public function init() {}

    private function cache_dir() {
        return omega_design_get_upload_dir('page-cache');
    }

    /**
     * Serves a cached page as early as the theme can run at all - called
     * straight from functions.php the moment the theme loads, rather than
     * waiting for template_redirect. Measured on this site: a cache hit at
     * template_redirect still paid ~270ms, because by then WordPress has
     * already run every plugin's and the theme's init work (block, pattern
     * and icon registration, WooCommerce setup, ...) - none of which a
     * saved HTML file needs. Serving here skips all of it.
     *
     * Deliberately decides only from the request itself (method, URL,
     * cookies) - the main query hasn't run yet, so there's no is_page()
     * etc. That's still safe: a cache file only ever exists for a URL that
     * passed the full is_cacheable_request() check (with the real query)
     * when it was captured, so cart/checkout/account/404/search URLs never
     * have one to serve. The personalization-cookie check is repeated here
     * because it's per-visitor, not per-URL.
     */
    public static function serve_early() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (('GET' !== $method && 'HEAD' !== $method) || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (self::has_personalization_cookie() || null === self::cache_key_uri()) {
            return;
        }

        $file = self::find_cached_file(OMEGA_DESIGN_UPLOADS_THEME_DIR . '/page-cache', $per_language);
        if (null === $file) {
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=' . get_option('blog_charset', 'UTF-8'));
            header('X-Omega-Cache: HIT');
            if ($per_language) {
                header('Vary: Accept-Language, Cookie', false);
            }
            header('Cache-Control: max-age=0, must-revalidate');
        }
        if ('HEAD' !== $method) {
            readfile($file);
        }
        exit;
    }

    private static function has_personalization_cookie() {
        foreach (array_keys($_COOKIE) as $cookie_name) {
            foreach (self::SKIP_COOKIE_PREFIXES as $prefix) {
                if (0 === strpos($cookie_name, $prefix)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * The request URI with tracking-only query args stripped, or null when
     * any other query arg is present (a real query - filters, pagination
     * params, add-to-cart, previews... - must never be served from cache).
     */
    private static function cache_key_uri() {
        $uri   = $_SERVER['REQUEST_URI'] ?? '/';
        $parts = explode('?', $uri, 2);
        if (!isset($parts[1]) || '' === $parts[1]) {
            return $parts[0];
        }

        parse_str($parts[1], $query);
        foreach (array_keys($query) as $key) {
            if (!in_array(strtolower((string) $key), self::IGNORED_QUERY_KEYS, true)) {
                return null;
            }
        }

        return $parts[0];
    }

    /**
     * Runs at the earliest point WordPress has resolved the main query but
     * has not yet loaded/rendered the actual template - late enough to know
     * what's being requested, early enough to skip the entire expensive
     * part (block parsing/rendering) on a hit.
     */
    public function maybe_serve_or_capture() {
        if (!$this->is_cacheable_request()) {
            return;
        }

        $file = self::find_cached_file($this->cache_dir(), $per_language);

        if (null !== $file) {
            // Normally already served by serve_early() - this is the
            // fallback if the theme was loaded some other way.
            if (!headers_sent()) {
                header('X-Omega-Cache: HIT');
            }
            readfile($file);
            exit;
        }

        if (!headers_sent()) {
            header('X-Omega-Cache: MISS');
        }
        $this->cache_file = null;
        ob_start([$this, 'capture']);
    }

    /**
     * ob_start() callback - receives the complete rendered page right
     * before it's sent to the browser. Only ever WRITES to cache here;
     * the return value (unmodified) is what actually reaches the visitor
     * either way, so a save failure never affects what's served.
     */
    public function capture($buffer) {
        // A real page is never this short - an early exit()/redirect mid-
        // request (or a fatal that PHP still flushed) would otherwise get
        // cached as a blank/broken page for everyone after.
        if (strlen($buffer) < 500) {
            return $buffer;
        }

        $status = http_response_code();
        if ($status && $status !== 200) {
            return $buffer;
        }

        $dir = $this->cache_dir();
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        $this->protect_directory($dir);

        // A bilingual page (it carries the language toggle - see
        // visitor_language.php) is cached once per language; every other
        // page is identical for everyone, so it gets one shared copy.
        $per_language     = false !== strpos($buffer, 'class="omega-lang-switch"');
        $this->cache_file = $dir . '/' . self::cache_key($per_language) . '.html';

        // Write to a temp file and rename (atomic on the same filesystem),
        // so a request reading the file mid-write never sees a partial page.
        $tmp = $this->cache_file . '.' . wp_unique_id() . '.tmp';
        if (false !== file_put_contents($tmp, $buffer)) {
            rename($tmp, $this->cache_file);
        }

        return $buffer;
    }

    private function is_cacheable_request() {
        if ('GET' !== ($_SERVER['REQUEST_METHOD'] ?? 'GET')) {
            return false;
        }

        if (is_admin() || is_search() || is_feed() || is_404()) {
            return false;
        }

        if ((defined('DOING_AJAX') && DOING_AJAX)
            || (defined('DOING_CRON') && DOING_CRON)
            || (defined('REST_REQUEST') && REST_REQUEST)
            || (defined('WP_CLI') && WP_CLI)
        ) {
            return false;
        }

        if (is_user_logged_in()) {
            return false;
        }

        if (null === self::cache_key_uri()) {
            return false;
        }

        if (self::has_personalization_cookie()) {
            return false;
        }

        if ($this->is_woocommerce_account_flow_page()) {
            return false;
        }

        return true;
    }

    /**
     * WooCommerce's cart/checkout/my-account pages are per-visitor by
     * nature (cart contents, order history, login forms) and must never be
     * served from a shared cache, regardless of the cookie checks above.
     */
    private function is_woocommerce_account_flow_page() {
        if (!function_exists('wc_get_page_id') || !is_page()) {
            return false;
        }

        $queried_id = get_queried_object_id();
        foreach (['cart', 'checkout', 'myaccount'] as $page) {
            $page_id = wc_get_page_id($page);
            if ($page_id && $page_id === $queried_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bilingual pages (includes/core/visitor_language.php) render
     * differently per visitor language, so they're cached per language;
     * all other pages share one language-neutral copy.
     */
    private static function cache_key($per_language = false) {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        return md5($host . self::cache_key_uri() . ($per_language ? '|' . visitor_language::current() : ''));
    }

    /** A fresh cached copy for this request - the visitor's language copy first, else the shared one. */
    private static function find_cached_file($dir, &$per_language = false) {
        foreach ([true, false] as $candidate) {
            $file = $dir . '/' . self::cache_key($candidate) . '.html';
            if (is_file($file) && (time() - filemtime($file)) < self::TTL) {
                $per_language = $candidate;
                return $file;
            }
        }
        $per_language = false;
        return null;
    }

    private function protect_directory($dir) {
        $index_file = $dir . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden.');
        }

        $htaccess_file = $dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Options -Indexes\n");
        }
    }

    /**
     * Deliberately blunt: clears every cached page rather than working out
     * which URLs a given edit could have affected (a changed header/footer
     * template part, a global styles change, or a product's stock status
     * can each affect far more pages than just the one post being saved).
     */
    public function flush() {
        $dir = $this->cache_dir();
        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*.html') as $file) {
            @unlink($file);
        }

        $this->schedule_warm();
    }

    /**
     * "Build once, then always fast": right after the cache is cleared (a
     * post/product saved, menus or Customizer changed, theme switched),
     * the site's main URLs are re-rendered in the background by WP-Cron,
     * so the next real visitor gets a ready cached page instead of paying
     * for the full render themselves. A burst of saves (bulk edits, an
     * import) collapses into one run, since an already-scheduled run is
     * never scheduled twice.
     */
    public function schedule_warm() {
        if (!wp_next_scheduled(self::WARM_HOOK)) {
            wp_schedule_single_event(time() + 15, self::WARM_HOOK);
        }
    }

    public function warm() {
        foreach ($this->warm_urls() as $url) {
            wp_remote_get($url, [
                'timeout'    => 20,
                'redirection'=> 0,
                'blocking'   => true,
                'sslverify'  => apply_filters('https_local_ssl_verify', false),
                'cookies'    => [],
                'user-agent' => 'Omega Design cache warm-up; ' . home_url('/'),
            ]);
        }
    }

    /**
     * Home, the WooCommerce shop and blog index, every published page, and
     * the most recent posts and products - capped at WARM_LIMIT so a large
     * catalog doesn't turn one warm-up into thousands of requests (the
     * rest still cache on their first real visit, as before).
     */
    private function warm_urls() {
        $urls = [home_url('/')];

        if (function_exists('wc_get_page_permalink')) {
            $urls[] = wc_get_page_permalink('shop');
        }

        $posts_page = (int) get_option('page_for_posts');
        if ($posts_page) {
            $urls[] = get_permalink($posts_page);
        }

        $excluded = [];
        if (function_exists('wc_get_page_id')) {
            foreach (['cart', 'checkout', 'myaccount'] as $page) {
                $excluded[] = (int) wc_get_page_id($page);
            }
        }

        $ids = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => self::WARM_LIMIT,
            'post__not_in'   => array_filter($excluded),
            'orderby'        => 'menu_order date',
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        $ids = array_merge($ids, get_posts([
            'post_type'      => array_filter(['post', post_type_exists('product') ? 'product' : '']),
            'post_status'    => 'publish',
            'posts_per_page' => 15,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]));

        foreach ($ids as $id) {
            $urls[] = get_permalink($id);
        }

        return array_slice(array_values(array_unique(array_filter($urls))), 0, self::WARM_LIMIT);
    }
}
