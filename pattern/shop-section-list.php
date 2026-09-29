<?php
/**
 * Title: Shop - Compact List
 * Slug: omega-design/shop-section-list
 * Categories: omega-design-shop
 * Description: Catalog rows with summary, rating, stock and price - ideal for large or technical ranges.
 * Keywords: shop, products, woocommerce, store, list, catalog, rows
 * Viewport Width: 1400
 *
 * Built from templates/shop-list.html by omega_pattern_shop_section(), so it
 * always matches the "Compact List" Shop Layout.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

echo omega_pattern_shop_section('shop-list', 85, __('All Products', 'omega-design'));
