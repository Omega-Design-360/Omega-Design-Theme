<?php
/**
 * Lead capture backend for the "Omega Newsletter Form" block
 * (blocks/omega-newsletter). Registers a private "omega_lead" post type so
 * submissions are visible/searchable/exportable from wp-admin with no
 * custom database table or migration, and handles the AJAX submit
 * (blocks/omega-newsletter/view.js posts to admin-ajax.php).
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class leads {

    private static $instance = null;

    const POST_TYPE = 'omega_lead';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('wp_ajax_omega_newsletter_subscribe', [$this, 'handle_subscribe']);
        add_action('wp_ajax_nopriv_omega_newsletter_subscribe', [$this, 'handle_subscribe']);
    }

    public function init() {}

    public function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => __('Leads', 'omega-design'),
                'singular_name' => __('Lead', 'omega-design'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-email-alt',
            'menu_position'      => 26,
            'supports'           => ['title'],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'capabilities'       => [
                'create_posts' => 'do_not_allow',
            ],
        ]);
    }

    public function handle_subscribe() {
        check_ajax_referer('omega_newsletter_subscribe', 'nonce');

        // Honeypot: a real visitor never fills this hidden field in.
        if (!empty($_POST['company'])) {
            wp_send_json_success();
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'omega-design')], 400);
        }

        $source_url = isset($_POST['source_url']) ? esc_url_raw(wp_unslash($_POST['source_url'])) : '';

        $existing = get_posts([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => '_omega_lead_email',
            'meta_value'     => $email,
            'fields'         => 'ids',
        ]);

        if (!empty($existing)) {
            $lead_id = $existing[0];
            wp_update_post(['ID' => $lead_id, 'post_date' => current_time('mysql')]);
        } else {
            $lead_id = wp_insert_post([
                'post_type'   => self::POST_TYPE,
                'post_title'  => $email,
                'post_status' => 'publish',
            ]);
        }

        if (is_wp_error($lead_id) || !$lead_id) {
            wp_send_json_error(['message' => __('Something went wrong. Please try again.', 'omega-design')], 500);
        }

        update_post_meta($lead_id, '_omega_lead_email', $email);
        update_post_meta($lead_id, '_omega_lead_source_url', $source_url);

        wp_send_json_success();
    }
}
