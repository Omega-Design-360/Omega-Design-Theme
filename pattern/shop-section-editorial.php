<?php
/**
 * Title: Shop - Editorial Grid
 * Slug: omega-design/shop-section-editorial
 * Categories: omega-design-shop
 * Description: Large portrait product photos with minimal text and a fold-out filter panel.
 * Keywords: shop, products, woocommerce, store, editorial, lookbook, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-editorial.html by pattern_helpers::shop_section(), so it
 * always matches the "Editorial Grid" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-editorial', 84, __('The Collection', 'omega-design'));
