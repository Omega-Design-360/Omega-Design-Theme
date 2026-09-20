<?php
/**
 * Title: Landing Page - Gift Shop
 * Slug: omega-design/landing-gift-shop
 * Categories: omega-design-general
 * Description: A full gifts/flowers e-commerce landing page - hero slider, shop-by-occasion, tabbed featured products (live WooCommerce data), a personalized-gifts promo, trust badges, two promo banners, testimonials and a blog + newsletter row. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, gifts, flowers, ecommerce, hero, carousel, woocommerce
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css) for anything more custom -
 * no hand-typed multi-property inline `style` JSON and no Cover blocks.
 * That's what keeps every block's stored markup matching what the block
 * editor itself would generate, so opening this page never shows an
 * "attempt recovery" warning. See pattern/landing-fashion-store.php for the
 * template this file follows.
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR MENU"-style eyebrow line above a heading. */
$eyebrow = function ($text) {
	return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
		. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** Icon + title + description column, icon left of text (trust badges). */
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

/** A photo-with-overlay-text section - replaces a hand-authored Cover block. $badge_html, when given, is inserted as a sibling of the background image so it can be absolutely positioned over a corner (see .omega-gift-badge in assets/css/landing-gift-shop.css). */
$build_photo_card = function ($size_class, $content_html, $badge_html = '') use ($omega_ph) {
	$out = '<!-- wp:group {"className":"omega-photo-card ' . esc_attr($size_class) . '","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card ' . esc_attr($size_class) . '">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="' . $omega_ph . '" alt=""/></figure><!-- /wp:image -->' . "\n";
	$out .= $badge_html;
	$out .= '<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-photo-card__content">' . "\n" . $content_html . '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/** Small circular badge overlapping the top-right corner of a photo card (built via $build_photo_card's $badge_html param) - same technique as .omega-hero-badge in pattern/landing-coffee-shop.php. */
$build_corner_badge = function ($class, $eyebrow_text, $main_text) {
	return '<!-- wp:group {"className":"' . esc_attr($class) . '","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->' . "\n"
		. '<div class="wp-block-group ' . esc_attr($class) . '">' . "\n"
		. '<!-- wp:paragraph {"align":"center","className":"omega-eyebrow","textColor":"background","fontSize":"x-small"} --><p class="has-text-align-center omega-eyebrow has-background-color has-text-color has-x-small-font-size">' . esc_html($eyebrow_text) . '</p><!-- /wp:paragraph -->' . "\n"
		. '<!-- wp:paragraph {"align":"center","textColor":"background","fontSize":"medium"} --><p class="has-text-align-center has-background-color has-text-color has-medium-font-size">' . esc_html($main_text) . '</p><!-- /wp:paragraph -->' . "\n"
		. '</div>' . "\n<!-- /wp:group -->\n";
};

/** One WooCommerce Product Collection block (used for each Featured Products tab). */
$build_product_collection = function ($query_id, $order_by, $on_sale = false) {
	$query = [
		'perPage' => 6, 'pages' => 0, 'offset' => 0, 'postType' => 'product',
		'order' => 'desc', 'orderBy' => $order_by, 'search' => '', 'exclude' => [],
		'inherit' => false, 'taxQuery' => [], 'isProductCollectionBlock' => true,
		'woocommerceOnSale' => $on_sale, 'woocommerceStockStatus' => ['instock', 'outofstock', 'onbackorder'],
		'woocommerceAttributes' => [], 'woocommerceHandPickedProducts' => [],
	];
	$attrs = [
		'queryId' => $query_id, 'query' => $query, 'tagName' => 'div',
		'dimensions' => ['widthType' => 'fill', 'fixedWidth' => ''],
		'displayLayout' => ['type' => 'flex', 'columns' => 3],
		'queryContextIncludes' => ['collection'],
	];
	$inner = '<!-- wp:woocommerce/product-template -->' . "\n"
		. '<!-- wp:woocommerce/product-image {"showSaleBadge":true,"isDescendentOfQueryLoop":true,"aspectRatio":"3/4"} -->' . "\n"
		. '<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->' . "\n"
		. '<!-- /wp:woocommerce/product-image -->' . "\n\n"
		. '<!-- wp:post-title {"textAlign":"center","level":3,"isLink":true,"fontSize":"medium"} /-->' . "\n\n"
		. '<!-- wp:woocommerce/product-rating {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->' . "\n\n"
		. '<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center","fontSize":"medium"} /-->' . "\n\n"
		. '<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true} /-->' . "\n"
		. '<!-- /wp:woocommerce/product-template -->';
	return '<!-- wp:woocommerce/product-collection ' . wp_json_encode($attrs) . ' -->' . "\n"
		. '<div class="wp-block-woocommerce-product-collection">' . "\n" . $inner . "\n</div>\n"
		. '<!-- /wp:woocommerce/product-collection -->';
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--gift-shop","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--gift-shop">

<?php // HERO SLIDER ?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":7000,"loop":true,"showArrows":true,"showDots":true,"align":"full","className":"omega-hero-slider","prevLabel":"Previous slide","nextLabel":"Next slide","dotsLabel":"Slides"} -->
<div class="wp-block-omega-design-slider omega-hero-slider alignfull omega-slider" data-autoplay="1" data-autoplay-speed="7000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous slide" data-next-label="Next slide" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$hero_slides = [
	['eyebrow' => __('Gifts That Speak from the Heart', 'omega-design'), 'heading' => __('Make Every Moment Special', 'omega-design'), 'body' => __('Beautiful gifts, fresh flowers, and personalized surprises for the people you love.', 'omega-design'), 'cta' => __('Shop Now', 'omega-design'), 'stats' => [['redeem', __('10K+ Happy Customers', 'omega-design')], ['star', __('4.8 Average Rating', 'omega-design')], ['local-shipping', __('Free Delivery $50+', 'omega-design')]]],
	['eyebrow' => __('This Week Only', 'omega-design'), 'heading' => __('Fresh Flowers, Delivered With Love', 'omega-design'), 'body' => __('Handpicked bouquets delivered same-day, straight to their door.', 'omega-design'), 'cta' => __('Shop Flowers', 'omega-design'), 'stats' => [['local-shipping', __('Same-Day Delivery', 'omega-design')], ['star', __('4.8 Average Rating', 'omega-design')], ['redeem', __('Freshness Guaranteed', 'omega-design')]]],
	['eyebrow' => __('New Arrivals', 'omega-design'), 'heading' => __('Personalized Gifts, Made Just for Them', 'omega-design'), 'body' => __('Add a name, a date, a little something extra - make it unforgettable.', 'omega-design'), 'cta' => __('Shop Personalized', 'omega-design'), 'stats' => [['redeem', __('500+ Gift Ideas', 'omega-design')], ['star', __('4.8 Average Rating', 'omega-design')], ['local-shipping', __('Free Delivery $50+', 'omega-design')]]],
];
foreach ($hero_slides as $slide) :
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
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Explore Gifts', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ($slide['stats'] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|xs"}}} -->
<div class="wp-block-group">
<!-- wp:icon {"icon":"omega-icons/<?php echo esc_attr($stat[0]); ?>","textColor":"primary","style":{"dimensions":{"width":"20px"}}} /-->
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Hero image', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->

<?php // SHOP BY OCCASION ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Shop by Occasion', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-category-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-row">
<?php foreach ([__('Birthday', 'omega-design'), __('Anniversary', 'omega-design'), __("Valentine's Day", 'omega-design'), __('Wedding', 'omega-design'), __('New Baby', 'omega-design'), __('Congratulations', 'omega-design'), __('Get Well Soon', 'omega-design'), __('Thank You', 'omega-design'), __('Just Because', 'omega-design')] as $occ) : ?>
<!-- wp:group {"className":"omega-category-item","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-item">
<!-- wp:image {"className":"omega-round-image","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($occ); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"small"} --><p class="has-text-align-center omega-strong has-small-font-size"><?php echo esc_html($occ); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php // FEATURED PRODUCTS (TABS) ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Featured Products', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(31, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Flowers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(32, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Personalized', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(33, 'rating'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Combo Offers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(34, 'date', true); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->

<?php // PERSONALIZED GIFTS PROMO ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
$personalized_content = $eyebrow(__('Make It Extra Special', 'omega-design'));
$personalized_content .= '<!-- wp:heading {"level":3,"textColor":"background"} --><h3 class="wp-block-heading has-background-color has-text-color">' . esc_html__('Personalized Gifts', 'omega-design') . '</h3><!-- /wp:heading -->' . "\n";
$personalized_content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html__('Add a personal touch to your gifts and create lasting memories.', 'omega-design') . '</p><!-- /wp:paragraph -->' . "\n";
$personalized_content .= '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#">' . esc_html__('Shop Personalized', 'omega-design') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' . "\n";
$gift_badge = $build_corner_badge('omega-gift-badge', __('Free', 'omega-design'), __('Gift Wrap', 'omega-design'));
echo $build_photo_card('omega-photo-card--tall', $personalized_content, $gift_badge);
?>
</div>
<!-- /wp:group -->

<?php // TRUST BADGES ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'local-shipping', 'title' => __('Free Shipping', 'omega-design'), 'desc' => __('On orders over $50', 'omega-design')],
	['icon' => 'shield-lock',    'title' => __('Secure Payment', 'omega-design'), 'desc' => __('100% secure checkout', 'omega-design')],
	['icon' => 'inventory-2',    'title' => __('Easy Returns', 'omega-design'), 'desc' => __('30-day hassle free', 'omega-design')],
	['icon' => 'support-agent',  'title' => __('24/7 Support', 'omega-design'), 'desc' => __("We're always here", 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // PROMO BANNERS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
$promos = [
	['title' => __('Fresh Flowers', 'omega-design'), 'sub' => __('For Brighter Days', 'omega-design'), 'body' => __('Handpicked. Fresh. Beautiful.', 'omega-design'), 'cta' => __('Shop Flowers', 'omega-design')],
	['title' => __('Gift Combos', 'omega-design'),   'sub' => __('More Love, More Savings', 'omega-design'), 'body' => __('Special combinations for special people.', 'omega-design'), 'cta' => __('View Combos', 'omega-design')],
];
foreach ($promos as $promo) :
	$content = $eyebrow($promo['sub']);
	$content .= '<!-- wp:heading {"level":3,"textColor":"background"} --><h3 class="wp-block-heading has-background-color has-text-color">' . esc_html($promo['title']) . '</h3><!-- /wp:heading -->' . "\n";
	$content .= '<!-- wp:paragraph {"textColor":"background"} --><p class="has-background-color has-text-color">' . esc_html($promo['body']) . '</p><!-- /wp:paragraph -->' . "\n";
	$content .= '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#">' . esc_html($promo['cta']) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' . "\n";
	?>
<!-- wp:column -->
<div class="wp-block-column">
<?php echo $build_photo_card('omega-photo-card--tall', $content); ?>
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('What Our Customers Say', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
$testimonials = [
	['quote' => __('Absolutely beautiful flowers! The quality and delivery were perfect. Will definitely order again!', 'omega-design'), 'name' => __('Sarah K.', 'omega-design')],
	['quote' => __('The personalized gift made my anniversary so special. Highly recommended!', 'omega-design'), 'name' => __('Ali R.', 'omega-design')],
	['quote' => __('Amazing service and such unique gift options. My go-to store for every occasion!', 'omega-design'), 'name' => __('Maria S.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:image {"className":"omega-round-image omega-avatar-lg","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-lg"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t['name']); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"align":"center","className":"omega-stars"} --><p class="has-text-align-center omega-stars">★★★★★</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-italic","fontSize":"small"} --><p class="has-text-align-center omega-italic has-small-font-size">"<?php echo esc_html($t['quote']); ?>"</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"small"} --><p class="has-text-align-center omega-strong has-small-font-size"><?php echo esc_html($t['name']); ?></p><!-- /wp:paragraph -->
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
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Love & Gifting Tips', 'omega-design'); ?></h2><!-- /wp:heading -->
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
<!-- wp:group {"className":"omega-card","backgroundColor":"primary","textColor":"button-text","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-button-text-color has-primary-background-color has-text-color has-background">
<!-- wp:heading {"level":4,"textColor":"button-text","fontSize":"medium"} --><h4 class="wp-block-heading has-button-text-color has-text-color has-medium-font-size"><?php esc_html_e('Join Our Heartfelt Community', 'omega-design'); ?></h4><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text","fontSize":"small"} --><p class="has-button-text-color has-text-color has-small-font-size"><?php esc_html_e('Get exclusive offers, gifting ideas, and more straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — spread the love!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — spread the love!", 'omega-design'); ?>">
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
