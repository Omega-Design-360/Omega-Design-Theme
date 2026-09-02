/**
 * "Omega Icon" block (omega-design/icon) - a self-contained icon picker
 * pre-loaded with the theme's stroke icons, so it shows up in the block
 * inserter like any other block with no dependency on a third-party icon
 * plugin. Unlike the WordPress-native icon library (registered in
 * includes/core/icons.php), this block keeps the icons as real multi-part
 * stroke SVGs - core's icon registry sanitizer strips stroke/circle/rect
 * entirely, so it can only ever hold flat filled glyphs.
 *
 * Each saved icon renders as <svg data-icon="NAME">, with its child
 * elements in the exact same order as the ICONS source below, because
 * assets/css/omega-icon-choreography.css targets specific children by
 * position (nth-child) to animate them individually on hover - ported
 * directly from the "Icon choreography" section of mysite.css (the
 * mysite-editor plugin), just retargeted from ".mysite-card:hover" to
 * ".omega-ico-host:hover" so it works anywhere on the site, not only
 * inside that plugin's card component.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var RawHTML = wp.element.RawHTML;
	var useState = wp.element.useState;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var BlockControls = wp.blockEditor.BlockControls;
	var Placeholder = wp.components.Placeholder;
	var Button = wp.components.Button;
	var ToolbarGroup = wp.components.ToolbarGroup;
	var ToolbarButton = wp.components.ToolbarButton;
	var PanelBody = wp.components.PanelBody;
	var SearchControl = wp.components.SearchControl;
	var __ = wp.i18n.__;

	/**
	 * Verbatim source: the same ICONS object used elsewhere on the site
	 * (mysite.js's initServices()). Every path/circle/rect stays in its
	 * original order - the choreography CSS depends on that order.
	 */
	var ICONS = {
		globe: '<path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M3.6 9h16.8M3.6 15h16.8"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/>',
		wp: '<path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="m6 8 3 8 3-8 3 8 3-8"/>',
		palette: '<path d="M12 3a9 9 0 0 0 0 18 2 2 0 0 0 1.6-3.2 2 2 0 0 1 1.6-3.2H18a3 3 0 0 0 3-3A9 9 0 0 0 12 3Z"/><circle cx="8" cy="10" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16" cy="10" r="1"/>',
		badge: '<path d="M12 2.5 20 6v5.5c0 4.4-3.2 7.6-8 8.7-4.8-1.1-8-4.3-8-8.7V6l8-3.5Z"/><circle cx="12" cy="10.5" r="2.6"/><path d="M9.6 14.4 9 19l3-1.6L15 19l-.6-4.6"/>',
		cart: '<path d="M3 4h2l2.4 10.2A2 2 0 0 0 9.35 16H18a2 2 0 0 0 1.95-1.55L21.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
		chart: '<path d="M4 20V4"/><path d="M4 20h16"/><path d="m7.5 15 3.5-4 3 2.5L20 7"/><path d="M20 7h-3.5M20 7v3.5"/>',
		shield: '<path d="M12 3 20 6v6c0 4.6-3.3 7.9-8 9-4.7-1.1-8-4.4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/>',
		phone: '<rect x="6.5" y="2.5" width="11" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
		share: '<circle cx="17.5" cy="5.5" r="2.5"/><circle cx="6.5" cy="12" r="2.5"/><circle cx="17.5" cy="18.5" r="2.5"/><path d="m8.8 10.8 6.4-3.8M8.8 13.2l6.4 3.8"/>',
		pen: '<path d="M4 20h4L20 8a2.8 2.8 0 0 0-4-4L4 16v4Z"/><path d="m14.5 5.5 4 4"/>'
	};

	function buildIconMarkup(name) {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-icon="' + name + '" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' + ICONS[name] + '</svg>';
	}

	var ICON_LIST = [
		{ name: 'globe', title: __('Globe', 'omega-design'), keywords: [__('web design', 'omega-design'), __('website', 'omega-design'), __('world', 'omega-design'), __('international', 'omega-design'), __('earth', 'omega-design')] },
		{ name: 'wp', title: __('WordPress', 'omega-design'), keywords: [__('WordPress development', 'omega-design'), __('CMS', 'omega-design'), __('build', 'omega-design'), __('development', 'omega-design')] },
		{ name: 'palette', title: __('Palette', 'omega-design'), keywords: [__('branding', 'omega-design'), __('design', 'omega-design'), __('color', 'omega-design'), __('creative', 'omega-design'), __('paint', 'omega-design')] },
		{ name: 'badge', title: __('Badge', 'omega-design'), keywords: [__('quality guarantee', 'omega-design'), __('award', 'omega-design'), __('certified', 'omega-design'), __('seal', 'omega-design'), __('trust', 'omega-design')] },
		{ name: 'cart', title: __('Cart', 'omega-design'), keywords: [__('e-commerce', 'omega-design'), __('WooCommerce', 'omega-design'), __('shop', 'omega-design'), __('checkout', 'omega-design'), __('shopping', 'omega-design')] },
		{ name: 'chart', title: __('Chart', 'omega-design'), keywords: [__('SEO', 'omega-design'), __('analytics', 'omega-design'), __('growth', 'omega-design'), __('statistics', 'omega-design'), __('reporting', 'omega-design')] },
		{ name: 'share', title: __('Share', 'omega-design'), keywords: [__('social media marketing', 'omega-design'), __('social', 'omega-design'), __('network', 'omega-design'), __('connect', 'omega-design')] },
		{ name: 'shield', title: __('Shield', 'omega-design'), keywords: [__('security & maintenance', 'omega-design'), __('security', 'omega-design'), __('protection', 'omega-design'), __('safety', 'omega-design')] },
		{ name: 'phone', title: __('Phone', 'omega-design'), keywords: [__('support & contact', 'omega-design'), __('call', 'omega-design'), __('mobile', 'omega-design'), __('contact', 'omega-design')] },
		{ name: 'pen', title: __('Pen', 'omega-design'), keywords: [__('content & copywriting', 'omega-design'), __('writing', 'omega-design'), __('edit', 'omega-design'), __('blog', 'omega-design')] }
	];

	function findIcon(name) {
		for (var i = 0; i < ICON_LIST.length; i++) {
			if (ICON_LIST[i].name === name) return ICON_LIST[i];
		}
		return null;
	}

	function iconMatchesSearch(icon, term) {
		if (!term) return true;
		term = term.toLowerCase();
		if (icon.title.toLowerCase().indexOf(term) !== -1) return true;
		for (var i = 0; i < icon.keywords.length; i++) {
			if (icon.keywords[i].toLowerCase().indexOf(term) !== -1) return true;
		}
		return false;
	}

	function IconGrid(props) {
		var visible = ICON_LIST.filter(function (icon) {
			return iconMatchesSearch(icon, props.search);
		});

		return createElement(
			'div',
			{ className: 'omega-icon-block-grid' },
			visible.map(function (icon) {
				return createElement(
					Button,
					{
						key: icon.name,
						className: 'omega-icon-block-grid__item' + (props.selected === icon.name ? ' is-selected' : ''),
						label: icon.title,
						showTooltip: true,
						isPressed: props.selected === icon.name,
						onClick: function () {
							props.onSelect(icon.name);
						}
					},
					createElement(RawHTML, {}, buildIconMarkup(icon.name))
				);
			})
		);
	}

	registerBlockType('omega-design/icon', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps({ className: 'omega-icon-block' });

			var searchState = useState('');
			var search = searchState[0];
			var setSearch = searchState[1];

			var pickerState = useState(!attributes.icon);
			var isPicking = pickerState[0];
			var setIsPicking = pickerState[1];

			var icon = attributes.icon ? findIcon(attributes.icon) : null;

			function selectIcon(name) {
				setAttributes({ icon: name });
				setIsPicking(false);
			}

			var pickerPanel = createElement(
				Fragment,
				{},
				createElement(SearchControl, {
					value: search,
					onChange: setSearch,
					placeholder: __('Search icons…', 'omega-design')
				}),
				createElement(IconGrid, {
					search: search,
					selected: attributes.icon,
					onSelect: selectIcon
				})
			);

			if (isPicking || !icon) {
				return createElement(
					'div',
					blockProps,
					createElement(
						Placeholder,
						{
							icon: 'art',
							label: __('Omega Icon', 'omega-design'),
							instructions: __('Choose an icon.', 'omega-design')
						},
						pickerPanel
					)
				);
			}

			return createElement(
				Fragment,
				{},
				createElement(
					BlockControls,
					{},
					createElement(
						ToolbarGroup,
						{},
						createElement(ToolbarButton, {
							onClick: function () {
								setIsPicking(true);
							}
						}, __('Replace icon', 'omega-design'))
					)
				),
				createElement(
					InspectorControls,
					{},
					createElement(
						PanelBody,
						{ title: __('Icon', 'omega-design'), initialOpen: true },
						pickerPanel
					)
				),
				createElement(
					'div',
					blockProps,
					createElement(RawHTML, {}, buildIconMarkup(icon.name))
				)
			);
		},
		save: function (props) {
			var blockProps = wp.blockEditor.useBlockProps.save({ className: 'omega-icon-block' });

			if (!props.attributes.icon) {
				return null;
			}

			return createElement(
				'div',
				blockProps,
				createElement(RawHTML, {}, buildIconMarkup(props.attributes.icon))
			);
		}
	});
})(window.wp);
