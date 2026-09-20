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

defined('ABSPATH') || exit;

class typography {

    const HEADING_MOD = 'omega_heading_font';
    const BODY_MOD     = 'omega_body_font';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

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

    public function init() {}

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
        $choices  = ['' => __('Theme Default', 'omega-design')];
        $families = wp_get_global_settings(['typography', 'fontFamilies']);

        if (!is_array($families)) {
            return $choices;
        }

        $flat = [];
        foreach ($families as $value) {
            if (is_array($value) && isset($value['slug'])) {
                $flat[] = $value;
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    if (is_array($item) && isset($item['slug'])) {
                        $flat[] = $item;
                    }
                }
            }
        }

        foreach ($flat as $family) {
            if (!empty($family['slug']) && !empty($family['name'])) {
                $choices[$family['slug']] = $family['name'];
            }
        }

        return $choices;
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
        $value   = sanitize_key((string) $value);
        $choices = self::get_font_choices();
        return isset($choices[$value]) ? $value : '';
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
        $css_path = OMEGA_DESIGN_ASSETS . '/css/admin-pages.css';
        wp_enqueue_style(
            'omega-design-admin-pages',
            OMEGA_DESIGN_CSS_URI . '/admin-pages.css',
            [],
            file_exists($css_path) ? filemtime($css_path) : OMEGA_DESIGN_ASSET_VERSION
        );

        // The Customizer's controls pane is a plain wp-admin page like the
        // Settings page (see menus.php's own enqueue_admin_page_assets()),
        // and never prints the theme.json-derived --wp--preset--font-
        // family--* variables on its own - without this, every preview
        // card's var(--wp--preset--font-family--{slug}) (no fallback, so a
        // wrong font is immediately obvious rather than silently matching
        // by coincidence) would resolve to nothing and the card would just
        // keep showing whatever font it inherited.
        wp_add_inline_style('omega-design-admin-pages', wp_get_global_stylesheet(['variables']));

        $js_path = OMEGA_DESIGN_ASSETS . '/js/admin-typography-preview.js';
        wp_enqueue_script(
            'omega-design-admin-typography-preview',
            OMEGA_DESIGN_JS_URI . '/admin-typography-preview.js',
            [],
            file_exists($js_path) ? filemtime($js_path) : OMEGA_DESIGN_ASSET_VERSION,
            true
        );
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

        if (!$wp_customize->get_panel('omega_design_panel')) {
            $wp_customize->add_panel('omega_design_panel', [
                'title'       => __('Omega Design', 'omega-design'),
                'description' => __('Theme-specific options for Omega Design. General site identity, colors and layout are managed in Global Styles via the Site Editor.', 'omega-design'),
                'priority'    => 30,
            ]);
        }

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

        // WP_Customize_Control only exists once the Customizer's own class
        // files have loaded, right before 'customize_register' fires - see
        // omega_define_typography_controls()'s own comment for why this is
        // called here rather than the classes being declared at this
        // file's top level.
        omega_define_typography_controls();

        $wp_customize->add_control(new omega_heading_font_control($wp_customize, self::HEADING_MOD, [
            'label'    => __('Heading Font', 'omega-design'),
            'section'  => 'omega_typography_settings',
            'priority' => 10,
        ]));

        $wp_customize->add_control(new omega_body_font_control($wp_customize, self::BODY_MOD, [
            'label'    => __('Body Font', 'omega-design'),
            'section'  => 'omega_typography_settings',
            'priority' => 20,
        ]));
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

        wp_register_style('omega-design-typography', false, [], OMEGA_DESIGN_ASSET_VERSION);
        wp_enqueue_style('omega-design-typography');
        wp_add_inline_style('omega-design-typography', $css);
    }
}

/**
 * Declared lazily (called from register_customizer(), which only ever runs
 * on 'customize_register') rather than at this file's top level, since
 * WP_Customize_Control doesn't exist yet when this file is first required
 * during theme bootstrap - the same fatal-error trap the color_mode
 * control hit before this pattern was established (see
 * includes/customizer/color_mode.php's own version of this function).
 */
function omega_define_typography_controls() {
    if (class_exists(__NAMESPACE__ . '\\omega_heading_font_control')) {
        return;
    }

    class omega_heading_font_control extends \WP_Customize_Control {
        public $type = 'omega_heading_font';

        public function render_content() {
            ?>
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
            <?php
            $control = $this;
            typography::render_font_picker(
                $this->value(),
                function () use ($control) {
                    $control->link();
                },
                __('Build Your Dream', 'omega-design'),
                'heading',
                $this->id
            );
            ?>
            <?php
        }
    }

    class omega_body_font_control extends \WP_Customize_Control {
        public $type = 'omega_body_font';

        public function render_content() {
            ?>
            <?php if ($this->label) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php
            $control = $this;
            typography::render_font_picker(
                $this->value(),
                function () use ($control) {
                    $control->link();
                },
                __('The quick brown fox jumps over the lazy dog.', 'omega-design'),
                'body',
                $this->id
            );
            ?>
            <?php
        }
    }
}
