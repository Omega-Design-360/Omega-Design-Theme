<?php
/**
 * Site-wide Button Defaults - corner style and default look
 *
 * The actual theme mods (omega_button_radius/omega_button_look) and their
 * choice lists live on the menus class (Settings > Design), which already
 * owns the POST-handling for the whole Settings page - this class only
 * adds the same setting to the native Customizer, plus the shared preview-
 * card markup both surfaces render (see render_radius_cards()/
 * render_look_cards()). The values themselves are actually applied on the
 * front end by includes/core/hooks.php (--omega-btn-radius custom property
 * and the body.omega-default-btn-{look} class respectively).
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class buttons {

    /**
     * Matches the <select> option labels render_design_form() used before
     * this class existed - kept here rather than added to
     * menus::BUTTON_RADIUS_CHOICES since that constant's values are the
     * actual px radii applied on the front end (includes/core/hooks.php),
     * not display labels.
     */
    const RADIUS_LABELS = [
        'sharp'   => 'Sharp',
        'soft'    => 'Soft (default)',
        'rounded' => 'Rounded',
        'pill'    => 'Pill',
    ];

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
    }

    public function init() {}

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-btn-radius-grid/.omega-btn-
     * look-grid, admin-pages.css) - reused here so Buttons has the same
     * native "radio" fallback (a bare dot + text label) replaced with a
     * real preview in the Customizer too.
     */
    public function enqueue_control_assets() {
        $css_path = OMEGA_DESIGN_ASSETS . '/css/admin-pages.css';
        wp_enqueue_style(
            'omega-design-admin-pages',
            OMEGA_DESIGN_CSS_URI . '/admin-pages.css',
            [],
            file_exists($css_path) ? filemtime($css_path) : OMEGA_DESIGN_ASSET_VERSION
        );

        // The Customizer's controls pane is a plain wp-admin page like the
        // Settings page (see menus.php's own enqueue_admin_page_assets()),
        // and never prints the theme.json-derived --wp--preset--color--*
        // variables on its own - every var(--wp--preset--color--...) in
        // these swatches has an explicit fallback already, so this was
        // invisible (the fallback happened to match this site's actual
        // primary color), but without it the previews would silently stop
        // tracking the real color scheme the moment that ever changed.
        wp_add_inline_style('omega-design-admin-pages', wp_get_global_stylesheet(['variables']));
    }

    public function register_customizer($wp_customize) {
        if (!$wp_customize->get_panel('omega_design_panel')) {
            $wp_customize->add_panel('omega_design_panel', [
                'title'       => __('Omega Design', 'omega-design'),
                'description' => __('Theme-specific options for Omega Design. General site identity, colors, typography and layout are managed in Global Styles via the Site Editor.', 'omega-design'),
                'priority'    => 30,
            ]);
        }

        $wp_customize->add_section('omega_button_settings', [
            'title'       => __('Buttons', 'omega-design'),
            'description' => __('Applies to any Button block that hasn\'t been given its own Style or custom radius in the block editor - an explicit per-button choice always overrides this.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 20,
        ]);

        $wp_customize->add_setting('omega_button_radius', [
            'default'           => 'soft',
            'sanitize_callback' => [$this, 'sanitize_radius'],
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_setting('omega_button_look', [
            'default'           => 'fill',
            'sanitize_callback' => [$this, 'sanitize_look'],
            'transport'         => 'refresh',
        ]);

        // WP_Customize_Control only exists once the Customizer's own class
        // files have loaded, right before 'customize_register' fires - see
        // omega_define_button_controls()'s own comment for why this is
        // called here rather than the classes being declared at this
        // file's top level.
        omega_define_button_controls();

        $wp_customize->add_control(new omega_button_radius_control($wp_customize, 'omega_button_radius', [
            'label'    => __('Corner Style', 'omega-design'),
            'section'  => 'omega_button_settings',
            'priority' => 10,
        ]));

        $wp_customize->add_control(new omega_button_look_control($wp_customize, 'omega_button_look', [
            'label'    => __('Default Look', 'omega-design'),
            'section'  => 'omega_button_settings',
            'priority' => 20,
        ]));
    }

    public function sanitize_radius($value) {
        $value = sanitize_key((string) $value);
        return array_key_exists($value, menus::BUTTON_RADIUS_CHOICES) ? $value : 'soft';
    }

    public function sanitize_look($value) {
        $value = sanitize_key((string) $value);
        return array_key_exists($value, menus::BUTTON_LOOK_CHOICES) ? $value : 'fill';
    }

    /**
     * Corner Style picker: 4 cards, each a small filled-look button swatch
     * at that corner's actual px radius (menus::BUTTON_RADIUS_CHOICES,
     * the same values includes/core/hooks.php writes to --omega-btn-radius)
     * - always rendered in the "Fill" look so only the corner varies here,
     * "Default Look" (render_look_cards()) isolates the other variable the
     * same way. $link_callback receives each choice's key and must echo
     * whatever attributes bind that <input> to its context - a plain
     * name="omega_button_radius" for the POST form, or the Customizer's
     * own name + $this->link() for two-way JS binding.
     */
    public static function render_radius_cards($current, $link_callback) {
        ?>
        <div class="omega-btn-radius-grid">
            <?php foreach (menus::BUTTON_RADIUS_CHOICES as $key => $px) : ?>
                <label class="omega-btn-radius-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="omega-btn-radius-card__input"
                    />
                    <span class="omega-btn-radius-card__radio"></span>
                    <span class="omega-btn-radius-card__preview">
                        <span class="omega-btn-radius-card__swatch" style="border-radius:<?php echo esc_attr($px); ?>;">
                            <?php esc_html_e('Button', 'omega-design'); ?>
                        </span>
                    </span>
                    <span class="omega-btn-radius-card__name"><?php echo esc_html(self::RADIUS_LABELS[$key] ?? $key); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Default Look picker: one card per menus::BUTTON_LOOK_CHOICES,
     * swatches styled to match the real is-style-omega-* rules in
     * block-style-variations.css exactly (fill/ghost/soft/pill/3d) - a
     * fixed 6px (the "Soft" corner default) on every swatch except Pill
     * (which, like the real CSS, always forces 999px regardless of the
     * separate Corner Style setting), so this grid previews look alone.
     */
    public static function render_look_cards($current, $link_callback) {
        ?>
        <div class="omega-btn-look-grid">
            <?php foreach (menus::BUTTON_LOOK_CHOICES as $key => $label) : ?>
                <label class="omega-btn-look-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="omega-btn-look-card__input"
                    />
                    <span class="omega-btn-look-card__radio"></span>
                    <span class="omega-btn-look-card__preview">
                        <span class="omega-btn-look-card__swatch omega-btn-look-card__swatch--<?php echo esc_attr($key); ?>">
                            <?php esc_html_e('Button', 'omega-design'); ?>
                        </span>
                    </span>
                    <span class="omega-btn-look-card__name"><?php echo esc_html($label); ?></span>
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
 * during theme bootstrap - the same fatal-error trap the color_mode control
 * hit before this pattern was established (see
 * includes/customizer/color_mode.php's own version of this function).
 */
function omega_define_button_controls() {
    if (class_exists(__NAMESPACE__ . '\\omega_button_radius_control')) {
        return;
    }

    class omega_button_radius_control extends \WP_Customize_Control {
        public $type = 'omega_button_radius';

        public function render_content() {
            ?>
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <?php
            $name    = '_customize-radio-' . $this->id;
            $control = $this;
            buttons::render_radius_cards(
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

    class omega_button_look_control extends \WP_Customize_Control {
        public $type = 'omega_button_look';

        public function render_content() {
            ?>
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php
            $name    = '_customize-radio-' . $this->id;
            $control = $this;
            buttons::render_look_cards(
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
