<?php
/**
 * Dynamic render for omega-design/content-slider. A pure PHP-from-
 * attributes block (no InnerBlocks, no static save() to keep in sync) -
 * $attributes['slides'] is the whole slide list, each slide a plain array
 * of scalar values (image, text, colors, sizes, borders, animation) edited
 * entirely through this block's own Inspector Controls in index.js.
 *
 * Reuses the SAME carousel engine as omega-design/slider (blocks/omega-
 * slider/view.js + style.css - both enqueued via block.json's "style"/
 * "viewScript"): that engine only ever reads generic .omega-slider /
 * data-* attributes and treats the wrapper's direct children as "the
 * slides", so it works here without any changes. The per-slide content
 * animation (data-slide-animation) is the one addition made to view.js to
 * support this block - see the "playSlideAnimation" note there.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$slides = isset($attributes['slides']) && is_array($attributes['slides']) ? $attributes['slides'] : [];

if (empty($slides)) {
	return;
}

$autoplay        = !empty($attributes['autoplay']);
$autoplay_speed  = isset($attributes['autoplaySpeed']) ? (int) $attributes['autoplaySpeed'] : 6000;
$loop            = !empty($attributes['loop']);
$show_arrows     = !empty($attributes['showArrows']);
$show_dots       = !empty($attributes['showDots']);
$effect          = isset($attributes['effect']) ? (string) $attributes['effect'] : 'slide';
$slider_height   = isset($attributes['sliderHeight']) ? (string) $attributes['sliderHeight'] : '480px';

$wrapper_attributes = get_block_wrapper_attributes([
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

/**
 * Builds one slide's inline style attribute from its own customization
 * fields - everything here is per-slide and admin-editable, so it has to
 * be inline rather than a shared CSS class. Guarded with function_exists()
 * since this render.php is include()'d fresh for every instance of the
 * block on a page - a bare function declaration would fatal error
 * ("cannot redeclare") the second time the same page renders two of these.
 */
if (!function_exists('omega_content_slider_slide_style')) {
	function omega_content_slider_slide_style($slide) {
		$bg = $slide['backgroundColor'] ?? '';
		return $bg ? 'background-color:' . esc_attr($bg) . ';' : '';
	}
}

if (!function_exists('omega_content_slider_bg_style')) {
	function omega_content_slider_bg_style($slide) {
		$url  = $slide['backgroundImageUrl'] ?? '';
		$size = $slide['backgroundImageSize'] ?? 'cover';
		if (!$url) {
			return '';
		}
		return sprintf("background-image:url('%s');background-size:%s;", esc_url_raw($url), esc_attr($size));
	}
}

if (!function_exists('omega_content_slider_overlay_style')) {
	function omega_content_slider_overlay_style($slide) {
		$color   = $slide['backgroundOverlayColor'] ?? '#000000';
		$opacity = isset($slide['backgroundOverlayOpacity']) ? max(0, min(100, (int) $slide['backgroundOverlayOpacity'])) : 0;
		if ($opacity <= 0) {
			return '';
		}
		$rgb = omega_content_slider_hex_to_rgb($color);
		return sprintf('background-color:rgba(%d,%d,%d,%s);', $rgb[0], $rgb[1], $rgb[2], round($opacity / 100, 2));
	}
}

if (!function_exists('omega_content_slider_hex_to_rgb')) {
	function omega_content_slider_hex_to_rgb($hex) {
		$hex = ltrim((string) $hex, '#');
		if (3 === strlen($hex)) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if (6 !== strlen($hex) || !ctype_xdigit($hex)) {
			return [0, 0, 0];
		}
		return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
	}
}

if (!function_exists('omega_content_slider_image_style')) {
	function omega_content_slider_image_style($slide) {
		$width       = $slide['imageWidth'] ?? '100%';
		$height      = $slide['imageHeight'] ?? '420px';
		$fit         = $slide['imageObjectFit'] ?? 'cover';
		$radius      = isset($slide['imageBorderRadius']) ? (int) $slide['imageBorderRadius'] : 0;
		$border_w    = isset($slide['imageBorderWidth']) ? (int) $slide['imageBorderWidth'] : 0;
		$border_c    = $slide['imageBorderColor'] ?? '#000000';
		$border_s    = $slide['imageBorderStyle'] ?? 'solid';

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
}

if (!function_exists('omega_content_slider_content_style')) {
	function omega_content_slider_content_style($slide) {
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
}

if (!function_exists('omega_content_slider_button_style')) {
	function omega_content_slider_button_style($slide) {
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
}
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore -- already escaped by get_block_wrapper_attributes() ?>>
<?php foreach ($slides as $slide) :
	$heading      = $slide['heading'] ?? '';
	$heading_color = $slide['headingColor'] ?? '';
	$body_text    = $slide['bodyText'] ?? '';
	$body_color   = $slide['bodyColor'] ?? '';
	$button_text  = $slide['buttonText'] ?? '';
	$button_url   = $slide['buttonUrl'] ?? '#';
	$image_url    = $slide['imageUrl'] ?? '';
	$image_alt    = $slide['imageAlt'] ?? '';
	$animation    = $slide['animation'] ?? '';
	$content_pos  = $slide['contentPosition'] ?? 'left';
	?>
	<div class="omega-content-slider__slide" style="<?php echo esc_attr(omega_content_slider_slide_style($slide)); ?>">
		<?php $bg_style = omega_content_slider_bg_style($slide); ?>
		<?php if ($bg_style) : ?>
			<div class="omega-content-slider__slide-bg" style="<?php echo esc_attr($bg_style); ?>"></div>
		<?php endif; ?>
		<?php $overlay_style = omega_content_slider_overlay_style($slide); ?>
		<?php if ($overlay_style) : ?>
			<div class="omega-content-slider__slide-overlay" style="<?php echo esc_attr($overlay_style); ?>"></div>
		<?php endif; ?>
		<div class="omega-content-slider__media">
			<?php if ($image_url) : ?>
				<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" style="<?php echo esc_attr(omega_content_slider_image_style($slide)); ?>" />
			<?php endif; ?>
		</div>
		<div class="omega-content-slider__content omega-content-slider__content--<?php echo esc_attr($content_pos); ?>" style="<?php echo esc_attr(omega_content_slider_content_style($slide)); ?>"<?php echo $animation ? ' data-slide-animation="' . esc_attr($animation) . '"' : ''; ?>>
			<?php if ($heading) : ?>
				<h2 class="omega-content-slider__heading"<?php echo $heading_color ? ' style="color:' . esc_attr($heading_color) . ';"' : ''; ?>><?php echo esc_html($heading); ?></h2>
			<?php endif; ?>
			<?php if ($body_text) : ?>
				<p class="omega-content-slider__body"<?php echo $body_color ? ' style="color:' . esc_attr($body_color) . ';"' : ''; ?>><?php echo esc_html($body_text); ?></p>
			<?php endif; ?>
			<?php if ($button_text) : ?>
				<a class="omega-content-slider__button" href="<?php echo esc_url($button_url); ?>" style="<?php echo esc_attr(omega_content_slider_button_style($slide)); ?>"><?php echo esc_html($button_text); ?></a>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>
