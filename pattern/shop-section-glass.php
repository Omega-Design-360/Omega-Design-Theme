<?php
/**
 * Title: Shop - Glass Boutique
 * Slug: omega-design/shop-section-glass
 * Categories: omega-design-shop
 * Description: Fashion hero, filter sidebar (category, price, brand, size, color) and photo cards with a frosted-glass details panel on hover.
 * Keywords: shop, products, woocommerce, store, glass, boutique, fashion, filters
 * Viewport Width: 1400
 *
 * Built from templates/shop-glass.html by pattern_helpers::shop_section(), so it
 * always matches the "Glass Boutique" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-glass', 86, __('Shop All', 'omega-design'));
