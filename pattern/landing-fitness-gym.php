<?php
/**
 * Title: Landing Page - Fitness Gym
 * Slug: omega-design/landing-fitness-gym
 * Categories: omega-design-general
 * Description: A full gym/fitness landing page - photo hero, feature strip, a programs grid, a special-offer split, transformations + membership pricing, a blog/testimonials split, and an app + newsletter band. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, gym, fitness, membership, pricing, hero, carousel
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css and assets/css/landing-
 * fitness-gym.css) for anything more custom - no hand-typed multi-property
 * inline `style` JSON and no Cover blocks. That's what keeps every block's
 * stored markup matching what the block editor itself would generate, so
 * opening this page never shows an "attempt recovery" warning.
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR MENU"-style eyebrow line above a heading. */
$eyebrow = function ($text) {
	return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
		. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** Icon + title + description column, icon left of text (feature strip). */
$build_icon_row_left = function ($items) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"30px"}}} /-->' . "\n";
		$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"medium"} --><p class="omega-strong has-medium-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n</div><!-- /wp:column -->\n";
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

/** A small icon + label row (no description) - used in the hero side list. */
$build_icon_label_list = function ($items) {
	$out = '<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|xs"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item[0]) . '","textColor":"primary","style":{"dimensions":{"width":"18px"}}} /-->' . "\n";
		$out .= '<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">' . esc_html($item[1]) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n";
	}
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** A photo-with-overlay-text section - replaces a hand-authored Cover block. */
$build_photo_card = function ($size_class, $content_html) use ($omega_ph) {
	$out = '<!-- wp:group {"className":"omega-photo-card ' . esc_attr($size_class) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card ' . esc_attr($size_class) . '">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="' . $omega_ph . '" alt=""/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card__content">' . "\n" . $content_html . '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** One photo "program" card: image, title, description, arrow link. */
$build_program_card = function ($title, $desc) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} -->' . "\n";
	$out .= '<figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($title) . '"/></figure>' . "\n<!-- /wp:image -->\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong"} --><p class="omega-strong">' . esc_html($title) . ' →</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

/**
 * One pricing tier card: title, price, feature list, CTA button. The
 * featured (middle) tier uses backgroundColor "primary" (a plain slug
 * attribute); the others use "surface". The card's rounded corners, border
 * and "Most Popular" badge styling come from assets/css/landing-fitness-
 * gym.css via className, not from inline border/typography style JSON.
 */
$build_pricing_card = function ($title, $price, $features, $featured = false) {
	$bg          = $featured ? 'primary' : 'surface';
	$class_name  = $featured ? 'omega-pricing-card omega-pricing-card--featured' : 'omega-pricing-card';
	$text_attr   = $featured ? ',"textColor":"button-text"' : '';
	$text_class  = $featured ? ' has-button-text-color has-text-color' : '';

	$out  = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"' . $class_name . '","backgroundColor":"' . $bg . '"' . $text_attr . ',"style":{"spacing":{"padding":{"top":"var:preset|spacing|m","right":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|m"}}},"layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group ' . $class_name . ' has-' . $bg . '-background-color has-background' . $text_class . '" style="padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--m);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--m)">' . "\n";
	if ($featured) {
		$out .= '<!-- wp:paragraph {"className":"omega-pricing-card__badge","textColor":"button-text","fontSize":"x-small"} --><p class="omega-pricing-card__badge has-button-text-color has-text-color has-x-small-font-size">' . esc_html__('Most Popular', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
	}
	$out .= '<!-- wp:heading {"level":4,"fontSize":"medium"' . $text_attr . '} --><h4 class="wp-block-heading has-medium-font-size' . $text_class . '">' . esc_html($title) . '</h4><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"xxx-large"' . $text_attr . '} --><p class="omega-strong has-xxx-large-font-size' . $text_class . '">' . esc_html($price) . ' ' . esc_html__('/ month', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:list' . ($featured ? ' {"textColor":"button-text"}' : '') . ' --><ul class="wp-block-list' . $text_class . '">' . "\n";
	foreach ($features as $feature) {
		$out .= '<!-- wp:list-item --><li>✓ ' . esc_html($feature) . '</li><!-- /wp:list-item -->' . "\n";
	}
	$out .= '</ul><!-- /wp:list -->' . "\n";
	$out .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
	if ($featured) {
		$out .= '<!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Get Started', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
	} else {
		$out .= '<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__('Get Started', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
	}
	$out .= '</div><!-- /wp:buttons -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--fitness-gym","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--fitness-gym">

<?php // HERO ?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php
$hero_content = $eyebrow(__('Fitness for a Better Tomorrow', 'omega-design'));
$hero_content .= '<!-- wp:heading {"level":1,"textColor":"background"} --><h1 class="wp-block-heading has-background-color has-text-color">' . esc_html__('A Stronger Happier You', 'omega-design') . '</h1><!-- /wp:heading -->' . "\n";
$hero_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Train smarter. Eat better. Live healthier. Join FitLife and discover your best self.', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$hero_content .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
$hero_content .= '<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Start Your Journey', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$hero_content .= '<!-- wp:button {"className":"is-style-outline","textColor":"background"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-background-color has-text-color wp-element-button" href="#">' . esc_html__('▶ Watch Video', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$hero_content .= '</div><!-- /wp:buttons -->' . "\n";

$hero_content .= '<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->' . "\n<div class=\"wp-block-columns\">\n";
foreach ([['10K+', __('Happy Members', 'omega-design')], ['50+', __('Expert Trainers', 'omega-design')], ['100+', __('Fitness Classes', 'omega-design')], ['95%', __('Success Rate', 'omega-design')]] as $stat) {
	$hero_content .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$hero_content .= '<!-- wp:paragraph {"className":"omega-stat__value","textColor":"background","fontSize":"large"} --><p class="omega-stat__value has-background-color has-text-color has-large-font-size">' . esc_html($stat[0]) . '</p><!-- /wp:paragraph -->' . "\n";
	$hero_content .= '<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">' . esc_html($stat[1]) . '</p><!-- /wp:paragraph -->' . "\n";
	$hero_content .= '</div><!-- /wp:column -->' . "\n";
}
$hero_content .= '</div><!-- /wp:columns -->' . "\n";

$hero_side = $build_icon_label_list([
	['fitness-center',      __('Stronger Body', 'omega-design')],
	['psychology',          __('Healthier Mind', 'omega-design')],
	['bolt',                __('More Energy', 'omega-design')],
	['sentiment-satisfied',  __('Happier You', 'omega-design')],
]);

$hero_columns  = '<!-- wp:columns {"verticalAlignment":"center"} -->' . "\n<div class=\"wp-block-columns are-vertically-aligned-center\">\n";
$hero_columns .= '<!-- wp:column {"verticalAlignment":"center","width":"65%"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:65%\">\n" . $hero_content . '</div><!-- /wp:column -->' . "\n";
$hero_columns .= '<!-- wp:column {"verticalAlignment":"center","width":"35%"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:35%\">\n" . $hero_side . '</div><!-- /wp:column -->' . "\n";
$hero_columns .= '</div><!-- /wp:columns -->' . "\n";

echo $build_photo_card('omega-photo-card--tall', $hero_columns);
?>

</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'fitness-center', 'title' => __('Modern Equipment', 'omega-design'),     'desc' => __('Top quality facilities', 'omega-design')],
	['icon' => 'groups',         'title' => __('Expert Trainers', 'omega-design'),      'desc' => __('Certified professionals', 'omega-design')],
	['icon' => 'assignment',     'title' => __('Personalized Plans', 'omega-design'),   'desc' => __('Tailored to your goals', 'omega-design')],
	['icon' => 'nutrition',      'title' => __('Nutrition Guidance', 'omega-design'),   'desc' => __('Eat smart, perform better', 'omega-design')],
	['icon' => 'favorite',       'title' => __('Support Community', 'omega-design'),    'desc' => __('Be part of something bigger', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // PROGRAMS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Find Your Fit', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Our Fitness Programs', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Programs →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ([
	[__('Weight Training', 'omega-design'), __('Build strength & muscle', 'omega-design')],
	[__('Cardio Fitness', 'omega-design'), __('Boost endurance & burn fat', 'omega-design')],
	[__('Yoga & Mindfulness', 'omega-design'), __('Find balance & inner peace', 'omega-design')],
] as $p) {
	echo $build_program_card($p[0], $p[1]);
}
?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ([
	[__('HIIT Workouts', 'omega-design'), __('Get fit in less time', 'omega-design')],
	[__('Personal Training', 'omega-design'), __('One-on-one coaching', 'omega-design')],
	[__('Group Classes', 'omega-design'), __('Fitness is more fun together', 'omega-design')],
] as $p) {
	echo $build_program_card($p[0], $p[1]);
}
?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // SPECIAL OFFER SPLIT ?>
<!-- wp:columns {"align":"full"} -->
<div class="wp-block-columns alignfull">

<!-- wp:column -->
<div class="wp-block-column">
<?php
$offer_content  = $eyebrow(__('Special Offer', 'omega-design'));
$offer_content .= '<!-- wp:heading {"level":3,"textColor":"background"} --><h3 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Get 20% Off Your First Month', 'omega-design') . '</h3><!-- /wp:heading -->' . "\n";
$offer_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Start your fitness journey today and unlock a healthier, stronger you.', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$offer_content .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
$offer_content .= '<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Claim Offer', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$offer_content .= '</div><!-- /wp:buttons -->' . "\n";
echo $build_photo_card('omega-photo-card--short', $offer_content);
?>
</div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--xl)">
<?php echo $eyebrow(__('Your Goals, Our Support', 'omega-design')); ?>
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Your Goals Our Support', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:list --><ul class="wp-block-list">
<!-- wp:list-item --><li>✓ <?php esc_html_e('Weight Loss', 'omega-design'); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li>✓ <?php esc_html_e('Muscle Gain', 'omega-design'); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li>✓ <?php esc_html_e('Better Health', 'omega-design'); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li>✓ <?php esc_html_e('More Confidence', 'omega-design'); ?></li><!-- /wp:list-item -->
</ul><!-- /wp:list -->
<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<?php // TRANSFORMATIONS + PRICING ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns -->
<div class="wp-block-columns">

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%">
<?php echo $eyebrow(__('Transformations', 'omega-design')); ?>
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Real People. Real Results.', 'omega-design'); ?></h3><!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<?php foreach ([[__('FitLife changed my life! I\'m stronger, healthier and more confident.', 'omega-design'), __('Ahmed R.', 'omega-design')], [__('Amazing trainers and great support. I feel healthier and happier every day!', 'omega-design'), __('Sara K.', 'omega-design')]] as $tr) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:paragraph {"className":"omega-italic","fontSize":"small"} --><p class="omega-italic has-small-font-size">"<?php echo esc_html($tr[0]); ?>"</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">— <?php echo esc_html($tr[1]); ?> ★★★★★</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%">
<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Choose Your Plan', 'omega-design')); ?>
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Membership Plans', 'omega-design'); ?></h3><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Plans →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
echo $build_pricing_card(__('Basic', 'omega-design'), '$29', [__('Gym Access', 'omega-design'), __('Basic Classes', 'omega-design'), __('Locker Access', 'omega-design')]);
echo $build_pricing_card(__('Premium', 'omega-design'), '$49', [__('Gym Access', 'omega-design'), __('All Classes', 'omega-design'), __('Personalized Plan', 'omega-design'), __('Nutrition Guidance', 'omega-design')], true);
echo $build_pricing_card(__('VIP', 'omega-design'), '$79', [__('Everything in Premium', 'omega-design'), __('1-on-1 Personal Training', 'omega-design'), __('Priority Support', 'omega-design'), __('Exclusive Events', 'omega-design')]);
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // BLOG + TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns -->
<div class="wp-block-columns">

<!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%">
<?php echo $eyebrow(__('Tips & Insights', 'omega-design')); ?>
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Latest from Our Blog', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:query {"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":1}} -->
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:post-title {"level":5,"isLink":true} /-->
<!-- wp:post-date {"fontSize":"small"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%">
<?php echo $eyebrow(__('Hear from Our Community', 'omega-design')); ?>
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('What Our Members Say', 'omega-design'); ?></h3><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"showArrows":true,"showDots":false,"slidesPerView":2,"slidesPerViewTablet":1,"slidesPerViewMobile":1,"gap":"16px","prevLabel":"Previous","nextLabel":"Next"} -->
<div class="wp-block-omega-design-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="2" data-spv-tablet="1" data-spv-mobile="1" data-gap="16px" data-prev-label="Previous" data-next-label="Next" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php foreach ([[__('Great environment, professional trainers, and amazing results. Highly recommend FitLife!', 'omega-design'), __('Omar S.', 'omega-design')], [__('The best gym in the city! Friendly staff, clean facilities, and motivating atmosphere.', 'omega-design'), __('Ayesha M.', 'omega-design')]] as $t) : ?>
<!-- wp:group {"className":"omega-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-surface-background-color has-background">
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">"<?php echo esc_html($t[0]); ?>"</p><!-- /wp:paragraph -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:image {"className":"omega-round-image omega-avatar-sm","sizeSlug":"thumbnail"} -->
<figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-sm"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t[1]); ?>"/></figure>
<!-- /wp:image -->
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">— <?php echo esc_html($t[1]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"textColor":"warning","fontSize":"small"} --><p class="has-warning-color has-text-color has-small-font-size">★★★★★</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // APP + NEWSLETTER ?>
<!-- wp:columns {"align":"full","verticalAlignment":"center"} -->
<div class="wp-block-columns alignfull are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"60%","backgroundColor":"primary","textColor":"button-text","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|l","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-button-text-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l);flex-basis:60%">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Download Our App', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('Your fitness companion, anytime anywhere.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} --><div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('App Store', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} --><div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Google Play', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<?php
echo $build_icon_label_list([
	['schedule', __('Track Workouts', 'omega-design')],
	['assignment', __('Nutrition Plans', 'omega-design')],
	['event', __('Book Classes', 'omega-design')],
	['favorite', __('Stay Motivated', 'omega-design')],
]);
?>
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%","backgroundColor":"heading","textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|l","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-column is-vertically-aligned-center has-background-color has-heading-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l);flex-basis:40%">
<!-- wp:heading {"level":3,"textColor":"background"} --><h3 class="wp-block-heading has-background-color has-text-color"><?php esc_html_e('Join Our Fitness Community', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color"><?php esc_html_e('Get tips, updates, and exclusive offers straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — welcome to FitLife!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — welcome to FitLife!", 'omega-design'); ?>">
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
