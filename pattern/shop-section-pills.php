<?php
/**
 * Title: Shop - Category Pills
 * Slug: omega-design/shop-section-pills
 * Categories: omega-design-shop
 * Description: A sticky row of category chips above a 4-column product grid - swipeable on phones.
 * Keywords: shop, products, woocommerce, store, categories, chips, pills, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-pills.html by pattern_helpers::shop_section(), so it
 * always matches the "Category Pills" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-pills', 82, __('Shop by Category', 'omega-design'));
