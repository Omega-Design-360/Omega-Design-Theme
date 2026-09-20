<?php
/**
 * Page/Post Content Width
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class content_width {

    const META_KEY = 'omega_content_width';

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
        add_filter('render_block_core/post-content', [$this, 'maybe_apply_width'], 10, 3);
    }

    public function init() {}

    /**
     * Post types that get the "Page Width" control in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters('omega_design_content_width_post_types', ['post', 'page']);
    }

    public function register_meta() {
        foreach ($this->get_supported_post_types() as $post_type) {
            register_post_meta($post_type, self::META_KEY, [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => 'string',
                'default'       => '',
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
            'omega-design-content-width',
            OMEGA_DESIGN_JS_URI . '/content-width.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', 'omega-design-title-toggle'],
            $this->asset_version(),
            true
        );
    }

    /**
     * filemtime()-based version so a saved edit to content-width.js is
     * picked up on the next load instead of being cached by the browser
     * under the static OMEGA_DESIGN_ASSET_VERSION query string.
     */
    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/content-width.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    /**
     * Widen the rendered core/post-content wrapper by adding a class that
     * overrides --wp--style--global--content-size for its own subtree, so
     * every direct child that normally centers at "content" width (the
     * default constrained-layout behavior) picks up "wide" or the full
     * available page width instead. Skipped for Query Loop items (they
     * carry a 'query' block context) so archive/listing content is unaffected.
     */
    public function maybe_apply_width($block_content, $parsed_block, $block) {
        if (isset($block->context['query'])) {
            return $block_content;
        }

        $post_id = $block->context['postId'] ?? get_the_ID();

        if (!$post_id) {
            return $block_content;
        }

        $width = get_post_meta($post_id, self::META_KEY, true);

        if (!in_array($width, ['wide', 'full'], true)) {
            return $block_content;
        }

        $class = 'has-omega-content-width-' . $width;
        $updated = preg_replace('/\bclass="/', 'class="' . esc_attr($class) . ' ', $block_content, 1);

        return null !== $updated ? $updated : $block_content;
    }
}
