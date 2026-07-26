<?php
/**
 * Color Mode (Light / Dark / Auto)
 *
 * Default "auto" mode follows the visitor's OS/browser color-scheme
 * preference via CSS `prefers-color-scheme`, which works the same way
 * on desktop and mobile. Admins can override this from
 * Customize > Omega Design > Color Mode to force the site to always be
 * light or always be dark, regardless of the visitor's system setting.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class color_mode {

    const THEME_MOD    = 'omega_color_mode';
    const DEFAULT_MODE = 'auto';
    const VALID_MODES  = ['auto', 'light', 'dark'];

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('customize_register', [$this, 'register_customizer']);
        add_filter('body_class', [$this, 'add_body_class']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Front end gets the mode via a real <body> class from add_body_class().
        // The block editor canvas is a separate iframe document that never
        // runs body_class(), so without these two the CSS file is never even
        // loaded there and, even if it were, would have no matching class to
        // key off of - the editor would always show light-mode colors no
        // matter what's chosen in Customize > Omega Design > Color Mode.
        add_editor_style('assets/css/color-mode.css');
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

    public function init() {}

    public function get_mode() {
        $mode = get_theme_mod(self::THEME_MOD, self::DEFAULT_MODE);
        return in_array($mode, self::VALID_MODES, true) ? $mode : self::DEFAULT_MODE;
    }

    public function register_customizer($wp_customize) {
        if (!$wp_customize->get_panel('omega_design_panel')) {
            $wp_customize->add_panel('omega_design_panel', [
                'title'       => __('Omega Design', 'omega-design'),
                'description' => __('Theme-specific options for Omega Design: color mode and the blog sidebar. General site identity, colors, typography and layout are managed in Global Styles via the Site Editor.', 'omega-design'),
                'priority'    => 30,
            ]);
        }

        $wp_customize->add_section('omega_color_mode_settings', [
            'title'       => __('Color Mode', 'omega-design'),
            'description' => __('Choose whether the site follows each visitor\'s system preference automatically, or is locked to one look for everyone.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 10,
        ]);

        $wp_customize->add_setting(self::THEME_MOD, [
            'default'           => self::DEFAULT_MODE,
            'sanitize_callback' => [$this, 'sanitize_mode'],
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control(self::THEME_MOD, [
            'label'       => __('Site Color Mode', 'omega-design'),
            'description' => __("Automatic matches each visitor's device or browser setting, on both desktop and mobile.", 'omega-design'),
            'section'     => 'omega_color_mode_settings',
            'type'        => 'radio',
            'choices'     => [
                'auto'  => __("Automatic (match visitor's system)", 'omega-design'),
                'light' => __('Always Light', 'omega-design'),
                'dark'  => __('Always Dark', 'omega-design'),
            ],
        ]);
    }

    public function sanitize_mode($value) {
        return in_array($value, self::VALID_MODES, true) ? $value : self::DEFAULT_MODE;
    }

    public function add_body_class($classes) {
        $classes[] = 'omega-color-mode-' . $this->get_mode();
        return $classes;
    }

    public function enqueue_assets() {
        $css_path = get_template_directory() . '/assets/css/color-mode.css';
        if (!file_exists($css_path)) {
            return;
        }

        wp_enqueue_style(
            'omega-design-color-mode',
            get_template_directory_uri() . '/assets/css/color-mode.css',
            [],
            filemtime($css_path)
        );
    }

    /**
     * Applies the current color-mode class inside the editor canvas iframe,
     * since that document never runs the front end's body_class() filter.
     */
    public function enqueue_editor_assets() {
        $js_path = OMEGA_DESIGN_ASSETS . '/js/color-mode-editor.js';

        wp_enqueue_script(
            'omega-design-color-mode-editor',
            OMEGA_DESIGN_JS_URI . '/color-mode-editor.js',
            [],
            file_exists($js_path) ? filemtime($js_path) : OMEGA_DESIGN_ASSET_VERSION,
            true
        );

        wp_add_inline_script(
            'omega-design-color-mode-editor',
            'window.omegaColorMode = ' . wp_json_encode($this->get_mode()) . ';',
            'before'
        );
    }
}
