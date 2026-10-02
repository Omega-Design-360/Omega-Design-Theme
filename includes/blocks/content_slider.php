<?php
/**
 * omega-design/content-slider render helpers
 *
 * Builds each slide's inline styles from its own customization fields -
 * everything here is per-slide and admin-editable, so it has to be inline
 * rather than a shared CSS class. Used by blocks/omega-content-slider/
 * render.php, which WordPress include()s fresh for every instance of the
 * block on a page (hence a class rather than functions declared in that
 * template).
 *
 * @package OmegaDesign\blocks
 */

namespace OmegaDesign\blocks;

defined('ABSPATH') || exit;

class content_slider {

    /**
     * The slider wrapper's attributes - the same generic .omega-slider /
     * data-* attributes omega-design/slider's carousel engine reads.
     */
    public static function wrapper_attributes(array $attributes) {
        $autoplay      = !empty($attributes['autoplay']);
        $autoplay_speed = isset($attributes['autoplaySpeed']) ? (int) $attributes['autoplaySpeed'] : 6000;
        $loop          = !empty($attributes['loop']);
        $show_arrows   = !empty($attributes['showArrows']);
        $show_dots     = !empty($attributes['showDots']);
        $effect        = isset($attributes['effect']) ? (string) $attributes['effect'] : 'slide';
        $slider_height = isset($attributes['sliderHeight']) ? (string) $attributes['sliderHeight'] : '480px';

        return get_block_wrapper_attributes([
            'class'                  => 'omega-slider omega-content-slider',
            'data-autoplay'          => $autoplay ? '1' : '0',
            'data-autoplay-speed'    => $autoplay_speed,
            'data-loop'              => $loop ? '1' : '0',
            'data-arrows'            => $show_arrows ? '1' : '0',
            'data-dots'              => $show_dots ? '1' : '0',
            'data-spv'               => '1',
            'data-spv-tablet'        => '1',
            'data-spv-mobile'        => '1',
            'data-gap'               => '0px',
            'data-prev-label'        => __('Previous slide', 'omega-design'),
            'data-next-label'        => __('Next slide', 'omega-design'),
            'data-dots-label'        => __('Slides', 'omega-design'),
            'data-effect'            => $effect,
            'data-thumbnails'        => '0',
            'data-progress-bar'      => '0',
            'data-peek'              => '0',
            'style'                  => '--omega-content-slider-height:' . esc_attr($slider_height) . ';',
        ]);
    }

    public static function slide_style($slide) {
        $bg = $slide['backgroundColor'] ?? '';
        return $bg ? 'background-color:' . esc_attr($bg) . ';' : '';
    }

    public static function bg_style($slide) {
        $url  = $slide['backgroundImageUrl'] ?? '';
        $size = $slide['backgroundImageSize'] ?? 'cover';
        if (!$url) {
            return '';
        }
        return sprintf("background-image:url('%s');background-size:%s;", esc_url_raw($url), esc_attr($size));
    }

    public static function overlay_style($slide) {
        $color   = $slide['backgroundOverlayColor'] ?? '#000000';
        $opacity = isset($slide['backgroundOverlayOpacity']) ? max(0, min(100, (int) $slide['backgroundOverlayOpacity'])) : 0;
        if ($opacity <= 0) {
            return '';
        }
        $rgb = self::hex_to_rgb($color);
        return sprintf('background-color:rgba(%d,%d,%d,%s);', $rgb[0], $rgb[1], $rgb[2], round($opacity / 100, 2));
    }

    public static function hex_to_rgb($hex) {
        $hex = ltrim((string) $hex, '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (6 !== strlen($hex) || !ctype_xdigit($hex)) {
            return [0, 0, 0];
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    public static function image_style($slide) {
        $width    = $slide['imageWidth'] ?? '100%';
        $height   = $slide['imageHeight'] ?? '420px';
        $fit      = $slide['imageObjectFit'] ?? 'cover';
        $radius   = isset($slide['imageBorderRadius']) ? (int) $slide['imageBorderRadius'] : 0;
        $border_w = isset($slide['imageBorderWidth']) ? (int) $slide['imageBorderWidth'] : 0;
        $border_c = $slide['imageBorderColor'] ?? '#000000';
        $border_s = $slide['imageBorderStyle'] ?? 'solid';

        $style = sprintf(
            'width:%s;height:%s;object-fit:%s;border-radius:%dpx;',
            esc_attr($width),
            esc_attr($height),
            esc_attr($fit),
            $radius
        );

        if ($border_w > 0) {
            $style .= sprintf('border:%dpx %s %s;', $border_w, esc_attr($border_s), esc_attr($border_c));
        }

        return $style;
    }

    public static function content_style($slide) {
        $self_map  = ['top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end'];
        $items_map = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];

        $v_align = $slide['contentVerticalAlign'] ?? 'center';
        $h_align = $slide['textAlign'] ?? 'left';
        if (!isset($self_map[$v_align])) {
            $v_align = 'center';
        }
        if (!isset($items_map[$h_align])) {
            $h_align = 'left';
        }

        return sprintf('align-self:%s;align-items:%s;text-align:%s;', $self_map[$v_align], $items_map[$h_align], $h_align);
    }

    public static function button_style($slide) {
        $radius = isset($slide['buttonBorderRadius']) ? (int) $slide['buttonBorderRadius'] : 6;
        $bg     = $slide['buttonBackgroundColor'] ?? '';
        $color  = $slide['buttonTextColor'] ?? '';

        $style = sprintf('border-radius:%dpx;', $radius);
        if ($bg) {
            $style .= 'background-color:' . esc_attr($bg) . ';';
        }
        if ($color) {
            $style .= 'color:' . esc_attr($color) . ';';
        }

        return $style;
    }

    /**
     * Only the first slide is visible on load; every later slide's image
     * waits until the carousel brings it near the viewport.
     */
    public static function image_loading_attr($slide_index) {
        return $slide_index > 0 ? ' loading="lazy" decoding="async"' : '';
    }

    /**
     * ' style="color:..."' for an optional text color, or ''.
     */
    public static function color_attr($color) {
        return $color ? ' style="color:' . esc_attr($color) . ';"' : '';
    }
}
