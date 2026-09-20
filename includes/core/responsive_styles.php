<?php
/**
 * Tablet/mobile overrides for the Hover Colors, Hover Shadow, Custom Size
 * (Width/Height) and Text Alignment controls added to the Group/Row/Stack/
 * Grid, Columns and Column blocks in assets/js/editor.js.
 *
 * Those blocks are static (no PHP render callback) - editor.js already
 * bakes the "desktop" values in directly as inline styles/CSS custom
 * properties at save time, which works with no PHP involved at all. But a
 * single inline style can't vary by viewport width, so tablet/mobile
 * overrides need a real `@media` rule instead. WordPress re-parses every
 * block's attributes from post_content on every request regardless of
 * whether the block is static, so `render_block` still fires here - this
 * reads those attributes and prepends a `<style>` block scoped to a class
 * generated fresh on every render (via wp_unique_id()), so two copies of
 * the same duplicated block never collide over a shared selector.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class responsive_styles {

    private static $instance = null;

    const HOVER_BLOCKS = ['core/group', 'core/columns', 'core/column'];
    const SIZE_BLOCKS   = ['core/group', 'core/columns'];

    const BREAKPOINTS = [
        'tablet' => '1024px',
        'mobile' => '599px',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block', [$this, 'inject_responsive_styles'], 10, 2);
    }

    public function init() {}

    public function inject_responsive_styles($block_content, $block) {
        $name         = $block['blockName'] ?? '';
        $is_hover_block = in_array($name, self::HOVER_BLOCKS, true);
        $is_size_block  = in_array($name, self::SIZE_BLOCKS, true);

        if (!$is_hover_block && !$is_size_block) {
            return $block_content;
        }

        $style      = $block['attrs']['style'] ?? [];
        $hover      = $is_hover_block ? ($style['omegaHover'] ?? []) : [];
        $dimensions = $is_size_block ? ($style['dimensions'] ?? []) : [];
        $align      = $is_hover_block ? ($style['omegaAlign'] ?? []) : [];

        if (empty($hover) && empty($dimensions) && empty($align)) {
            return $block_content;
        }

        $css = '';
        foreach (self::BREAKPOINTS as $device => $max_width) {
            $hover_decl = $this->build_hover_declarations($hover, $device);
            $base_decl  = $this->build_size_declarations($dimensions, $device) . $this->build_align_declarations($align, $device);

            if ($hover_decl === '' && $base_decl === '') {
                continue;
            }

            $css .= '@media (max-width:' . $max_width . '){';
            if ($base_decl !== '') {
                $css .= 'SELECTOR{' . $base_decl . '}';
            }
            if ($hover_decl !== '') {
                $css .= 'SELECTOR:hover{' . $hover_decl . '}';
            }
            $css .= '}';
        }

        if ($css === '') {
            return $block_content;
        }

        $unique_class = 'omega-rid-' . wp_unique_id();
        $css          = str_replace('SELECTOR', '.' . $unique_class, $css);

        return '<style>' . $css . '</style>' . $this->add_class_to_first_tag($block_content, $unique_class);
    }

    private function build_hover_declarations($hover, $device) {
        if (empty($hover)) {
            return '';
        }

        $property_map = [
            'Text'       => 'color',
            'Background' => 'background-color',
            'Border'     => 'border-color',
        ];

        $declarations = '';
        foreach ($property_map as $suffix => $property) {
            $value = $hover[$device . $suffix] ?? '';
            if ($value !== '') {
                $declarations .= $property . ':' . $this->sanitize_css_value($value) . ';';
            }
        }

        $size = $hover[$device . 'ShadowSize'] ?? null;
        if (!empty($size) && is_numeric($size)) {
            $size     = (int) $size;
            $offset_x = isset($hover[$device . 'ShadowOffsetX']) && is_numeric($hover[$device . 'ShadowOffsetX'])
                ? (int) $hover[$device . 'ShadowOffsetX']
                : 0;
            $offset_y = isset($hover[$device . 'ShadowOffsetY']) && is_numeric($hover[$device . 'ShadowOffsetY'])
                ? (int) $hover[$device . 'ShadowOffsetY']
                : (int) round($size / 3);
            $color = $hover[$device . 'ShadowColor'] ?? 'rgba(0, 0, 0, 0.35)';

            $declarations .= 'box-shadow:' . $offset_x . 'px ' . $offset_y . 'px ' . $size . 'px ' . $this->sanitize_css_value($color) . ';';
        }

        return $declarations;
    }

    private function build_size_declarations($dimensions, $device) {
        if (empty($dimensions)) {
            return '';
        }

        $width  = $dimensions[$device . 'Width'] ?? '';
        $height = $dimensions[$device . 'Height'] ?? '';

        $declarations = '';
        if ($width !== '') {
            $declarations .= 'width:' . $this->sanitize_css_value($width) . ';';
        }
        if ($height !== '') {
            $declarations .= 'height:' . $this->sanitize_css_value($height) . ';';
        }

        return $declarations;
    }

    private function build_align_declarations($align, $device) {
        if (empty($align)) {
            return '';
        }

        $value = $align[$device . 'TextAlign'] ?? '';
        if (!in_array($value, ['left', 'center', 'right', 'justify'], true)) {
            return '';
        }

        return 'text-align:' . $value . ';';
    }

    /**
     * Keeps only characters valid in a CSS color/length/var() value, so a
     * stray value can't break out of the generated <style> tag.
     */
    private function sanitize_css_value($value) {
        return preg_replace('/[^a-zA-Z0-9#.,%\-\s()]/', '', (string) $value);
    }

    /**
     * Injects a class into the block's root element - the element the
     * generated `@media` rules above target via `.$unique_class`.
     */
    private function add_class_to_first_tag($html, $class) {
        $class = esc_attr($class);

        if (preg_match('/^\s*<[a-z0-9]+[^>]*\sclass="/i', $html)) {
            return preg_replace('/^(\s*<[a-z0-9]+[^>]*\sclass=")/i', '$1' . $class . ' ', $html, 1);
        }

        return preg_replace('/^(\s*<[a-z0-9]+)/i', '$1 class="' . $class . '"', $html, 1);
    }
}
