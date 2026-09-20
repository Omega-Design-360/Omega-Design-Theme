<?php
/**
 * Title: Section - Fashion Store: Trust Badges
 * Slug: omega-design/section-fashion-trust-badges
 * Categories: omega-design-sections
 * Description: A row of 5 icon + title + description trust badges (Free Shipping, Easy Returns, ...) - insertable on its own, on any page.
 * Keywords: trust badges, icons, features, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'local-shipping',    'title' => __('Free Shipping', 'omega-design'),    'desc' => __('On orders over $50', 'omega-design')],
	['icon' => 'inventory-2',       'title' => __('Easy Returns', 'omega-design'),     'desc' => __('30-day hassle free', 'omega-design')],
	['icon' => 'shield-lock',       'title' => __('Secure Payment', 'omega-design'),   'desc' => __('100% secure checkout', 'omega-design')],
	['icon' => 'support-agent',     'title' => __('24/7 Support', 'omega-design'),     'desc' => __("We're always here", 'omega-design')],
	['icon' => 'workspace-premium', 'title' => __('Premium Quality', 'omega-design'),  'desc' => __('Only the best for you', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->
