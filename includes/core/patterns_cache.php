<?php
/**
 * Caches the GET /wp/v2/block-patterns/patterns REST response - the route
 * that populates the block inserter's "Patterns" tab and the New Page/Post
 * starter-pattern picker, called on essentially every editor load. This
 * site has 236 registered patterns (this theme's own landing-page/megamenu
 * patterns plus WooCommerce's bundled set), and WP_REST_Block_Patterns_Controller
 * resolves every one of them (source lookup, content, viewportWidth, etc.)
 * on every single request with no caching of its own - measured at
 * ~750ms, cold or warm, every time.
 *
 * Same approach as includes/core/icons.php's icon-listing cache (see that
 * file for the fuller rationale, including the get_query_params() vs
 * get_params() pitfall this deliberately avoids from the start): a 1-hour
 * transient, kept warm by a WP-Cron job so no real editor session pays the
 * ~750ms cost directly. A much shorter TTL than icons' 1 day is deliberate -
 * patterns are code (register_block_pattern() calls from this theme or
 * active plugins), not content someone edits through the UI, but a plugin
 * update or activation could still change the set, so this leans toward
 * refreshing itself sooner rather than a stale inserter tab.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\rest_response_cache;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class patterns_cache {

    use singleton;
    use rest_response_cache;

    const ROUTE     = '/wp/v2/block-patterns/patterns';
    const CRON_HOOK = 'omega_patterns_warm_cache';
    const TTL       = HOUR_IN_SECONDS;

    /**
     * Patterns per chunk when caching. 236 patterns serialized in one
     * transient hits this server's max_allowed_packet outright (confirmed:
     * a single set_transient() of the full payload fails its underlying
     * INSERT and caches nothing at all) - same problem icons.php's own
     * listing cache already had to solve the same way. 40/chunk keeps each
     * transient comfortably under that limit.
     */
    const CHUNK_SIZE = 40;

    private function __construct() {
        add_filter('rest_pre_dispatch', [$this, 'maybe_serve_cached'], 10, 3);
        add_filter('rest_post_dispatch', [$this, 'maybe_cache_response'], 10, 3);

        add_action('init', [$this, 'schedule_cache_warm']);
        add_action(self::CRON_HOOK, [$this, 'warm_cache']);
        add_action('after_switch_theme', [$this, 'warm_cache_soon']);
        add_action('switch_theme', [$this, 'unschedule_cache_warm']);

        // Patterns are registered from PHP at 'init' (this theme's and any
        // plugin's), so the set can only actually change when code changes -
        // clear the stale cache immediately on either, rather than waiting
        // out the TTL.
        add_action('activated_plugin', [$this, 'flush_cache']);
        add_action('deactivated_plugin', [$this, 'flush_cache']);
        add_action('switch_theme', [$this, 'flush_cache']);
    }

    private function is_route($request) {
        return self::ROUTE === $request->get_route();
    }

    private function can_view() {
        // Matches WP_REST_Block_Patterns_Controller::get_items_permissions_check().
        return current_user_can('edit_posts');
    }

    private function cache_key($request) {
        // See icons.php's icons_cache_key() for why get_query_params() -
        // never get_params() - is the only safe source here: get_params()
        // returns a different array on the pre-dispatch vs post-dispatch
        // side of the very same request, once WP_REST_Server merges the
        // route's schema defaults in between the two.
        //
        // The theme's pattern files' mtimes are part of the key, so adding
        // or editing a pattern (pattern/*.php, or the shop templates those
        // are built from) shows up in the inserter and the New Page starter
        // picker straight away - previously a new pattern stayed missing
        // from both until this 1-hour cache happened to expire.
        return 'omega_patterns_rest_' . substr(md5(wp_json_encode($request->get_query_params()) . '|' . self::files_version()), 0, 30);
    }

    /** Changes whenever a theme pattern file is added, removed or edited. */
    private static function files_version() {
        static $version = null;
        if (null === $version) {
            $dir   = get_template_directory();
            $files = array_merge((array) glob($dir . '/pattern/*.php'), (array) glob($dir . '/templates/shop-*.html'));
            $stamp = '';
            foreach ($files as $file) {
                $stamp .= $file . ':' . @filemtime($file) . '|';
            }
            $version = md5($stamp);
        }
        return $version;
    }

    public function maybe_serve_cached($result, $server, $request) {
        if (!$this->is_route($request) || !$this->can_view()) {
            return $result;
        }

        $data = self::read_chunked_transient($this->cache_key($request));

        return null === $data ? $result : rest_ensure_response($data);
    }

    public function maybe_cache_response($response, $server, $request) {
        if (!$this->is_route($request) || !self::is_cacheable_response($response)) {
            return $response;
        }

        self::write_chunked_transient($this->cache_key($request), $response->get_data(), self::CHUNK_SIZE, self::TTL);

        return $response;
    }

    public function flush_cache() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_omega\_patterns\_rest\_%' OR option_name LIKE '\_transient\_timeout\_omega\_patterns\_rest\_%'"
        );
    }

    public function schedule_cache_warm() {
        self::schedule_recurring(self::CRON_HOOK, 'hourly');
    }

    public function unschedule_cache_warm() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public function warm_cache_soon() {
        $this->schedule_cache_warm();
        wp_schedule_single_event(time() + 30, self::CRON_HOOK);
    }

    /**
     * Re-renders the listing in the background (see
     * rest_response_cache::warm_route_as_admin()) so no editor session pays
     * the ~750ms cost directly.
     */
    public function warm_cache() {
        self::warm_route_as_admin(self::ROUTE, [$this, 'maybe_cache_response']);
    }
}
