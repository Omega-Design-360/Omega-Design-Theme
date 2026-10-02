<?php
/**
 * Left Sidebar Admin Menu, Dashboard & Settings Pages
 *
 * @package OmegaDesign\admin
 */

namespace OmegaDesign\customizer;

use OmegaDesign\core\core;
use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class menus {

    use singleton;
    use assets;

    /**
     * Settings form section => its save method. Each form only includes a
     * hidden "omega_section_*" marker for the sections it actually
     * contains, so handle_save_settings() never touches settings a given
     * form didn't show. Saved in this order.
     */
    const SAVE_SECTIONS = [
        'color_scheme'  => 'save_color_scheme_section',
        'color_mode'    => 'save_color_mode_section',
        'sidebar'       => 'save_sidebar_section',
        'typography'    => 'save_typography_section',
        'design'        => 'save_design_section',
        'logo'          => 'save_logo_section',
        'header_nav'    => 'save_header_nav_section',
        'announcement'  => 'save_announcement_section',
        'footer'        => 'save_footer_section',
        'product_page'  => 'save_product_page_section',
    ];

    /**
     * Sections with their own nonce field, checked in this order; a form
     * with none of them (the Dashboard's color-mode toggle) uses
     * omega_nonce_mode.
     */
    const NONCE_SECTIONS = [
        'color_scheme', 'sidebar', 'typography', 'design', 'logo',
        'header_nav', 'announcement', 'footer', 'product_page',
    ];

    private $dashboard_hook = null;
    private $settings_hook  = null;

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
        return $classes . ' omega-admin-page-body ' . $this->color_mode_class();
    }

    /**
     * The left-hand admin menu icon for "Omega Design" - a base64 data URI
     * (WordPress's own recommended approach for a custom SVG menu icon, per
     * add_menu_page()'s docs) rather than a plain file URL, so it works the
     * same way a dashicon does without an extra HTTP request. Falls back to
     * a dashicon if the file is ever missing so a bad path can't leave the
     * whole admin menu item without an icon.
     */
    private function get_menu_icon_data_uri() {
        $path = OMEGA_DESIGN_ASSETS . '/images/theme-icon.svg';
        if (!file_exists($path)) {
            return 'dashicons-layout';
        }

        $svg = file_get_contents($path);
        if (false === $svg) {
            return 'dashicons-layout';
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function register_admin_menu() {
        $this->dashboard_hook = add_menu_page(
            __('Omega Design', 'omega-design'),
            __('Omega Design', 'omega-design'),
            'manage_options',
            'omega-dashboard',
            [$this, 'dashboard_page'],
            $this->get_menu_icon_data_uri(),
            2
        );

        self::add_submenu(__('Dashboard', 'omega-design'), 'omega-dashboard', [$this, 'dashboard_page']);
        self::add_submenu(__('Site Builder', 'omega-design'), 'site-editor.php');
        $this->settings_hook = self::add_submenu(__('Settings', 'omega-design'), 'omega-settings', [$this, 'settings_page']);
        self::add_submenu(__('Appearance', 'omega-design'), 'customize.php');
        self::add_submenu(__('Menus', 'omega-design'), 'nav-menus.php');
        self::add_submenu(__('Mega Menus', 'omega-design'), 'edit.php?post_type=mega_menu');
        self::add_submenu(__('Sidebar', 'omega-design'), self::sidebar_editor_path());
    }

    /**
     * One "Omega Design" submenu item (manage_options only), titled the
     * same in the page title and the menu. Returns the page hook suffix.
     */
    private static function add_submenu($title, $slug, $callback = '') {
        return add_submenu_page('omega-dashboard', $title, $title, 'manage_options', $slug, $callback);
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
            // A plain <img>, not a background-image span - core's admin-bar
            // CSS forces "background-image: none !important" on anything
            // classed .ab-icon, which an <img> tag simply isn't subject to.
            'title' => '<img src="' . esc_url(\OmegaDesign\core\asset_urls::versioned('/images/theme-icon.svg')) . '" class="omega-topbar-icon" alt="" />' . esc_html__('Omega Design', 'omega-design'),
            'href'  => admin_url('admin.php?page=omega-dashboard'),
        ]);

        foreach (self::admin_bar_links() as $id => list($title, $path)) {
            $wp_admin_bar->add_node([
                'id'     => $id,
                'parent' => 'omega-design',
                'title'  => $title,
                'href'   => admin_url($path),
            ]);
        }
    }

    /**
     * Site Editor path for the sidebar template part. The theme registers no
     * classic widget areas (its sidebars are block template parts), so
     * widgets.php would only show WordPress's "not widget-aware" error.
     */
    private static function sidebar_editor_path() {
        return 'site-editor.php?postType=wp_template_part&postId=' . rawurlencode(get_stylesheet() . '//sidebar') . '&canvas=edit';
    }

    /** Admin bar node id => [title, admin path], in display order. */
    private static function admin_bar_links() {
        return [
            'omega-site-builder' => ['Site Builder', 'site-editor.php'],
            'omega-settings'     => ['Settings', 'admin.php?page=omega-settings'],
            'omega-appearance'   => ['Appearance', 'customize.php'],
            'omega-menus'        => ['Menus', 'nav-menus.php'],
            'omega-sidebar'      => ['Sidebar', self::sidebar_editor_path()],
        ];
    }

    public function enqueue_admin_page_assets($hook) {
        if (!in_array($hook, [$this->dashboard_hook, $this->settings_hook], true)) {
            return;
        }

        // This admin screen is a plain wp-admin page, not the front end or the
        // block editor - WordPress never prints the theme.json-derived
        // --wp--preset--color--* variables here on its own, so they're
        // printed alongside admin-pages.css.
        self::enqueue_admin_pages_style(true);

        // Same color-mode.css used on the front end, so this page can carry
        // the same omega-color-mode-{mode} class and pick up the identical
        // light/dark values - one palette, one switch, everywhere.
        self::enqueue_style('omega-design-color-mode', 'css/color-mode.css', ['omega-design-admin-pages']);

        // Both pages render the same Site Logo form (render_logo_form()),
        // so both need the media modal and admin-logo.js.
        wp_enqueue_media();
        self::enqueue_script('omega-design-admin-logo', 'js/admin-logo.js', ['media-editor', self::admin_core_script()], true);

        if ($hook === $this->settings_hook) {
            $this->enqueue_settings_page_assets();
        }
    }

    private function enqueue_settings_page_assets() {
        self::enqueue_script('omega-design-admin-layout-picker', 'js/admin-layout-picker.js', [self::admin_core_script()], true);
        self::enqueue_script('omega-design-admin-settings-tabs', 'js/admin-settings-tabs.js', [self::admin_core_script()], true);

        // The real front-end announcement-bar stylesheet, reused as-is so
        // the Announcement Bar form's live preview strip renders
        // pixel-identical to what actually shows on the front end - see
        // render_announcement_form() and admin-announcement-preview.js.
        self::enqueue_style('omega-design-announcement-bar', 'css/announcement-bar.css', ['omega-design-admin-pages']);
        self::enqueue_script('omega-design-admin-announcement-preview', 'js/admin-announcement-preview.js', [self::admin_core_script()], true);

        // Header Navigation form's own live preview - see
        // render_header_nav_form() and admin-header-nav-preview.js.
        self::enqueue_script('omega-design-admin-header-nav-preview', 'js/admin-header-nav-preview.js', [self::admin_core_script()], true);

        // Typography form's dropdown + single preview card - see
        // render_typography_form() and admin-typography-preview.js.
        self::enqueue_script('omega-design-admin-typography-preview', 'js/admin-typography-preview.js', [self::admin_core_script()], true);
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
     * the full Settings page - see SAVE_SECTIONS for how a form's sections
     * are picked out, so this one handler can't accidentally wipe out
     * settings a given form never showed (e.g. the dashboard's mode-only
     * form won't touch the sidebar settings).
     */
    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'omega-design'));
        }

        check_admin_referer('omega_save_settings', self::posted_nonce_field());

        foreach (self::SAVE_SECTIONS as $section => $save_method) {
            if (isset($_POST['omega_section_' . $section])) {
                $this->$save_method();
            }
        }

        $redirect_to = isset($_POST['omega_redirect_to'])
            ? esc_url_raw(wp_unslash($_POST['omega_redirect_to']))
            : admin_url('admin.php?page=omega-dashboard');

        wp_safe_redirect(add_query_arg('omega_saved', '1', $redirect_to));
        exit;
    }

    /**
     * The nonce field of the first section (NONCE_SECTIONS order) the
     * submitted form contains.
     */
    private static function posted_nonce_field() {
        foreach (self::NONCE_SECTIONS as $section) {
            if (isset($_POST['omega_section_' . $section])) {
                return 'omega_nonce_' . $section;
            }
        }
        return 'omega_nonce_mode';
    }

    /**
     * Opening <form> tag of one Settings section plus the hidden fields
     * handle_save_settings() reads: the admin-post action, the section
     * marker, where to return to, and the section's own nonce.
     */
    private static function render_settings_form_open($section, $nonce_field, $redirect_to, $class = '') {
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"<?php echo $class ? ' class="' . esc_attr($class) . '"' : ''; ?>>
            <input type="hidden" name="action" value="omega_save_settings" />
            <input type="hidden" name="omega_section_<?php echo esc_attr($section); ?>" value="1" />
            <input type="hidden" name="omega_redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
            <?php wp_nonce_field('omega_save_settings', $nonce_field); ?>
        <?php
    }

    /**
     * A <select> of every classic menu, with a "Select a menu" (0) option.
     */
    private static function render_menu_select($name, array $menus, $current_id) {
        ?>
        <select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>">
            <option value="0"><?php esc_html_e('— Select a menu —', 'omega-design'); ?></option>
            <?php foreach ($menus as $menu) : ?>
                <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($current_id, $menu->term_id); ?>><?php echo esc_html($menu->name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Plain-text titles of a classic menu's top-level items, at most $limit.
     */
    private static function menu_top_level_titles($menu_id, $limit) {
        $items = wp_get_nav_menu_items($menu_id);
        if (!$items) {
            return [];
        }

        $titles = [];
        foreach ($items as $item) {
            if (0 === (int) $item->menu_item_parent) {
                $titles[] = wp_strip_all_tags($item->title);
            }
        }
        return array_slice($titles, 0, $limit);
    }

    /* ── POST readers ──────────────────────────────────────────── */

    /** The unslashed POST value, or $default when it wasn't submitted. */
    private static function posted($key, $default = '') {
        return isset($_POST[$key]) ? wp_unslash($_POST[$key]) : $default;
    }

    /** A single-line text POST value (colors may be var() references, so not hex-only). */
    private static function posted_text($key) {
        return isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
    }

    private static function posted_absint($key, $default = 0) {
        return isset($_POST[$key]) ? absint($_POST[$key]) : $default;
    }

    /** A checkbox: true only when submitted and non-empty. */
    private static function posted_flag($key) {
        return !empty($_POST[$key]);
    }

    /* ── Theme mod writers ─────────────────────────────────────── */

    /**
     * Saves POST $key to theme mod $mod when it's one of $choices' keys,
     * else $default.
     */
    private static function save_choice_mod($mod, $key, array $choices, $default) {
        $value = self::posted($key, $default);
        if (!array_key_exists($value, $choices)) {
            $value = $default;
        }
        set_theme_mod($mod, $value);
    }

    /** Saves a picked classic menu's ID, or 0 when it doesn't exist. */
    private static function save_menu_mod($mod, $key) {
        $menu_id = self::posted_absint($key);
        set_theme_mod($mod, ($menu_id && wp_get_nav_menu_object($menu_id)) ? $menu_id : 0);
    }

    /** Saves a picked media attachment's ID, or removes the mod. */
    private static function save_attachment_mod($mod, $key) {
        $attachment_id = self::posted_absint($key);

        if ($attachment_id > 0 && 'attachment' === get_post_type($attachment_id)) {
            set_theme_mod($mod, $attachment_id);
        } else {
            remove_theme_mod($mod);
        }
    }

    private static function save_text_mods(array $mods) {
        foreach ($mods as $mod) {
            set_theme_mod($mod, self::posted_text($mod));
        }
    }

    private static function save_flag_mods(array $mods) {
        foreach ($mods as $mod) {
            set_theme_mod($mod, self::posted_flag($mod));
        }
    }

    /* ── Sections ──────────────────────────────────────────────── */

    private function save_color_scheme_section() {
        $scheme = self::posted('omega_color_scheme', color_scheme::DEFAULT_SCHEME);
        set_theme_mod('omega_color_scheme', color_scheme::get_instance()->sanitize_scheme($scheme));
    }

    private function save_color_mode_section() {
        $mode = self::posted('omega_color_mode', 'auto');
        set_theme_mod('omega_color_mode', color_mode::get_instance()->sanitize_mode($mode));
    }

    private function save_sidebar_section() {
        $sidebar = sidebar::get_instance();

        set_theme_mod('omega_enable_sidebar', self::posted_flag('omega_enable_sidebar'));
        set_theme_mod('omega_sidebar_position', $sidebar->sanitize_position(self::posted('omega_sidebar_position', 'right')));
        set_theme_mod('omega_sidebar_width', max(20, min(50, self::posted_absint('omega_sidebar_width', 30))));
        set_theme_mod('omega_sidebar_default_template', $sidebar->sanitize_template(self::posted('omega_sidebar_default_template', 'sidebar')));

        self::save_flag_mods(array_values($sidebar->get_sidebar_locations_map()));
    }

    private function save_typography_section() {
        $typography = typography::get_instance();

        set_theme_mod(typography::HEADING_MOD, $typography->sanitize_font(self::posted('omega_heading_font')));
        set_theme_mod(typography::BODY_MOD, $typography->sanitize_font(self::posted('omega_body_font')));
    }

    private function save_design_section() {
        self::save_choice_mod('omega_button_radius', 'omega_button_radius', self::BUTTON_RADIUS_CHOICES, 'soft');
        self::save_choice_mod('omega_button_look', 'omega_button_look', self::BUTTON_LOOK_CHOICES, 'fill');
        self::save_flag_mods(['omega_svg_uploads_enabled']);
    }

    private function save_logo_section() {
        self::save_attachment_mod('custom_logo', 'omega_custom_logo');
        self::save_attachment_mod('omega_custom_logo_dark', 'omega_custom_logo_dark');
    }

    private function save_header_nav_section() {
        self::save_choice_mod('omega_nav_mode', 'omega_nav_mode', classic_header::style_choices(), 'block');
        self::save_menu_mod('omega_classic_menu_id', 'omega_classic_menu_id');
        self::save_flag_mods(['omega_header_sticky']);

        // Opt-in overrides only - left blank/default, classic_header.php
        // emits no inline style for them at all and theme.json keeps
        // deciding, same as everywhere else in this theme.
        self::save_text_mods(['omega_header_bg_color', 'omega_header_text_color', 'omega_header_font_family']);
        self::save_choice_mod('omega_header_font_size', 'omega_header_font_size', classic_header::FONT_SIZE_CHOICES, 'default');
        self::save_choice_mod('omega_header_height', 'omega_header_height', classic_header::HEIGHT_CHOICES, 'default');
    }

    private function save_announcement_section() {
        self::save_flag_mods(['omega_announcement_enabled', 'omega_announcement_dismissible']);

        // Trusted admin-authored HTML (phone/social links need real
        // markup) - same trust model as the Mega Menu's own Custom CSS
        // field, not stripped down to plain text.
        set_theme_mod('omega_announcement_content', self::posted('omega_announcement_content'));

        self::save_text_mods(['omega_announcement_bg', 'omega_announcement_text_color']);
    }

    private function save_footer_section() {
        self::save_choice_mod('omega_footer_mode', 'omega_footer_mode', classic_footer::style_choices(), 'block');
        self::save_menu_mod('omega_classic_footer_menu_id', 'omega_classic_footer_menu_id');

        // {year} in the copyright is a literal token replaced at render
        // time - see classic_footer.php's copyright_html() - not sanitized
        // away since it isn't HTML.
        self::save_text_mods(['omega_footer_tagline', 'omega_footer_copyright', 'omega_footer_cta_label']);

        set_theme_mod('omega_footer_cta_url', isset($_POST['omega_footer_cta_url']) ? esc_url_raw(wp_unslash($_POST['omega_footer_cta_url'])) : '');
    }

    private function save_product_page_section() {
        self::save_choice_mod('omega_product_page_layout', 'omega_product_page_layout', product_page::style_choices(), 'gallery-feature');
        self::save_flag_mods(['omega_related_products_carousel']);
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

        return [
            'woocommerce_active'   => class_exists('WooCommerce'),
            'php_ok'               => core::meets_php_requirement(),
            'php_version'          => PHP_VERSION,
            'wp_ok'                => core::meets_wp_requirement(),
            'wp_version'           => $wp_version,
            'megamenu_published'   => $megamenu_count > 0,
            'megamenu_count'       => $megamenu_count,
        ];
    }

    private function render_header($subtitle) {
        $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        ?>
        <div class="omega-admin-header">
            <div class="omega-admin-header__brand">
                <img src="<?php echo esc_url(\OmegaDesign\core\asset_urls::versioned('/images/theme-icon.svg')); ?>" alt="" class="omega-admin-header__logo" />
                <div>
                    <h1 class="omega-admin-header__title">
                        <?php esc_html_e('Omega Design', 'omega-design'); ?>
                        <span class="omega-badge omega-badge--neutral">v<?php echo esc_html(defined('OMEGA_DESIGN_VERSION') ? OMEGA_DESIGN_VERSION : '1.0.0'); ?></span>
                    </h1>
                    <p class="omega-admin-header__subtitle"><?php echo esc_html($subtitle); ?></p>
                </div>
            </div>
            <div class="omega-admin-header__actions">
                <a class="omega-btn omega-btn--ghost <?php echo 'omega-dashboard' === $current_page ? 'is-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=omega-dashboard')); ?>"><span class="dashicons dashicons-dashboard"></span> <?php esc_html_e('Dashboard', 'omega-design'); ?></a>
                <a class="omega-btn omega-btn--ghost <?php echo 'omega-settings' === $current_page ? 'is-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=omega-settings')); ?>"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Settings', 'omega-design'); ?></a>
                <a class="omega-btn omega-btn--primary" href="<?php echo esc_url(admin_url('site-editor.php')); ?>"><span class="dashicons dashicons-edit-large"></span> <?php esc_html_e('Site Editor', 'omega-design'); ?></a>
            </div>
        </div>
        <?php
    }

    private function render_color_mode_form($redirect_to) {
        $mode = color_mode::get_instance()->get_mode();
        ?>
        <?php self::render_settings_form_open('color_mode', 'omega_nonce_mode', $redirect_to, 'omega-card omega-card--mode'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-admin-appearance"></span>
                <div>
                    <h2><?php esc_html_e('Site Color Mode', 'omega-design'); ?></h2>
                </div>
            </div>

            <?php
            color_mode::render_mode_cards(
                $mode,
                function ($key) {
                    echo 'name="omega_color_mode"';
                }
            );
            ?>

            <?php submit_button(__('Save Mode', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_mode', false); ?>
        </form>
        <?php
    }

    /**
     * One upload widget within the Site Logo card. admin-logo.js wires up
     * every .omega-logo-picker on the page independently by querying for
     * these child classes, so any number of pickers can share one form.
     */
    private function render_logo_picker($field_name, $logo_id, $label, $modal_title, $modal_button, $hint = '') {
        ?>
        <div class="omega-logo-picker">
            <p class="omega-logo-picker__label"><?php echo esc_html($label); ?></p>
            <?php if ('' !== $hint) : ?>
                <p class="description"><?php echo esc_html($hint); ?></p>
            <?php endif; ?>
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
        <?php self::render_settings_form_open('logo', 'omega_nonce_logo', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-format-image"></span>
                <div>
                    <h2><?php esc_html_e('Site Logo', 'omega-design'); ?></h2>
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
                __('Use as dark mode logo', 'omega-design'),
                __('Swaps in automatically in dark mode.', 'omega-design')
            );
            ?>

            <?php submit_button(__('Save Logo', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_logo', false); ?>
        </form>
        <?php
    }

    public function dashboard_page() {
        $status        = $this->get_status_data();
        $current_url   = admin_url('admin.php?page=omega-dashboard');
        $license       = \OmegaDesign\core\license::get_instance();
        $license_state = $license->get_state();
        ?>
        <div class="wrap omega-admin-page <?php echo esc_attr($this->color_mode_class()); ?>">
            <?php $this->render_header(__('Overview of your theme setup, at a glance.', 'omega-design')); ?>

            <div class="omega-grid omega-grid--2">
                <?php $this->render_logo_form($current_url); ?>

                <div class="omega-stack">
                    <?php $this->render_color_mode_form($current_url); ?>

                    <div class="omega-card">
                        <div class="omega-card__head">
                            <span class="dashicons dashicons-share"></span>
                            <div>
                                <h2><?php esc_html_e('Quick Links', 'omega-design'); ?></h2>
                            </div>
                        </div>
                        <div class="omega-quicklinks">
                            <a href="<?php echo esc_url(admin_url('customize.php')); ?>"><span class="dashicons dashicons-admin-customizer"></span><?php esc_html_e('Customizer', 'omega-design'); ?></a>
                            <a href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><span class="dashicons dashicons-menu-alt"></span><?php esc_html_e('Navigation', 'omega-design'); ?></a>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=mega_menu')); ?>"><span class="dashicons dashicons-grid-view"></span><?php esc_html_e('Mega Menus', 'omega-design'); ?></a>
                            <a href="<?php echo esc_url(admin_url(self::sidebar_editor_path())); ?>"><span class="dashicons dashicons-screenoptions"></span><?php esc_html_e('Sidebar', 'omega-design'); ?></a>
                            <a href="<?php echo esc_url(admin_url('site-editor.php?path=%2Fstyles')); ?>"><span class="dashicons dashicons-art"></span><?php esc_html_e('Global Styles', 'omega-design'); ?></a>
                        </div>
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
                    <h3><?php esc_html_e('Sidebar', 'omega-design'); ?></h3>
                    <span class="omega-badge omega-badge--neutral"><?php esc_html_e('Block-based', 'omega-design'); ?></span>
                    <a class="omega-status-card__link" href="<?php echo esc_url(admin_url(self::sidebar_editor_path())); ?>"><?php esc_html_e('Edit in Site Editor', 'omega-design'); ?></a>
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

                <div class="omega-status-card">
                    <span class="dashicons dashicons-admin-network"></span>
                    <h3><?php esc_html_e('License', 'omega-design'); ?></h3>
                    <?php if ('valid' === $license_state['status']) : ?>
                        <span class="omega-badge omega-badge--success"><?php esc_html_e('Active', 'omega-design'); ?></span>
                        <p class="omega-status-card__meta">
                            <?php if (!empty($license_state['expires_at'])) : ?>
                                <?php printf(esc_html__('Renews/expires %s', 'omega-design'), esc_html(date_i18n(get_option('date_format'), strtotime($license_state['expires_at'])))); ?>
                            <?php else : ?>
                                <?php esc_html_e('Lifetime license', 'omega-design'); ?>
                            <?php endif; ?>
                        </p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="omega_design_deactivate_license" />
                            <input type="hidden" name="omega_license_redirect" value="<?php echo esc_url($current_url); ?>" />
                            <?php wp_nonce_field(\OmegaDesign\core\license::NONCE_ACTION, 'omega_nonce_license'); ?>
                            <button type="submit" class="omega-status-card__link omega-status-card__link--button"><?php esc_html_e('Deactivate on this site', 'omega-design'); ?></button>
                        </form>
                    <?php else : ?>
                        <span class="omega-badge omega-badge--warning"><?php esc_html_e('Not licensed', 'omega-design'); ?></span>
                        <a class="omega-status-card__link" href="#" data-omega-license-open><?php esc_html_e('Activate now', 'omega-design'); ?></a>
                    <?php endif; ?>
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
        $sticky        = (bool) get_theme_mod('omega_header_sticky', false);
        $bg_color      = get_theme_mod('omega_header_bg_color', '');
        $text_color    = get_theme_mod('omega_header_text_color', '');
        $font_family   = get_theme_mod('omega_header_font_family', '');
        $font_size     = get_theme_mod('omega_header_font_size', 'default');
        $height        = get_theme_mod('omega_header_height', 'default');

        // Top-level item titles per classic menu, keyed by term_id, so the
        // preview below can swap its mocked nav items live as the admin
        // changes the "Classic menu to use" select - without an AJAX round
        // trip for what's already a handful of short strings.
        $menu_items_map = [];
        foreach ($classic_menus as $menu) {
            $menu_items_map[$menu->term_id] = self::menu_top_level_titles($menu->term_id, 6);
        }

        // Same maps custom_style_css() uses to build the real front-end
        // !important overrides, reused here so the preview's font-size/
        // height steps land on the exact same values as the live site.
        $font_size_map = [
            'small'   => '0.875rem',
            'medium'  => '1rem',
            'large'   => '1.25rem',
            'x-large' => '1.75rem',
        ];
        $height_map = classic_header::HEIGHT_VALUES;
        ?>
        <?php self::render_settings_form_open('header_nav', 'omega_nonce_header_nav', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-menu-alt2"></span>
                <div>
                    <h2><?php esc_html_e('Header Navigation', 'omega-design'); ?></h2>
                </div>
            </div>

            <div class="omega-field">
                <label><?php esc_html_e('Header style', 'omega-design'); ?></label>
                <?php
                classic_header::render_style_cards(
                    $mode,
                    function ($key) {
                        echo 'name="omega_nav_mode"';
                    }
                );
                ?>
            </div>

            <div class="omega-field">
                <label><?php esc_html_e('Preview', 'omega-design'); ?></label>
                <div class="omega-header-nav-preview">
                    <div
                        id="omega-header-preview-bar"
                        class="omega-header-preview__bar"
                        style="<?php
                            echo $bg_color ? 'background:' . esc_attr($bg_color) . ';' : '';
                            echo $text_color ? 'color:' . esc_attr($text_color) . ';' : '';
                            echo $font_family ? 'font-family:' . esc_attr($font_family) . ';' : '';
                        ?>"
                    >
                        <div
                            id="omega-header-preview-inner"
                            class="omega-header-preview__inner"
                            style="<?php echo isset($height_map[$height]) ? 'padding-top:' . esc_attr($height_map[$height]) . ';padding-bottom:' . esc_attr($height_map[$height]) . ';' : ''; ?>"
                        >
                            <span class="omega-header-preview__logo"><?php echo esc_html(get_bloginfo('name') ?: 'LOGO'); ?></span>
                            <nav
                                id="omega-header-preview-nav"
                                class="omega-header-preview__nav"
                                style="<?php echo isset($font_size_map[$font_size]) ? 'font-size:' . esc_attr($font_size_map[$font_size]) . ';' : ''; ?>"
                            ></nav>
                        </div>
                    </div>
                </div>
                <p class="description"><?php esc_html_e('Reflects the classic menu and appearance overrides below. Applies to Classic styles only.', 'omega-design'); ?></p>
                <script type="application/json" id="omega-header-preview-menus-data"><?php echo wp_json_encode($menu_items_map); ?></script>
            </div>

            <div class="omega-field">
                <label for="omega_classic_menu_id"><?php esc_html_e('Classic menu to use (Classic styles only)', 'omega-design'); ?></label>
                <?php if (!empty($classic_menus)) : ?>
                    <?php self::render_menu_select('omega_classic_menu_id', $classic_menus, $classic_id); ?>
                <?php else : ?>
                    <p class="description"><?php esc_html_e('None yet - create one under Appearance > Menus.', 'omega-design'); ?></p>
                <?php endif; ?>
            </div>

            <?php self::render_toggle('omega_header_sticky', $sticky, __('Sticky header (hides on scroll down, reveals on scroll up)', 'omega-design')); ?>

            <h3><?php esc_html_e('Appearance overrides (Classic styles only)', 'omega-design'); ?></h3>

            <div class="omega-field-row">
                <div class="omega-field">
                    <label for="omega_header_bg_color"><?php esc_html_e('Header background color', 'omega-design'); ?></label>
                    <input type="text" name="omega_header_bg_color" id="omega_header_bg_color" value="<?php echo esc_attr($bg_color); ?>" placeholder="var(--wp--preset--color--background)" />
                    <p class="description"><?php esc_html_e('Hex or CSS variable.', 'omega-design'); ?></p>
                </div>

                <div class="omega-field">
                    <label for="omega_header_text_color"><?php esc_html_e('Header text color', 'omega-design'); ?></label>
                    <input type="text" name="omega_header_text_color" id="omega_header_text_color" value="<?php echo esc_attr($text_color); ?>" placeholder="inherit" />
                    <p class="description"><?php esc_html_e('Hex or CSS variable.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label for="omega_header_font_family"><?php esc_html_e('Header font family', 'omega-design'); ?></label>
                <input type="text" name="omega_header_font_family" id="omega_header_font_family" value="<?php echo esc_attr($font_family); ?>" placeholder="<?php esc_attr_e('theme default', 'omega-design'); ?>" />
                <p class="description"><?php esc_html_e('CSS font-family value.', 'omega-design'); ?></p>
            </div>

            <div class="omega-field-row">
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
        <?php self::render_settings_form_open('announcement', 'omega_nonce_announcement', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-megaphone"></span>
                <div>
                    <h2><?php esc_html_e('Announcement Bar', 'omega-design'); ?></h2>
                </div>
            </div>

            <?php self::render_toggle('omega_announcement_enabled', $enabled, __('Show announcement bar', 'omega-design')); ?>

            <div class="omega-field">
                <label><?php esc_html_e('Preview', 'omega-design'); ?></label>
                <div class="omega-announcement-preview">
                    <div
                        id="omega-announcement-preview-bar"
                        class="omega-announcement-bar"
                        style="<?php echo $bg ? 'background-color:' . esc_attr($bg) . ';' : ''; ?><?php echo $text_color ? 'color:' . esc_attr($text_color) . ';' : ''; ?>"
                    >
                        <div class="omega-announcement-bar__inner">
                            <span id="omega-announcement-preview-content"><?php echo '' !== trim(wp_strip_all_tags($content)) ? $content : esc_html__('Your announcement text appears here…', 'omega-design'); ?></span>
                            <button type="button" class="omega-announcement-bar__dismiss" id="omega-announcement-preview-dismiss" style="<?php echo $dismissible ? '' : 'display:none;'; ?>">&times;</button>
                        </div>
                    </div>
                </div>
                <p class="description"><?php esc_html_e('Updates live as you edit the fields below.', 'omega-design'); ?></p>
            </div>

            <div class="omega-field">
                <label for="omega_announcement_content"><?php esc_html_e('Content (HTML)', 'omega-design'); ?></label>
                <textarea name="omega_announcement_content" id="omega_announcement_content" style="width:100%;height:100px;font-family:monospace;" spellcheck="false"><?php echo esc_textarea($content); ?></textarea>
                <p class="description"><?php esc_html_e('HTML allowed, e.g. links.', 'omega-design'); ?></p>
            </div>

            <div class="omega-field-row">
                <div class="omega-field">
                    <label for="omega_announcement_bg"><?php esc_html_e('Background color', 'omega-design'); ?></label>
                    <input type="text" name="omega_announcement_bg" id="omega_announcement_bg" value="<?php echo esc_attr($bg); ?>" placeholder="var(--wp--preset--color--primary)" />
                    <p class="description"><?php esc_html_e('Hex or CSS variable.', 'omega-design'); ?></p>
                </div>

                <div class="omega-field">
                    <label for="omega_announcement_text_color"><?php esc_html_e('Text color', 'omega-design'); ?></label>
                    <input type="text" name="omega_announcement_text_color" id="omega_announcement_text_color" value="<?php echo esc_attr($text_color); ?>" placeholder="var(--wp--preset--color--button-text)" />
                    <p class="description"><?php esc_html_e('Hex or CSS variable.', 'omega-design'); ?></p>
                </div>
            </div>

            <?php self::render_toggle('omega_announcement_dismissible', $dismissible, __('Let visitors dismiss it', 'omega-design')); ?>

            <?php submit_button(__('Save Announcement Bar', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_announcement', false); ?>
        </form>
        <?php
    }

    /**
     * One select, driven by product_page.php - each option swaps the whole
     * single-product page for a hand-assembled layout built from
     * WooCommerce's own real blocks (variations, stock, cart all keep
     * working; only the surrounding structure and typography differ).
     */
    private function render_product_page_form($redirect_to) {
        $layout          = product_page::active_layout();
        $choices         = product_page::style_choices();
        $related_carousel = (bool) get_theme_mod('omega_related_products_carousel', true);
        ?>
        <?php self::render_settings_form_open('product_page', 'omega_nonce_product_page', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-cart"></span>
                <div>
                    <h2><?php esc_html_e('Product Page Layout', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Choose which layout WooCommerce product pages use. Works for both physical and digital (virtual/downloadable) products - the facts shown adapt automatically.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-layout-picker">
                <?php foreach ($choices as $value => $label) : ?>
                    <?php list($title, $description) = array_pad(explode(' - ', $label, 2), 2, ''); ?>
                    <label class="omega-layout-card <?php echo $layout === $value ? 'is-selected' : ''; ?>">
                        <input type="radio" name="omega_product_page_layout" value="<?php echo esc_attr($value); ?>" <?php checked($layout, $value); ?> />
                        <?php $this->render_layout_preview($value); ?>
                        <span class="omega-layout-card__title"><?php echo esc_html($title); ?></span>
                        <span class="omega-layout-card__desc"><?php echo esc_html($description); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <?php if (!class_exists('WooCommerce')) : ?>
                <p class="description"><?php esc_html_e('WooCommerce is not active - this only takes effect once it is.', 'omega-design'); ?></p>
            <?php endif; ?>

            <h3><?php esc_html_e('Related Products', 'omega-design'); ?></h3>

            <?php self::render_toggle('omega_related_products_carousel', $related_carousel, __('Show related products as a swipeable carousel', 'omega-design')); ?>
            <p class="description"><?php esc_html_e('Uses the same slider engine as the rest of the theme (arrows, touch swipe). Turn off to use WooCommerce\'s plain grid instead.', 'omega-design'); ?></p>

            <?php submit_button(__('Save Product Page Layout', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_product_page', false); ?>
        </form>
        <?php
    }

    /**
     * A small CSS-drawn wireframe per layout (no screenshots to keep in
     * sync) - shape and rough proportion only, so an admin can tell the
     * five apart at a glance before picking one. Real typography/color
     * come from product-page-layouts.css on the actual page.
     */
    private function render_layout_preview($layout) {
        switch ($layout) {
            case 'gallery-feature':
                ?>
                <span class="omega-lp omega-lp--gallery-feature" aria-hidden="true">
                    <span class="omega-lp__photo"></span>
                    <span class="omega-lp__col">
                        <i class="omega-lp__line omega-lp__line--lg"></i>
                        <i class="omega-lp__line"></i>
                        <i class="omega-lp__line omega-lp__line--accent"></i>
                        <i class="omega-lp__btn"></i>
                    </span>
                </span>
                <?php
                break;

            case 'command-deck':
                ?>
                <span class="omega-lp omega-lp--command-deck" aria-hidden="true">
                    <span class="omega-lp__rail"><i></i><i></i><i></i></span>
                    <span class="omega-lp__photo"></span>
                    <span class="omega-lp__panel"><i></i><i></i><i class="omega-lp__btn"></i></span>
                </span>
                <?php
                break;

            case 'split-stage':
                ?>
                <span class="omega-lp omega-lp--split-stage" aria-hidden="true">
                    <span class="omega-lp__half omega-lp__half--dark"></span>
                    <span class="omega-lp__half omega-lp__half--light">
                        <i class="omega-lp__line omega-lp__line--lg"></i>
                        <i class="omega-lp__line"></i>
                        <i class="omega-lp__btn"></i>
                    </span>
                </span>
                <?php
                break;

            case 'spec-sheet':
                ?>
                <span class="omega-lp omega-lp--spec-sheet" aria-hidden="true">
                    <span class="omega-lp__row">
                        <span class="omega-lp__thumb"></span>
                        <span class="omega-lp__col"><i class="omega-lp__line"></i><i class="omega-lp__line omega-lp__line--sm"></i></span>
                    </span>
                    <span class="omega-lp__table"><i></i><i></i><i></i></span>
                </span>
                <?php
                break;

            case 'boutique':
                ?>
                <span class="omega-lp omega-lp--boutique" aria-hidden="true">
                    <span class="omega-lp__circle"></span>
                    <i class="omega-lp__line omega-lp__line--center"></i>
                    <i class="omega-lp__line omega-lp__line--center omega-lp__line--sm"></i>
                    <i class="omega-lp__btn omega-lp__btn--pill"></i>
                </span>
                <?php
                break;
        }
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
        $tagline       = get_theme_mod('omega_footer_tagline', '');
        $copyright     = get_theme_mod('omega_footer_copyright', '');
        $cta_label     = get_theme_mod('omega_footer_cta_label', '');
        $cta_url       = get_theme_mod('omega_footer_cta_url', '');
        ?>
        <?php self::render_settings_form_open('footer', 'omega_nonce_footer', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-align-center"></span>
                <div>
                    <h2><?php esc_html_e('Footer', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Choose which footer actually shows to visitors: the block-based one from the Site Editor, or one of 5 fast, mobile-friendly classic layouts driven by a classic menu.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label><?php esc_html_e('Footer style', 'omega-design'); ?></label>
                <?php
                classic_footer::render_style_cards(
                    $mode,
                    function ($key) {
                        echo 'name="omega_footer_mode"';
                    }
                );
                ?>
            </div>

            <div class="omega-field">
                <label for="omega_classic_footer_menu_id"><?php esc_html_e('Classic menu to use (Classic styles only)', 'omega-design'); ?></label>
                <?php if (empty($classic_menus)) : ?>
                    <p class="description"><?php esc_html_e('No classic menus yet. Create one under Appearance > Menus first.', 'omega-design'); ?></p>
                <?php else : ?>
                    <?php self::render_menu_select('omega_classic_footer_menu_id', $classic_menus, $classic_id); ?>
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

    const BUTTON_RADIUS_CHOICES = [
        'sharp'   => '2px',
        'soft'    => '6px',
        'rounded' => '14px',
        'pill'    => '999px',
    ];

    /**
     * These names must match the "omega-{name}" style names registered in
     * block_style_variations::register_styles() - a default look reuses
     * exactly the same CSS rule as the matching per-button Style choice,
     * just scoped to a body class instead (see block-style-variations.css).
     */
    const BUTTON_LOOK_CHOICES = [
        'fill'  => 'Fill (default)',
        'ghost' => 'Ghost',
        'soft'  => 'Soft',
        'pill'  => 'Pill',
        '3d'    => '3D',
    ];

    /**
     * Site-wide design defaults: how new/unstyled Button blocks look
     * (corner rounding + overall look), and whether admins can upload SVGs
     * at all (includes/core/svg_upload.php already restricts SVG uploads to
     * administrators - this is the on/off switch for that capability, not
     * a role change). Everything here only ever affects the *default*
     * appearance - a block with its own explicit Style or a custom corner
     * radius the admin set by hand always wins over these.
     */
    private function render_color_scheme_form($redirect_to) {
        $scheme_obj = color_scheme::get_instance();
        $current = $scheme_obj->get_scheme_key();
        ?>
        <?php self::render_settings_form_open('color_scheme', 'omega_nonce_color_scheme', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-admin-customizer"></span>
                <div>
                    <h2><?php esc_html_e('Color Scheme', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Applies everywhere - header, footer, buttons, Shop page, every landing page.', 'omega-design'); ?></p>
                </div>
            </div>

            <?php
            color_scheme::render_scheme_cards(
                $scheme_obj->get_schemes(),
                $current,
                function ($key) {
                    echo 'name="omega_color_scheme"';
                }
            );
            ?>

            <?php submit_button(__('Save Color Scheme', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_color_scheme', false); ?>
        </form>
        <?php
    }

    /**
     * Site-wide heading/body font override - see typography.php, which
     * owns the theme mods, sanitization, front-end CSS output and the
     * matching Customizer section; this form just POSTs the same two
     * theme mods through the Settings page's own admin-post.php flow.
     */
    private function render_typography_form($redirect_to) {
        $heading_font = get_theme_mod(typography::HEADING_MOD, '');
        $body_font    = get_theme_mod(typography::BODY_MOD, '');
        ?>
        <?php self::render_settings_form_open('typography', 'omega_nonce_typography', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-editor-textcolor"></span>
                <div>
                    <h2><?php esc_html_e('Typography', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Applies to any heading or body text that hasn\'t been given its own explicit font in the block editor - an explicit per-block choice always overrides this.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label for="omega_heading_font"><?php esc_html_e('Heading font', 'omega-design'); ?></label>
                <?php
                typography::render_font_picker(
                    $heading_font,
                    function () {
                        echo 'name="omega_heading_font"';
                    },
                    __('Build Your Dream', 'omega-design'),
                    'heading',
                    'omega_heading_font'
                );
                ?>
            </div>

            <div class="omega-field">
                <label for="omega_body_font"><?php esc_html_e('Body font', 'omega-design'); ?></label>
                <?php
                typography::render_font_picker(
                    $body_font,
                    function () {
                        echo 'name="omega_body_font"';
                    },
                    __('The quick brown fox jumps over the lazy dog.', 'omega-design'),
                    'body',
                    'omega_body_font'
                );
                ?>
            </div>

            <?php submit_button(__('Save Typography', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_typography', false); ?>
        </form>
        <?php
    }

    private function render_design_form($redirect_to) {
        $radius       = get_theme_mod('omega_button_radius', 'soft');
        if (!array_key_exists($radius, self::BUTTON_RADIUS_CHOICES)) {
            $radius = 'soft';
        }
        $look         = get_theme_mod('omega_button_look', 'fill');
        if (!array_key_exists($look, self::BUTTON_LOOK_CHOICES)) {
            $look = 'fill';
        }
        $svg_uploads  = (bool) get_theme_mod('omega_svg_uploads_enabled', true);
        ?>
        <?php self::render_settings_form_open('design', 'omega_nonce_design', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-art"></span>
                <div>
                    <h2><?php esc_html_e('Site-wide Button Defaults', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('Applies to any Button block that hasn\'t been given its own Style or custom radius in the block editor - an explicit per-button choice always overrides this.', 'omega-design'); ?></p>
                </div>
            </div>

            <div class="omega-field">
                <label><?php esc_html_e('Corner style', 'omega-design'); ?></label>
                <?php
                buttons::render_radius_cards(
                    $radius,
                    function ($key) {
                        echo 'name="omega_button_radius"';
                    }
                );
                ?>
            </div>

            <div class="omega-field">
                <label><?php esc_html_e('Default look', 'omega-design'); ?></label>
                <?php
                buttons::render_look_cards(
                    $look,
                    function ($key) {
                        echo 'name="omega_button_look"';
                    }
                );
                ?>
                <p class="description"><?php esc_html_e('The same looks available per-button under Styles in the block editor.', 'omega-design'); ?></p>
            </div>

            <h3><?php esc_html_e('Media', 'omega-design'); ?></h3>

            <?php self::render_toggle('omega_svg_uploads_enabled', $svg_uploads, __('Allow administrators to upload SVG images', 'omega-design')); ?>
            <p class="description"><?php esc_html_e('Every SVG is still sanitized on upload either way - this only controls whether the option exists at all. Editors and other roles can never upload SVGs regardless of this setting.', 'omega-design'); ?></p>

            <?php submit_button(__('Save Design Settings', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_design', false); ?>
        </form>
        <?php
    }

    /**
     * Section map for the tabbed Settings screen: id => [label, icon]. One
     * panel per tab, switched client-side (assets/js/admin-settings-tabs.js)
     * so nothing here needs a page reload to navigate - each individual
     * form inside still POSTs to admin-post.php exactly as before and
     * redirects back to #<tab> so the save lands back on the same panel.
     */
    private function settings_tabs() {
        return [
            'general'     => [__('General', 'omega-design'), 'admin-generic'],
            'design'      => [__('Design', 'omega-design'), 'art'],
            'header'      => [__('Header & Announcement', 'omega-design'), 'menu-alt2'],
            'menus'       => [__('Menus & Pages', 'omega-design'), 'admin-page'],
            'footer'      => [__('Footer', 'omega-design'), 'align-center'],
            'woocommerce' => [__('WooCommerce', 'omega-design'), 'cart'],
            'license'     => [__('License', 'omega-design'), 'admin-network'],
        ];
    }

    public function settings_page() {
        $current_url = admin_url('admin.php?page=omega-settings');
        $status = $this->get_status_data();
        $tabs = $this->settings_tabs();
        // Each form's own redirect target includes its tab's hash, so a
        // save (a full server round-trip through admin-post.php) lands the
        // admin back on the same panel instead of always resetting to
        // General - the hash rides along on the Location header even
        // though it's never sent back to the server on the POST itself.
        $tab_url = function ($tab) use ($current_url) {
            return $current_url . '#' . $tab;
        };
        ?>
        <div class="wrap omega-admin-page <?php echo esc_attr($this->color_mode_class()); ?>">
            <?php $this->render_header(__('All Omega Design options in one place.', 'omega-design')); ?>

            <div class="omega-settings-shell">
                <nav class="omega-settings-nav" aria-label="<?php esc_attr_e('Settings sections', 'omega-design'); ?>">
                    <?php foreach ($tabs as $id => [$label, $icon]) : ?>
                        <a href="#<?php echo esc_attr($id); ?>" class="omega-settings-nav__item" data-tab="<?php echo esc_attr($id); ?>">
                            <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                            <?php echo esc_html($label); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="omega-settings-panels">

                    <section class="omega-settings-panel" data-panel="general">
                        <div class="omega-grid omega-grid--2">
                            <?php $this->render_logo_form($tab_url('general')); ?>

                            <div class="omega-stack">
                            <?php $this->render_color_mode_form($tab_url('general')); ?>

                            <?php $this->render_sidebar_form($tab_url('general')); ?>
                            </div>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="design">
                        <div class="omega-grid omega-grid--2">
                            <?php $this->render_color_scheme_form($tab_url('design')); ?>
                            <?php $this->render_typography_form($tab_url('design')); ?>
                            <?php $this->render_design_form($tab_url('design')); ?>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="header">
                        <div class="omega-grid omega-grid--2">
                            <?php $this->render_header_nav_form($tab_url('header')); ?>
                            <?php $this->render_announcement_form($tab_url('header')); ?>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="menus">
                        <div class="omega-grid omega-grid--2">
                            <?php $this->render_megamenu_card($status); ?>

                            <?php $this->render_page_settings_card(); ?>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="footer">
                        <div class="omega-grid omega-grid--1">
                            <?php $this->render_footer_form($tab_url('footer')); ?>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="woocommerce">
                        <div class="omega-grid omega-grid--2">
                            <?php $this->render_product_page_form($tab_url('woocommerce')); ?>
                            <?php $this->render_woocommerce_pages_card(); ?>
                        </div>
                    </section>

                    <section class="omega-settings-panel" data-panel="license">
                        <div class="omega-grid omega-grid--1">
                            <?php \OmegaDesign\core\license::get_instance()->render_settings_panel($tab_url('license')); ?>
                        </div>
                    </section>

                </div>
            </div>
        </div>
        <?php
    }

    private function render_sidebar_form($redirect_to) {
        $sidebar                  = sidebar::get_instance();
        $sidebar_enabled          = $sidebar->is_sidebar_enabled();
        $sidebar_position         = $sidebar->get_sidebar_position();
        $sidebar_width            = (int) get_theme_mod('omega_sidebar_width', 30);
        $sidebar_default_template = get_theme_mod('omega_sidebar_default_template', 'sidebar');
        ?>
        <?php self::render_settings_form_open('sidebar', 'omega_nonce_sidebar', $redirect_to, 'omega-card'); ?>

            <div class="omega-card__head">
                <span class="dashicons dashicons-align-right"></span>
                <div>
                    <h2><?php esc_html_e('Sidebar', 'omega-design'); ?></h2>
                </div>
            </div>

            <?php self::render_toggle('omega_enable_sidebar', $sidebar_enabled, __('Enable sidebar', 'omega-design')); ?>
            <p class="description"><?php esc_html_e('Master switch for every page type below. A single post or page can still override this from its own Page Settings panel in the editor.', 'omega-design'); ?></p>

            <div class="omega-field">
                <label><?php esc_html_e('Position', 'omega-design'); ?></label>
                <?php
                sidebar::render_position_cards(
                    $sidebar_position,
                    function ($key) {
                        echo 'name="omega_sidebar_position"';
                    }
                );
                ?>
            </div>

            <div class="omega-field">
                <label for="omega_sidebar_width"><?php esc_html_e('Width', 'omega-design'); ?> (<span id="omega_sidebar_width_value"><?php echo esc_html($sidebar_width); ?></span>%)</label>
                <input type="range" min="20" max="50" step="1" name="omega_sidebar_width" id="omega_sidebar_width" value="<?php echo esc_attr($sidebar_width); ?>" oninput="document.getElementById('omega_sidebar_width_value').textContent = this.value;" />
            </div>

            <div class="omega-field">
                <label for="omega_sidebar_default_template"><?php esc_html_e('Default Sidebar Content', 'omega-design'); ?></label>
                <select name="omega_sidebar_default_template" id="omega_sidebar_default_template">
                    <?php foreach ($sidebar->get_template_choices() as $slug => $label) : ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($sidebar_default_template, $slug); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p class="omega-field-heading"><strong><?php esc_html_e('Show sidebar on:', 'omega-design'); ?></strong></p>
            <?php foreach ($sidebar->get_location_labels() as $option_name => $meta) : ?>
                <?php self::render_toggle($option_name, (bool) get_theme_mod($option_name, $meta['default']), $meta['label']); ?>
                <?php if ($meta['description']) : ?>
                    <p class="description"><?php echo esc_html($meta['description']); ?></p>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php submit_button(__('Save Sidebar Settings', 'omega-design'), 'omega-btn omega-btn--primary', 'omega_submit_sidebar', false); ?>
        </form>
        <?php
    }

    /**
     * An on/off switch styled checkbox.
     */
    private static function render_toggle($name, $is_on, $label) {
        ?>
        <label class="omega-toggle">
            <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($is_on); ?> />
            <span class="omega-toggle__track"><span class="omega-toggle__thumb"></span></span>
            <span class="omega-toggle__label"><?php echo esc_html($label); ?></span>
        </label>
        <?php
    }

    private function render_page_settings_card() {
        ?>
        <div class="omega-card">
            <div class="omega-card__head">
                <span class="dashicons dashicons-heading"></span>
                <div>
                    <h2><?php esc_html_e('Page & Post Settings', 'omega-design'); ?></h2>
                </div>
            </div>
            <p class="description"><?php esc_html_e('Set per page/post in the editor sidebar.', 'omega-design'); ?></p>
            <ul class="omega-tip-list">
                <li><?php esc_html_e('Page Width (Normal/Wide/Full) lives in the same Page Settings panel.', 'omega-design'); ?></li>
                <li><?php esc_html_e('Sidebar visibility per-page is set from that page\'s own block settings.', 'omega-design'); ?></li>
            </ul>
            <p>
                <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Go to Pages', 'omega-design'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * The 4 WooCommerce core pages (Shop, Cart, Checkout, My Account), each
     * with a real status badge - not just links, since a missing/trashed
     * page here is a genuine storefront-breaking problem worth surfacing.
     */
    private function render_woocommerce_pages_card() {
        ?>
        <div class="omega-card">
            <div class="omega-card__head">
                <span class="dashicons dashicons-store"></span>
                <div>
                    <h2><?php esc_html_e('WooCommerce Pages', 'omega-design'); ?></h2>
                    <p><?php esc_html_e('The core pages every store needs, and whether each one is actually set and published.', 'omega-design'); ?></p>
                </div>
            </div>

            <?php if (!class_exists('WooCommerce')) : ?>
                <div class="omega-empty-state">
                    <span class="dashicons dashicons-cart"></span>
                    <p><?php esc_html_e('WooCommerce isn\'t active yet - install and activate it to see this store\'s page status.', 'omega-design'); ?></p>
                </div>
            <?php else : ?>
                <ul class="omega-resource-list">
                    <?php
                    $pages = [
                        'shop'      => __('Shop', 'omega-design'),
                        'cart'      => __('Cart', 'omega-design'),
                        'checkout'  => __('Checkout', 'omega-design'),
                        'myaccount' => __('My Account', 'omega-design'),
                    ];
                    foreach ($pages as $key => $label) :
                        $page_id = wc_get_page_id($key);
                        $page    = $page_id > 0 ? get_post($page_id) : null;
                        $is_live = $page && 'publish' === $page->post_status;
                        ?>
                        <li>
                            <span class="omega-resource-list__name">
                                <?php echo esc_html($label); ?>
                                <?php if ($is_live) : ?>
                                    <span class="omega-badge omega-badge--success"><?php esc_html_e('Live', 'omega-design'); ?></span>
                                <?php else : ?>
                                    <span class="omega-badge omega-badge--danger"><?php esc_html_e('Not set', 'omega-design'); ?></span>
                                <?php endif; ?>
                            </span>
                            <?php if ($is_live) : ?>
                                <a class="omega-resource-list__edit" href="<?php echo esc_url(get_edit_post_link($page_id)); ?>"><?php esc_html_e('Edit', 'omega-design'); ?></a>
                            <?php else : ?>
                                <a class="omega-resource-list__edit" href="<?php echo esc_url(admin_url('edit.php?post_type=page&page=wc-settings&tab=advanced')); ?>"><?php esc_html_e('Fix', 'omega-design'); ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p>
                    <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=advanced')); ?>"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Page Setup (WooCommerce)', 'omega-design'); ?></a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Mega Menu status card - lists the actually-published mega menus (not
     * just a count) with a direct edit link each, so the card carries real
     * information instead of sitting mostly empty next to Header
     * Navigation's long form.
     */
    private function render_megamenu_card($status) {
        $menus = post_type_exists('mega_menu')
            ? get_posts(['post_type' => 'mega_menu', 'post_status' => 'publish', 'numberposts' => 6, 'orderby' => 'title', 'order' => 'ASC'])
            : [];
        ?>
        <div class="omega-card">
            <div class="omega-card__head">
                <span class="dashicons dashicons-menu-alt3"></span>
                <div>
                    <h2><?php esc_html_e('Mega Menu', 'omega-design'); ?></h2>
                </div>
            </div>
            <p class="description"><?php esc_html_e('Attach one to a nav item from the Site Editor.', 'omega-design'); ?></p>

            <?php if (!empty($menus)) : ?>
                <ul class="omega-resource-list">
                    <?php foreach ($menus as $menu) : ?>
                        <li>
                            <span class="omega-resource-list__name"><?php echo esc_html($menu->post_title ?: __('(no title)', 'omega-design')); ?></span>
                            <a class="omega-resource-list__edit" href="<?php echo esc_url(get_edit_post_link($menu->ID)); ?>"><?php esc_html_e('Edit', 'omega-design'); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($status['megamenu_count'] > count($menus)) : ?>
                    <p class="omega-status-card__meta"><?php echo esc_html(sprintf(__('+%d more', 'omega-design'), $status['megamenu_count'] - count($menus))); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <div class="omega-empty-state">
                    <span class="dashicons dashicons-menu-alt3"></span>
                    <p><?php esc_html_e('No mega menus yet. Build your first one to attach rich dropdown content to any nav item.', 'omega-design'); ?></p>
                </div>
            <?php endif; ?>

            <p>
                <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=mega_menu')); ?>"><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Manage Mega Menus', 'omega-design'); ?></a>
                <a class="omega-btn omega-btn--ghost" href="<?php echo esc_url(admin_url('site-editor.php?p=/navigation')); ?>"><span class="dashicons dashicons-edit-large"></span> <?php esc_html_e('Manage Navigation', 'omega-design'); ?></a>
            </p>
        </div>
        <?php
    }
}
