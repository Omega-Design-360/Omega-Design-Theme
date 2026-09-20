<?php
/**
 * Title: Landing Page - Digital Agency
 * Slug: omega-design/landing-digital-agency
 * Categories: omega-design-general
 * Description: A full digital agency landing page - hero, feature strip, a services grid, a filterable portfolio, a 4-step process, a testimonial carousel, and a blog + newsletter row. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, agency, digital marketing, portfolio, services
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css, assets/css/landing-digital-agency.css)
 * for anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks. That's what keeps every block's stored markup
 * matching what the block editor itself would generate, so opening this
 * page never shows an "attempt recovery" warning. See
 * pattern/landing-fashion-store.php for the template this follows.
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR SERVICES"-style eyebrow line above a heading. */
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

/**
 * One pastel service card: icon, title, description, and a "Learn More"
 * link, inside a Group carrying a numbered `omega-service-card--N` class.
 * The pastel background tint lives in assets/css/landing-digital-agency.css
 * (one rule per variant), not in inline `style` JSON.
 */
$build_service_card = function ($icon, $title, $desc, $variant) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-service-card omega-service-card--' . (int) $variant . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-service-card omega-service-card--' . (int) $variant . '">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"heading","style":{"dimensions":{"width":"30px"}}} /-->' . "\n";
	$out .= '<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size">' . esc_html($title) . '</h4><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><a href="#">' . esc_html__('Learn More →', 'omega-design') . '</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

$build_service_row = function ($items) use ($build_service_card) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= $build_service_card($item[0], $item[1], $item[2], $item[3]);
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
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

/** One portfolio card: photo card with a title + category caption. */
$build_portfolio_card = function ($title, $category) use ($build_photo_card) {
	$content = '<!-- wp:paragraph {"className":"omega-strong","textColor":"background"} --><p class="omega-strong has-background-color has-text-color">' . esc_html($title) . '</p><!-- /wp:paragraph -->' . "\n";
	$content .= '<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">' . esc_html($category) . '</p><!-- /wp:paragraph -->' . "\n";
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= $build_photo_card('omega-photo-card--short', $content);
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

$build_portfolio_row = function ($items) use ($build_portfolio_card) {
	$out = '<!-- wp:columns -->' . "\n<div class=\"wp-block-columns\">\n";
	foreach ($items as $item) {
		$out .= $build_portfolio_card($item[0], $item[1]);
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--digital-agency","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--digital-agency">

<?php // HERO ?>
<!-- wp:group {"backgroundColor":"surface","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<?php echo $eyebrow(__('Creative Solutions for a Digital World', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('We Design Develop & Grow Your Business', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('From stunning websites to powerful digital marketing, we help brands stand out and succeed online.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Start Your Project', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Our Services', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['150+', __('Happy Clients', 'omega-design')], ['300+', __('Projects Completed', 'omega-design')], ['5+', __('Years of Experience', 'omega-design')], ['98%', __('Client Satisfaction', 'omega-design')]] as $stat) : ?>
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
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Hero illustration', 'omega-design'); ?>"/></figure><!-- /wp:image -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'groups',        'title' => __('Creative Team', 'omega-design'),      'desc' => __('Passionate professionals', 'omega-design')],
	['icon' => 'schedule',      'title' => __('On-Time Delivery', 'omega-design'),   'desc' => __('We value your time', 'omega-design')],
	['icon' => 'sell',          'title' => __('Affordable Pricing', 'omega-design'), 'desc' => __('Great value for everyone', 'omega-design')],
	['icon' => 'support-agent', 'title' => __('24/7 Support', 'omega-design'),       'desc' => __("We're always here", 'omega-design')],
	['icon' => 'public',        'title' => __('Global Clients', 'omega-design'),     'desc' => __('Serving worldwide', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SERVICES ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Our Services', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Everything You Need to Grow Online', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Services →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php
echo $build_service_row([
	['desktop-windows', __('Website Development', 'omega-design'),      __('Modern, fast & responsive websites.', 'omega-design'), 1],
	['code',             __('WordPress Development', 'omega-design'),   __('Custom WordPress solutions.', 'omega-design'), 2],
	['brush',            __('Graphic Design', 'omega-design'),          __('Creative designs for your brand.', 'omega-design'), 3],
	['diamond',          __('Branding & Identity', 'omega-design'),     __('Build a brand that stands out.', 'omega-design'), 4],
	['shopping-cart',    __('E-Commerce Solutions', 'omega-design'),    __('Online stores that sell more.', 'omega-design'), 5],
]);
echo $build_service_row([
	['trending-up',      __('SEO & Digital Marketing', 'omega-design'), __('Get found. Get more customers.', 'omega-design'), 6],
	['security',         __('Maintenance & Security', 'omega-design'),  __('Keep your website safe & updated.', 'omega-design'), 7],
	['android',          __('Android App Development', 'omega-design'), __('Turn your ideas into mobile apps.', 'omega-design'), 8],
	['campaign',         __('Social Media Management', 'omega-design'), __('Grow your social presence.', 'omega-design'), 9],
	['design-services',  __('UI/UX Design', 'omega-design'),            __('User-centered designs that convert.', 'omega-design'), 10],
]);
?>
</div>
<!-- /wp:group -->

<?php // PORTFOLIO ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $eyebrow(__('Our Portfolio', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Our Recent Work', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('All', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Business Website', 'omega-design'), __('Web Development', 'omega-design')],
	[__('Fashion Store', 'omega-design'), __('E-Commerce', 'omega-design')],
	[__('Brand Identity', 'omega-design'), __('Branding', 'omega-design')],
	[__('Mobile App', 'omega-design'), __('App Development', 'omega-design')],
	[__('Restaurant Website', 'omega-design'), __('Web Development', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Websites', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Business Website', 'omega-design'), __('Web Development', 'omega-design')],
	[__('Restaurant Website', 'omega-design'), __('Web Development', 'omega-design')],
	[__('Clinic Website', 'omega-design'), __('Web Development', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('E-Commerce', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Fashion Store', 'omega-design'), __('E-Commerce', 'omega-design')],
	[__('Coffee Shop', 'omega-design'), __('E-Commerce', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Branding', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Brand Identity', 'omega-design'), __('Branding', 'omega-design')],
	[__('Packaging Design', 'omega-design'), __('Branding', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Mobile Apps', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Mobile App', 'omega-design'), __('App Development', 'omega-design')],
	[__('Fitness App', 'omega-design'), __('App Development', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Graphics', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_portfolio_row([
	[__('Social Media Kit', 'omega-design'), __('Graphics', 'omega-design')],
	[__('Print Ads', 'omega-design'), __('Graphics', 'omega-design')],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->

<?php // PROCESS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $eyebrow(__('How We Work', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('A Simple Process for Amazing Results', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns">
<?php
$steps = [
	['01', 'forum', __('Discover', 'omega-design'), __('We understand your goals and requirements.', 'omega-design')],
	['02', 'assignment', __('Plan', 'omega-design'), __('We create a strategy and roadmap.', 'omega-design')],
	['03', 'settings', __('Design & Develop', 'omega-design'), __('We bring your ideas to life.', 'omega-design')],
	['04', 'rocket-launch', __('Launch & Grow', 'omega-design'), __('We deliver and support you for long-term success.', 'omega-design')],
];
foreach ($steps as $step) :
	?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"className":"omega-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-surface-background-color has-background">
<!-- wp:icon {"icon":"omega-icons/<?php echo esc_attr($step[1]); ?>","textColor":"primary","style":{"dimensions":{"width":"28px"}}} /-->
<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size"><?php echo esc_html($step[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size"><?php echo esc_html($step[2]); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($step[3]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $eyebrow(__('What Our Clients Say', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Trusted by Amazing People', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":false,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","className":"omega-testimonial-slider","prevLabel":"Previous testimonials","nextLabel":"Next testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="Previous testimonials" data-next-label="Next testimonials" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	['quote' => __('Omega Design transformed our idea into a beautiful website. Highly recommended!', 'omega-design'), 'name' => __('Ahmed R.', 'omega-design')],
	['quote' => __('Professional, creative and always deliver on time. They are amazing to work with!', 'omega-design'), 'name' => __('Sara K.', 'omega-design')],
	['quote' => __('Great communication and outstanding results. Will definitely work again!', 'omega-design'), 'name' => __('Bilal M.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:group {"className":"omega-card","backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-background-background-color has-background">
<!-- wp:paragraph --><p>"<?php echo esc_html($t['quote']); ?>"</p><!-- /wp:paragraph -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:image {"className":"omega-round-image omega-avatar-md","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t['name']); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php echo esc_html($t['name']); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">★★★★★</p><!-- /wp:paragraph -->
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
<!-- /wp:group -->

<?php // BLOG + NEWSLETTER ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Latest from Our Blog', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Tips, Insights & Updates', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Posts →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns">

<!-- wp:column {"width":"75%"} -->
<div class="wp-block-column" style="flex-basis:75%">
<!-- wp:query {"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:post-title {"level":4,"isLink":true} /-->
<!-- wp:post-date {"fontSize":"small"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%">
<!-- wp:group {"className":"omega-card","backgroundColor":"secondary","textColor":"button-text","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-button-text-color has-secondary-background-color has-text-color has-background">
<!-- wp:heading {"level":4,"textColor":"button-text","fontSize":"medium"} --><h4 class="wp-block-heading has-button-text-color has-text-color has-medium-font-size"><?php esc_html_e('Join Our Newsletter', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text","fontSize":"small"} --><p class="has-button-text-color has-text-color has-small-font-size"><?php esc_html_e('Get the latest tips, resources, and updates delivered to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo htmlspecialchars(__("Thanks — you're subscribed!", 'omega-design'), ENT_COMPAT); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo htmlspecialchars(__("Thanks — you're subscribed!", 'omega-design'), ENT_COMPAT); ?>">
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
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
