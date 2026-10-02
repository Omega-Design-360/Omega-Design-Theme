<?php
/**
 * Per-page "Hide footer" toggle - identical pattern to
 * header_visibility.php's "Hide header" (kept as a separate class rather
 * than merged, matching this theme's existing one-concern-per-file
 * convention).
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class footer_visibility {

    use singleton;
    use editor_meta;

    const META_KEY = 'omega_hide_footer';

    private function __construct() {
        $this->register_editor_meta_hooks();
    }

    protected function post_types_filter() {
        return 'omega_design_hide_footer_post_types';
    }

    protected function meta_fields() {
        return [self::META_KEY => self::boolean_meta_field()];
    }

    protected function enqueue_editor_screen_assets() {
        self::enqueue_editor_script('omega-design-hide-footer', 'js/hide-footer-toggle.js', ['omega-design-hide-header']);
    }

    /**
     * Whether the footer should be suppressed on the page currently being
     * rendered - same resolution as
     * header_visibility::is_hidden_for_current_page().
     */
    public static function is_hidden_for_current_page() {
        $post_id = self::current_page_id();
        return $post_id && (bool) get_post_meta($post_id, self::META_KEY, true);
    }
}
