<?php
/**
 * Per-page "Hide featured image" toggle - same pattern as title.php's "Hide
 * page title": a post meta flag set from the block editor sidebar, read at
 * render time via the render_block_core/post-featured-image filter.
 *
 * Only ever touches the theme's own automatic Featured Image block - the
 * one placed by single.html/page.html and rendered once per post, outside
 * the post's own content. A Featured Image block an editor has manually
 * placed inside the post content itself (same block type - WordPress gives
 * both the same postId/postType context on a singular view, so they can't
 * be told apart by context alone) is never touched, because it's rendered
 * from inside core/post-content's own call to apply_filters('the_content',
 * ...) - bracketed below between the_content's do_blocks() (priority 9,
 * see wp-includes/default-filters.php) with a priority-8 "entering" and
 * priority-10 "leaving" filter, so maybe_hide_image() can tell the two
 * apart by whether it's currently running inside that window.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class featured_image_visibility {

    const META_KEY = 'omega_hide_featured_image';

    private static $instance = null;

    /**
     * Depth counter rather than a plain bool - the_content() can run
     * re-entrantly (e.g. a Query Loop block rendering an excerpt for a
     * different post while the outer post's own content is still being
     * rendered), so a plain "leaving" flip at priority 10 from an inner
     * call could otherwise turn this off while the outer call is still in
     * progress.
     */
    private $content_render_depth = 0;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_filter('the_content', [$this, 'enter_content_render'], 8);
        add_filter('the_content', [$this, 'leave_content_render'], 10);
        add_filter('render_block_core/post-featured-image', [$this, 'maybe_hide_image'], 10, 3);
    }

    public function init() {}

    /**
     * Post types that get the "Hide featured image" toggle in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters('omega_design_hide_featured_image_post_types', ['post', 'page']);
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
            'omega-design-featured-image-toggle',
            OMEGA_DESIGN_JS_URI . '/featured-image-toggle.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', 'omega-design-hide-footer'],
            $this->asset_version(),
            true
        );
    }

    /**
     * filemtime()-based version so a saved edit to featured-image-toggle.js
     * is picked up on the next load instead of being cached by the browser
     * under the static OMEGA_DESIGN_ASSET_VERSION query string.
     */
    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/featured-image-toggle.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    public function enter_content_render($content) {
        $this->content_render_depth++;
        return $content;
    }

    public function leave_content_render($content) {
        $this->content_render_depth = max(0, $this->content_render_depth - 1);
        return $content;
    }

    /**
     * Suppress the core/post-featured-image block output when the current
     * post has the "hide featured image" meta set - but only the template's
     * own automatic instance. A Featured Image block sitting inside the
     * post's own content (any "section" built in the block editor) is left
     * alone even when the toggle is on, since it was placed there on
     * purpose. Also skipped for Query Loop items (they carry a 'query'
     * block context) so archive/listing thumbnails are never hidden by a
     * single post's own setting.
     */
    public function maybe_hide_image($block_content, $parsed_block, $block) {
        if ($this->content_render_depth > 0) {
            return $block_content;
        }

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
