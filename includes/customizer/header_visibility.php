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

use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class header_visibility {

    use singleton;
    use editor_meta;

    const META_KEY = 'omega_hide_header';

    private function __construct() {
        $this->register_editor_meta_hooks();
    }

    protected function post_types_filter() {
        return 'omega_design_hide_header_post_types';
    }

    protected function meta_fields() {
        return [self::META_KEY => self::boolean_meta_field()];
    }

    protected function enqueue_editor_screen_assets() {
        self::enqueue_editor_script('omega-design-hide-header', 'js/hide-header-toggle.js', ['omega-design-content-width']);
    }

    /**
     * Whether the header should be suppressed on the page currently being
     * rendered - see editor_meta::current_page_id() for how the page is
     * resolved (singular view, Posts page, block editor REST re-fetch).
     */
    public static function is_hidden_for_current_page() {
        $post_id = self::current_page_id();
        return $post_id && (bool) get_post_meta($post_id, self::META_KEY, true);
    }
}
