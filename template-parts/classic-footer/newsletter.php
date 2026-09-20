<?php
/**
 * Omega Design - Classic Footer, Style 4: Newsletter CTA
 *
 * The CTA row only renders once both a label and a URL are set under
 * Omega Design > Settings > Footer - no assumption that any particular
 * page (a "/contact" page, a newsletter plugin, etc.) exists on this
 * site.
 *
 * @package OmegaDesign
 * @var string $nav_html
 * @var string $tagline
 * @var string $cta_label
 * @var string $cta_url
 * @var string $copyright
 */

defined('ABSPATH') || exit;

$has_cta = '' !== trim(wp_strip_all_tags($cta_label)) && '' !== trim($cta_url);
?>
<footer class="site-footer omega-classic-footer omega-classic-footer--4">
    <?php if ($has_cta) : ?>
        <div class="omega-classic-footer__cta">
            <div class="omega-classic-footer__cta-inner">
                <?php if ('' !== trim(wp_strip_all_tags($tagline))) : ?>
                    <p class="omega-classic-footer__tagline"><?php echo esc_html($tagline); ?></p>
                <?php endif; ?>
                <a class="omega-classic-footer__cta-button" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
            </div>
        </div>
    <?php endif; ?>
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
