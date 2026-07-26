<?php
/**
 * Page/Post Background Color
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class background_color {

    const META_KEY = 'omega_background_color';
    const DARK_META_KEY = 'omega_background_color_dark';

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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_style']);
    }

    public function init() {}

    /**
     * Post types that get the "Background Color" control in the editor.
     */
    public function get_supported_post_types() {
        return apply_filters('omega_design_background_color_post_types', ['post', 'page']);
    }

    public function register_meta() {
        foreach ($this->get_supported_post_types() as $post_type) {
            foreach ([self::META_KEY, self::DARK_META_KEY] as $meta_key) {
                register_post_meta($post_type, $meta_key, [
                    'show_in_rest'      => true,
                    'single'            => true,
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => [$this, 'sanitize_color'],
                    'auth_callback'     => function () {
                        return current_user_can('edit_posts');
                    },
                ]);
            }
        }
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

    public function enqueue_editor_assets() {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->post_type, $this->get_supported_post_types(), true)) {
            return;
        }

        wp_enqueue_script(
            'omega-design-background-color',
            OMEGA_DESIGN_JS_URI . '/background-color.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n', 'omega-design-content-width'],
            $this->asset_version(OMEGA_DESIGN_ASSETS . '/js/background-color.js'),
            true
        );

        wp_enqueue_style(
            'omega-design-background-color-editor',
            OMEGA_DESIGN_CSS_URI . '/background-color-editor.css',
            [],
            $this->asset_version(OMEGA_DESIGN_ASSETS . '/css/background-color-editor.css')
        );
    }

    /**
     * filemtime()-based version so a saved edit to the JS or editor CSS is
     * picked up on the next load instead of being cached by the browser
     * under the static OMEGA_DESIGN_ASSET_VERSION query string.
     */
    private function asset_version($path) {
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
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

        $light = get_post_meta($post_id, self::META_KEY, true);
        $dark  = get_post_meta($post_id, self::DARK_META_KEY, true);

        if (!$light && !$dark) {
            return;
        }

        $css = '';

        if ($light) {
            $css .= 'body{background-color:' . esc_attr($light) . ' !important;}';
        }

        if ($dark) {
            $dark = esc_attr($dark);
            $css .= '@media (prefers-color-scheme: dark){body.omega-color-mode-auto{background-color:' . $dark . ' !important;}}';
            $css .= 'body.omega-color-mode-dark{background-color:' . $dark . ' !important;}';
        }

        wp_register_style('omega-design-background-color', false, [], OMEGA_DESIGN_ASSET_VERSION);
        wp_enqueue_style('omega-design-background-color');
        wp_add_inline_style('omega-design-background-color', $css);
    }
}
