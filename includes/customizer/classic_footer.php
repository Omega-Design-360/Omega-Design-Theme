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

use OmegaDesign\traits\assets;
use OmegaDesign\traits\classic_template_part;
use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class classic_footer {

    use singleton;
    use assets;
    use customizer_section;
    use classic_template_part;

    const STYLES = ['classic-1', 'classic-2', 'classic-3', 'classic-4', 'classic-5'];

    const TEMPLATE_FILES = [
        'classic-1' => 'simple.php',
        'classic-2' => 'columns.php',
        'classic-3' => 'centered.php',
        'classic-4' => 'newsletter.php',
        'classic-5' => 'bold.php',
    ];

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

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-footer-style-grid, admin-
     * pages.css) - this reuses that exact CSS in the Customizer's controls
     * panel too, so the style picker is available from Customize as well
     * as Settings, matching the Header Style picker (classic_header.php).
     */
    public function enqueue_control_assets() {
        self::enqueue_admin_pages_style(true);
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

        self::ensure_design_panel($wp_customize);

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

        self::add_card_control($wp_customize, 'omega_footer_mode', 'omega_footer_style', [__CLASS__, 'render_style_cards'], [
            'label'   => __('Footer Style', 'omega-design'),
            'section' => 'omega_footer_style_settings',
        ]);
    }

    public function sanitize_mode($value) {
        return self::sanitize_key_choice($value, self::style_choices(), 'block');
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
        return self::classic_style_or_empty(get_theme_mod('omega_footer_mode', 'block'));
    }

    /**
     * A small mockup of each footer style's actual layout (single row,
     * multi-column, centered, a CTA band, dark/bold, ...) instead of the
     * plain <select> full of text labels an admin can't visually tell
     * apart from one another. Shared by both the Settings page (menus.php)
     * and the native Customizer control.
     */
    public static function render_style_cards($current, $link_callback) {
        self::render_radio_card_grid('omega-footer-style', self::style_choices(), $current, $link_callback, [__CLASS__, 'render_style_card_body']);
    }

    public static function render_style_card_body($key, $label) {
        self::render_card_radio_dot('omega-footer-style');
        self::render_style_preview($key);
        ?>
        <span class="omega-footer-style-card__title"><?php echo esc_html($label); ?></span>
        <?php
    }

    private static function render_style_preview($key) {
        switch ($key) {
            case 'block':
                self::render_block_preview();
                break;
            case 'classic-2':
            case 'classic-5':
                self::render_columns_preview('classic-5' === $key);
                break;
            case 'classic-3':
                self::render_centered_preview();
                break;
            case 'classic-4':
                self::render_cta_preview();
                break;
            default:
                // classic-1: Simple - one row, logo left, nav + copyright below.
                self::render_simple_preview();
        }
    }

    private static function render_block_preview() {
        ?>
        <span class="omega-footer-style-card__preview">
            <span class="omega-footer-style-card__blocks-icon">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </span>
        </span>
        <?php
    }

    private static function render_columns_preview($dark) {
        ?>
        <span class="omega-footer-style-card__preview omega-footer-style-card__preview--columns<?php echo $dark ? ' omega-footer-style-card__preview--dark' : ''; ?>">
            <span class="omega-footer-style-card__logo"></span>
            <span class="omega-footer-style-card__columns">
                <?php for ($i = 0; $i < 3; $i++) : ?>
                    <span class="omega-footer-style-card__col">
                        <span class="omega-footer-style-card__col-head"></span>
                        <span class="omega-footer-style-card__col-line"></span>
                    </span>
                <?php endfor; ?>
            </span>
        </span>
        <?php
    }

    private static function render_centered_preview() {
        ?>
        <span class="omega-footer-style-card__preview omega-footer-style-card__preview--centered">
            <span class="omega-footer-style-card__logo"></span>
            <span class="omega-footer-style-card__tagline"></span>
            <?php self::render_nav_preview(3); ?>
        </span>
        <?php
    }

    private static function render_cta_preview() {
        ?>
        <span class="omega-footer-style-card__preview">
            <span class="omega-footer-style-card__cta-band">
                <span class="omega-footer-style-card__tagline omega-footer-style-card__tagline--light"></span>
                <span class="omega-footer-style-card__cta-btn"></span>
            </span>
            <?php self::render_logo_nav_row(); ?>
        </span>
        <?php
    }

    private static function render_simple_preview() {
        ?>
        <span class="omega-footer-style-card__preview">
            <?php self::render_logo_nav_row(); ?>
            <span class="omega-footer-style-card__copyright"></span>
        </span>
        <?php
    }

    private static function render_logo_nav_row() {
        ?>
        <span class="omega-footer-style-card__row">
            <span class="omega-footer-style-card__logo"></span>
            <?php self::render_nav_preview(2); ?>
        </span>
        <?php
    }

    private static function render_nav_preview($items) {
        ?>
        <span class="omega-footer-style-card__nav">
            <?php echo str_repeat('<span class="omega-footer-style-card__nav-item"></span>' . "\n", $items); ?>
        </span>
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

        self::enqueue_style('omega-design-classic-footer', 'css/classic-footer.css');
    }

    /**
     * "{year}" is the only placeholder - replaced with the current year at
     * render time, never baked into the stored setting, so the copyright
     * line never goes stale. Falls back to a fully generic default built
     * from this site's own bloginfo(), not any fixed site name.
     */
    private function copyright_html() {
        $template = self::text_or_fallback(
            get_theme_mod('omega_footer_copyright', ''),
            sprintf(
                /* translators: %s: site name */
                __('&copy; {year} %s. All rights reserved.', 'omega-design'),
                get_bloginfo('name')
            )
        );

        return str_replace('{year}', gmdate('Y'), $template);
    }

    /**
     * Replaces the whole "footer" template part's rendered content - same
     * mechanism and same reasoning as classic_header.php's own
     * maybe_render_classic_header(), including the "Hide footer" check
     * running first regardless of which mode is active.
     */
    public function maybe_render_classic_footer($block_content, $block) {
        if (!self::is_template_part($block, 'footer')) {
            return $block_content;
        }

        if (footer_visibility::is_hidden_for_current_page()) {
            return '';
        }

        $style = $this->active_style();
        if ('' === $style) {
            return $block_content;
        }

        $template_path = self::style_template_path('classic-footer', $style);
        if ('' === $template_path) {
            return $block_content;
        }

        return self::render_template_file($template_path, $this->template_vars());
    }

    /**
     * Variables every classic footer template file reads.
     */
    private function template_vars() {
        return [
            'nav_html'  => self::classic_menu_html('omega_classic_footer_menu_id'),
            'tagline'   => self::text_or_fallback(get_theme_mod('omega_footer_tagline', ''), get_bloginfo('description')),
            'cta_label' => get_theme_mod('omega_footer_cta_label', ''),
            'cta_url'   => get_theme_mod('omega_footer_cta_url', ''),
            'copyright' => $this->copyright_html(),
        ];
    }
}

classic_footer::get_instance();
