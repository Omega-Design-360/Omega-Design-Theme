<?php
/**
 * Product Page Layout - 5 distinct single-product layouts, each built from
 * WooCommerce's own dynamic blocks (gallery, price, rating, stock, the
 * variation-aware add-to-cart form) so every layout stays fully functional
 * (real variations, real stock, real cart) - only the surrounding structure,
 * typography and information design change between them. An admin picks the
 * active one under Omega Design > Settings > Product Page.
 *
 * Mirrors classic_header.php's "swap the whole region for hand-written
 * markup, driven by a single theme_mod" approach: templates/single-product.html
 * renders nothing but a single <div data-omega-product-layout> marker (a
 * core/html block), and this class replaces that marker's content entirely
 * via the render_block filter once WooCommerce's product data is available.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class product_page {

    private static $instance = null;

    const LAYOUTS = ['gallery-feature', 'command-deck', 'split-stage', 'spec-sheet', 'boutique'];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block', [$this, 'maybe_render_layout'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function init() {}

    public static function style_choices() {
        return [
            'gallery-feature' => __('Gallery Feature - large screenshot/photo stage, airy buy column', 'omega-design'),
            'command-deck'    => __('Command Deck - dense, conventional e-commerce layout', 'omega-design'),
            'split-stage'     => __('Split Stage - bold full-bleed dark photography panel', 'omega-design'),
            'spec-sheet'      => __('Spec Sheet - technical, software-pricing-table style', 'omega-design'),
            'boutique'        => __('Boutique - maximal restraint, one oversized price', 'omega-design'),
        ];
    }

    public static function active_layout() {
        $layout = get_theme_mod('omega_product_page_layout', 'gallery-feature');
        return in_array($layout, self::LAYOUTS, true) ? $layout : 'gallery-feature';
    }

    public function enqueue_assets() {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        $css_path = get_template_directory() . '/assets/css/product-page-layouts.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-product-page',
                get_template_directory_uri() . '/assets/css/product-page-layouts.css',
                [],
                filemtime($css_path)
            );
        }

        $js_path = get_template_directory() . '/assets/js/product-page-interactions.js';
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'omega-design-product-page',
                get_template_directory_uri() . '/assets/js/product-page-interactions.js',
                [],
                filemtime($js_path),
                true
            );
        }

        // The Related Products carousel reuses the theme's own slider engine
        // (blocks/omega-slider/view.js + style.css) directly - registered
        // already by includes/core/blocks.php's register_omega_slider(),
        // just not normally enqueued outside pages that actually insert
        // that block, so it needs enqueuing here explicitly.
        if ((bool) get_theme_mod('omega_related_products_carousel', true)) {
            wp_enqueue_style('omega-slider-block');
            wp_enqueue_script('omega-slider-block-view');
        }
    }

    /**
     * Sniffs the marker templates/single-product.html renders (a plain
     * core/html block, same technique hooks.php already uses for the
     * trust-badges placeholder) and, only on a real singular product view,
     * swaps it for one of the 5 hand-assembled layouts below.
     */
    public function maybe_render_layout($block_content, $block) {
        if (($block['blockName'] ?? '') !== 'core/html') {
            return $block_content;
        }
        if (strpos($block_content, 'data-omega-product-layout') === false) {
            return $block_content;
        }
        if (!function_exists('is_product') || !is_product()) {
            return $block_content;
        }

        $product_id = get_the_ID();
        $product    = $product_id ? wc_get_product($product_id) : null;
        if (!$product) {
            return $block_content;
        }

        $method = 'render_' . str_replace('-', '_', self::active_layout());
        if (!method_exists($this, $method)) {
            $method = 'render_gallery_feature';
        }

        return $this->$method($product);
    }

    /**
     * Renders one WooCommerce block in isolation via do_blocks() - not a
     * bare render_block() call. woocommerce_header.php already documents
     * why for the Mini Cart block specifically ("a bare render_block()
     * call skips parts of that pipeline and was observed to return
     * nothing"): WordPress's script-MODULE loading (wp_enqueue_script_module,
     * what these blocks' Interactivity API directives need to actually
     * hydrate - add-to-cart, quantity stepper, the shared wc/store/cart
     * state the header's Mini Cart subscribes to) is decided by an early
     * scan of the current template's own parsed block content. Since
     * templates/single-product.html contains nothing but a marker
     * core/html block, that scan never finds woocommerce/add-to-cart-form
     * in it - so its module can end up never enqueued, leaving the button
     * LOOKING interactive (its data-wp-* attributes render fine either
     * way) while quietly falling back to a plain, non-reactive form POST.
     * do_blocks() is this same theme's own already-proven fix for that.
     *
     * Safe to call here because we're still inside the main loop for a
     * singular product (the_post() already ran), which is exactly the
     * context these blocks' PHP render callbacks fall back to
     * (wc_get_product(get_the_ID())) when no explicit postId context is
     * passed down from an ancestor block.
     */
    private function block($name, $attrs = []) {
        $markup = '<!-- wp:' . $name . (empty($attrs) ? '' : ' ' . wp_json_encode($attrs)) . ' /-->';
        return (string) do_blocks($markup);
    }

    private function gallery() {
        return $this->block('woocommerce/product-image-gallery');
    }

    private function title($class, $font_size = '') {
        $attrs = ['level' => 1, 'className' => $class];
        if ('' !== $font_size) {
            $attrs['fontSize'] = $font_size;
        }
        return $this->block('core/post-title', $attrs);
    }

    private function price($class = '', $font_size = '') {
        $attrs = ['isDescendentOfSingleProductTemplate' => true];
        if ('' !== $class) {
            $attrs['className'] = $class;
        }
        if ('' !== $font_size) {
            $attrs['fontSize'] = $font_size;
        }
        return $this->block('woocommerce/product-price', $attrs);
    }

    private function rating() {
        return $this->block('woocommerce/product-rating', ['isDescendentOfSingleProductTemplate' => true]);
    }

    private function stock() {
        return $this->block('woocommerce/product-stock-indicator');
    }

    private function excerpt($length, $class = '') {
        $attrs = ['excerptLength' => $length];
        if ('' !== $class) {
            $attrs['className'] = $class;
        }
        return $this->block('core/post-excerpt', $attrs);
    }

    private function add_to_cart($class = '') {
        $attrs = ['quantitySelectorStyle' => 'stepper'];
        if ('' !== $class) {
            $attrs['className'] = $class;
        }
        return $this->block('woocommerce/add-to-cart-form', $attrs);
    }

    private function sku() {
        return $this->block('woocommerce/product-sku');
    }

    private function breadcrumbs() {
        return $this->block('woocommerce/breadcrumbs');
    }

    private function tabs($class = '') {
        $attrs = [];
        if ('' !== $class) {
            $attrs['className'] = $class;
        }
        return $this->block('woocommerce/product-details', $attrs);
    }

    /**
     * Classic (non-block) related products template - a query loop with
     * proper innerBlocks (thumbnail/title/price/button per card) can't be
     * reconstructed through render_block() without walking WooCommerce's
     * own block-tree building for woocommerce/product-collection, so this
     * reuses WC's tested classic renderer instead: identical query logic
     * (shared categories/tags), just classic markup - `.related.products`,
     * `ul.products li.product` - which product-page-layouts.css styles
     * with plain, stable WooCommerce class selectors.
     */
    /**
     * Default is the site's own slider engine (an admin can turn this back
     * into WooCommerce's plain grid via Settings > WooCommerce, in case a
     * specific store prefers the classic look) - either way this stays the
     * single shared implementation all 5 layouts call, so the choice
     * applies everywhere at once.
     */
    private function related($columns = 4) {
        global $product;
        if (!$product instanceof \WC_Product) {
            return '';
        }

        $use_carousel = (bool) get_theme_mod('omega_related_products_carousel', true);
        $related_ids  = wc_get_related_products($product->get_id(), $use_carousel ? 8 : $columns);

        if (empty($related_ids)) {
            return '';
        }

        if (!$use_carousel) {
            ob_start();
            woocommerce_related_products([
                'posts_per_page' => $columns,
                'columns'        => $columns,
            ]);
            return (string) ob_get_clean();
        }

        return $this->related_carousel($related_ids, $columns);
    }

    /**
     * Builds the SAME runtime markup blocks/omega-slider/view.js already
     * knows how to drive (a .omega-slider wrapper + data-* attributes, any
     * direct children treated as "the slides") by hand, rather than trying
     * to reconstruct omega-design/slider's own save() output - that block
     * is static (InnerBlocks-based, no render.php), so it has no PHP
     * render path meant to be called outside the block editor. The engine
     * itself doesn't care how its markup was produced, only that the
     * shape matches, so this is a legitimate, low-risk reuse rather than a
     * workaround. Each "slide" is WooCommerce's own real <li class="product">
     * from content-product.php - the exact same partial the rest of the
     * site's product loops use - so ratings, sale badges, variable-product
     * price ranges etc. all keep working with zero duplicated logic.
     */
    private function related_carousel($related_ids, $columns) {
        $query = new \WP_Query([
            'post_type'           => 'product',
            'post__in'            => $related_ids,
            'posts_per_page'      => count($related_ids),
            'orderby'             => 'post__in',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        ]);

        if (!$query->have_posts()) {
            wp_reset_postdata();
            return '';
        }

        ob_start();
        ?>
        <div class="omega-related-products">
            <h2 class="omega-related-products__heading"><?php esc_html_e('You May Also Like', 'omega-design'); ?></h2>
            <ul
                class="omega-slider omega-related-products-slider"
                data-autoplay="0"
                data-autoplay-speed="6000"
                data-loop="0"
                data-arrows="1"
                data-dots="0"
                data-spv="<?php echo esc_attr($columns); ?>"
                data-spv-tablet="2"
                data-spv-mobile="1"
                data-gap="20px"
                data-prev-label="<?php esc_attr_e('Previous related products', 'omega-design'); ?>"
                data-next-label="<?php esc_attr_e('Next related products', 'omega-design'); ?>"
                data-dots-label="<?php esc_attr_e('Related products', 'omega-design'); ?>"
                data-effect="slide"
                data-thumbnails="0"
                data-progress-bar="0"
                data-peek="0"
            >
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    wc_get_template_part('content', 'product');
                }
                ?>
            </ul>
        </div>
        <?php
        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    /**
     * A small set of real facts about the product being viewed, adapted to
     * whether it's virtual/downloadable or a physical good - never
     * fabricated placeholder numbers. Rows with no underlying data are
     * simply omitted rather than shown empty.
     */
    private function facts($product) {
        $facts = [];

        if ($product->is_virtual() || $product->is_downloadable()) {
            $facts[] = [__('Delivery', 'omega-design'), __('Instant, by email', 'omega-design')];

            $limit = (int) $product->get_download_limit();
            $facts[] = [__('Downloads allowed', 'omega-design'), $limit > 0
                ? sprintf(_n('%d download', '%d downloads', $limit, 'omega-design'), $limit)
                : __('Unlimited', 'omega-design')];

            $expiry = (int) $product->get_download_expiry();
            $facts[] = [__('Access', 'omega-design'), $expiry > 0
                ? sprintf(_n('Expires %d day after purchase', 'Expires %d days after purchase', $expiry, 'omega-design'), $expiry)
                : __('Never expires', 'omega-design')];
        } else {
            $weight = $product->get_weight();
            if ($weight) {
                $facts[] = [__('Weight', 'omega-design'), $weight . ' ' . get_option('woocommerce_weight_unit')];
            }

            if ($product->get_length() && $product->get_width() && $product->get_height()) {
                $facts[] = [__('Dimensions', 'omega-design'), $product->get_dimensions(true)];
            }

            $shipping_class = $product->get_shipping_class();
            if ($shipping_class) {
                $term = get_term_by('slug', $shipping_class, 'product_shipping_class');
                if ($term) {
                    $facts[] = [__('Shipping', 'omega-design'), $term->name];
                }
            }
        }

        $sku = $product->get_sku();
        if ($sku) {
            $facts[] = [__('SKU', 'omega-design'), $sku];
        }

        $categories = wp_strip_all_tags((string) wc_get_product_category_list($product->get_id()));
        if ($categories) {
            $facts[] = [__('Category', 'omega-design'), $categories];
        }

        return $facts;
    }

    private function facts_rows_html($product, $wrap_open, $row_tpl, $wrap_close) {
        $rows = '';
        foreach ($this->facts($product) as [$label, $value]) {
            $rows .= sprintf($row_tpl, esc_html($label), esc_html($value));
        }
        return '' !== $rows ? $wrap_open . $rows . $wrap_close : '';
    }

    /**
     * Layout 1 - Gallery Feature: a large image/screenshot stage with an
     * unboxed, airy buy column. The default layout.
     */
    private function render_gallery_feature($product) {
        ob_start();
        ?>
        <div class="opl opl--gallery-feature">
            <div class="opl-gf__grid">
                <div class="opl-gf__gallery-col">
                    <?php echo $this->gallery(); ?>
                </div>
                <div class="opl-gf__info-col">
                    <?php echo $this->title('opl-gf__title', 'xx-large'); ?>
                    <?php echo $this->excerpt(26, 'opl-gf__pitch'); ?>
                    <?php echo $this->price('opl-gf__price', 'x-large'); ?>
                    <?php echo $this->add_to_cart('opl-gf__cta'); ?>
                    <div class="opl-gf__meta">
                        <?php echo $this->rating(); ?>
                        <?php echo $this->stock(); ?>
                        <?php echo $this->sku(); ?>
                    </div>
                </div>
            </div>
            <?php echo $this->tabs('opl-gf__tabs'); ?>
            <div class="opl__related"><?php echo $this->related(4); ?></div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Layout 2 - Command Deck: the familiar dense e-commerce layout -
     * thumbnail-and-stage gallery beside an elevated, sectioned buy panel,
     * a spec grid below.
     */
    private function render_command_deck($product) {
        ob_start();
        ?>
        <div class="opl opl--command-deck">
            <div class="opl-cd__crumb"><?php echo $this->breadcrumbs(); ?></div>
            <div class="opl-cd__grid">
                <div class="opl-cd__gallery"><?php echo $this->gallery(); ?></div>
                <div class="opl-cd__buy">
                    <div class="opl-cd__section">
                        <?php echo $this->title('opl-cd__title', 'large'); ?>
                        <?php echo $this->rating(); ?>
                    </div>
                    <div class="opl-cd__section">
                        <?php echo $this->price('opl-cd__price'); ?>
                        <?php echo $this->stock(); ?>
                        <?php echo $this->sku(); ?>
                    </div>
                    <div class="opl-cd__section">
                        <?php echo $this->add_to_cart('opl-cd__cta'); ?>
                    </div>
                </div>
            </div>
            <?php echo $this->facts_rows_html(
                $product,
                '<h2 class="opl-cd__specs-title">' . esc_html__('Specifications', 'omega-design') . '</h2><div class="opl-cd__specs">',
                '<div class="row"><span>%s</span><span>%s</span></div>',
                '</div>'
            ); ?>
            <?php echo $this->tabs('opl-cd__tabs'); ?>
            <div class="opl__related"><?php echo $this->related(4); ?></div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Layout 3 - Split Stage: a full-bleed dark photography panel for the
     * gallery, sharp-edged, against a light info panel.
     */
    private function render_split_stage($product) {
        ob_start();
        ?>
        <div class="opl opl--split-stage">
            <div class="opl-ss__split">
                <div class="opl-ss__dark">
                    <?php echo $this->gallery(); ?>
                    <div class="opl-ss__price-tag"><?php echo $this->price('', 'xx-large'); ?></div>
                </div>
                <div class="opl-ss__light">
                    <?php echo $this->title('opl-ss__title', 'xxx-large'); ?>
                    <?php echo $this->excerpt(30, 'opl-ss__desc'); ?>
                    <?php echo $this->facts_rows_html(
                        $product,
                        '<div class="opl-ss__facts">',
                        '<div><b>%2$s</b><span>%1$s</span></div>',
                        '</div>'
                    ); ?>
                    <?php echo $this->add_to_cart('opl-ss__cta'); ?>
                </div>
            </div>
            <?php echo $this->tabs('opl-ss__tabs'); ?>
            <div class="opl__related"><?php echo $this->related(4); ?></div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Layout 4 - Spec Sheet: a software-pricing-table shape - metadata and
     * license/shipping facts as a real table, not a badge row.
     */
    private function render_spec_sheet($product) {
        ob_start();
        ?>
        <div class="opl opl--spec-sheet">
            <div class="opl-sh__head">
                <div class="opl-sh__thumb"><?php echo $this->gallery(); ?></div>
                <div class="opl-sh__headtext">
                    <?php echo $this->title('opl-sh__title', 'large'); ?>
                    <?php echo $this->excerpt(20, 'opl-sh__tagline'); ?>
                    <div class="opl-sh__chips">
                        <?php echo $this->sku(); ?>
                        <?php echo $this->stock(); ?>
                    </div>
                </div>
            </div>
            <div class="opl-sh__price-strip"><?php echo $this->price(); ?></div>
            <?php echo $this->facts_rows_html(
                $product,
                '<table class="opl-sh__table"><thead><tr><th>' . esc_html__('Detail', 'omega-design') . '</th><th>' . esc_html__('Value', 'omega-design') . '</th></tr></thead><tbody>',
                '<tr><td>%s</td><td>%s</td></tr>',
                '</tbody></table>'
            ); ?>
            <div class="opl-sh__cta"><?php echo $this->add_to_cart(); ?></div>
            <?php echo $this->tabs('opl-sh__tabs'); ?>
            <div class="opl__related"><?php echo $this->related(4); ?></div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Layout 5 - Boutique: maximal restraint - a single centered column, no
     * cards, one oversized price as the whole gesture.
     */
    private function render_boutique($product) {
        ob_start();
        ?>
        <div class="opl opl--boutique">
            <div class="opl-bq__wrap">
                <div class="opl-bq__frame"><?php echo $this->gallery(); ?></div>
                <?php echo $this->title('opl-bq__title', 'xx-large'); ?>
                <div class="opl-bq__rule"></div>
                <?php echo $this->excerpt(24, 'opl-bq__pitch'); ?>
                <?php echo $this->price('opl-bq__price', 'xxx-large'); ?>
                <?php echo $this->add_to_cart('opl-bq__cta'); ?>
            </div>
            <div class="opl-bq__tabs"><?php echo $this->tabs(); ?></div>
            <div class="opl__related"><?php echo $this->related(4); ?></div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}

product_page::get_instance();
