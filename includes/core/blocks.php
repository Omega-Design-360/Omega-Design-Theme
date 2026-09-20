<?php
/**
 * Registers the theme's native "Omega Icon" block (omega-design/icon) - a
 * self-contained icon picker pre-loaded with the theme's stroke icons and
 * their hover choreography (assets/css/omega-icon-choreography.css, ported
 * from mysite.css), so it shows up in the block inserter with no dependency
 * on a third-party icon plugin.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class blocks {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function init() {}

    public function register() {
        $this->register_omega_icon();
        $this->register_omega_slider();
        $this->register_omega_content_slider();
        $this->register_omega_tabs();
        $this->register_omega_newsletter();
    }

    private function register_omega_icon() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-icon';

        if (!file_exists($dir . '/block.json')) {
            return;
        }

        $script_path = $dir . '/index.js';
        wp_register_script(
            'omega-icon-block-editor',
            OMEGA_DESIGN_URI . '/blocks/omega-icon/index.js',
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
            file_exists($script_path) ? filemtime($script_path) : OMEGA_DESIGN_VERSION,
            true
        );
        wp_set_script_translations('omega-icon-block-editor', 'omega-design');

        $style_path = OMEGA_DESIGN_ASSETS . '/css/omega-icon-choreography.css';
        wp_register_style(
            'omega-icon-block',
            OMEGA_DESIGN_CSS_URI . '/omega-icon-choreography.css',
            [],
            file_exists($style_path) ? filemtime($style_path) : OMEGA_DESIGN_VERSION
        );

        register_block_type($dir);
    }

    /**
     * Registers a block's editor script, front-end view script and shared
     * style.css from its own directory using the theme's standard hand-
     * rolled (no build step) convention - mirrors register_omega_icon()
     * above. $handles keys: 'editor' (always registered), 'view' and
     * 'style' (only if the corresponding file exists in $dir).
     */
    private function register_block_assets($dir, $handles, $editor_deps = ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n']) {
        if (!file_exists($dir . '/block.json')) {
            return;
        }

        $editor_path = $dir . '/index.js';
        if (file_exists($editor_path) && !empty($handles['editor'])) {
            wp_register_script(
                $handles['editor'],
                OMEGA_DESIGN_URI . '/blocks/' . basename($dir) . '/index.js',
                $editor_deps,
                filemtime($editor_path),
                true
            );
            wp_set_script_translations($handles['editor'], 'omega-design');
        }

        $view_path = $dir . '/view.js';
        if (file_exists($view_path) && !empty($handles['view'])) {
            wp_register_script(
                $handles['view'],
                OMEGA_DESIGN_URI . '/blocks/' . basename($dir) . '/view.js',
                [],
                filemtime($view_path),
                true
            );
        }

        $style_path = $dir . '/style.css';
        if (file_exists($style_path) && !empty($handles['style'])) {
            wp_register_style(
                $handles['style'],
                OMEGA_DESIGN_URI . '/blocks/' . basename($dir) . '/style.css',
                [],
                filemtime($style_path)
            );
        }
    }

    private function register_omega_slider() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-slider';
        $this->register_block_assets($dir, [
            'editor' => 'omega-slider-block-editor',
            'view'   => 'omega-slider-block-view',
            'style'  => 'omega-slider-block',
        ], ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data']);
        register_block_type($dir);
    }

    /**
     * omega-design/content-slider: a dynamic block (see block.json's
     * "render" -> blocks/omega-content-slider/render.php, resolved
     * natively by register_block_type() with no render_callback needed
     * here) built entirely from its own attributes.slides array, not
     * InnerBlocks - see that block's index.js docblock for why. Reuses
     * omega-slider's own registered view script/style (the carousel
     * engine doesn't care what's inside a slide) plus its own style.css
     * for the slide/image/text/button layout.
     */
    private function register_omega_content_slider() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-content-slider';
        $this->register_block_assets($dir, [
            'editor' => 'omega-content-slider-block-editor',
            'style'  => 'omega-content-slider-block',
        ], ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n']);
        register_block_type($dir);
    }

    private function register_omega_tabs() {
        $tabs_dir = OMEGA_DESIGN_BLOCKS . '/omega-tabs';
        $this->register_block_assets($tabs_dir, [
            'editor' => 'omega-tabs-block-editor',
            'view'   => 'omega-tabs-block-view',
            'style'  => 'omega-tabs-block',
        ]);
        register_block_type($tabs_dir, [
            'render_callback' => [$this, 'render_omega_tabs'],
        ]);

        $item_dir = OMEGA_DESIGN_BLOCKS . '/omega-tabs-item';
        $this->register_block_assets($item_dir, [
            'editor' => 'omega-tabs-item-block-editor',
        ]);
        register_block_type($item_dir);
    }

    /**
     * Renders the omega-design/tabs wrapper: the tab bar is built here from
     * each child omega-design/tabs-item's "label" attribute (read from
     * $block->parsed_block, since a static save() has no access to a
     * sibling block's attributes), and $content is already the rendered
     * panels markup (identical shape to what save() produced, but with any
     * dynamic inner blocks - like a WooCommerce Product Collection - fully
     * rendered).
     */
    public function render_omega_tabs($attributes, $content, $block) {
        $labels = [];
        foreach ($block->parsed_block['innerBlocks'] ?? [] as $inner) {
            if (($inner['blockName'] ?? '') === 'omega-design/tabs-item') {
                $labels[] = $inner['attrs']['label'] ?? __('Tab', 'omega-design');
            }
        }

        if (empty($labels)) {
            return $content;
        }

        $tab_list = '<div class="omega-tabs__list" role="tablist">';
        foreach ($labels as $i => $label) {
            $tab_list .= sprintf(
                '<button type="button" class="omega-tabs__tab%s" role="tab" aria-selected="%s">%s</button>',
                0 === $i ? ' is-active' : '',
                0 === $i ? 'true' : 'false',
                esc_html($label)
            );
        }
        $tab_list .= '</div>';

        // $content is already the block's own wrapper (.omega-tabs, carrying
        // its align/spacing supports) plus the panels inside it - insert the
        // tab bar as the wrapper's first child rather than adding another
        // wrapping element around it.
        if (!preg_match('/^(\s*<[a-z0-9]+)([^>]*)(>)/i', $content, $matches)) {
            return $content;
        }

        $insert_at = strlen($matches[0]);
        return substr($content, 0, $insert_at) . $tab_list . substr($content, $insert_at);
    }

    private function register_omega_newsletter() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-newsletter';
        $this->register_block_assets($dir, [
            'editor' => 'omega-newsletter-block-editor',
            'view'   => 'omega-newsletter-block-view',
            'style'  => 'omega-newsletter-block',
        ]);

        if (wp_script_is('omega-newsletter-block-view', 'registered')) {
            wp_localize_script('omega-newsletter-block-view', 'omegaNewsletter', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('omega_newsletter_subscribe'),
            ]);
        }

        register_block_type($dir);
    }
}
