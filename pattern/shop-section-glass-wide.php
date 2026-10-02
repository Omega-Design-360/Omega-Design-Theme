<?php
/**
 * Title: Shop - Glass Showcase
 * Slug: omega-design/shop-section-glass-wide
 * Categories: omega-design-shop
 * Description: A full-width 4-column grid of photo cards with a frosted-glass details panel on hover - no filters.
 * Keywords: shop, products, woocommerce, store, glass, showcase, fashion, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-glass-wide.html by pattern_helpers::shop_section(), so it
 * always matches the "Glass Showcase" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-glass-wide', 87, __('Shop All', 'omega-design'));
