<?php
/**
 * Title: Landing Page - Furniture Store
 * Slug: omega-design/landing-furniture-store
 * Categories: omega-design-general
 * Description: A full furniture & home decor landing page - hero, trust strip, a shop-by-room row, tabbed featured products (live WooCommerce data), a promo banner, a feature strip, a testimonial slider, and a newsletter signup. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, furniture, home decor, interior, shop, woocommerce
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-digital-agency.php: a
 * small, boring vocabulary of backgroundColor/textColor/fontSize-by-slug,
 * block align, and preset-token spacing, plus shared className hooks
 * (assets/css/landing-pages.css, assets/css/landing-furniture-store.css)
 * for anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks, so the saved markup always matches what the
 * block editor itself would generate and never trips "attempt recovery".
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();

/** One room card: icon, title, inside a Group carrying a numbered `omega-category-card--N` class (tint in assets/css/landing-furniture-store.css). */
$build_category_card = function ($icon, $title, $variant) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-category-card omega-category-card--' . (int) $variant . '","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-category-card omega-category-card--' . (int) $variant . '">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"heading","style":{"dimensions":{"width":"32px"}}} /-->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"medium"} --><p class="omega-strong has-medium-font-size">' . esc_html($title) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

$build_category_row = function ($items) use ($build_category_card) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= $build_category_card($item[0], $item[1], $item[2]);
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--furniture-store","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--furniture-store">

<?php // HERO ?>
<!-- wp:group {"backgroundColor":"surface","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<?php echo omega_pattern_eyebrow(__('Timeless Comfort', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Furniture That Feels Like Home', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Handcrafted pieces for every room, built to last and designed to make your space feel like you.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Shop Now', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Browse Rooms', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['15+', __('Years in Business', 'omega-design')], ['30K+', __('Pieces Delivered', 'omega-design')], ['4.9★', __('Average Rating', 'omega-design')], ['Free', __('White-Glove Delivery', 'omega-design')]] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-stat__value","textColor":"primary","fontSize":"large"} --><p class="omega-stat__value has-primary-color has-text-color has-large-font-size"><?php echo esc_html($stat[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%">
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Living room furniture', 'omega-design'); ?>"/></figure><!-- /wp:image -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TRUST STRIP ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'local-shipping', 'title' => __('Free Delivery', 'omega-design'),   'desc' => __('White-glove & assembly', 'omega-design')],
	['icon' => 'verified',       'title' => __('Quality Guarantee', 'omega-design'), 'desc' => __('Built to last', 'omega-design')],
	['icon' => 'security',       'title' => __('Secure Payment', 'omega-design'),  'desc' => __('100% protected checkout', 'omega-design')],
	['icon' => 'support-agent',  'title' => __('Design Support', 'omega-design'),  'desc' => __('Free room planning help', 'omega-design')],
	['icon' => 'inventory-2',    'title' => __('Easy Returns', 'omega-design'),    'desc' => __('30-day hassle free', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SHOP BY ROOM ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Shop by Room', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Furnish Every Corner', 'omega-design'); ?></h2><!-- /wp:heading -->
<?php
echo $build_category_row([
	['weekend',        __('Living Room', 'omega-design'), 1],
	['bed',            __('Bedroom', 'omega-design'), 2],
	['table-restaurant', __('Dining', 'omega-design'), 3],
	['chair',          __('Office', 'omega-design'), 4],
]);
echo $build_category_row([
	['deck',           __('Outdoor', 'omega-design'), 5],
	['lightbulb',      __('Lighting', 'omega-design'), 6],
	['local-florist',  __('Decor', 'omega-design'), 7],
	['category',       __('Storage', 'omega-design'), 8],
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURED PRODUCTS (TABS) ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Featured Pieces', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Customer Favorites', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(31, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('New Arrivals', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(32, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('On Sale', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(33, 'date', true); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->

<?php // PROMO ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo omega_pattern_split_promo([
	'bg'          => '#292524',
	'offer_color' => '#65a30d',
	'eyebrow'     => __('Limited Time', 'omega-design'),
	'title'       => __('Up to 25% Off Dining Sets', 'omega-design'),
	'offer'       => __('This Month Only', 'omega-design'),
	'body'        => __('Solid wood tables and chairs built for family dinners for years to come.', 'omega-design'),
	'cta'         => __('Shop Dining', 'omega-design'),
	'image_url'   => $omega_ph,
	'image_alt'   => __('Dining set deal', 'omega-design'),
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'build',             'title' => __('Handcrafted Quality', 'omega-design'), 'desc' => __('Real wood, real joinery', 'omega-design')],
	['icon' => 'yard',               'title' => __('Sustainably Sourced', 'omega-design'), 'desc' => __('Responsibly harvested materials', 'omega-design')],
	['icon' => 'local-shipping',     'title' => __('White-Glove Delivery', 'omega-design'), 'desc' => __('Placed exactly where you want it', 'omega-design')],
	['icon' => 'workspace-premium',  'title' => __('Lifetime Warranty', 'omega-design'), 'desc' => __('On every frame we build', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('What Our Customers Say', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":5000,"showArrows":true,"showDots":true,"className":"omega-testimonial-slider","prevLabel":"Previous testimonial","nextLabel":"Next testimonial","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="5000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous testimonial" data-next-label="Next testimonial" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	['quote' => __('The dining table is stunning in person and the delivery crew assembled everything and took the packaging away. Zero hassle.', 'omega-design'), 'name' => __('Rachel B.', 'omega-design')],
	['quote' => __('Ordered a full bedroom set and the quality is far beyond what I expected for the price. Will be back for the living room next.', 'omega-design'), 'name' => __('Tom W.', 'omega-design')],
	['quote' => __('Their design team helped me plan my whole apartment over a video call. Genuinely helpful, not just a sales pitch.', 'omega-design'), 'name' => __('Nadia F.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:group {"layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"align":"center","className":"omega-stars","fontSize":"large"} --><p class="has-text-align-center omega-stars has-large-font-size">★★★★★</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-italic","fontSize":"large"} --><p class="has-text-align-center omega-italic has-large-font-size">"<?php echo esc_html($t['quote']); ?>"</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-strong"} --><p class="has-text-align-center omega-strong"><?php echo esc_html($t['name']); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->

</div>
<!-- /wp:group -->

<?php // NEWSLETTER ?>
<!-- wp:group {"align":"full","className":"omega-newsletter-section","backgroundColor":"secondary","textColor":"button-text","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-newsletter-section has-button-text-color has-secondary-background-color has-text-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Get Design Inspiration', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('New collections and seasonal sales, delivered straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>">
<form class="omega-newsletter-form" novalidate>
<input type="text" name="omega_newsletter_company" class="omega-newsletter-form__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true"/>
<div class="omega-newsletter-form__row">
<input type="email" class="omega-newsletter-form__input" placeholder="<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>" name="omega_newsletter_email" required/>
<button type="submit" class="omega-newsletter-form__submit wp-element-button"><?php esc_html_e('Subscribe', 'omega-design'); ?></button>
</div>
<p class="omega-newsletter-form__message" aria-live="polite"></p>
</form>
</div>
<!-- /wp:omega-design/newsletter-form -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
