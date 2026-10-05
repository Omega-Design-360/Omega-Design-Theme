<?php
/**
 * Real Estate landing page ("Arab Real Estates") - the shared layout behind
 * pattern/landing-real-estate.php (English), landing-real-estate-ar.php
 * (Arabic) and landing-real-estate-bilingual.php (both). Each pattern file
 * sets $re_lang ('en' or 'ar') and $re_text (its copy, keyed like the
 * English table) and includes this file, which echoes the page.
 *
 * Core blocks only (plus the theme's Newsletter block), styled through
 * className hooks in assets/css/landing-real-estate.css - no inline style
 * JSON and no Custom HTML, so the saved markup matches what the block
 * editor generates. Images come from assets/images/real-estate-template/;
 * a file that isn't there yet falls back to the theme placeholder.
 *
 * The page carries its own header and footer, and the
 * "omega-landing--standalone" class: a page using it gets the "Landing
 * Page (No Header/Footer)" template automatically (hooks.php).
 *
 * @package OmegaDesign
 * @var string $re_lang
 * @var array  $re_text
 */

defined('ABSPATH') || exit;

/** This language's copy for $key. */
$t = function ($key) use ($re_text) {
	return $re_text[$key] ?? '';
};

/* ── Block markup helpers ─────────────────────────────────────────── */

/** URL of one of this template's photos, or the placeholder until it exists. */
$re_img = function ($file) {
	$path = OMEGA_DESIGN_ASSETS . '/images/real-estate-template/' . $file;
	return esc_url(file_exists($path) ? OMEGA_DESIGN_IMAGES_URI . '/real-estate-template/' . $file : OMEGA_DESIGN_IMAGES_URI . '/placeholder.svg');
};

/** The block comment's attribute JSON (leading space included), or ''. */
$re_json = function (array $attrs) {
	return $attrs ? ' ' . wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
};

/** The class list core saves for these attributes, after $base. */
$re_classes = function ($base, array $attrs) {
	$classes = $base ? [$base] : [];
	if (!empty($attrs['align'])) {
		$classes[] = 'align' . $attrs['align'];
	}
	if (!empty($attrs['className'])) {
		$classes[] = $attrs['className'];
	}
	if (!empty($attrs['textColor'])) {
		$classes[] = 'has-' . $attrs['textColor'] . '-color';
		$classes[] = 'has-text-color';
	}
	if (!empty($attrs['backgroundColor'])) {
		$classes[] = 'has-' . $attrs['backgroundColor'] . '-background-color';
		$classes[] = 'has-background';
	}
	if (!empty($attrs['fontSize'])) {
		$classes[] = 'has-' . $attrs['fontSize'] . '-font-size';
	}
	return esc_attr(implode(' ', $classes));
};

/** Scroll-reveal length shared by every animated block (ms). */
$re_anim_duration = 800;

/**
 * The theme's "Scroll Animation" block setting (scroll-animations-editor.js)
 * for $effect (fade-up, slide-left, zoom-in, ...) after $delay ms: the block
 * attributes plus the class/data attributes its save() adds, so the markup
 * matches the editor's and the setting stays editable per block.
 * Returns [attributes, extra class, data attributes HTML].
 */
$re_anim = function ($effect, $delay = 0) use ($re_anim_duration) {
	if (!$effect) {
		return [[], '', ''];
	}
	return [
		['omegaAnimation' => $effect, 'omegaAnimationDuration' => $re_anim_duration, 'omegaAnimationDelay' => (int) $delay],
		' omega-animate',
		' data-omega-animate="' . esc_attr($effect) . '" data-omega-animate-duration="' . $re_anim_duration . '" data-omega-animate-delay="' . (int) $delay . '"',
	];
};

/**
 * Group block. $class may be '' - pass layout etc. through $attrs, and
 * 'anim' => [effect, delay] for a scroll animation.
 */
$re_group = function ($class, $inner, array $attrs = []) use ($re_json, $re_classes, $re_anim) {
	if ($class) {
		$attrs = ['className' => $class] + $attrs;
	}
	list($anim_attrs, $anim_class, $anim_data) = $re_anim(...($attrs['anim'] ?? [null]));
	unset($attrs['anim']);
	$attrs += ['layout' => ['type' => 'constrained']];
	$attrs += $anim_attrs;
	$tag = $attrs['tagName'] ?? 'div';
	return '<!-- wp:group' . $re_json($attrs) . " -->\n<$tag class=\"" . $re_classes('wp-block-group', $attrs) . $anim_class . "\"$anim_data>\n" . $inner . "</$tag>\n<!-- /wp:group -->\n";
};

/** Flex row group - $justify: left|center|right|space-between. $attrs as for $re_group. */
$re_row = function ($class, $inner, $justify = 'left', $wrap = 'nowrap', array $attrs = []) use ($re_group) {
	return $re_group($class, $inner, ['layout' => ['type' => 'flex', 'flexWrap' => $wrap, 'justifyContent' => $justify, 'verticalAlignment' => 'center']] + $attrs);
};

/** Paragraph - $html is already escaped. */
$re_p = function ($html, $class = '', array $attrs = []) use ($re_json, $re_classes) {
	if ($class) {
		$attrs = ['className' => $class] + $attrs;
	}
	$classes = $re_classes('', $attrs);
	return '<!-- wp:paragraph' . $re_json($attrs) . ' --><p' . ($classes ? ' class="' . $classes . '"' : '') . '>' . $html . "</p><!-- /wp:paragraph -->\n";
};

/** Heading - $html is already escaped. */
$re_h = function ($html, $level = 2, $class = '', array $attrs = []) use ($re_json, $re_classes) {
	$attrs = ['level' => $level] + ($class ? ['className' => $class] : []) + $attrs;
	return '<!-- wp:heading' . $re_json($attrs) . " --><h$level class=\"" . $re_classes('wp-block-heading', $attrs) . "\">" . $html . "</h$level><!-- /wp:heading -->\n";
};

/** Image block (no link) from this template's image folder. */
$re_image = function ($file, $alt, $class = '') use ($re_json, $re_img) {
	$attrs = ['sizeSlug' => 'large', 'linkDestination' => 'none'] + ($class ? ['className' => $class] : []);
	return '<!-- wp:image' . $re_json($attrs) . ' --><figure class="wp-block-image size-large' . ($class ? ' ' . esc_attr($class) : '') . '"><img src="' . $re_img($file) . '" alt="' . esc_attr($alt) . '"/></figure><!-- /wp:image -->' . "\n";
};

/** Core Icon block from the theme's icon library. */
$re_icon = function ($name, $class = '') use ($re_json) {
	return '<!-- wp:icon' . $re_json(['icon' => 'omega-icons/' . $name] + ($class ? ['className' => $class] : [])) . " /-->\n";
};

/** One Button - $style: dark|gold|outline|ghost|light|tab|tab-active. $label is already escaped. */
$re_button = function ($label, $style = 'dark', $url = '#') use ($re_json) {
	$class = 'omega-re-btn omega-re-btn--' . $style;
	return '<!-- wp:button' . $re_json(['className' => $class]) . ' --><div class="wp-block-button ' . $class . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url($url) . '">' . $label . "</a></div><!-- /wp:button -->\n";
};

$re_buttons = function ($inner, $class = '') use ($re_json) {
	return '<!-- wp:buttons' . $re_json($class ? ['className' => $class] : []) . ' --><div class="wp-block-buttons' . ($class ? ' ' . esc_attr($class) : '') . "\">\n" . $inner . "</div><!-- /wp:buttons -->\n";
};

/** Delay between staggered columns revealing one after another (ms). */
$re_stagger = 140;

/**
 * Columns - one $cells entry per column. $anim: one scroll-animation
 * effect for every column (or one per column), staggered so they reveal
 * one after another.
 */
$re_columns = function (array $cells, $class = '', array $widths = [], $anim = null) use ($re_json, $re_anim, $re_stagger) {
	$out = '<!-- wp:columns' . $re_json($class ? ['className' => $class] : []) . ' --><div class="wp-block-columns' . ($class ? ' ' . esc_attr($class) : '') . "\">\n";
	foreach (array_values($cells) as $i => $cell) {
		$width = $widths[$i] ?? '';
		$effect = is_array($anim) ? ($anim[$i] ?? null) : $anim;
		list($anim_attrs, $anim_class, $anim_data) = $re_anim($effect, $i * $re_stagger);
		$attrs = ($width ? ['width' => $width] : []) + $anim_attrs;
		$out .= '<!-- wp:column' . $re_json($attrs) . ' --><div class="wp-block-column' . $anim_class . '"' . ($width ? ' style="flex-basis:' . esc_attr($width) . '"' : '') . $anim_data . ">\n" . $cell . "</div><!-- /wp:column -->\n";
	}
	return $out . "</div><!-- /wp:columns -->\n";
};

/** Full-width page section with the content constrained inside it. */
$re_section = function ($class, $inner) use ($re_group) {
	return $re_group('omega-re-section ' . $class, $inner, ['align' => 'full']) . "\n";
};

/** A button label with the reading-direction arrow after it. */
$re_arrow_label = function ($key) use ($t) {
	return esc_html($t($key)) . ' ' . esc_html($t('arrow'));
};

/** Gold small-caps eyebrow line above a heading. */
$re_eyebrow = function ($key) use ($re_p, $t) {
	return $re_p(esc_html($t($key)), 'omega-re-eyebrow', ['fontSize' => 'x-small']);
};

/** Section header: eyebrow + H2 (+ subtitle) at the start, optional outline button at the end. */
$re_section_head = function ($eyebrow, $title, $subtitle = '', $button = '') use ($re_group, $re_row, $re_eyebrow, $re_h, $re_p, $re_buttons, $re_button, $re_arrow_label, $t) {
	$text = $re_eyebrow($eyebrow) . $re_h(esc_html($t($title))) . ($subtitle ? $re_p(esc_html($t($subtitle)), 'omega-re-subtitle', ['fontSize' => 'small']) : '');
	$inner = $re_group('omega-re-section-head__text', $text, ['layout' => ['type' => 'default']]);
	if ($button) {
		$inner .= $re_buttons($re_button($re_arrow_label($button), 'outline'));
	}
	return $re_row('omega-re-section-head', $inner, 'space-between', 'wrap', ['anim' => ['fade-up']]);
};

/** Site logo: house icon + "ARAB / REAL ESTATES" wordmark. */
$re_logo = function () use ($re_row, $re_group, $re_icon, $re_p, $t) {
	$words = $re_p(esc_html($t('logo_name')), 'omega-re-logo__name') . $re_p(esc_html($t('logo_tag')), 'omega-re-logo__tag');
	return $re_row('omega-re-logo', $re_icon('home-work', 'omega-re-logo__icon') . $re_group('omega-re-logo__text', $words, ['layout' => ['type' => 'default']]));
};

/**
 * Navigation block with the page's menu links. $extra adds links (label,
 * url, class) after them - the header uses it for the phone number and the
 * call-to-action, which the CSS shows only inside the phone menu.
 */
$re_nav = function ($class, $overlay = 'mobile', array $extra = []) use ($re_json, $t) {
	$out = '<!-- wp:navigation' . $re_json(['className' => $class, 'overlayMenu' => $overlay, 'layout' => ['type' => 'flex', 'justifyContent' => 'center']]) . " -->\n";
	foreach (['nav_home', 'nav_properties', 'nav_about', 'nav_services', 'nav_agents', 'nav_contact'] as $key) {
		$out .= '<!-- wp:navigation-link' . $re_json(['label' => $t($key), 'url' => '#', 'kind' => 'custom']) . " /-->\n";
	}
	foreach ($extra as list($label, $url, $link_class)) {
		$out .= '<!-- wp:navigation-link' . $re_json(['label' => $label, 'url' => $url, 'kind' => 'custom', 'className' => 'omega-re-nav__extra ' . $link_class]) . " /-->\n";
	}
	return $out . "<!-- /wp:navigation -->\n";
};

/** Icon + text on one line (location, phone, ...). $html is already escaped. */
$re_icon_line = function ($icon, $html, $class = 'omega-re-icon-line') use ($re_row, $re_icon, $re_p) {
	return $re_row($class, $re_icon($icon) . $re_p($html, '', ['fontSize' => 'x-small']));
};

/** A phone number - kept left-to-right in Arabic by landing-real-estate.css. */
$re_phone = function ($number) {
	return esc_html($number);
};

/* ── Cards ────────────────────────────────────────────────────────── */

/** $card: [badge, title, location, beds, baths, area, price, image] copy keys / file. */
$re_property_card = function (array $card) use ($re_group, $re_row, $re_image, $re_p, $re_h, $re_icon, $re_icon_line, $t) {
	list($badge, $title, $location, $beds, $baths, $area, $price, $image) = $card;
	$media = $re_image($image, $t($title))
		. $re_p(esc_html($t($badge)), 'omega-re-badge', ['fontSize' => 'x-small'])
		. $re_icon('favorite', 'omega-re-fav');
	$specs = $re_icon_line('bed', esc_html($t($beds)), 'omega-re-spec')
		. $re_icon_line('bathtub', esc_html($t($baths)), 'omega-re-spec')
		. $re_icon_line('square-foot', esc_html($t($area)), 'omega-re-spec');
	$body = $re_h(esc_html($t($title)), 3, 'omega-re-card-title', ['fontSize' => 'medium'])
		. $re_icon_line('location-on', esc_html($t($location)))
		. $re_row('omega-re-specs', $specs, 'left', 'wrap')
		. $re_p('<strong>' . esc_html($t($price)) . '</strong>', 'omega-re-price');
	return $re_group('omega-re-card omega-re-property', $re_group('omega-re-card__media', $media) . $re_group('omega-re-card__body', $body), ['backgroundColor' => 'surface']);
};

$re_feature = function ($icon, $title, $text) use ($re_row, $re_group, $re_icon, $re_p, $t) {
	$words = $re_p('<strong>' . esc_html($t($title)) . '</strong>', 'omega-re-feature__title', ['fontSize' => 'x-small'])
		. $re_p(esc_html($t($text)), 'omega-re-muted', ['fontSize' => 'x-small']);
	return $re_row('omega-re-feature', $re_icon($icon, 'omega-re-badge-icon') . $re_group('', $words, ['layout' => ['type' => 'default']]));
};

$re_category_card = function ($title, $text, $image) use ($re_group, $re_row, $re_image, $re_h, $re_p, $re_icon, $t) {
	$words = $re_h(esc_html($t($title)), 3, 'omega-re-card-title', ['fontSize' => 'medium']) . $re_p(esc_html($t($text)), 'omega-re-muted', ['fontSize' => 'x-small']);
	$body = $re_row('omega-re-category__body', $re_group('', $words, ['layout' => ['type' => 'default']]) . $re_icon('arrow-forward', 'omega-re-arrow'), 'space-between');
	return $re_group('omega-re-card omega-re-category', $re_image($image, $t($title)) . $body, ['backgroundColor' => 'surface']);
};

$re_service_card = function ($icon, $title, $text) use ($re_row, $re_group, $re_icon, $re_p, $t) {
	$words = $re_p('<strong>' . esc_html($t($title)) . '</strong>', 'omega-re-service__title', ['fontSize' => 'small'])
		. $re_p(esc_html($t($text)), 'omega-re-muted', ['fontSize' => 'x-small']);
	return $re_row('omega-re-card omega-re-service', $re_icon($icon, 'omega-re-service__icon') . $re_group('', $words, ['layout' => ['type' => 'default']]));
};

$re_agent_card = function ($name, $role, $phone, $image) use ($re_group, $re_image, $re_p, $re_icon_line, $re_phone, $t) {
	$body = $re_p('<strong>' . esc_html($t($name)) . '</strong>', 'omega-re-agent__name', ['fontSize' => 'small'])
		. $re_p(esc_html($t($role)), 'omega-re-muted', ['fontSize' => 'x-small'])
		. $re_icon_line('call', $re_phone($phone), 'omega-re-icon-line omega-re-agent__phone');
	return $re_group('omega-re-card omega-re-agent', $re_image($image, $t($name), 'omega-re-agent__photo') . $re_group('omega-re-card__body', $body), ['backgroundColor' => 'surface']);
};

$re_testimonial = function ($quote, $name, $city, $image) use ($re_row, $re_group, $re_image, $re_p, $t) {
	$words = $re_p('★★★★★', 'omega-re-stars', ['fontSize' => 'small'])
		. $re_p(esc_html($t('quote_open') . $t($quote) . $t('quote_close')), 'omega-re-quote', ['fontSize' => 'x-small'])
		. $re_p('<strong>' . esc_html($t($name)) . '</strong>', 'omega-re-quote__name', ['fontSize' => 'x-small'])
		. $re_p(esc_html($t($city)), 'omega-re-muted', ['fontSize' => 'x-small']);
	return $re_row('omega-re-card omega-re-testimonial', $re_image($image, $t($name), 'omega-re-avatar') . $re_group('', $words, ['layout' => ['type' => 'default']]));
};

$re_search_field = function ($icon, $label, $value) use ($re_row, $re_group, $re_icon, $re_p, $t) {
	$words = $re_p('<strong>' . esc_html($t($label)) . '</strong>', 'omega-re-field__label', ['fontSize' => 'x-small'])
		. $re_p(esc_html($t($value)), 'omega-re-muted', ['fontSize' => 'x-small']);
	return $re_row('omega-re-field', $re_icon($icon, 'omega-re-field__icon') . $re_group('omega-re-field__text', $words, ['layout' => ['type' => 'default']]) . $re_icon('expand-more', 'omega-re-field__chevron'));
};

$re_stat = function ($value, $label) use ($re_group, $re_p, $t) {
	return $re_group('omega-re-stat', $re_p(esc_html($value), 'omega-re-stat__value') . $re_p(esc_html($t($label)), 'omega-re-stat__label', ['fontSize' => 'x-small']), ['layout' => ['type' => 'default']]);
};

/* ── Page ─────────────────────────────────────────────────────────── */

$re_phone_number = '+966 50 123 4567';
$page            = '';

// Header
$header_actions = $re_icon_line('call', $re_phone($re_phone_number), 'omega-re-icon-line omega-re-header__phone')
	. $re_buttons($re_button(esc_html($t('list_property')), 'dark'));
$menu_extra     = [
	[$re_phone_number, 'tel:' . str_replace(' ', '', $re_phone_number), 'omega-re-nav__phone'],
	[$t('list_property'), '#', 'omega-re-nav__cta'],
];
$page .= $re_group('omega-re-header', $re_row('omega-re-header__bar', $re_logo() . $re_nav('omega-re-nav', 'mobile', $menu_extra) . $re_row('omega-re-header__actions', $header_actions), 'space-between'), ['align' => 'full', 'tagName' => 'header']) . "\n";

// Hero
$hero_text = $re_eyebrow('hero_eyebrow')
	. $re_h(esc_html($t('hero_title')), 1, 'omega-re-hero__title')
	. $re_p(esc_html($t('hero_lead')), 'omega-re-hero__lead')
	. $re_buttons($re_button($re_arrow_label('explore'), 'dark') . $re_button('▶ ' . esc_html($t('watch_video')), 'ghost'))
	. $re_row('omega-re-stats', $re_stat('500+', 'stat_properties') . $re_stat('120+', 'stat_clients') . $re_stat('15+', 'stat_years'), 'left', 'wrap');
$page .= $re_group('omega-re-hero', $re_image('Hero-Image.webp', $t('hero_alt'), 'omega-re-hero__bg') . $re_group('omega-re-hero__content', $hero_text, ['layout' => ['type' => 'default'], 'anim' => ['fade-up']]), ['align' => 'full']) . "\n";

// Search bar (overlaps the hero)
$tabs = $re_buttons($re_button(esc_html($t('tab_buy')), 'tab-active') . $re_button(esc_html($t('tab_rent')), 'tab') . $re_button(esc_html($t('tab_commercial')), 'tab'), 'omega-re-search__tabs');
$fields = $re_search_field('location-on', 'field_location', 'field_location_value')
	. $re_search_field('home-work', 'field_type', 'field_type_value')
	. $re_search_field('payments', 'field_price', 'field_price_value')
	. $re_search_field('bed', 'field_bedrooms', 'field_bedrooms_value')
	. $re_buttons($re_button(esc_html($t('search')), 'dark'), 'omega-re-search__submit');
$page .= $re_group('omega-re-search-wrap', $re_group('omega-re-search', $tabs . $re_row('omega-re-search__fields', $fields, 'space-between', 'wrap'), ['align' => 'wide', 'backgroundColor' => 'surface', 'anim' => ['fade-up', 200]]), ['align' => 'full']) . "\n";

// Popular properties
$page .= $re_section('omega-re-properties', $re_section_head('featured_eyebrow', 'featured_title', 'featured_subtitle', 'featured_button')
	. $re_columns([
		$re_property_card(['for_sale', 'p1_title', 'p1_location', 'p1_beds', 'p1_baths', 'p1_area', 'p1_price', 'property-1.webp']),
		$re_property_card(['for_rent', 'p2_title', 'p2_location', 'p2_beds', 'p2_baths', 'p2_area', 'p2_price', 'property-2.webp']),
		$re_property_card(['for_sale', 'p3_title', 'p3_location', 'p3_beds', 'p3_baths', 'p3_area', 'p3_price', 'property-3.webp']),
		$re_property_card(['for_rent', 'p4_title', 'p4_location', 'p4_beds', 'p4_baths', 'p4_area', 'p4_price', 'property-4.webp']),
	], 'omega-re-grid', [], 'fade-up'));

// Why choose us
$features = $re_columns([
	$re_feature('verified-user', 'f1_title', 'f1_text'),
	$re_feature('real-estate-agent', 'f2_title', 'f2_text'),
	$re_feature('shield-lock', 'f3_title', 'f3_text'),
	$re_feature('handshake', 'f4_title', 'f4_text'),
], 'omega-re-features', [], 'zoom-in');
$why_text = $re_eyebrow('why_eyebrow')
	. $re_h(esc_html($t('why_title')))
	. $re_p(esc_html($t('why_text')), 'omega-re-muted', ['fontSize' => 'small'])
	. $features;
$page .= $re_group('omega-re-why', $re_columns([$re_image('why-choose.webp', $t('why_alt'), 'omega-re-why__image'), $why_text], 'omega-re-why__cols', ['36%', '64%'], ['slide-left', 'slide-right']), ['align' => 'full']) . "\n";

// Categories
$page .= $re_section('omega-re-categories', $re_section_head('categories_eyebrow', 'categories_title', '', 'categories_button')
	. $re_columns([
		$re_category_card('c1_title', 'c1_text', 'category-villas.webp'),
		$re_category_card('c2_title', 'c2_text', 'category-apartments.webp'),
		$re_category_card('c3_title', 'c3_text', 'category-commercial.webp'),
		$re_category_card('c4_title', 'c4_text', 'category-land.webp'),
	], 'omega-re-grid', [], 'fade-up'));

// Investment banner
$invest_text = $re_eyebrow('invest_eyebrow')
	. $re_h(esc_html($t('invest_title_1')) . '<br>' . esc_html($t('invest_title_2')), 2, 'omega-re-invest__title')
	. $re_p(esc_html($t('invest_lead')), 'omega-re-invest__lead', ['fontSize' => 'small'])
	. $re_buttons($re_button($re_arrow_label('invest_button'), 'gold'));
$page .= $re_group('omega-re-invest', $re_image('invest-skyline.webp', $t('invest_alt'), 'omega-re-band__bg') . $re_group('omega-re-band__content', $invest_text, ['layout' => ['type' => 'default'], 'anim' => ['fade-up']]), ['align' => 'full']) . "\n";

// Services
$page .= $re_section('omega-re-services', $re_section_head('services_eyebrow', 'services_title')
	. $re_columns([
		$re_service_card('house', 's1_title', 's1_text'),
		$re_service_card('key', 's2_title', 's2_text'),
		$re_service_card('sell', 's3_title', 's3_text'),
		$re_service_card('manage-accounts', 's4_title', 's4_text'),
	], 'omega-re-grid', [], 'fade-up'));

// Agents
$page .= $re_section('omega-re-agents', $re_section_head('agents_eyebrow', 'agents_title', '', 'agents_button')
	. $re_columns([
		$re_agent_card('a1_name', 'a1_role', '+966 50 123 4567', 'agent-1.webp'),
		$re_agent_card('a2_name', 'a2_role', '+966 50 234 5678', 'agent-2.webp'),
		$re_agent_card('a3_name', 'a3_role', '+966 50 345 6789', 'agent-3.webp'),
		$re_agent_card('a4_name', 'a4_role', '+966 50 456 7890', 'agent-4.webp'),
	], 'omega-re-grid', [], 'fade-up'));

// Testimonials
$slider_nav = $re_row('omega-re-slider-nav', $re_icon('arrow-back', 'omega-re-nav-btn') . $re_icon('arrow-forward', 'omega-re-nav-btn omega-re-nav-btn--active'));
$testimonial_head = $re_row('omega-re-section-head', $re_group('omega-re-section-head__text', $re_eyebrow('testimonials_eyebrow') . $re_h(esc_html($t('testimonials_title'))), ['layout' => ['type' => 'default']]) . $slider_nav, 'space-between', 'wrap', ['anim' => ['fade-up']]);
$page .= $re_section('omega-re-testimonials', $testimonial_head
	. $re_columns([
		$re_testimonial('t1_quote', 't1_name', 't1_city', 'client-1.webp'),
		$re_testimonial('t2_quote', 't2_name', 't2_city', 'client-2.webp'),
		$re_testimonial('t3_quote', 't3_name', 't3_city', 'client-3.webp'),
	], 'omega-re-grid', [], 'fade-up'));

// Call to action
$cta_text = $re_h(esc_html($t('cta_title_1')) . '<br>' . esc_html($t('cta_title_2')), 2, 'omega-re-cta__title')
	. $re_p(esc_html($t('cta_lead')), 'omega-re-cta__lead', ['fontSize' => 'small'])
	. $re_buttons($re_button($re_arrow_label('contact_us'), 'gold') . $re_button('☎ ' . $re_phone($re_phone_number), 'light', 'tel:+966501234567'));
$page .= $re_group('omega-re-cta', $re_image('cta-villa.webp', $t('cta_alt'), 'omega-re-band__bg') . $re_group('omega-re-band__content', $cta_text, ['layout' => ['type' => 'default'], 'anim' => ['fade-up']]), ['align' => 'full']) . "\n";

// Footer
$success     = htmlspecialchars($t('newsletter_success'), ENT_COMPAT);
$placeholder = esc_attr($t('newsletter_placeholder'));
$arrow       = esc_html($t('arrow'));
$newsletter  = $re_p('<strong>' . esc_html($t('newsletter_label')) . '</strong>', 'omega-re-footer__label', ['fontSize' => 'x-small'])
	. '<!-- wp:omega-design/newsletter-form {"placeholder":"' . $placeholder . '","buttonText":"' . $arrow . '","successMessage":"' . $success . '","className":"omega-re-newsletter"} -->' . "\n"
	. '<div class="wp-block-omega-design-newsletter-form omega-re-newsletter omega-newsletter-form-block" data-success-message="' . $success . '">' . "\n"
	. '<form class="omega-newsletter-form" novalidate>' . "\n"
	. '<input type="text" name="omega_newsletter_company" class="omega-newsletter-form__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true"/>' . "\n"
	. '<div class="omega-newsletter-form__row">' . "\n"
	. '<input type="email" class="omega-newsletter-form__input" placeholder="' . $placeholder . '" name="omega_newsletter_email" required/>' . "\n"
	. '<button type="submit" class="omega-newsletter-form__submit wp-element-button">' . $arrow . '</button>' . "\n"
	. '</div>' . "\n"
	. '<p class="omega-newsletter-form__message" aria-live="polite"></p>' . "\n"
	. '</form>' . "\n"
	. '</div>' . "\n"
	. '<!-- /wp:omega-design/newsletter-form -->' . "\n";
$social = '<!-- wp:social-links {"className":"is-style-logos-only omega-re-social"} --><ul class="wp-block-social-links is-style-logos-only omega-re-social">'
	. '<!-- wp:social-link {"url":"#","service":"facebook"} /--><!-- wp:social-link {"url":"#","service":"instagram"} /--><!-- wp:social-link {"url":"#","service":"linkedin"} /--><!-- wp:social-link {"url":"#","service":"youtube"} /-->'
	. "</ul><!-- /wp:social-links -->\n";
$footer_top = $re_row('omega-re-footer__top', $re_logo() . $re_nav('omega-re-nav omega-re-nav--footer', 'never') . $social . $re_group('omega-re-footer__newsletter', $newsletter, ['layout' => ['type' => 'default']]), 'space-between', 'wrap');
$footer_bottom = $re_row('omega-re-footer__bottom', $re_p('© 2024 ' . esc_html($t('copyright')), '', ['fontSize' => 'x-small'])
	. $re_row('omega-re-footer__legal', $re_p('<a href="#">' . esc_html($t('privacy')) . '</a>', '', ['fontSize' => 'x-small']) . $re_p('<a href="#">' . esc_html($t('terms')) . '</a>', '', ['fontSize' => 'x-small'])), 'space-between', 'wrap');
$page .= $re_group('omega-re-footer', $footer_top . $footer_bottom, ['align' => 'full', 'tagName' => 'footer']) . "\n";

$wrapper = 'omega-landing omega-landing--real-estate omega-landing--standalone omega-lang-' . ('ar' === $re_lang ? 'ar omega-re-ar' : 'en');
echo $re_group($wrapper, $page, ['align' => 'full', 'backgroundColor' => 'background']);
