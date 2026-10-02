<?php
/**
 * Page/Post Background Color
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class background_color {

    use singleton;
    use editor_meta;

    const META_KEY = 'omega_background_color';
    const DARK_META_KEY = 'omega_background_color_dark';

    private function __construct() {
        $this->register_editor_meta_hooks();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_style']);
    }

    protected function post_types_filter() {
        return 'omega_design_background_color_post_types';
    }

    protected function meta_fields() {
        $field = self::string_meta_field([$this, 'sanitize_color']);

        return [
            self::META_KEY      => $field,
            self::DARK_META_KEY => $field,
        ];
    }

    /**
     * Accepts either a 3-/6-digit hex color or a bare CSS custom-property
     * reference such as "var(--wp--custom--page-background)" (the only two
     * formats the editor's text fields hand back). Anything else is
     * dropped rather than echoed into the front-end <style> tag
     * unsanitized.
     */
    public function sanitize_color($value) {
        $value = trim(sanitize_text_field((string) $value));

        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            return $value;
        }

        if (preg_match('/^var\(--[A-Za-z0-9-]+\)$/', $value)) {
            return $value;
        }

        return '';
    }

    protected function enqueue_editor_screen_assets() {
        self::enqueue_editor_script(
            'omega-design-background-color',
            'js/background-color.js',
            ['omega-design-featured-image-toggle', 'omega-design-sidebar-toggle']
        );

        self::enqueue_style('omega-design-background-color-editor', 'css/background-color-editor.css');
    }

    /**
     * Prints the saved color(s) as an inline style on a no-file style
     * handle, with !important so it reliably wins over the theme.json
     * global styles rule (":root :where(body)"), which outranks a bare
     * "body" selector on specificity alone.
     *
     * The dark value is scoped to the same "omega-color-mode-*" body
     * classes color_mode.php already uses site-wide: "auto" (respects the
     * visitor's OS/browser preference) only applies it inside a
     * prefers-color-scheme: dark media query, while "dark" (forced from
     * Customize > Omega Design > Color Mode) applies it unconditionally.
     */
    public function enqueue_front_style() {
        if (!is_singular($this->get_supported_post_types())) {
            return;
        }

        $post_id = get_queried_object_id();

        if (!$post_id) {
            return;
        }

        $css = self::build_css(
            get_post_meta($post_id, self::META_KEY, true),
            get_post_meta($post_id, self::DARK_META_KEY, true)
        );

        if ('' === $css) {
            return;
        }

        self::enqueue_inline_style('omega-design-background-color', $css);
    }

    private static function build_css($light, $dark) {
        $css = '';

        if ($light) {
            $css .= 'body{background-color:' . esc_attr($light) . ' !important;}';
        }

        if ($dark) {
            $dark = esc_attr($dark);
            $css .= '@media (prefers-color-scheme: dark){body.omega-color-mode-auto{background-color:' . $dark . ' !important;}}';
            $css .= 'body.omega-color-mode-dark{background-color:' . $dark . ' !important;}';
        }

        return $css;
    }
}
