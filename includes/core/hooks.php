<?php
/**
 * Hooks Class
 * 
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\customizer\menus;
use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class hooks {

    use singleton;
    use assets;

    private $actions = [];
    private $filters = [];
    private $registered = false;

    private function __construct() {
        $this->init_core_hooks();
    }

    /**
     * "6 min read" instead of core's "6 minutes" for Time to Read blocks
     * carrying the omega-read-short class (the compact card meta rows in
     * pattern/landing-blog-hub.php). Other Time to Read blocks are untouched.
     */
    public function short_read_time($content, $block) {
        if (false === strpos(' ' . ($block['attrs']['className'] ?? '') . ' ', ' omega-read-short ')) {
            return $content;
        }

        return preg_replace_callback('/>\s*([\d–-]+)\s+[^<]*</u', function ($m) {
            /* translators: %s: number of minutes. */
            return '>' . sprintf(__('%s min read', 'omega-design'), $m[1]) . '<';
        }, $content, 1);
    }

    public function init() {
        $this->register_hooks();
    }

    private function init_core_hooks() {
        add_filter('render_block_core/post-time-to-read', [$this, 'short_read_time'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('after_setup_theme', [$this, 'theme_setup']);
        add_filter('block_categories_all', [$this, 'register_block_categories'], 10, 2);
        add_filter('render_block', [$this, 'modify_block_render'], 10, 2);
        add_filter('body_class', [$this, 'add_design_body_classes']);
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
        $this->register_queued($this->actions, 'add_action');
    }

    private function register_filters() {
        $this->register_queued($this->filters, 'add_filter');
    }

    /**
     * Hands each queued hook to $register (add_action/add_filter), skipping
     * any whose callback can't be resolved.
     */
    private function register_queued(array $queue, $register) {
        foreach ($queue as $hook) {
            $cb = $this->resolve_callback($hook['callback']);
            if ($cb) {
                $register($hook['hook'], $cb, $hook['priority'], $hook['accepted_args']);
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
        wp_register_style('omega-design-style', self::asset_uri('css/style.css'), [], self::asset_version('css/style.css'));
    }

    public function enqueue_frontend_assets() {
        $this->register_assets();
        wp_enqueue_style('omega-design-style');

        if (is_admin_bar_showing()) {
            wp_add_inline_style('omega-design-style', $this->get_admin_bar_logo_css());
        }

        wp_add_inline_style('omega-design-style', $this->get_design_defaults_css());

        // The "My Login Form" plugin (if active) owns this notice now, styled
        // to match its own login/register pages — see
        // Includes/Integrations/Woocommerce.php::enqueue_checkout_auth_notice().
        // Only fall back to the theme's own version when that plugin is absent,
        // so the two never both try to restyle the same DOM node.
        if (
            function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url() && !is_user_logged_in()
            && !class_exists('MyLoginForm\\Integrations\\Woocommerce')
        ) {
            $this->enqueue_checkout_auth_notice();
        }

        if (function_exists('is_cart') && is_cart()) {
            $this->enqueue_cart_page_assets();
        }

        if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url()) {
            $this->enqueue_checkout_page_assets();
        }

        // Blog page + post archives (category, tag, author, date): card grid
        // with scroll reveal - assets/css/blog-cards.css, assets/js/blog-cards.js.
        if (is_home() || is_category() || is_tag() || is_author() || is_date()) {
            self::enqueue_style('omega-design-blog-cards', 'css/blog-cards.css', ['omega-design-style']);
            self::enqueue_script('omega-design-blog-cards', 'js/blog-cards.js', [], ['in_footer' => true, 'strategy' => 'defer']);
        }

        // The "Blog Hub" page template (templates/blog-hub.html) widens its
        // content area via the Blog Hub stylesheet, pattern or not.
        if (is_singular('page') && 'blog-hub' === get_page_template_slug()) {
            self::enqueue_style('omega-design-landing-blog-hub', 'css/landing-blog-hub.css');
        }

        if (is_singular() && (
            has_block('omega-design/slider')
            || has_block('omega-design/tabs')
            || has_block('omega-design/newsletter-form')
        )) {
            $this->enqueue_landing_page_assets();
        }
    }

    /**
     * Each landing pattern's outermost wrapper carries a page-specific
     * marker class ("omega-landing--{slug}") purely so its own CSS/JS file
     * only loads on that one page - never guessed from the URL/template,
     * just a literal string search over this page's own content.
     */
    const LANDING_PAGES = [
        'fashion-store',
        'coffee-shop',
        'digital-agency',
        'fitness-gym',
        'travel-agency',
        'gift-shop',
        'restaurant',
        'medical-clinic',
        'law-firm',
        'furniture-store',
        'beauty-cosmetics',
        'beauty-salon',
        'jewelry-store',
        'blog-hub',
    ];

    /**
     * Layout CSS shared by every landing pattern (pattern/landing-*.php) -
     * section classes core block styles don't already cover (category row,
     * brand strip, photo-card overlay technique, ...) - plus, for whichever
     * specific pattern is actually on this page, that page's own CSS/JS
     * file from assets/css/landing-{slug}.css / assets/js/landing-{slug}.js
     * (only if those files exist - most pages need none beyond the shared
     * stylesheet). Gated on has_block() so none of this loads on pages that
     * don't use these patterns.
     */
    private function enqueue_landing_page_assets() {
        self::enqueue_landing_shared_style();

        $post = get_post();
        if (!$post) {
            return;
        }

        foreach (self::LANDING_PAGES as $slug) {
            if (false === strpos($post->post_content, 'omega-landing--' . $slug)) {
                continue;
            }

            self::enqueue_landing_style($slug);
            self::enqueue_script('omega-design-landing-' . $slug, 'js/landing-' . $slug . '.js', [], true);
        }
    }

    private static function enqueue_landing_shared_style() {
        self::enqueue_style('omega-design-landing-pages', 'css/landing-pages.css');
    }

    private static function enqueue_landing_style($slug) {
        self::enqueue_style('omega-design-landing-' . $slug, 'css/landing-' . $slug . '.css', ['omega-design-landing-pages']);
    }

    private function enqueue_cart_page_assets() {
        self::enqueue_page_assets('omega-design-cart-page', 'css/cart-page.css', 'js/cart-page-interactions.js');
    }

    private function enqueue_checkout_page_assets() {
        self::enqueue_page_assets('omega-design-checkout-page', 'css/checkout-page.css', 'js/checkout-page-interactions.js');
    }

    /**
     * A page's own stylesheet + footer script, sharing one handle.
     */
    private static function enqueue_page_assets($handle, $css, $js) {
        self::enqueue_style($handle, $css);
        self::enqueue_script($handle, $js, [], true);
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
            self::asset_uri('js/checkout-auth-notice.js'),
            [],
            self::asset_version('js/checkout-auth-notice.js'),
            true
        );

        // The "My Login Form" plugin (if active) replaces WooCommerce's native
        // account/login/register pages with its own; wp_login_url() already
        // resolves to its login page via that plugin's 'login_url' filter.
        $login_url = wp_login_url(wc_get_checkout_url());

        list($register_url, $show_register) = self::checkout_registration_link();

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

    /**
     * [register URL, whether to show the register button]. Registration
     * lives on a separate page the "My Login Form" plugin tracks in its own
     * option, independent of WooCommerce's "enable myaccount registration"
     * setting - falling back to WooCommerce's own account page.
     */
    private static function checkout_registration_link() {
        $register_page_id = (int) get_option('my_login_form_register_page_id');
        if ($register_page_id && 'publish' === get_post_status($register_page_id)) {
            return [get_permalink($register_page_id), true];
        }

        if (function_exists('my_login_form_registration_url')) {
            return [my_login_form_registration_url(), false];
        }

        return [wc_get_page_permalink('myaccount'), 'yes' === get_option('woocommerce_enable_myaccount_registration')];
    }

    public function enqueue_admin_assets($hook) {
        wp_enqueue_style('omega-design-admin', self::asset_uri('css/admin.css'), [], OMEGA_DESIGN_VERSION);
        wp_add_inline_style('omega-design-admin', $this->get_admin_bar_logo_css());
    }

    /**
     * CSS that swaps the default WordPress logo in the admin toolbar
     * for the theme icon at assets/images/theme-icon.svg.
     */
    private function get_admin_bar_logo_css() {
        $icon_url = esc_url(\OmegaDesign\core\asset_urls::versioned('/images/theme-icon.svg'));

        // Core forces "background-image: none !important" directly on
        // .ab-icon (admin-bar.css's blanket dashicon reset), which no
        // amount of specificity on that same selector can out-rank. Its
        // reset list stops at .ab-icon and .ab-item::before though, never
        // .ab-icon::before - so the icon is painted on that pseudo-element
        // instead, a selector core's rule simply never touches.
        return "
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
                content: '';
                display: inline-block;
                width: 20px;
                height: 20px;
                background-image: url('{$icon_url}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 20px 20px;
            }
            #wpadminbar .omega-topbar-icon {
                width: 16px;
                height: 16px;
                margin: 8px 6px 0 0;
                float: left;
                vertical-align: middle;
            }
            /*
             * WordPress's own \"+ New\" toolbar item renders its plus-sign
             * via the dashicons webfont; when that font fails to load, the
             * browser falls back to a system font that happens to map the
             * same private-use codepoint to an unrelated wrench/tool glyph
             * instead of a blank/missing-glyph box - hiding just the icon
             * (not the whole item) keeps \"+ New\" usable as a plain text
             * link while removing the broken symbol.
             */
            #wpadminbar #wp-admin-bar-new-content > .ab-item > .ab-icon {
                display: none !important;
            }
            /*
             * Any admin bar top-level item without its own icon still gets
             * a default dashicon glyph reserved via .ab-item:before (a
             * private-use-area font codepoint - reads as an empty string in
             * script/JSON output, but the browser still renders whatever
             * that codepoint falls back to when the dashicons font can't
             * supply it, the same wrench/tool symbol as the \"+ New\" fix
             * above). The omega-topbar-icon <img> already supplies our own
             * icon, so this default glyph is just noise sitting in front
             * of it.
             */
            #wpadminbar #wp-admin-bar-omega-design > .ab-item:before {
                content: '' !important;
                width: 0 !important;
            }
            /*
             * WordPress recolors any custom SVG menu icon to a flat neutral
             * gray to match its own icon set (it literally rewrites every
             * fill/gradient in the SVG before embedding it) - this forces
             * the left-hand sidebar icon back to the theme's real,
             * unmodified file so its actual colors show.
             */
            #adminmenu #toplevel_page_omega-dashboard .wp-menu-image {
                background-image: url('{$icon_url}') !important;
                background-size: 20px auto !important;
                opacity: 1 !important;
            }
        ";
    }

    /**
     * Corner radius for the site-wide "Default look" set in
     * Settings > Design (includes/customizer/menus.php) - consumed by
     * theme.json's button element style via
     * var(--omega-btn-radius, 6px), so a block's own explicit border
     * radius (set directly in the block editor) still wins since it's
     * applied as that block's own inline style, layered on top of this.
     */
    private function get_design_defaults_css() {
        $radius = get_theme_mod('omega_button_radius', 'soft');
        if (!array_key_exists($radius, menus::BUTTON_RADIUS_CHOICES)) {
            $radius = 'soft';
        }

        return ':root { --omega-btn-radius: ' . menus::BUTTON_RADIUS_CHOICES[$radius] . '; }';
    }

    /**
     * Adds omega-default-btn-{look} when Settings > Design's "Default
     * look" isn't the plain Fill style - block-style-variations.css
     * already has the matching rule for each look, scoped to this class
     * for any Button block that hasn't been given its own explicit Style.
     */
    public function add_design_body_classes($classes) {
        $look = get_theme_mod('omega_button_look', 'fill');
        if ('fill' !== $look && array_key_exists($look, menus::BUTTON_LOOK_CHOICES)) {
            $classes[] = 'omega-default-btn-' . $look;
        }
        return $classes;
    }

    public function enqueue_block_assets() {
        $this->register_assets();
        wp_enqueue_style('omega-design-style');

        if (is_admin()) {
            $this->enqueue_editor_canvas_landing_styles();
        }
    }

    /**
     * Landing-page CSS for the editor canvas. Since WordPress 6.3 the
     * canvas is an iframe that only receives styles enqueued here on
     * enqueue_block_assets - anything enqueued on enqueue_block_editor_assets
     * (where this used to live) styles the editor's outer UI only, so the
     * canvas showed every landing page in the theme's default palette and
     * fonts instead of its real look.
     *
     * Every landing-*.css file is loaded, not just the one matching the
     * post's saved content: a pattern inserted after the editor loaded,
     * and templates in the Site Editor (no post at all), would otherwise
     * still render unstyled. Each file only targets its own page's classes
     * (.omega-landing--{slug} ...), so they can't affect each other.
     */
    private function enqueue_editor_canvas_landing_styles() {
        self::enqueue_landing_shared_style();

        foreach (self::LANDING_PAGES as $slug) {
            self::enqueue_landing_style($slug);
        }
    }

    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'omega-design-editor',
            self::asset_uri('js/editor.js'),
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n', 'wp-dom-ready', 'wp-data', 'wp-core-data'],
            self::asset_version('js/editor.js'),
            true
        );

        // Landing-page CSS for the canvas is loaded on enqueue_block_assets
        // instead - see enqueue_editor_canvas_landing_styles().
    }

    /**
     * Prepended (not appended) so "Omega Design" is the FIRST category a
     * user sees when browsing the block inserter by category, rather than
     * the last - with WooCommerce active there are already 4 core
     * categories plus 2 WooCommerce ones ahead of it, so appending left it
     * requiring a full scroll to the bottom of a long list to ever notice
     * the theme's own blocks existed at all.
     */
    public function register_block_categories($categories, $post) {
        array_unshift($categories, [
            'slug'  => 'omega-design',
            'title' => __('Omega Design', 'omega-design'),
        ]);
        return $categories;
    }

    public function modify_block_render($block_content, $block) {
        switch ((string) ($block['blockName'] ?? '')) {
            // Blocks that can carry an omegaLink (editor.js "Link" panel).
            case 'core/group':
            case 'core/columns':
            case 'core/column':
                return $this->add_block_link_overlay($block_content, $block);

            case 'core/site-logo':
                return $this->inject_dark_mode_logo($block_content, $block);

            case 'core/icon':
                return $this->add_icon_hover_style($block_content, $block);

            case 'core/html':
                return $this->render_product_badges($block_content);

            default:
                return $block_content;
        }
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

        $items = '';
        foreach (self::product_badges($product->is_virtual() || $product->is_downloadable()) as list($icon, $label)) {
            $items .= self::product_badge_html($icon, $label);
        }

        return '<ul class="omega-pdp__badges">' . $items . '</ul>';
    }

    /**
     * [icon, label] pairs - delivery/returns copy differs for digital
     * (virtual/downloadable) products.
     */
    private static function product_badges($is_digital) {
        if ($is_digital) {
            return [
                ['bolt', __('Instant delivery', 'omega-design')],
                ['shield', __('Secure checkout', 'omega-design')],
                ['refresh', __('Free lifetime updates', 'omega-design')],
            ];
        }

        return [
            ['truck', __('Fast dispatch', 'omega-design')],
            ['shield', __('Secure checkout', 'omega-design')],
            ['refresh', __('Easy 30-day returns', 'omega-design')],
        ];
    }

    private static function product_badge_html($icon, $label) {
        $path = self::PRODUCT_BADGE_ICONS[$icon] ?? '';

        return '<li class="omega-pdp__badge">'
            . '<svg class="omega-pdp__badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>'
            . '<span>' . esc_html($label) . '</span>'
            . '</li>';
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

        $style = self::hover_css_vars($hover);
        if ('' === $style) {
            return $block_content;
        }

        $parts = block_html::split_first_tag($block_content);
        if (null === $parts) {
            return $block_content;
        }

        list($tag_open, $tag_attrs, $tag_close, $rest) = $parts;

        $tag_attrs = block_html::merge_class_attr($tag_attrs, 'has-omega-hover-color');
        $tag_attrs = block_html::merge_style_attr($tag_attrs, esc_attr($style));

        return $tag_open . $tag_attrs . $tag_close . $rest;
    }

    /**
     * The --omega-hover-* custom properties for a block's omegaHover
     * settings ('' when none are set).
     */
    private static function hover_css_vars(array $hover) {
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

        return $style;
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

        $parts = block_html::split_first_tag($block_content);
        if (null === $parts) {
            return $block_content;
        }

        list($tag_open, $tag_attrs, $tag_close, $rest) = $parts;

        $label   = self::block_link_label($link, $block_content);
        $overlay = '<a' . self::block_link_attrs($href, $link, $label) . '></a>';

        return $tag_open . block_html::merge_class_attr($tag_attrs, 'omega-has-block-link') . $tag_close . $overlay . $rest;
    }

    /**
     * The overlay link's accessible name: the admin-set label, else the
     * block's own text (trimmed to 100 characters).
     */
    private static function block_link_label(array $link, $block_content) {
        $label = trim((string) ($link['label'] ?? ''));
        if ('' !== $label) {
            return $label;
        }

        $label = trim(wp_strip_all_tags($block_content));
        if (strlen($label) > 100) {
            $label = rtrim(substr($label, 0, 100)) . '…';
        }
        return $label;
    }

    private static function block_link_attrs($href, array $link, $label) {
        $attrs = ' href="' . $href . '" class="omega-block-link"';
        if ('_blank' === ($link['target'] ?? '')) {
            $attrs .= ' target="_blank" rel="noopener noreferrer"';
        }
        if ('' !== $label) {
            $attrs .= ' aria-label="' . esc_attr($label) . '"';
        }
        return $attrs;
    }

    /**
     * core/site-logo only ever renders the single image behind the
     * `custom_logo` theme mod - adds the dark mode variant (dark_logo),
     * sized to the block's own width setting.
     */
    private function inject_dark_mode_logo($block_content, $block) {
        $width = isset($block['attrs']['width']) ? (int) $block['attrs']['width'] : 0;

        return dark_logo::add_variant($block_content, [
            'style' => $width ? sprintf('width:%dpx;height:auto;', $width) : '',
        ]);
    }
}