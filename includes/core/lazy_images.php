<?php
/**
 * Lazy-loading for images WordPress leaves alone.
 *
 * WordPress core only adds loading="lazy" to images that carry both width
 * and height attributes (wp_get_loading_optimization_attributes()). Most of
 * this theme's own images - pattern photos, slider slides, shop heroes -
 * are plain <img src alt> block markup with neither, so every one of them
 * was downloaded up front on page load, however far down the page.
 *
 * This lazy-loads those images too, except the first few on the page
 * (EAGER_COUNT), which are likely above the fold - a hero image must never
 * wait. It only acts on the final 'template' pass of
 * wp_filter_content_tags(), which sees the whole rendered page once, in
 * document order, so the count reflects real page position. Images that
 * already have a loading attribute, or width+height (WordPress's own
 * logic handles those), are never touched.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class lazy_images {

    use singleton;

    /** Dimensionless images at the top of the page left to load normally. */
    const EAGER_COUNT = 2;

    /** Dimensionless images seen so far on this page. */
    private $seen = 0;

    private function __construct() {
        add_filter('wp_content_img_tag', [$this, 'maybe_lazy_load'], 10, 2);
        add_filter('render_block_omega-design/slider', [$this, 'keep_slider_images_eager']);
    }

    /**
     * omega-design/slider's "cards" transition measures the target slide's
     * image (getBoundingClientRect) to animate toward it - a lazy image
     * that hasn't loaded yet and has no width/height has no size there, so
     * those stay eager. Images with dimensions are left to WordPress.
     */
    public function keep_slider_images_eager($block_content) {
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
            if (self::has_loading_attr($m[0]) || self::has_dimensions($m[0])) {
                return $m[0];
            }
            return preg_replace('/^<img\b/i', '<img loading="eager"', $m[0], 1);
        }, $block_content);
    }

    public function maybe_lazy_load($image, $context) {
        if ('template' !== $context || self::has_loading_attr($image) || self::has_dimensions($image)) {
            return $image;
        }

        $this->seen++;
        if ($this->seen <= (int) apply_filters('omega_design_eager_image_count', self::EAGER_COUNT)) {
            return $image;
        }

        return self::add_lazy_attrs($image);
    }

    /**
     * Adds loading="lazy" decoding="async" to every <img> in $html that has
     * no loading attribute of its own - for markup that's hidden until a
     * user action (mega menu panels, later carousel slides).
     */
    public static function add_lazy_attrs($html) {
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
            if (self::has_loading_attr($m[0])) {
                return $m[0];
            }
            $attrs = ' loading="lazy"' . (preg_match('/\sdecoding=/i', $m[0]) ? '' : ' decoding="async"');
            return preg_replace('/^<img\b/i', '<img' . $attrs, $m[0], 1);
        }, $html);
    }

    private static function has_loading_attr($image) {
        return (bool) preg_match('/\sloading=/i', $image);
    }

    private static function has_dimensions($image) {
        return preg_match('/\swidth=/i', $image) && preg_match('/\sheight=/i', $image);
    }
}
