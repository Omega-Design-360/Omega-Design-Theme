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

defined('ABSPATH') || exit;

class classic_header {

    private static $instance = null;

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

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block_core/template-part', [$this, 'maybe_render_classic_header'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function init() {}

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
        $mode = self::normalize_mode(get_theme_mod('omega_nav_mode', 'block'));
        return in_array($mode, self::STYLES, true) ? $mode : '';
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

    public function enqueue_assets() {
        if ('' === $this->active_style()) {
            return;
        }

        // filemtime() as the *primary* version, not a fallback - `$ver ??
        // filemtime(...)` never actually ran filemtime() at all, since
        // OMEGA_DESIGN_VERSION is a constant that's never null, so every
        // CSS/JS edit here kept enqueuing under the exact same unchanged
        // version string and browsers had every reason to keep serving a
        // stale cached copy instead of re-fetching. Matches hooks.php's own
        // asset_version() helper, which gets this right already.

        $css_path = get_template_directory() . '/assets/css/classic-header.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-classic-header',
                get_template_directory_uri() . '/assets/css/classic-header.css',
                [],
                filemtime($css_path)
            );

            $custom_css = $this->custom_style_css();
            if ('' !== $custom_css) {
                wp_add_inline_style('omega-design-classic-header', $custom_css);
            }
        }

        $js_path = get_template_directory() . '/assets/js/classic-header.js';
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'omega-design-classic-header',
                get_template_directory_uri() . '/assets/js/classic-header.js',
                [],
                filemtime($js_path),
                true
            );
        }
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
        $rules = [];

        $bg_color = trim((string) get_theme_mod('omega_header_bg_color', ''));
        if ('' !== $bg_color) {
            $rules[] = '.omega-classic-header{background:' . $bg_color . ' !important;}';
        }

        $text_color = trim((string) get_theme_mod('omega_header_text_color', ''));
        if ('' !== $text_color) {
            $rules[] = '.omega-classic-header{color:' . $text_color . ' !important;}';
        }

        $font_family = trim((string) get_theme_mod('omega_header_font_family', ''));
        if ('' !== $font_family) {
            $rules[] = '.omega-classic-header{font-family:' . $font_family . ' !important;}';
        }

        $font_size_map = [
            'small'   => 'var(--wp--preset--font-size--small, 0.875rem)',
            'medium'  => 'var(--wp--preset--font-size--medium, 1rem)',
            'large'   => 'var(--wp--preset--font-size--large, 1.25rem)',
            'x-large' => 'var(--wp--preset--font-size--x-large, 1.75rem)',
        ];
        $font_size = self::sanitize_choice(get_theme_mod('omega_header_font_size', 'default'), self::FONT_SIZE_CHOICES, 'default');
        if (isset($font_size_map[$font_size])) {
            $rules[] = '.omega-classic-header__nav a{font-size:' . $font_size_map[$font_size] . ' !important;}';
        }

        $height_map = [
            'compact'  => '0.55rem',
            'regular'  => '1rem',
            'spacious' => '1.6rem',
        ];
        $height = self::sanitize_choice(get_theme_mod('omega_header_height', 'default'), self::HEIGHT_CHOICES, 'default');
        if (isset($height_map[$height])) {
            $rules[] = '.omega-classic-header .omega-classic-header__inner{padding-top:' . $height_map[$height] . ' !important;padding-bottom:' . $height_map[$height] . ' !important;}';
        }

        return implode('', $rules);
    }

    /**
     * Replaces the whole "header" template part's rendered content - the
     * theme's own parts/header.html, or a heavier Site-Editor-customized
     * version stored in the database, whichever would otherwise have
     * rendered - with hand-written markup: no block parsing overhead for
     * this region of the page at all.
     */
    public function maybe_render_classic_header($block_content, $block) {
        if ('header' !== ($block['attrs']['slug'] ?? '')) {
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

        $template_file = self::TEMPLATE_FILES[$style] ?? '';
        $template_path = get_template_directory() . '/template-parts/classic-header/' . $template_file;
        if ('' === $template_file || !file_exists($template_path)) {
            return $block_content;
        }

        $menu_id  = (int) get_theme_mod('omega_classic_menu_id', 0);
        $nav_html = '';
        if ($menu_id && wp_get_nav_menu_object($menu_id)) {
            $nav_html = (string) wp_nav_menu([
                'menu'        => $menu_id,
                'echo'        => false,
                'container'   => false,
                'fallback_cb' => false,
            ]);
        }

        $brand_html = $this->brand_html();
        $sticky     = (bool) get_theme_mod('omega_header_sticky', false);

        ob_start();
        include $template_path;
        return (string) ob_get_clean();
    }

    /**
     * The site's own logo (Customizer/Settings > Site Identity -
     * has_custom_logo()/get_custom_logo() are core WordPress, so this
     * works on any site the moment an admin sets one) if one is set,
     * falling back to the site title as a plain link otherwise. Never
     * assumes either exists.
     */
    private function brand_html() {
        if (has_custom_logo()) {
            return $this->with_dark_logo_variant(get_custom_logo());
        }

        return '<a class="omega-classic-header__title" href="' . esc_url(home_url('/')) . '">' . get_bloginfo('name') . '</a>';
    }

    /**
     * Duplicates get_custom_logo()'s <img> into a light/dark pair - same
     * mechanism, theme_mod ('omega_custom_logo_dark', set from the Omega
     * Design dashboard) and .omega-logo-light/.omega-logo-dark CSS classes
     * as hooks.php's inject_dark_mode_logo() already uses for the
     * block-based core/site-logo, so a dark logo uploaded once applies
     * here too rather than only to the block-editor header. color-mode.css
     * shows/hides whichever one matches the active color mode; no CSS
     * changes needed to support this.
     */
    private function with_dark_logo_variant($logo_html) {
        $dark_logo_id = (int) get_theme_mod('omega_custom_logo_dark');

        if (!$dark_logo_id || strpos($logo_html, 'custom-logo') === false) {
            return $logo_html;
        }

        $dark_image = wp_get_attachment_image($dark_logo_id, 'full', false, [
            'class' => 'custom-logo omega-logo-dark',
        ]);

        $with_dark_logo = preg_replace_callback(
            '/<img\b[^>]*\bclass="[^"]*\bcustom-logo\b[^"]*"[^>]*\/?>/i',
            function ($matches) use ($dark_image) {
                $light_image = preg_replace('/class="([^"]*)"/', 'class="$1 omega-logo-light"', $matches[0], 1);
                return $light_image . $dark_image;
            },
            $logo_html,
            1
        );

        return null !== $with_dark_logo ? $with_dark_logo : $logo_html;
    }
}

classic_header::get_instance();
