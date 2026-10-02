<?php
/**
 * Title: Shop - Hero Banner
 * Slug: omega-design/shop-section-hero
 * Categories: omega-design-shop
 * Description: A full-width banner with category chips and store perks, followed by a 4-column product grid.
 * Keywords: shop, products, woocommerce, store, hero, banner, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-hero.html by pattern_helpers::shop_section(), so it
 * always matches the "Hero Banner" Shop Layout.
 */

defined('ABSPATH') || exit;

use OmegaDesign\patterns\pattern_helpers;

echo pattern_helpers::shop_section('shop-hero', 83, __('Shop the Collection', 'omega-design'));
