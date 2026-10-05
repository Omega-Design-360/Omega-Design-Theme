<?php
/**
 * Header for PHP templates that call get_header() - Elementor's "Full Width"
 * page template, Elementor Pro Theme Builder templates and WooCommerce's
 * classic templates. The theme's own block templates (templates/*.html)
 * don't use this file.
 *
 * Renders the same header/footer template parts the block templates show,
 * so the Block/Classic header choice, Hide header/footer and an Elementor
 * Pro Theme Builder header/footer (includes/compat/elementor.php) all
 * apply here too.
 *
 * @package OmegaDesign
 */

defined('ABSPATH') || exit;

// Both parts are rendered before wp_head(), the same order WordPress's own
// block template canvas uses: blocks enqueue their script modules (mini
// cart, navigation, search...) while rendering, and the import map those
// modules need is printed in wp_head(). footer.php prints the footer.
$omega_design_header_html            = do_blocks('<!-- wp:template-part {"slug":"header","tagName":"header"} /-->');
$GLOBALS['omega_design_footer_html'] = do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->');
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<?php echo $omega_design_header_html; ?>
