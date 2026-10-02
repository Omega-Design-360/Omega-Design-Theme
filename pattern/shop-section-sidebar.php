<?php
/**
 * Title: Shop - Sidebar Filters
 * Slug: omega-design/shop-section-sidebar
 * Categories: omega-design-shop
 * Description: A product grid with a filter sidebar (category, price, rating, availability). On phones the filters open from a Filters button.
 * Keywords: shop, products, woocommerce, store, filters, sidebar, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-sidebar.html by pattern_helpers::shop_section(), so it
 * always matches the "Sidebar Filters" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-sidebar', 81, __('Shop All Products', 'omega-design'));
