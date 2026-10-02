<?php
/**
 * Shop Layouts - 5 alternative block templates for the WooCommerce Shop
 * page (templates/shop-*.html), chosen from a "Shop Layout" panel that
 * only appears in the editor sidebar while editing the Shop page itself.
 *
 * Why not ordinary page templates: WooCommerce never renders the Shop page
 * through its page template at all - /shop/ is the product post-type
 * archive, so a template picked in the normal "Template" dropdown would
 * silently do nothing. And a template listed for the "page" post type
 * shows up in that dropdown (and the "Swap template" modal, which has no
 * way to know which page is open) on every page of the site.
 *
 * So instead: theme.json registers the five templates for a post type
 * that doesn't exist ("omega_shop_layout"), which keeps them out of every
 * page's template list while still making them real, Site-Editor-editable
 * templates; the chosen one is stored as post meta on the Shop page; and
 * on the front end it's prepended to the archive template hierarchy, so
 * the block template loader picks it ahead of archive-product.html.
 * Leaving the panel on "Default" keeps the theme's normal Shop template.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class shop_layouts {

    use singleton;
    use assets;

    const META_KEY = 'omega_shop_layout';

    private function __construct() {
        add_action('init', [$this, 'register_meta']);
        add_filter('archive_template_hierarchy', [$this, 'prepend_layout_template'], 20);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_site_editor_styles']);

        // Glass layouts (templates/shop-glass*.html).
        add_filter('render_block_data', [$this, 'resolve_attribute_filter_ids']);
        add_filter('render_block_woocommerce/product-image', [$this, 'glass_card_media'], 10, 3);
        add_filter('render_block_core/post-terms', [$this, 'glass_card_swatches'], 10, 3);
        add_filter('render_block_core/image', [$this, 'glass_hero_image'], 10, 2);
    }

    /**
     * The Size and Color filters in the glass templates are identified by
     * class (omega-filter-attr--{slug}) rather than a hard-coded attribute
     * ID, since that ID differs on every site. This resolves the slug to
     * the site's own attribute just before the block renders; a site
     * without that attribute simply gets no such filter.
     */
    /*
     * Applied recursively from whichever block this filter first sees:
     * nested filter blocks are built from their parent's parsed block, so
     * by the time render_block_data reaches one of them its attributes are
     * already fixed - the attributeId has to be in place before that.
     */
    public function resolve_attribute_filter_ids($parsed_block) {
        if (empty($parsed_block['innerBlocks']) || !function_exists('wc_attribute_taxonomy_id_by_name') || !self::has_attribute_filter($parsed_block)) {
            return $parsed_block;
        }

        return $this->resolve_attribute_filter_ids_in($parsed_block);
    }

    private static function has_attribute_filter($block) {
        if ('woocommerce/product-filter-attribute' === ($block['blockName'] ?? '')) {
            return empty($block['attrs']['attributeId']) && false !== strpos((string) ($block['attrs']['className'] ?? ''), 'omega-filter-attr--');
        }
        foreach ($block['innerBlocks'] ?? [] as $inner) {
            if (self::has_attribute_filter($inner)) {
                return true;
            }
        }
        return false;
    }

    private function resolve_attribute_filter_ids_in($block) {
        if ('woocommerce/product-filter-attribute' === ($block['blockName'] ?? '')
            && empty($block['attrs']['attributeId'])
            && preg_match('/\bomega-filter-attr--([a-z0-9_-]+)/', (string) ($block['attrs']['className'] ?? ''), $m)
        ) {
            $id = (int) wc_attribute_taxonomy_id_by_name($m[1]);
            if ($id) {
                $block['attrs']['attributeId'] = $id;
            }
        }

        if (!empty($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $i => $inner) {
                $block['innerBlocks'][$i] = $this->resolve_attribute_filter_ids_in($inner);
            }
        }

        return $block;
    }

    private static function block_has_class($block, $class) {
        return false !== strpos(' ' . ($block['attrs']['className'] ?? '') . ' ', ' ' . $class . ' ');
    }

    /**
     * Badge (-25% / New / Bestseller / Limited) and wishlist heart layered
     * over each glass card's photo - computed from the product's real data.
     */
    public function glass_card_media($content, $block, $instance) {
        if (!self::block_has_class($block, 'omega-glass-card__media')) {
            return $content;
        }

        $product = wc_get_product($instance->context['postId'] ?? get_the_ID());
        if (!$product) {
            return $content;
        }

        return preg_replace('/^(\s*<[^>]+>)/', '$1' . self::glass_badge_html($product) . self::wishlist_button_html($product), $content, 1);
    }

    /**
     * The first badge that applies, in priority order: sale percentage,
     * low stock, bestseller, new (last 30 days) - or '' for none.
     */
    private static function glass_badge_html($product) {
        if ($product->is_on_sale()) {
            $pct = self::sale_percent($product);
            return self::glass_badge('sale', $pct > 0 ? '-' . $pct . '%' : esc_html__('Sale', 'omega-design'));
        }

        if ($product->managing_stock() && $product->get_stock_quantity() !== null && $product->get_stock_quantity() <= 5 && $product->is_in_stock()) {
            return self::glass_badge('limited', esc_html__('Limited', 'omega-design'));
        }

        if ((int) $product->get_total_sales() >= 100) {
            return self::glass_badge('best', esc_html__('Bestseller', 'omega-design'));
        }

        if ($product->get_date_created() && $product->get_date_created()->getTimestamp() > time() - 30 * DAY_IN_SECONDS) {
            return self::glass_badge('new', esc_html__('New', 'omega-design'));
        }

        return '';
    }

    private static function sale_percent($product) {
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();
        return ($regular > 0 && $sale > 0) ? (int) round(100 - $sale / $regular * 100) : 0;
    }

    /** $text must already be escaped. */
    private static function glass_badge($modifier, $text) {
        return '<span class="omega-glass-badge omega-glass-badge--' . $modifier . '">' . $text . '</span>';
    }

    private static function wishlist_button_html($product) {
        return '<button type="button" class="omega-glass-wish" data-product="' . (int) $product->get_id() . '" aria-pressed="false" aria-label="' . esc_attr(sprintf(__('Add %s to wishlist', 'omega-design'), $product->get_name())) . '">'
            . '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 20.7 4.9 13.9a4.8 4.8 0 0 1 6.8-6.8l.3.3.3-.3a4.8 4.8 0 0 1 6.8 6.8Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg></button>';
    }

    /**
     * Color swatch dots for a glass card (a Post Terms block on pa_color),
     * using the color saved on each attribute term.
     */
    public function glass_card_swatches($content, $block, $instance) {
        if (!self::block_has_class($block, 'omega-glass-card__swatches')) {
            return $content;
        }

        $post_id = (int) ($instance->context['postId'] ?? get_the_ID());
        $terms   = $post_id ? get_the_terms($post_id, 'pa_color') : false;
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $dots = '';
        foreach (array_slice($terms, 0, 5) as $term) {
            $dots .= self::swatch_dot_html($term);
        }

        if ('' === $dots) {
            return '';
        }

        return '<div class="omega-glass-card__swatches" role="img" aria-label="' . esc_attr(sprintf(__('Colors: %s', 'omega-design'), implode(', ', wp_list_pluck($terms, 'name')))) . '">' . $dots . '</div>';
    }

    /**
     * One color dot for a pa_color term, from the color saved on the term -
     * '' when it has none.
     */
    private static function swatch_dot_html($term) {
        $hex = sanitize_hex_color((string) get_term_meta($term->term_id, 'color', true));
        if (!$hex) {
            return '';
        }

        return '<span class="omega-glass-swatch" style="--omega-swatch:' . esc_attr($hex) . '" title="' . esc_attr($term->name) . '"></span>';
    }

    /** The hero photo ships with the theme; point it at the theme's real URL. */
    public function glass_hero_image($content, $block) {
        if (!self::block_has_class($block, 'omega-glass-hero__image')) {
            return $content;
        }

        return str_replace(
            'src="/wp-content/themes/Omega-Design-Theme/assets/',
            'src="' . esc_url(get_template_directory_uri()) . '/assets/',
            $content
        );
    }

    /**
     * Template slug => label/description. The slug is also the file name
     * in templates/ and the "name" in theme.json's customTemplates.
     */
    public static function layouts() {
        return [
            'shop-sidebar'   => [
                'label'       => __('Sidebar Filters', 'omega-design'),
                'description' => __('Filters in a left sidebar, 3-column grid. On phones the filters open from a "Filters" button.', 'omega-design'),
            ],
            'shop-pills'     => [
                'label'       => __('Category Pills', 'omega-design'),
                'description' => __('A sticky row of category chips above a 4-column grid - swipeable on phones.', 'omega-design'),
            ],
            'shop-hero'      => [
                'label'       => __('Hero Banner', 'omega-design'),
                'description' => __('A full-width banner with category chips, store perks and a 4-column grid.', 'omega-design'),
            ],
            'shop-editorial' => [
                'label'       => __('Editorial Grid', 'omega-design'),
                'description' => __('Large portrait photos, minimal text and a fold-out filter panel.', 'omega-design'),
            ],
            'shop-list'      => [
                'label'       => __('Compact List', 'omega-design'),
                'description' => __('Catalog rows with summary, rating, stock and price - ideal for large or technical ranges.', 'omega-design'),
            ],
            'shop-glass'     => [
                'label'       => __('Glass Boutique', 'omega-design'),
                'description' => __('Fashion hero, filter sidebar (category, price, brand, size, color) and photo cards whose details appear on a frosted-glass panel on hover.', 'omega-design'),
            ],
            'shop-glass-wide' => [
                'label'       => __('Glass Showcase', 'omega-design'),
                'description' => __('The Glass Boutique look without filters - a full-width 4-column grid of frosted-glass photo cards.', 'omega-design'),
            ],
        ];
    }

    /**
     * layouts() as a list of {value, label, description} for the editor
     * panel's select control.
     */
    private static function layout_options() {
        $options = [];
        foreach (self::layouts() as $slug => $layout) {
            $options[] = [
                'value'       => $slug,
                'label'       => $layout['label'],
                'description' => $layout['description'],
            ];
        }
        return $options;
    }

    private function shop_page_id() {
        return function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0;
    }

    /**
     * The chosen layout slug, or '' for the theme's default Shop template.
     */
    public function active_layout() {
        $page_id = $this->shop_page_id();
        if ($page_id <= 0) {
            return '';
        }

        $layout = (string) get_post_meta($page_id, self::META_KEY, true);
        return array_key_exists($layout, self::layouts()) ? $layout : '';
    }

    /**
     * Only the Shop page's own product archive - not product search
     * results (which share the same post-type archive query but have
     * their own product-search-results template) and not category/tag
     * archives, which keep the theme's standard archive template.
     */
    private function is_shop_archive() {
        return function_exists('is_shop') && is_shop() && !is_search();
    }

    public function register_meta() {
        register_post_meta('page', self::META_KEY, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitize_layout'],
            'auth_callback'     => [$this, 'can_edit_layout'],
        ]);
    }

    public function sanitize_layout($value) {
        $value = sanitize_key($value);
        return array_key_exists($value, self::layouts()) ? $value : '';
    }

    public function can_edit_layout() {
        return current_user_can('edit_pages');
    }

    public function prepend_layout_template($templates) {
        if (!$this->is_shop_archive()) {
            return $templates;
        }

        $layout = $this->active_layout();
        if ('' === $layout) {
            return $templates;
        }

        array_unshift($templates, $layout);
        return $templates;
    }

    /**
     * One small stylesheet shared by all five layouts, loaded only on the
     * Shop archive when one of them is actually active.
     */
    public function enqueue_frontend_assets() {
        if ($this->is_shop_archive() && '' !== $this->active_layout()) {
            $this->enqueue_stylesheet();
            if (0 === strpos($this->active_layout(), 'shop-glass')) {
                $this->enqueue_glass_script();
            }
            return;
        }

        // The same layouts inserted on an ordinary page as a "Shop" section
        // pattern (pattern/shop-section-*.php) - a literal marker-class
        // search over the page's own content, like hooks.php does for the
        // landing patterns.
        $post = is_singular() ? get_post() : null;
        if ($post && false !== strpos($post->post_content, 'omega-shop-layout')) {
            $this->enqueue_stylesheet();
            if (false !== strpos($post->post_content, 'omega-glass-card')) {
                $this->enqueue_glass_script();
            }
        }
    }

    /** Grid/list toggle, wishlist hearts and collapsible filter groups. */
    private function enqueue_glass_script() {
        self::enqueue_script('omega-design-shop-glass', 'js/shop-glass.js', [self::core_script()], ['in_footer' => true, 'strategy' => 'defer']);
    }

    /**
     * Block editor canvases (post editor + Site Editor), so these layouts
     * look the same while being edited as on the front end.
     */
    public function enqueue_site_editor_styles() {
        if (!is_admin()) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !$screen->is_block_editor()) {
            return;
        }

        $this->enqueue_stylesheet();
    }

    private function enqueue_stylesheet() {
        // After style.css, whose generic product-card rules this overrides.
        $deps = wp_style_is('omega-design-style', 'registered') ? ['omega-design-style'] : [];
        self::enqueue_style('omega-design-shop-layouts', 'css/shop-layouts.css', $deps);
    }

    /**
     * The "Shop Layout" panel - enqueued only while editing the Shop page,
     * so the option never appears anywhere else.
     */
    public function enqueue_editor_assets() {
        $post = get_post();
        if (!$post || 'page' !== $post->post_type || (int) $post->ID !== $this->shop_page_id()) {
            return;
        }

        $enqueued = self::enqueue_script(
            'omega-design-shop-layout',
            'js/shop-layout.js',
            ['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', self::editor_shared_script()],
            true
        );
        if (!$enqueued) {
            return;
        }

        wp_localize_script('omega-design-shop-layout', 'OmegaShopLayouts', [
            'metaKey'         => self::META_KEY,
            'layouts'         => self::layout_options(),
            'siteEditorUrl'   => admin_url('site-editor.php?p=%2Fwp_template%2F' . rawurlencode(get_stylesheet() . '//')),
            'shopUrl'         => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '',
        ]);
    }
}
