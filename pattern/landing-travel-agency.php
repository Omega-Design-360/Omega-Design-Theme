<?php
/**
 * Title: Landing Page - Travel Agency
 * Slug: omega-design/landing-travel-agency
 * Categories: omega-design-general
 * Description: A full travel-agency landing page - photo hero with a booking search bar, feature strip, destinations grid, tour categories, a special-offer band, a "why choose us" grid, testimonials, and a blog + newsletter row. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, travel, tourism, destinations, booking, hero
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css and assets/css/landing-
 * travel-agency.css) for anything more custom - no hand-typed multi-
 * property inline `style` JSON and no Cover blocks. That's what keeps
 * every block's stored markup matching what the block editor itself would
 * generate, so opening this page never shows an "attempt recovery"
 * warning. See pattern/landing-fashion-store.php for the same approach.
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
	$out = '<!-- wp:columns {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-columns are-vertically-aligned-bottom\">\n";
	$out .= '<!-- wp:column {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-bottom\">\n";
	$out .= $eyebrow($eyebrow_text);
	$out .= '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">' . esc_html($heading_text) . '</h2><!-- /wp:heading -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	$out .= '<!-- wp:column {"verticalAlignment":"bottom"} -->' . "\n<div class=\"wp-block-column is-vertically-aligned-bottom\">\n";
	$out .= '<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#">' . esc_html($link_text) . '</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n</div><!-- /wp:columns -->\n";
	return $out;
};

/** Icon + title + description column, icon left of text (feature strip). */
$build_icon_row_left = function ($items) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"32px"}}} /-->' . "\n";
		$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"medium"} --><p class="omega-strong has-medium-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n</div><!-- /wp:column -->\n";
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

/** Icon-top pastel card (Why Choose Us grid). */
$build_highlight_card = function ($icon, $title, $desc) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-card","backgroundColor":"surface","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-card has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"primary","style":{"dimensions":{"width":"32px"}}} /-->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong"} --><p class="omega-strong">' . esc_html($title) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** A photo-with-overlay-text section - replaces a hand-authored Cover block. */
$build_photo_card = function ($size_class, $content_html, $align = '') use ($omega_ph) {
	$align_attr  = $align ? '"align":"' . $align . '",' : '';
	$align_class = $align ? 'align' . $align . ' ' : '';
	$out  = '<!-- wp:group {' . $align_attr . '"className":"omega-photo-card ' . esc_attr($size_class) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group ' . $align_class . 'omega-photo-card ' . esc_attr($size_class) . '">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="' . $omega_ph . '" alt=""/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained","contentSize":"600px"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card__content">' . "\n" . $content_html . '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** One destination card: photo, name, "From $x", star rating. */
$build_destination_card = function ($name, $price, $rating) use ($omega_ph) {
	$out  = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong"} --><p class="omega-strong">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size">' . esc_html(sprintf(__('From %s', 'omega-design'), $price)) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} --><p class="omega-stars has-small-font-size">&#9733; ' . esc_html($rating) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

/** One tour-category card: photo + caption. */
$build_category_card = function ($name) use ($omega_ph) {
	$out  = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} --><figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:paragraph {"align":"center","className":"omega-strong"} --><p class="has-text-align-center omega-strong">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--travel-agency","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--travel-agency">

<?php // HERO ?>
<?php
$hero_content  = $eyebrow(__('Discover. Explore. Experience.', 'omega-design'));
$hero_content .= '<!-- wp:heading {"level":1,"textColor":"background"} --><h1 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Your Next Adventure Awaits', 'omega-design') . '</h1><!-- /wp:heading -->' . "\n";
$hero_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Discover amazing destinations, unique experiences, and unforgettable memories with TravelGo.', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
echo $build_photo_card('omega-photo-card--tall', $hero_content, 'full');
?>

<?php // Booking search bar, overlapping the hero (see assets/css/landing-travel-agency.css) ?>
<!-- wp:group {"align":"wide","backgroundColor":"background","className":"omega-search-widget","style":{"spacing":{"padding":{"top":"var:preset|spacing|m","right":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|m"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-background-background-color has-background omega-search-widget omega-animate" style="padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--m);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--m)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
<?php
$fields = [
	['location-on', __('Where to?', 'omega-design'), __('Search destinations, tours…', 'omega-design')],
	['calendar-month', __('When?', 'omega-design'), __('Select dates', 'omega-design')],
	['person', __('Travelers', 'omega-design'), __('2 Adults', 'omega-design')],
];
foreach ($fields as $field) :
	?>
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:icon {"icon":"omega-icons/<?php echo esc_attr($field[0]); ?>","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->
<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php echo esc_html($field[1]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($field[2]); ?></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"accent","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-accent-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Search Now →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'sell',          'title' => __('Best Price Guarantee', 'omega-design'), 'desc' => __('Get the best deals', 'omega-design')],
	['icon' => 'map',           'title' => __('Handpicked Destinations', 'omega-design'), 'desc' => __('Curated with care', 'omega-design')],
	['icon' => 'support-agent', 'title' => __('24/7 Support', 'omega-design'), 'desc' => __("We're here for you", 'omega-design')],
	['icon' => 'verified-user', 'title' => __('Safe & Secure Booking', 'omega-design'), 'desc' => __('Your data is protected', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // DESTINATIONS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $build_section_header(__('Popular Destinations', 'omega-design'), __('Explore Top Destinations', 'omega-design'), __('View All Destinations →', 'omega-design')); ?>

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ([['Maldives', '$799', '4.8 (1.2K)'], ['Dubai', '$499', '4.7 (980)'], ['Bali', '$399', '4.8 (1.1K)']] as $d) {
	echo $build_destination_card($d[0], $d[1], $d[2]);
}
?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ([['Istanbul', '$450', '4.6 (760)'], ['Paris', '$599', '4.7 (980)'], ['Singapore', '$520', '4.8 (1.3K)']] as $d) {
	echo $build_destination_card($d[0], $d[1], $d[2]);
}
?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TOUR CATEGORIES ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $build_section_header(__('Find Your Perfect Trip', 'omega-design'), __('Popular Tour Categories', 'omega-design'), __('View All Tours →', 'omega-design')); ?>

<!-- wp:columns -->
<div class="wp-block-columns">
<?php foreach ([__('Beach Getaways', 'omega-design'), __('City Tours', 'omega-design'), __('Adventure Tours', 'omega-design')] as $c) { echo $build_category_card($c); } ?>
</div>
<!-- /wp:columns -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php foreach ([__('Family Trips', 'omega-design'), __('Honeymoon Packages', 'omega-design'), __('Cultural Experiences', 'omega-design')] as $c) { echo $build_category_card($c); } ?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // SPECIAL OFFER ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
$offer_content  = '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"accent","fontSize":"small"} --><p class="omega-eyebrow has-accent-color has-text-color has-small-font-size">' . esc_html__('Special Offer', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$offer_content .= '<!-- wp:heading {"level":2,"textColor":"background"} --><h2 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Save Up to 30%', 'omega-design') . '</h2><!-- /wp:heading -->' . "\n";
$offer_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('On selected travel packages', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$offer_content .= '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"accent","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-accent-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('View Deals →', 'omega-design') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' . "\n";
echo $build_photo_card('omega-photo-card--short', $offer_content);
?>
</div>
<!-- /wp:group -->

<?php // WHY CHOOSE US ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $eyebrow(__('Why Choose TravelGo', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Travel Better With Us', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns alignwide">
<?php
echo $build_highlight_card('groups', __('Expert Guidance', 'omega-design'), __('Travel experts with local knowledge', 'omega-design'));
echo $build_highlight_card('settings', __('Customizable Packages', 'omega-design'), __('Trips tailored to your needs', 'omega-design'));
echo $build_highlight_card('payments', __('Affordable Prices', 'omega-design'), __('More travel for less', 'omega-design'));
echo $build_highlight_card('favorite', __('Memorable Experiences', 'omega-design'), __('Create stories that last', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $build_section_header(__('What Our Travelers Say', 'omega-design'), __('Loved by Adventurers Worldwide', 'omega-design'), __('View All Reviews →', 'omega-design')); ?>

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
$testimonials = [
	[__('An amazing experience! Everything was perfectly planned and the support team were great.', 'omega-design'), __('Sarah K.', 'omega-design')],
	[__('Best travel company! The trip to Bali was a dream come true. Highly recommended.', 'omega-design'), __('Ali R.', 'omega-design')],
	[__('Professional, friendly, and reliable. I\'ll definitely book my next trip with TravelGo.', 'omega-design'), __('Maria S.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:image {"sizeSlug":"thumbnail","className":"omega-round-image omega-avatar-md"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t[1]); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">"<?php echo esc_html($t[0]); ?>"</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-strong omega-stars","fontSize":"small"} --><p class="omega-strong omega-stars has-small-font-size"><?php echo esc_html($t[1]); ?> ★★★★★</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // BLOG + NEWSLETTER ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $build_section_header(__('Travel Tips & Stories', 'omega-design'), __('Latest from Our Blog', 'omega-design'), __('View All Posts →', 'omega-design')); ?>

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
<!-- wp:heading {"level":4,"textColor":"button-text","fontSize":"medium"} --><h4 class="wp-block-heading has-button-text-color has-text-color has-medium-font-size"><?php esc_html_e('Join Our Travel Community', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text","fontSize":"small"} --><p class="has-button-text-color has-text-color has-small-font-size"><?php esc_html_e('Get travel tips, exclusive deals, and inspiration straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — happy travels!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — happy travels!", 'omega-design'); ?>">
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
