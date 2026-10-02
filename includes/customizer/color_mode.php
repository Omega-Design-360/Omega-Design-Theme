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

use OmegaDesign\traits\assets;
use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class color_mode {

    use singleton;
    use assets;
    use customizer_section;

    const THEME_MOD    = 'omega_color_mode';
    const DEFAULT_MODE = 'auto';
    const VALID_MODES  = ['auto', 'light', 'dark'];

    const SUN_ICON  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
    const MOON_ICON = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';

    private function __construct() {
        add_action('customize_register', [$this, 'register_customizer']);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
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

    public function get_mode() {
        return $this->sanitize_mode(get_theme_mod(self::THEME_MOD, self::DEFAULT_MODE));
    }

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-mode-grid, admin-pages.css) -
     * this reuses that exact CSS in the Customizer's controls panel too, so
     * the native "radio" control (a bare dot + text label, no preview at
     * all) isn't the only place this setting can be changed from.
     */
    public function enqueue_control_assets() {
        self::enqueue_admin_pages_style(false);
    }

    public function register_customizer($wp_customize) {
        self::ensure_design_panel($wp_customize);

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

        self::add_card_control($wp_customize, self::THEME_MOD, 'omega_color_mode', [__CLASS__, 'render_mode_cards'], [
            'label'   => __('Color Mode', 'omega-design'),
            'section' => 'omega_color_mode_settings',
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
        self::enqueue_style('omega-design-color-mode', 'css/color-mode.css');
    }

    /**
     * Applies the current color-mode class inside the editor canvas iframe,
     * since that document never runs the front end's body_class() filter.
     */
    public function enqueue_editor_assets() {
        self::enqueue_script('omega-design-color-mode-editor', 'js/color-mode-editor.js', [self::editor_shared_script()], true);

        wp_add_inline_script(
            'omega-design-color-mode-editor',
            'window.omegaColorMode = ' . wp_json_encode($this->get_mode()) . ';',
            'before'
        );
    }

    private static function mode_choices() {
        return [
            'auto' => [
                'label' => __('Auto Mode', 'omega-design'),
                'desc'  => __("Automatically switches based on each visitor's device.", 'omega-design'),
            ],
            'light' => [
                'label' => __('Light Mode', 'omega-design'),
                'desc'  => __('Clean, bright and modern look for your website.', 'omega-design'),
            ],
            'dark' => [
                'label' => __('Dark Mode', 'omega-design'),
                'desc'  => __('Easy on the eyes, great for low-light browsing.', 'omega-design'),
            ],
        ];
    }

    /**
     * Shared markup for both the Settings page (menus.php) and the native
     * Customizer control - a small fake-browser mockup per mode
     * (nav/heading/button/decorative shape, styled in that mode's own
     * colors) plus an icon, title and description, instead of a bare radio
     * dot.
     */
    public static function render_mode_cards($current, $link_callback) {
        self::render_radio_card_grid('omega-mode', self::mode_choices(), $current, $link_callback, [__CLASS__, 'render_mode_card_body']);
    }

    public static function render_mode_card_body($key, $mode) {
        self::render_card_radio_dot('omega-mode');

        if ('auto' === $key) {
            self::render_auto_preview();
        } else {
            self::render_single_mode_preview($key);
        }

        self::render_mode_meta($key, $mode);
    }

    private static function render_auto_preview() {
        ?>
        <span class="omega-mode-card__browser omega-mode-card__browser--auto">
            <?php self::render_browser_dots(); ?>
            <span class="omega-mode-card__browser-body">
                <?php
                self::render_browser_half('light', __('Build Your', 'omega-design'), __('Modern solutions.', 'omega-design'));
                self::render_browser_half('dark', __('Your Dream', 'omega-design'), __('Better tomorrow.', 'omega-design'));
                ?>
            </span>
        </span>
        <?php
    }

    private static function render_browser_half($half, $heading, $text) {
        ?>
        <span class="omega-mode-card__browser-half omega-mode-card__browser-half--<?php echo esc_attr($half); ?>">
            <span class="omega-mode-card__nav">
                <span class="omega-mode-card__logo">LOGO</span>
            </span>
            <?php self::render_hero_preview($heading, $text); ?>
        </span>
        <?php
    }

    private static function render_single_mode_preview($key) {
        ?>
        <span class="omega-mode-card__browser omega-mode-card__browser--<?php echo esc_attr($key); ?>">
            <?php self::render_browser_dots(); ?>
            <span class="omega-mode-card__browser-body">
                <span class="omega-mode-card__nav">
                    <span class="omega-mode-card__logo">LOGO</span>
                    <span class="omega-mode-card__nav-links">
                        <span><?php esc_html_e('Home', 'omega-design'); ?></span>
                        <span><?php esc_html_e('About', 'omega-design'); ?></span>
                        <span><?php esc_html_e('Services', 'omega-design'); ?></span>
                        <span><?php esc_html_e('Contact', 'omega-design'); ?></span>
                    </span>
                </span>
                <?php self::render_hero_preview(__('Build Your Dream', 'omega-design'), __('Modern solutions for a better tomorrow.', 'omega-design')); ?>
            </span>
        </span>
        <?php
    }

    private static function render_browser_dots() {
        echo '<span class="omega-mode-card__browser-dots"><span></span><span></span><span></span></span>';
    }

    private static function render_hero_preview($heading, $text) {
        ?>
        <span class="omega-mode-card__heading"><?php echo esc_html($heading); ?></span>
        <span class="omega-mode-card__text"><?php echo esc_html($text); ?></span>
        <span class="omega-mode-card__btn"><?php esc_html_e('Get Started', 'omega-design'); ?></span>
        <span class="omega-mode-card__decor"></span>
        <?php
    }

    private static function render_mode_meta($key, $mode) {
        ?>
        <span class="omega-mode-card__meta">
            <span class="omega-mode-card__icon<?php echo 'auto' === $key ? ' omega-mode-card__icon--split' : ''; ?>">
                <?php echo self::mode_icon($key); // phpcs:ignore -- static, trusted markup ?>
            </span>
            <span>
                <span class="omega-mode-card__title"><?php echo esc_html($mode['label']); ?></span>
                <span class="omega-mode-card__desc"><?php echo esc_html($mode['desc']); ?></span>
            </span>
        </span>
        <?php
    }

    private static function mode_icon($key) {
        switch ((string) $key) {
            case 'light':
                return self::SUN_ICON;
            case 'dark':
                return self::MOON_ICON;
            default:
                return self::SUN_ICON . self::MOON_ICON;
        }
    }
}
