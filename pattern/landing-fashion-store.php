<?php
/**
 * Title: Landing Page - Fashion Store
 * Slug: omega-design/landing-fashion-store
 * Categories: omega-design-general
 * Description: A full fashion/e-commerce landing page - hero slider, trust badges, shop-by-category, tabbed trending products (live WooCommerce data), promo banners, brand strip, feature row, Instagram + app promo, latest blog posts, testimonial carousel and a newsletter signup form. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, ecommerce, fashion, shop, hero, carousel, woocommerce
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
 * "attempt recovery" warning. See assets/css/landing-pages.css for the
 * shared component classes (.omega-eyebrow, .omega-photo-card, ...) and
 * blocks/omega-slider, blocks/omega-tabs, blocks/omega-newsletter for the
 * theme's own interactive blocks used here.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';

// Same callables as before (each $name(...) call below is unchanged) - now
// backed by shared functions in includes/patterns/pattern-helpers.php so
// the standalone per-section patterns (pattern/section-fashion-*.php) can
// reuse the exact same markup builders instead of duplicating them.
$omega_ph = omega_pattern_placeholder_url();
$eyebrow = 'omega_pattern_eyebrow';
$build_icon_row_left = 'omega_pattern_icon_row_left';
$build_photo_card = 'omega_pattern_photo_card';
$build_corner_badge = 'omega_pattern_corner_badge';
$build_product_collection = 'omega_pattern_product_collection';

?>
<!-- wp:group {"className":"omega-landing omega-landing--fashion-store","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--fashion-store">

<?php // HERO SLIDER ?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":7000,"loop":true,"showArrows":true,"showDots":true,"align":"full","className":"omega-hero-slider","prevLabel":"Previous slide","nextLabel":"Next slide","dotsLabel":"Slides"} -->
<div class="wp-block-omega-design-slider omega-hero-slider alignfull omega-slider" data-autoplay="1" data-autoplay-speed="7000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous slide" data-next-label="Next slide" data-dots-label="Slides" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
$hero_slides = [
	['eyebrow' => __('New Collection', 'omega-design'), 'heading' => __('Style Without Limits', 'omega-design'), 'body' => __('Trendy. Comfortable. Uniquely you. Explore the latest fashion for women, men and kids.', 'omega-design'), 'cta' => __('Shop Now', 'omega-design'), 'stats' => [['10K+', __('Happy Customers', 'omega-design')], ['4.8★', __('Average Rating', 'omega-design')], ['100%', __('Secure Shopping', 'omega-design')]]],
	['eyebrow' => __('This Week Only', 'omega-design'), 'heading' => __('New Arrivals Every Week', 'omega-design'), 'body' => __('Fresh drops across women\'s, men\'s and kids\' collections, updated weekly so there\'s always something new.', 'omega-design'), 'cta' => __('Shop New Arrivals', 'omega-design'), 'stats' => [['500+', __('New Styles', 'omega-design')], ['4.8★', __('Average Rating', 'omega-design')], ['100%', __('Secure Shopping', 'omega-design')]]],
	['eyebrow' => __('Limited Time', 'omega-design'), 'heading' => __('Free Shipping On Orders $50+', 'omega-design'), 'body' => __('No code needed, free standard shipping is automatically applied at checkout on every order over $50.', 'omega-design'), 'cta' => __('Start Shopping', 'omega-design'), 'stats' => [['Free', __('Shipping $50+', 'omega-design')], ['30-Day', __('Easy Returns', 'omega-design')], ['100%', __('Secure Checkout', 'omega-design')]]],
];
$hero_image = omega_pattern_fashion_asset('hero/hero-models.png');
$hero_badge = omega_pattern_fashion_asset('hero/sale-badge.png');
foreach ($hero_slides as $i => $slide) :
	?>
<!-- wp:group {"backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-surface-background-color has-background is-layout-constrained" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" data-slide-animation="fade-up" style="flex-basis:50%">
<?php echo $eyebrow($slide['eyebrow']); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php echo esc_html($slide['heading']); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php echo esc_html($slide['body']); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html($slide['cta']); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns">
<?php foreach ($slide['stats'] as $stat) : ?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-stat__value","fontSize":"large"} --><p class="omega-stat__value has-large-font-size"><?php echo esc_html($stat[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($stat[1]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"omega-hero-visual"} -->
<div class="wp-block-column omega-hero-visual" style="flex-basis:50%">
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} -->
<figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Hero image', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
<?php if (0 === $i) : ?>
<!-- wp:image {"className":"omega-hero-badge"} -->
<figure class="wp-block-image omega-hero-badge"><img src="<?php echo esc_url($hero_badge); ?>" alt="<?php esc_attr_e('Up to 50% off', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->
<?php endif; ?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->

<?php // TRUST BADGES ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'local-shipping',    'title' => __('Free Shipping', 'omega-design'),    'desc' => __('On orders over $50', 'omega-design')],
	['icon' => 'inventory-2',       'title' => __('Easy Returns', 'omega-design'),     'desc' => __('30-day hassle free', 'omega-design')],
	['icon' => 'shield-lock',       'title' => __('Secure Payment', 'omega-design'),   'desc' => __('100% secure checkout', 'omega-design')],
	['icon' => 'support-agent',     'title' => __('24/7 Support', 'omega-design'),     'desc' => __("We're always here", 'omega-design')],
	['icon' => 'workspace-premium', 'title' => __('Premium Quality', 'omega-design'),  'desc' => __('Only the best for you', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // SHOP BY CATEGORY ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Shop by Category', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-category-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-row">
<?php
// The theme's own asset filenames for the first 8 were renamed to
// descriptive names at some point after this loop was written (which
// still generated "category-01.png" etc for every item) - only category-
// 09/10/11.png were ever left with their original numbered names, so
// those three still resolve correctly while the first 8 don't.
$category_files = ['women.png', 'men.png', 'kids.png', 'dresses.png', 'tops.png', 'bottoms.png', 'outerwear.png', 'shoes.png', 'category-09.png', 'category-10.png', 'category-11.png'];
foreach (['Women', 'Men', 'Kids', 'Dresses', 'Tops', 'Bottoms', 'Outerwear', 'Shoes', 'Bags', 'Accessories', 'Sale'] as $cat_i => $cat) : ?>
<!-- wp:group {"className":"omega-category-item","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-category-item">
<!-- wp:image {"className":"omega-round-image","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-round-image"><img src="<?php echo esc_url(omega_pattern_fashion_asset('categories/' . $category_files[$cat_i])); ?>" alt="<?php echo esc_attr($cat); ?>"/></figure><!-- /wp:image -->
<!-- wp:paragraph {"align":"center","className":"omega-strong","fontSize":"small"} --><p class="has-text-align-center omega-strong has-small-font-size"><?php echo esc_html($cat); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php // TRENDING PRODUCTS (TABS) ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Trending Products', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/tabs -->
<div class="wp-block-omega-design-tabs omega-tabs"><div class="omega-tabs__panels">

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Best Sellers', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(11, 'popularity'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('New Arrivals', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(12, 'date'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('Top Rated', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(13, 'rating'); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

<!-- wp:omega-design/tabs-item {"label":"<?php echo esc_attr__('On Sale', 'omega-design'); ?>"} -->
<div class="wp-block-omega-design-tabs-item omega-tabs__panel"><div class="omega-tabs__panel-content"><?php echo $build_product_collection(14, 'date', true); ?></div></div>
<!-- /wp:omega-design/tabs-item -->

</div></div>
<!-- /wp:omega-design/tabs -->

</div>
<!-- /wp:group -->

<?php // PROMO BANNERS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Shop the Collection', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns">
<?php
$promos = [
	['bg' => '#fbe4e2', 'offer_color' => '#c0392b', 'eyebrow' => '', 'title' => __("Women's Collection", 'omega-design'), 'offer' => __('Up to 40% Off', 'omega-design'), 'body' => __('Discover your perfect look', 'omega-design'), 'cta' => __('Shop Women', 'omega-design'), 'image' => 'promos/womens-collection.png'],
	['bg' => '#e9e7e3', 'offer_color' => '#1c1c1c', 'eyebrow' => '', 'title' => __("Men's Essentials", 'omega-design'), 'offer' => __('Timeless Styles', 'omega-design'), 'body' => __('For the modern man', 'omega-design'), 'cta' => __('Shop Men', 'omega-design'), 'image' => 'promos/mens-essentials.png'],
	['bg' => '#e1edf7', 'offer_color' => '#1c1c1c', 'eyebrow' => '', 'title' => __('Kids Fashion', 'omega-design'), 'offer' => __('Little Outfits', 'omega-design'), 'body' => __('Big Smiles', 'omega-design'), 'cta' => __('Shop Kids', 'omega-design'), 'image' => 'promos/kids-fashion.png'],
];
foreach ($promos as $promo) :
	?>
<!-- wp:column -->
<div class="wp-block-column">
<?php
echo omega_pattern_split_promo([
	'bg'          => $promo['bg'],
	'offer_color' => $promo['offer_color'],
	'title'       => $promo['title'],
	'offer'       => $promo['offer'],
	'body'        => $promo['body'],
	'cta'         => $promo['cta'],
	'image_url'   => omega_pattern_fashion_asset($promo['image']),
	'image_alt'   => $promo['title'],
]);
?>
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TOP BRANDS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Top Brands', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-brand-row","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group omega-brand-row">
<?php
// Only the brands with a real logo file in assets/images/.../brands/ -
// H&M, Levi's and Mango were referenced here but never had a matching
// asset, so they always rendered as a broken image.
$brands = [
	['name' => 'Nike', 'file' => 'nike.png'],
	['name' => 'Adidas', 'file' => 'adidas.png'],
	['name' => 'Zara', 'file' => 'zara.png'],
	['name' => 'Puma', 'file' => 'puma.png'],
	['name' => 'Calvin Klein', 'file' => 'calvin-klein.png'],
	['name' => 'Gucci', 'file' => 'gucci.png'],
	['name' => 'Reebok', 'file' => 'reebok.png'],
];
foreach ($brands as $brand) :
	?>
<!-- wp:image {"className":"omega-brand-row__item","sizeSlug":"thumbnail"} --><figure class="wp-block-image size-thumbnail omega-brand-row__item"><img src="<?php echo esc_url(omega_pattern_fashion_asset('brands/' . $brand['file'])); ?>" alt="<?php echo esc_attr($brand['name']); ?>"/></figure><!-- /wp:image -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php // FEATURE STRIP ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo $build_icon_row_left([
	['icon' => 'eco',      'title' => __('Sustainable Fashion', 'omega-design'), 'desc' => __('Better for you, better for the planet', 'omega-design')],
	['icon' => 'diamond',  'title' => __('Premium Quality', 'omega-design'),      'desc' => __('Carefully selected materials', 'omega-design')],
	['icon' => 'favorite', 'title' => __('Styles for Everyone', 'omega-design'),  'desc' => __('Fashion for all ages', 'omega-design')],
	['icon' => 'public',   'title' => __('Worldwide Shipping', 'omega-design'),   'desc' => __('Delivering happiness globally', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // INSTAGRAM + BLOG (left) / APP PROMO (right) ?>
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Follow Us on Instagram', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"className":"omega-instagram-handle","textColor":"primary"} --><p class="omega-instagram-handle has-primary-color has-text-color"><?php esc_html_e('@stylehub', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:gallery {"columns":6,"linkTo":"none","className":"omega-instagram-grid"} -->
<figure class="wp-block-gallery has-nested-images columns-6 is-cropped omega-instagram-grid">
<?php for ($i = 1; $i <= 6; $i++) : ?>
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} --><figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo esc_url(omega_pattern_fashion_asset(sprintf('instagram/instagram-%02d.png', $i))); ?>" alt="<?php esc_attr_e('Instagram photo', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<?php endfor; ?>
</figure>
<!-- /wp:gallery -->
</div>
<!-- /wp:group -->

<?php // LATEST FROM THE BLOG ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:group {"className":"omega-section-heading-row","layout":{"type":"flex","justifyContent":"space-between"}} -->
<div class="wp-block-group omega-section-heading-row">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Latest from Our Blog', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/')); ?>"><?php esc_html_e('View All Posts', 'omega-design'); ?> &rarr;</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:query {"className":"omega-blog-list","query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query omega-blog-list">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:post-title {"level":4,"isLink":true} /-->
<!-- wp:post-date {"fontSize":"small"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:group {"className":"omega-card","backgroundColor":"accent","textColor":"button-text","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-card has-button-text-color has-accent-background-color has-text-color has-background">
<!-- wp:image {"className":"omega-app-phone-image"} --><figure class="wp-block-image omega-app-phone-image"><img src="<?php echo esc_url(omega_pattern_fashion_asset('app/app-phone.png')); ?>" alt="<?php esc_attr_e('App screenshot', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Download Our App', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('Shop anytime, anywhere - exclusive app-only deals, faster checkout and order tracking.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:list {"textColor":"button-text"} --><ul class="wp-block-list has-button-text-color has-text-color">
<!-- wp:list-item --><li><?php esc_html_e('Exclusive app deals', 'omega-design'); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><?php esc_html_e('Faster checkout', 'omega-design'); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><?php esc_html_e('Track your orders', 'omega-design'); ?></li><!-- /wp:list-item -->
</ul><!-- /wp:list -->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('App Store', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"backgroundColor":"background","textColor":"heading"} --><div class="wp-block-button"><a class="wp-block-button__link has-heading-color has-background-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Google Play', 'omega-design'); ?></a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide has-surface-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('What Our Customers Say', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":5000,"showArrows":true,"showDots":true,"className":"omega-testimonial-slider","prevLabel":"Previous testimonial","nextLabel":"Next testimonial","dotsLabel":"Testimonials"} -->
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
<!-- wp:heading {"level":3,"textColor":"button-text"} --><h3 class="wp-block-heading has-button-text-color has-text-color"><?php esc_html_e('Join Our Newsletter', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"button-text"} --><p class="has-button-text-color has-text-color"><?php esc_html_e('Get exclusive deals, fashion tips, and new arrivals straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
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
