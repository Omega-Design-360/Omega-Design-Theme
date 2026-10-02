<?php
/**
 * omega-design/product-layout front-end markup - the whole single product
 * page, built by product_page::render_current_layout() in the layout
 * chosen under Omega Design > Settings > Product Page.
 *
 * @package OmegaDesign
 */

defined('ABSPATH') || exit;

echo \OmegaDesign\customizer\product_page::get_instance()->render_current_layout();
