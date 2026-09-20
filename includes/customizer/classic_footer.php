<?php
/**
 * Classic Footer - 5 hand-written, mobile-friendly footer layouts, each
 * its own real, findable file under template-parts/classic-footer/ -
 * mirrors classic_header.php exactly (same architecture, same reasons).
 * Every piece of content is resolved dynamically at render time
 * (site title/tagline via bloginfo(), copyright via a template with a
 * {year} token, links via an admin-picked classic menu) - nothing about
 * a specific site's name, pages, or content is ever assumed.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class classic_footer {

    private static $instance = null;

    const STYLES = ['classic-1', 'classic-2', 'classic-3', 'classic-4', 'classic-5'];

    const TEMPLATE_FILES = [
        'classic-1' => 'simple.php',
        'classic-2' => 'columns.php',
        'classic-3' => 'centered.php',
        'classic-4' => 'newsletter.php',
        'classic-5' => 'bold.php',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block_core/template-part', [$this, 'maybe_render_classic_footer'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Priority 20 (after the mysite-editor plugin's own default-priority
        // customize_register hook, includes/core/footer.php) so its
        // 'footer_options' section already exists by the time
        // register_customizer() tries to remove it below - see the
        // remove_section() call there for why.
        add_action('customize_register', [$this, 'register_customizer'], 20);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
    }

    public function init() {}

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-footer-style-grid, admin-
     * pages.css) - this reuses that exact CSS in the Customizer's controls
     * panel too, so the style picker is available from Customize as well
     * as Settings, matching the Header Style picker (classic_header.php).
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
        // Settings page (see menus.php's own enqueue_admin_page_assets())
        // and never prints the theme.json-derived --wp--preset--color--*
        // variables on its own - every var(--wp--preset--color--...) below
        // has an explicit fallback already, so this is mostly a safety net
        // rather than something visibly broken without it (see the same
        // fix in typography.php/buttons.php for a case where it wasn't).
        wp_add_inline_style('omega-design-admin-pages', wp_get_global_stylesheet(['variables']));
    }

    public function register_customizer($wp_customize) {
        // The mysite-editor plugin's own "Footer Options" section (a plain
        // template <select> + raw CSS/HTML textareas) duplicates - and can
        // conflict with - this theme's own Footer Style picker below,
        // which is the theme's supported way to switch footer layouts.
        // Left registered by the plugin itself (this only hides it from
        // the Customizer's UI), so it comes back on its own if that plugin
        // is ever deactivated or replaced.
        if ($wp_customize->get_section('footer_options')) {
            $wp_customize->remove_section('footer_options');
        }

        if (!$wp_customize->get_panel('omega_design_panel')) {
            $wp_customize->add_panel('omega_design_panel', [
                'title'       => __('Omega Design', 'omega-design'),
                'description' => __('Theme-specific options for Omega Design.', 'omega-design'),
                'priority'    => 30,
            ]);
        }

        $wp_customize->add_section('omega_footer_style_settings', [
            'title'       => __('Footer Style', 'omega-design'),
            'description' => __('Pick a footer layout - a hand-built Classic style, or Block Footer to keep editing the footer in the Site Editor instead. More options (tagline, copyright, the classic menu to use, ...) live under Omega Design > Settings > Footer.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 25,
        ]);

        $wp_customize->add_setting('omega_footer_mode', [
            'default'           => 'block',
            'sanitize_callback' => [$this, 'sanitize_mode'],
            'transport'         => 'refresh',
        ]);

        // WP_Customize_Control only exists once the Customizer's own class
        // files have loaded, right before 'customize_register' fires - see
        // omega_define_footer_style_control()'s own comment for why this
        // is called here rather than the class being declared at this
        // file's top level.
        omega_define_footer_style_control();

        $wp_customize->add_control(new omega_footer_style_control($wp_customize, 'omega_footer_mode', [
            'label'   => __('Footer Style', 'omega-design'),
            'section' => 'omega_footer_style_settings',
        ]));
    }

    public function sanitize_mode($value) {
        $value = sanitize_key((string) $value);
        return array_key_exists($value, self::style_choices()) ? $value : 'block';
    }

    public static function style_choices() {
        return [
            'block'     => __('Block Footer (Site Editor)', 'omega-design'),
            'classic-1' => __('Classic 1 - Simple', 'omega-design'),
            'classic-2' => __('Classic 2 - Columns', 'omega-design'),
            'classic-3' => __('Classic 3 - Centered', 'omega-design'),
            'classic-4' => __('Classic 4 - Newsletter CTA', 'omega-design'),
            'classic-5' => __('Classic 5 - Bold', 'omega-design'),
        ];
    }

    private function active_style() {
        $mode = get_theme_mod('omega_footer_mode', 'block');
        return in_array($mode, self::STYLES, true) ? $mode : '';
    }

    /**
     * A small mockup of each footer style's actual layout (single row,
     * multi-column, centered, a CTA band, dark/bold, ...) instead of the
     * plain <select> full of text labels an admin can't visually tell
     * apart from one another. Shared by both the Settings page (menus.php)
     * and the native Customizer control below. $link_callback receives
     * each style's key and must echo whatever attributes bind that
     * <input> to its context - a plain name="omega_footer_mode" for the
     * POST form, or the Customizer's own name + $this->link() for two-way
     * JS binding.
     */
    public static function render_style_cards($current, $link_callback) {
        $labels = self::style_choices();
        ?>
        <div class="omega-footer-style-grid">
            <?php foreach ($labels as $key => $label) : ?>
                <label class="omega-footer-style-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="omega-footer-style-card__input"
                    />
                    <span class="omega-footer-style-card__radio"></span>

                    <?php if ('block' === $key) : ?>
                        <span class="omega-footer-style-card__preview">
                            <span class="omega-footer-style-card__blocks-icon">
                                <span></span><span></span><span></span>
                                <span></span><span></span><span></span>
                            </span>
                        </span>
                    <?php elseif ('classic-2' === $key || 'classic-5' === $key) : ?>
                        <span class="omega-footer-style-card__preview omega-footer-style-card__preview--columns<?php echo 'classic-5' === $key ? ' omega-footer-style-card__preview--dark' : ''; ?>">
                            <span class="omega-footer-style-card__logo"></span>
                            <span class="omega-footer-style-card__columns">
                                <span class="omega-footer-style-card__col">
                                    <span class="omega-footer-style-card__col-head"></span>
                                    <span class="omega-footer-style-card__col-line"></span>
                                </span>
                                <span class="omega-footer-style-card__col">
                                    <span class="omega-footer-style-card__col-head"></span>
                                    <span class="omega-footer-style-card__col-line"></span>
                                </span>
                                <span class="omega-footer-style-card__col">
                                    <span class="omega-footer-style-card__col-head"></span>
                                    <span class="omega-footer-style-card__col-line"></span>
                                </span>
                            </span>
                        </span>
                    <?php elseif ('classic-3' === $key) : ?>
                        <span class="omega-footer-style-card__preview omega-footer-style-card__preview--centered">
                            <span class="omega-footer-style-card__logo"></span>
                            <span class="omega-footer-style-card__tagline"></span>
                            <span class="omega-footer-style-card__nav">
                                <span class="omega-footer-style-card__nav-item"></span>
                                <span class="omega-footer-style-card__nav-item"></span>
                                <span class="omega-footer-style-card__nav-item"></span>
                            </span>
                        </span>
                    <?php elseif ('classic-4' === $key) : ?>
                        <span class="omega-footer-style-card__preview">
                            <span class="omega-footer-style-card__cta-band">
                                <span class="omega-footer-style-card__tagline omega-footer-style-card__tagline--light"></span>
                                <span class="omega-footer-style-card__cta-btn"></span>
                            </span>
                            <span class="omega-footer-style-card__row">
                                <span class="omega-footer-style-card__logo"></span>
                                <span class="omega-footer-style-card__nav">
                                    <span class="omega-footer-style-card__nav-item"></span>
                                    <span class="omega-footer-style-card__nav-item"></span>
                                </span>
                            </span>
                        </span>
                    <?php else : /* classic-1: Simple - one row, logo left, nav + copyright below. */ ?>
                        <span class="omega-footer-style-card__preview">
                            <span class="omega-footer-style-card__row">
                                <span class="omega-footer-style-card__logo"></span>
                                <span class="omega-footer-style-card__nav">
                                    <span class="omega-footer-style-card__nav-item"></span>
                                    <span class="omega-footer-style-card__nav-item"></span>
                                </span>
                            </span>
                            <span class="omega-footer-style-card__copyright"></span>
                        </span>
                    <?php endif; ?>

                    <span class="omega-footer-style-card__title"><?php echo esc_html($label); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * CSS only - unlike the header, nothing here needs to collapse behind
     * a tap (no dropdowns, no hamburger): columns just stack vertically
     * on mobile via pure CSS, so there's no JS-driven toggle to get wrong.
     */
    public function enqueue_assets() {
        if ('' === $this->active_style()) {
            return;
        }

        $css_path = get_template_directory() . '/assets/css/classic-footer.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-classic-footer',
                get_template_directory_uri() . '/assets/css/classic-footer.css',
                [],
                filemtime($css_path)
            );
        }
    }

    /**
     * "{year}" is the only placeholder - replaced with the current year at
     * render time, never baked into the stored setting, so the copyright
     * line never goes stale. Falls back to a fully generic default built
     * from this site's own bloginfo(), not any fixed site name.
     */
    private function copyright_html() {
        $template = get_theme_mod('omega_footer_copyright', '');
        if ('' === trim(wp_strip_all_tags($template))) {
            $template = sprintf(
                /* translators: %s: site name */
                __('&copy; {year} %s. All rights reserved.', 'omega-design'),
                get_bloginfo('name')
            );
        }
        return str_replace('{year}', gmdate('Y'), $template);
    }

    /**
     * Replaces the whole "footer" template part's rendered content - same
     * mechanism and same reasoning as classic_header.php's own
     * maybe_render_classic_header(), including the "Hide footer" check
     * running first regardless of which mode is active.
     */
    public function maybe_render_classic_footer($block_content, $block) {
        if ('footer' !== ($block['attrs']['slug'] ?? '')) {
            return $block_content;
        }

        if (footer_visibility::is_hidden_for_current_page()) {
            return '';
        }

        $style = $this->active_style();
        if ('' === $style) {
            return $block_content;
        }

        $template_file = self::TEMPLATE_FILES[$style] ?? '';
        $template_path = get_template_directory() . '/template-parts/classic-footer/' . $template_file;
        if ('' === $template_file || !file_exists($template_path)) {
            return $block_content;
        }

        $menu_id  = (int) get_theme_mod('omega_classic_footer_menu_id', 0);
        $nav_html = '';
        if ($menu_id && wp_get_nav_menu_object($menu_id)) {
            $nav_html = (string) wp_nav_menu([
                'menu'        => $menu_id,
                'echo'        => false,
                'container'   => false,
                'fallback_cb' => false,
            ]);
        }

        $tagline = get_theme_mod('omega_footer_tagline', '');
        if ('' === trim(wp_strip_all_tags($tagline))) {
            $tagline = get_bloginfo('description');
        }

        $cta_label = get_theme_mod('omega_footer_cta_label', '');
        $cta_url   = get_theme_mod('omega_footer_cta_url', '');
        $copyright = $this->copyright_html();

        ob_start();
        include $template_path;
        return (string) ob_get_clean();
    }
}

/**
 * Declared lazily (called from register_customizer(), which only ever runs
 * on 'customize_register') rather than at this file's top level, since
 * WP_Customize_Control doesn't exist yet when this file is first required
 * during theme bootstrap - the same fatal-error trap the header style
 * control hit before this pattern was established (see
 * includes/customizer/classic_header.php's own version of this function).
 */
function omega_define_footer_style_control() {
    if (class_exists(__NAMESPACE__ . '\\omega_footer_style_control')) {
        return;
    }

    class omega_footer_style_control extends \WP_Customize_Control {
        public $type = 'omega_footer_style';

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
            classic_footer::render_style_cards(
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

classic_footer::get_instance();
