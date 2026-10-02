<?php
/**
 * Backwards-compatible global functions
 *
 * The theme's own code uses the classes these forward to. These wrappers
 * keep the old global function names working for child themes, custom
 * patterns or snippets written against earlier versions of the theme.
 * Each is guarded with function_exists() so a child theme can still
 * provide its own version.
 *
 * @package OmegaDesign
 */

use OmegaDesign\blocks\content_slider;
use OmegaDesign\core\asset_urls;
use OmegaDesign\core\core;
use OmegaDesign\core\uploads;
use OmegaDesign\patterns\pattern_helpers;

defined('ABSPATH') || exit;

/* ── Uploads / assets / logging (were in functions.php) ─────────── */

if (!function_exists('omega_design_create_upload_directories')) {
    function omega_design_create_upload_directories() {
        uploads::create_directories();
    }
}

if (!function_exists('omega_design_get_upload_dir')) {
    function omega_design_get_upload_dir($subdir = '') {
        return uploads::get_dir($subdir);
    }
}

if (!function_exists('omega_design_get_upload_url')) {
    function omega_design_get_upload_url($subdir = '') {
        return uploads::get_url($subdir);
    }
}

if (!function_exists('omega_design_asset_url')) {
    function omega_design_asset_url($path, $type = 'css') {
        return asset_urls::get($path, $type);
    }
}

if (!function_exists('omega_design_versioned_asset_url')) {
    function omega_design_versioned_asset_url($relative_path) {
        return asset_urls::versioned($relative_path);
    }
}

if (!function_exists('omega_design_save_to_uploads')) {
    function omega_design_save_to_uploads($file_data, $filename, $subdir = '') {
        return uploads::save($file_data, $filename, $subdir);
    }
}

if (!function_exists('omega_design_delete_from_uploads')) {
    function omega_design_delete_from_uploads($filename, $subdir = '') {
        return uploads::delete($filename, $subdir);
    }
}

if (!function_exists('omega_design_get_from_uploads')) {
    function omega_design_get_from_uploads($filename, $subdir = '') {
        return uploads::get($filename, $subdir);
    }
}

if (!function_exists('omega_design_log')) {
    function omega_design_log($data, $type = 'debug') {
        uploads::log($data, $type);
    }
}

if (!function_exists('omega_design_debug')) {
    function omega_design_debug($data) {
        uploads::log($data, 'debug');
    }
}

if (!function_exists('omega_design_initialize')) {
    function omega_design_initialize() {
        return core::get_instance()->init();
    }
}

if (!function_exists('omega_design_check_wp_compatibility')) {
    function omega_design_check_wp_compatibility() {
        return core::meets_wp_requirement();
    }
}

if (!function_exists('omega_design_check_php_compatibility')) {
    function omega_design_check_php_compatibility() {
        return core::meets_php_requirement();
    }
}

/* ── Pattern markup builders (were includes/patterns/pattern-helpers.php) ── */

if (!function_exists('omega_pattern_placeholder_url')) {
    function omega_pattern_placeholder_url() {
        return pattern_helpers::placeholder_url();
    }
}

if (!function_exists('omega_pattern_fashion_asset')) {
    function omega_pattern_fashion_asset($relative_path) {
        return pattern_helpers::fashion_asset($relative_path);
    }
}

if (!function_exists('omega_pattern_eyebrow')) {
    function omega_pattern_eyebrow($text) {
        return pattern_helpers::eyebrow($text);
    }
}

if (!function_exists('omega_pattern_icon_row_left')) {
    function omega_pattern_icon_row_left($items) {
        return pattern_helpers::icon_row_left($items);
    }
}

if (!function_exists('omega_pattern_photo_card')) {
    function omega_pattern_photo_card($size_class, $content_html, $badge_html = '', $image_url = '') {
        return pattern_helpers::photo_card($size_class, $content_html, $badge_html, $image_url);
    }
}

if (!function_exists('omega_pattern_split_promo')) {
    function omega_pattern_split_promo($args) {
        return pattern_helpers::split_promo($args);
    }
}

if (!function_exists('omega_pattern_corner_badge')) {
    function omega_pattern_corner_badge($class, $eyebrow_text, $main_text) {
        return pattern_helpers::corner_badge($class, $eyebrow_text, $main_text);
    }
}

if (!function_exists('omega_pattern_shop_section')) {
    function omega_pattern_shop_section($layout, $query_id, $heading) {
        return pattern_helpers::shop_section($layout, $query_id, $heading);
    }
}

if (!function_exists('omega_pattern_product_collection')) {
    function omega_pattern_product_collection($query_id, $order_by, $on_sale = false) {
        return pattern_helpers::product_collection($query_id, $order_by, $on_sale);
    }
}

/* ── Content slider style builders (were in blocks/omega-content-slider/render.php) ── */

if (!function_exists('omega_content_slider_slide_style')) {
    function omega_content_slider_slide_style($slide) {
        return content_slider::slide_style($slide);
    }
}

if (!function_exists('omega_content_slider_bg_style')) {
    function omega_content_slider_bg_style($slide) {
        return content_slider::bg_style($slide);
    }
}

if (!function_exists('omega_content_slider_overlay_style')) {
    function omega_content_slider_overlay_style($slide) {
        return content_slider::overlay_style($slide);
    }
}

if (!function_exists('omega_content_slider_hex_to_rgb')) {
    function omega_content_slider_hex_to_rgb($hex) {
        return content_slider::hex_to_rgb($hex);
    }
}

if (!function_exists('omega_content_slider_image_style')) {
    function omega_content_slider_image_style($slide) {
        return content_slider::image_style($slide);
    }
}

if (!function_exists('omega_content_slider_content_style')) {
    function omega_content_slider_content_style($slide) {
        return content_slider::content_style($slide);
    }
}

if (!function_exists('omega_content_slider_button_style')) {
    function omega_content_slider_button_style($slide) {
        return content_slider::button_style($slide);
    }
}
