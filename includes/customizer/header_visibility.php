<?php
/**
 * Per-page "Hide header" toggle - same pattern as title.php's "Hide page
 * title": a post meta flag set from the block editor sidebar, read at
 * render time. classic_header::maybe_render_classic_header() (the single
 * place that decides what renders for the "header" template part, in both
 * Block Navigation and Classic modes) calls is_hidden_for_current_page()
 * and returns an empty string when it's set, before any style-specific
 * logic runs - so this applies regardless of which header mode is active.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class header_visibility {

    const META_KEY = 'omega_hide_header';

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

    /**
     * Post types that get the "Hide header" toggle in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters('omega_design_hide_header_post_types', ['post', 'page']);
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
            'omega-design-hide-header',
            OMEGA_DESIGN_JS_URI . '/hide-header-toggle.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', 'omega-design-content-width'],
            $this->asset_version(),
            true
        );
    }

    /**
     * filemtime()-based version so a saved edit to hide-header-toggle.js is
     * picked up on the next load instead of being cached by the browser
     * under the static OMEGA_DESIGN_ASSET_VERSION query string.
     */
    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/hide-header-toggle.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    /**
     * Whether the header should be suppressed on the page currently being
     * rendered.
     */
    public static function is_hidden_for_current_page() {
        $post_id = 0;

        if (is_singular()) {
            $post_id = get_queried_object_id();
        } elseif (is_home()) {
            // A site's "Posts page" (Settings > Reading) is a real Page
            // with its own ID and its own "Hide header" meta, but
            // WordPress swaps in the blog listing/archive template for
            // it instead of that page's own content when you visit it -
            // is_singular() is false there even though a real post ID
            // backs the URL, so the toggle would otherwise silently never
            // take effect on that one specific page while working
            // correctly everywhere else.
            $post_id = (int) get_option('page_for_posts');
        }

        if (!$post_id && !empty($_REQUEST['post_id'])) {
            // The block editor's canvas re-fetches the header template
            // part through the wp/v2/block-renderer REST endpoint
            // (Gutenberg's ServerSideRender), which runs outside any
            // normal page query - is_singular()/get_queried_object_id()
            // never resolve there at all (confirmed: both return
            // false/0), so a saved "Hide header" flag would otherwise
            // silently have no effect on that specific request even
            // though it works correctly on the real front-end page.
            // Gutenberg's own request always includes the post being
            // edited as a `post_id` parameter, which lands here as a
            // plain request var like any other REST param.
            $post_id = absint($_REQUEST['post_id']);
        }

        return $post_id && (bool) get_post_meta($post_id, self::META_KEY, true);
    }
}
