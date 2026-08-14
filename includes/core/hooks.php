<?php
/**
 * Hooks Class
 * 
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class hooks {

    private static $instance = null;
    private $actions = [];
    private $filters = [];
    private $registered = false;

    private function __construct() {
        $this->init_core_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        $this->register_hooks();
    }

    private function init_core_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('after_setup_theme', [$this, 'theme_setup']);
        add_filter('block_categories_all', [$this, 'register_block_categories'], 10, 2);
        add_filter('render_block', [$this, 'modify_block_render'], 10, 2);
    }

    private function register_hooks() {
        if ($this->registered) {
            return;
        }

        $this->register_actions();
        $this->register_filters();
        $this->registered = true;
    }

    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    private function register_actions() {
        foreach ($this->actions as $action) {
            $cb = $this->resolve_callback($action['callback']);
            if ($cb) {
                add_action($action['hook'], $cb, $action['priority'], $action['accepted_args']);
            }
        }
    }

    private function register_filters() {
        foreach ($this->filters as $filter) {
            $cb = $this->resolve_callback($filter['callback']);
            if ($cb) {
                add_filter($filter['hook'], $cb, $filter['priority'], $filter['accepted_args']);
            }
        }
    }

    private function resolve_callback($callback) {
        if (is_callable($callback)) {
            return $callback;
        }

        if (is_array($callback) && count($callback) === 2) {
            list($class, $method) = $callback;

            if (is_object($class) && method_exists($class, $method)) {
                return [$class, $method];
            }

            if (is_string($class) && class_exists($class)) {
                if (method_exists($class, 'get_instance')) {
                    $instance = $class::get_instance();
                    return method_exists($instance, $method) ? [$instance, $method] : null;
                }
                
                if (method_exists($class, $method)) {
                    return [$class, $method];
                }
            }
        }

        return null;
    }

    public function theme_setup() {
        load_theme_textdomain(OMEGA_DESIGN_TEXTDOMAIN, OMEGA_DESIGN_DIR . '/languages');
        add_theme_support('wp-block-styles');
        add_theme_support('responsive-embeds');
        add_theme_support('editor-styles');
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
        add_theme_support('custom-logo', [
            'height'      => 60,
            'width'       => 200,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => ['site-title', 'site-description'],
        ]);
    }

    public function register_assets() {
        wp_register_style('omega-design-style', OMEGA_DESIGN_CSS_URI . '/style.css', [], $this->asset_version('/css/style.css'));
        wp_register_script('omega-design-script', OMEGA_DESIGN_JS_URI . '/main.js', ['jquery'], OMEGA_DESIGN_VERSION, true);
    }

    /**
     * filemtime()-based version for assets under active development, so a
     * saved edit is picked up on the next load instead of being served from
     * the browser's cache under the same static OMEGA_DESIGN_VERSION query
     * string until a manual version bump.
     */
    private function asset_version($relative_path) {
        $path = OMEGA_DESIGN_ASSETS . '/' . ltrim($relative_path, '/');
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    public function enqueue_frontend_assets() {
        $this->register_assets();
        wp_enqueue_style('omega-design-style');
        wp_enqueue_script('omega-design-script');

        if (is_admin_bar_showing()) {
            wp_add_inline_style('omega-design-style', $this->get_admin_bar_logo_css());
        }

        if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url() && !is_user_logged_in()) {
            $this->enqueue_checkout_auth_notice();
        }
    }

    /**
     * The woocommerce/checkout block renders its "must be logged in" prompt
     * entirely client-side from a compiled JS bundle (no PHP template or
     * filter hook available), so it's restyled in the DOM after the fact
     * instead. See assets/js/checkout-auth-notice.js.
     */
    private function enqueue_checkout_auth_notice() {
        wp_enqueue_script(
            'omega-design-checkout-auth-notice',
            OMEGA_DESIGN_JS_URI . '/checkout-auth-notice.js',
            [],
            $this->asset_version('/js/checkout-auth-notice.js'),
            true
        );

        $checkout_url = wc_get_checkout_url();

        // The "My Login Form" plugin (if active) replaces WooCommerce's native
        // account/login/register pages with its own; wp_login_url() already
        // resolves to its login page via that plugin's 'login_url' filter, but
        // registration lives on a separate page it tracks in its own option,
        // independent of WooCommerce's "enable myaccount registration" setting.
        $login_url = wp_login_url($checkout_url);

        $register_page_id = (int) get_option('my_login_form_register_page_id');
        if ($register_page_id && 'publish' === get_post_status($register_page_id)) {
            $show_register = true;
            $register_url  = get_permalink($register_page_id);
        } elseif (function_exists('my_login_form_registration_url')) {
            $show_register = false;
            $register_url  = my_login_form_registration_url();
        } else {
            $show_register = 'yes' === get_option('woocommerce_enable_myaccount_registration');
            $register_url  = wc_get_page_permalink('myaccount');
        }

        wp_localize_script('omega-design-checkout-auth-notice', 'omegaCheckoutAuth', [
            'loginUrl'      => $login_url,
            'registerUrl'   => $register_url,
            'showRegister'  => $show_register,
            'title'         => __('Please log in to continue', 'omega-design'),
            'message'       => __("You'll need to log in to complete your order. Sign in if you already have an account, or create a new one — it only takes a minute.", 'omega-design'),
            'loginLabel'    => __('Log in now', 'omega-design'),
            'registerLabel' => __('Create an account', 'omega-design'),
        ]);
    }

    public function enqueue_admin_assets($hook) {
        wp_enqueue_style('omega-design-admin', OMEGA_DESIGN_CSS_URI . '/admin.css', [], OMEGA_DESIGN_VERSION);
        wp_add_inline_style('omega-design-admin', $this->get_admin_bar_logo_css());
    }

    /**
     * CSS that swaps the default WordPress logo in the admin toolbar
     * for the theme icon at assets/icons/Omega-Design.png.
     */
    private function get_admin_bar_logo_css() {
        $icon_url = esc_url(omega_design_versioned_asset_url('/icons/Omega-Design.png'));

        return "
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon {
                background-image: url('{$icon_url}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 20px 20px;
            }
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                content: '';
            }
        ";
    }

    public function enqueue_block_assets() {
        $this->register_assets();
        wp_enqueue_style('omega-design-style');
    }

    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'omega-design-editor',
            OMEGA_DESIGN_JS_URI . '/editor.js',
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n', 'wp-dom-ready', 'wp-data', 'wp-core-data'],
            $this->asset_version('/js/editor.js'),
            true
        );
    }

    public function register_block_categories($categories, $post) {
        $categories[] = [
            'slug'  => 'omega-design',
            'title' => __('Omega Design', 'omega-design'),
        ];
        return $categories;
    }

    public function modify_block_render($block_content, $block) {
        if ($block['blockName'] === 'core/group') {
            // Add custom classes if needed
        }

        if ($block['blockName'] === 'core/site-logo') {
            $block_content = $this->inject_dark_mode_logo($block_content, $block);
        }

        return $block_content;
    }

    /**
     * core/site-logo only ever renders the single image behind the
     * `custom_logo` theme mod. When a dark mode logo has been set from the
     * Omega Design dashboard, this duplicates the rendered <img> into an
     * .omega-logo-light/.omega-logo-dark pair so color-mode.css can toggle
     * between them using the same .omega-color-mode-* / prefers-color-scheme
     * rules already used for the rest of the palette.
     */
    private function inject_dark_mode_logo($block_content, $block) {
        $dark_logo_id = (int) get_theme_mod('omega_custom_logo_dark');

        if (!$dark_logo_id || strpos($block_content, 'custom-logo') === false) {
            return $block_content;
        }

        $width = isset($block['attrs']['width']) ? (int) $block['attrs']['width'] : 0;
        $dark_image = wp_get_attachment_image($dark_logo_id, 'full', false, [
            'class' => 'custom-logo omega-logo-dark',
            'style' => $width ? sprintf('width:%dpx;height:auto;', $width) : '',
        ]);

        $with_dark_logo = preg_replace_callback(
            '/<img\b[^>]*\bclass="[^"]*\bcustom-logo\b[^"]*"[^>]*\/?>/i',
            function ($matches) use ($dark_image) {
                $light_image = preg_replace('/class="([^"]*)"/', 'class="$1 omega-logo-light"', $matches[0], 1);
                return $light_image . $dark_image;
            },
            $block_content,
            1
        );

        return null !== $with_dark_logo ? $with_dark_logo : $block_content;
    }
}