<?php
/**
 * Title: Landing Page - Beauty & Cosmetics
 * Slug: omega-design/landing-beauty-cosmetics
 * Categories: omega-design-general
 * Description: A full beauty & cosmetics landing page - hero, trust strip, a shop-by-category row, tabbed featured products (live WooCommerce data), a promo banner, a feature strip, a testimonial slider, and a newsletter signup. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, beauty, cosmetics, skincare, makeup, shop, woocommerce
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-digital-agency.php: a
 * small, boring vocabulary of backgroundColor/textColor/fontSize-by-slug,
 * block align, and preset-token spacing, plus shared className hooks
 * (assets/css/landing-pages.css, assets/css/landing-beauty-cosmetics.css)
 * for anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks, so the saved markup always matches what the
 * block editor itself would generate and never trips "attempt recovery".
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();

/** One category card: icon, title, inside a Group carrying a numbered `omega-category-card--N` class (tint in assets/css/landing-beauty-cosmetics.css). */
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
<!-- wp:group {"className":"omega-landing omega-landing--beauty-cosmetics","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--beauty-cosmetics">

<?php // HERO ?>
<!-- wp:group {"backgroundColor":"surface","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<?php echo omega_pattern_eyebrow(__('Clean Beauty, Real Results', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Beauty That\'s Uniquely You', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Skincare, makeup and fragrance made with ingredients you can trust - cruelty-free, dermatologist tested, always.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Shop Now', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Take the Skin Quiz', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['200+', __('Products', 'omega-design')], ['80K+', __('Happy Customers', 'omega-design')], ['4.9★', __('Average Rating', 'omega-design')], ['100%', __('Cruelty-Free', 'omega-design')]] as $stat) : ?>
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
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Skincare and cosmetics', 'omega-design'); ?>"/></figure><!-- /wp:image -->
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
	['icon' => 'local-shipping', 'title' => __('Free Shipping', 'omega-design'),   'desc' => __('On orders over $40', 'omega-design')],
	['icon' => 'volunteer-activism', 'title' => __('Cruelty-Free', 'omega-design'), 'desc' => __('Never tested on animals', 'omega-design')],
	['icon' => 'security',       'title' => __('Secure Payment', 'omega-design'),  'desc' => __('100% protected checkout', 'omega-design')],
	['icon' => 'support-agent',  'title' => __('Beauty Advisors', 'omega-design'), 'desc' => __('Free personalized advice', 'omega-design')],
	['icon' => 'inventory-2',    'title' => __('Easy Returns', 'omega-design'),    'desc' => __('30-day hassle free', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SHOP BY CATEGORY ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Shop by Category', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Your Routine, Reimagined', 'omega-design'); ?></h2><!-- /wp:heading -->
<?php
echo $build_category_row([
	['water-drop',    __('Skincare', 'omega-design'), 1],
	['face-retouching-natural', __('Makeup', 'omega-design'), 2],
	['content-cut',   __('Haircare', 'omega-design'), 3],
	['local-florist', __('Fragrance', 'omega-design'), 4],
]);
echo $build_category_row([
	['bathtub',       __('Bath & Body', 'omega-design'), 5],
	['brush',         __('Tools & Brushes', 'omega-design'), 6],
	['spa',           __('Wellness', 'omega-design'), 7],
	['redeem',        __('Gift Sets', 'omega-design'), 8],
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURED PRODUCTS (TABS) ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Featured Products', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Bestsellers & New Drops', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(41, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('New Arrivals', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(42, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('On Sale', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(43, 'date', true); ?></div></div>
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
	'bg'          => '#500724',
	'offer_color' => '#fbbf24',
	'eyebrow'     => __('Limited Time', 'omega-design'),
	'title'       => __('Up to 30% Off Skincare Sets', 'omega-design'),
	'offer'       => __('This Week Only', 'omega-design'),
	'body'        => __('Bundle your routine and save - cleanser, serum and moisturizer, curated for your skin type.', 'omega-design'),
	'cta'         => __('Shop Skincare', 'omega-design'),
	'image_url'   => $omega_ph,
	'image_alt'   => __('Skincare set deal', 'omega-design'),
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'eco',               'title' => __('Clean Ingredients', 'omega-design'), 'desc' => __('No parabens or sulfates', 'omega-design')],
	['icon' => 'verified',          'title' => __('Dermatologist Tested', 'omega-design'), 'desc' => __('Safe for sensitive skin', 'omega-design')],
	['icon' => 'local-shipping',    'title' => __('Discreet Shipping', 'omega-design'), 'desc' => __('Plain, unmarked packaging', 'omega-design')],
	['icon' => 'workspace-premium', 'title' => __('Premium Formulas', 'omega-design'), 'desc' => __('Backed by real science', 'omega-design')],
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
	['quote' => __('My skin has never looked better. The skin quiz actually matched me with products that work for my type.', 'omega-design'), 'name' => __('Chloe M.', 'omega-design')],
	['quote' => __('Fast, discreet shipping and the packaging is beautiful. Feels like a treat every time it arrives.', 'omega-design'), 'name' => __('Aisha R.', 'omega-design')],
	['quote' => __('Finally a brand that\'s actually cruelty-free and the products still outperform luxury brands I used to buy.', 'omega-design'), 'name' => __('Grace L.', 'omega-design')],
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
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Join the Glow List', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('New launches, beauty tips and exclusive offers - straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
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
