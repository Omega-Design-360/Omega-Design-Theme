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

        if (is_admin_bar_showing()) {
            wp_add_inline_style('omega-design-style', $this->get_admin_bar_logo_css());
        }

        if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url() && !is_user_logged_in()) {
            $this->enqueue_checkout_auth_notice();
        }

        if (function_exists('is_cart') && is_cart()) {
            $this->enqueue_cart_page_assets();
        }

        if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url()) {
            $this->enqueue_checkout_page_assets();
        }
    }

    private function enqueue_cart_page_assets() {
        $css_path = OMEGA_DESIGN_ASSETS . '/css/cart-page.css';
        if (file_exists($css_path)) {
            wp_enqueue_style('omega-design-cart-page', OMEGA_DESIGN_CSS_URI . '/cart-page.css', [], filemtime($css_path));
        }

        $js_path = OMEGA_DESIGN_ASSETS . '/js/cart-page-interactions.js';
        if (file_exists($js_path)) {
            wp_enqueue_script('omega-design-cart-page', OMEGA_DESIGN_JS_URI . '/cart-page-interactions.js', [], filemtime($js_path), true);
        }
    }

    private function enqueue_checkout_page_assets() {
        $css_path = OMEGA_DESIGN_ASSETS . '/css/checkout-page.css';
        if (file_exists($css_path)) {
            wp_enqueue_style('omega-design-checkout-page', OMEGA_DESIGN_CSS_URI . '/checkout-page.css', [], filemtime($css_path));
        }

        $js_path = OMEGA_DESIGN_ASSETS . '/js/checkout-page-interactions.js';
        if (file_exists($js_path)) {
            wp_enqueue_script('omega-design-checkout-page', OMEGA_DESIGN_JS_URI . '/checkout-page-interactions.js', [], filemtime($js_path), true);
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

    const LINK_BLOCKS = ['core/group', 'core/columns', 'core/column'];

    public function modify_block_render($block_content, $block) {
        $name = $block['blockName'] ?? '';

        if (in_array($name, self::LINK_BLOCKS, true)) {
            $block_content = $this->add_block_link_overlay($block_content, $block);
        }

        if ($name === 'core/site-logo') {
            $block_content = $this->inject_dark_mode_logo($block_content, $block);
        }

        if ($name === 'core/icon') {
            $block_content = $this->add_icon_hover_style($block_content, $block);
        }

        if ($name === 'core/html') {
            $block_content = $this->render_product_badges($block_content);
        }

        return $block_content;
    }

    const PRODUCT_BADGE_ICONS = [
        'bolt'    => '<path d="M13 3 6 13h5l-1 8 8-11h-5z"/>',
        'truck'   => '<path d="M3 7h10v9H3z"/><path d="M13 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'shield'  => '<path d="M12 3 20 6v6c0 4.6-3.3 7.9-8 9-4.7-1.1-8-4.4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'refresh' => '<path d="M4 12a8 8 0 1 1 2.5 5.8"/><path d="M4 12V7m0 5h5"/>',
    ];

    /**
     * templates/single-product.html marks its trust-badges row with a
     * data-omega-product-badges placeholder <div> (a core/html block, since
     * that's a static template shared by every product) - this fills it in
     * per-request with copy that actually matches the product being viewed,
     * rather than one hardcoded shipping-flavored set that would misdescribe
     * a virtual/downloadable product (no dispatch, no physical returns).
     */
    private function render_product_badges($block_content) {
        if (strpos($block_content, 'data-omega-product-badges') === false) {
            return $block_content;
        }

        $product_id = get_the_ID();
        $product    = $product_id ? wc_get_product($product_id) : null;
        if (!$product) {
            return $block_content;
        }

        $is_digital = $product->is_virtual() || $product->is_downloadable();

        $badges = $is_digital
            ? [
                ['bolt', __('Instant delivery', 'omega-design')],
                ['shield', __('Secure checkout', 'omega-design')],
                ['refresh', __('Free lifetime updates', 'omega-design')],
            ]
            : [
                ['truck', __('Fast dispatch', 'omega-design')],
                ['shield', __('Secure checkout', 'omega-design')],
                ['refresh', __('Easy 30-day returns', 'omega-design')],
            ];

        $items = '';
        foreach ($badges as [$icon, $label]) {
            $path   = self::PRODUCT_BADGE_ICONS[$icon] ?? '';
            $items .= '<li class="omega-pdp__badge">'
                . '<svg class="omega-pdp__badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>'
                . '<span>' . esc_html($label) . '</span>'
                . '</li>';
        }

        return '<ul class="omega-pdp__badges">' . $items . '</ul>';
    }

    /**
     * core/icon is a dynamic block (WP core's render_block_core_icon(),
     * see wp-includes/blocks/icon.php) - its front end never uses the JS
     * save() output that assets/js/editor.js's "Hover Colors & Shadow" panel
     * (extended to this block there) normally bakes hover styling into for
     * static blocks like Group/Columns. So the same attributes.style.
     * omegaHover value is re-read here and applied directly to the rendered
     * wrapper <div>, independently of what save() produced.
     */
    private function add_icon_hover_style($block_content, $block) {
        $hover = $block['attrs']['style']['omegaHover'] ?? [];
        if (empty($hover)) {
            return $block_content;
        }

        $vars = [
            'text'       => '--omega-hover-text-color',
            'background' => '--omega-hover-bg-color',
            'border'     => '--omega-hover-border-color',
        ];

        $style = '';
        foreach ($vars as $key => $css_var) {
            if (!empty($hover[$key])) {
                $style .= $css_var . ':' . $hover[$key] . ';';
            }
        }

        if (!empty($hover['shadowSize'])) {
            $offset_x     = $hover['shadowOffsetX'] ?? 0;
            $offset_y     = $hover['shadowOffsetY'] ?? round($hover['shadowSize'] / 3);
            $shadow_color = $hover['shadowColor'] ?? 'rgba(0, 0, 0, 0.35)';
            $style       .= '--omega-hover-shadow:' . $offset_x . 'px ' . $offset_y . 'px ' . $hover['shadowSize'] . 'px ' . $shadow_color . ';';
        }

        if ('' === $style) {
            return $block_content;
        }

        if (!preg_match('/^(\s*<[a-z0-9]+)([^>]*)(>)/i', $block_content, $matches)) {
            return $block_content;
        }

        $tag_open  = $matches[1];
        $tag_attrs = $matches[2];
        $tag_close = $matches[3];

        if (preg_match('/\sclass="/i', $tag_attrs)) {
            $tag_attrs = preg_replace('/\sclass="/i', ' class="has-omega-hover-color ', $tag_attrs, 1);
        } else {
            $tag_attrs .= ' class="has-omega-hover-color"';
        }

        if (preg_match('/\sstyle="/i', $tag_attrs)) {
            $tag_attrs = preg_replace('/\sstyle="/i', ' style="' . esc_attr($style) . ' ', $tag_attrs, 1);
        } else {
            $tag_attrs .= ' style="' . esc_attr($style) . '"';
        }

        return $tag_open . $tag_attrs . $tag_close . substr($block_content, strlen($matches[0]));
    }

    /**
     * Wraps a Group/Row/Grid/Columns/Column block's rendered markup with a
     * full-cover overlay <a> when an admin has set a Link URL on it in the
     * Inspector (assets/js/editor.js "Link" panel), so the entire block -
     * not just a heading or button inside it - is clickable.
     *
     * Inserted as an absolutely positioned overlay (assets/css/style.css)
     * rather than wrapping the block's own markup in an <a>, so a card that
     * already has its own inner link/button never ends up as invalid
     * nested-<a> HTML; anything inside that still needs its own click target
     * just needs a higher z-index to sit above the overlay.
     */
    private function add_block_link_overlay($block_content, $block) {
        $link = $block['attrs']['style']['omegaLink'] ?? [];
        $url  = trim((string) ($link['url'] ?? ''));
        if ('' === $url) {
            return $block_content;
        }

        $href = esc_url($url);
        if ('' === $href) {
            return $block_content;
        }

        if (!preg_match('/^(\s*<[a-z0-9]+)([^>]*)(>)/i', $block_content, $matches)) {
            return $block_content;
        }

        $label = trim((string) ($link['label'] ?? ''));
        if ('' === $label) {
            $label = trim(wp_strip_all_tags($block_content));
            if (strlen($label) > 100) {
                $label = rtrim(substr($label, 0, 100)) . '…';
            }
        }

        $link_attrs = ' href="' . $href . '" class="omega-block-link"';
        if ('_blank' === ($link['target'] ?? '')) {
            $link_attrs .= ' target="_blank" rel="noopener noreferrer"';
        }
        if ('' !== $label) {
            $link_attrs .= ' aria-label="' . esc_attr($label) . '"';
        }

        $tag_open  = $matches[1];
        $tag_attrs = $matches[2];
        $tag_close = $matches[3];

        if (preg_match('/\sclass="/i', $tag_attrs)) {
            $tag_attrs = preg_replace('/\sclass="/i', ' class="omega-has-block-link ', $tag_attrs, 1);
        } else {
            $tag_attrs .= ' class="omega-has-block-link"';
        }

        $overlay = '<a' . $link_attrs . '></a>';

        return $tag_open . $tag_attrs . $tag_close . $overlay . substr($block_content, strlen($matches[0]));
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