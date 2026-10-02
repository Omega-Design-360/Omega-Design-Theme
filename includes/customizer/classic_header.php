<?php
/**
 * Classic Header - 5 hand-written, mobile-friendly header layouts, each
 * its own real, findable file under template-parts/classic-header/ (not
 * markup assembled invisibly in PHP) - bypassing block parsing entirely
 * for speed: the site's own logo if one is set (else the site title),
 * one wp_nav_menu() call, one small shared CSS file, one small shared
 * vanilla-JS toggle. An admin picks a style (or "Block Navigation" to
 * leave the Site Editor header alone) under Omega Design > Settings >
 * Header Navigation.
 *
 * Existing classic-menu Mega Menu support in megamenu.php
 * (process_classic_menu_items/add_classic_trigger_class/inject_classic_panel)
 * applies automatically here since it hooks wp_nav_menu()'s own filters,
 * not anything specific to how the menu is embedded in the page.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\core\dark_logo;
use OmegaDesign\traits\assets;
use OmegaDesign\traits\classic_template_part;
use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class classic_header {

    use singleton;
    use assets;
    use customizer_section;
    use classic_template_part;

    const STYLES = ['classic-1', 'classic-2', 'classic-3', 'classic-4', 'classic-5'];

    /**
     * Style slug -> its own real template file under
     * template-parts/classic-header/, so each one is something an admin
     * can actually find and hand-edit, rather than markup generated
     * invisibly inside this class.
     */
    const TEMPLATE_FILES = [
        'classic-1' => 'minimal-bar.php',
        'classic-2' => 'centered.php',
        'classic-3' => 'split.php',
        'classic-4' => 'boxed-nav.php',
        'classic-5' => 'bold-accent.php',
    ];

    /**
     * "default" always means "no override at all" - nothing is written to
     * .omega-classic-header__nav a's font-size and the theme's own Global
     * Styles scale keeps deciding, same "don't hardcode, let the theme
     * decide" rule the rest of this file already follows. The 4 explicit
     * choices still point at theme.json's own font-size presets rather
     * than inventing arbitrary rem numbers, so they track whatever scale
     * this particular site actually has configured.
     */
    const FONT_SIZE_CHOICES = [
        'default' => 'Theme default',
        'small'   => 'Small',
        'medium'  => 'Medium',
        'large'   => 'Large',
        'x-large' => 'Extra Large',
    ];

    const HEIGHT_CHOICES = [
        'default'  => 'Theme default (per style)',
        'compact'  => 'Compact',
        'regular'  => 'Regular',
        'spacious' => 'Spacious',
    ];

    /** Font-size choice => CSS value (theme.json presets, with fallbacks). */
    const FONT_SIZE_VALUES = [
        'small'   => 'var(--wp--preset--font-size--small, 0.875rem)',
        'medium'  => 'var(--wp--preset--font-size--medium, 1rem)',
        'large'   => 'var(--wp--preset--font-size--large, 1.25rem)',
        'x-large' => 'var(--wp--preset--font-size--x-large, 1.75rem)',
    ];

    /** Height choice => vertical padding of the header's inner row. */
    const HEIGHT_VALUES = [
        'compact'  => '0.55rem',
        'regular'  => '1rem',
        'spacious' => '1.6rem',
    ];

    private function __construct() {
        add_filter('render_block_core/template-part', [$this, 'maybe_render_classic_header'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Priority 20 (after the mysite-editor plugin's own default-priority
        // customize_register hook, includes/core/header.php) so its
        // 'header_options' section already exists by the time
        // register_customizer() tries to remove it below - see the
        // remove_section() call there for why.
        add_action('customize_register', [$this, 'register_customizer'], 20);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
    }

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-header-style-grid, admin-
     * pages.css) - this reuses that exact CSS in the Customizer's controls
     * panel too, so the style picker is available from Customize as well
     * as Settings, matching the Color Scheme/Color Mode/Sidebar pickers.
     */
    public function enqueue_control_assets() {
        self::enqueue_admin_pages_style(false);
    }

    public function register_customizer($wp_customize) {
        // The mysite-editor plugin's own "Header Options" section (a plain
        // template <select> + raw CSS/HTML textareas) duplicates - and can
        // conflict with - this theme's own Header Style picker below,
        // which is the theme's supported way to switch header layouts.
        // Left registered by the plugin itself (this only hides it from
        // the Customizer's UI), so it comes back on its own if that plugin
        // is ever deactivated or replaced.
        if ($wp_customize->get_section('header_options')) {
            $wp_customize->remove_section('header_options');
        }

        self::ensure_design_panel($wp_customize);

        $wp_customize->add_section('omega_header_style_settings', [
            'title'       => __('Header Style', 'omega-design'),
            'description' => __('Pick a header layout - a hand-built Classic style, or Block Navigation to keep editing the header in the Site Editor instead. More options (colors, sticky behavior, the classic menu to use, ...) live under Omega Design > Settings > Header Navigation.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 15,
        ]);

        $wp_customize->add_setting('omega_nav_mode', [
            'default'           => 'block',
            'sanitize_callback' => [$this, 'sanitize_nav_mode'],
            'transport'         => 'refresh',
        ]);

        self::add_card_control($wp_customize, 'omega_nav_mode', 'omega_header_style', [__CLASS__, 'render_style_cards'], [
            'label'   => __('Header Style', 'omega-design'),
            'section' => 'omega_header_style_settings',
        ]);
    }

    public function sanitize_nav_mode($value) {
        $value = self::normalize_mode(sanitize_key((string) $value));
        return self::sanitize_choice($value, self::style_choices(), 'block');
    }

    public static function style_choices() {
        return [
            'block'      => __('Block Navigation (Site Editor)', 'omega-design'),
            'classic-1'  => __('Classic 1 - Minimal Bar', 'omega-design'),
            'classic-2'  => __('Classic 2 - Centered', 'omega-design'),
            'classic-3'  => __('Classic 3 - Split', 'omega-design'),
            'classic-4'  => __('Classic 4 - Boxed Nav', 'omega-design'),
            'classic-5'  => __('Classic 5 - Bold Accent', 'omega-design'),
        ];
    }

    private function active_style() {
        return self::classic_style_or_empty(self::normalize_mode(get_theme_mod('omega_nav_mode', 'block')));
    }

    /**
     * Settings used to offer a plain "classic" toggle (2 choices) before
     * this grew into 5 distinct styles. Sites that already saved that bare
     * value keep working as "Classic 1" instead of silently losing their
     * classic menu until someone reopens Settings and re-saves.
     */
    public static function normalize_mode($mode) {
        return 'classic' === $mode ? 'classic-1' : $mode;
    }

    /**
     * Generic "is this one of the known option-list keys" guard, shared by
     * the font-size and height selects (menus.php) - anything unrecognized
     * (a stale value from before a choice list changed, a tampered POST)
     * falls back to $default rather than ever reaching the CSS output.
     */
    public static function sanitize_choice($value, array $choices, $default) {
        return array_key_exists($value, $choices) ? $value : $default;
    }

    /**
     * A small mockup of each header style's actual layout (logo/nav
     * arrangement, pill nav vs. plain, dark bar, ...) instead of the plain
     * <select> full of text labels an admin can't visually tell apart from
     * one another. Shared by both the Settings page (menus.php) and the
     * native Customizer control.
     */
    public static function render_style_cards($current, $link_callback) {
        self::render_radio_card_grid('omega-header-style', self::style_choices(), $current, $link_callback, [__CLASS__, 'render_style_card_body']);
    }

    public static function render_style_card_body($key, $label) {
        self::render_card_radio_dot('omega-header-style');
        self::render_style_preview($key);
        ?>
        <span class="omega-header-style-card__title"><?php echo esc_html($label); ?></span>
        <?php
    }

    private static function render_style_preview($key) {
        switch ($key) {
            case 'block':
                self::render_block_preview();
                break;
            case 'classic-2':
                self::render_centered_preview();
                break;
            case 'classic-3':
                self::render_row_preview('', '', ['', '', '--btn']);
                break;
            case 'classic-4':
                self::render_row_preview('', ' omega-header-style-card__nav--boxed', ['--active', '', '']);
                break;
            case 'classic-5':
                self::render_row_preview(' omega-header-style-card__preview--dark', '', ['', '', '']);
                break;
            default:
                // classic-1: the plain, roomy logo-left/nav-right base layout.
                self::render_row_preview('', '', ['', '', '']);
        }
    }

    private static function render_block_preview() {
        ?>
        <span class="omega-header-style-card__preview">
            <span class="omega-header-style-card__blocks-icon">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </span>
        </span>
        <?php
    }

    private static function render_centered_preview() {
        ?>
        <span class="omega-header-style-card__preview omega-header-style-card__preview--centered">
            <span class="omega-header-style-card__logo"></span>
            <span class="omega-header-style-card__divider"></span>
            <?php self::render_nav_preview('', ['', '', '']); ?>
        </span>
        <?php
    }

    /**
     * Logo-left / nav-right row. $item_modifiers holds one BEM modifier
     * suffix per nav item ('' for a plain item, e.g. '--btn').
     */
    private static function render_row_preview($preview_class, $nav_class, array $item_modifiers) {
        ?>
        <span class="omega-header-style-card__preview<?php echo esc_attr($preview_class); ?>">
            <span class="omega-header-style-card__row">
                <span class="omega-header-style-card__logo"></span>
                <?php self::render_nav_preview($nav_class, $item_modifiers); ?>
            </span>
        </span>
        <?php
    }

    private static function render_nav_preview($nav_class, array $item_modifiers) {
        ?>
        <span class="omega-header-style-card__nav<?php echo esc_attr($nav_class); ?>">
            <?php foreach ($item_modifiers as $modifier) : ?>
                <span class="omega-header-style-card__nav-item<?php echo $modifier ? esc_attr(' omega-header-style-card__nav-item' . $modifier) : ''; ?>"></span>
            <?php endforeach; ?>
        </span>
        <?php
    }

    public function enqueue_assets() {
        if ('' === $this->active_style()) {
            return;
        }

        // filemtime() as the *primary* version (see traits\assets), so every
        // CSS/JS edit here is re-fetched by browsers instead of served from
        // a stale cached copy under an unchanged theme version string.
        if (self::enqueue_style('omega-design-classic-header', 'css/classic-header.css')) {
            $custom_css = $this->custom_style_css();
            if ('' !== $custom_css) {
                wp_add_inline_style('omega-design-classic-header', $custom_css);
            }
        }

        self::enqueue_script('omega-design-classic-header', 'js/classic-header.js', [], true);
    }

    /**
     * Settings > Header Navigation's "Appearance overrides" (background
     * color, text color, font family, menu text size, header height) -
     * every one of them opt-in and blank/"default" unless an admin
     * actually sets it, so a fresh site with nothing configured here gets
     * zero output from this method and the theme's own Global Styles
     * fully decide, exactly like every other part of this theme.
     * !important is used deliberately: this is meant to win over whichever
     * of the 5 numbered styles is active (several of which set their own
     * background/padding at equal or lower specificity), by design - an
     * explicit admin choice here should always be the final word.
     */
    private function custom_style_css() {
        $rules = [
            self::theme_mod_rule('omega_header_bg_color', '.omega-classic-header', 'background'),
            self::theme_mod_rule('omega_header_text_color', '.omega-classic-header', 'color'),
            self::theme_mod_rule('omega_header_font_family', '.omega-classic-header', 'font-family'),
        ];

        $font_size = self::choice_value('omega_header_font_size', self::FONT_SIZE_CHOICES, self::FONT_SIZE_VALUES);
        if ('' !== $font_size) {
            $rules[] = self::important_rule('.omega-classic-header__nav a', ['font-size' => $font_size]);
        }

        $padding = self::choice_value('omega_header_height', self::HEIGHT_CHOICES, self::HEIGHT_VALUES);
        if ('' !== $padding) {
            $rules[] = self::important_rule('.omega-classic-header .omega-classic-header__inner', [
                'padding-top'    => $padding,
                'padding-bottom' => $padding,
            ]);
        }

        return implode('', $rules);
    }

    /**
     * "selector{property:value !important;}" for a free-text theme mod, or
     * '' when it's blank.
     */
    private static function theme_mod_rule($theme_mod, $selector, $property) {
        $value = trim((string) get_theme_mod($theme_mod, ''));
        return '' === $value ? '' : self::important_rule($selector, [$property => $value]);
    }

    /**
     * The CSS value mapped to a choice theme mod's (sanitized) value, or ''
     * for "default"/anything without a mapping.
     */
    private static function choice_value($theme_mod, array $choices, array $values) {
        $choice = self::sanitize_choice(get_theme_mod($theme_mod, 'default'), $choices, 'default');
        return $values[$choice] ?? '';
    }

    private static function important_rule($selector, array $declarations) {
        $css = '';
        foreach ($declarations as $property => $value) {
            $css .= $property . ':' . $value . ' !important;';
        }
        return $selector . '{' . $css . '}';
    }

    /**
     * Replaces the whole "header" template part's rendered content - the
     * theme's own parts/header.html, or a heavier Site-Editor-customized
     * version stored in the database, whichever would otherwise have
     * rendered - with hand-written markup: no block parsing overhead for
     * this region of the page at all.
     */
    public function maybe_render_classic_header($block_content, $block) {
        if (!self::is_template_part($block, 'header')) {
            return $block_content;
        }

        // Checked here rather than only inside the classic-style branch
        // below, since this filter is the one place that decides what
        // renders for the header slot in *both* Block Navigation and
        // Classic modes - a page's "Hide header" toggle should win either
        // way, not just when a classic style happens to be active.
        if (header_visibility::is_hidden_for_current_page()) {
            return '';
        }

        $style = $this->active_style();
        if ('' === $style) {
            return $block_content;
        }

        $template_path = self::style_template_path('classic-header', $style);
        if ('' === $template_path) {
            return $block_content;
        }

        return self::render_template_file($template_path, $this->template_vars());
    }

    /**
     * Variables every classic header template file reads.
     */
    private function template_vars() {
        return [
            'nav_html'      => self::classic_menu_html('omega_classic_menu_id'),
            'brand_html'    => $this->brand_html(),
            'sticky'        => (bool) get_theme_mod('omega_header_sticky', false),
            'wc_icons_html' => class_exists('OmegaDesign\\customizer\\woocommerce_header')
                ? woocommerce_header::get_instance()->icons_html()
                : '',
        ];
    }

    /**
     * The site's own logo (Customizer/Settings > Site Identity -
     * has_custom_logo()/get_custom_logo() are core WordPress, so this
     * works on any site the moment an admin sets one) if one is set, with
     * its dark mode variant (core\dark_logo), falling back to the site
     * title as a plain link otherwise. Never assumes either exists.
     */
    private function brand_html() {
        if (has_custom_logo()) {
            return dark_logo::add_variant(get_custom_logo());
        }

        return '<a class="omega-classic-header__title" href="' . esc_url(home_url('/')) . '">' . get_bloginfo('name') . '</a>';
    }
}

classic_header::get_instance();
