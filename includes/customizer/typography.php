<?php
/**
 * Site-wide Typography - heading and body font
 *
 * Two opt-in overrides (default "" means "no override, let Global Styles
 * decide" - same convention as every other appearance override in this
 * theme). The choices themselves are never hardcoded here: they're read
 * straight from theme.json's settings.typography.fontFamilies via
 * wp_get_global_settings(), so this list always matches whatever fonts the
 * theme actually ships without needing to be kept in sync by hand.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\assets;
use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class typography {

    use singleton;
    use assets;
    use customizer_section;

    const HEADING_MOD = 'omega_heading_font';
    const BODY_MOD     = 'omega_body_font';

    private function __construct() {
        // Priority 20 (after the mysite-editor plugin's own default-
        // priority customize_register hook, includes/core/typography.php)
        // so its 'theme_typography' section already exists by the time
        // register_customizer() tries to remove it below - see the
        // remove_section() call there for why.
        add_action('customize_register', [$this, 'register_customizer'], 20);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_style']);
    }

    /**
     * '' ("Theme Default") plus every registered font family - reading it
     * via wp_get_global_settings() instead of re-declaring the list keeps
     * this automatically in sync with whatever fonts are actually
     * available (theme.json's settings.typography.fontFamilies, plus any
     * a plugin like WooCommerce merges in on top).
     *
     * Preset-type global settings (font families, color palette, etc.)
     * come back grouped by origin - array('default' => [...], 'theme' =>
     * [...], 'custom' => [...]) - rather than as one flat list, so this
     * flattens every origin's entries together instead of assuming a
     * particular shape.
     */
    public static function get_font_choices() {
        // Built once per request (after init, once translations and every
        // origin's font families are in place) - the font picker alone
        // asks for this once per <option>.
        static $cached = null;
        if (null !== $cached) {
            return $cached;
        }

        $choices = self::build_font_choices();
        if (did_action('init')) {
            $cached = $choices;
        }
        return $choices;
    }

    private static function build_font_choices() {
        $choices  = ['' => __('Theme Default', 'omega-design')];
        $families = wp_get_global_settings(['typography', 'fontFamilies']);

        if (!is_array($families)) {
            return $choices;
        }

        foreach (self::flatten_font_families($families) as $family) {
            if (!empty($family['slug']) && !empty($family['name'])) {
                $choices[$family['slug']] = $family['name'];
            }
        }

        return $choices;
    }

    /**
     * Every font family entry from either a flat list or a list grouped
     * by origin.
     */
    private static function flatten_font_families(array $families) {
        $flat = [];
        foreach ($families as $value) {
            if (!is_array($value)) {
                continue;
            }

            if (isset($value['slug'])) {
                $flat[] = $value;
                continue;
            }

            foreach ($value as $item) {
                if (is_array($item) && isset($item['slug'])) {
                    $flat[] = $item;
                }
            }
        }
        return $flat;
    }

    /**
     * The actual CSS font-family value for a slug - a var() reference to
     * the matching theme.json preset, exactly like every other font-family
     * value this theme ever outputs (see theme.json's own
     * styles.typography.fontFamily). Falls back to the site's real default
     * stack for "" /an unknown slug rather than an empty string, so a
     * preview card's own inline style="" is never left without a font.
     */
    public static function get_font_family_css($slug) {
        $choices = self::get_font_choices();
        if ('' === $slug || !isset($choices[$slug])) {
            return 'var(--wp--preset--font-family--system-font)';
        }
        return 'var(--wp--preset--font-family--' . $slug . ')';
    }

    public function sanitize_font($value) {
        return self::sanitize_key_choice($value, self::get_font_choices(), '');
    }

    /**
     * The theme's own Settings page (menus.php) draws the same dropdown +
     * single preview card with this same markup/CSS (.omega-font-select,
     * .omega-font-preview-card, admin-pages.css) - reused here so
     * Typography has the same native "radio" fallback (a bare dot + text
     * label) replaced with a real preview in the Customizer too.
     * admin-typography-preview.js is what actually re-fonts the preview
     * live as the dropdown changes, in both places.
     */
    public function enqueue_control_assets() {
        // With the --wp--preset--font-family--* variables: every preview
        // card's var(--wp--preset--font-family--{slug}) has no fallback,
        // so without them the card would just keep showing whatever font
        // it inherited.
        self::enqueue_admin_pages_style(true);

        self::enqueue_script('omega-design-admin-typography-preview', 'js/admin-typography-preview.js', [], true);
    }

    public function register_customizer($wp_customize) {
        // The mysite-editor plugin's own "Typography & Buttons" section
        // (raw font-family/size/weight/color fields per heading level,
        // plus button typography) duplicates - and can conflict with -
        // this theme's own Typography and Buttons sections below, which
        // are the theme's supported way to control site-wide fonts and
        // button appearance. Left registered by the plugin itself (this
        // only hides it from the Customizer's UI), so it comes back on
        // its own if that plugin is ever deactivated or replaced.
        if ($wp_customize->get_section('theme_typography')) {
            $wp_customize->remove_section('theme_typography');
        }

        self::ensure_design_panel($wp_customize);

        $wp_customize->add_section('omega_typography_settings', [
            'title'       => __('Typography', 'omega-design'),
            'description' => __('Site-wide heading and body fonts, picked from the fonts already registered in the theme. A block with its own explicit font set in the editor always overrides this.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 15,
        ]);

        $wp_customize->add_setting(self::HEADING_MOD, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitize_font'],
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_setting(self::BODY_MOD, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitize_font'],
            'transport'         => 'refresh',
        ]);

        self::add_font_control($wp_customize, self::HEADING_MOD, 'omega_heading_font', 'heading', __('Build Your Dream', 'omega-design'), [
            'label'    => __('Heading Font', 'omega-design'),
            'section'  => 'omega_typography_settings',
            'priority' => 10,
        ]);

        self::add_font_control($wp_customize, self::BODY_MOD, 'omega_body_font', 'body', __('The quick brown fox jumps over the lazy dog.', 'omega-design'), [
            'label'    => __('Body Font', 'omega-design'),
            'section'  => 'omega_typography_settings',
            'priority' => 20,
        ]);
    }

    /**
     * A card_control drawing render_font_picker() for $setting_id.
     */
    private static function add_font_control($wp_customize, $setting_id, $type, $variant, $sample, array $args) {
        self::add_card_control($wp_customize, $setting_id, $type, function ($current, $link_callback, $control) use ($variant, $sample) {
            self::render_font_picker($current, [$control, 'print_link'], $sample, $variant, $control->id);
        }, $args);
    }

    /**
     * Shared markup for both the Settings page and the Customizer controls
     * above - a plain <select> (every registered font is one more preview
     * card an admin has to scan past otherwise) plus a single preview card
     * that always reflects whichever option is currently selected,
     * re-fonted live by admin-typography-preview.js reading each <option>'s
     * own data-font-family as the dropdown changes - no page reload, and
     * no need to bind a whole grid of radios to one setting.
     * $link_callback must echo whatever attributes bind the <select> to
     * its context - a plain name="..." for the POST form, or the
     * Customizer's own $this->link() for two-way JS binding.
     */
    public static function render_font_picker($current, $link_callback, $sample, $variant, $field_id) {
        $choices     = self::get_font_choices();
        $preview_id  = $field_id . '-preview';
        ?>
        <select
            <?php call_user_func($link_callback); ?>
            id="<?php echo esc_attr($field_id); ?>"
            class="omega-font-select"
            data-preview="<?php echo esc_attr($preview_id); ?>"
        >
            <?php foreach ($choices as $slug => $name) : ?>
                <option
                    value="<?php echo esc_attr($slug); ?>"
                    data-font-family="<?php echo esc_attr(self::get_font_family_css($slug)); ?>"
                    <?php selected($current, $slug); ?>
                ><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="omega-font-preview-card">
            <span
                id="<?php echo esc_attr($preview_id); ?>"
                class="omega-font-preview-card__sample omega-font-preview-card__sample--<?php echo esc_attr($variant); ?>"
                style="font-family:<?php echo esc_attr(self::get_font_family_css($current)); ?>;"
            ><?php echo esc_html($sample); ?></span>
        </div>
        <?php
    }

    /**
     * !important overrides - blank means don't output anything, let Global
     * Styles fully decide, same convention as every other appearance
     * override in this theme (see classic_header::custom_style_css()'s own
     * version of this comment). !important is needed on both rules since
     * theme.json's own ":root :where(body)"/":root :where(h1,h2,...)"
     * rules already outrank a bare "body"/"h1,h2,..." selector on
     * specificity alone.
     */
    public function enqueue_front_style() {
        $heading = get_theme_mod(self::HEADING_MOD, '');
        $body    = get_theme_mod(self::BODY_MOD, '');

        if ('' === $heading && '' === $body) {
            return;
        }

        $css = '';

        if ('' !== $body) {
            $css .= 'body{font-family:' . self::get_font_family_css($body) . ' !important;}';
        }

        if ('' !== $heading) {
            $css .= 'h1,h2,h3,h4,h5,h6{font-family:' . self::get_font_family_css($heading) . ' !important;}';
        }

        self::enqueue_inline_style('omega-design-typography', $css);
    }
}
