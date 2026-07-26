<?php
/**
 * Page/Post Title Visibility
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class title {

    const META_KEY = 'omega_hide_page_title';

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
        add_filter('render_block_core/post-title', [$this, 'maybe_hide_title'], 10, 3);
    }

    public function init() {}

    /**
     * Post types that get the "Hide page title" toggle in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters('omega_design_title_toggle_post_types', ['post', 'page']);
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
            'omega-design-title-toggle',
            OMEGA_DESIGN_JS_URI . '/title-toggle.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n'],
            $this->asset_version(),
            true
        );
    }

    /**
     * filemtime()-based version so a saved edit to title-toggle.js is
     * picked up on the next load instead of being cached by the browser
     * under the static OMEGA_DESIGN_ASSET_VERSION query string.
     */
    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/title-toggle.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    /**
     * Suppress the core/post-title block output when the current post has
     * the "hide title" meta set. Skipped for Query Loop items (they carry
     * a 'query' block context) so archive/listing titles are never hidden.
     */
    public function maybe_hide_title($block_content, $parsed_block, $block) {
        if (isset($block->context['query'])) {
            return $block_content;
        }

        $post_id = $block->context['postId'] ?? get_the_ID();

        if (!$post_id || !get_post_meta($post_id, self::META_KEY, true)) {
            return $block_content;
        }

        return '';
    }
}
