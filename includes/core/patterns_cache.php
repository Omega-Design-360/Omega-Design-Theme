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

defined('ABSPATH') || exit;

class patterns_cache {

    private static $instance = null;

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

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

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

    public function init() {}

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
        return 'omega_patterns_rest_' . substr(md5(wp_json_encode($request->get_query_params())), 0, 30);
    }

    public function maybe_serve_cached($result, $server, $request) {
        if (!$this->is_route($request) || !$this->can_view()) {
            return $result;
        }

        $key  = $this->cache_key($request);
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

    public function maybe_cache_response($response, $server, $request) {
        if (!$this->is_route($request) || is_wp_error($response)) {
            return $response;
        }

        $status = $response instanceof \WP_REST_Response ? $response->get_status() : 200;
        if ($status < 200 || $status >= 300) {
            return $response;
        }

        $key    = $this->cache_key($request);
        $chunks = array_chunk($response->get_data(), self::CHUNK_SIZE);

        foreach ($chunks as $i => $chunk) {
            set_transient($key . '_c' . $i, $chunk, self::TTL);
        }
        set_transient($key . '_meta', ['chunks' => count($chunks)], self::TTL);

        return $response;
    }

    public function flush_cache() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_omega\_patterns\_rest\_%' OR option_name LIKE '\_transient\_timeout\_omega\_patterns\_rest\_%'"
        );
    }

    public function schedule_cache_warm() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
        }
    }

    public function unschedule_cache_warm() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public function warm_cache_soon() {
        $this->schedule_cache_warm();
        wp_schedule_single_event(time() + 30, self::CRON_HOOK);
    }

    /**
     * See icons.php's warm_cache() for why this calls maybe_cache_response()
     * directly instead of trusting the 'rest_post_dispatch' filter to fire
     * on its own: WP_REST_Server::dispatch() (the method available from a
     * cron context, with no real HTTP request to route) never applies that
     * filter itself - only serve_request(), the full HTTP-serving path a
     * real browser request takes, does. Relying on the filter here would
     * silently pay the ~750ms cost on every cron run and throw the result
     * away uncached.
     */
    public function warm_cache() {
        $previous_user = get_current_user_id();
        $admins        = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);

        if (empty($admins)) {
            return;
        }

        wp_set_current_user($admins[0]);

        $request  = new \WP_REST_Request('GET', self::ROUTE);
        $server   = rest_get_server();
        $response = $server->dispatch($request);
        $this->maybe_cache_response($response, $server, $request);

        wp_set_current_user($previous_user);
    }
}
