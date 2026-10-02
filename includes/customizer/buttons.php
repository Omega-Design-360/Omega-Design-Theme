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

use OmegaDesign\traits\assets;
use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class buttons {

    use singleton;
    use assets;
    use customizer_section;

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

    private function __construct() {
        add_action('customize_register', [$this, 'register_customizer']);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
    }

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-btn-radius-grid/.omega-btn-
     * look-grid, admin-pages.css) - reused here so Buttons has the same
     * native "radio" fallback (a bare dot + text label) replaced with a
     * real preview in the Customizer too.
     */
    public function enqueue_control_assets() {
        // The Customizer's controls pane is a plain wp-admin page like the
        // Settings page and never prints the theme.json-derived
        // --wp--preset--color--* variables on its own, so they're printed
        // alongside the stylesheet to keep the previews tracking the real
        // color scheme.
        self::enqueue_admin_pages_style(true);
    }

    public function register_customizer($wp_customize) {
        self::ensure_design_panel($wp_customize);

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

        self::add_card_control($wp_customize, 'omega_button_radius', 'omega_button_radius', [__CLASS__, 'render_radius_cards'], [
            'label'    => __('Corner Style', 'omega-design'),
            'section'  => 'omega_button_settings',
            'priority' => 10,
        ]);

        self::add_card_control($wp_customize, 'omega_button_look', 'omega_button_look', [__CLASS__, 'render_look_cards'], [
            'label'    => __('Default Look', 'omega-design'),
            'section'  => 'omega_button_settings',
            'priority' => 20,
        ]);
    }

    public function sanitize_radius($value) {
        return self::sanitize_key_choice($value, menus::BUTTON_RADIUS_CHOICES, 'soft');
    }

    public function sanitize_look($value) {
        return self::sanitize_key_choice($value, menus::BUTTON_LOOK_CHOICES, 'fill');
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
        self::render_radio_card_grid('omega-btn-radius', menus::BUTTON_RADIUS_CHOICES, $current, $link_callback, function ($key, $px) {
            self::render_button_card_body('omega-btn-radius', '', 'border-radius:' . $px . ';', self::RADIUS_LABELS[$key] ?? $key);
        });
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
        self::render_radio_card_grid('omega-btn-look', menus::BUTTON_LOOK_CHOICES, $current, $link_callback, function ($key, $label) {
            self::render_button_card_body('omega-btn-look', 'omega-btn-look-card__swatch--' . $key, '', $label);
        });
    }

    /**
     * Radio dot + a "Button" swatch (with an optional extra class and
     * inline style) + the card's name.
     */
    private static function render_button_card_body($prefix, $modifier_class, $style, $name) {
        $swatch_class = trim($prefix . '-card__swatch ' . $modifier_class);

        self::render_card_radio_dot($prefix);
        ?>
        <span class="<?php echo esc_attr($prefix); ?>-card__preview">
            <span class="<?php echo esc_attr($swatch_class); ?>"<?php echo $style ? ' style="' . esc_attr($style) . '"' : ''; ?>>
                <?php esc_html_e('Button', 'omega-design'); ?>
            </span>
        </span>
        <span class="<?php echo esc_attr($prefix); ?>-card__name"><?php echo esc_html($name); ?></span>
        <?php
    }
}
