<?php
/**
 * Title: Landing Page - Blog / Magazine
 * Slug: omega-design/landing-blog-magazine
 * Categories: omega-design-general
 * Description: A modern blogging-website home page - numbered trending ticker, an editorial masthead with search, a bento grid of top stories with text over the photos, live topic tiles with article counts, a latest-stories feed with author avatars and reading times beside a sticky sidebar, an editor's picks band and a gradient newsletter card. Every post section is live from your own posts.
 * Keywords: landing page, blog, blogging, magazine, news, articles, posts, editorial, publication, journal
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-bookstore.php: a small,
 * boring vocabulary of backgroundColor/textColor/fontSize-by-slug, block
 * align, and preset-token spacing, plus shared className hooks
 * (assets/css/landing-pages.css, assets/css/landing-blog-magazine.css) for
 * anything more custom - no hand-typed multi-property inline `style` JSON
 * and no Cover blocks, so the saved markup always matches what the block
 * editor itself would generate and never trips "attempt recovery".
 *
 * Every post section is a core Query Loop (the topic tiles are a Terms
 * Query): the page fills itself from the site's own content and stays
 * current as new posts are published. The loops use consecutive offsets
 * (bento 0-4, latest 5-10, editor's picks 11-13, don't-miss 14-17) so a
 * story never appears twice; the trending ticker deliberately repeats the
 * newest headlines as a quick scan.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

/** Query Loop opening comment + wrapper for a non-inherited post query. */
$omega_mag_query = function ($query_id, $per_page, $offset, $class) {
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

/** Byline: avatar + author, date, reading time. */
$omega_mag_byline = function ($with_read_time = true) {
	return '<!-- wp:group {"className":"omega-mag-byline","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->' . "\n"
		. '<div class="wp-block-group omega-mag-byline">' . "\n"
		. '<!-- wp:post-author {"avatarSize":24,"showBio":false,"fontSize":"small"} /-->' . "\n"
		. '<!-- wp:post-date {"fontSize":"small"} /-->' . "\n"
		. ($with_read_time ? '<!-- wp:post-time-to-read {"fontSize":"small"} /-->' . "\n" : '')
		. '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
};

/** Section header: small label + big title (+ optional "view all" link). */
$omega_mag_head = function ($label, $title, $link_text = '') {
	$out  = '<!-- wp:group {"className":"omega-mag-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-mag-head">' . "\n";
	$out .= '<!-- wp:group {"className":"omega-mag-head__text","layout":{"type":"default"}} -->' . "\n" . '<div class="wp-block-group omega-mag-head__text">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-mag-kicker","fontSize":"small"} --><p class="omega-mag-kicker has-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:heading {"level":2,"className":"omega-mag-title"} --><h2 class="wp-block-heading omega-mag-title">' . esc_html($title) . '</h2><!-- /wp:heading -->' . "\n";
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	if ('' !== $link_text) {
		$out .= '<!-- wp:paragraph {"className":"omega-mag-more","fontSize":"small"} --><p class="omega-mag-more has-small-font-size"><a href="#latest">' . esc_html($link_text) . '</a></p><!-- /wp:paragraph -->' . "\n";
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--blog-magazine","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--blog-magazine">

<?php // TRENDING TICKER ?>
<!-- wp:group {"align":"wide","className":"omega-mag-trending","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide omega-mag-trending">
<!-- wp:paragraph {"className":"omega-mag-trending__label","fontSize":"small"} --><p class="omega-mag-trending__label has-small-font-size"><?php esc_html_e('Trending', 'omega-design'); ?></p><!-- /wp:paragraph -->
<?php echo $omega_mag_query(91, 5, 0, 'omega-mag-trending__list'); ?>
<!-- wp:post-template -->
<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"small"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->

<?php // MASTHEAD ?>
<!-- wp:group {"align":"wide","className":"omega-mag-masthead","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group alignwide omega-mag-masthead">
<!-- wp:group {"className":"omega-mag-masthead__text","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-masthead__text">
<!-- wp:paragraph {"className":"omega-mag-kicker","fontSize":"small"} --><p class="omega-mag-kicker has-small-font-size"><?php esc_html_e('Independent stories · Updated daily', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"className":"omega-mag-masthead__title"} --><h1 class="wp-block-heading omega-mag-masthead__title"><?php echo wp_kses(__('Ideas, stories &amp; guides for <em>curious</em> minds.', 'omega-design'), ['em' => []]); ?></h1><!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:search {"label":"<?php echo esc_attr__('Search the blog', 'omega-design'); ?>","showLabel":false,"placeholder":"<?php echo esc_attr__('Search articles, topics, authors…', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Search', 'omega-design'); ?>","buttonUseIcon":true,"className":"omega-mag-search"} /-->
</div>
<!-- /wp:group -->

<?php // BENTO: 1 lead + 4 tiles ?>
<!-- wp:group {"align":"wide","className":"omega-mag-bento-wrap","layout":{"type":"default"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-mag-bento-wrap omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $omega_mag_query(92, 5, 0, 'omega-mag-bento'); ?>
<!-- wp:post-template {"layout":{"type":"grid","columnCount":4}} -->
<!-- wp:group {"className":"omega-mag-tile","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-tile">
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:group {"className":"omega-mag-tile__body","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-tile__body">
<!-- wp:post-terms {"term":"category","className":"omega-mag-chip"} /-->
<!-- wp:post-title {"level":2,"isLink":true,"className":"omega-mag-tile__title"} /-->
<?php echo $omega_mag_byline(false); ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->

<?php // TOPICS: live category tiles ?>
<!-- wp:group {"align":"wide","className":"omega-mag-section","layout":{"type":"default"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-mag-section omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php echo $omega_mag_head(__('Browse by topic', 'omega-design'), __('Find your next read', 'omega-design')); ?>
<!-- wp:terms-query {"termQuery":{"perPage":8,"taxonomy":"category","order":"desc","orderBy":"count","include":[],"hideEmpty":true,"showNested":false,"inherit":false},"className":"omega-mag-topics"} -->
<div class="wp-block-terms-query omega-mag-topics"><!-- wp:term-template -->
<!-- wp:term-name {"isLink":true} /-->

<!-- wp:term-count {"bracketType":"none"} /-->
<!-- /wp:term-template --></div>
<!-- /wp:terms-query -->
</div>
<!-- /wp:group -->

<?php // LATEST + SIDEBAR ?>
<!-- wp:group {"anchor":"latest","align":"wide","className":"omega-mag-section","layout":{"type":"default"}} -->
<div id="latest" class="wp-block-group alignwide omega-mag-section">
<!-- wp:columns {"className":"omega-mag-feed","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|xl","left":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-columns omega-mag-feed">

<!-- wp:column {"width":"68%"} -->
<div class="wp-block-column" style="flex-basis:68%">
<?php echo $omega_mag_head(__('Fresh off the press', 'omega-design'), __('Latest stories', 'omega-design')); ?>
<?php echo $omega_mag_query(94, 6, 5, 'omega-mag-latest'); ?>
<!-- wp:post-template {"layout":{"type":"grid","columnCount":2,"minimumColumnWidth":"16rem"}} -->
<!-- wp:group {"className":"omega-mag-card","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10"} /-->
<!-- wp:post-terms {"term":"category","className":"omega-mag-chip"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"className":"omega-mag-card__title"} /-->
<!-- wp:post-excerpt {"excerptLength":18} /-->
<?php echo $omega_mag_byline(true); ?>
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph --><p><?php esc_html_e('More stories are on the way - check back soon.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"32%","className":"omega-mag-sidebar"} -->
<div class="wp-block-column omega-mag-sidebar" style="flex-basis:32%">
<!-- wp:group {"className":"omega-mag-panel","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-panel">
<!-- wp:heading {"level":3,"className":"omega-mag-panel__title"} --><h3 class="wp-block-heading omega-mag-panel__title"><?php esc_html_e("Don't miss", 'omega-design'); ?></h3><!-- /wp:heading -->
<?php echo $omega_mag_query(96, 4, 14, 'omega-mag-ranked'); ?>
<!-- wp:post-template -->
<!-- wp:group {"className":"omega-mag-ranked__item","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-ranked__item">
<!-- wp:post-terms {"term":"category","className":"omega-mag-chip omega-mag-chip--plain"} /-->
<!-- wp:post-title {"level":4,"isLink":true} /-->
<!-- wp:post-time-to-read {"fontSize":"small"} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"omega-mag-panel omega-mag-panel--cta","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-panel omega-mag-panel--cta">
<!-- wp:paragraph {"className":"omega-mag-kicker","fontSize":"small"} --><p class="omega-mag-kicker has-small-font-size"><?php esc_html_e('Free, every Friday', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('The best of the week, in one email', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"omega-mag-btn"} --><div class="wp-block-button omega-mag-btn"><a class="wp-block-button__link wp-element-button" href="#newsletter"><?php esc_html_e('Subscribe', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"omega-mag-panel","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-panel">
<!-- wp:heading {"level":3,"className":"omega-mag-panel__title"} --><h3 class="wp-block-heading omega-mag-panel__title"><?php esc_html_e('Popular tags', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:tag-cloud {"smallestFontSize":"0.8125rem","largestFontSize":"0.8125rem","className":"omega-mag-tags"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // EDITOR'S PICKS ?>
<!-- wp:group {"align":"full","className":"omega-mag-picks","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-mag-picks omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide">
<?php echo $omega_mag_head(__('Hand-picked', 'omega-design'), __("Editor's picks", 'omega-design')); ?>
<?php echo $omega_mag_query(95, 3, 11, 'omega-mag-picks__grid'); ?>
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"omega-mag-tile omega-mag-tile--tall","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-tile omega-mag-tile--tall">
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:group {"className":"omega-mag-tile__body","layout":{"type":"default"}} -->
<div class="wp-block-group omega-mag-tile__body">
<!-- wp:post-terms {"term":"category","className":"omega-mag-chip"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"className":"omega-mag-tile__title"} /-->
<!-- wp:post-time-to-read {"fontSize":"small"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph --><p><?php esc_html_e("Editor's picks appear here once there are enough published posts.", 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<?php // NEWSLETTER ?>
<!-- wp:group {"anchor":"newsletter","align":"wide","className":"omega-newsletter-section omega-mag-newsletter","layout":{"type":"constrained","contentSize":"640px"},"omegaAnimation":"fade-up"} -->
<div id="newsletter" class="wp-block-group alignwide omega-newsletter-section omega-mag-newsletter omega-animate" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:paragraph {"align":"center","className":"omega-mag-kicker","fontSize":"small"} --><p class="has-text-align-center omega-mag-kicker has-small-font-size"><?php esc_html_e('The Weekly Digest', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('Stories worth your time. Once a week.', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e("Join thousands of readers getting our best articles, the week's most-read stories and a few surprises - free.", 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:omega-design/newsletter-form {"placeholder":"<?php echo esc_attr__('you@example.com', 'omega-design'); ?>","buttonText":"<?php echo esc_attr__('Subscribe free', 'omega-design'); ?>","successMessage":"<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>"} -->
<div class="wp-block-omega-design-newsletter-form omega-newsletter-form-block" data-success-message="<?php echo htmlspecialchars(__("Thanks — you're on the list!", 'omega-design'), ENT_COMPAT); ?>">
<form class="omega-newsletter-form" novalidate>
<input type="text" name="omega_newsletter_company" class="omega-newsletter-form__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true"/>
<div class="omega-newsletter-form__row">
<input type="email" class="omega-newsletter-form__input" placeholder="<?php echo esc_attr__('you@example.com', 'omega-design'); ?>" name="omega_newsletter_email" required/>
<button type="submit" class="omega-newsletter-form__submit wp-element-button"><?php esc_html_e('Subscribe free', 'omega-design'); ?></button>
</div>
<p class="omega-newsletter-form__message" aria-live="polite"></p>
</form>
</div>
<!-- /wp:omega-design/newsletter-form -->
<!-- wp:paragraph {"align":"center","className":"omega-mag-fineprint","fontSize":"small"} --><p class="has-text-align-center omega-mag-fineprint has-small-font-size"><?php esc_html_e('No spam. Unsubscribe in one click.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
