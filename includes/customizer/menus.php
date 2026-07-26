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

        $nav_id     = (int) get_option('omega_megamenu_nav_id');
        $nav_status = $nav_id ? get_post_status($nav_id) : false;

        $sidebars_widgets = wp_get_sidebars_widgets();
        $widget_count     = isset($sidebars_widgets['omega-sidebar']) ? count($sidebars_widgets['omega-sidebar']) : 0;

        return [
            'woocommerce_active' => class_exists('WooCommerce'),
            'php_ok'              => version_compare(PHP_VERSION, '7.4', '>='),
            'php_version'         => PHP_VERSION,
            'wp_ok'               => version_compare($wp_version, '5.8', '>='),
            'wp_version'          => $wp_version,
            'megamenu_published'  => 'publish' === $nav_status,
            'megamenu_nav_id'     => $nav_id,
            'widget_count'        => $widget_count,
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
                        <span class="omega-badge omega-badge--success"><?php esc_html_e('Ready', 'omega-design'); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('Not set up', 'omega-design'); ?></span>
                    <?php endif; ?>
                    <a class="omega-status-card__link" href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><?php esc_html_e('Manage navigation', 'omega-design'); ?></a>
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
                <div class="omega-card">
                    <div class="omega-card__head">
                        <span class="dashicons dashicons-menu-alt3"></span>
                        <div>
                            <h2><?php esc_html_e('Mega Menu', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('The mega menu reads its links from a "Mega Menu" navigation, and its panels from a navigation-link labeled Shop, Resources or Blog.', 'omega-design'); ?></p>
                        </div>
                    </div>
                    <?php if ($status['megamenu_published']) : ?>
                        <span class="omega-badge omega-badge--success"><?php esc_html_e('Navigation ready', 'omega-design'); ?></span>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('Not set up yet', 'omega-design'); ?></span>
                    <?php endif; ?>
                    <p>
                        <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><span class="dashicons dashicons-edit-large"></span> <?php esc_html_e('Manage Navigation', 'omega-design'); ?></a>
                    </p>
                </div>

                <div class="omega-card">
                    <div class="omega-card__head">
                        <span class="dashicons dashicons-heading"></span>
                        <div>
                            <h2><?php esc_html_e('Page & Post Titles', 'omega-design'); ?></h2>
                            <p><?php esc_html_e('Title visibility is set per page or post, not globally: open any Page or Post and use the "Hide page title" toggle at the top of the editor sidebar.', 'omega-design'); ?></p>
                        </div>
                    </div>
                    <p>
                        <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Go to Pages', 'omega-design'); ?></a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}
