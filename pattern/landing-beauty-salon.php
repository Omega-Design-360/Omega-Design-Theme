<?php
/**
 * Title: Landing Page - Beauty Salon
 * Slug: omega-design/landing-beauty-salon
 * Categories: omega-design-general
 * Description: A luxury cream/gold beauty salon landing page - hero with a portrait, script tagline and floating rating card, a services card grid, an about section, signature treatments with prices, a dark before/after "Real Transformations" band, a "Why Choose Us" row, a first-visit offer banner, a testimonial slider, an Instagram strip and a closing booking CTA. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, beauty salon, salon, spa, hair, nails, beauty
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-law-firm.php: only
 * backgroundColor/textColor/fontSize-by-slug, block align and preset-token
 * spacing in the block attributes, plus className hooks styled by
 * assets/css/landing-pages.css and assets/css/landing-beauty-salon.css -
 * no hand-typed multi-property inline `style` JSON and no Cover blocks, so
 * the saved markup matches what the block editor itself generates and
 * never trips "attempt recovery". The cream/gold look comes from
 * landing-beauty-salon.css remapping this page's preset color variables.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

/** URL of one of this template's own photos in assets/images/beauty-salon-template/. */
$salon_img = function ($file) {
	return esc_url(OMEGA_DESIGN_IMAGES_URI . '/beauty-salon-template/' . $file);
};

/** Opens a full-width section wrapper; close it with $section_close. */
$section_open = function ($class, $bg = '') {
	$attrs = '"className":"' . esc_attr($class) . '","align":"full",';
	$classes = 'wp-block-group alignfull ' . esc_attr($class);
	if ($bg) {
		$attrs .= '"backgroundColor":"' . esc_attr($bg) . '",';
		$classes .= ' has-' . esc_attr($bg) . '-background-color has-background';
	}
	$out = '<!-- wp:group {' . $attrs . '"style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->' . "\n";
	$out .= '<div class="' . $classes . ' omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">' . "\n";
	return $out;
};
$section_close = '</div>' . "\n<!-- /wp:group -->\n\n";

/** Centered section heading + subtitle. */
$section_heading = function ($title, $subtitle) {
	$out = '<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">' . esc_html($title) . '</h2><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"align":"center","className":"omega-salon-subtitle","fontSize":"small"} --><p class="has-text-align-center omega-salon-subtitle has-small-font-size">' . esc_html($subtitle) . '</p><!-- /wp:paragraph -->' . "\n";
	return $out;
};

/** A small round "→" link used at the corner of cards. */
$arrow_link = function ($class = 'omega-salon-arrow') {
	return '<!-- wp:paragraph {"className":"' . esc_attr($class) . '"} --><p class="' . esc_attr($class) . '"><a href="#" aria-label="' . esc_attr__('Learn more', 'omega-design') . '">→</a></p><!-- /wp:paragraph -->' . "\n";
};

/** One hero stat ("500+" / "Happy Clients"). */
$build_stat = function ($value, $label) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-stat","layout":{"type":"constrained"}} -->' . "\n<div class=\"wp-block-group omega-salon-stat\">\n";
	$out .= '<!-- wp:paragraph {"className":"omega-salon-stat__value","textColor":"primary","fontSize":"x-large"} --><p class="omega-salon-stat__value has-primary-color has-text-color has-x-large-font-size">' . esc_html($value) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One service card: photo, an icon badge overlapping its bottom edge, title + arrow, description. */
$build_service_card = function ($icon, $title, $desc, $image) use ($salon_img, $arrow_link) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-salon-card has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card__media","layout":{"type":"constrained"}} -->' . "\n<div class=\"wp-block-group omega-salon-card__media\">\n";
	$out .= '<!-- wp:image {"className":"omega-salon-card__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-card__image"><img src="' . $salon_img($image) . '" alt="' . esc_attr($title) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card__icon","layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-group omega-salon-card__icon">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card__body","layout":{"type":"constrained"}} --><div class="wp-block-group omega-salon-card__body">' . "\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card__title-row","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} --><div class="wp-block-group omega-salon-card__title-row">' . "\n";
	$out .= '<!-- wp:heading {"level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-medium-font-size">' . esc_html($title) . '</h3><!-- /wp:heading -->' . "\n";
	$out .= $arrow_link('omega-salon-arrow omega-salon-arrow--solid');
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One signature treatment card: photo, title, short description, price + duration + arrow. */
$build_treatment_card = function ($title, $desc, $price, $duration, $image) use ($salon_img, $arrow_link) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card omega-salon-card--treatment","backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-salon-card omega-salon-card--treatment has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-salon-card__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-card__image"><img src="' . $salon_img($image) . '" alt="' . esc_attr($title) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-salon-card__body","layout":{"type":"constrained"}} --><div class="wp-block-group omega-salon-card__body">' . "\n";
	$out .= '<!-- wp:heading {"level":3,"fontSize":"small"} --><h3 class="wp-block-heading has-small-font-size">' . esc_html($title) . '</h3><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-salon-price-row","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} --><div class="wp-block-group omega-salon-price-row">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"heading","fontSize":"medium"} --><p class="omega-strong has-heading-color has-text-color has-medium-font-size">' . esc_html($price) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size">' . esc_html($duration) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= $arrow_link('omega-salon-arrow omega-salon-arrow--outline');
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One before/after pair - two photos side by side, each with a pill label. */
$build_before_after = function ($alt, $before, $after) use ($salon_img) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-ba","layout":{"type":"flex","flexWrap":"nowrap"}} -->' . "\n<div class=\"wp-block-group omega-salon-ba\">\n";
	foreach ([[__('Before', 'omega-design'), $before], [__('After', 'omega-design'), $after]] as [$label, $image]) {
		$out .= '<!-- wp:group {"className":"omega-salon-ba__item","layout":{"type":"constrained"}} --><div class="wp-block-group omega-salon-ba__item">' . "\n";
		$out .= '<!-- wp:image {"className":"omega-salon-ba__image","sizeSlug":"medium"} --><figure class="wp-block-image size-medium omega-salon-ba__image"><img src="' . $salon_img($image) . '" alt="' . esc_attr($alt . ' - ' . $label) . '"/></figure><!-- /wp:image -->' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-salon-ba__label","fontSize":"x-small"} --><p class="omega-salon-ba__label has-x-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n";
	}
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One "Why Choose" item: round tinted icon + title + description. */
$build_why_item = function ($icon, $title, $desc) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-why","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->' . "\n<div class=\"wp-block-group omega-salon-why\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-why__icon","layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-group omega-salon-why__icon">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"primary","style":{"dimensions":{"width":"26px"}}} /-->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"heading","fontSize":"small"} --><p class="omega-strong has-heading-color has-text-color has-small-font-size">' . esc_html($title) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One testimonial card: avatar + name + stars, then the quote. */
$build_testimonial = function ($name, $quote, $avatar) use ($salon_img) {
	$out = '<!-- wp:group {"className":"omega-card omega-salon-testimonial","backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-card omega-salon-testimonial has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-round-image omega-avatar-lg","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-lg"><img src="' . $salon_img($avatar) . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"heading","fontSize":"small"} --><p class="omega-strong has-heading-color has-text-color has-small-font-size">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">★★★★★</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">"' . esc_html($quote) . '"</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** One icon + label item in the closing CTA band. */
$build_cta_feature = function ($icon, $label) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-salon-cta-feature","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} --><div class="wp-block-group omega-salon-cta-feature">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"accent","style":{"dimensions":{"width":"36px"}}} /-->' . "\n";
	$out .= '<!-- wp:paragraph {"align":"center","fontSize":"x-small"} --><p class="has-text-align-center has-x-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

$build_row = function ($items, $builder, $class = '') {
	$attrs = $class ? '"className":"' . esc_attr($class) . '",' : '';
	$out = '<!-- wp:columns {' . $attrs . '"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide" . ($class ? ' ' . esc_attr($class) : '') . "\">\n";
	foreach ($items as $item) {
		$out .= $builder(...$item);
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--beauty-salon omega-lang-en","backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--beauty-salon omega-lang-en has-background-background-color has-background">

<?php // HERO ?>
<?php echo $section_open('omega-salon-hero'); ?>
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
<?php echo pattern_helpers::eyebrow(__('Luxury Beauty & Wellness', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Your Beauty,', 'omega-design'); ?><br><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-primary-color"><?php esc_html_e('Refined.', 'omega-design'); ?></mark></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Professional care, modern techniques and a relaxing environment to bring out your natural beauty.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Book an Appointment →', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline omega-salon-btn-circle"} --><div class="wp-block-button is-style-outline omega-salon-btn-circle"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Explore Services', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"isStackedOnMobile":false,"className":"omega-salon-stats"} -->
<div class="wp-block-columns is-not-stacked-on-mobile omega-salon-stats">
<?php
foreach ([['500+', __('Happy Clients', 'omega-design')], ['5+', __('Years Experience', 'omega-design')], ['20+', __('Beauty Experts', 'omega-design')]] as $stat) {
	echo $build_stat($stat[0], $stat[1]);
}
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"52%"} -->
<div class="wp-block-column" style="flex-basis:52%">
<!-- wp:group {"className":"omega-salon-hero-art","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-salon-hero-art">
<!-- wp:image {"className":"omega-salon-hero-portrait","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-hero-portrait"><img src="<?php echo $salon_img('hero.webp'); ?>" alt="<?php esc_attr_e('Beauty salon model portrait', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-hero-tagline","textColor":"primary"} --><p class="omega-salon-script omega-salon-hero-tagline has-primary-color has-text-color"><?php esc_html_e('Beauty', 'omega-design'); ?><br><?php esc_html_e('Looks Good', 'omega-design'); ?><br><?php esc_html_e('On You', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-rating-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-salon-rating-card has-surface-background-color has-background">
<!-- wp:paragraph {"className":"omega-salon-rating-card__value","textColor":"heading","fontSize":"x-large"} --><p class="omega-salon-rating-card__value has-heading-color has-text-color has-x-large-font-size"><?php esc_html_e('4.9', 'omega-design'); ?> <mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-color">★</mark></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size"><?php esc_html_e('500+ Happy Clients', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-avatars","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-salon-avatars">
<?php for ($i = 1; $i <= 4; $i++) : ?>
<!-- wp:image {"className":"omega-round-image omega-avatar-md","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $salon_img('avatar-' . $i . '.webp'); ?>" alt="<?php esc_attr_e('Happy client', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<?php endfor; ?>
<!-- wp:paragraph {"className":"omega-salon-avatars__more"} --><p class="omega-salon-avatars__more">+</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
<?php echo $section_close; ?>

<?php // OUR SERVICES ?>
<?php echo $section_open('omega-salon-services'); ?>
<!-- wp:group {"className":"omega-salon-section-head","align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide omega-salon-section-head">
<?php echo $section_heading(__('Our Services', 'omega-design'), __('Discover our range of premium beauty services designed to make you look and feel your absolute best.', 'omega-design')); ?>
<!-- wp:buttons {"className":"omega-salon-view-all","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons omega-salon-view-all">
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('View All Services →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<?php
echo $build_row([
	['content-cut',       __('Hair Styling', 'omega-design'),  __('Trendy cuts, styling, coloring and more for your perfect look.', 'omega-design'), 'service-hair-styling.webp'],
	['spa',               __('Skin & Facial', 'omega-design'), __('Rejuvenating facials and advanced skin treatments.', 'omega-design'), 'service-skin-facial.webp'],
	['brush',             __('Nails & Beauty', 'omega-design'), __('Manicure, pedicure and nail art for every style.', 'omega-design'), 'service-nails-beauty.webp'],
	['self-improvement',  __('Spa & Wellness', 'omega-design'), __('Relaxing treatments to refresh your mind and body.', 'omega-design'), 'service-spa-wellness.webp'],
], $build_service_card);
?>
<?php echo $section_close; ?>

<?php // ABOUT ?>
<!-- wp:group {"className":"omega-salon-about","align":"full","backgroundColor":"page-background","layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-salon-about has-page-background-background-color has-background omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"align":"full","verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"0"}}}} -->
<div class="wp-block-columns alignfull are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:image {"className":"omega-salon-about__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-about__image"><img src="<?php echo $salon_img('about.webp'); ?>" alt="<?php esc_attr_e('Stylist working in the salon', 'omega-design'); ?>"/></figure><!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%","className":"omega-salon-about__content"} -->
<div class="wp-block-column is-vertically-aligned-center omega-salon-about__content" style="flex-basis:50%">
<?php echo pattern_helpers::eyebrow(__('About Luméa', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Beauty designed', 'omega-design'); ?><br><?php esc_html_e('around you.', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('At Luméa, we believe beauty is more than just a look — it\'s a feeling. Our expert stylists and therapists are dedicated to providing personalized treatments in a luxurious and relaxing environment.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-about__features","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group omega-salon-about__features">
<?php foreach ([['groups', __('Expert Team', 'omega-design')], ['sanitizer', __('Premium Products', 'omega-design')], ['face-retouching-natural', __('Personalized Care', 'omega-design')]] as $f) : ?>
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|xs"}}} --><div class="wp-block-group">
<!-- wp:icon {"icon":"omega-icons/<?php echo esc_attr($f[0]); ?>","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->
<!-- wp:paragraph {"className":"omega-strong","textColor":"heading","fontSize":"x-small"} --><p class="omega-strong has-heading-color has-text-color has-x-small-font-size"><?php echo esc_html($f[1]); ?></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Our Story →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // SIGNATURE TREATMENTS ?>
<?php echo $section_open('omega-salon-treatments'); ?>
<?php echo $section_heading(__('Signature Treatments', 'omega-design'), __('Experience our most loved beauty treatments, curated by experts.', 'omega-design')); ?>
<?php
echo $build_row([
	[__('Premium Hair Makeover', 'omega-design'), __('Cut, color & styling', 'omega-design'), '$120', __('90 mins', 'omega-design'), 'treatment-hair-makeover.webp'],
	[__('Hydrating Facial', 'omega-design'),      __('Deep cleansing & glow', 'omega-design'), '$80', __('60 mins', 'omega-design'), 'treatment-hydrating-facial.webp'],
	[__('Luxury Manicure', 'omega-design'),       __('Premium nail care', 'omega-design'), '$50', __('45 mins', 'omega-design'), 'treatment-luxury-manicure.webp'],
	[__('Relaxing Body Massage', 'omega-design'), __('Full body relaxation', 'omega-design'), '$90', __('60 mins', 'omega-design'), 'treatment-body-massage.webp'],
], $build_treatment_card);
?>
<?php echo $section_close; ?>

<?php // REAL TRANSFORMATIONS ?>
<?php echo $section_open('omega-salon-transformations', 'secondary'); ?>
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"24%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:24%">
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-transformations__title","textColor":"accent"} --><p class="omega-salon-script omega-salon-transformations__title has-accent-color has-text-color"><?php esc_html_e('Real', 'omega-design'); ?><br><?php esc_html_e('Transformations', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"accent","fontSize":"x-small"} --><p class="omega-eyebrow has-accent-color has-text-color has-x-small-font-size"><?php esc_html_e('See the confidence in every look.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"secondary"} --><div class="wp-block-button"><a class="wp-block-button__link has-secondary-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('View Gallery →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"76%"} -->
<div class="wp-block-column" style="flex-basis:76%">
<?php
echo $build_row([
	[__('Skin transformation', 'omega-design'), 'face-before.webp', 'face-after.webp'],
	[__('Hair transformation', 'omega-design'), 'hair-before.webp', 'hair-after.webp'],
	[__('Hand & nail care transformation', 'omega-design'), 'hand-before.webp', 'hand-after.webp'],
], $build_before_after);
?>
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<?php echo $section_close; ?>

<?php // WHY CHOOSE ?>
<?php echo $section_open('omega-salon-why-section'); ?>
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('Why Choose Luméa?', 'omega-design'); ?></h2><!-- /wp:heading -->
<?php
echo $build_row([
	['groups',            __('Expert Beauty Specialists', 'omega-design'), __('Trained professionals with years of experience.', 'omega-design')],
	['workspace-premium', __('Premium Products', 'omega-design'),          __('We use only high-quality, skin-friendly products.', 'omega-design')],
	['favorite',          __('Personalized Treatments', 'omega-design'),   __('Every service is tailored to your unique needs.', 'omega-design')],
	['spa',               __('Relaxing Private Experience', 'omega-design'), __('A calm and luxurious environment just for you.', 'omega-design')],
], $build_why_item, 'omega-salon-why-row');
?>
<?php echo $section_close; ?>

<?php // SPECIAL OFFER ?>
<!-- wp:group {"className":"omega-salon-offer","align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-salon-offer omega-animate" style="padding-right:var(--wp--preset--spacing--l);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"50%","className":"omega-salon-offer__content"} -->
<div class="wp-block-column is-vertically-aligned-center omega-salon-offer__content" style="flex-basis:50%">
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-offer__eyebrow","textColor":"primary"} --><p class="omega-salon-script omega-salon-offer__eyebrow has-primary-color has-text-color"><?php esc_html_e('Special Offer', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Your First Visit', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size"><?php esc_html_e('Enjoy', 'omega-design'); ?> <strong><?php esc_html_e('20% OFF', 'omega-design'); ?></strong> <?php esc_html_e('your first beauty treatment.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"secondary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-secondary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Claim Your Offer →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:group {"className":"omega-salon-offer__art","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-salon-offer__art">
<!-- wp:image {"className":"omega-salon-offer__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-offer__image"><img src="<?php echo $salon_img('offer.webp'); ?>" alt="<?php esc_attr_e('Relaxed client after a treatment', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"className":"omega-salon-offer__badge","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group omega-salon-offer__badge">
<!-- wp:paragraph {"align":"center","className":"omega-salon-offer__badge-value","textColor":"heading"} --><p class="has-text-align-center omega-salon-offer__badge-value has-heading-color has-text-color"><?php esc_html_e('20%', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","textColor":"heading","fontSize":"large"} --><p class="has-text-align-center omega-strong has-heading-color has-text-color has-large-font-size"><?php esc_html_e('OFF', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<?php echo $section_open('omega-salon-testimonials'); ?>
<?php echo $section_heading(__('What Our Clients Say', 'omega-design'), __('Real stories, real results from our valued clients.', 'omega-design')); ?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":false,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","className":"omega-testimonial-slider omega-salon-slider","prevLabel":"Previous testimonials","nextLabel":"Next testimonials","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-salon-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="Previous testimonials" data-next-label="Next testimonials" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
echo $build_testimonial(__('Sarah Ahmed', 'omega-design'), __('Absolutely love the service! The staff are so professional and the results are amazing. Best salon experience ever.', 'omega-design'), 'avatar-3.webp');
echo $build_testimonial(__('Emily Carter', 'omega-design'), __('The facial treatment was incredible. My skin has never looked better! Highly recommended.', 'omega-design'), 'avatar-2.webp');
echo $build_testimonial(__('Priya Sharma', 'omega-design'), __('Such a relaxing and luxurious experience. The team really makes you feel special. I\'ll definitely be back!', 'omega-design'), 'avatar-4.webp');
echo $build_testimonial(__('Layla Hassan', 'omega-design'), __('From booking to the final look, everything was flawless. My hair has never felt so healthy.', 'omega-design'), 'avatar-1.webp');
?>
</div>
<!-- /wp:omega-design/slider -->
<?php echo $section_close; ?>

<?php // INSTAGRAM ?>
<!-- wp:group {"className":"omega-salon-instagram","align":"full","backgroundColor":"page-background","style":{"spacing":{"padding":{"top":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-salon-instagram has-page-background-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"24%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:24%">
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-instagram__eyebrow","textColor":"primary"} --><p class="omega-salon-script omega-salon-instagram__eyebrow has-primary-color has-text-color"><?php esc_html_e('Follow Our Journey', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"fontSize":"x-large"} --><h3 class="wp-block-heading has-x-large-font-size"><?php esc_html_e('@lumea.beautysalon', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size"><?php esc_html_e('Beauty · Lifestyle · Inspiration', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Follow Us →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"76%"} -->
<div class="wp-block-column" style="flex-basis:76%">
<!-- wp:group {"className":"omega-salon-instagram__grid","layout":{"type":"grid","columnCount":6}} -->
<div class="wp-block-group omega-salon-instagram__grid">
<?php for ($i = 1; $i <= 6; $i++) : ?>
<!-- wp:image {"className":"omega-rounded-image","sizeSlug":"medium"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $salon_img('instagram-' . $i . '.webp'); ?>" alt="<?php esc_attr_e('Instagram post', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<?php endfor; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // CTA ?>
<?php echo $section_open('omega-salon-cta', 'secondary'); ?>
<!-- wp:image {"className":"omega-salon-cta__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-salon-cta__bg"><img src="<?php echo $salon_img('cta.webp'); ?>" alt=""/></figure><!-- /wp:image -->
<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"omega-salon-cta__content"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center omega-salon-cta__content">
<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Ready for your next', 'omega-design'); ?><br><?php esc_html_e('beauty moment?', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('Reserve your appointment today and let us take care of you.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"secondary"} --><div class="wp-block-button"><a class="wp-block-button__link has-secondary-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Book an Appointment →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
<!-- wp:columns {"isStackedOnMobile":false} -->
<div class="wp-block-columns is-not-stacked-on-mobile">
<?php
foreach ([['event-available', __('Easy Booking', 'omega-design')], ['diamond', __('Premium Experience', 'omega-design')], ['favorite', __('Feel Amazing', 'omega-design')]] as $f) {
	echo $build_cta_feature($f[0], $f[1]);
}
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<?php echo $section_close; ?>

</div>
<!-- /wp:group -->
