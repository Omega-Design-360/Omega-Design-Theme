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
 * Per-slide inline styles are built by OmegaDesign\blocks\content_slider
 * (includes/blocks/content_slider.php).
 *
 * @var array $attributes
 */

use OmegaDesign\blocks\content_slider;

defined('ABSPATH') || exit;

$slides = isset($attributes['slides']) && is_array($attributes['slides']) ? $attributes['slides'] : [];

if (empty($slides)) {
	return;
}

$wrapper_attributes = content_slider::wrapper_attributes($attributes);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore -- already escaped by get_block_wrapper_attributes() ?>>
<?php foreach ($slides as $slide_index => $slide) :
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
	<div class="omega-content-slider__slide" style="<?php echo esc_attr(content_slider::slide_style($slide)); ?>">
		<?php $bg_style = content_slider::bg_style($slide); ?>
		<?php if ($bg_style) : ?>
			<div class="omega-content-slider__slide-bg" style="<?php echo esc_attr($bg_style); ?>"></div>
		<?php endif; ?>
		<?php $overlay_style = content_slider::overlay_style($slide); ?>
		<?php if ($overlay_style) : ?>
			<div class="omega-content-slider__slide-overlay" style="<?php echo esc_attr($overlay_style); ?>"></div>
		<?php endif; ?>
		<div class="omega-content-slider__media">
			<?php if ($image_url) : ?>
				<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" style="<?php echo esc_attr(content_slider::image_style($slide)); ?>"<?php echo content_slider::image_loading_attr($slide_index); ?> />
			<?php endif; ?>
		</div>
		<div class="omega-content-slider__content omega-content-slider__content--<?php echo esc_attr($content_pos); ?>" style="<?php echo esc_attr(content_slider::content_style($slide)); ?>"<?php echo $animation ? ' data-slide-animation="' . esc_attr($animation) . '"' : ''; ?>>
			<?php if ($heading) : ?>
				<h2 class="omega-content-slider__heading"<?php echo content_slider::color_attr($heading_color); ?>><?php echo esc_html($heading); ?></h2>
			<?php endif; ?>
			<?php if ($body_text) : ?>
				<p class="omega-content-slider__body"<?php echo content_slider::color_attr($body_color); ?>><?php echo esc_html($body_text); ?></p>
			<?php endif; ?>
			<?php if ($button_text) : ?>
				<a class="omega-content-slider__button" href="<?php echo esc_url($button_url); ?>" style="<?php echo esc_attr(content_slider::button_style($slide)); ?>"><?php echo esc_html($button_text); ?></a>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>
