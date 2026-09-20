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
        add_action('switch_theme', [$this, 'flush']);
        add_action('customize_save_after', [$this, 'flush']);
    }

    public function init() {}

    private function cache_dir() {
        return omega_design_get_upload_dir('page-cache');
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

        $file = $this->cache_dir() . '/' . $this->cache_key() . '.html';

        if (file_exists($file) && (time() - filemtime($file)) < self::TTL) {
            // Reading and echoing a flat file is a few milliseconds - the
            // entire point is to never reach block rendering below this.
            readfile($file);
            exit;
        }

        $this->cache_file = $file;
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

        if (!empty($_GET)) {
            return false;
        }

        foreach (array_keys($_COOKIE) as $cookie_name) {
            foreach (self::SKIP_COOKIE_PREFIXES as $prefix) {
                if (0 === strpos($cookie_name, $prefix)) {
                    return false;
                }
            }
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

    private function cache_key() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri  = $_SERVER['REQUEST_URI'] ?? '';

        return md5($host . $uri);
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
    }
}
