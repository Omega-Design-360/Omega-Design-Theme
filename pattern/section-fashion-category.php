<?php
/**
 * Title: Section - Fashion Store: Shop by Category
 * Slug: omega-design/section-fashion-category
 * Categories: omega-design-sections
 * Description: A horizontally scrollable row of circular category thumbnails (Women, Men, Kids, ...) - insertable on its own, on any page.
 * Keywords: category, shop by category, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Shop by Category', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-category-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-row">
<?php foreach (['Women', 'Men', 'Kids', 'Dresses', 'Tops', 'Bottoms', 'Outerwear', 'Shoes', 'Bags', 'Accessories', 'Sale'] as $cat_i => $cat) : ?>
<!-- wp:group {"className":"omega-category-item","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-item">
<!-- wp:image {"className":"omega-round-image","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image"><img src="<?php echo esc_url(omega_pattern_fashion_asset(sprintf('categories/category-%02d.png', $cat_i + 1))); ?>" alt="<?php echo esc_attr($cat); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"small"} --><p class="has-text-align-center omega-strong has-small-font-size"><?php echo esc_html($cat); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
