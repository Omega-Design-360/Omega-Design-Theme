/**
 * Adds a "Width" control to the block Inspector (Styles tab) for the
 * Group, Row, Grid (all core/group) and Columns (core/columns) blocks,
 * so editors can switch between Standard (content), Wide and Full page
 * layout widths without hunting for the toolbar alignment control.
 *
 * Uses the block's native `align` attribute, so it renders with the
 * `contentSize` / `wideSize` already defined in theme.json - no extra
 * CSS required.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.blockEditor || !wp.components || !wp.element || !wp.compose || !wp.data) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var ColorPalette = wp.blockEditor.ColorPalette;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var ButtonGroup = wp.components.ButtonGroup;
	var RangeControl = wp.components.RangeControl;
	var TabPanel = wp.components.TabPanel;
	var BaseControl = wp.components.BaseControl;
	var __ = wp.i18n.__;

	var TARGET_BLOCKS = ['core/group', 'core/columns'];

	/**
	 * Shared Desktop/Tablet/Mobile plumbing for the responsive controls
	 * further down (Custom Size, Hover Colors & Shadow). A "device" value is
	 * always one of 'desktop' | 'tablet' | 'mobile'; per-field attribute
	 * keys for tablet/mobile are the desktop key prefixed with the device
	 * name (e.g. 'width' -> 'tabletWidth' -> 'mobileWidth'), so the same
	 * getters/setters work for all three without a nested object shape.
	 *
	 * Tablet/mobile values are resolved with the same cascade the CSS
	 * `max-width` media queries produce on the front end (see
	 * includes/core/responsive_styles.php): mobile falls back to tablet,
	 * which falls back to desktop, matching a narrower-wins-if-set rule.
	 */
	var DEVICE_TABS = [
		{ name: 'desktop', title: __('Desktop', 'omega-design') },
		{ name: 'tablet', title: __('Tablet', 'omega-design') },
		{ name: 'mobile', title: __('Mobile', 'omega-design') }
	];

	function capitalize(value) {
		return value.charAt(0).toUpperCase() + value.slice(1);
	}

	function fieldKeyForDevice(baseKey, device) {
		return device === 'desktop' ? baseKey : device + capitalize(baseKey);
	}

	function resolveResponsiveValue(data, baseKey, device) {
		if (device === 'mobile' && data[fieldKeyForDevice(baseKey, 'mobile')] !== undefined) {
			return data[fieldKeyForDevice(baseKey, 'mobile')];
		}
		if (device !== 'desktop' && data[fieldKeyForDevice(baseKey, 'tablet')] !== undefined) {
			return data[fieldKeyForDevice(baseKey, 'tablet')];
		}
		return data[baseKey];
	}

	/**
	 * The Post/Site Editor's Desktop/Tablet/Mobile preview toggle lives in
	 * different data stores across WordPress versions, so all three are
	 * checked. Only used for the editor canvas live preview below - the
	 * front end always gets its tablet/mobile values from real `@media`
	 * rules (see includes/core/responsive_styles.php), not from this.
	 */
	function getCurrentDeviceType() {
		var stores = ['core/editor', 'core/edit-site', 'core/edit-post'];

		for (var i = 0; i < stores.length; i++) {
			var store = wp.data.select(stores[i]);
			if (store && typeof store.getDeviceType === 'function') {
				var type = store.getDeviceType();
				if (type) {
					return type.toLowerCase();
				}
			}
		}

		return 'desktop';
	}

	var WIDTH_OPTIONS = [
		{ label: __('Standard', 'omega-design'), value: undefined },
		{ label: __('Wide', 'omega-design'), value: 'wide' },
		{ label: __('Full', 'omega-design'), value: 'full' }
	];

	var withLayoutWidthControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (TARGET_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var currentAlign = props.attributes.align;

			var buttons = WIDTH_OPTIONS.map(function (option) {
				var isActive = currentAlign === option.value;
				return createElement(
					Button,
					{
						key: option.label,
						variant: isActive ? 'primary' : 'secondary',
						isPressed: isActive,
						onClick: function () {
							props.setAttributes({ align: option.value });
						}
					},
					option.label
				);
			});

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{ group: 'styles' },
					createElement(
						PanelBody,
						{ title: __('Width', 'omega-design'), initialOpen: true },
						createElement(ButtonGroup, {}, buttons)
					)
				)
			);
		};
	}, 'withLayoutWidthControl');

	addFilter('editor.BlockEdit', 'omega-design/layout-width-control', withLayoutWidthControl);

	/**
	 * Adds custom "Width" and "Height" number+unit inputs, with Desktop/
	 * Tablet/Mobile variants, to the same Inspector Styles panel, for the
	 * same Group/Row/Stack/Columns blocks. Stored under
	 * attributes.style.dimensions.{width,height,tabletWidth,mobileHeight,...}
	 * - the same attribute core already uses for minHeight/aspectRatio - so
	 * it needs no new attribute registration and won't collide with the
	 * preset Standard/Wide/Full align buttons above.
	 *
	 * Tablet/mobile values only take effect on the front end via the
	 * `@media` rules generated in includes/core/responsive_styles.php -
	 * the desktop value here is the only one baked into the saved markup
	 * directly, since a plain inline style can't vary by viewport.
	 */
	var UnitControl = wp.components.__experimentalUnitControl || wp.components.UnitControl;

	var SIZE_UNITS = [
		{ value: 'px', label: 'px', default: '' },
		{ value: '%', label: '%', default: '' },
		{ value: 'em', label: 'em', default: '' },
		{ value: 'rem', label: 'rem', default: '' },
		{ value: 'vw', label: 'vw', default: '' },
		{ value: 'vh', label: 'vh', default: '' }
	];

	function getCustomSize(attributes) {
		return (attributes.style && attributes.style.dimensions) || {};
	}

	function setCustomSize(props, key, value) {
		var style = props.attributes.style || {};
		var dimensions = Object.assign({}, style.dimensions);

		if (value) {
			dimensions[key] = value;
		} else {
			delete dimensions[key];
		}

		var nextStyle = Object.assign({}, style);
		if (Object.keys(dimensions).length) {
			nextStyle.dimensions = dimensions;
		} else {
			delete nextStyle.dimensions;
		}

		props.setAttributes({ style: nextStyle });
	}

	function getResponsiveSizeStyle(attributes, device) {
		var size = getCustomSize(attributes);
		var width = resolveResponsiveValue(size, 'width', device);
		var height = resolveResponsiveValue(size, 'height', device);
		var style = {};

		if (width) { style.width = width; }
		if (height) { style.height = height; }

		return style;
	}

	var withCustomSizeControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (TARGET_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var size = getCustomSize(props.attributes);

			function renderDeviceFields(device) {
				var widthKey = fieldKeyForDevice('width', device);
				var heightKey = fieldKeyForDevice('height', device);

				return createElement(
					Fragment,
					{ key: device },
					createElement(UnitControl, {
						key: widthKey,
						label: __('Width', 'omega-design'),
						units: SIZE_UNITS,
						value: size[widthKey] || '',
						onChange: function (value) {
							setCustomSize(props, widthKey, value);
						}
					}),
					createElement(UnitControl, {
						key: heightKey,
						label: __('Height', 'omega-design'),
						units: SIZE_UNITS,
						value: size[heightKey] || '',
						onChange: function (value) {
							setCustomSize(props, heightKey, value);
						}
					})
				);
			}

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{ group: 'styles' },
					createElement(
						PanelBody,
						{ title: __('Custom Size', 'omega-design'), initialOpen: false },
						createElement(TabPanel, { tabs: DEVICE_TABS }, function (tab) {
							return renderDeviceFields(tab.name);
						})
					)
				)
			);
		};
	}, 'withCustomSizeControl');

	if (UnitControl && TabPanel) {
		addFilter('editor.BlockEdit', 'omega-design/custom-size-control', withCustomSizeControl);
	}

	/**
	 * Mirrors the width/height for the currently active device preview
	 * (Desktop/Tablet/Mobile, from the editor's own preview toggle) onto
	 * the block's wrapper element in the editor canvas, so switching device
	 * previews in the editor matches what the front end's `@media` rules
	 * will actually render.
	 */
	var withCustomSizeStyleEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (TARGET_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var style = getResponsiveSizeStyle(props.attributes, getCurrentDeviceType());

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			var wrapperProps = Object.assign({}, props.wrapperProps, {
				style: Object.assign({}, props.wrapperProps && props.wrapperProps.style, style)
			});

			return createElement(BlockListBlock, Object.assign({}, props, { wrapperProps: wrapperProps }));
		};
	}, 'withCustomSizeStyleEditor');

	addFilter('editor.BlockListBlock', 'omega-design/custom-size-style-editor', withCustomSizeStyleEditor);

	/**
	 * Bakes the same width/height into the saved block markup, so it
	 * renders on the front end without needing a render_block PHP filter.
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/custom-size-style-save', function (extraProps, blockType, attributes) {
		if (TARGET_BLOCKS.indexOf(blockType.name) === -1) {
			return extraProps;
		}

		var size = getCustomSize(attributes);
		if (!size.width && !size.height) {
			return extraProps;
		}

		var style = Object.assign({}, extraProps.style);
		if (size.width) { style.width = size.width; }
		if (size.height) { style.height = size.height; }
		extraProps.style = style;

		return extraProps;
	});

	/**
	 * Adds a "Hover Colors & Shadow" panel (text, background, border, shadow
	 * color/size/offsets), with Desktop/Tablet/Mobile variants, to the same
	 * Inspector Styles group, for Group/Row/Stack/Grid, Columns and Column
	 * blocks. Stored under attributes.style.omegaHover - a custom key
	 * nested in the same "style" attribute core already auto-registers for
	 * these blocks' color/border supports, so no new attribute registration
	 * is needed. Tablet/mobile fields use the same device-prefixed key
	 * convention as Custom Size above (e.g. 'text' -> 'tabletText').
	 *
	 * The shadow itself is composed client-side from shadowColor + shadowSize
	 * (a blur radius in px, with the vertical offset derived as a third of
	 * that) rather than stored as a single CSS string, so both are exposed as
	 * independently editable controls.
	 *
	 * The desktop values' `:hover` rule lives in assets/css/style.css
	 * (enqueued on both the front end and the editor canvas via
	 * `enqueue_block_assets`), reading the CSS custom properties set inline
	 * below, falling back to the theme-wide default color/shadow in
	 * theme.json (settings.custom.hover) when a given field is left unset.
	 * Tablet/mobile overrides only take effect on the front end via the
	 * `@media` rules generated in includes/core/responsive_styles.php,
	 * since a plain inline style/custom property can't vary by viewport.
	 */
	var HOVER_BLOCKS = ['core/group', 'core/columns', 'core/column'];

	var HOVER_FIELDS = [
		{ key: 'text', cssVar: '--omega-hover-text-color', label: __('Hover Text Color', 'omega-design') },
		{ key: 'background', cssVar: '--omega-hover-bg-color', label: __('Hover Background Color', 'omega-design') },
		{ key: 'border', cssVar: '--omega-hover-border-color', label: __('Hover Border Color', 'omega-design') }
	];

	function getHoverColors(attributes) {
		return (attributes.style && attributes.style.omegaHover) || {};
	}

	function setHoverColor(props, key, value) {
		var style = props.attributes.style || {};
		var hover = Object.assign({}, style.omegaHover);

		if (value !== undefined && value !== null && value !== '') {
			hover[key] = value;
		} else {
			delete hover[key];
		}

		var nextStyle = Object.assign({}, style);
		if (Object.keys(hover).length) {
			nextStyle.omegaHover = hover;
		} else {
			delete nextStyle.omegaHover;
		}

		props.setAttributes({ style: nextStyle });
	}

	var DEFAULT_HOVER_SHADOW_COLOR = 'rgba(0, 0, 0, 0.35)';

	function getHoverWrapperProps(attributes) {
		var hover = getHoverColors(attributes);
		var style = {};

		HOVER_FIELDS.forEach(function (field) {
			if (hover[field.key]) {
				style[field.cssVar] = hover[field.key];
			}
		});

		if (hover.shadowSize) {
			var offsetX = hover.shadowOffsetX || 0;
			var offsetY = hover.shadowOffsetY !== undefined ? hover.shadowOffsetY : Math.round(hover.shadowSize / 3);
			style['--omega-hover-shadow'] = offsetX + 'px ' + offsetY + 'px ' + hover.shadowSize + 'px ' + (hover.shadowColor || DEFAULT_HOVER_SHADOW_COLOR);
		}

		if (!Object.keys(style).length) {
			return null;
		}

		return { className: 'has-omega-hover-color', style: style };
	}

	function getResponsiveHoverStyle(attributes, device) {
		var hover = getHoverColors(attributes);
		var style = {};

		HOVER_FIELDS.forEach(function (field) {
			var value = resolveResponsiveValue(hover, field.key, device);
			if (value) { style[field.cssVar] = value; }
		});

		var shadowSize = resolveResponsiveValue(hover, 'shadowSize', device);
		if (shadowSize) {
			var offsetX = resolveResponsiveValue(hover, 'shadowOffsetX', device) || 0;
			var offsetYValue = resolveResponsiveValue(hover, 'shadowOffsetY', device);
			var offsetY = offsetYValue !== undefined ? offsetYValue : Math.round(shadowSize / 3);
			var shadowColor = resolveResponsiveValue(hover, 'shadowColor', device) || DEFAULT_HOVER_SHADOW_COLOR;
			style['--omega-hover-shadow'] = offsetX + 'px ' + offsetY + 'px ' + shadowSize + 'px ' + shadowColor;
		}

		return style;
	}

	var withHoverColorControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (HOVER_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var hover = getHoverColors(props.attributes);

			function renderDeviceFields(device) {
				var colorFields = HOVER_FIELDS.concat([
					{ key: 'shadowColor', label: __('Hover Shadow Color', 'omega-design') }
				]);

				var colorControls = colorFields.map(function (field) {
					var key = fieldKeyForDevice(field.key, device);
					return createElement(
						BaseControl,
						{ key: key, label: field.label },
						createElement(ColorPalette, {
							value: hover[key],
							onChange: function (value) {
								setHoverColor(props, key, value);
							}
						})
					);
				});

				var sizeKey = fieldKeyForDevice('shadowSize', device);
				var offsetXKey = fieldKeyForDevice('shadowOffsetX', device);
				var offsetYKey = fieldKeyForDevice('shadowOffsetY', device);
				var defaultOffsetY = Math.round((hover[sizeKey] || 0) / 3);

				var shadowControls = [
					createElement(RangeControl, {
						key: sizeKey,
						label: __('Shadow Size', 'omega-design'),
						help: __('Blur radius in pixels. Set to 0 to disable the hover shadow.', 'omega-design'),
						value: hover[sizeKey] || 0,
						min: 0,
						max: 60,
						step: 2,
						allowReset: true,
						onChange: function (value) {
							setHoverColor(props, sizeKey, value);
						}
					}),
					createElement(RangeControl, {
						key: offsetXKey,
						label: __('Shadow Horizontal Offset', 'omega-design'),
						help: __('Shifts the shadow left (negative) or right (positive) in pixels.', 'omega-design'),
						value: hover[offsetXKey] || 0,
						min: -60,
						max: 60,
						step: 2,
						allowReset: true,
						onChange: function (value) {
							setHoverColor(props, offsetXKey, value);
						}
					}),
					createElement(RangeControl, {
						key: offsetYKey,
						label: __('Shadow Vertical Offset', 'omega-design'),
						help: __('Shifts the shadow up (negative) or down (positive) in pixels. Defaults to a third of the shadow size.', 'omega-design'),
						value: hover[offsetYKey] !== undefined ? hover[offsetYKey] : defaultOffsetY,
						min: -60,
						max: 60,
						step: 2,
						allowReset: true,
						onChange: function (value) {
							setHoverColor(props, offsetYKey, value);
						}
					})
				];

				return createElement(Fragment, { key: device }, colorControls.concat(shadowControls));
			}

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{ group: 'styles' },
					createElement(
						PanelBody,
						{ title: __('Hover Colors & Shadow', 'omega-design'), initialOpen: false },
						createElement(TabPanel, { tabs: DEVICE_TABS }, function (tab) {
							return renderDeviceFields(tab.name);
						})
					)
				)
			);
		};
	}, 'withHoverColorControls');

	if (ColorPalette && TabPanel) {
		addFilter('editor.BlockEdit', 'omega-design/hover-color-controls', withHoverColorControls);
	}

	/**
	 * Mirrors the hover colors for the currently active device preview
	 * (Desktop/Tablet/Mobile, from the editor's own preview toggle) onto
	 * the block's wrapper element in the editor canvas (as CSS custom
	 * properties + a class), so hovering the block in the editor previews
	 * the same effect the front end will render at that device size.
	 */
	var withHoverColorStyleEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (HOVER_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var style = getResponsiveHoverStyle(props.attributes, getCurrentDeviceType());

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			var existingStyle = (props.wrapperProps && props.wrapperProps.style) || {};
			var existingClassName = (props.wrapperProps && props.wrapperProps.className) || '';

			var wrapperProps = Object.assign({}, props.wrapperProps, {
				style: Object.assign({}, existingStyle, style),
				className: (existingClassName + ' has-omega-hover-color').trim()
			});

			return createElement(BlockListBlock, Object.assign({}, props, { wrapperProps: wrapperProps }));
		};
	}, 'withHoverColorStyleEditor');

	addFilter('editor.BlockListBlock', 'omega-design/hover-color-style-editor', withHoverColorStyleEditor);

	/**
	 * Bakes the same hover colors into the saved block markup, so they
	 * render on the front end without needing a render_block PHP filter.
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/hover-color-style-save', function (extraProps, blockType, attributes) {
		if (HOVER_BLOCKS.indexOf(blockType.name) === -1) {
			return extraProps;
		}

		var hoverProps = getHoverWrapperProps(attributes);
		if (!hoverProps) {
			return extraProps;
		}

		extraProps.style = Object.assign({}, extraProps.style, hoverProps.style);
		extraProps.className = ((extraProps.className || '') + ' ' + hoverProps.className).trim();

		return extraProps;
	});

	/**
	 * Adds a "Text Alignment" panel (Left/Center/Right/Justify), with
	 * Desktop/Tablet/Mobile variants, to the same Inspector Styles group,
	 * for Group/Row/Stack/Grid, Columns and Column blocks. Stored under
	 * attributes.style.omegaAlign - a custom key nested in the same "style"
	 * attribute core already auto-registers for these blocks, following the
	 * same device-prefixed key convention as Hover Colors and Custom Size
	 * (e.g. 'textAlign' -> 'tabletTextAlign' -> 'mobileTextAlign').
	 *
	 * Unlike Hover Colors, this has no `:hover` involved - the desktop value
	 * is a plain inline `text-align` style, baked into the saved markup the
	 * same way Custom Size's width/height are. Tablet/mobile overrides are
	 * generated in includes/core/responsive_styles.php as real `@media`
	 * rules, again since a plain inline style can't vary by viewport.
	 */
	var ALIGN_BLOCKS = HOVER_BLOCKS;

	var ALIGN_OPTIONS = [
		{ label: __('Left', 'omega-design'), value: 'left' },
		{ label: __('Center', 'omega-design'), value: 'center' },
		{ label: __('Right', 'omega-design'), value: 'right' },
		{ label: __('Justify', 'omega-design'), value: 'justify' }
	];

	function getTextAlign(attributes) {
		return (attributes.style && attributes.style.omegaAlign) || {};
	}

	function setTextAlign(props, key, value) {
		var style = props.attributes.style || {};
		var align = Object.assign({}, style.omegaAlign);

		if (value) {
			align[key] = value;
		} else {
			delete align[key];
		}

		var nextStyle = Object.assign({}, style);
		if (Object.keys(align).length) {
			nextStyle.omegaAlign = align;
		} else {
			delete nextStyle.omegaAlign;
		}

		props.setAttributes({ style: nextStyle });
	}

	function getResponsiveAlignStyle(attributes, device) {
		var align = getTextAlign(attributes);
		var value = resolveResponsiveValue(align, 'textAlign', device);

		return value ? { textAlign: value } : {};
	}

	var withTextAlignControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (ALIGN_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var align = getTextAlign(props.attributes);

			function renderDeviceFields(device) {
				var key = fieldKeyForDevice('textAlign', device);
				var current = align[key];

				var buttons = ALIGN_OPTIONS.map(function (option) {
					var isActive = current === option.value;
					return createElement(
						Button,
						{
							key: option.value,
							variant: isActive ? 'primary' : 'secondary',
							isPressed: isActive,
							onClick: function () {
								setTextAlign(props, key, isActive ? undefined : option.value);
							}
						},
						option.label
					);
				});

				return createElement(ButtonGroup, { key: device }, buttons);
			}

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{ group: 'styles' },
					createElement(
						PanelBody,
						{ title: __('Text Alignment', 'omega-design'), initialOpen: false },
						createElement(TabPanel, { tabs: DEVICE_TABS }, function (tab) {
							return renderDeviceFields(tab.name);
						})
					)
				)
			);
		};
	}, 'withTextAlignControl');

	if (TabPanel) {
		addFilter('editor.BlockEdit', 'omega-design/text-align-control', withTextAlignControl);
	}

	/**
	 * Mirrors the text alignment for the currently active device preview
	 * onto the block's wrapper element in the editor canvas.
	 */
	var withTextAlignStyleEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (ALIGN_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var style = getResponsiveAlignStyle(props.attributes, getCurrentDeviceType());

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			var wrapperProps = Object.assign({}, props.wrapperProps, {
				style: Object.assign({}, props.wrapperProps && props.wrapperProps.style, style)
			});

			return createElement(BlockListBlock, Object.assign({}, props, { wrapperProps: wrapperProps }));
		};
	}, 'withTextAlignStyleEditor');

	addFilter('editor.BlockListBlock', 'omega-design/text-align-style-editor', withTextAlignStyleEditor);

	/**
	 * Bakes the desktop text alignment into the saved block markup, so it
	 * renders on the front end without needing a render_block PHP filter.
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/text-align-style-save', function (extraProps, blockType, attributes) {
		if (ALIGN_BLOCKS.indexOf(blockType.name) === -1) {
			return extraProps;
		}

		var align = getTextAlign(attributes);
		if (!align.textAlign) {
			return extraProps;
		}

		extraProps.style = Object.assign({}, extraProps.style, { textAlign: align.textAlign });

		return extraProps;
	});

	/**
	 * Adds a "Mega Menu" panel to the Inspector for Navigation Link and
	 * Navigation Submenu blocks, letting an editor attach one of the site's
	 * "Mega Menu" posts (Omega Design > Mega Menus in wp-admin - its own
	 * title + block-editor content + Custom CSS, not a generic Pattern) as
	 * a hover/click panel for that nav item - no label-matching, no PHP
	 * editing. "Mega Menu" is also registered as its own insertable item
	 * type, so it can be added directly from the Navigation block's own
	 * "+" inserter, the same way core's "Custom Link" is.
	 *
	 * The choice is stored as a plain `megaMenuPattern` attribute (format
	 * "mega_menu:{post_id}"). These blocks are dynamic (rendered via PHP on
	 * every request, no save() markup), so - exactly like the
	 * `omegaHover`/`dimensions` keys responsive_styles.php already reads off
	 * Group/Columns without any server-side attribute registration -
	 * includes/customizer/megamenu.php reads this straight off
	 * $block['attrs']['megaMenuPattern'] with no schema wiring needed there.
	 */
	var MEGAMENU_BLOCKS = ['core/navigation-link', 'core/navigation-submenu'];

	addFilter('blocks.registerBlockType', 'omega-design/megamenu-attribute', function (settings, name) {
		if (MEGAMENU_BLOCKS.indexOf(name) === -1) {
			return settings;
		}

		settings.attributes = Object.assign({}, settings.attributes, {
			megaMenuPattern: { type: 'string', default: '' }
		});

		return settings;
	});

	if (wp.blocks.registerBlockVariation) {
		wp.blocks.registerBlockVariation('core/navigation-link', {
			name: 'omega-design-mega-menu',
			title: __('Mega Menu', 'omega-design'),
			description: __('A navigation item that opens one of your Mega Menus.', 'omega-design'),
			icon: 'grid-view',
			attributes: { label: __('Mega Menu', 'omega-design') },
			isActive: function (blockAttributes) {
				return !!blockAttributes.megaMenuPattern;
			},
			scope: ['inserter']
		});
	}

	var ComboboxControl = wp.components.ComboboxControl;
	var useSelect = wp.data.useSelect;

	function useMegaMenuOptions() {
		return useSelect(function (select) {
			var core = select('core');
			var options = [{ label: __('None', 'omega-design'), value: '' }];

			var menus = core.getEntityRecords('postType', 'mega_menu', {
				per_page: -1,
				status: 'publish',
				orderby: 'title',
				order: 'asc'
			}) || [];

			menus.forEach(function (record) {
				options.push({
					label: record.title && record.title.raw ? record.title.raw : record.slug,
					value: 'mega_menu:' + record.id
				});
			});

			return options;
		}, []);
	}

	var withMegaMenuControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (MEGAMENU_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var options = useMegaMenuOptions();

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{},
					createElement(
						PanelBody,
						{ title: __('Mega Menu', 'omega-design'), initialOpen: false },
						createElement(ComboboxControl, {
							label: __('Mega Menu', 'omega-design'),
							help: __('Attach one of your Mega Menus as a hover/click panel for this nav item. Manage them under Omega Design > Mega Menus in wp-admin.', 'omega-design'),
							value: props.attributes.megaMenuPattern || '',
							options: options,
							onChange: function (value) {
								props.setAttributes({ megaMenuPattern: value || '' });
							}
						})
					)
				)
			);
		};
	}, 'withMegaMenuControl');

	if (ComboboxControl) {
		addFilter('editor.BlockEdit', 'omega-design/megamenu-control', withMegaMenuControl);
	}
})(window.wp);
