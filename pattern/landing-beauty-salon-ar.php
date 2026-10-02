<?php
/**
 * Title: Landing Page - Beauty Salon (Arabic)
 * Slug: omega-design/landing-beauty-salon-ar
 * Categories: omega-design-general
 * Description: Arabic, right-to-left version of the Beauty Salon landing page for GCC markets. A luxury cream/gold beauty salon landing page - hero with a portrait, script tagline and floating rating card, a services card grid, an about section, signature treatments with prices, a dark before/after "Real Transformations" band, a "Why Choose Us" row, a first-visit offer banner, a testimonial slider, an Instagram strip and a closing booking CTA. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, beauty salon, salon, spa, hair, nails, beauty, arabic, rtl, gcc, عربي, صالون تجميل
 * Viewport Width: 1400
 *
 * Arabic copy of pattern/landing-beauty-salon.php - same blocks and
 * classes, plus .omega-salon-ar on the wrapper, which switches
 * assets/css/landing-beauty-salon.css to right-to-left with Arabic fonts.
 * Keep the two files' structure in sync when changing either.
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
	return '<!-- wp:paragraph {"className":"' . esc_attr($class) . '"} --><p class="' . esc_attr($class) . '"><a href="#" aria-label="' . esc_attr__('اعرفي المزيد', 'omega-design') . '">→</a></p><!-- /wp:paragraph -->' . "\n";
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
	foreach ([[__('قبل', 'omega-design'), $before], [__('بعد', 'omega-design'), $after]] as [$label, $image]) {
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
<!-- wp:group {"className":"omega-landing omega-landing--beauty-salon omega-salon-ar omega-lang-ar","backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--beauty-salon omega-salon-ar omega-lang-ar has-background-background-color has-background">

<?php // HERO ?>
<?php echo $section_open('omega-salon-hero'); ?>
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
<?php echo pattern_helpers::eyebrow(__('الفخامة في الجمال والعناية', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('جمالكِ،', 'omega-design'); ?><br><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-primary-color"><?php esc_html_e('بلمسة راقية.', 'omega-design'); ?></mark></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('عناية احترافية وتقنيات حديثة في أجواء مريحة تُبرز جمالكِ الطبيعي.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('احجزي موعدكِ ←', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline omega-salon-btn-circle"} --><div class="wp-block-button is-style-outline omega-salon-btn-circle"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('استكشفي خدماتنا', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"isStackedOnMobile":false,"className":"omega-salon-stats"} -->
<div class="wp-block-columns is-not-stacked-on-mobile omega-salon-stats">
<?php
foreach ([['500+', __('عميلة سعيدة', 'omega-design')], ['5+', __('سنوات من الخبرة', 'omega-design')], ['20+', __('خبيرة تجميل', 'omega-design')]] as $stat) {
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
<!-- wp:image {"className":"omega-salon-hero-portrait","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-hero-portrait"><img src="<?php echo $salon_img('hero.png'); ?>" alt="<?php esc_attr_e('صورة عارضة صالون التجميل', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-hero-tagline","textColor":"primary"} --><p class="omega-salon-script omega-salon-hero-tagline has-primary-color has-text-color"><?php esc_html_e('الجمال', 'omega-design'); ?><br><?php esc_html_e('يليق', 'omega-design'); ?><br><?php esc_html_e('بكِ', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-rating-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-salon-rating-card has-surface-background-color has-background">
<!-- wp:paragraph {"className":"omega-salon-rating-card__value","textColor":"heading","fontSize":"x-large"} --><p class="omega-salon-rating-card__value has-heading-color has-text-color has-x-large-font-size"><?php esc_html_e('4.9', 'omega-design'); ?> <mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-color">★</mark></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size"><?php esc_html_e('+500 عميلة سعيدة', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-avatars","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-salon-avatars">
<?php for ($i = 1; $i <= 4; $i++) : ?>
<!-- wp:image {"className":"omega-round-image omega-avatar-md","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $salon_img('avatar-' . $i . '.jpg'); ?>" alt="<?php esc_attr_e('عميلة سعيدة', 'omega-design'); ?>"/></figure><!-- /wp:image -->
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
<?php echo $section_heading(__('خدماتنا', 'omega-design'), __('اكتشفي مجموعتنا من خدمات التجميل الفاخرة المصممة لتمنحكِ أجمل إطلالة وأفضل إحساس.', 'omega-design')); ?>
<!-- wp:buttons {"className":"omega-salon-view-all","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons omega-salon-view-all">
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('عرض جميع الخدمات ←', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<?php
echo $build_row([
	['content-cut',       __('تصفيف الشعر', 'omega-design'),  __('قصّات عصرية وتصفيف وصبغات وأكثر لإطلالة مثالية.', 'omega-design'), 'service-hair-styling.jpg'],
	['spa',               __('العناية بالبشرة', 'omega-design'), __('جلسات لنضارة الوجه وعلاجات متقدمة للبشرة.', 'omega-design'), 'service-skin-facial.jpg'],
	['brush',             __('الأظافر والتجميل', 'omega-design'), __('مانيكير وباديكير وفن الأظافر لكل الأذواق.', 'omega-design'), 'service-nails-beauty.jpg'],
	['self-improvement',  __('السبا والاسترخاء', 'omega-design'), __('علاجات مريحة تجدد نشاط الجسم والذهن.', 'omega-design'), 'service-spa-wellness.jpg'],
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
<!-- wp:image {"className":"omega-salon-about__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-about__image"><img src="<?php echo $salon_img('about.jpg'); ?>" alt="<?php esc_attr_e('خبيرة تصفيف تعمل في الصالون', 'omega-design'); ?>"/></figure><!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%","className":"omega-salon-about__content"} -->
<div class="wp-block-column is-vertically-aligned-center omega-salon-about__content" style="flex-basis:50%">
<?php echo pattern_helpers::eyebrow(__('عن لوميا', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('جمالٌ مصمَّم', 'omega-design'); ?><br><?php esc_html_e('خصيصاً لكِ.', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('في لوميا، نؤمن أن الجمال أكثر من مجرد مظهر — إنه إحساس. تكرّس خبيراتنا في التصفيف والعناية جهودهن لتقديم علاجات مخصصة في أجواء فاخرة ومريحة.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:group {"className":"omega-salon-about__features","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group omega-salon-about__features">
<?php foreach ([['groups', __('فريق خبير', 'omega-design')], ['sanitizer', __('منتجات فاخرة', 'omega-design')], ['face-retouching-natural', __('عناية مخصصة', 'omega-design')]] as $f) : ?>
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|xs"}}} --><div class="wp-block-group">
<!-- wp:icon {"icon":"omega-icons/<?php echo esc_attr($f[0]); ?>","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->
<!-- wp:paragraph {"className":"omega-strong","textColor":"heading","fontSize":"x-small"} --><p class="omega-strong has-heading-color has-text-color has-x-small-font-size"><?php echo esc_html($f[1]); ?></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('قصتنا ←', 'omega-design'); ?></a></div><!-- /wp:button -->
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
<?php echo $section_heading(__('علاجاتنا المميزة', 'omega-design'), __('استمتعي بأكثر علاجات التجميل طلباً، بإشراف خبيراتنا.', 'omega-design')); ?>
<?php
echo $build_row([
	[__('تحوّل كامل للشعر', 'omega-design'), __('قص وصبغ وتصفيف', 'omega-design'), '120 ر.س', __('90 دقيقة', 'omega-design'), 'treatment-hair-makeover.jpg'],
	[__('جلسة ترطيب للوجه', 'omega-design'),      __('تنظيف عميق ونضارة', 'omega-design'), '80 ر.س', __('60 دقيقة', 'omega-design'), 'treatment-hydrating-facial.jpg'],
	[__('مانيكير فاخر', 'omega-design'),       __('عناية فائقة بالأظافر', 'omega-design'), '50 ر.س', __('45 دقيقة', 'omega-design'), 'treatment-luxury-manicure.jpg'],
	[__('مساج استرخاء للجسم', 'omega-design'), __('استرخاء كامل للجسم', 'omega-design'), '90 ر.س', __('60 دقيقة', 'omega-design'), 'treatment-body-massage.jpg'],
], $build_treatment_card);
?>
<?php echo $section_close; ?>

<?php // REAL TRANSFORMATIONS ?>
<?php echo $section_open('omega-salon-transformations', 'secondary'); ?>
<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"24%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:24%">
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-transformations__title","textColor":"accent"} --><p class="omega-salon-script omega-salon-transformations__title has-accent-color has-text-color"><?php esc_html_e('تحولات', 'omega-design'); ?><br><?php esc_html_e('حقيقية', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"accent","fontSize":"x-small"} --><p class="omega-eyebrow has-accent-color has-text-color has-x-small-font-size"><?php esc_html_e('شاهدي الثقة في كل إطلالة.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"secondary"} --><div class="wp-block-button"><a class="wp-block-button__link has-secondary-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('معرض الصور ←', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"76%"} -->
<div class="wp-block-column" style="flex-basis:76%">
<?php
echo $build_row([
	[__('تحوّل البشرة', 'omega-design'), 'face-before.jpg', 'face-after.jpg'],
	[__('تحوّل الشعر', 'omega-design'), 'hair-before.jpg', 'hair-after.jpg'],
	[__('تحوّل العناية باليدين والأظافر', 'omega-design'), 'hand-before.jpg', 'hand-after.jpg'],
], $build_before_after);
?>
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<?php echo $section_close; ?>

<?php // WHY CHOOSE ?>
<?php echo $section_open('omega-salon-why-section'); ?>
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('لماذا تختارين لوميا؟', 'omega-design'); ?></h2><!-- /wp:heading -->
<?php
echo $build_row([
	['groups',            __('أخصائيات تجميل خبيرات', 'omega-design'), __('محترفات مدرَّبات بسنوات من الخبرة.', 'omega-design')],
	['workspace-premium', __('منتجات فاخرة', 'omega-design'),          __('نستخدم فقط منتجات عالية الجودة ولطيفة على البشرة.', 'omega-design')],
	['favorite',          __('علاجات مخصصة', 'omega-design'),   __('كل خدمة مصممة لتناسب احتياجاتكِ الفريدة.', 'omega-design')],
	['spa',               __('تجربة خاصة ومريحة', 'omega-design'), __('أجواء هادئة وفاخرة مخصصة لكِ.', 'omega-design')],
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
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-offer__eyebrow","textColor":"primary"} --><p class="omega-salon-script omega-salon-offer__eyebrow has-primary-color has-text-color"><?php esc_html_e('عرض خاص', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('زيارتكِ الأولى', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size"><?php esc_html_e('استمتعي', 'omega-design'); ?> <strong><?php esc_html_e('بخصم 20%', 'omega-design'); ?></strong> <?php esc_html_e('على أول جلسة تجميل لكِ.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"secondary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-secondary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('احصلي على العرض ←', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:group {"className":"omega-salon-offer__art","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-salon-offer__art">
<!-- wp:image {"className":"omega-salon-offer__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-salon-offer__image"><img src="<?php echo $salon_img('offer.png'); ?>" alt="<?php esc_attr_e('عميلة مسترخية بعد الجلسة', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"className":"omega-salon-offer__badge","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group omega-salon-offer__badge">
<!-- wp:paragraph {"align":"center","className":"omega-salon-offer__badge-value","textColor":"heading"} --><p class="has-text-align-center omega-salon-offer__badge-value has-heading-color has-text-color"><?php esc_html_e('20%', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","textColor":"heading","fontSize":"large"} --><p class="has-text-align-center omega-strong has-heading-color has-text-color has-large-font-size"><?php esc_html_e('خصم', 'omega-design'); ?></p><!-- /wp:paragraph -->
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
<?php echo $section_heading(__('آراء عميلاتنا', 'omega-design'), __('قصص حقيقية ونتائج حقيقية من عميلاتنا العزيزات.', 'omega-design')); ?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":false,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","className":"omega-testimonial-slider omega-salon-slider","prevLabel":"الآراء السابقة","nextLabel":"الآراء التالية","dotsLabel":"آراء العميلات"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-salon-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="الآراء السابقة" data-next-label="الآراء التالية" data-dots-label="آراء العميلات" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
echo $build_testimonial(__('سارة أحمد', 'omega-design'), __('أحببت الخدمة كثيراً! الطاقم محترف جداً والنتائج مذهلة. أفضل تجربة صالون على الإطلاق.', 'omega-design'), 'avatar-3.jpg');
echo $build_testimonial(__('نورة القحطاني', 'omega-design'), __('جلسة العناية بالوجه كانت رائعة. لم تبدُ بشرتي أجمل من ذلك! أنصح بها بشدة.', 'omega-design'), 'avatar-2.jpg');
echo $build_testimonial(__('ريم العتيبي', 'omega-design'), __('تجربة مريحة وفاخرة للغاية. الفريق يُشعركِ بأنكِ مميزة. سأعود بالتأكيد!', 'omega-design'), 'avatar-4.jpg');
echo $build_testimonial(__('ليلى حسن', 'omega-design'), __('من الحجز حتى الإطلالة النهائية، كان كل شيء مثالياً. لم يكن شعري بهذه الصحة من قبل.', 'omega-design'), 'avatar-1.jpg');
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
<!-- wp:paragraph {"className":"omega-salon-script omega-salon-instagram__eyebrow","textColor":"primary"} --><p class="omega-salon-script omega-salon-instagram__eyebrow has-primary-color has-text-color"><?php esc_html_e('تابعي رحلتنا', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"fontSize":"x-large"} --><h3 class="wp-block-heading has-x-large-font-size"><?php esc_html_e('@lumea.beautysalon', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"x-small"} --><p class="has-x-small-font-size"><?php esc_html_e('جمال · أسلوب حياة · إلهام', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('تابعينا ←', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"76%"} -->
<div class="wp-block-column" style="flex-basis:76%">
<!-- wp:group {"className":"omega-salon-instagram__grid","layout":{"type":"grid","columnCount":6}} -->
<div class="wp-block-group omega-salon-instagram__grid">
<?php for ($i = 1; $i <= 6; $i++) : ?>
<!-- wp:image {"className":"omega-rounded-image","sizeSlug":"medium"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $salon_img('instagram-' . $i . '.jpg'); ?>" alt="<?php esc_attr_e('منشور إنستغرام', 'omega-design'); ?>"/></figure><!-- /wp:image -->
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
<!-- wp:image {"className":"omega-salon-cta__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-salon-cta__bg"><img src="<?php echo $salon_img('cta.jpg'); ?>" alt=""/></figure><!-- /wp:image -->
<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"omega-salon-cta__content"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center omega-salon-cta__content">
<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('هل أنتِ مستعدة', 'omega-design'); ?><br><?php esc_html_e('للحظة جمال جديدة؟', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('احجزي موعدكِ اليوم ودعينا نعتني بكِ.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"secondary"} --><div class="wp-block-button"><a class="wp-block-button__link has-secondary-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('احجزي موعدكِ ←', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
<!-- wp:columns {"isStackedOnMobile":false} -->
<div class="wp-block-columns is-not-stacked-on-mobile">
<?php
foreach ([['event-available', __('حجز سهل', 'omega-design')], ['diamond', __('تجربة فاخرة', 'omega-design')], ['favorite', __('إحساس رائع', 'omega-design')]] as $f) {
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
