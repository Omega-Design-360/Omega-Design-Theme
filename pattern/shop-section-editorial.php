<?php
/**
 * Title: Shop - Editorial Grid
 * Slug: omega-design/shop-section-editorial
 * Categories: omega-design-shop
 * Description: Large portrait product photos with minimal text and a fold-out filter panel.
 * Keywords: shop, products, woocommerce, store, editorial, lookbook, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-editorial.html by omega_pattern_shop_section(), so it
 * always matches the "Editorial Grid" Shop Layout.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

echo omega_pattern_shop_section('shop-editorial', 84, __('The Collection', 'omega-design'));
