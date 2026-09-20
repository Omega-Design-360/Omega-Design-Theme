<?php
/**
 * Shared block-markup builder functions used by the landing page patterns
 * AND the standalone per-section patterns split out from them (pattern/
 * section-fashion-*.php) - extracted here once so both can require_once
 * this single file instead of each carrying its own copy.
 *
 * @package OmegaDesign
 */

defined('ABSPATH') || exit;

if (!function_exists('omega_pattern_placeholder_url')) {
	function omega_pattern_placeholder_url() {
		return esc_url(OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');
	}
}

/**
 * URL to a real photo in assets/images/fashion-store-template-assets/ (the
 * Fashion Store landing page's own image set), e.g.
 * omega_pattern_fashion_asset('categories/category-01.png').
 */
if (!function_exists('omega_pattern_fashion_asset')) {
	function omega_pattern_fashion_asset($relative_path) {
		return esc_url(OMEGA_DESIGN_IMAGES_URI . '/fashion-store-template-assets/' . ltrim($relative_path, '/'));
	}
}

/** An "OUR MENU"-style eyebrow line above a heading. */
if (!function_exists('omega_pattern_eyebrow')) {
	function omega_pattern_eyebrow($text) {
		return '<!-- wp:paragraph {"className":"omega-eyebrow","textColor":"primary","fontSize":"small"} -->'
			. '<p class="omega-eyebrow has-primary-color has-text-color has-small-font-size">' . esc_html($text) . '</p>'
			. '<!-- /wp:paragraph -->' . "\n";
	}
}

/** Icon + title + description column, icon left of text (trust badges, feature strips). */
if (!function_exists('omega_pattern_icon_row_left')) {
	function omega_pattern_icon_row_left($items) {
		$out = '<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|m","left":"var:preset|spacing|m"}}}} -->' . "\n<div class=\"wp-block-columns alignwide\">\n";
		foreach ($items as $item) {
			$out .= '<!-- wp:column -->' . "\n<div class=\"wp-block-column\">\n";
			$out .= '<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|s"}}} -->' . "\n<div class=\"wp-block-group\">\n";
			$out .= '<!-- wp:icon {"icon":"omega-icons/' . esc_attr($item['icon']) . '","textColor":"primary","style":{"dimensions":{"width":"32px"}}} /-->' . "\n";
			$out .= '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group">' . "\n";
			$out .= '<!-- wp:paragraph {"className":"omega-strong","fontSize":"medium"} --><p class="omega-strong has-medium-font-size">' . esc_html($item['title']) . '</p><!-- /wp:paragraph -->' . "\n";
			$out .= '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($item['desc']) . '</p><!-- /wp:paragraph -->' . "\n";
			$out .= '</div><!-- /wp:group -->' . "\n</div><!-- /wp:group -->\n</div><!-- /wp:column -->\n";
		}
		$out .= '</div><!-- /wp:columns -->' . "\n";
		return $out;
	}
}

/** A photo-with-overlay-text section - replaces a hand-authored Cover block. $badge_html, when given, is inserted as a sibling of the background image so it can be absolutely positioned over a corner. $image_url, when given, replaces the placeholder background photo. */
if (!function_exists('omega_pattern_photo_card')) {
	function omega_pattern_photo_card($size_class, $content_html, $badge_html = '', $image_url = '') {
		$bg_url = $image_url ? esc_url($image_url) : omega_pattern_placeholder_url();
		$out = '<!-- wp:group {"className":"omega-photo-card ' . esc_attr($size_class) . '","layout":{"type":"constrained"}} -->' . "\n";
		$out .= '<div class="wp-block-group omega-photo-card ' . esc_attr($size_class) . '">' . "\n";
		$out .= '<!-- wp:image {"className":"omega-photo-card__bg","sizeSlug":"full"} --><figure class="wp-block-image size-full omega-photo-card__bg"><img src="' . $bg_url . '" alt=""/></figure><!-- /wp:image -->' . "\n";
		$out .= $badge_html;
		$out .= '<!-- wp:group {"className":"omega-photo-card__content","layout":{"type":"constrained"}} -->' . "\n";
		$out .= '<div class="wp-block-group omega-photo-card__content">' . "\n" . $content_html . '</div><!-- /wp:group -->' . "\n";
		$out .= '</div><!-- /wp:group -->' . "\n";
		return $out;
	}
}

/**
 * A wide, short promo banner: a solid-color text panel (eyebrow-style
 * offer line + heading + body copy + button) beside a photo that fills the
 * rest of the row - e.g. the "Women's Collection / Up to 40% Off / Shop
 * Women" banners on the Fashion Store landing page.
 *
 * Deliberately real block markup (heading/paragraph/button) rather than
 * text flattened into the photo itself: an earlier version of this banner
 * used a single wide screenshot with the copy baked into the image and
 * only the button as separate markup, sized as a TALL portrait card. That
 * mismatch (a landscape image forced into a portrait box) cropped most of
 * the photo out. Keeping the text as text means the banner's shape can
 * change without re-cutting an image, and the copy stays editable/
 * accessible like every other block on the page.
 */
if (!function_exists('omega_pattern_split_promo')) {
	function omega_pattern_split_promo($args) {
		$bg          = $args['bg'];
		$offer_color = $args['offer_color'];
		$eyebrow     = $args['eyebrow'] ?? '';
		$title       = $args['title'];
		$offer       = $args['offer'] ?? '';
		$body        = $args['body'] ?? '';
		$cta         = $args['cta'];
		$image_url   = $args['image_url'];
		$image_alt   = $args['image_alt'] ?? '';

		$eyebrow_html = $eyebrow
			? '<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.05em"}},"fontSize":"x-small"} --><p class="has-x-small-font-size" style="font-weight:600;text-transform:uppercase;letter-spacing:0.05em">' . esc_html($eyebrow) . '</p><!-- /wp:paragraph -->'
			: '';

		$offer_html = $offer
			? '<!-- wp:paragraph {"style":{"color":{"text":"' . esc_attr($offer_color) . '"},"typography":{"fontWeight":"700"}},"fontSize":"medium"} --><p class="has-medium-font-size" style="color:' . esc_attr($offer_color) . ';font-weight:700">' . esc_html($offer) . '</p><!-- /wp:paragraph -->'
			: '';

		return '<!-- wp:columns {"className":"omega-split-promo","style":{"border":{"radius":"12px"}}} -->'
			. '<div class="wp-block-columns omega-split-promo" style="border-radius:12px">'
				. '<!-- wp:column {"verticalAlignment":"center","width":"55%","style":{"color":{"background":"' . esc_attr($bg) . '"},"spacing":{"padding":{"top":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|m","right":"var:preset|spacing|m"}}}} -->'
				. '<div class="wp-block-column is-vertically-aligned-center has-background" style="background-color:' . esc_attr($bg) . ';padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--m);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--m);flex-basis:55%">'
					. $eyebrow_html
					. '<!-- wp:heading {"level":3,"style":{"spacing":{"margin":{"top":"0","bottom":"0.25rem"}}}} --><h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.25rem">' . esc_html($title) . '</h3><!-- /wp:heading -->'
					. $offer_html
					. ($body ? '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html($body) . '</p><!-- /wp:paragraph -->' : '')
					. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"secondary","textColor":"button-text"} --><div class="wp-block-button"><a class="wp-block-button__link has-button-text-color has-secondary-background-color has-text-color has-background wp-element-button" href="#">' . esc_html($cta) . ' &rarr;</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
				. '</div>'
				. '<!-- /wp:column -->'
				. '<!-- wp:column {"width":"45%"} -->'
				. '<div class="wp-block-column" style="flex-basis:45%">'
					. '<!-- wp:image {"sizeSlug":"large","className":"omega-split-promo__image"} --><figure class="wp-block-image size-large omega-split-promo__image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '"/></figure><!-- /wp:image -->'
				. '</div>'
				. '<!-- /wp:column -->'
			. '</div>'
			. '<!-- /wp:columns -->';
	}
}

/** Small circular badge overlapping the top-right corner of a photo card (built via omega_pattern_photo_card()'s $badge_html param). */
if (!function_exists('omega_pattern_corner_badge')) {
	function omega_pattern_corner_badge($class, $eyebrow_text, $main_text) {
		return '<!-- wp:group {"className":"' . esc_attr($class) . '","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->' . "\n"
			. '<div class="wp-block-group ' . esc_attr($class) . '">' . "\n"
			. '<!-- wp:paragraph {"align":"center","className":"omega-eyebrow","textColor":"background","fontSize":"x-small"} --><p class="has-text-align-center omega-eyebrow has-background-color has-text-color has-x-small-font-size">' . esc_html($eyebrow_text) . '</p><!-- /wp:paragraph -->' . "\n"
			. '<!-- wp:paragraph {"align":"center","textColor":"background","fontSize":"medium"} --><p class="has-text-align-center has-background-color has-text-color has-medium-font-size">' . esc_html($main_text) . '</p><!-- /wp:paragraph -->' . "\n"
			. '</div>' . "\n<!-- /wp:group -->\n";
	}
}

/** One WooCommerce Product Collection block (used for each Trending Products tab). */
if (!function_exists('omega_pattern_product_collection')) {
	function omega_pattern_product_collection($query_id, $order_by, $on_sale = false) {
		$query = [
			'perPage' => 8, 'pages' => 0, 'offset' => 0, 'postType' => 'product',
			'order' => 'desc', 'orderBy' => $order_by, 'search' => '', 'exclude' => [],
			'inherit' => false, 'taxQuery' => [], 'isProductCollectionBlock' => true,
			'woocommerceOnSale' => $on_sale, 'woocommerceStockStatus' => ['instock', 'outofstock', 'onbackorder'],
			'woocommerceAttributes' => [], 'woocommerceHandPickedProducts' => [],
		];
		$attrs = [
			'queryId' => $query_id, 'query' => $query, 'tagName' => 'div',
			'dimensions' => ['widthType' => 'fill', 'fixedWidth' => ''],
			// shrinkColumns is what actually lets a flex item shrink below
			// its content's own intrinsic width (browsers default flex
			// items to min-width:auto, not 0) - without it, "columns" is
			// just a label and the row silently wraps to fewer, wider items
			// than requested. archive-product.html's own product grid
			// already sets this for the same reason.
			'displayLayout' => ['type' => 'flex', 'columns' => 4, 'shrinkColumns' => true],
			'queryContextIncludes' => ['collection'],
		];
		// Same .omega-product-card treatment (surface background, rounded
		// corners, hover lift, compact Add to Cart button) as the real Shop
		// archive's cards (templates/archive-product.html) - reusing that
		// existing class/CSS instead of a second card style just for tabs.
		$inner = '<!-- wp:woocommerce/product-template -->' . "\n"
			. '<!-- wp:group {"className":"omega-product-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|s","bottom":"var:preset|spacing|m","left":"var:preset|spacing|s","right":"var:preset|spacing|s"},"blockGap":"var:preset|spacing|xs"},"border":{"radius":"12px"},"shadow":"var:preset|shadow|soft"},"backgroundColor":"surface","layout":{"type":"constrained"}} -->' . "\n"
			. '<div class="wp-block-group omega-product-card has-surface-background-color has-background" style="border-radius:12px;box-shadow:var(--wp--preset--shadow--soft);padding-top:var(--wp--preset--spacing--s);padding-right:var(--wp--preset--spacing--s);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--s)">' . "\n"
			. '<!-- wp:woocommerce/product-image {"showSaleBadge":true,"isDescendentOfQueryLoop":true,"aspectRatio":"3/4"} -->' . "\n"
			. '<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->' . "\n"
			. '<!-- /wp:woocommerce/product-image -->' . "\n\n"
			. '<!-- wp:post-title {"textAlign":"center","level":3,"isLink":true,"fontSize":"medium"} /-->' . "\n\n"
			. '<!-- wp:woocommerce/product-rating {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->' . "\n\n"
			. '<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center","fontSize":"medium"} /-->' . "\n\n"
			. '<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true} /-->' . "\n"
			. '</div>' . "\n"
			. '<!-- /wp:group -->' . "\n"
			. '<!-- /wp:woocommerce/product-template -->';
		return '<!-- wp:woocommerce/product-collection ' . wp_json_encode($attrs) . ' -->' . "\n"
			. '<div class="wp-block-woocommerce-product-collection">' . "\n" . $inner . "\n</div>\n"
			. '<!-- /wp:woocommerce/product-collection -->';
	}
}
