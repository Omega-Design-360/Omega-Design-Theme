<?php
/**
 * omega-design/view-toggle front-end markup. assets/js/shop-glass.js
 * delegates on .omega-view-toggle__btn[data-view] and restores the
 * visitor's last choice, so the grid button is only the initial state.
 *
 * @package OmegaDesign
 */

defined('ABSPATH') || exit;

$omega_views = [
    'grid' => [
        'label' => __('Grid view', 'omega-design'),
        'path'  => 'M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z',
    ],
    'list' => [
        'label' => __('List view', 'omega-design'),
        'path'  => 'M3 5h3v3H3zm5 0h13v3H8zM3 10.5h3v3H3zm5 0h13v3H8zM3 16h3v3H3zm5 0h13v3H8z',
    ],
];
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'omega-view-toggle', 'role' => 'group', 'aria-label' => esc_attr__('Product view', 'omega-design')]); ?>>
    <?php foreach ($omega_views as $omega_view => $omega_view_data) : ?>
        <?php $omega_is_default = 'grid' === $omega_view; ?>
        <button type="button" class="omega-view-toggle__btn<?php echo $omega_is_default ? ' is-active' : ''; ?>" data-view="<?php echo esc_attr($omega_view); ?>" aria-pressed="<?php echo $omega_is_default ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr($omega_view_data['label']); ?>"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="<?php echo esc_attr($omega_view_data['path']); ?>"/></svg></button>
    <?php endforeach; ?>
</div>
