<?php
/**
 * Footer for PHP templates that call get_footer() - see header.php, which
 * renders the footer template part ahead of wp_head().
 *
 * @package OmegaDesign
 */

defined('ABSPATH') || exit;

echo $GLOBALS['omega_design_footer_html'] ?? do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->');
?>
</div>
<?php wp_footer(); ?>
</body>
</html>
