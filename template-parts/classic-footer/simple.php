<?php
/**
 * Omega Design - Classic Footer, Style 1: Simple
 *
 * Included by includes/customizer/classic_footer.php with these already
 * built: $nav_html (rendered wp_nav_menu() for the menu chosen under
 * Omega Design > Settings > Footer), $copyright (HTML, {year} already
 * replaced). Edit this file directly to hand-customize this style - the
 * CSS variant class (.omega-classic-footer--1) lives in
 * assets/css/classic-footer.css.
 *
 * @package OmegaDesign
 * @var string $nav_html
 * @var string $tagline
 * @var string $cta_label
 * @var string $cta_url
 * @var string $copyright
 */

defined('ABSPATH') || exit;
?>
<footer class="site-footer omega-classic-footer omega-classic-footer--1">
    <div class="omega-classic-footer__inner">
        <a class="omega-classic-footer__title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <?php if ('' !== $nav_html) : ?>
            <nav class="omega-classic-footer__nav omega-classic-footer__nav--inline" aria-label="<?php esc_attr_e('Footer', 'omega-design'); ?>">
                <?php echo $nav_html; ?>
            </nav>
        <?php endif; ?>
        <p class="omega-classic-footer__copyright"><?php echo wp_kses_post($copyright); ?></p>
    </div>
</footer>
