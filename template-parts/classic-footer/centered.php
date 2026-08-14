<?php
/**
 * Omega Design - Classic Footer, Style 3: Centered
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
<footer class="site-footer omega-classic-footer omega-classic-footer--3">
    <div class="omega-classic-footer__inner">
        <a class="omega-classic-footer__title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <?php if ('' !== trim(wp_strip_all_tags($tagline))) : ?>
            <p class="omega-classic-footer__tagline"><?php echo esc_html($tagline); ?></p>
        <?php endif; ?>
        <?php if ('' !== $nav_html) : ?>
            <nav class="omega-classic-footer__nav omega-classic-footer__nav--inline" aria-label="<?php esc_attr_e('Footer', 'omega-design'); ?>">
                <?php echo $nav_html; ?>
            </nav>
        <?php endif; ?>
        <p class="omega-classic-footer__copyright"><?php echo wp_kses_post($copyright); ?></p>
    </div>
</footer>
