<?php
/**
 * Title: Section - Fashion Store: Testimonials
 * Slug: omega-design/section-fashion-testimonials
 * Categories: omega-design-sections
 * Description: A rotating customer-testimonial carousel (avatar, star rating, quote, name) - insertable on its own, on any page.
 * Keywords: testimonials, reviews, carousel, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();
?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-group alignwide has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)">
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('What Our Customers Say', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":5000,"loop":true,"showArrows":true,"showDots":true,"className":"omega-testimonial-slider","prevLabel":"Previous testimonial","nextLabel":"Next testimonial","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="5000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous testimonial" data-next-label="Next testimonial" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	['quote' => __('Amazing quality and super fast delivery! I\'m in love with my new dress. Will definitely shop again!', 'omega-design'), 'name' => __('Sarah K.', 'omega-design')],
	['quote' => __('Customer support was incredibly helpful when I needed to exchange a size. Smooth experience start to finish.', 'omega-design'), 'name' => __('James T.', 'omega-design')],
	['quote' => __('The quality is even better than the photos. This is my new go-to store for everyday essentials.', 'omega-design'), 'name' => __('Maria L.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:group {"layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"center"}} -->
<div class="wp-block-group">
<!-- wp:image {"className":"omega-round-image omega-avatar-lg","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-lg"><img src="<?php echo esc_url(omega_pattern_fashion_asset('blog/testimonial-avatar.png')); ?>" alt="<?php echo esc_attr($t['name']); ?>"/></figure><!-- /wp:image -->
</div>
<!-- /wp:group -->
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
