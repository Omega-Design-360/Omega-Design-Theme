<?php
/**
 * Registers "Omega Icons" as a collection in WordPress core's native icon
 * registry (wp_register_icon_collection() / wp_register_icon(), added in
 * WP 7.1 - see wp-includes/icons.php), so they appear in the core/icon
 * block's "Icon library" alongside the built-in "WordPress" (Dashicons) set.
 *
 * The full Material Symbols Outlined catalog (~4,000 icons - see
 * icons-material.php for labels and provenance). Each is registered via
 * `file_path` rather than inline `content`: WP_Icons_Registry sanitizes
 * (wp_kses()) an icon's SVG the moment `content` is provided, so passing
 * ~4,000 inline strings would run that sanitization on every single
 * request regardless of whether any of them are ever rendered - measured
 * at ~400ms. With `file_path`, sanitization is deferred to get_content(),
 * which only runs for an icon actually resolved via wp_get_icon() - i.e.
 * one actually used on the current page, or browsed in the editor's Icon
 * library - so a typical front-end page pays for only the handful of
 * icons it actually renders, not the whole catalog.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class icons {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    const CRON_HOOK = 'omega_icons_warm_cache';

    private function __construct() {
        add_action('init', [$this, 'register']);
        add_filter('rest_pre_dispatch', [$this, 'maybe_serve_cached_icons'], 10, 3);
        add_filter('rest_post_dispatch', [$this, 'maybe_cache_icons_response'], 10, 3);

        // See warm_cache() below for why this exists.
        add_action('init', [$this, 'schedule_cache_warm']);
        add_action(self::CRON_HOOK, [$this, 'warm_cache']);
        add_action('after_switch_theme', [$this, 'warm_cache_soon']);
        add_action('switch_theme', [$this, 'unschedule_cache_warm']);
    }

    public function init() {}

    /**
     * The 1-day transient cache above only helps AFTER something has
     * already paid the ~4.8s cost of a cold /wp/v2/icons request once -
     * whoever happens to open the block/Site Editor at the moment the
     * cache is empty (first activation, once every 24h on expiry, or right
     * after any icon file edit changes the cache key) eats that delay
     * directly, and the Site Editor calls this route on essentially every
     * load regardless of whether the template being edited uses any icon
     * at all. Running the same request in the background on a schedule -
     * well inside the 1-day TTL, so it always refreshes before the old
     * entry expires - means no real editor session should ever hit that
     * cold path under normal operation.
     */
    public function schedule_cache_warm() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'twicedaily', self::CRON_HOOK);
        }
    }

    public function unschedule_cache_warm() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * A single one-off run shortly after activation, so a fresh install
     * isn't left with a cold cache for however long it takes the regular
     * twice-daily schedule to first fire.
     */
    public function warm_cache_soon() {
        $this->schedule_cache_warm();
        wp_schedule_single_event(time() + 30, self::CRON_HOOK);
    }

    /**
     * Dispatches GET /wp/v2/icons in-process (same mechanism WP itself uses
     * for internal REST calls - no real HTTP round trip). The REST
     * controller's own permission check requires edit_posts, which a cron
     * run has no logged-in user for, so this borrows the first
     * administrator's identity only for the duration of the one request.
     *
     * Deliberately calls maybe_cache_icons_response() directly rather than
     * relying on the 'rest_post_dispatch' filter firing on its own:
     * WP_REST_Server::dispatch() (which rest_do_request() calls) only ever
     * applies 'rest_pre_dispatch' - 'rest_post_dispatch' is applied by
     * serve_request(), the full HTTP-serving path a real browser request
     * takes, which dispatch() alone never reaches. Relying on the filter
     * here would silently pay the full ~3.5s resolution cost on every cron
     * run and then throw the result away uncached, defeating the entire
     * point of warming it in the background.
     */
    public function warm_cache() {
        $previous_user = get_current_user_id();
        $admins        = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);

        if (empty($admins)) {
            return;
        }

        wp_set_current_user($admins[0]);

        $request  = new \WP_REST_Request('GET', '/wp/v2/icons');
        $server   = rest_get_server();
        $response = $server->dispatch($request);
        $this->maybe_cache_icons_response($response, $server, $request);

        wp_set_current_user($previous_user);
    }

    /**
     * The editor's Icon library modal fetches GET /wp/v2/icons, which calls
     * WP_Icons_Registry::get_registered_icons() - that resolves (file_get_contents
     * + wp_kses sanitize) every registered icon's content unconditionally,
     * even when a specific collection is requested (filtering happens after
     * resolution, not before). Measured at ~3.2s for this catalog's ~4,000
     * icons. These two filters cache that listing response in a transient
     * for a day, so only the first picker-open per cache window pays that
     * cost - every open after that is a cache hit.
     *
     * Deliberately scoped to the *listing* routes only (/wp/v2/icons and
     * /wp/v2/icons/{collection}) via is_icons_listing_route() - the
     * single-icon route (/wp/v2/icons/{collection}/{name}) already only
     * resolves one file and doesn't need this.
     */
    private function is_icons_listing_route($request) {
        return (bool) preg_match('#^/wp/v2/icons(?:/[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?)?$#', $request->get_route());
    }

    /**
     * Cache key changes automatically if the icon catalog changes: either
     * the label list (icons-material.php) or the SVG files directory
     * (assets/icons/material-symbols/, whose mtime updates when a file is
     * added, removed, or renamed) - on top of the 1-day TTL as a backstop
     * for in-place edits to an existing file's content, which don't always
     * change a directory's own mtime.
     *
     * Deliberately reads get_query_params() here, never get_params(): the
     * write side (maybe_cache_icons_response, on 'rest_post_dispatch') sees
     * the request AFTER WP_REST_Server::match_request_to_handler() has
     * already merged the route's schema defaults (context, page, per_page,
     * ...) into it, while the read side (maybe_serve_cached_icons, on
     * 'rest_pre_dispatch') runs BEFORE that merge - so get_params() silently
     * returns a different array on each side of the SAME request, and the
     * two ends never compute the same key. get_query_params() reflects only
     * what was actually in the URL's query string and is never mutated by
     * that later default-merging, so it stays identical on both sides
     * (confirmed: it was this mismatch, not the 1-day TTL, that meant the
     * cache was never actually being read from at all - every real request
     * silently recomputed the full ~4s response, cache or no cache).
     */
    private function icons_cache_key($request) {
        $data_file_mtime = @filemtime(__DIR__ . '/icons-material.php');
        $svg_dir_mtime   = @filemtime(OMEGA_DESIGN_ASSETS . '/icons/material-symbols');

        $parts = $request->get_route() . '|' . wp_json_encode($request->get_query_params())
            . '|' . $data_file_mtime . '|' . $svg_dir_mtime;

        return 'omega_icons_rest_' . substr(md5($parts), 0, 30);
    }

    /**
     * rest_pre_dispatch fires before WP_REST_Server routes the request to
     * WP_REST_Icons_Controller and runs its own get_items_permissions_check()
     * - so short-circuiting straight to a cached response here would skip
     * that check entirely and leak the icon list to anyone who can reach
     * this route, logged in or not. Mirrors that same capability check
     * (current_user_can('edit_posts'), or any show_in_rest post type's own
     * edit cap) before ever serving from cache.
     */
    private function current_user_can_view_icons() {
        if (current_user_can('edit_posts')) {
            return true;
        }

        foreach (get_post_types(['show_in_rest' => true], 'objects') as $post_type) {
            if (current_user_can($post_type->cap->edit_posts)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Icons per chunk when caching the full listing. The whole response
     * serialized is ~2.5-3MB (4,000+ icons), which fails outright against
     * this server's actual max_allowed_packet of 1MB (confirmed via `SHOW
     * VARIABLES`) - a single set_transient() of the full payload silently
     * fails the underlying INSERT and never actually caches anything. 200
     * icons/chunk keeps each transient around ~130KB, comfortably under
     * any realistic max_allowed_packet (1MB is already a common default).
     */
    const ICON_CACHE_CHUNK_SIZE = 200;

    public function maybe_serve_cached_icons($result, $server, $request) {
        if (!$this->is_icons_listing_route($request) || !$this->current_user_can_view_icons()) {
            return $result;
        }

        $key  = $this->icons_cache_key($request);
        $meta = get_transient($key . '_meta');
        if (false === $meta || !isset($meta['chunks'])) {
            return $result;
        }

        $data = [];
        for ($i = 0; $i < $meta['chunks']; $i++) {
            $chunk = get_transient($key . '_c' . $i);
            if (false === $chunk) {
                // A chunk expired/evicted independently - treat the whole
                // thing as a miss rather than serve an incomplete list.
                return $result;
            }
            $data = array_merge($data, $chunk);
        }

        return rest_ensure_response($data);
    }

    public function maybe_cache_icons_response($response, $server, $request) {
        if (!$this->is_icons_listing_route($request) || is_wp_error($response)) {
            return $response;
        }

        // rest_do_request()/dispatch() convert a failed permission check into
        // a WP_REST_Response carrying a 401/403 status rather than a raw
        // WP_Error, so is_wp_error() alone won't catch it - caching that
        // would serve the error to everyone until the transient expired.
        $status = $response instanceof \WP_REST_Response ? $response->get_status() : 200;
        if ($status < 200 || $status >= 300) {
            return $response;
        }

        $key    = $this->icons_cache_key($request);
        $chunks = array_chunk($response->get_data(), self::ICON_CACHE_CHUNK_SIZE);

        foreach ($chunks as $i => $chunk) {
            set_transient($key . '_c' . $i, $chunk, DAY_IN_SECONDS);
        }
        set_transient($key . '_meta', ['chunks' => count($chunks)], DAY_IN_SECONDS);

        return $response;
    }

    public function register() {
        if (!function_exists('wp_register_icon_collection')) {
            return;
        }

        wp_register_icon_collection('omega-icons', [
            'label'       => __('Omega Icons', 'omega-design'),
            'description' => __('Icons from the Omega Design theme.', 'omega-design'),
        ]);

        foreach ($this->get_material_icons() as $name => $label) {
            wp_register_icon('omega-icons/' . $name, [
                'label'     => $label,
                'file_path' => OMEGA_DESIGN_ASSETS . '/icons/material-symbols/' . $name . '.svg',
            ]);
        }
    }

    /**
     * Labels for the full Material Symbols Outlined catalog, sourced from
     * Google's Material Symbols (Apache 2.0) - see icons-material.php.
     * The actual SVG content lives one file per icon under
     * assets/icons/material-symbols/, not here (see class doc comment).
     */
    private function get_material_icons() {
        return require __DIR__ . '/icons-material.php';
    }
}
