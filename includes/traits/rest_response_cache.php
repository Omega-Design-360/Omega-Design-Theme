<?php
/**
 * Chunked REST Response Cache
 *
 * Shared by icons.php (the /wp/v2/icons listing) and patterns_cache.php
 * (/wp/v2/block-patterns/patterns): both cache a large, slow-to-build REST
 * listing in transients, split into chunks because a single transient of
 * the full payload exceeds a typical MySQL max_allowed_packet and silently
 * fails to save, and both keep the cache warm from WP-Cron.
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

defined('ABSPATH') || exit;

trait rest_response_cache {

    /**
     * The cached data for $key, or null on a miss - including when any one
     * chunk expired/was evicted independently, so an incomplete list is
     * never served.
     */
    protected static function read_chunked_transient($key) {
        $meta = get_transient($key . '_meta');
        if (false === $meta || !isset($meta['chunks'])) {
            return null;
        }

        $data = [];
        for ($i = 0; $i < $meta['chunks']; $i++) {
            $chunk = get_transient($key . '_c' . $i);
            if (false === $chunk) {
                return null;
            }
            $data = array_merge($data, $chunk);
        }

        return $data;
    }

    protected static function write_chunked_transient($key, array $data, $chunk_size, $ttl) {
        $chunks = array_chunk($data, $chunk_size);

        foreach ($chunks as $i => $chunk) {
            set_transient($key . '_c' . $i, $chunk, $ttl);
        }
        set_transient($key . '_meta', ['chunks' => count($chunks)], $ttl);
    }

    /**
     * Whether a dispatched REST response is worth caching. rest_do_request()/
     * dispatch() convert a failed permission check into a WP_REST_Response
     * carrying a 401/403 status rather than a raw WP_Error, so is_wp_error()
     * alone won't catch it - caching that would serve the error to everyone
     * until the transient expired.
     */
    protected static function is_cacheable_response($response) {
        if (is_wp_error($response)) {
            return false;
        }

        $status = $response instanceof \WP_REST_Response ? $response->get_status() : 200;
        return $status >= 200 && $status < 300;
    }

    protected static function schedule_recurring($hook, $recurrence) {
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), $recurrence, $hook);
        }
    }

    /**
     * Dispatches a GET request to $route in-process (no real HTTP round
     * trip) as the first administrator - a cron run has no logged-in user
     * to pass the controller's own permission check - and hands the result
     * to $cache_callback($response, $server, $request).
     *
     * The callback is called directly rather than relying on the
     * 'rest_post_dispatch' filter: WP_REST_Server::dispatch() (the method
     * available from a cron context) never applies that filter itself -
     * only serve_request(), the full HTTP-serving path a real browser
     * request takes, does.
     */
    protected static function warm_route_as_admin($route, callable $cache_callback) {
        $previous_user = get_current_user_id();
        $admins        = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);

        if (empty($admins)) {
            return;
        }

        wp_set_current_user($admins[0]);

        $request  = new \WP_REST_Request('GET', $route);
        $server   = rest_get_server();
        $response = $server->dispatch($request);
        call_user_func($cache_callback, $response, $server, $request);

        wp_set_current_user($previous_user);
    }
}
