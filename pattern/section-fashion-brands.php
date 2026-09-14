<?php
/**
 * Title: Section - Fashion Store: Top Brands
 * Slug: omega-design/section-fashion-brands
 * Categories: omega-design-sections
 * Description: A row of brand logos - insertable on its own, on any page.
 * Keywords: brands, logos, strip, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Top Brands', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-brand-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-brand-row">
<?php
$brands = [
	['name' => 'Nike', 'file' => 'nike.png'],
	['name' => 'Adidas', 'file' => 'adidas.png'],
	['name' => 'Zara', 'file' => 'zara.png'],
	['name' => 'H&M', 'file' => 'hm.png'],
	['name' => "Levi's", 'file' => 'levis.png'],
	['name' => 'Puma', 'file' => 'puma.png'],
	['name' => 'Calvin Klein', 'file' => 'calvin-klein.png'],
	['name' => 'Mango', 'file' => 'mango.png'],
];
foreach ($brands as $brand) :
	?>
<!-- wp:image {"className":"omega-brand-row__item","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-brand-row__item"><img src="<?php echo esc_url(omega_pattern_fashion_asset('brands/' . $brand['file'])); ?>" alt="<?php echo esc_attr($brand['name']); ?>"/></figure><!-- /wp:image -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
