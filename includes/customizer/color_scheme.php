<?php
/**
 * Site-wide Color Scheme picker.
 *
 * theme.json ships one default palette ("Classic Green"). This lets an
 * admin swap every themed page - header, footer, buttons, the Shop
 * archive, every landing page pattern - to one of 5 predefined palettes at
 * once, by overriding the same CSS custom properties theme.json itself
 * defines (--wp--preset--color--primary etc.) via an inline <style> in
 * wp_head. Nothing else needs to change: every block already colored via
 * backgroundColor/textColor:"primary" (or any other preset slug) picks up
 * the new value automatically, the same way a10 Global Styles change would,
 * without touching a single pattern file.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class color_scheme {

    const THEME_MOD     = 'omega_color_scheme';
    const DEFAULT_SCHEME = 'green';

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
        add_action('wp_head', [$this, 'output_styles']);
        add_action('admin_head', [$this, 'output_styles']);
    }

    /**
     * The theme's own Settings page (menus.php) draws this same swatch-card
     * grid with its own markup/CSS (.omega-scheme-grid, admin-pages.css) -
     * this reuses that exact CSS in the Customizer's controls panel too, so
     * the native "radio" control (plain text + a bare radio dot, no color
     * preview at all) isn't the only place this setting can be changed from.
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

    public function init() {}

    /**
     * Each scheme provides light-mode values for every color slug
     * theme.json's own palette defines, plus a "-dark" counterpart for
     * color_mode.php's dark mode. background/page-background/heading-dark/
     * body-text(-dark)/border(-dark)/success/warning/danger(-dark) are
     * deliberately identical across all 5 - those are neutral/semantic
     * colors, not part of a scheme's brand identity, so only the brand-
     * facing slugs (primary, secondary, accent, surface, button-background)
     * actually vary between schemes.
     */
    public function get_schemes() {
        return [
            'green' => [
                'label' => __('Classic Green', 'omega-design'),
                'tagline' => __('Fresh. Natural. Trusted.', 'omega-design'),
                'swatch' => ['#1FBB00', '#051A00', '#B35B00'],
                'light' => [
                    'primary' => '#1FBB00', 'secondary' => '#051A00', 'accent' => '#B35B00',
                    'heading' => '#041200', 'surface' => '#F5F7F2', 'page-background' => '#F5F7F2',
                    'button-background' => '#0F5900',
                ],
                'dark' => [
                    'primary' => '#39E75F', 'secondary' => '#1E3A12', 'accent' => '#E2933D',
                    'surface' => '#152B0C', 'background' => '#0E1A08', 'page-background' => '#0E1A08',
                    'button-background' => '#1B7A32',
                ],
            ],
            'violet' => [
                'label' => __('Violet Glass', 'omega-design'),
                'tagline' => __('Modern. Creative. Bold.', 'omega-design'),
                'swatch' => ['#7C3AED', '#1E1B4B', '#22D3EE'],
                'light' => [
                    'primary' => '#7C3AED', 'secondary' => '#1E1B4B', 'accent' => '#22D3EE',
                    'heading' => '#1E1B4B', 'surface' => '#F5F3FF', 'page-background' => '#F5F3FF',
                    'button-background' => '#7C3AED',
                ],
                'dark' => [
                    'primary' => '#A78BFA', 'secondary' => '#3730A3', 'accent' => '#67E8F9',
                    'surface' => '#1C1730', 'background' => '#14102B', 'page-background' => '#14102B',
                    'button-background' => '#8B5CF6',
                ],
            ],
            'rose-gold' => [
                'label' => __('Rose Gold', 'omega-design'),
                'tagline' => __('Elegant. Warm. Chic.', 'omega-design'),
                'swatch' => ['#B76E79', '#1C1C1C', '#D4AF37'],
                'light' => [
                    'primary' => '#B76E79', 'secondary' => '#1C1C1C', 'accent' => '#D4AF37',
                    'heading' => '#1C1C1C', 'surface' => '#F7F3F0', 'page-background' => '#F7F3F0',
                    'button-background' => '#B76E79',
                ],
                'dark' => [
                    'primary' => '#D99BA3', 'secondary' => '#3A3A3A', 'accent' => '#E6C766',
                    'surface' => '#242121', 'background' => '#181616', 'page-background' => '#181616',
                    'button-background' => '#A85D68',
                ],
            ],
            'ocean' => [
                'label' => __('Ocean Blue', 'omega-design'),
                'tagline' => __('Calm. Professional. Clean.', 'omega-design'),
                'swatch' => ['#2563EB', '#0C1B33', '#06B6D4'],
                'light' => [
                    'primary' => '#2563EB', 'secondary' => '#0C1B33', 'accent' => '#06B6D4',
                    'heading' => '#0C1B33', 'surface' => '#F0F6FF', 'page-background' => '#F0F6FF',
                    'button-background' => '#2563EB',
                ],
                'dark' => [
                    'primary' => '#60A5FA', 'secondary' => '#1E3A5F', 'accent' => '#22D3EE',
                    'surface' => '#132238', 'background' => '#0A1526', 'page-background' => '#0A1526',
                    'button-background' => '#3B82F6',
                ],
            ],
            'sunset' => [
                'label' => __('Sunset Coral', 'omega-design'),
                'tagline' => __('Vibrant. Energetic. Warm.', 'omega-design'),
                'swatch' => ['#F97350', '#3B1F2B', '#FBBF24'],
                'light' => [
                    'primary' => '#F97350', 'secondary' => '#3B1F2B', 'accent' => '#FBBF24',
                    'heading' => '#3B1F2B', 'surface' => '#FFF6F0', 'page-background' => '#FFF6F0',
                    'button-background' => '#F97350',
                ],
                'dark' => [
                    'primary' => '#FB9376', 'secondary' => '#5A3140', 'accent' => '#FCD34D',
                    'surface' => '#2A1A20', 'background' => '#1C1216', 'page-background' => '#1C1216',
                    'button-background' => '#E55F3D',
                ],
            ],
        ];
    }

    public function get_scheme_key() {
        $key = get_theme_mod(self::THEME_MOD, self::DEFAULT_SCHEME);
        return isset($this->get_schemes()[$key]) ? $key : self::DEFAULT_SCHEME;
    }

    public function register_customizer($wp_customize) {
        if (!$wp_customize->get_panel('omega_design_panel')) {
            $wp_customize->add_panel('omega_design_panel', [
                'title'       => __('Omega Design', 'omega-design'),
                'description' => __('Theme-specific options for Omega Design.', 'omega-design'),
                'priority'    => 30,
            ]);
        }

        $wp_customize->add_section('omega_color_scheme_settings', [
            'title'       => __('Color Scheme', 'omega-design'),
            'description' => __('Pick the brand color palette used site-wide - header, footer, buttons, and every page. This overrides the primary/secondary/accent colors Global Styles would otherwise use.', 'omega-design'),
            'panel'       => 'omega_design_panel',
            'priority'    => 5,
        ]);

        $wp_customize->add_setting(self::THEME_MOD, [
            'default'           => self::DEFAULT_SCHEME,
            'sanitize_callback' => [$this, 'sanitize_scheme'],
            'transport'         => 'refresh',
        ]);

        // WP_Customize_Control only exists once the Customizer's own class
        // files have loaded, which happens right before 'customize_register'
        // fires - defining omega_color_scheme_control at the bottom of this
        // file (top-level) ran too early (this file is required during
        // theme bootstrap, well before that), so the class was silently
        // never declared and `new omega_color_scheme_control(...)` fataled.
        // Defining it here instead, inside the same hook callback, only
        // ever runs once WP_Customize_Control is guaranteed to exist.
        omega_define_color_scheme_control();

        $wp_customize->add_control(new omega_color_scheme_control($wp_customize, self::THEME_MOD, [
            'label'   => __('Color Scheme', 'omega-design'),
            'section' => 'omega_color_scheme_settings',
            'schemes' => $this->get_schemes(),
        ]));
    }

    public function sanitize_scheme($value) {
        return isset($this->get_schemes()[$value]) ? $value : self::DEFAULT_SCHEME;
    }

    /**
     * Prints the active scheme's light values as :root custom properties
     * (unconditionally, even for "green" - explicit rather than relying on
     * theme.json's own defaults matching, so a future theme.json edit can't
     * silently drift the "green" scheme out of sync with this list), plus
     * the same for dark mode scoped under the site's existing
     * .omega-color-mode-dark / prefers-color-scheme handling (see
     * color_mode.php).
     */
    public function output_styles() {
        $scheme = $this->get_schemes()[$this->get_scheme_key()];
        $css = ':root{';
        foreach ($scheme['light'] as $slug => $hex) {
            $css .= '--wp--preset--color--' . $slug . ':' . $hex . ';';
        }
        $css .= '}';

        if (!empty($scheme['dark'])) {
            $dark_vars = '';
            foreach ($scheme['dark'] as $slug => $hex) {
                $dark_vars .= '--wp--preset--color--' . $slug . ':' . $hex . ';';
            }
            // CSS custom properties inherit through descendants regardless
            // of which element sets them, so setting these on body (like
            // color_mode.php's own dark-mode overrides already do) reaches
            // every var(--wp--preset--color--...) use on the page just as
            // well as setting them on :root would.
            $css .= '@media (prefers-color-scheme: dark){body.omega-color-mode-auto{' . $dark_vars . '}}';
            $css .= 'body.omega-color-mode-dark{' . $dark_vars . '}';
        }

        echo '<style id="omega-color-scheme">' . $css . '</style>' . "\n";
    }

    /**
     * Shared markup for both the Settings page (menus.php) and the native
     * Customizer control below - a richer "showcase" card per scheme: a
     * gradient banner with the scheme name/tagline, then a row of labelled
     * swatches (with hex codes) for the roles that actually vary between
     * schemes. $link_callback receives each scheme's key and must echo
     * whatever attributes bind that <input> to its context - a plain
     * name="omega_color_scheme" for the POST form, or the Customizer's own
     * name + $this->link() for two-way JS binding.
     */
    public static function render_scheme_cards($schemes, $current, $link_callback) {
        $rows = [
            'primary'         => __('Primary', 'omega-design'),
            'secondary'       => __('Secondary', 'omega-design'),
            'accent'          => __('Accent', 'omega-design'),
            'page-background' => __('Background', 'omega-design'),
            'heading'         => __('Text', 'omega-design'),
        ];
        ?>
        <div class="omega-scheme-grid">
            <?php foreach ($schemes as $key => $scheme) : ?>
                <label class="omega-scheme-card">
                    <input
                        type="radio"
                        <?php call_user_func($link_callback, $key); ?>
                        value="<?php echo esc_attr($key); ?>"
                        <?php checked($current, $key); ?>
                        class="omega-scheme-card__input"
                    />
                    <span
                        class="omega-scheme-card__banner"
                        style="background: linear-gradient(135deg, <?php echo esc_attr($scheme['light']['primary']); ?> 0%, <?php echo esc_attr($scheme['light']['secondary']); ?> 100%);"
                    >
                        <span class="omega-scheme-card__name"><?php echo esc_html($scheme['label']); ?></span>
                        <?php if (!empty($scheme['tagline'])) : ?>
                            <span class="omega-scheme-card__tagline"><?php echo esc_html($scheme['tagline']); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="omega-scheme-card__swatches">
                        <?php foreach ($rows as $slug => $row_label) :
                            $hex = isset($scheme['light'][$slug]) ? $scheme['light'][$slug] : '';
                        ?>
                            <span class="omega-scheme-card__swatch">
                                <span class="omega-scheme-card__circle" style="background:<?php echo esc_attr($hex); ?>"></span>
                                <span class="omega-scheme-card__swatch-label"><?php echo esc_html($row_label); ?></span>
                                <span class="omega-scheme-card__hex"><?php echo esc_html($hex); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

/**
 * Renders the same .omega-scheme-grid / .omega-scheme-card swatch cards
 * the theme's own Settings page uses (see menus.php's
 * render_color_scheme_form() and their CSS in admin-pages.css, enqueued
 * for the controls panel by color_scheme::enqueue_control_assets()) -
 * WP_Customize_Control's own "radio" type only ever prints a bare radio
 * dot next to a text label, with no way to show a color preview at all.
 *
 * Declared lazily (called from register_customizer(), which only ever
 * runs on 'customize_register') rather than at this file's top level,
 * since WP_Customize_Control doesn't exist yet when this file is first
 * required during theme bootstrap.
 */
function omega_define_color_scheme_control() {
    if (class_exists(__NAMESPACE__ . '\\omega_color_scheme_control')) {
        return;
    }

    class omega_color_scheme_control extends \WP_Customize_Control {
        public $type = 'omega_color_scheme';
        public $schemes = [];

        public function render_content() {
            if (empty($this->schemes)) {
                return;
            }
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
            \OmegaDesign\customizer\color_scheme::render_scheme_cards(
                $this->schemes,
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
