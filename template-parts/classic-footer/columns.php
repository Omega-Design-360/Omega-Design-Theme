<?php
/**
 * Omega Design - Classic Footer, Style 2: Columns
 *
 * Top-level menu items with children render as a column (the item's own
 * label becomes the column heading, its children become the column's
 * links) purely via CSS on the same wp_nav_menu() output the other
 * styles use - no special menu structure required, and a top-level item
 * with no children just renders as a single link, same as always.
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
<footer class="site-footer omega-classic-footer omega-classic-footer--2">
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
