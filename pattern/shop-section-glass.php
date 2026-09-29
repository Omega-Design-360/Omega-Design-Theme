<?php
/**
 * Title: Shop - Glass Boutique
 * Slug: omega-design/shop-section-glass
 * Categories: omega-design-shop
 * Description: Fashion hero, filter sidebar (category, price, brand, size, color) and photo cards with a frosted-glass details panel on hover.
 * Keywords: shop, products, woocommerce, store, glass, boutique, fashion, filters
 * Viewport Width: 1400
 *
 * Built from templates/shop-glass.html by omega_pattern_shop_section(), so it
 * always matches the "Glass Boutique" Shop Layout.
 */

defined('ABSPATH') || exit;

require_once OMEGA_DESIGN_DIR . '/includes/patterns/pattern-helpers.php';

echo omega_pattern_shop_section('shop-glass', 86, __('Shop All', 'omega-design'));
