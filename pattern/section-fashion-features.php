<?php
/**
 * Title: Section - Fashion Store: Feature Strip
 * Slug: omega-design/section-fashion-features
 * Categories: omega-design-sections
 * Description: A row of 4 icon + title + description feature highlights on a tinted background - insertable on its own, on any page.
 * Keywords: features, icons, strip, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';
?>
<!-- wp:group {"align":"wide","backgroundColor":"surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<?php
echo omega_pattern_icon_row_left([
	['icon' => 'eco',      'title' => __('Sustainable Fashion', 'omega-design'), 'desc' => __('Better for you, better for the planet', 'omega-design')],
	['icon' => 'diamond',  'title' => __('Premium Quality', 'omega-design'),      'desc' => __('Carefully selected materials', 'omega-design')],
	['icon' => 'favorite', 'title' => __('Styles for Everyone', 'omega-design'),  'desc' => __('Fashion for all ages', 'omega-design')],
	['icon' => 'public',   'title' => __('Worldwide Shipping', 'omega-design'),   'desc' => __('Delivering happiness globally', 'omega-design')],
]);
?>
</div>
<!-- /wp:group -->
