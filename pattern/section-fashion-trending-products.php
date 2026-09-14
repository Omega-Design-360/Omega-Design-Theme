<?php
/**
 * Title: Section - Fashion Store: Trending Products
 * Slug: omega-design/section-fashion-trending-products
 * Categories: omega-design-sections
 * Description: Tabbed live WooCommerce product grids (Best Sellers, New Arrivals, Top Rated, On Sale) - insertable on its own, on any page.
 * Keywords: products, woocommerce, tabs, trending, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Trending Products', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(21, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('New Arrivals', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(22, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Top Rated', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(23, 'rating'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('On Sale', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(24, 'date', true); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->
