<?php
/**
 * Title: Landing Page - Law Firm
 * Slug: omega-design/landing-law-firm
 * Categories: omega-design-general
 * Description: A premium dark/gold law firm landing page - hero with a portrait and floating quote card, a trust strip, an image-based practice areas grid, an about section with an experience badge, a "Why Choose Us" grid, a numbered process, an attorney team row, a stats band, a testimonial slider, a blog/insights row, an FAQ accordion, and a closing CTA band. Every image, heading and line of copy is editable after inserting.
 * Keywords: landing page, law firm, attorney, legal, lawyer, dark
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Follows the same conventions as pattern/landing-digital-agency.php: a
 * small, boring vocabulary of backgroundColor/textColor/fontSize-by-slug,
 * block align, and preset-token spacing, plus shared className hooks
 * (assets/css/landing-pages.css, assets/css/landing-law-firm.css) for
 * anything more custom - no hand-typed multi-property inline `style` JSON
 * and no Cover blocks, so the saved markup always matches what the block
 * editor itself would generate and never trips "attempt recovery". The
 * dark/glass look (unlike every other landing page in this theme, which
 * stays on the theme's normal light palette) comes entirely from
 * landing-law-firm.css remapping this page's own preset color variables -
 * every block below still just says backgroundColor="surface" etc, same
 * as any other pattern.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

$omega_ph = pattern_helpers::placeholder_url();

/** One proof/stat item with a left divider (hero proof row, numbers band). */
$build_stat = function ($value, $label, $class = 'omega-law-stat') {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"' . esc_attr($class) . '","layout":{"type":"constrained"}} -->' . "\n<div class=\"wp-block-group " . esc_attr($class) . "\">\n";
	$out .= '<!-- wp:paragraph {"className":"omega-law-stat__value","textColor":"primary","fontSize":"x-large"} --><p class="omega-law-stat__value has-primary-color has-text-color has-x-large-font-size">' . esc_html($value) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($label) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One practice-area card: image top, an icon badge overlapping its bottom edge, title/desc/link below. */
$build_practice_card = function ($icon, $title, $desc) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-practice-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-practice-card has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:group {"className":"omega-practice-card__media","layout":{"type":"constrained"}} -->' . "\n<div class=\"wp-block-group omega-practice-card__media\">\n";
	$out .= '<!-- wp:image {"className":"omega-practice-card__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-practice-card__image"><img src="' . $omega_ph . '" alt=""/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-practice-card__icon","layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-group omega-practice-card__icon">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"primary","style":{"dimensions":{"width":"20px"}}} /-->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n";
	$out .= '<!-- wp:group {"className":"omega-practice-card__body","layout":{"type":"constrained"}} --><div class="wp-block-group omega-practice-card__body">' . "\n";
	$out .= '<!-- wp:heading {"level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-medium-font-size">' . esc_html($title) . '</h3><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size"><a href="#">' . esc_html__('Learn More →', 'omega-design') . '</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

$build_practice_row = function ($items) use ($build_practice_card) {
	$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
	foreach ($items as $item) {
		$out .= $build_practice_card($item[0], $item[1], $item[2]);
	}
	$out .= '</div><!-- /wp:columns -->' . "\n";
	return $out;
};

/** One numbered "Why Choose Us" card. */
$build_why_card = function ($number, $title, $desc, $featured = false) {
	$class = 'omega-law-why-card' . ($featured ? ' omega-law-why-card--featured' : '');
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"' . esc_attr($class) . '","layout":{"type":"constrained"}} -->' . "\n<div class=\"wp-block-group " . esc_attr($class) . "\">\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary"} --><p class="omega-strong has-primary-color has-text-color">' . esc_html($number) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:heading {"level":3,"fontSize":"large"} --><h3 class="wp-block-heading has-large-font-size">' . esc_html($title) . '</h3><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One numbered process step (no literal connector graphic - see landing-law-firm.php doc comment on why). */
$build_process_step = function ($number, $icon, $title, $desc) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-law-step","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group omega-law-step\">\n";
	$out .= '<!-- wp:group {"className":"omega-law-step__number","layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-group omega-law-step__number">' . "\n";
	$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($icon) . '","textColor":"primary","style":{"dimensions":{"width":"22px"}}} /-->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size">' . esc_html($number) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size">' . esc_html($title) . '</h4><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($desc) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One attorney card: photo, name/role, small link - inside a surface-tinted card. */
$build_team_card = function ($name, $role) use ($omega_ph) {
	$out = '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
	$out .= '<!-- wp:group {"className":"omega-law-team-card","backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-law-team-card has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:image {"className":"omega-law-team-card__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-law-team-card__image"><img src="' . $omega_ph . '" alt="' . esc_attr($name) . '"/></figure><!-- /wp:image -->' . "\n";
	$out .= '<!-- wp:group {"className":"omega-law-team-card__info","layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"center"}} -->' . "\n<div class=\"wp-block-group omega-law-team-card__info\">\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:heading {"level":4,"fontSize":"medium"} --><h4 class="wp-block-heading has-medium-font-size">' . esc_html($name) . '</h4><!-- /wp:heading -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($role) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:paragraph {"textColor":"primary"} --><p class="has-primary-color has-text-color"><a href="#">↗</a></p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:column -->\n";
	return $out;
};

/** One testimonial: a colored initials circle (no headshot needed) + quote + name/role. */
$build_testimonial = function ($initials, $quote, $name, $role) {
	$out = '<!-- wp:group {"className":"omega-card omega-law-testimonial","backgroundColor":"surface","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n";
	$out .= '<div class="wp-block-group omega-card omega-law-testimonial has-surface-background-color has-background">' . "\n";
	$out .= '<!-- wp:group {"className":"omega-law-avatar-initials","layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-group omega-law-avatar-initials">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">' . esc_html($initials) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-stars","textColor":"primary","fontSize":"small"} --><p class="omega-stars has-primary-color has-text-color has-small-font-size">★★★★★</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">"' . esc_html($quote) . '"</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size">' . esc_html($name) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($role) . '</p><!-- /wp:paragraph -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	$out .= '</div><!-- /wp:group -->' . "\n";
	return $out;
};

?>
<!-- wp:group {"className":"omega-landing omega-landing--law-firm","backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-landing omega-landing--law-firm has-background-background-color has-background">

<?php // HERO ?>
<!-- wp:group {"className":"omega-law-hero","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-law-hero omega-animate" style="padding-top:var(--wp--preset--spacing--2-xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--2-xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
<?php echo pattern_helpers::eyebrow(__('Law Today · A Brighter Tomorrow', 'omega-design')); ?>
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading"><?php esc_html_e('Your Rights. Our Commitment.', 'omega-design'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Strategic legal solutions for individuals, families and businesses - backed by experience, driven by results.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Get a Free Consultation', 'omega-design'); ?></a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('Watch Our Story', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:columns {"className":"omega-law-proof-row","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns omega-law-proof-row">
<?php
foreach ([['1,500+', __('Clients Represented', 'omega-design')], ['95%', __('Success Rate', 'omega-design')], ['24/7', __('Confidential Support', 'omega-design')]] as $stat) {
	echo $build_stat($stat[0], $stat[1], 'omega-law-proof');
}
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%">
<!-- wp:group {"className":"omega-law-hero-art","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-law-hero-art">
<!-- wp:image {"className":"omega-law-portrait","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-law-portrait"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Attorney portrait', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"className":"omega-law-floating-quote omega-glass-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-law-floating-quote omega-glass-card">
<!-- wp:paragraph {"className":"omega-law-quote-mark","textColor":"primary"} --><p class="omega-law-quote-mark has-primary-color has-text-color">“</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"medium"} --><p class="has-medium-font-size"><?php esc_html_e('Justice delivers more than verdicts. It restores futures.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size"><?php esc_html_e('Our Firm', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TRUST STRIP ?>
<!-- wp:group {"className":"omega-law-trust-strip","align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-law-trust-strip has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<?php
echo pattern_helpers::icon_row_left([
	['icon' => 'military-tech', 'title' => __('10+ Years', 'omega-design'),   'desc' => __('Legal Experience', 'omega-design')],
	['icon' => 'groups',        'title' => __('1,500+ Clients', 'omega-design'), 'desc' => __('Successfully Represented', 'omega-design')],
	['icon' => 'verified',      'title' => __('Confidential', 'omega-design'), 'desc' => __('Client-First Representation', 'omega-design')],
	['icon' => 'schedule',      'title' => __('24/7', 'omega-design'),        'desc' => __('Support When It Matters', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // PRACTICE AREAS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo pattern_helpers::eyebrow(__('Practice Areas', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Expert Legal Guidance for Life\'s Important Moments', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Practice Areas →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<?php
echo $build_practice_row([
	['family-restroom',   __('Family Law', 'omega-design'),      __('Compassionate support for what matters most.', 'omega-design')],
	['gavel',              __('Criminal Defense', 'omega-design'), __('Strong representation - your rights, our priority.', 'omega-design')],
	['corporate-fare',     __('Business Law', 'omega-design'),     __('Legal solutions built around your growth.', 'omega-design')],
]);
echo $build_practice_row([
	['personal-injury',    __('Personal Injury', 'omega-design'),  __('Fighting for the compensation you deserve.', 'omega-design')],
	['real-estate-agent',  __('Real Estate Law', 'omega-design'),  __('Secure your property - build your future.', 'omega-design')],
	['description',        __('Estate Planning', 'omega-design'),  __('Plan today. Protect tomorrow.', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->

<?php // ABOUT ?>
<!-- wp:group {"align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"width":"48%"} -->
<div class="wp-block-column" style="flex-basis:48%">
<!-- wp:group {"className":"omega-law-about-photo","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-law-about-photo">
<!-- wp:image {"className":"omega-law-about-photo__image","sizeSlug":"large"} --><figure class="wp-block-image size-large omega-law-about-photo__image"><img src="<?php echo $omega_ph; ?>" alt="<?php esc_attr_e('Our office', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<!-- wp:group {"className":"omega-law-experience-badge","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group omega-law-experience-badge">
<!-- wp:paragraph {"align":"center","textColor":"primary","fontSize":"x-large"} --><p class="has-text-align-center has-primary-color has-text-color has-x-large-font-size"><?php esc_html_e('10', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","fontSize":"x-small"} --><p class="has-text-align-center has-x-small-font-size"><?php esc_html_e('Years of Experience', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"52%"} -->
<div class="wp-block-column" style="flex-basis:52%">
<!-- wp:group {"className":"omega-glass-card omega-law-about-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-glass-card omega-law-about-card">
<?php echo pattern_helpers::eyebrow(__('About Us', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('More Than Legal Advice. A Partner for What\'s Next.', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('We combine deep legal expertise with a modern, client-first approach. We listen, we strategize, and we stand by you every step of the way.', 'omega-design'); ?></p><!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns">
<?php
$features = [
	['01', __('Client-Focused Approach', 'omega-design'), __('Your goals guide every strategy.', 'omega-design')],
	['02', __('Transparent Communication', 'omega-design'), __('Clear answers, no complexity.', 'omega-design')],
	['03', __('Results-Driven', 'omega-design'), __('Focused on outcomes that matter.', 'omega-design')],
];
foreach ($features as $f) :
	?>
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"omega-strong","textColor":"primary","fontSize":"small"} --><p class="omega-strong has-primary-color has-text-color has-small-font-size"><?php echo esc_html($f[0]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"omega-strong","fontSize":"small"} --><p class="omega-strong has-small-font-size"><?php echo esc_html($f[1]); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php echo esc_html($f[2]); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->

<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Meet Our Attorneys →', 'omega-design'); ?></a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // WHY CHOOSE US ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"650px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"align":"center","className":"omega-eyebrow","textColor":"primary","fontSize":"small"} --><p class="has-text-align-center omega-eyebrow has-primary-color has-text-color has-small-font-size"><?php esc_html_e('Why Clients Choose Us', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('A Different Kind of Legal Experience', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e('Premium legal representation should feel personal, clear and prepared.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns">
<?php
echo $build_why_card('01', __('Strategic Thinking', 'omega-design'), __('We look beyond the immediate issue to build a legal strategy around your bigger objectives.', 'omega-design'));
echo $build_why_card('02', __('Personal Attention', 'omega-design'), __('You work with experienced professionals who understand your case, priorities and concerns.', 'omega-design'), true);
echo $build_why_card('03', __('Clear Communication', 'omega-design'), __('No legal jargon when plain language will do - you always know what happens next.', 'omega-design'));
echo $build_why_card('04', __('Modern Approach', 'omega-design'), __('Technology, organization and efficient processes keep your case moving forward.', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // PROCESS ?>
<!-- wp:group {"align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"bottom"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo pattern_helpers::eyebrow(__('How It Works', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Your Path to Legal Solutions', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph --><p><?php esc_html_e('A simple, transparent process designed around your peace of mind.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|l"}}}} -->
<div class="wp-block-columns alignwide">
<?php
echo $build_process_step('01', 'forum', __('Consult', 'omega-design'), __('Share your situation with our experts.', 'omega-design'));
echo $build_process_step('02', 'assignment', __('Strategize', 'omega-design'), __('We create a tailored legal plan.', 'omega-design'));
echo $build_process_step('03', 'gavel', __('Take Action', 'omega-design'), __('We represent and guide you every step.', 'omega-design'));
echo $build_process_step('04', 'military-tech', __('Achieve Results', 'omega-design'), __('Your goals, our commitment.', 'omega-design'));
?>
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // TEAM ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"verticalAlignment":"bottom"} -->
<div class="wp-block-columns are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo pattern_helpers::eyebrow(__('Our Attorneys', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Experience You Can Trust in Your Corner', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph --><p><?php esc_html_e('A multidisciplinary team bringing legal depth, strategic thinking and genuine care to every matter.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|m"}}}} -->
<div class="wp-block-columns">
<?php
echo $build_team_card(__('Daniel Mercer', 'omega-design'), __('Managing Partner · Business Law', 'omega-design'));
echo $build_team_card(__('Amelia Hart', 'omega-design'), __('Senior Partner · Family Law', 'omega-design'));
echo $build_team_card(__('Marcus Reed', 'omega-design'), __('Partner · Criminal Defense', 'omega-design'));
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // NUMBERS BAND ?>
<!-- wp:group {"className":"omega-law-numbers","align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-law-numbers has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<?php
foreach ([['1,500+', __('Clients Represented', 'omega-design')], ['95%', __('Client Satisfaction', 'omega-design')], ['10+', __('Years of Experience', 'omega-design')], ['24/7', __('Confidential Support', 'omega-design')]] as $stat) {
	echo $build_stat($stat[0], $stat[1]);
}
?>
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<?php // TESTIMONIALS ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<?php echo pattern_helpers::eyebrow(__('Client Testimonials', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Trusted by People Like You', 'omega-design'); ?></h2><!-- /wp:heading -->

<!-- wp:omega-design/slider {"autoplay":true,"autoplaySpeed":6000,"showArrows":true,"showDots":true,"slidesPerView":3,"slidesPerViewTablet":2,"slidesPerViewMobile":1,"gap":"24px","className":"omega-testimonial-slider","prevLabel":"Previous testimonials","nextLabel":"Next testimonials","dotsLabel":"Testimonials"} -->
<div class="wp-block-omega-design-slider omega-testimonial-slider omega-slider" data-autoplay="1" data-autoplay-speed="6000" data-loop="1" data-arrows="1" data-dots="1" data-spv="3" data-spv-tablet="2" data-spv-mobile="1" data-gap="24px" data-prev-label="Previous testimonials" data-next-label="Next testimonials" data-dots-label="Testimonials" data-effect="slide" data-thumbnails="0" data-progress-bar="0" data-peek="0">
<?php
echo $build_testimonial('SM', __('Professional, knowledgeable, and truly cared about my case. I always felt informed and supported.', 'omega-design'), __('Sarah M.', 'omega-design'), __('Family Law Client', 'omega-design'));
echo $build_testimonial('JT', __('Excellent service and outstanding attention to detail. They made a difficult process much easier.', 'omega-design'), __('James T.', 'omega-design'), __('Personal Injury Client', 'omega-design'));
echo $build_testimonial('ER', __('A team you can trust. Clear communication, thoughtful advice and real support throughout.', 'omega-design'), __('Emily R.', 'omega-design'), __('Business Law Client', 'omega-design'));
?>
</div>
<!-- /wp:omega-design/slider -->

</div>
<!-- /wp:group -->

<?php // INSIGHTS / BLOG ?>
<!-- wp:group {"align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"bottom"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-bottom">
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<?php echo pattern_helpers::eyebrow(__('Legal Insights', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Knowledge That Helps You Move Forward', 'omega-design'); ?></h2><!-- /wp:heading -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom">
<!-- wp:paragraph {"align":"right"} --><p class="has-text-align-right"><a href="#"><?php esc_html_e('View All Insights →', 'omega-design'); ?></a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:query {"align":"wide","query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<div class="wp-block-query alignwide">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"omega-law-insight","backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-law-insight has-surface-background-color has-background">
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:group {"className":"omega-law-insight__body","layout":{"type":"constrained"}} -->
<div class="wp-block-group omega-law-insight__body">
<!-- wp:post-date {"textColor":"primary","fontSize":"x-small"} /-->
<!-- wp:post-title {"level":4,"isLink":true,"fontSize":"medium"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->

</div>
<!-- /wp:group -->

<?php // FAQ ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}}},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignwide omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-bottom:var(--wp--preset--spacing--xl)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|2xl"}}}} -->
<div class="wp-block-columns alignwide">

<!-- wp:column {"width":"35%"} -->
<div class="wp-block-column" style="flex-basis:35%">
<?php echo pattern_helpers::eyebrow(__('Common Questions', 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Answers Before You Ask', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('We believe legal guidance should be clear from the very first conversation.', 'omega-design'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="#"><?php esc_html_e('Ask a Question →', 'omega-design'); ?></a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"65%"} -->
<div class="wp-block-column" style="flex-basis:65%">
<!-- wp:details {"summary":"<?php echo esc_attr__('What happens during my first consultation?', 'omega-design'); ?>","showContent":true} -->
<details class="wp-block-details" open><summary><?php esc_html_e('What happens during my first consultation?', 'omega-design'); ?></summary>
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('We listen to your situation, discuss the relevant issues, answer your initial questions and outline practical next steps.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

<!-- wp:details {"summary":"<?php echo esc_attr__('How do I know which practice area I need?', 'omega-design'); ?>"} -->
<details class="wp-block-details"><summary><?php esc_html_e('How do I know which practice area I need?', 'omega-design'); ?></summary>
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('Tell us what is happening - our team can help identify the relevant legal area and direct your matter to the right attorney.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

<!-- wp:details {"summary":"<?php echo esc_attr__('Will my information remain confidential?', 'omega-design'); ?>"} -->
<details class="wp-block-details"><summary><?php esc_html_e('Will my information remain confidential?', 'omega-design'); ?></summary>
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('Confidentiality is central to the attorney-client relationship - we discuss privacy and engagement details during your consultation.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

<!-- wp:details {"summary":"<?php echo esc_attr__('How quickly can we get started?', 'omega-design'); ?>"} -->
<details class="wp-block-details"><summary><?php esc_html_e('How quickly can we get started?', 'omega-design'); ?></summary>
<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size"><?php esc_html_e('After the initial consultation, we explain the recommended next steps, timing and engagement requirements for your matter.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</details>
<!-- /wp:details -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

<?php // CTA ?>
<!-- wp:group {"className":"omega-law-cta","align":"full","backgroundColor":"secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|l","right":"var:preset|spacing|l"}}},"layout":{"type":"constrained"},"omegaAnimation":"fade-up"} -->
<div class="wp-block-group alignfull omega-law-cta has-secondary-background-color has-background omega-animate" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--l)" data-omega-animate="fade-up" data-omega-animate-duration="600" data-omega-animate-delay="0">

<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<?php echo pattern_helpers::eyebrow(__("Let's Talk", 'omega-design')); ?>
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php esc_html_e('Ready to Discuss Your Case?', 'omega-design'); ?></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><?php esc_html_e('Get the legal support you need today.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"primary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-primary-background-color has-text-color has-background wp-element-button" href="mailto:hello@example.com"><?php esc_html_e('Schedule a Free Consultation →', 'omega-design'); ?></a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<!-- wp:paragraph {"align":"right","fontSize":"small"} --><p class="has-text-align-right has-small-font-size"><?php esc_html_e('No obligation. 100% confidential.', 'omega-design'); ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
