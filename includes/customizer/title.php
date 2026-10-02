<?php
/**
 * Page/Post Title Visibility
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class title {

    use singleton;
    use editor_meta;

    const META_KEY = 'omega_hide_page_title';

    private function __construct() {
        $this->register_editor_meta_hooks();
        add_filter('render_block_core/post-title', [$this, 'maybe_hide_title'], 10, 3);
    }

    protected function post_types_filter() {
        return 'omega_design_title_toggle_post_types';
    }

    protected function meta_fields() {
        return [self::META_KEY => self::boolean_meta_field()];
    }

    protected function enqueue_editor_screen_assets() {
        self::enqueue_editor_script('omega-design-title-toggle', 'js/title-toggle.js');
    }

    /**
     * Suppress the core/post-title block output when the current post has
     * the "hide title" meta set. Skipped for Query Loop items so
     * archive/listing titles are never hidden.
     */
    public function maybe_hide_title($block_content, $parsed_block, $block) {
        $post_id = self::block_post_id($block);

        if (!$post_id || !get_post_meta($post_id, self::META_KEY, true)) {
            return $block_content;
        }

        return '';
    }
}
