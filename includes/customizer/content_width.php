<?php
/**
 * Page/Post Content Width
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class content_width {

    use singleton;
    use editor_meta;

    const META_KEY = 'omega_content_width';

    private function __construct() {
        $this->register_editor_meta_hooks();
        add_filter('render_block_core/post-content', [$this, 'maybe_apply_width'], 10, 3);
    }

    protected function post_types_filter() {
        return 'omega_design_content_width_post_types';
    }

    protected function meta_fields() {
        return [self::META_KEY => self::string_meta_field()];
    }

    protected function enqueue_editor_screen_assets() {
        self::enqueue_editor_script('omega-design-content-width', 'js/content-width.js', ['omega-design-title-toggle']);
    }

    /**
     * Widen the rendered core/post-content wrapper by adding a class that
     * overrides --wp--style--global--content-size for its own subtree, so
     * every direct child that normally centers at "content" width (the
     * default constrained-layout behavior) picks up "wide" or the full
     * available page width instead. Skipped for Query Loop items so
     * archive/listing content is unaffected.
     */
    public function maybe_apply_width($block_content, $parsed_block, $block) {
        $post_id = self::block_post_id($block);

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
