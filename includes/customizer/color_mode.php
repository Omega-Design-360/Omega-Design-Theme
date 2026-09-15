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

    public function init() {}

    public function get_mode() {
        $mode = get_theme_mod(self::THEME_MOD, self::DEFAULT_MODE);
        return in_array($mode, self::VALID_MODES, true) ? $mode : self::DEFAULT_MODE;
    }

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-mode-grid, admin-pages.css) -
     * this reuses that exact CSS in the Customizer's controls panel too, so
     * the native "radio" control (a bare dot + text label, no preview at
     * all) isn't the only place this setting can be changed from.
     */
    public function enqueue_control_assets() {
        $css_path = OMEGA_DESIGN_ASSETS . '/css/admin-pages.css';
        wp_enqueue_style(
            'omega-design-admin-pages',
            OMEGA_DESIGN_CSS_URI . '/admin-pages.css',
            [],
            file_exists($css_path) ? filemtime($css_path) : OMEGA_DESIGN_ASSET_VERSION
        );
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

        // WP_Customize_Control only exists once the Customizer's own class
        // files have loaded, right before 'customize_register' fires - see
        // omega_define_color_mode_control()'s own comment for why this is
        // called here rather than the class being declared at this file's
        // top level.
        omega_define_color_mode_control();

        $wp_customize->add_control(new omega_color_mode_control($wp_customize, self::THEME_MOD, [
            'label'   => __('Color Mode', 'omega-design'),
            'section' => 'omega_color_mode_settings',
        ]));
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

    /**
     * Shared markup for both the Settings page (menus.php) and the native
     * Customizer control below - a small fake-browser mockup per mode
     * (nav/heading/button/decorative shape, styled in that mode's own
     * colors) plus an icon, title and description, instead of a bare radio
     * dot. $link_callback receives each mode's key and must echo whatever
     * attributes bind that <input> to its context - a plain
     * name="omega_color_mode" for the POST form, or the Customizer's own
     * name + $this->link() for two-way JS binding.
     */
    public static function render_mode_cards($current, $link_callback) {
        $modes = [
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

        $sun_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
        $moon_icon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';

        ?>
        <div class="omega-mode-grid">
            <?php foreach ($modes as $key => $mode) : ?>
                <label class="omega-mode-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="omega-mode-card__input"
                    />
                    <span class="omega-mode-card__radio"></span>

                    <?php if ('auto' === $key) : ?>
                        <span class="omega-mode-card__browser omega-mode-card__browser--auto">
                            <span class="omega-mode-card__browser-dots"><span></span><span></span><span></span></span>
                            <span class="omega-mode-card__browser-body">
                                <span class="omega-mode-card__browser-half omega-mode-card__browser-half--light">
                                    <span class="omega-mode-card__nav">
                                        <span class="omega-mode-card__logo">LOGO</span>
                                    </span>
                                    <span class="omega-mode-card__heading"><?php esc_html_e('Build Your', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__text"><?php esc_html_e('Modern solutions.', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__btn"><?php esc_html_e('Get Started', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__decor"></span>
                                </span>
                                <span class="omega-mode-card__browser-half omega-mode-card__browser-half--dark">
                                    <span class="omega-mode-card__nav">
                                        <span class="omega-mode-card__logo">LOGO</span>
                                    </span>
                                    <span class="omega-mode-card__heading"><?php esc_html_e('Your Dream', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__text"><?php esc_html_e('Better tomorrow.', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__btn"><?php esc_html_e('Get Started', 'omega-design'); ?></span>
                                    <span class="omega-mode-card__decor"></span>
                                </span>
                            </span>
                        </span>
                    <?php else : ?>
                        <span class="omega-mode-card__browser omega-mode-card__browser--<?php echo esc_attr($key); ?>">
                            <span class="omega-mode-card__browser-dots"><span></span><span></span><span></span></span>
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
                                <span class="omega-mode-card__heading"><?php esc_html_e('Build Your Dream', 'omega-design'); ?></span>
                                <span class="omega-mode-card__text"><?php esc_html_e('Modern solutions for a better tomorrow.', 'omega-design'); ?></span>
                                <span class="omega-mode-card__btn"><?php esc_html_e('Get Started', 'omega-design'); ?></span>
                                <span class="omega-mode-card__decor"></span>
                            </span>
                        </span>
                    <?php endif; ?>

                    <span class="omega-mode-card__meta">
                        <span class="omega-mode-card__icon<?php echo 'auto' === $key ? ' omega-mode-card__icon--split' : ''; ?>">
                            <?php
                            if ('light' === $key) {
                                echo $sun_icon; // phpcs:ignore -- static, trusted markup
                            } elseif ('dark' === $key) {
                                echo $moon_icon; // phpcs:ignore -- static, trusted markup
                            } else {
                                echo $sun_icon . $moon_icon; // phpcs:ignore -- static, trusted markup
                            }
                            ?>
                        </span>
                        <span>
                            <span class="omega-mode-card__title"><?php echo esc_html($mode['label']); ?></span>
                            <span class="omega-mode-card__desc"><?php echo esc_html($mode['desc']); ?></span>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

/**
 * Declared lazily (called from register_customizer(), which only ever runs
 * on 'customize_register') rather than at this file's top level, since
 * WP_Customize_Control doesn't exist yet when this file is first required
 * during theme bootstrap - the same fatal-error trap the color_scheme
 * control hit before this pattern was established (see
 * includes/customizer/color_scheme.php's own version of this function).
 */
function omega_define_color_mode_control() {
    if (class_exists(__NAMESPACE__ . '\\omega_color_mode_control')) {
        return;
    }

    class omega_color_mode_control extends \WP_Customize_Control {
        public $type = 'omega_color_mode';

        public function render_content() {
            ?>
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <?php
            $name = '_customize-radio-' . $this->id;
            $control = $this;
            color_mode::render_mode_cards(
                $this->value(),
                function ($key) use ($name, $control) {
                    printf('name="%s" ', esc_attr($name));
                    $control->link();
                }
            );
            ?>
            <?php
        }
    }
}
