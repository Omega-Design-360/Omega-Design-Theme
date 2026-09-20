<?php
/**
 * Omega Design - Classic Footer, Style 5: Bold
 *
 * Same columns layout as Style 2, with the dark/accent CSS variant
 * (.omega-classic-footer--5) in assets/css/classic-footer.css.
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
<footer class="site-footer omega-classic-footer omega-classic-footer--5">
    <div class="omega-classic-footer__inner">
        <div class="omega-classic-footer__brand">
            <a class="omega-classic-footer__title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
            <?php if ('' !== trim(wp_strip_all_tags($tagline))) : ?>
                <p class="omega-classic-footer__tagline"><?php echo esc_html($tagline); ?></p>
            <?php endif; ?>
        </div>
        <?php if ('' !== $nav_html) : ?>
            <nav class="omega-classic-footer__nav omega-classic-footer__nav--columns" aria-label="<?php esc_attr_e('Footer', 'omega-design'); ?>">
                <?php echo $nav_html; ?>
            </nav>
        <?php endif; ?>
    </div>
    <div class="omega-classic-footer__bottom">
        <p class="omega-classic-footer__copyright"><?php echo wp_kses_post($copyright); ?></p>
    </div>
</footer>
