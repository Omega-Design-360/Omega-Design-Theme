<?php
/**
 * Title: Landing Page - Restaurant
 * Slug: omega-design/landing-restaurant
 * Categories: omega-design-general
 * Description: A full restaurant landing page - photo hero, a menu-category icon row, a special-offer split, an about section, a signature dishes grid, testimonials + a reservation card, and a blog + newsletter row. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, restaurant, dining, menu, reservation, hero
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css, assets/css/landing-
 * restaurant.css) for anything more custom - no hand-typed multi-property
 * inline `style` JSON and no Cover blocks. That's what keeps every block's
 * stored markup matching what the block editor itself would generate, so
 * opening this page never shows an "attempt recovery" warning. See
 * pattern/landing-fashion-store.php for the same approach applied to
 * another page.
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR MENU"-style eyebrow line above a heading. */
$eyebrow = function ($text) {
	return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
		. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** A section header row: eyebrow + heading on the left, a "View All" link on the right. */
$build_section_header = function ($eyebrow_text, $heading_text, $link_text) use ($eyebrow) {
	$out  = '<!-- wp:columns {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-columns are-vertically-aligned-bottom\">\n";
	$out .= '<!-- wp:column {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-bottom\">\n";
	$out .= $eyebrow($eyebrow_text);
	$out .= '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">' . esc_html($heading_text) . '</h2><!-- /wp:heading -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	$out .= '<!-- wp:column {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-bottom\">\n";
	$out .= '<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#">' . esc_html($link_text) . '</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n</div><!-- /wp:columns -->\n";
	return $out;
};

/** Icon + title + description column, icon left of text (hero row). */
$build_icon_row_left = function ($items) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"26px"}}} /-->' . "\n";
		$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"background","fontSize":"small"} --><p class="omega-strong has-background-color has-text-color has-small-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n</div><!-- /wp:column -->\n";
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

/** A photo-with-overlay-text section - replaces a hand-authored Cover block. */
$build_photo_card = function ($size_class, $content_html, $align = '') use ($omega_ph) {
	$align_attr  = $align ? '"align":"' . $align . '",' : '';
	$align_class = $align ? 'align' . $align . ' ' : '';
	$out  = '<!-- wp:group {' . $align_attr . '"className":"omega-photo-card ' . esc_attr($size_class) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group ' . $align_class . 'omega-photo-card ' . esc_attr($size_class) . '">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="' . $omega_ph . '" alt=""/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card__content">' . "\n" . $content_html . '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** One dish card: photo, name, price, round "+" button, rating. */
$build_dish_card = function ($name, $price, $rating) use ($omega_ph) {
	$out  = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"center","flexWrap":"nowrap"}} -->' . "\n<div class=\"wp-block-group\">\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size">' . esc_html($price) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">&#9733; ' . esc_html($rating) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
	$out .= '<!-- wp:button {"className":"omega-menu-add-btn","backgroundColor":"heading","textColor":"background"} --><div class="wp-block-button omega-menu-add-btn"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#" title="' . esc_attr(sprintf(__('Add %s', 'omega-design'), $name)) . '">+</a></div><!-- /wp:button -->' . "\n";
	$out .= '</div><!-- /wp:buttons -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--restaurant","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--restaurant">

<?php // HERO ?>
<?php
$hero_content  = $eyebrow(__('Delicious Food, Memorable Moments', 'omega-design'));
$hero_content .= '<!-- wp:heading {"level":1,"textColor":"background"} --><h1 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Good Food Brings People Together', 'omega-design') . '</h1><!-- /wp:heading -->' . "\n";
$hero_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Experience a perfect blend of taste, ambiance, and hospitality at DineDelight.', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$hero_content .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
$hero_content .= '<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Book a Table', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$hero_content .= '<!-- wp:button {"className":"is-style-outline","textColor":"background"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-background-color has-text-color wp-element-button" href="#">' . esc_html__('View Our Menu', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$hero_content .= '</div><!-- /wp:buttons -->' . "\n";
$hero_content .= $build_icon_row_left([
	['icon' => 'nutrition', 'title' => __('Fresh Ingredients', 'omega-design'), 'desc' => __('Locally Sourced', 'omega-design')],
	['icon' => 'chef-hat',  'title' => __('Expert Chefs', 'omega-design'), 'desc' => __('World-Class Cuisine', 'omega-design')],
	['icon' => 'spa',       'title' => __('A Cozy Atmosphere', 'omega-design'), 'desc' => __('For Every Occasion', 'omega-design')],
]);

$hero_badge  = '<!-- wp:group {"className":"omega-hero-badge","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->' . "\n";
$hero_badge .= '<div class="wp-block-group omega-hero-badge">' . "\n";
$hero_badge .= '<!-- wp:paragraph {"align":"center","textColor":"background","className":"omega-strong","fontSize":"x-small"} --><p class="has-text-align-center has-background-color has-text-color omega-strong has-x-small-font-size">&#9658;</p><!-- /wp:paragraph -->' . "\n";
$hero_badge .= '<!-- wp:paragraph {"align":"center","textColor":"background","fontSize":"x-small"} --><p class="has-text-align-center has-background-color has-text-color has-x-small-font-size">' . esc_html__('Watch Our Story', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$hero_badge .= '</div><!-- /wp:group -->' . "\n";

echo $build_photo_card('omega-photo-card--tall', $hero_badge . $hero_content, 'full');
?>

<?php // MENU CATEGORY ICONS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $build_section_header(__('Explore Our Menu', 'omega-design'), __('A Flavor for Every Craving', 'omega-design'), __('View Full Menu →', 'omega-design')); ?>
<!-- wp:group {"className":"omega-category-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-row">
<?php foreach ([__('Breakfast', 'omega-design'), __('Appetizers', 'omega-design'), __('Main Course', 'omega-design'), __('Pasta', 'omega-design'), __('Pizza', 'omega-design'), __('Desserts', 'omega-design'), __('Beverages', 'omega-design')] as $cat) : ?>
<!-- wp:group {"className":"omega-category-item","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-item">
<!-- wp:image {"className":"omega-round-image","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($cat); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"small"} --><p class="has-text-align-center omega-strong has-small-font-size"><?php echo esc_html($cat); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php // SPECIAL OFFER SPLIT ?>
<!-- wp:columns {"align":"full"} -->
<div class="wp-block-columns alignfull">
<!-- wp:column -->
<div class="wp-block-column">
<?php
$offer_content  = $eyebrow(__('Special Offer', 'omega-design'));
$offer_content .= '<!-- wp:heading {"level":3,"textColor":"background"} --><h3 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Get 20% Off On Your First Visit', 'omega-design') . '</h3><!-- /wp:heading -->' . "\n";
$offer_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Delicious food is just a booking away!', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$offer_content .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
$offer_content .= '<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Book Now →', 'omega-design') . '</a></div><!-- /wp:button -->' . "\n";
$offer_content .= '</div><!-- /wp:buttons -->' . "\n";
echo $build_photo_card('omega-photo-card--short', $offer_content);
?>
</div>
<!-- /wp:column -->
<!-- wp:column {"backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--xl)">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Family Time Special', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Good Food, Brighter Moments.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} --><div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('View Deals →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-medium omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php // ABOUT ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt=""/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">
<?php echo $eyebrow(__('About DineDelight', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('More Than Just a Restaurant', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('At DineDelight, we believe great food creates great memories. Our passion is to serve delicious meals made from fresh ingredients in a warm and welcoming atmosphere, whether it\'s a family dinner, a romantic date, or a casual hangout.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['10K+', __('Happy Customers', 'omega-design')], ['50+', __('Signature Dishes', 'omega-design')], ['4.8', __('Average Rating', 'omega-design')], ['100%', __('Fresh Ingredients', 'omega-design')]] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"large"} --><p class="omega-strong has-primary-color has-text-color has-large-font-size"><?php echo esc_html($stat[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Our Story →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // SIGNATURE DISHES ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $build_section_header(__('Our Signature Dishes', 'omega-design'), __("Chef's Special Creations", 'omega-design'), __('View All Dishes →', 'omega-design')); ?>
<!-- wp:columns -->
<div class="wp-block-columns">
<?php foreach ([[__('Grilled Salmon', 'omega-design'), '$18.00', '4.6 (124)'], [__('Beef Steak', 'omega-design'), '$24.00', '4.6 (98)'], [__('Chicken Alfredo Pasta', 'omega-design'), '$16.00', '4.6 (76)']] as $d) { echo $build_dish_card($d[0], $d[1], $d[2]); } ?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php foreach ([[__('Margherita Pizza', 'omega-design'), '$14.00', '4.7 (112)'], [__('Chocolate Lava Cake', 'omega-design'), '$8.00', '4.6 (64)'], [__('Fresh Fruit Mocktail', 'omega-design'), '$6.00', '4.6 (91)']] as $d) { echo $build_dish_card($d[0], $d[1], $d[2]); } ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS + RESERVATION ?>
<!-- wp:columns {"align":"full"} -->
<div class="wp-block-columns alignfull">
<!-- wp:column {"width":"60%","backgroundColor":"heading","textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|l","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-column has-background-color has-heading-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l);flex-basis:60%">
<?php echo $eyebrow(__('What Our Guests Say', 'omega-design')); ?>
<!-- wp:heading {"level":2,"textColor":"background"} --><h2 class="wp-block-heading has-background-color has-text-color"><?php esc_html_e('Loved by Food Enthusiasts', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"showArrows":true,"showDots":false,"slidesPerView":2,"slidesPerViewTablet":1,"slidesPerViewMobile":1,"gap":"16px","prevLabel":"Previous","nextLabel":"Next"} -->
<div class="wp-block-omega-design-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="2" data-spv-tablet="1" data-spv-mobile="1" data-gap="16px" data-prev-label="Previous" data-next-label="Next" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php foreach ([[__('Amazing food, great atmosphere, and super friendly staff. Definitely my favorite restaurant in the city!', 'omega-design'), __('Ahmed R.', 'omega-design')], [__('The pasta was outstanding! Perfect place for family dining. Highly recommended!', 'omega-design'), __('Sara K.', 'omega-design')]] as $t) : ?>
<!-- wp:group {"className":"omega-testimonial-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-testimonial-card">
<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size">"<?php echo esc_html($t[0]); ?>"</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-strong","textColor":"background","fontSize":"small"} --><p class="omega-strong has-background-color has-text-color has-small-font-size"><?php echo esc_html($t[1]); ?> &#9733;&#9733;&#9733;&#9733;&#9733;</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"40%","backgroundColor":"secondary","textColor":"button-text","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","right":"var:preset|spacing|l","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-column has-button-text-color has-secondary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l);flex-basis:40%">
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Reserve Your Table', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text","fontSize":"small"} --><p class="has-button-text-color has-text-color has-small-font-size"><?php esc_html_e('Good food awaits you!', 'omega-design'); ?></p><!-- /wp:paragraph -->
<?php foreach ([__('People', 'omega-design'), __('Select Date', 'omega-design'), __('7:00 PM', 'omega-design')] as $field) : ?>
<!-- wp:paragraph {"backgroundColor":"background","textColor":"heading","className":"omega-form-field","fontSize":"small"} --><p class="has-heading-color has-background-background-color has-text-color has-background omega-form-field has-small-font-size"><?php echo esc_html($field); ?></p><!-- /wp:paragraph -->
<?php endforeach; ?>
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Book a Table →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php // BLOG + NEWSLETTER ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $eyebrow(__('From Our Blog', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Food Inspiration & Stories', 'omega-design'); ?></h2><!-- /wp:heading -->
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
<!-- wp:group {"backgroundColor":"heading","textColor":"background","className":"omega-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background-color has-heading-background-color has-text-color has-background omega-card">
<!-- wp:heading {"level":4,"textColor":"background","fontSize":"medium"} --><h4 class="wp-block-heading has-background-color has-text-color has-medium-font-size"><?php esc_html_e('Join Our Foodie Community', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"background","fontSize":"small"} --><p class="has-background-color has-text-color has-small-font-size"><?php esc_html_e('Get the latest updates, special offers, and delicious stories.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — bon appétit!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — bon appétit!", 'omega-design'); ?>">
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
