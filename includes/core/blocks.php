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

use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class blocks {

    use singleton;
    use assets;

    const EDITOR_DEPS = ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'];

    private function __construct() {
        add_action('init', [$this, 'register']);
        add_filter('register_block_type_args', [$this, 'add_background_support'], 10, 2);
        add_filter('register_block_type_args', [$this, 'add_responsive_attribute'], 10, 2);
        add_filter('register_block_type_args', [$this, 'add_image_size_attribute'], 10, 2);
    }

    /**
     * Adds server-side attribute schema to a block type's registration args
     * - the server half of an attribute assets/js/editor.js adds in the
     * editor, so the REST API accepts it and render_block sees it.
     */
    public static function with_attributes($args, array $attributes) {
        $args['attributes'] = array_merge($args['attributes'] ?? [], $attributes);
        return $args;
    }

    /**
     * Server half of editor.js's "Image Size" panel attribute, so the
     * Image block's REST schema accepts it and render_block sees it.
     */
    public function add_image_size_attribute($args, $block_type) {
        if ('core/image' !== $block_type) {
            return $args;
        }

        return self::with_attributes($args, ['omegaImage' => ['type' => 'object']]);
    }

    /**
     * Blocks that never get the "Responsive Settings" panel (editor.js) -
     * the layout blocks have their own Responsive Layout panel, the rest
     * are wrappers with no element of their own to style.
     */
    const RESPONSIVE_EXCLUDED = [
        'core/group', 'core/columns', 'core/column', 'core/freeform',
        'core/block', 'core/template-part', 'core/missing', 'core/pattern',
    ];

    /**
     * Server half of editor.js's "Responsive Settings" attribute: without
     * it, a dynamic block previewed in the editor via ServerSideRender
     * (Archives, Calendar, RSS...) would be rejected by the REST API for
     * carrying an attribute its schema doesn't declare.
     */
    public function add_responsive_attribute($args, $block_type) {
        if (in_array($block_type, self::RESPONSIVE_EXCLUDED, true)) {
            return $args;
        }

        return self::with_attributes($args, ['omegaResponsive' => ['type' => 'object']]);
    }

    /**
     * Server half of editor.js's "omega-design/column-background" filter -
     * core only renders a block's background image (and its Fixed
     * background / focal point / size settings) when the block type itself
     * declares background support, which core/column(s) don't by default.
     */
    public function add_background_support($args, $block_type) {
        if (!in_array($block_type, ['core/column', 'core/columns'], true)) {
            return $args;
        }

        $supports             = $args['supports'] ?? [];
        $background           = $supports['background'] ?? [];
        $supports['background'] = array_merge($background, [
            'backgroundImage' => true,
            'backgroundSize'  => true,
        ]);
        $args['supports'] = $supports;

        return $args;
    }

    public function register() {
        $this->register_omega_icon();
        $this->register_omega_slider();
        $this->register_omega_content_slider();
        $this->register_omega_tabs();
        $this->register_omega_newsletter();
        $this->register_server_rendered_block('omega-view-toggle', ['wp-server-side-render']);
        $this->register_server_rendered_block('omega-product-layout');
    }

    private function register_omega_icon() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-icon';

        if (!file_exists($dir . '/block.json')) {
            return;
        }

        $script_path = $dir . '/index.js';
        wp_register_script(
            'omega-icon-block-editor',
            self::block_file_uri($dir, 'index.js'),
            self::EDITOR_DEPS,
            file_exists($script_path) ? filemtime($script_path) : OMEGA_DESIGN_VERSION,
            true
        );
        wp_set_script_translations('omega-icon-block-editor', 'omega-design');

        wp_register_style(
            'omega-icon-block',
            self::asset_uri('css/omega-icon-choreography.css'),
            [],
            self::has_asset('css/omega-icon-choreography.css') ? self::asset_version('css/omega-icon-choreography.css') : OMEGA_DESIGN_VERSION
        );

        register_block_type($dir);
    }

    /**
     * Registers a block's editor script, front-end view script and shared
     * style.css from its own directory using the theme's standard hand-
     * rolled (no build step) convention - mirrors register_omega_icon()
     * above. $handles keys: 'editor', 'view' and 'style' (each only if the
     * corresponding file exists in $dir).
     */
    private function register_block_assets($dir, $handles, $editor_deps = self::EDITOR_DEPS) {
        if (!file_exists($dir . '/block.json')) {
            return;
        }

        if (self::register_block_script($dir, 'index.js', $handles['editor'] ?? '', $editor_deps)) {
            wp_set_script_translations($handles['editor'], 'omega-design');
        }

        self::register_block_script($dir, 'view.js', $handles['view'] ?? '', [self::core_script()]);

        $style_path = $dir . '/style.css';
        if (file_exists($style_path) && !empty($handles['style'])) {
            wp_register_style($handles['style'], self::block_file_uri($dir, 'style.css'), [], filemtime($style_path));
        }
    }

    /**
     * Registers $file from a block's directory as a footer script under
     * $handle, when both exist. Returns whether it was registered.
     */
    private static function register_block_script($dir, $file, $handle, array $deps) {
        $path = $dir . '/' . $file;
        if (!file_exists($path) || empty($handle)) {
            return false;
        }

        wp_register_script($handle, self::block_file_uri($dir, $file), $deps, filemtime($path), true);
        return true;
    }

    private static function block_file_uri($dir, $file) {
        return OMEGA_DESIGN_URI . '/blocks/' . basename($dir) . '/' . $file;
    }

    private function register_omega_slider() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-slider';
        $this->register_block_assets($dir, [
            'editor' => 'omega-slider-block-editor',
            'view'   => 'omega-slider-block-view',
            'style'  => 'omega-slider-block',
        ], array_merge(self::EDITOR_DEPS, ['wp-data', self::editor_shared_script()]));
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
        ], array_merge(self::EDITOR_DEPS, [self::editor_shared_script()]));
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
        $labels = self::tab_labels($block->parsed_block['innerBlocks'] ?? []);

        if (empty($labels)) {
            return $content;
        }

        // $content is already the block's own wrapper (.omega-tabs, carrying
        // its align/spacing supports) plus the panels inside it - insert the
        // tab bar as the wrapper's first child rather than adding another
        // wrapping element around it.
        return block_html::insert_after_first_tag($content, self::tab_list_html($labels));
    }

    /**
     * Each tabs-item child's label, in order.
     */
    private static function tab_labels(array $inner_blocks) {
        $labels = [];
        foreach ($inner_blocks as $inner) {
            if (($inner['blockName'] ?? '') === 'omega-design/tabs-item') {
                $labels[] = $inner['attrs']['label'] ?? __('Tab', 'omega-design');
            }
        }
        return $labels;
    }

    private static function tab_list_html(array $labels) {
        $tab_list = '<div class="omega-tabs__list" role="tablist">';
        foreach ($labels as $i => $label) {
            $tab_list .= sprintf(
                '<button type="button" class="omega-tabs__tab%s" role="tab" aria-selected="%s">%s</button>',
                0 === $i ? ' is-active' : '',
                0 === $i ? 'true' : 'false',
                esc_html($label)
            );
        }
        return $tab_list . '</div>';
    }

    /**
     * A block rendered entirely by its own render.php (block.json "render"),
     * with only a small editor script - omega-design/view-toggle and
     * omega-design/product-layout. Its editor handle is "{dir}-block-editor".
     */
    private function register_server_rendered_block($slug, array $extra_editor_deps = []) {
        $dir = OMEGA_DESIGN_BLOCKS . '/' . $slug;
        $this->register_block_assets($dir, ['editor' => $slug . '-block-editor'], array_merge(self::EDITOR_DEPS, $extra_editor_deps));
        register_block_type($dir);
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
