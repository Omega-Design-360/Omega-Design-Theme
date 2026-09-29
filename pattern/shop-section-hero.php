<?php
/**
 * Title: Shop - Hero Banner
 * Slug: omega-design/shop-section-hero
 * Categories: omega-design-shop
 * Description: A full-width banner with category chips and store perks, followed by a 4-column product grid.
 * Keywords: shop, products, woocommerce, store, hero, banner, grid
 * Viewport Width: 1400
 *
 * Built from templates/shop-hero.html by omega_pattern_shop_section(), so it
 * always matches the "Hero Banner" Shop Layout.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

echo omega_pattern_shop_section('shop-hero', 83, __('Shop the Collection', 'omega-design'));
