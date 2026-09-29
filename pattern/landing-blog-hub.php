<?php
/**
 * Title: Landing Page - Blog Hub
 * Slug: omega-design/landing-blog-hub
 * Categories: omega-design-general
 * Description: A blog home page for a tech / tools / lifestyle publication - image hero, colorful topic tiles, a featured article, latest articles with topic pills, popular articles and reader testimonials beside a sidebar with search, newsletter, popular categories, numbered trending posts and tags, plus a "trusted by" logo strip. Every post, category and tag section is live from your own content.
 * Keywords: landing page, blog, blogging, magazine, articles, tutorials, reviews, tools, tech, news
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as the other landing patterns: preset
 * slugs, shared className hooks (assets/css/landing-blog-hub.css) and no
 * hand-typed multi-property inline styles, so the markup always matches
 * what the block editor itself would save.
 *
 * Post loops use consecutive offsets (featured 0, latest 1-6, popular
 * 7-11) so no article appears twice in the main column; the sidebar's
 * "Trending" list deliberately repeats the newest headlines as a scan.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

$omega_hub_img = esc_url(get_template_directory_uri() . '/assets/images/blog-hub/hero.jpg');

/** Query Loop opening comment + wrapper for a non-inherited post query. */
$omega_hub_query = function ($query_id, $per_page, $offset, $class) {
	$attrs = [
		'queryId'   => $query_id,
		'query'     => [
			'perPage' => $per_page, 'pages' => 0, 'offset' => $offset, 'postType' => 'post',
			'order' => 'desc', 'orderBy' => 'date', 'author' => '', 'search' => '',
			'exclude' => [], 'sticky' => '', 'inherit' => false,
		],
		'className' => $class,
	];
	return '<!-- wp:query ' . wp_json_encode($attrs) . ' -->' . "\n" . '<div class="wp-block-query ' . esc_attr($class) . '">' . "\n";
};

/** Date · reading time · round arrow link. */
$omega_hub_meta = function ($with_arrow = true) {
	return '<!-- wp:group {"className":"omega-hub-meta","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->' . "\n"
		. '<div class="wp-block-group omega-hub-meta">' . "\n"
		. '<!-- wp:post-date {"format":"M j, Y","fontSize":"small"} /-->' . "\n"
		. '<!-- wp:post-time-to-read {"displayAsRange":false,"className":"omega-read-short","fontSize":"small"} /-->' . "\n"
		. ($with_arrow ? '<!-- wp:read-more {"content":"' . esc_attr__('Read article', 'omega-design') . '","className":"omega-hub-go"} /-->' . "\n" : '')
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
};

/** A big horizontal "feature" card: image left, chip/title/excerpt/meta right. */
$omega_hub_big_card = function ($query_id, $offset, $class) use ($omega_hub_query, $omega_hub_meta) {
	return $omega_hub_query($query_id, 1, $offset, $class)
		. '<!-- wp:post-template -->' . "\n"
		. '<!-- wp:group {"className":"omega-hub-feature","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group omega-hub-feature">' . "\n"
		. '<!-- wp:post-featured-image {"isLink":true} /-->' . "\n"
		. '<!-- wp:group {"className":"omega-hub-feature__body","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group omega-hub-feature__body">' . "\n"
		. '<!-- wp:post-terms {"term":"category","className":"omega-hub-chip"} /-->' . "\n"
		. '<!-- wp:post-title {"level":3,"isLink":true,"className":"omega-hub-feature__title"} /-->' . "\n"
		. '<!-- wp:post-excerpt {"excerptLength":26} /-->' . "\n"
		. $omega_hub_meta(true)
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n"
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n"
		. '<!-- /wp:post-template -->' . "\n"
		. '</div>' . "\n" . '<!-- /wp:query -->' . "\n";
};
/** Section heading row: title + optional "View all" link. */
$omega_hub_head = function ($title, $link = true) {
	$out  = '<!-- wp:group {"className":"omega-hub-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-hub-head">' . "\n";
	$out .= '<!-- wp:heading {"level":2,"className":"omega-hub-h2"} --><h2 class="wp-block-heading omega-hub-h2">' . esc_html($title) . '</h2><!-- /wp:heading -->' . "\n";
	if ($link) {
		$out .= '<!-- wp:paragraph {"className":"omega-hub-all","fontSize":"small"} --><p class="omega-hub-all has-small-font-size"><a href="#latest">' . esc_html__('View all', 'omega-design') . '</a></p><!-- /wp:paragraph -->' . "\n";
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	return $out;
};

/** A sidebar panel. */
$omega_hub_panel = function ($title, $inner, $class = '', $link = false) {
	$heading = '<!-- wp:heading {"level":3,"className":"omega-hub-panel__title"} --><h3 class="wp-block-heading omega-hub-panel__title">' . esc_html($title) . '</h3><!-- /wp:heading -->' . "\n";
	if ($link) {
		$heading = '<!-- wp:group {"className":"omega-hub-panel__head","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} -->' . "\n"
			. '<div class="wp-block-group omega-hub-panel__head">' . "\n" . $heading
			. '<!-- wp:paragraph {"className":"omega-hub-all","fontSize":"small"} --><p class="omega-hub-all has-small-font-size"><a href="#latest">' . esc_html__('View all', 'omega-design') . '</a></p><!-- /wp:paragraph -->' . "\n"
			. '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	}
	return '<!-- wp:group {"className":"omega-hub-panel' . ($class ? ' ' . $class : '') . '","layout":{"type":"default"}} -->' . "\n"
		. '<div class="wp-block-group omega-hub-panel' . ($class ? ' ' . esc_attr($class) : '') . '">' . "\n"
		. $heading
		. $inner . "\n"
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
};

/** One topic tile: icon + label. */
$omega_hub_topic = function ($icon, $label, $n) {
	return '<!-- wp:group {"className":"omega-hub-topic omega-hub-topic--' . (int) $n . '","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->' . "\n"
		. '<div class="wp-block-group omega-hub-topic omega-hub-topic--' . (int) $n . '">' . "\n"
		. '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","style":{"dimensions":{"width":"34px"}}} /-->' . "\n"
		. '<!-- wp:paragraph {"className":"omega-hub-topic__label","fontSize":"small"} --><p class="omega-hub-topic__label has-small-font-size"><a href="#latest">' . esc_html($label) . '</a></p><!-- /wp:paragraph -->' . "\n"
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--blog-hub","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--blog-hub">

<?php // HERO ?>
<!-- wp:group {"align":"wide","className":"omega-hub-hero","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide omega-hub-hero">
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"omega-hub-hero__bg"} -->
<figure class="wp-block-image size-full omega-hub-hero__bg"><img src="<?php echo $omega_hub_img; ?>" alt="<?php esc_attr_e('A laptop on a desk overlooking the mountains', 'omega-design'); ?>"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"omega-hub-hero__text","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-hero__text">
<!-- wp:paragraph {"className":"omega-hub-eyebrow","fontSize":"small"} --><p class="omega-hub-eyebrow has-small-font-size"><?php esc_html_e('Welcome to the blog', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"className":"omega-hub-hero__title"} --><h1 class="wp-block-heading omega-hub-hero__title"><?php echo wp_kses(__('Discover Ideas, Tools and Insights <em>for a Better Tomorrow</em>', 'omega-design'), ['em' => []]); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph {"className":"omega-hub-hero__lead"} --><p class="omega-hub-hero__lead"><?php esc_html_e('In-depth articles, practical guides and the latest trends in tech, business, design, lifestyle and more.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons {"className":"omega-hub-hero__cta"} -->
<div class="wp-block-buttons omega-hub-hero__cta">
<!-- wp:button {"className":"omega-hub-btn"} --><div class="wp-block-button omega-hub-btn"><a class="wp-block-button__link wp-element-button" href="#latest"><?php esc_html_e('Explore Articles', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"omega-hub-btn omega-hub-btn--ghost"} --><div class="wp-block-button omega-hub-btn omega-hub-btn--ghost"><a class="wp-block-button__link wp-element-button" href="#featured"><?php esc_html_e('Featured Story', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"omega-hub-hero__script"} --><p class="omega-hub-hero__script"><?php echo wp_kses(__('Ideas<br>Inspiration<br>Knowledge', 'omega-design'), ['br' => []]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<?php // TOPIC TILES ?>
<!-- wp:group {"align":"wide","className":"omega-hub-topics","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide omega-hub-topics">
<?php
foreach ([
	['memory', __('Technology', 'omega-design')],
	['bar-chart', __('Business', 'omega-design')],
	['color-palette', __('Design', 'omega-design')],
	['local-cafe', __('Lifestyle', 'omega-design')],
	['web', __('WordPress', 'omega-design')],
	['school', __('Tutorials', 'omega-design')],
	['build', __('Tools', 'omega-design')],
	['star', __('Reviews', 'omega-design')],
] as $i => $topic) {
	echo $omega_hub_topic($topic[0], $topic[1], $i + 1);
}
?>
</div>
<!-- /wp:group -->

<?php // MAIN + SIDEBAR ?>
<!-- wp:columns {"align":"wide","className":"omega-hub-layout","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|l","left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns alignwide omega-hub-layout">

<!-- wp:column {"width":"70%","className":"omega-hub-main"} -->
<div class="wp-block-column omega-hub-main" style="flex-basis:70%">

<?php // FEATURED ?>
<!-- wp:group {"anchor":"featured","className":"omega-hub-section","layout":{"type":"default"}} -->
<div id="featured" class="wp-block-group omega-hub-section">
<?php echo $omega_hub_head(__('Featured Article', 'omega-design'), false); ?>
<?php echo $omega_hub_big_card(101, 0, 'omega-hub-featured'); ?>
</div>
<!-- /wp:group -->

<?php // LATEST ?>
<!-- wp:group {"anchor":"latest","className":"omega-hub-section","layout":{"type":"default"}} -->
<div id="latest" class="wp-block-group omega-hub-section">
<!-- wp:group {"className":"omega-hub-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group omega-hub-head">
<!-- wp:heading {"level":2,"className":"omega-hub-h2"} --><h2 class="wp-block-heading omega-hub-h2"><?php esc_html_e('Latest Articles', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:group {"className":"omega-hub-pillrow","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group omega-hub-pillrow">
<!-- wp:paragraph {"className":"omega-hub-pill-all"} --><p class="omega-hub-pill-all"><a href="#latest"><?php esc_html_e('All', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
<!-- wp:terms-query {"termQuery":{"perPage":5,"taxonomy":"category","order":"desc","orderBy":"count","include":[],"hideEmpty":true,"showNested":false,"inherit":false},"className":"omega-hub-pills"} -->
<div class="wp-block-terms-query omega-hub-pills"><!-- wp:term-template -->
<!-- wp:term-name {"isLink":true} /-->
<!-- /wp:term-template --></div>
<!-- /wp:terms-query -->
<!-- wp:paragraph {"className":"omega-hub-all","fontSize":"small"} --><p class="omega-hub-all has-small-font-size"><a href="#latest"><?php esc_html_e('View all', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php echo $omega_hub_query(102, 6, 1, 'omega-hub-grid'); ?>
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"omega-hub-card","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10"} /-->
<!-- wp:post-terms {"term":"category","className":"omega-hub-chip omega-hub-chip--over"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"className":"omega-hub-card__title"} /-->
<?php echo $omega_hub_meta(true); ?>
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph --><p><?php esc_html_e('New articles are on the way - check back soon.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->

<?php // POPULAR ?>
<!-- wp:group {"className":"omega-hub-section","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-section">
<?php echo $omega_hub_head(__('Popular Articles', 'omega-design')); ?>
<?php echo $omega_hub_big_card(105, 7, 'omega-hub-featured omega-hub-featured--popular'); ?>
<?php echo $omega_hub_query(103, 4, 8, 'omega-hub-grid omega-hub-grid--small'); ?>
<!-- wp:post-template {"layout":{"type":"grid","columnCount":4}} -->
<!-- wp:group {"className":"omega-hub-card omega-hub-card--small","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-card omega-hub-card--small">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10"} /-->
<!-- wp:post-terms {"term":"category","className":"omega-hub-chip omega-hub-chip--over"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"className":"omega-hub-card__title"} /-->
<?php echo $omega_hub_meta(true); ?>
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"className":"omega-hub-section","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-section">
<?php echo $omega_hub_head(__('What Our Readers Say', 'omega-design'), false); ?>
<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":true,"className":"omega-testimonial-slider omega-hub-quotes","prevLabel":"Previous testimonial","nextLabel":"Next testimonial","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-hub-quotes omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="1" data-spv="1" data-spv-tablet="1" data-spv-mobile="1" data-gap="0px" data-prev-label="Previous testimonial" data-next-label="Next testimonial" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
foreach ([
	[__('This blog has become my go-to resource for practical guides and useful tools. The content is always insightful and easy to follow.', 'omega-design'), 'Sarah K.', __('Freelancer & Blogger', 'omega-design')],
	[__('Clear, honest reviews and tutorials that actually work. I have recommended it to my whole team.', 'omega-design'), 'Daniel R.', __('Product Designer', 'omega-design')],
	[__('The weekly newsletter alone is worth it - I learn something new and useful every single time.', 'omega-design'), 'Aisha M.', __('Small Business Owner', 'omega-design')],
] as $q) :
	?>
<!-- wp:group {"className":"omega-hub-quote","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-quote">
<!-- wp:paragraph {"className":"omega-hub-quote__text"} --><p class="omega-hub-quote__text">&ldquo;<?php echo esc_html($q[0]); ?>&rdquo;</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-hub-quote__name"} --><p class="omega-hub-quote__name"><strong><?php echo esc_html($q[1]); ?></strong> <span><?php echo esc_html($q[2]); ?></span></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:omega-design/slider -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:column -->

<?php // SIDEBAR ?>
<!-- wp:column {"width":"30%","className":"omega-hub-side"} -->
<div class="wp-block-column omega-hub-side" style="flex-basis:30%">
<?php
echo $omega_hub_panel(
	__('Search Articles', 'omega-design'),
	'<!-- wp:search {"label":"' . esc_attr__('Search articles', 'omega-design') . '","showLabel":false,"placeholder":"' . esc_attr__('Search articles, tutorials, reviews…', 'omega-design') . '","buttonText":"' . esc_attr__('Search', 'omega-design') . '","buttonUseIcon":true,"className":"omega-hub-search"} /-->'
);
?>

<!-- wp:group {"className":"omega-hub-panel omega-hub-panel--news","layout":{"type":"default"}} -->
<div class="wp-block-group omega-hub-panel omega-hub-panel--news">
<!-- wp:heading {"level":3,"className":"omega-hub-panel__title"} --><h3 class="wp-block-heading omega-hub-panel__title"><?php esc_html_e('Subscribe to Our Newsletter', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('Get the latest articles, tools and resources straight to your inbox.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('Enter your email address…', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe', 'omega-design'); ?>","successMessage":"<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>">
<form class="omega-newsletter-form" novalidate>
<input type="text" name="omega_newsletter_company" class="omega-newsletter-form__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true"/>
<div class="omega-newsletter-form__row">
<input type="email" class="omega-newsletter-form__input" placeholder="<?php echo esc_attr__('Enter your email address…', 'omega-design'); ?>" name="omega_newsletter_email" required/>
<button type="submit" class="omega-newsletter-form__submit wp-element-button"><?php esc_html_e('Subscribe', 'omega-design'); ?></button>
</div>
<p class="omega-newsletter-form__message" aria-live="polite"></p>
</form>
</div>
<!-- /wp:omega-design/newsletter-form -->
</div>
<!-- /wp:group -->

<?php
echo $omega_hub_panel(
	__('Popular Categories', 'omega-design'),
	'<!-- wp:terms-query {"termQuery":{"perPage":7,"taxonomy":"category","order":"desc","orderBy":"count","include":[],"hideEmpty":true,"showNested":false,"inherit":false},"className":"omega-hub-cats"} -->' . "\n"
	. '<div class="wp-block-terms-query omega-hub-cats"><!-- wp:term-template -->' . "\n"
	. '<!-- wp:term-name {"isLink":true} /-->' . "\n\n"
	. '<!-- wp:term-count {"bracketType":"none"} /-->' . "\n"
	. '<!-- /wp:term-template --></div>' . "\n"
	. '<!-- /wp:terms-query -->',
	'',
	true
);

echo $omega_hub_panel(
	__('Trending Posts', 'omega-design'),
	$omega_hub_query(104, 5, 0, 'omega-hub-trending')
	. '<!-- wp:post-template -->' . "\n"
	. '<!-- wp:group {"className":"omega-hub-trend","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->' . "\n"
	. '<div class="wp-block-group omega-hub-trend">' . "\n"
	. '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->' . "\n"
	. '<!-- wp:group {"className":"omega-hub-trend__body","layout":{"type":"default"}} -->' . "\n"
	. '<div class="wp-block-group omega-hub-trend__body">' . "\n"
	. '<!-- wp:post-title {"level":4,"isLink":true} /-->' . "\n"
	. '<!-- wp:post-date {"format":"M j, Y","fontSize":"small"} /-->' . "\n"
	. '</div>' . "\n" . '<!-- /wp:group -->' . "\n"
	. '</div>' . "\n" . '<!-- /wp:group -->' . "\n"
	. '<!-- /wp:post-template -->' . "\n"
	. '</div>' . "\n" . '<!-- /wp:query -->',
	'omega-hub-panel--trending',
	true
);

echo $omega_hub_panel(
	__('Tags', 'omega-design'),
	'<!-- wp:tag-cloud {"smallestFontSize":"0.8125rem","largestFontSize":"0.8125rem","className":"omega-hub-tags"} /-->',
	'',
	true
);
?>
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<?php // TRUSTED BY ?>
<!-- wp:group {"align":"wide","className":"omega-hub-trusted","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide omega-hub-trusted">
<!-- wp:paragraph {"className":"omega-hub-trusted__label","fontSize":"small"} --><p class="omega-hub-trusted__label has-small-font-size"><strong><?php esc_html_e('Trusted by creators and professionals', 'omega-design'); ?></strong></p><!-- /wp:paragraph -->
<?php foreach (['WordPress', 'Google', 'Microsoft', 'envato', 'shopify', 'Adobe'] as $brand) : ?>
<!-- wp:paragraph {"className":"omega-hub-trusted__logo"} --><p class="omega-hub-trusted__logo"><?php echo esc_html($brand); ?></p><!-- /wp:paragraph -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
