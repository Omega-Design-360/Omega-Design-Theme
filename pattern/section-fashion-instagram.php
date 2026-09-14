<?php
/**
 * Title: Section - Fashion Store: Instagram + App Promo
 * Slug: omega-design/section-fashion-instagram
 * Categories: omega-design-sections
 * Description: A 6-photo Instagram gallery next to an app-download promo card - insertable on its own, on any page.
 * Keywords: instagram, gallery, app promo, section
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_INCLUDES . '/patterns/pattern-helpers.php';

$omega_ph = omega_pattern_placeholder_url();
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|l","bottom":"var:preset|spacing|l"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--l);padding-bottom:var(--wp--preset--spacing--l)">
<!-- wp:columns -->
<div class="wp-block-columns">

<!-- wp:column {"width":"60%"} -->
<div class="wp-block-column" style="flex-basis:60%">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e('Follow Us on Instagram', 'omega-design'); ?></h3><!-- /wp:heading -->
<!-- wp:gallery {"columns":3,"linkTo":"none","className":"omega-instagram-grid"} -->
<figure class="wp-block-gallery has-nested-images columns-3 is-cropped omega-instagram-grid">
<?php for ($i = 1; $i <= 6; $i++) : ?>
<!-- wp:image {"sizeSlug":"large","className":"omega-rounded-image"} --><figure class="wp-block-image size-large omega-rounded-image"><img src="<?php echo esc_url(omega_pattern_fashion_asset(sprintf('instagram/instagram-%02d.png', $i))); ?>" alt="<?php esc_attr_e('Instagram photo', 'omega-design'); ?>"/></figure><!-- /wp:image -->
<?php endfor; ?>
</figure>
<!-- /wp:gallery -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
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
<!-- /wp:column -->

</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
