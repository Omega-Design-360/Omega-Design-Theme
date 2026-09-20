<?php
/**
 * Omega Design - Classic Header, Style 3: Split
 *
 * Included by includes/customizer/classic_header.php with $nav_html
 * already built (the rendered wp_nav_menu() output for the menu chosen
 * under Omega Design > Settings > Header Navigation). Edit this file
 * directly to hand-customize this specific style's markup - the CSS
 * variant class (.omega-classic-header--3) lives in
 * assets/css/classic-header.css.
 *
 * @package OmegaDesign
 * @var string $nav_html
 * @var string $brand_html
 * @var bool   $sticky
 * @var string $wc_icons_html
 */

defined('ABSPATH') || exit;
?>
<header class="site-header omega-classic-header omega-classic-header--3<?php echo $sticky ? ' omega-classic-header--sticky' : ''; ?>">
    <div class="omega-classic-header__inner">
        <?php echo $brand_html; ?>
        <?php if ('' !== $nav_html || '' !== $wc_icons_html) : ?>
            <div class="omega-classic-header__actions">
                <?php if ('' !== $nav_html) : ?>
                    <nav id="omega-classic-nav" class="omega-classic-header__nav" aria-label="<?php esc_attr_e('Primary', 'omega-design'); ?>">
                        <?php echo $nav_html; ?>
                    </nav>
                <?php endif; ?>
                <?php echo $wc_icons_html; ?>
                <?php if ('' !== $nav_html) : ?>
                    <button class="omega-classic-header__toggle" type="button" aria-expanded="false" aria-controls="omega-classic-nav" aria-label="<?php esc_attr_e('Menu', 'omega-design'); ?>">
                        <span></span><span></span><span></span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
<?php if ($sticky) : ?>
    <div class="omega-classic-header__spacer" aria-hidden="true"></div>
<?php endif; ?>
