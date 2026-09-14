<?php
/**
 * Title: Section - Fashion Store: Promo Banners
 * Slug: omega-design/section-fashion-promo-banners
 * Categories: omega-design-sections
 * Description: Three photo-card promo banners (Women's/Men's/Kids' collections) with a sale badge on the first - insertable on its own, on any page.
 * Keywords: promo, banners, photo card, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
$promos = [
	['title' => __("Women's Collection", 'omega-design'), 'sub' => __('Up to 40% Off', 'omega-design'), 'body' => __('Discover your perfect look', 'omega-design'), 'cta' => __('Shop Women', 'omega-design'), 'image' => 'promos/womens-collection.png'],
	['title' => __("Men's Essentials", 'omega-design'),    'sub' => __('Timeless Styles', 'omega-design'), 'body' => __('For the modern man', 'omega-design'), 'cta' => __('Shop Men', 'omega-design'), 'image' => 'promos/mens-essentials.png'],
	['title' => __("Kids' Fashion", 'omega-design'),        'sub' => __('Little Outfits', 'omega-design'),  'body' => __('Big smiles', 'omega-design'), 'cta' => __('Shop Kids', 'omega-design'), 'image' => 'promos/kids-fashion.png'],
];
foreach ($promos as $i => $promo) :
	// The photo itself already has this promo's title/offer copy baked in
	// (screenshot-derived asset), so only the button is repeated here - as
	// real, editable markup rather than a second stack of text fighting the
	// photo's own for the same space. omega-promo-card bottom-anchors it
	// below where that baked-in text sits.
	$content = '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#">' . esc_html($promo['cta']) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' . "\n";
	$badge = 0 === $i ? omega_pattern_corner_badge('omega-sale-badge', __('Limited Time', 'omega-design'), __('-40%', 'omega-design')) : '';
	?>
<!-- wp:column -->
<div class="wp-block-column">
<?php echo omega_pattern_photo_card('omega-photo-card--tall omega-promo-card', $content, $badge, omega_pattern_fashion_asset($promo['image'])); ?>
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
