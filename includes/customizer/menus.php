<?php
/**
 * Left Sidebar Admin Menu, Dashboard & Settings Pages
 *
 * @package OmegaDesign\admin
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class menus {

    private static $instance = null;

    private $dashboard_hook = null;
    private $settings_hook  = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_bar_menu', [$this, 'register_admin_bar_menu'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_page_assets']);
        add_action('admin_post_omega_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_notices', [$this, 'maybe_show_saved_notice']);
        add_filter('admin_body_class', [$this, 'add_admin_body_class']);
    }

    /**
     * color-mode.css keys its dark-mode overrides off body.omega-color-mode-*,
     * matching the front end's own body_class() output. wp-admin's <body>
     * never gets that class on its own, so without this the dark override
     * rules would simply never match here, even though the wrapper div and
     * the underlying --wp--preset--color--* variables are otherwise correct.
     */
    public function add_admin_body_class($classes) {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, [$this->dashboard_hook, $this->settings_hook], true)) {
            return $classes;
        }
        return $classes . ' ' . $this->color_mode_class();
    }

    public function init() {}

    public function register_admin_menu() {
        $this->dashboard_hook = add_menu_page(
            __('Omega Design', 'omega-design'),
            __('Omega Design', 'omega-design'),
            'manage_options',
            'omega-dashboard',
            [$this, 'dashboard_page'],
            'dashicons-layout',
            2
        );

        add_submenu_page(
            'omega-dashboard',
            __('Dashboard', 'omega-design'),
            __('Dashboard', 'omega-design'),
            'manage_options',
            'omega-dashboard',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'omega-dashboard',
            __('Site Builder', 'omega-design'),
            __('Site Builder', 'omega-design'),
            'manage_options',
            'site-editor.php'
        );

        $this->settings_hook = add_submenu_page(
            'omega-dashboard',
            __('Settings', 'omega-design'),
            __('Settings', 'omega-design'),
            'manage_options',
            'omega-settings',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'omega-dashboard',
            __('Appearance', 'omega-design'),
            __('Appearance', 'omega-design'),
            'manage_options',
            'customize.php'
        );

        add_submenu_page(
            'omega-dashboard',
            __('Menus', 'omega-design'),
            __('Menus', 'omega-design'),
            'manage_options',
            'nav-menus.php'
        );

        add_submenu_page(
            'omega-dashboard',
            __('Mega Menus', 'omega-design'),
            __('Mega Menus', 'omega-design'),
            'manage_options',
            'edit.php?post_type=mega_menu'
        );

        add_submenu_page(
            'omega-dashboard',
            __('Widgets', 'omega-design'),
            __('Widgets', 'omega-design'),
            'manage_options',
            'widgets.php'
        );
    }

    /**
     * Top Admin Bar Menu
     */
    public function register_admin_bar_menu($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node([
            'id'    => 'omega-design',
            'title' => 'Omega Design',
            'href'  => admin_url('admin.php?page=omega-dashboard'),
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'omega-site-builder',
            'parent' => 'omega-design',
            'title'  => 'Site Builder',
            'href'   => admin_url('site-editor.php'),
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'omega-settings',
            'parent' => 'omega-design',
            'title'  => 'Settings',
            'href'   => admin_url('admin.php?page=omega-settings'),
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'omega-appearance',
            'parent' => 'omega-design',
            'title'  => 'Appearance',
            'href'   => admin_url('customize.php'),
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'omega-menus',
            'parent' => 'omega-design',
            'title'  => 'Menus',
            'href'   => admin_url('nav-menus.php'),
        ]);

        $wp_admin_bar->add_node([
            'id'     => 'omega-widgets',
            'parent' => 'omega-design',
            'title'  => 'Widgets',
            'href'   => admin_url('widgets.php'),
        ]);
    }

    public function enqueue_admin_page_assets($hook) {
        if (!in_array($hook, [$this->dashboard_hook, $this->settings_hook], true)) {
            return;
        }

        $admin_pages_path = OMEGA_DESIGN_ASSETS . '/css/admin-pages.css';
        wp_enqueue_style(
            'omega-design-admin-pages',
            OMEGA_DESIGN_CSS_URI . '/admin-pages.css',
            [],
            file_exists($admin_pages_path) ? filemtime($admin_pages_path) : OMEGA_DESIGN_VERSION
        );

        // This admin screen is a plain wp-admin page, not the front end or the
        // block editor - WordPress never prints the theme.json-derived
        // --wp--preset--color--* variables here on its own. Without this,
        // admin-pages.css's var(--wp--preset--color--...) references would
        // all resolve to nothing.
        wp_add_inline_style('omega-design-admin-pages', wp_get_global_stylesheet(['variables']));

        // Same color-mode.css used on the front end, so this page can carry
        // the same omega-color-mode-{mode} class and pick up the identical
        // light/dark values - one palette, one switch, everywhere.
        $color_mode_path = OMEGA_DESIGN_ASSETS . '/css/color-mode.css';
        wp_enqueue_style(
            'omega-design-color-mode',
            OMEGA_DESIGN_CSS_URI . '/color-mode.css',
            ['omega-design-admin-pages'],
            file_exists($color_mode_path) ? filemtime($color_mode_path) : OMEGA_DESIGN_VERSION
        );

        if ($hook === $this->dashboard_hook) {
            wp_enqueue_media();
            $logo_js_path = OMEGA_DESIGN_ASSETS . '/js/admin-logo.js';
            wp_enqueue_script(
                'omega-design-admin-logo',
                OMEGA_DESIGN_JS_URI . '/admin-logo.js',
                ['media-editor'],
                file_exists($logo_js_path) ? filemtime($logo_js_path) : OMEGA_DESIGN_VERSION,
                true
            );
        }
    }

    /**
     * Resolves the current site-wide color mode for use as a class on this
     * admin page's own wrapper, so it renders with the exact same light/dark
     * values as the front end instead of a separate admin-only palette.
     */
    private function color_mode_class() {
        if (class_exists('\OmegaDesign\customizer\color_mode')) {
            return 'omega-color-mode-' . color_mode::get_instance()->get_mode();
        }
        return 'omega-color-mode-auto';
    }

    /**
     * Handles saves from both the Dashboard's quick color-mode toggle and
     * the full Settings page. Each form only includes a hidden
     * "omega_section_*" marker for the sections it actually contains, so
     * this one handler can't accidentally wipe out settings a given form
     * never showed (e.g. the dashboard's mode-only form won't touch the
     * sidebar settings).
     */
    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'omega-design'));
        }

        if (isset($_POST['omega_section_sidebar'])) {
            $nonce_field = 'omega_nonce_sidebar';
        } elseif (isset($_POST['omega_section_logo'])) {
            $nonce_field = 'omega_nonce_logo';
        } elseif (isset($_POST['omega_section_header_nav'])) {
            $nonce_field = 'omega_nonce_header_nav';
        } elseif (isset($_POST['omega_section_announcement'])) {
            $nonce_field = 'omega_nonce_announcement';
        } elseif (isset($_POST['omega_section_footer'])) {
            $nonce_field = 'omega_nonce_footer';
        } else {
            $nonce_field = 'omega_nonce_mode';
        }
        check_admin_referer('omega_save_settings', $nonce_field);

        if (isset($_POST['omega_section_color_mode'])) {
            $mode = isset($_POST['omega_color_mode']) ? wp_unslash($_POST['omega_color_mode']) : 'auto';
            set_theme_mod('omega_color_mode', color_mode::get_instance()->sanitize_mode($mode));
        }

        if (isset($_POST['omega_section_sidebar'])) {
            set_theme_mod('omega_enable_sidebar', !empty($_POST['omega_enable_sidebar']));

            $position = isset($_POST['omega_sidebar_position']) ? wp_unslash($_POST['omega_sidebar_position']) : 'right';
            set_theme_mod('omega_sidebar_position', sidebar::get_instance()->sanitize_position($position));

            $width = isset($_POST['omega_sidebar_width']) ? absint($_POST['omega_sidebar_width']) : 30;
            set_theme_mod('omega_sidebar_width', max(20, min(50, $width)));
        }

        if (isset($_POST['omega_section_logo'])) {
            $attachment_id = isset($_POST['omega_custom_logo']) ? absint($_POST['omega_custom_logo']) : 0;

            if ($attachment_id > 0 && 'attachment' === get_post_type($attachment_id)) {
                set_theme_mod('custom_logo', $attachment_id);
            } else {
                remove_theme_mod('custom_logo');
            }

            $dark_attachment_id = isset($_POST['omega_custom_logo_dark']) ? absint($_POST['omega_custom_logo_dark']) : 0;

            if ($dark_attachment_id > 0 && 'attachment' === get_post_type($dark_attachment_id)) {
                set_theme_mod('omega_custom_logo_dark', $dark_attachment_id);
            } else {
                remove_theme_mod('omega_custom_logo_dark');
            }
        }

        if (isset($_POST['omega_section_header_nav'])) {
            $mode = isset($_POST['omega_nav_mode']) ? wp_unslash($_POST['omega_nav_mode']) : 'block';
            if (!array_key_exists($mode, classic_header::style_choices())) {
                $mode = 'block';
            }
            set_theme_mod('omega_nav_mode', $mode);

            $menu_id = isset($_POST['omega_classic_menu_id']) ? absint($_POST['omega_classic_menu_id']) : 0;
            if ($menu_id && wp_get_nav_menu_object($menu_id)) {
                set_theme_mod('omega_classic_menu_id', $menu_id);
            } else {
                set_theme_mod('omega_classic_menu_id', 0);
            }

            set_theme_mod('omega_header_sticky', !empty($_POST['omega_header_sticky']));

            // All four below are opt-in overrides only - left blank/default,
            // classic_header.php emits no inline style for them at all and
            // theme.json keeps deciding, same as everywhere else in this
            // theme. sanitize_text_field (not sanitize_hex_color) since a
            // color field may hold a var(--wp--preset--color--*) reference,
            // not just a hex value - same pattern the Announcement Bar's
            // own color fields already use.
            $bg = isset($_POST['omega_header_bg_color']) ? sanitize_text_field(wp_unslash($_POST['omega_header_bg_color'])) : '';
            set_theme_mod('omega_header_bg_color', $bg);

            $text_color = isset($_POST['omega_header_text_color']) ? sanitize_text_field(wp_unslash($_POST['omega_header_text_color'])) : '';
            set_theme_mod('omega_header_text_color', $text_color);

            $font_family = isset($_POST['omega_header_font_family']) ? sanitize_text_field(wp_unslash($_POST['omega_header_font_family'])) : '';
            set_theme_mod('omega_header_font_family', $font_family);

            $font_size = isset($_POST['omega_header_font_size']) ? wp_unslash($_POST['omega_header_font_size']) : 'default';
            set_theme_mod('omega_header_font_size', classic_header::sanitize_choice($font_size, classic_header::FONT_SIZE_CHOICES, 'default'));

            $height = isset($_POST['omega_header_height']) ? wp_unslash($_POST['omega_header_height']) : 'default';
            set_theme_mod('omega_header_height', classic_header::sanitize_choice($height, classic_header::HEIGHT_CHOICES, 'default'));
        }

        if (isset($_POST['omega_section_announcement'])) {
            set_theme_mod('omega_announcement_enabled', !empty($_POST['omega_announcement_enabled']));
            set_theme_mod('omega_announcement_dismissible', !empty($_POST['omega_announcement_dismissible']));

            // Trusted admin-authored HTML (phone/social links need real
            // markup) - same trust model as the Mega Menu's own Custom CSS
            // field, not stripped down to plain text.
            $content = isset($_POST['omega_announcement_content']) ? wp_unslash($_POST['omega_announcement_content']) : '';
            set_theme_mod('omega_announcement_content', $content);

            $bg = isset($_POST['omega_announcement_bg']) ? sanitize_text_field(wp_unslash($_POST['omega_announcement_bg'])) : '';
            set_theme_mod('omega_announcement_bg', $bg);

            $text_color = isset($_POST['omega_announcement_text_color']) ? sanitize_text_field(wp_unslash($_POST['omega_announcement_text_color'])) : '';
            set_theme_mod('omega_announcement_text_color', $text_color);
        }

        if (isset($_POST['omega_section_footer'])) {
            $footer_mode = isset($_POST['omega_footer_mode']) ? wp_unslash($_POST['omega_footer_mode']) : 'block';
            if (!array_key_exists($footer_mode, classic_footer::style_choices())) {
                $footer_mode = 'block';
            }
            set_theme_mod('omega_footer_mode', $footer_mode);

            $footer_menu_id = isset($_POST['omega_classic_footer_menu_id']) ? absint($_POST['omega_classic_footer_menu_id']) : 0;
            if ($footer_menu_id && wp_get_nav_menu_object($footer_menu_id)) {
                set_theme_mod('omega_classic_footer_menu_id', $footer_menu_id);
            } else {
                set_theme_mod('omega_classic_footer_menu_id', 0);
            }

            $tagline = isset($_POST['omega_footer_tagline']) ? sanitize_text_field(wp_unslash($_POST['omega_footer_tagline'])) : '';
            set_theme_mod('omega_footer_tagline', $tagline);

            // {year} is a literal token replaced at render time - see
            // classic_footer.php's copyright_html() - not sanitized away
            // since it isn't HTML.
            $copyright = isset($_POST['omega_footer_copyright']) ? sanitize_text_field(wp_unslash($_POST['omega_footer_copyright'])) : '';
            set_theme_mod('omega_footer_copyright', $copyright);

            $cta_label = isset($_POST['omega_footer_cta_label']) ? sanitize_text_field(wp_unslash($_POST['omega_footer_cta_label'])) : '';
            set_theme_mod('omega_footer_cta_label', $cta_label);

            $cta_url = isset($_POST['omega_footer_cta_url']) ? esc_url_raw(wp_unslash($_POST['omega_footer_cta_url'])) : '';
            set_theme_mod('omega_footer_cta_url', $cta_url);
        }

        $redirect_to = isset($_POST['omega_redirect_to'])
            ? esc_url_raw(wp_unslash($_POST['omega_redirect_to']))
            : admin_url('admin.php?page=omega-dashboard');

        wp_safe_redirect(add_query_arg('omega_saved', '1', $redirect_to));
        exit;
    }

    public function maybe_show_saved_notice() {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->id, [$this->dashboard_hook, $this->settings_hook], true)) {
            return;
        }

        if (empty($_GET['omega_saved'])) {
            return;
        }
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Omega Design settings saved.', 'omega-design'); ?></p>
        </div>
        <?php
    }

    /**
     * Environment / feature status used by the dashboard status cards.
     */
    private function get_status_data() {
        global $wp_version;

        $megamenu_counts   = post_type_exists('mega_menu') ? wp_count_posts('mega_menu') : null;
        $megamenu_count    = $megamenu_counts && isset($megamenu_counts->publish) ? (int) $megamenu_counts->publish : 0;

        $sidebars_widgets = wp_get_sidebars_widgets();
        $widget_count     = isset($sidebars_widgets['omega-sidebar']) ? count($sidebars_widgets['omega-sidebar']) : 0;

        return [
            'woocommerce_active'   => class_exists('WooCommerce'),
            'php_ok'               => version_compare(PHP_VERSION, '7.4', '>='),
            'php_version'          => PHP_VERSION,
            'wp_ok'                => version_compare($wp_version, '5.8', '>='),
            'wp_version'           => $wp_version,
            'megamenu_published'   => $megamenu_count > 0,
            'megamenu_count'       => $megamenu_count,
            'widget_count'         => $widget_count,
        ];
    }

    private function render_header($subtitle) {
        ?>
        <div class="omega-admin-header">
            <div class="omega-admin-header__brand">
                <img src="<?php echo esc_url(omega_design_versioned_asset_url('/icons/Omega-Design.png')); ?>" alt="" class="omega-admin-header__logo" />
                <div>
                    <h1 class="omega-admin-header__title">
                        <?php esc_html_e('Omega Design', 'omega-design'); ?>
                        <span class="omega-badge omega-badge--neutral">v<?php echo esc_html(defined('OMEGA_DESIGN_VERSION') ? OMEGA_DESIGN_VERSION : '1.0.0'); ?></span>
                    </h1>
                    <p class="omega-admin-header__subtitle"><?php echo esc_html($subtitle); ?></p>
                </div>
            </div>
            <div class="omega-admin-header__actions">
                <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('admin.php?page=omega-dashboard')); ?>"><span class="dashicons dashicons-dashboard"></span> <?php esc_html_e('Dashboard', 'omega-design'); ?></a>
                <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('admin.php?page=omega-settings')); ?>"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Settings', 'omega-design'); ?></a>
                <a class="omega-btn omega-btn--primary" href="<?php echo esc_url(admin_url('site-editor.php')); ?>"><span class="dashicons dashicons-edit-large"></span> <?php esc_html_e('Site Editor', 'omega-design'); ?></a>
            </div>
        </div>
        <?php
    }

    private function render_color_mode_form($redirect_to) {
        $mode = color_mode::get_instance()->get_mode();
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card omega-card--mode">
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_color_mode" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', 'omega_nonce_mode'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-admin-appearance"></span>
                <div>
                    <h2><?php esc_html_e('Site Color Mode', 'omega-design'); ?></h2>
                    <p><?php esc_html_e("Automatic matches each visitor's device, on both desktop and mobile. Override it here to force one look for everyone.", 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-segmented" role="radiogroup" aria-label="<?php esc_attr_e('Site Color Mode', 'omega-design'); ?>">
                <?php
                $choices = [
                    'auto'  => __('Automatic', 'omega-design'),
                    'light' => __('Light', 'omega-design'),
                    'dark'  => __('Dark', 'omega-design'),
                ];
                foreach ($choices as $value => $label) :
                    $id = 'omega-color-mode-' . $value;
                    ?>
                    <input type="radio" name="omega_color_mode" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>" <?php checked($mode, $value); ?> />
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                <?php endforeach; ?>
            </div>

            <?php submit_button(__('Save Mode', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_mode', false); ?>
        </form>
        <?php
    }

    /**
     * One upload widget within the Site Logo card. admin-logo.js wires up
     * every .omega-logo-picker on the page independently by querying for
     * these child classes, so any number of pickers can share one form.
     */
    private function render_logo_picker($field_name, $logo_id, $label, $modal_title, $modal_button) {
        ?>
        <div class="omega-logo-picker">
            <p class="omega-logo-picker__label"><?php echo esc_html($label); ?></p>
            <input type="hidden" name="<?php echo esc_attr($field_name); ?>" class="omega-logo-picker__input" value="<?php echo esc_attr($logo_id); ?>" />

            <div class="omega-logo-preview omega-logo-picker__preview">
                <?php if ($logo_id) : ?>
                    <?php echo wp_get_attachment_image($logo_id, 'medium'); ?>
                <?php else : ?>
                    <span class="omega-logo-placeholder dashicons dashicons-format-image"></span>
                <?php endif; ?>
            </div>

            <div class="omega-logo-actions">
                <button type="button" class="omega-btn omega-btn--ghost omega-logo-picker__choose" data-title="<?php echo esc_attr($modal_title); ?>" data-button="<?php echo esc_attr($modal_button); ?>">
                    <span class="dashicons dashicons-upload"></span> <?php esc_html_e('Choose Logo', 'omega-design'); ?>
                </button>
                <button type="button" class="omega-btn omega-btn--ghost omega-logo-picker__remove" <?php echo $logo_id ? '' : 'style="display:none;"'; ?>>
                    <span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Remove', 'omega-design'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    private function render_logo_form($redirect_to) {
        $logo_id      = (int) get_theme_mod('custom_logo');
        $dark_logo_id = (int) get_theme_mod('omega_custom_logo_dark');
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card">
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_logo" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', 'omega_nonce_logo'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-format-image"></span>
                <div>
                    <h2><?php esc_html_e('Site Logo', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Shown in the header next to the site title. Add a dark mode version to swap it in automatically when the site is in dark mode.', 'omega-design'); ?></p>
                </div>
            </div>

            <?php
            $this->render_logo_picker(
                'omega_custom_logo',
                $logo_id,
                __('Light Mode Logo', 'omega-design'),
                __('Select Site Logo', 'omega-design'),
                __('Use as logo', 'omega-design')
            );
            $this->render_logo_picker(
                'omega_custom_logo_dark',
                $dark_logo_id,
                __('Dark Mode Logo (optional)', 'omega-design'),
                __('Select Dark Mode Logo', 'omega-design'),
                __('Use as dark mode logo', 'omega-design')
            );
            ?>

            <?php submit_button(__('Save Logo', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_logo', false); ?>
        </form>
        <?php
    }

    public function dashboard_page() {
        $status       = $this->get_status_data();
        $current_url  = admin_url('admin.php?page=omega-dashboard');
        ?>
        <div class="wrap omega-admin-page <?php echo esc_attr($this->color_mode_class()); ?>">
            <?php $this->render_header(__('Overview of your theme setup, at a glance.', 'omega-design')); ?>

            <div class="omega-grid omega-grid--3">
                <?php $this->render_color_mode_form($current_url); ?>
                <?php $this->render_logo_form($current_url); ?>

                <div class="omega-card">
                    <div class="omega-card__head">
                        <span class="dashicons dashicons-share"></span>
                        <div>
                            <h2><?php esc_html_e('Quick Links', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('Jump straight to the tools you use most.', 'omega-design'); ?></p>
                        </div>
                    </div>
                    <div class="omega-quicklinks">
                        <a href="<?php echo esc_url(admin_url('customize.php')); ?>"><span class="dashicons dashicons-admin-customizer"></span><?php esc_html_e('Customizer', 'omega-design'); ?></a>
                        <a href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><span class="dashicons dashicons-menu-alt"></span><?php esc_html_e('Navigation', 'omega-design'); ?></a>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=mega_menu')); ?>"><span class="dashicons dashicons-grid-view"></span><?php esc_html_e('Mega Menus', 'omega-design'); ?></a>
                        <a href="<?php echo esc_url(admin_url('widgets.php')); ?>"><span class="dashicons dashicons-screenoptions"></span><?php esc_html_e('Widgets', 'omega-design'); ?></a>
                        <a href="<?php echo esc_url(admin_url('site-editor.php?path=%2Fstyles')); ?>"><span class="dashicons dashicons-art"></span><?php esc_html_e('Global Styles', 'omega-design'); ?></a>
                    </div>
                </div>
            </div>

            <h2 class="omega-section-title"><?php esc_html_e('Status', 'omega-design'); ?></h2>
            <div class="omega-grid omega-grid--4">

                <div class="omega-status-card">
                    <span class="dashicons dashicons-cart"></span>
                    <h3><?php esc_html_e('WooCommerce', 'omega-design'); ?></h3>
                    <?php if ($status['woocommerce_active']) : ?>
                        <span class="omega-badge omega-badge--success"><?php esc_html_e('Active', 'omega-design'); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--neutral"><?php esc_html_e('Not installed', 'omega-design'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="omega-status-card">
                    <span class="dashicons dashicons-menu-alt3"></span>
                    <h3><?php esc_html_e('Mega Menu', 'omega-design'); ?></h3>
                    <?php if ($status['megamenu_published']) : ?>
                        <span class="omega-badge omega-badge--success"><?php echo esc_html(sprintf(_n('%d ready', '%d ready', $status['megamenu_count'], 'omega-design'), $status['megamenu_count'])); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('None yet', 'omega-design'); ?></span>
                    <?php endif; ?>
                    <a class="omega-status-card__link" href="<?php echo esc_url(admin_url('edit.php?post_type=mega_menu')); ?>"><?php esc_html_e('Manage Mega Menus', 'omega-design'); ?></a>
                </div>

                <div class="omega-status-card">
                    <span class="dashicons dashicons-screenoptions"></span>
                    <h3><?php esc_html_e('Sidebar Widgets', 'omega-design'); ?></h3>
                    <span class="omega-badge omega-badge--neutral"><?php echo esc_html($status['widget_count']); ?></span>
                    <a class="omega-status-card__link" href="<?php echo esc_url(admin_url('widgets.php')); ?>"><?php esc_html_e('Manage widgets', 'omega-design'); ?></a>
                </div>

                <div class="omega-status-card">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h3><?php esc_html_e('Environment', 'omega-design'); ?></h3>
                    <?php if ($status['php_ok'] && $status['wp_ok']) : ?>
                        <span class="omega-badge omega-badge--success"><?php esc_html_e('Compatible', 'omega-design'); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('Needs attention', 'omega-design'); ?></span>
                    <?php endif; ?>
                    <p class="omega-status-card__meta">PHP <?php echo esc_html($status['php_version']); ?> &middot; WP <?php echo esc_html($status['wp_version']); ?></p>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Lets an admin pick which system actually renders the header's
     * Navigation block: the block-based one edited in the Site Editor
     * (default), or a classic wp_nav_menu() menu managed under Appearance >
     * Menus - useful for admins who'd rather build their menu (and attach
     * Mega Menus) there instead. Swapping is handled by
     * megamenu::maybe_render_classic_navigation(), which reads these same
     * theme mods at render time.
     */
    private function render_header_nav_form($redirect_to) {
        $mode          = classic_header::normalize_mode(get_theme_mod('omega_nav_mode', 'block'));
        $classic_id    = (int) get_theme_mod('omega_classic_menu_id', 0);
        $classic_menus = wp_get_nav_menus();
        $choices       = classic_header::style_choices();
        $sticky        = (bool) get_theme_mod('omega_header_sticky', false);
        $bg_color      = get_theme_mod('omega_header_bg_color', '');
        $text_color    = get_theme_mod('omega_header_text_color', '');
        $font_family   = get_theme_mod('omega_header_font_family', '');
        $font_size     = get_theme_mod('omega_header_font_size', 'default');
        $height        = get_theme_mod('omega_header_height', 'default');
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card">
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_header_nav" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', 'omega_nonce_header_nav'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-menu-alt2"></span>
                <div>
                    <h2><?php esc_html_e('Header Navigation', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Choose which header actually shows to visitors: the block-based one from the Site Editor, or one of 5 fast, mobile-friendly classic layouts (your site logo if one is set, else the site title) driven by a classic menu.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label for="omega_nav_mode"><?php esc_html_e('Header style', 'omega-design'); ?></label>
                <select name="omega_nav_mode" id="omega_nav_mode">
                    <?php foreach ($choices as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($mode, $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="omega-field">
                <label for="omega_classic_menu_id"><?php esc_html_e('Classic menu to use (Classic styles only)', 'omega-design'); ?></label>
                <?php if (empty($classic_menus)) : ?>
                    <p class="description"><?php esc_html_e('No classic menus yet. Create one under Appearance > Menus first.', 'omega-design'); ?></p>
                <?php else : ?>
                    <select name="omega_classic_menu_id" id="omega_classic_menu_id">
                        <option value="0"><?php esc_html_e('— Select a menu —', 'omega-design'); ?></option>
                        <?php foreach ($classic_menus as $menu) : ?>
                            <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($classic_id, $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <label class="omega-toggle">
                <input type="checkbox" name="omega_header_sticky" value="1" <?php checked($sticky); ?> />
                <span class="omega-toggle__track"><span class="omega-toggle__thumb"></span></span>
                <span class="omega-toggle__label"><?php esc_html_e('Sticky header (hides on scroll down, reveals on scroll up)', 'omega-design'); ?></span>
            </label>

            <h3><?php esc_html_e('Appearance overrides (Classic styles only)', 'omega-design'); ?></h3>
            <p class="description"><?php esc_html_e('Leave any of these blank to keep following the theme\'s own Global Styles - these only override that one field, for the header specifically.', 'omega-design'); ?></p>

            <div class="omega-field">
                <label for="omega_header_bg_color"><?php esc_html_e('Header background color', 'omega-design'); ?></label>
                <input type="text" name="omega_header_bg_color" id="omega_header_bg_color" value="<?php echo esc_attr($bg_color); ?>" placeholder="var(--wp--preset--color--background)" />
            </div>

            <div class="omega-field">
                <label for="omega_header_text_color"><?php esc_html_e('Header text color', 'omega-design'); ?></label>
                <input type="text" name="omega_header_text_color" id="omega_header_text_color" value="<?php echo esc_attr($text_color); ?>" placeholder="inherit" />
            </div>

            <div class="omega-field">
                <label for="omega_header_font_family"><?php esc_html_e('Header font family', 'omega-design'); ?></label>
                <input type="text" name="omega_header_font_family" id="omega_header_font_family" value="<?php echo esc_attr($font_family); ?>" placeholder="<?php esc_attr_e('theme default', 'omega-design'); ?>" />
                <p class="description"><?php esc_html_e('A CSS font-family value, e.g. Georgia, serif - or the name of a font already loaded elsewhere on the site.', 'omega-design'); ?></p>
            </div>

            <div class="omega-field">
                <label for="omega_header_font_size"><?php esc_html_e('Menu text size', 'omega-design'); ?></label>
                <select name="omega_header_font_size" id="omega_header_font_size">
                    <?php foreach (classic_header::FONT_SIZE_CHOICES as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($font_size, $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="omega-field">
                <label for="omega_header_height"><?php esc_html_e('Header height', 'omega-design'); ?></label>
                <select name="omega_header_height" id="omega_header_height">
                    <?php foreach (classic_header::HEIGHT_CHOICES as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($height, $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php submit_button(__('Save Header Navigation', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_header_nav', false); ?>
        </form>
        <?php
    }

    /**
     * A second, independent bar (phone number, promo message, social
     * links) stacked above whatever header is active - see
     * announcement_bar.php, which renders it on wp_body_open() regardless
     * of Block Navigation vs. Classic mode.
     */
    private function render_announcement_form($redirect_to) {
        $enabled     = (bool) get_theme_mod('omega_announcement_enabled', false);
        $content     = get_theme_mod('omega_announcement_content', '');
        $bg          = get_theme_mod('omega_announcement_bg', '');
        $text_color  = get_theme_mod('omega_announcement_text_color', '');
        $dismissible = (bool) get_theme_mod('omega_announcement_dismissible', true);
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card">
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_announcement" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', 'omega_nonce_announcement'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-megaphone"></span>
                <div>
                    <h2><?php esc_html_e('Announcement Bar', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('A second bar shown above the header on every page - a phone number, a promo message, social links, whatever HTML you need.', 'omega-design'); ?></p>
                </div>
            </div>

            <label class="omega-toggle">
                <input type="checkbox" name="omega_announcement_enabled" value="1" <?php checked($enabled); ?> />
                <span class="omega-toggle__track"><span class="omega-toggle__thumb"></span></span>
                <span class="omega-toggle__label"><?php esc_html_e('Show announcement bar', 'omega-design'); ?></span>
            </label>

            <div class="omega-field">
                <label for="omega_announcement_content"><?php esc_html_e('Content (HTML)', 'omega-design'); ?></label>
                <textarea name="omega_announcement_content" id="omega_announcement_content" style="width:100%;height:100px;font-family:monospace;" spellcheck="false"><?php echo esc_textarea($content); ?></textarea>
                <p class="description"><?php esc_html_e('E.g. Call us: <a href="tel:+15551234567">(555) 123-4567</a> &middot; Free shipping over $50', 'omega-design'); ?></p>
            </div>

            <div class="omega-field">
                <label for="omega_announcement_bg"><?php esc_html_e('Background color', 'omega-design'); ?></label>
                <input type="text" name="omega_announcement_bg" id="omega_announcement_bg" value="<?php echo esc_attr($bg); ?>" placeholder="var(--wp--preset--color--primary)" />
            </div>

            <div class="omega-field">
                <label for="omega_announcement_text_color"><?php esc_html_e('Text color', 'omega-design'); ?></label>
                <input type="text" name="omega_announcement_text_color" id="omega_announcement_text_color" value="<?php echo esc_attr($text_color); ?>" placeholder="var(--wp--preset--color--button-text)" />
            </div>

            <label class="omega-toggle">
                <input type="checkbox" name="omega_announcement_dismissible" value="1" <?php checked($dismissible); ?> />
                <span class="omega-toggle__track"><span class="omega-toggle__thumb"></span></span>
                <span class="omega-toggle__label"><?php esc_html_e('Let visitors dismiss it', 'omega-design'); ?></span>
            </label>

            <?php submit_button(__('Save Announcement Bar', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_announcement', false); ?>
        </form>
        <?php
    }

    /**
     * Mirrors render_header_nav_form() - same "Block vs. one of 5 Classic
     * styles" shape, driven by classic_footer.php. Every text field here
     * defaults to empty and falls back to something computed from this
     * site's own data at render time (bloginfo(), the current year) -
     * see classic_footer.php - never a fixed example value.
     */
    private function render_footer_form($redirect_to) {
        $mode          = get_theme_mod('omega_footer_mode', 'block');
        $classic_id    = (int) get_theme_mod('omega_classic_footer_menu_id', 0);
        $classic_menus = wp_get_nav_menus();
        $choices       = classic_footer::style_choices();
        $tagline       = get_theme_mod('omega_footer_tagline', '');
        $copyright     = get_theme_mod('omega_footer_copyright', '');
        $cta_label     = get_theme_mod('omega_footer_cta_label', '');
        $cta_url       = get_theme_mod('omega_footer_cta_url', '');
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card">
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_footer" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', 'omega_nonce_footer'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-align-center"></span>
                <div>
                    <h2><?php esc_html_e('Footer', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Choose which footer actually shows to visitors: the block-based one from the Site Editor, or one of 5 fast, mobile-friendly classic layouts driven by a classic menu.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label for="omega_footer_mode"><?php esc_html_e('Footer style', 'omega-design'); ?></label>
                <select name="omega_footer_mode" id="omega_footer_mode">
                    <?php foreach ($choices as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($mode, $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="omega-field">
                <label for="omega_classic_footer_menu_id"><?php esc_html_e('Classic menu to use (Classic styles only)', 'omega-design'); ?></label>
                <?php if (empty($classic_menus)) : ?>
                    <p class="description"><?php esc_html_e('No classic menus yet. Create one under Appearance > Menus first.', 'omega-design'); ?></p>
                <?php else : ?>
                    <select name="omega_classic_footer_menu_id" id="omega_classic_footer_menu_id">
                        <option value="0"><?php esc_html_e('— Select a menu —', 'omega-design'); ?></option>
                        <?php foreach ($classic_menus as $menu) : ?>
                            <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($classic_id, $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Nest items under a parent to render that parent as a column heading in the "Columns"/"Bold" styles.', 'omega-design'); ?></p>
                <?php endif; ?>
            </div>

            <div class="omega-field">
                <label for="omega_footer_tagline"><?php esc_html_e('Tagline (optional)', 'omega-design'); ?></label>
                <input type="text" name="omega_footer_tagline" id="omega_footer_tagline" value="<?php echo esc_attr($tagline); ?>" placeholder="<?php echo esc_attr(get_bloginfo('description')); ?>" />
            </div>

            <div class="omega-field">
                <label for="omega_footer_copyright"><?php esc_html_e('Copyright text (optional)', 'omega-design'); ?></label>
                <input type="text" name="omega_footer_copyright" id="omega_footer_copyright" value="<?php echo esc_attr($copyright); ?>" placeholder="<?php echo esc_attr(sprintf(__('&copy; {year} %s. All rights reserved.', 'omega-design'), get_bloginfo('name'))); ?>" />
                <p class="description"><?php esc_html_e('{year} is replaced with the current year automatically.', 'omega-design'); ?></p>
            </div>

            <div class="omega-field">
                <label for="omega_footer_cta_label"><?php esc_html_e('CTA button text ("Newsletter CTA" style only)', 'omega-design'); ?></label>
                <input type="text" name="omega_footer_cta_label" id="omega_footer_cta_label" value="<?php echo esc_attr($cta_label); ?>" />
            </div>

            <div class="omega-field">
                <label for="omega_footer_cta_url"><?php esc_html_e('CTA button link', 'omega-design'); ?></label>
                <input type="url" name="omega_footer_cta_url" id="omega_footer_cta_url" value="<?php echo esc_attr($cta_url); ?>" placeholder="https://" />
            </div>

            <?php submit_button(__('Save Footer', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_footer', false); ?>
        </form>
        <?php
    }

    public function settings_page() {
        $current_url = admin_url('admin.php?page=omega-settings');
        $sidebar_enabled = sidebar::get_instance()->is_sidebar_enabled();
        $sidebar_position = sidebar::get_instance()->get_sidebar_position();
        $sidebar_width = (int) get_theme_mod('omega_sidebar_width', 30);
        $status = $this->get_status_data();
        ?>
        <div class="wrap omega-admin-page <?php echo esc_attr($this->color_mode_class()); ?>">
            <?php $this->render_header(__('All Omega Design options in one place.', 'omega-design')); ?>

            <div class="omega-grid omega-grid--2">
                <?php $this->render_color_mode_form($current_url); ?>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="omega-card">
                    <input type="hidden" name="action" value="omega_save_settings" />
                    <input type="hidden" name="omega_section_sidebar" value="1" />
                    <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($current_url); ?>" />
                    <?php wp_nonce_field('omega_save_settings', 'omega_nonce_sidebar'); ?>

                    <div class="omega-card__head">
                        <span class="dashicons dashicons-align-right"></span>
                        <div>
                            <h2><?php esc_html_e('Sidebar', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('Controls the sidebar template part used on category archives.', 'omega-design'); ?></p>
                        </div>
                    </div>

                    <label class="omega-toggle">
                        <input type="checkbox" name="omega_enable_sidebar" value="1" <?php checked($sidebar_enabled); ?> />
                        <span class="omega-toggle__track"><span class="omega-toggle__thumb"></span></span>
                        <span class="omega-toggle__label"><?php esc_html_e('Enable sidebar', 'omega-design'); ?></span>
                    </label>

                    <div class="omega-field">
                        <label for="omega_sidebar_position"><?php esc_html_e('Position', 'omega-design'); ?></label>
                        <select name="omega_sidebar_position" id="omega_sidebar_position">
                            <option value="left" <?php selected($sidebar_position, 'left'); ?>><?php esc_html_e('Left', 'omega-design'); ?></option>
                            <option value="right" <?php selected($sidebar_position, 'right'); ?>><?php esc_html_e('Right', 'omega-design'); ?></option>
                        </select>
                    </div>

                    <div class="omega-field">
                        <label for="omega_sidebar_width"><?php esc_html_e('Width', 'omega-design'); ?> (<span id="omega_sidebar_width_value"><?php echo esc_html($sidebar_width); ?></span>%)</label>
                        <input type="range" min="20" max="50" step="1" name="omega_sidebar_width" id="omega_sidebar_width" value="<?php echo esc_attr($sidebar_width); ?>" oninput="document.getElementById('omega_sidebar_width_value').textContent = this.value;" />
                    </div>

                    <?php submit_button(__('Save Sidebar Settings', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_sidebar', false); ?>
                </form>
            </div>

            <div class="omega-grid omega-grid--2">
                <?php $this->render_header_nav_form($current_url); ?>

                <div class="omega-card">
                    <div class="omega-card__head">
                        <span class="dashicons dashicons-menu-alt3"></span>
                        <div>
                            <h2><?php esc_html_e('Mega Menu', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('Build mega menus under Omega Design > Mega Menus (title, content, optional Custom CSS - each is its own post). Then, in the Site Editor\'s Navigation panel, add a "Mega Menu" item from the + inserter or attach one to any nav item from the block settings.', 'omega-design'); ?></p>
                        </div>
                    </div>
                    <?php if ($status['megamenu_published']) : ?>
                        <span class="omega-badge omega-badge--success"><?php echo esc_html(sprintf(_n('%d mega menu ready', '%d mega menus ready', $status['megamenu_count'], 'omega-design'), $status['megamenu_count'])); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('No mega menus yet', 'omega-design'); ?></span>
                    <?php endif; ?>
                    <p>
                        <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=mega_menu')); ?>"><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Manage Mega Menus', 'omega-design'); ?></a>
                        <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><span class="dashicons dashicons-edit-large"></span> <?php esc_html_e('Manage Navigation', 'omega-design'); ?></a>
                    </p>
                </div>

                <div class="omega-card">
                    <div class="omega-card__head">
                        <span class="dashicons dashicons-heading"></span>
                        <div>
                            <h2><?php esc_html_e('Page & Post Settings', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('Title and header visibility are set per page or post, not globally: open any Page or Post and use the "Hide page title" / "Hide header" toggles in the editor sidebar\'s Page Settings panel.', 'omega-design'); ?></p>
                        </div>
                    </div>
                    <p>
                        <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Go to Pages', 'omega-design'); ?></a>
                    </p>
                </div>
            </div>

            <div class="omega-grid omega-grid--2">
                <?php $this->render_announcement_form($current_url); ?>
                <?php $this->render_footer_form($current_url); ?>
            </div>
        </div>
        <?php
    }
}
