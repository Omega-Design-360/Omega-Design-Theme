<?php
/**
 * Title: Section - Fashion Store: Hero Slider
 * Slug: omega-design/section-fashion-hero
 * Categories: omega-design-sections
 * Description: The rotating hero slider from the Fashion Store landing page (heading, copy, CTA button and stats per slide) - insertable on its own, on any page.
 * Keywords: hero, slider, banner, section
 *
 * Split out of pattern/landing-fashion-store.php so it can be inserted on
 * its own - see includes/patterns/pattern-helpers.php for the shared
 * markup builders both files use.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();
$eyebrow = 'omega_pattern_eyebrow';
?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":7000,"loop":true,"showArrows":true,"showDots":true,"align":"full","className":"omega-hero-slider","prevLabel":"Previous slide","nextLabel":"Next slide","dotsLabel":"Slides"} -->
<div class="wp-block-omega-design-slider omega-hero-slider alignfull omega-slider" data-autoplay="1" data-autoplay-speed="7000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous slide" data-next-label="Next slide" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$hero_slides = [
	['eyebrow' => __('New Collection', 'omega-design'), 'heading' => __('Style Without Limits', 'omega-design'), 'body' => __('Trendy. Comfortable. Uniquely you. Explore the latest fashion for women, men and kids.', 'omega-design'), 'cta' => __('Shop Now', 'omega-design'), 'stats' => [['10K+', __('Happy Customers', 'omega-design')], ['4.8★', __('Average Rating', 'omega-design')], ['100%', __('Secure Shopping', 'omega-design')]]],
	['eyebrow' => __('This Week Only', 'omega-design'), 'heading' => __('New Arrivals Every Week', 'omega-design'), 'body' => __('Fresh drops across women\'s, men\'s and kids\' collections, updated weekly so there\'s always something new.', 'omega-design'), 'cta' => __('Shop New Arrivals', 'omega-design'), 'stats' => [['500+', __('New Styles', 'omega-design')], ['4.8★', __('Average Rating', 'omega-design')], ['100%', __('Secure Shopping', 'omega-design')]]],
	['eyebrow' => __('Limited Time', 'omega-design'), 'heading' => __('Free Shipping On Orders $50+', 'omega-design'), 'body' => __('No code needed, free standard shipping is automatically applied at checkout on every order over $50.', 'omega-design'), 'cta' => __('Start Shopping', 'omega-design'), 'stats' => [['Free', __('Shipping $50+', 'omega-design')], ['30-Day', __('Easy Returns', 'omega-design')], ['100%', __('Secure Checkout', 'omega-design')]]],
];
$hero_image = omega_pattern_fashion_asset('hero/hero-models.png');
$hero_badge = omega_pattern_fashion_asset('hero/sale-badge.png');
foreach ($hero_slides as $i => $slide) :
	?>
<!-- wp:group {"backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<?php echo $eyebrow($slide['eyebrow']); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php echo esc_html($slide['heading']); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php echo esc_html($slide['body']); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html($slide['cta']); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ($slide['stats'] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-stat__value","fontSize":"large"} --><p class="omega-stat__value has-large-font-size"><?php echo esc_html($stat[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"omega-hero-visual"} -->
<div class="wp-block-column omega-hero-visual" style="flex-basis:50%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Hero image', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
<?php if (0 === $i) : ?>
<!-- wp:image {"className":"omega-hero-badge"} -->
<figure class="wp-block-image omega-hero-badge"><img src="<?php echo esc_url($hero_badge); ?>" alt="<?php esc_attr_e('Up to 50% off', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
<?php endif; ?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->
