<?php
/**
 * Title: Shop - Compact List
 * Slug: omega-design/shop-section-list
 * Categories: omega-design-shop
 * Description: Catalog rows with summary, rating, stock and price - ideal for large or technical ranges.
 * Keywords: shop, products, woocommerce, store, list, catalog, rows
 * Viewport Width: 1400
 *
 * Built from templates/shop-list.html by pattern_helpers::shop_section(), so it
 * always matches the "Compact List" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-list', 85, __('All Products', 'omega-design'));
