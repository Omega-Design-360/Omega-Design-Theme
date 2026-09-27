<?php
/**
 * Title: Landing Page - Bookstore
 * Slug: omega-design/landing-bookstore
 * Categories: omega-design-general
 * Description: A full bookstore landing page - hero, trust strip, a shop-by-genre row, tabbed featured products (live WooCommerce data), a promo banner, a feature strip, a testimonial slider, and a newsletter signup. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, bookstore, books, reading, shop, woocommerce
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-digital-agency.php: a
 * small, boring vocabulary of backgroundColor/textColor/fontSize-by-slug,
 * block align, and preset-token spacing, plus shared className hooks
 * (assets/css/landing-pages.css, assets/css/landing-bookstore.css) for
 * anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks, so the saved markup always matches what the
 * block editor itself would generate and never trips "attempt recovery".
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();

/** One genre card: icon, title, inside a Group carrying a numbered `omega-category-card--N` class (tint in assets/css/landing-bookstore.css). */
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
<!-- wp:group {"className":"omega-landing omega-landing--bookstore","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--bookstore">

<?php // HERO ?>
<!-- wp:group {"backgroundColor":"surface","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<?php echo omega_pattern_eyebrow(__('For Every Reader', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Your Next Great Read Awaits', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('New releases, timeless classics and staff picks - curated for readers who never want to put a book down.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Shop Books', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Browse Genres', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['20K+', __('Titles in Stock', 'omega-design')], ['60K+', __('Happy Readers', 'omega-design')], ['4.9★', __('Average Rating', 'omega-design')], ['Free', __('Bookmark with Every Order', 'omega-design')]] as $stat) : ?>
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
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Stack of books', 'omega-design'); ?>"/></figure><!-- /wp:image -->
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
	['icon' => 'local-shipping', 'title' => __('Free Shipping', 'omega-design'),  'desc' => __('On orders over $35', 'omega-design')],
	['icon' => 'verified',       'title' => __('Signed Editions', 'omega-design'), 'desc' => __('Available on select titles', 'omega-design')],
	['icon' => 'security',       'title' => __('Secure Payment', 'omega-design'), 'desc' => __('100% protected checkout', 'omega-design')],
	['icon' => 'support-agent',  'title' => __('Book Recommendations', 'omega-design'), 'desc' => __('From real booksellers', 'omega-design')],
	['icon' => 'inventory-2',    'title' => __('Easy Returns', 'omega-design'),   'desc' => __('30-day hassle free', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SHOP BY GENRE ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Shop by Genre', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Find Your Next Favorite', 'omega-design'); ?></h2><!-- /wp:heading -->
<?php
echo $build_category_row([
	['auto-stories',  __('Fiction', 'omega-design'), 1],
	['menu-book',     __('Non-Fiction', 'omega-design'), 2],
	['child-care',    __("Children's", 'omega-design'), 3],
	['search',        __('Mystery & Thriller', 'omega-design'), 4],
]);
echo $build_category_row([
	['rocket-launch', __('Sci-Fi & Fantasy', 'omega-design'), 5],
	['person',        __('Biography', 'omega-design'), 6],
	['history-edu',   __('History', 'omega-design'), 7],
	['local-library', __('Rare & Collectible', 'omega-design'), 8],
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURED PRODUCTS (TABS) ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo omega_pattern_eyebrow(__('Featured Titles', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('What Everyone\'s Reading', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(51, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('New Releases', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(52, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('On Sale', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo omega_pattern_product_collection(53, 'date', true); ?></div></div>
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
	'bg'          => '#1e293b',
	'offer_color' => '#ca8a04',
	'eyebrow'     => __('Limited Time', 'omega-design'),
	'title'       => __('Buy 2, Get 1 Free on Paperbacks', 'omega-design'),
	'offer'       => __('This Month Only', 'omega-design'),
	'body'        => __('Stock up on your reading list - mix and match across every genre in store.', 'omega-design'),
	'cta'         => __('Shop Paperbacks', 'omega-design'),
	'image_url'   => $omega_ph,
	'image_alt'   => __('Paperback book deal', 'omega-design'),
]);
?>
</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'local-shipping',    'title' => __('Fast Delivery', 'omega-design'),   'desc' => __('2-4 business days', 'omega-design')],
	['icon' => 'redeem',            'title' => __('Signed Copies', 'omega-design'),   'desc' => __('While supplies last', 'omega-design')],
	['icon' => 'support-agent',     'title' => __('Book Club Picks', 'omega-design'), 'desc' => __('New pick every month', 'omega-design')],
	['icon' => 'workspace-premium', 'title' => __('Rare & Collectible', 'omega-design'), 'desc' => __('First editions & more', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('What Our Readers Say', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":5000,"showArrows":true,"showDots":true,"className":"omega-testimonial-slider","prevLabel":"Previous testimonial","nextLabel":"Next testimonial","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="5000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous testimonial" data-next-label="Next testimonial" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	['quote' => __('The book club pick this month completely changed my reading habits. I look forward to it every time now.', 'omega-design'), 'name' => __('Olivia P.', 'omega-design')],
	['quote' => __('Found a signed first edition I\'d been hunting for years. Packaged with so much care it arrived in perfect condition.', 'omega-design'), 'name' => __('Daniel K.', 'omega-design')],
	['quote' => __('Their staff recommendations are always spot on. Better than any algorithm I\'ve tried.', 'omega-design'), 'name' => __('Fatima Z.', 'omega-design')],
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
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Join Our Reading List', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('New releases, staff picks and exclusive discounts - delivered to your inbox monthly.', 'omega-design'); ?></p><!-- /wp:paragraph -->
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
