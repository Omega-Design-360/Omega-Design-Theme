<?php
/**
 * Per-page "Hide footer" toggle - identical pattern to
 * header_visibility.php's "Hide header" (see that file for the fuller
 * explanation of each edge case handled below; kept as a separate class
 * rather than merged, matching this theme's existing one-concern-per-file
 * convention).
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class footer_visibility {

    const META_KEY = 'omega_hide_footer';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

    public function init() {}

    public function get_supported_post_types() {
        return apply_filters('omega_design_hide_footer_post_types', ['post', 'page']);
    }

    public function register_meta() {
        foreach ($this->get_supported_post_types() as $post_type) {
            register_post_meta($post_type, self::META_KEY, [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'boolean',
                'default'       => false,
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    public function enqueue_editor_assets() {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->post_type, $this->get_supported_post_types(), true)) {
            return;
        }

        wp_enqueue_script(
            'omega-design-hide-footer',
            OMEGA_DESIGN_JS_URI . '/hide-footer-toggle.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', 'omega-design-hide-header'],
            $this->asset_version(),
            true
        );
    }

    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/hide-footer-toggle.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    /**
     * Whether the footer should be suppressed on the page currently being
     * rendered. Same three resolution paths as
     * header_visibility::is_hidden_for_current_page(): a real singular
     * view, the site's "Posts page" (is_home() instead of is_singular()
     * there), and the block editor's own REST-based re-fetch of the
     * template part (which has neither, but always passes post_id as a
     * request parameter).
     */
    public static function is_hidden_for_current_page() {
        $post_id = 0;

        if (is_singular()) {
            $post_id = get_queried_object_id();
        } elseif (is_home()) {
            $post_id = (int) get_option('page_for_posts');
        }

        if (!$post_id && !empty($_REQUEST['post_id'])) {
            $post_id = absint($_REQUEST['post_id']);
        }

        return $post_id && (bool) get_post_meta($post_id, self::META_KEY, true);
    }
}
