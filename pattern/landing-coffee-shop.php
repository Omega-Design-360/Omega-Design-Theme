<?php
/**
 * Title: Landing Page - Coffee Shop
 * Slug: omega-design/landing-coffee-shop
 * Categories: omega-design-general
 * Description: A full cafe/coffee-shop landing page - photo hero, feature strip, tabbed menu, a special-offer band, an our-story section, a live WooCommerce bestsellers grid, an app promo, a multi-card testimonial carousel, latest blog posts and a newsletter signup. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, cafe, coffee shop, menu, hero, carousel, woocommerce
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Every block below sticks to a small, deliberately boring vocabulary of
 * attributes (backgroundColor/textColor/fontSize by slug, block align,
 * single-purpose preset-token spacing) plus a small set of shared
 * `className`s (assets/css/landing-pages.css, assets/css/landing-coffee-shop.css)
 * for anything more custom - no hand-typed multi-property inline `style`
 * JSON and no Cover blocks. That's what keeps every block's stored markup
 * matching what the block editor itself would generate, so opening this
 * page never shows an "attempt recovery" warning. See
 * pattern/landing-fashion-store.php for the same approach applied to
 * another page, and assets/css/landing-pages.css for the shared component
 * classes (.omega-eyebrow, .omega-photo-card, ...).
 */

defined('ABSPATH') || exit;

$omega_ph = esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');

/** An "OUR MENU"-style eyebrow line above a heading (uses the theme's primary color). */
$eyebrow = function ($text) {
	return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
		. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
		. '<!-- /wp:paragraph -->' . "\n";
};

/** Icon + title (+ optional description) row, icon left of text, items stacked vertically. */
$build_icon_row_left = function ($items) {
	$out = '<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|m"}}} -->' . "\n<div class=\"wp-block-group\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"24px"}}} /-->' . "\n";
		$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
		$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		if (!empty($item['desc'])) {
			$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		}
		$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n";
	}
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/**
 * Icon + title + description item, icon centered above the text (feature
 * strip, Our Story mini icons) - a flex Group of Groups rather than
 * Columns, matching the same reliable shape as $build_icon_row_left.
 */
$build_icon_row_centered = function ($items) {
	$out = '<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|m"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->' . "\n<div class=\"wp-block-group alignwide\">\n";
	foreach ($items as $item) {
		$out .= '<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap","justifyContent":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|xs"}}} -->' . "\n<div class=\"wp-block-group\">\n";
		$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"36px"}}} /-->' . "\n";
		$out .= '<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"medium"} --><p class="has-text-align-center omega-strong has-medium-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '<!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n";
	}
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

/**
 * One editable "menu item" card: photo, name, price and a round add button.
 * Static/editable rather than a live WooCommerce query, since a cafe menu's
 * categories (Hot Coffee, Iced Coffee, ...) won't exist as real product
 * categories in every store this pattern gets inserted into.
 */
$build_menu_item = function ($name, $price) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:image {"sizeSlug":"medium","className":"omega-rounded-image"} -->' . "\n";
	$out .= '<figure class="wp-block-image size-medium omega-rounded-image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure>' . "\n<!-- /wp:image -->\n";
	$out .= '<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"center","flexWrap":"nowrap"}} -->' . "\n<div class=\"wp-block-group\">\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size">' . esc_html($price) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:buttons --><div class="wp-block-buttons">' . "\n";
	$out .= '<!-- wp:button {"className":"omega-menu-add-btn","backgroundColor":"heading","textColor":"background"} -->' . "\n";
	$out .= '<div class="wp-block-button omega-menu-add-btn"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#" title="' . esc_attr(sprintf(__('Add %s', 'omega-design'), $name)) . '">+</a></div><!-- /wp:button -->' . "\n";
	$out .= '</div><!-- /wp:buttons -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:column -->' . "\n";
	return $out;
};

$build_menu_row = function ($items) use ($build_menu_item) {
	$out = '<!-- wp:columns -->' . "\n<div class=\"wp-block-columns\">\n";
	foreach ($items as $item) {
		$out .= $build_menu_item($item[0], $item[1]);
	}
	$out .= '</div>' . "\n<!-- /wp:columns -->\n";
	return $out;
};

/** One WooCommerce Product Collection block (used for the Bestsellers grid). */
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
<!-- wp:group {"className":"omega-landing omega-landing--coffee-shop","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--coffee-shop">

<?php // HERO ?>
<!-- wp:group {"align":"full","className":"omega-photo-card omega-photo-card--tall","layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-photo-card omega-photo-card--tall omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Coffee shop hero', 'omega-design'); ?>"/></figure><!-- /wp:image -->

<!-- wp:group {"className":"omega-hero-badge","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group omega-hero-badge">
<!-- wp:paragraph {"align":"center","className":"omega-eyebrow","textColor":"background","fontSize":"x-small"} -->
<p class="has-text-align-center omega-eyebrow has-background-color has-text-color has-x-small-font-size"><?php esc_html_e('Fresh Beans', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","textColor":"background","fontSize":"x-small"} -->
<p class="has-text-align-center has-background-color has-text-color has-x-small-font-size"><?php esc_html_e('Premium Quality', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained","contentSize":"600px"}} -->
<div class="wp-block-group omega-photo-card__content">

<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->
<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size"><?php esc_html_e('Good Coffee · Good Mood', 'omega-design'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"textColor":"background"} -->
<h1 class="wp-block-heading has-background-color has-text-color"><?php esc_html_e('Life Tastes Better with Coffee', 'omega-design'); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"background"} -->
<p class="has-background-color has-text-color"><?php esc_html_e('Freshly brewed. Carefully crafted. A perfect cup, every time.', 'omega-design'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Order Online', 'omega-design'); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline","textColor":"background"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-background-color has-text-color wp-element-button" href="#"><?php esc_html_e('Explore Menu', 'omega-design'); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"},"margin":{"top":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns">
<?php foreach ([['100%', __('Premium Beans', 'omega-design')], ['50+', __('Unique Recipes', 'omega-design')], ['10K+', __('Happy Customers', 'omega-design')]] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-strong","textColor":"background","fontSize":"large"} -->
<p class="omega-strong has-background-color has-text-color has-large-font-size"><?php echo esc_html($stat[0]); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"textColor":"background","fontSize":"small"} -->
<p class="has-background-color has-text-color has-small-font-size"><?php echo esc_html($stat[1]); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_centered([
	['icon' => 'local-cafe', 'title' => __('Premium Beans', 'omega-design'), 'desc' => __('Sourced from the best farms around the world', 'omega-design')],
	['icon' => 'kettle',     'title' => __('Freshly Brewed', 'omega-design'), 'desc' => __('Made fresh, just for you', 'omega-design')],
	['icon' => 'spa',        'title' => __('Cozy Atmosphere', 'omega-design'), 'desc' => __('The perfect place to relax', 'omega-design')],
	['icon' => 'wifi',       'title' => __('Free WiFi', 'omega-design'), 'desc' => __('Work, study or unwind', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // MENU (TABS) ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:55%">
<?php echo $eyebrow(__('Our Menu', 'omega-design')); ?>
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('Coffee for Every Mood', 'omega-design'); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e('From classic favorites to unique creations, there\'s a perfect cup waiting for you.', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('View Full Menu', 'omega-design'); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-bottom" style="flex-basis:45%">
<!-- wp:paragraph {"align":"right"} -->
<p class="has-text-align-right"><a href="#"><?php esc_html_e('All Menu →', 'omega-design'); ?></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Hot Coffee', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_menu_row([
	[__('Cappuccino', 'omega-design'), '$4.50'],
	[__('Café Latte', 'omega-design'), '$4.00'],
	[__('Americano', 'omega-design'), '$3.50'],
	[__('Flat White', 'omega-design'), '$4.25'],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Iced Coffee', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_menu_row([
	[__('Iced Americano', 'omega-design'), '$3.50'],
	[__('Iced Latte', 'omega-design'), '$4.25'],
	[__('Cold Brew', 'omega-design'), '$4.50'],
	[__('Iced Mocha', 'omega-design'), '$4.75'],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Specials', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_menu_row([
	[__('Caramel Macchiato', 'omega-design'), '$5.00'],
	[__('Honey Lavender Latte', 'omega-design'), '$5.25'],
	[__('Spiced Chai', 'omega-design'), '$4.75'],
	[__('Vanilla Bean Frappé', 'omega-design'), '$5.50'],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Pastries', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content">
<?php echo $build_menu_row([
	[__('Butter Croissant', 'omega-design'), '$3.25'],
	[__('Blueberry Muffin', 'omega-design'), '$3.50'],
	[__('Cinnamon Roll', 'omega-design'), '$3.75'],
	[__('Almond Biscotti', 'omega-design'), '$2.75'],
]); ?>
</div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->

<?php // SPECIAL OFFER ?>
<!-- wp:group {"align":"full","backgroundColor":"secondary","textColor":"button-text","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-button-text-color has-secondary-background-color has-text-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"38%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:38%">
<?php echo $eyebrow(__('Special Offer', 'omega-design')); ?>
<!-- wp:heading {"level":2,"textColor":"button-text"} -->
<h2 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Get 20% Off Your First Order', 'omega-design'); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} -->
<p class="has-button-text-color has-text-color"><?php echo wp_kses(__('Use code: <strong>BREWNEST20</strong>', 'omega-design'), ['strong' => []]); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Shop Now', 'omega-design'); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"32%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:32%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Coffee bag', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%">
<?php
echo $build_icon_row_left([
	['icon' => 'eco',      'title' => __('Organic & Sustainable', 'omega-design')],
	['icon' => 'shield',   'title' => __('Ethically Sourced', 'omega-design')],
	['icon' => 'favorite', 'title' => __('Better Coffee, A Brighter Tomorrow', 'omega-design')],
]);
?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // OUR STORY ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Coffee shop interior', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">
<?php echo $eyebrow(__('Our Story', 'omega-design')); ?>
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('More Than Coffee, A Community', 'omega-design'); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e('At BrewNest, we believe coffee brings people together. It\'s not just a drink, it\'s a moment of connection, creativity, and inspiration. Our mission is to serve exceptional coffee while building a space where everyone feels at home.', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Our Story', 'omega-design'); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<?php
echo $build_icon_row_centered([
	['icon' => 'workspace-premium', 'title' => __('Quality', 'omega-design'), 'desc' => __('In every cup', 'omega-design')],
	['icon' => 'groups',            'title' => __('People', 'omega-design'), 'desc' => __('At our heart', 'omega-design')],
	['icon' => 'eco',               'title' => __('Sustainability', 'omega-design'), 'desc' => __('For a better tomorrow', 'omega-design')],
]);
?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // BESTSELLERS (LIVE WOOCOMMERCE) ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('Featured Products', 'omega-design')); ?>
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('Bestsellers', 'omega-design'); ?></h2>
<!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} -->
<p class="has-text-align-right"><a href="#"><?php esc_html_e('View All →', 'omega-design'); ?></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php echo $build_product_collection(21, 'popularity'); ?>

</div>
<!-- /wp:group -->

<?php // APP PROMO ?>
<!-- wp:group {"align":"wide","className":"omega-rounded-band","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-rounded-band has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%">
<!-- wp:image {"sizeSlug":"medium","className":"omega-round-image"} -->
<figure class="wp-block-image size-medium omega-round-image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Coffee cup', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('Good Coffee At Your Fingertips', 'omega-design'); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e('Order through our app and enjoy exclusive deals, rewards and faster checkout.', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('App Store', 'omega-design'); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"backgroundColor":"heading","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-heading-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Google Play', 'omega-design'); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%">
<?php
echo $build_icon_row_left([
	['icon' => 'redeem',       'title' => __('Exclusive Deals', 'omega-design')],
	['icon' => 'receipt-long', 'title' => __('Track Your Order', 'omega-design')],
	['icon' => 'loyalty',      'title' => __('Earn Rewards', 'omega-design')],
	['icon' => 'storefront',   'title' => __('Find Nearest Store', 'omega-design')],
]);
?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo $eyebrow(__('What Our Customers Say', 'omega-design')); ?>
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('Loved by Coffee Lovers', 'omega-design'); ?></h2>
<!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":false,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","className":"omega-testimonial-slider","prevLabel":"Previous testimonials","nextLabel":"Next testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="0" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="Previous testimonials" data-next-label="Next testimonials" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$testimonials = [
	['quote' => __('Amazing coffee and such a cozy atmosphere! My favorite place to work and relax.', 'omega-design'), 'name' => __('Sarah K.', 'omega-design')],
	['quote' => __('Best coffee in town! The quality and flavor are always on point.', 'omega-design'), 'name' => __('Ali R.', 'omega-design')],
	['quote' => __('Great service, delicious drinks, and a beautiful space. Highly recommend!', 'omega-design'), 'name' => __('Maria S.', 'omega-design')],
];
foreach ($testimonials as $t) :
	?>
<!-- wp:group {"className":"omega-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-surface-background-color has-background">
<!-- wp:paragraph {"className":"omega-italic"} -->
<p class="omega-italic">"<?php echo esc_html($t['quote']); ?>"</p>
<!-- /wp:paragraph -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->
<div class="wp-block-group">
<!-- wp:image {"sizeSlug":"thumbnail","className":"omega-round-image omega-avatar-md"} -->
<figure class="wp-block-image size-thumbnail omega-round-image omega-avatar-md"><img src="<?php echo $omega_ph; ?>" alt="<?php echo esc_attr($t['name']); ?>"/></figure>
<!-- /wp:image -->
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} -->
<p class="omega-strong has-small-font-size"><?php echo esc_html($t['name']); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-stars","fontSize":"small"} -->
<p class="omega-stars has-small-font-size">★★★★★</p>
<!-- /wp:paragraph -->
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

<?php // BLOG ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo $eyebrow(__('From Our Blog', 'omega-design')); ?>
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e('Coffee Stories & Tips', 'omega-design'); ?></h2>
<!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} -->
<p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Posts →', 'omega-design'); ?></a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

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
<!-- /wp:group -->

<?php // NEWSLETTER ?>
<!-- wp:group {"align":"full","backgroundColor":"heading","textColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-background-color has-heading-background-color has-text-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:heading {"level":3,"textColor":"background"} -->
<h3 class="wp-block-heading has-background-color has-text-color"><?php esc_html_e('Join Our Coffee Community', 'omega-design'); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"background"} -->
<p class="has-background-color has-text-color"><?php esc_html_e('Get exclusive offers, brewing tips, and the latest updates.', 'omega-design'); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo esc_attr__("Thanks — welcome to the community!", 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo esc_attr__("Thanks — welcome to the community!", 'omega-design'); ?>">
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
