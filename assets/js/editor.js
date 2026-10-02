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

	/* ── Shared building blocks for every panel below ───────────────── */

	// Tablet and Mobile only - panels whose Desktop values stay on core's
	// own controls.
	var SMALL_DEVICE_TABS = DEVICE_TABS.slice(1);

	// What counts as "unset" (key removed rather than stored) differs per
	// control and is part of the saved attribute shape, so each setter
	// below names the rule it has always used.
	function isFalsy(value) {
		return !value;
	}

	function isBlank(value) {
		return value === undefined || value === null || value === '';
	}

	function isBlankOrFalse(value) {
		return isBlank(value) || value === false;
	}

	/** A custom group nested in a block's `style` attribute (e.g. style.omegaHover), or {}. */
	function getStyleGroup(attributes, group) {
		return (attributes.style && attributes.style[group]) || {};
	}

	/** A copy of `style` with `group` set to `values` - or removed once values is empty. */
	function withStyleGroup(style, group, values) {
		var nextStyle = Object.assign({}, style);
		if (Object.keys(values).length) {
			nextStyle[group] = values;
		} else {
			delete nextStyle[group];
		}
		return nextStyle;
	}

	/** Sets one key of style[group] - or removes it when isEmpty(value). */
	function setStyleGroupValue(props, group, key, value, isEmpty) {
		var style = props.attributes.style || {};
		var values = Object.assign({}, style[group]);

		if (isEmpty(value)) {
			delete values[key];
		} else {
			values[key] = value;
		}

		props.setAttributes({ style: withStyleGroup(style, group, values) });
	}

	/**
	 * A copy of a per-device map ({desktop:{}, tablet:{}, mobile:{}}) with
	 * one device's key set - or removed when isEmpty(value), dropping the
	 * device itself once it has nothing left.
	 */
	function withDeviceValue(all, device, key, value, isEmpty) {
		var next = Object.assign({}, all);
		var settings = Object.assign({}, next[device]);

		if (isEmpty(value)) {
			delete settings[key];
		} else {
			settings[key] = value;
		}

		if (Object.keys(settings).length) {
			next[device] = settings;
		} else {
			delete next[device];
		}

		return next;
	}

	/** The object, or undefined once it has no keys (so the attribute is dropped). */
	function objectOrUndefined(object) {
		return Object.keys(object).length ? object : undefined;
	}

	/** Adds attribute definitions to a block type's registration settings. */
	function addBlockAttributes(settings, attributes) {
		settings.attributes = Object.assign({}, settings.attributes, attributes);
		return settings;
	}

	/** The block's own edit UI plus one Inspector panel. */
	function inspectorPanel(BlockEdit, props, inspectorProps, panelProps, content) {
		return createElement(
			Fragment,
			{},
			createElement(BlockEdit, props),
			createElement(
				InspectorControls,
				inspectorProps,
				createElement(PanelBody, panelProps, content)
			)
		);
	}

	/** Device tabs, each rendering renderDevice(deviceName). */
	function deviceTabPanel(tabs, renderDevice) {
		return createElement(TabPanel, { tabs: tabs }, function (tab) {
			return renderDevice(tab.name);
		});
	}

	/** One option of a primary/secondary button group. */
	function optionButton(key, label, isActive, onClick) {
		return createElement(
			Button,
			{
				key: key,
				variant: isActive ? 'primary' : 'secondary',
				isPressed: isActive,
				onClick: onClick
			},
			label
		);
	}

	/** Editor canvas block with a scoped <style> rendered just before it. */
	function withPreviewStyle(BlockListBlock, props, css) {
		return createElement(
			Fragment,
			{},
			createElement('style', {}, css),
			createElement(BlockListBlock, props)
		);
	}

	/** Editor canvas block with `overrides` merged into its wrapper props. */
	function withWrapperOverrides(BlockListBlock, props, overrides) {
		var wrapperProps = Object.assign({}, props.wrapperProps, overrides);
		return createElement(BlockListBlock, Object.assign({}, props, { wrapperProps: wrapperProps }));
	}

	/** The wrapper's existing inline style with `style` added. */
	function mergedWrapperStyle(props, style) {
		return Object.assign({}, props.wrapperProps && props.wrapperProps.style, style);
	}

	/** The wrapper's existing class name with `className` appended. */
	function mergedWrapperClassName(props, className) {
		var existingClassName = (props.wrapperProps && props.wrapperProps.className) || '';
		return (existingClassName + ' ' + className).trim();
	}

	/** Adds `style` to a block's saved wrapper props. */
	function addSaveStyle(extraProps, style) {
		extraProps.style = Object.assign({}, extraProps.style, style);
		return extraProps;
	}

	/**
	 * Canvas preview CSS for the active Tablet/Mobile preview: the tablet
	 * rules, plus the mobile ones on top when previewing mobile - the same
	 * cascade the front end's `@media` rules produce.
	 */
	function deviceCascadeCss(all, device, buildRules) {
		var css = buildRules(all.tablet || {});
		if (device === 'mobile') {
			css += buildRules(all.mobile || {});
		}
		return css;
	}

	/**
	 * Top/Right/Bottom/Left size fields for one box value (padding, margin).
	 * onChangeBox receives the whole box, or '' once every side is cleared.
	 */
	function boxControl(key, label, box, onChangeBox) {
		return createElement(BaseControl, { key: key, label: label, __nextHasNoMarginBottom: true },
			createElement('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } },
				['top', 'right', 'bottom', 'left'].map(function (side) {
					return createElement(UnitControl, {
						key: side,
						label: side.charAt(0).toUpperCase() + side.slice(1),
						units: SIZE_UNITS,
						value: box[side] || '',
						onChange: function (value) {
							var next = Object.assign({}, box);
							if (value) { next[side] = value; } else { delete next[side]; }
							onChangeBox(Object.keys(next).length ? next : '');
						}
					});
				})
			)
		);
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
				return optionButton(option.label, option.label, currentAlign === option.value, function () {
					props.setAttributes({ align: option.value });
				});
			});

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Width', 'omega-design'), initialOpen: true },
				createElement(ButtonGroup, {}, buttons));
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
		return getStyleGroup(attributes, 'dimensions');
	}

	function setCustomSize(props, key, value) {
		setStyleGroupValue(props, 'dimensions', key, value, isFalsy);
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

	/**
	 * A plain Group (flow/constrained layout) has no vertical alignment
	 * control of its own, so once it's given a Height its content always
	 * sits at the top. "Content Position" fills that gap - it's the Desktop
	 * tab of the Responsive Layout panel below, whose Tablet/Mobile
	 * "Content alignment" overrides it per device. Row/Stack/Grid already
	 * have core's own alignment controls and are left alone. Stored
	 * as style.dimensions.omegaVAlign and applied as an .omega-valign-*
	 * class - on the front end by includes/core/responsive_styles.php, and
	 * in the editor canvas by withCustomSizeStyleEditor below - never in
	 * save(), so existing blocks' saved markup stays valid.
	 */
	var VALIGN_OPTIONS = [
		{ value: '', label: __('Top', 'omega-design') },
		{ value: 'center', label: __('Middle', 'omega-design') },
		{ value: 'bottom', label: __('Bottom', 'omega-design') }
	];

	function isFlexOrGridLayout(attributes) {
		var type = attributes.layout && attributes.layout.type;
		return type === 'flex' || type === 'grid';
	}

	function getVAlignClass(attributes) {
		var value = getCustomSize(attributes).omegaVAlign;
		return (value === 'center' || value === 'bottom') && !isFlexOrGridLayout(attributes)
			? 'omega-valign-' + value
			: '';
	}

	function renderContentPosition(props, size) {
		var current = size.omegaVAlign || '';

		return createElement(
			BaseControl,
			{ key: 'omega-valign', label: __('Content Position', 'omega-design'), __nextHasNoMarginBottom: true },
			createElement(
				ButtonGroup,
				{},
				VALIGN_OPTIONS.map(function (option) {
					return optionButton(option.value || 'top', option.label, current === option.value, function () {
						setCustomSize(props, 'omegaVAlign', option.value);
					});
				})
			)
		);
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

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Custom Size', 'omega-design'), initialOpen: false },
				deviceTabPanel(DEVICE_TABS, renderDeviceFields));
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
			var valignClass = props.name === 'core/group' ? getVAlignClass(props.attributes) : '';

			if (!Object.keys(style).length && !valignClass) {
				return createElement(BlockListBlock, props);
			}

			return withWrapperOverrides(BlockListBlock, props, {
				className: mergedWrapperClassName(props, valignClass),
				style: mergedWrapperStyle(props, style)
			});
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

		var style = {};
		if (size.width) { style.width = size.width; }
		if (size.height) { style.height = size.height; }

		return addSaveStyle(extraProps, style);
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

	/**
	 * core/icon gets the same Hover Colors & Shadow panel as the blocks
	 * above, but kept in its own array rather than pushed into HOVER_BLOCKS -
	 * that array is also reused below as ALIGN_BLOCKS for Text Alignment,
	 * which doesn't apply to a single icon. isHoverEligible() below is the
	 * one place both arrays are checked together.
	 *
	 * core/icon is a dynamic block (PHP render_callback, see
	 * includes/core/hooks.php's render_block_core_icon() handling) - unlike
	 * Group/Columns/Column, the front end never uses this block's save()
	 * output, so the 'blocks.getSaveContent.extraProps' filter below can't
	 * put the hover class/vars on the rendered markup the way it does for
	 * those blocks. The PHP side re-derives the same class/vars from
	 * attributes.style.omegaHover independently for the actual front-end
	 * output; this filter still runs for core/icon anyway so the editor's
	 * own validation copy of the saved markup stays consistent.
	 */
	var ICON_HOVER_BLOCKS = ['core/icon'];

	function isHoverEligible(name) {
		return HOVER_BLOCKS.indexOf(name) !== -1 || ICON_HOVER_BLOCKS.indexOf(name) !== -1;
	}

	var HOVER_FIELDS = [
		{ key: 'text', cssVar: '--omega-hover-text-color', label: __('Hover Text Color', 'omega-design') },
		{ key: 'background', cssVar: '--omega-hover-bg-color', label: __('Hover Background Color', 'omega-design') },
		{ key: 'border', cssVar: '--omega-hover-border-color', label: __('Hover Border Color', 'omega-design') }
	];

	function getHoverColors(attributes) {
		return getStyleGroup(attributes, 'omegaHover');
	}

	// Keeps 0 (a shadow size/offset of zero is a real value).
	function setHoverColor(props, key, value) {
		setStyleGroupValue(props, 'omegaHover', key, value, isBlank);
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
			if (!isHoverEligible(props.name) || !props.isSelected) {
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

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Hover Colors & Shadow', 'omega-design'), initialOpen: false },
				deviceTabPanel(DEVICE_TABS, renderDeviceFields));
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
			if (!isHoverEligible(props.name)) {
				return createElement(BlockListBlock, props);
			}

			var style = getResponsiveHoverStyle(props.attributes, getCurrentDeviceType());

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			return withWrapperOverrides(BlockListBlock, props, {
				style: mergedWrapperStyle(props, style),
				className: mergedWrapperClassName(props, 'has-omega-hover-color')
			});
		};
	}, 'withHoverColorStyleEditor');

	addFilter('editor.BlockListBlock', 'omega-design/hover-color-style-editor', withHoverColorStyleEditor);

	/**
	 * Bakes the same hover colors into the saved block markup, so they
	 * render on the front end without needing a render_block PHP filter.
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/hover-color-style-save', function (extraProps, blockType, attributes) {
		if (!isHoverEligible(blockType.name)) {
			return extraProps;
		}

		var hoverProps = getHoverWrapperProps(attributes);
		if (!hoverProps) {
			return extraProps;
		}

		addSaveStyle(extraProps, hoverProps.style);
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
		return getStyleGroup(attributes, 'omegaAlign');
	}

	function setTextAlign(props, key, value) {
		setStyleGroupValue(props, 'omegaAlign', key, value, isFalsy);
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
					return optionButton(option.value, option.label, isActive, function () {
						setTextAlign(props, key, isActive ? undefined : option.value);
					});
				});

				return createElement(ButtonGroup, { key: device }, buttons);
			}

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Text Alignment', 'omega-design'), initialOpen: false },
				deviceTabPanel(DEVICE_TABS, renderDeviceFields));
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

			return withWrapperOverrides(BlockListBlock, props, { style: mergedWrapperStyle(props, style) });
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

		return addSaveStyle(extraProps, { textAlign: align.textAlign });
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

		return addBlockAttributes(settings, {
			megaMenuPattern: { type: 'string', default: '' }
		});
	});

	/**
	 * Core gives Group a Background image (with Focal point, Fixed
	 * background, Size and Repeat) but not Column/Columns. Opting them into
	 * the same core support reuses that exact panel; the server side
	 * (includes/core/blocks.php add_background_support()) opts them in too,
	 * so core renders the image as an inline style at render time - nothing
	 * is added to save(), so existing blocks stay valid.
	 */
	var BACKGROUND_BLOCKS = ['core/column', 'core/columns'];

	addFilter('blocks.registerBlockType', 'omega-design/column-background', function (settings, name) {
		if (BACKGROUND_BLOCKS.indexOf(name) === -1) {
			return settings;
		}

		settings.supports = Object.assign({}, settings.supports, {
			background: Object.assign({}, settings.supports && settings.supports.background, {
				backgroundImage: true,
				backgroundSize: true,
				__experimentalDefaultControls: { backgroundImage: true }
			})
		});

		return settings;
	});

	/**
	 * "Responsive Background" - a different background image (and focal
	 * point) for Tablet and Mobile on Group/Row/Stack/Grid, Columns and
	 * Column. Desktop keeps using core's own Background image panel; these
	 * only replace it below the tablet/mobile breakpoints, with the same
	 * mobile -> tablet -> desktop cascade as every other responsive control
	 * here. Stored under attributes.style.omegaBackground
	 * ({tabletImage:{id,url}, tabletPosition, mobileImage, mobilePosition})
	 * and output as `@media` rules by includes/core/responsive_styles.php -
	 * nothing is added to save(), so existing blocks stay valid.
	 */
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var FocalPointPicker = wp.components.FocalPointPicker;
	var RESPONSIVE_BG_BLOCKS = ['core/group', 'core/columns', 'core/column'];
	function getResponsiveBackground(attributes) {
		return getStyleGroup(attributes, 'omegaBackground');
	}

	/** Merges several keys at once (an image and its focal point change together). */
	function setResponsiveBackground(props, changes) {
		var style = props.attributes.style || {};
		var background = Object.assign({}, style.omegaBackground, changes);

		Object.keys(background).forEach(function (key) {
			if (background[key] === undefined || background[key] === '') {
				delete background[key];
			}
		});

		props.setAttributes({ style: withStyleGroup(style, 'omegaBackground', background) });
	}

	function positionToPoint(position) {
		var parts = String(position || '50% 50%').split(' ');
		return {
			x: (parseFloat(parts[0]) || 50) / 100,
			y: (parseFloat(parts[1]) || 50) / 100
		};
	}

	function pointToPosition(point) {
		return Math.round(point.x * 100) + '% ' + Math.round(point.y * 100) + '%';
	}

	function renderResponsiveBackgroundFields(props, device) {
		var background = getResponsiveBackground(props.attributes);
		var imageKey = device + 'Image';
		var positionKey = device + 'Position';
		var image = background[imageKey];
		var noneKey = device + 'None';
		var BgToggle = wp.components.ToggleControl;
		var noneToggle = BgToggle && createElement(BgToggle, {
			key: 'none',
			label: device === 'mobile' ? __('No background image on mobile', 'omega-design') : __('No background image on tablet (and mobile)', 'omega-design'),
			checked: !!background[noneKey],
			__nextHasNoMarginBottom: true,
			onChange: function (value) {
				var change = {};
				change[noneKey] = value || undefined;
				if (value) {
					change[imageKey] = undefined;
					change[positionKey] = undefined;
				}
				setResponsiveBackground(props, change);
			}
		});

		if (background[noneKey]) {
			return createElement('div', { key: device, style: { paddingTop: '12px' } }, noneToggle);
		}

		function onSelect(media) {
			var change = {};
			change[imageKey] = { id: media.id, url: media.url };
			setResponsiveBackground(props, change);
		}

		function onRemove() {
			var change = {};
			change[imageKey] = undefined;
			change[positionKey] = undefined;
			setResponsiveBackground(props, change);
		}

		return createElement(
			'div',
			{ key: device, style: { paddingTop: '12px' } },
			createElement(
				MediaUploadCheck,
				{},
				createElement(MediaUpload, {
					onSelect: onSelect,
					allowedTypes: ['image'],
					value: image && image.id,
					render: function (upload) {
						return createElement(
							'div',
							{ style: { display: 'flex', gap: '8px', marginBottom: '12px' } },
							createElement(Button, { variant: 'secondary', onClick: upload.open },
								image ? __('Replace image', 'omega-design') : __('Choose image', 'omega-design')),
							image && createElement(Button, { variant: 'tertiary', isDestructive: true, onClick: onRemove },
								__('Remove', 'omega-design'))
						);
					}
				})
			),
			image && FocalPointPicker && createElement(FocalPointPicker, {
				label: __('Focal point', 'omega-design'),
				url: image.url,
				value: positionToPoint(background[positionKey]),
				onChange: function (point) {
					var change = {};
					change[positionKey] = pointToPosition(point);
					setResponsiveBackground(props, change);
				}
			}),
			!image && createElement('p', { style: { margin: '0 0 12px', color: '#757575', fontSize: '12px' } },
				device === 'mobile'
					? __('No mobile image - uses the tablet image, or the desktop one.', 'omega-design')
					: __('No tablet image - uses the desktop image.', 'omega-design')),
			noneToggle
		);
	}

	var withResponsiveBackgroundControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (RESPONSIVE_BG_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Responsive Background', 'omega-design'), initialOpen: false },
				deviceTabPanel(SMALL_DEVICE_TABS, function (device) {
					return renderResponsiveBackgroundFields(props, device);
				}));
		};
	}, 'withResponsiveBackgroundControl');

	/**
	 * Editor canvas preview for the active Tablet/Mobile device preview. A
	 * scoped <style> with !important rather than wrapperProps.style, since
	 * core's own background support writes its inline style onto the same
	 * element and would otherwise win.
	 */
	var withResponsiveBackgroundPreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (RESPONSIVE_BG_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var device = getCurrentDeviceType();
			if (device === 'desktop') {
				return createElement(BlockListBlock, props);
			}

			var background = getResponsiveBackground(props.attributes);
			// Same cascade as the front end: the most specific device that
			// sets either an image or "No image" wins.
			var chain = device === 'mobile' ? ['mobile', 'tablet'] : ['tablet'];
			var image = null;
			var isNone = false;
			for (var i = 0; i < chain.length; i++) {
				if (background[chain[i] + 'Image']) { image = background[chain[i] + 'Image']; break; }
				if (background[chain[i] + 'None']) { isNone = true; break; }
			}

			var css;
			if (isNone) {
				css = '#block-' + props.clientId + '{background-image:none !important;}';
			} else if (image && image.url) {
				var position = resolveResponsiveValue(background, 'position', device) || '50% 50%';
				css = '#block-' + props.clientId + '{' +
					'background-image:url("' + encodeURI(image.url).replace(/"/g, '%22') + '") !important;' +
					'background-position:' + position + ' !important;' +
					'background-size:cover !important;}';
			} else {
				return createElement(BlockListBlock, props);
			}

			return withPreviewStyle(BlockListBlock, props, css);
		};
	}, 'withResponsiveBackgroundPreview');

	if (MediaUpload && MediaUploadCheck && TabPanel) {
		addFilter('editor.BlockEdit', 'omega-design/responsive-background-control', withResponsiveBackgroundControl);
		addFilter('editor.BlockListBlock', 'omega-design/responsive-background-preview', withResponsiveBackgroundPreview);
	}

	/**
	 * "Responsive Layout" - Tablet/Mobile overrides for what core's own
	 * layout controls set on desktop: justification, vertical alignment,
	 * orientation, wrap and gap (Row/Stack), justification (Group), column
	 * count (Grid), stacking/alignment/gap (Columns), width/alignment
	 * (Column), plus padding and "Hide on this device" for all of them.
	 * Desktop stays on core's controls. Stored under
	 * attributes.style.omegaResponsive ({tablet:{...}, mobile:{...}}) and
	 * output as `@media` rules by includes/core/responsive_styles.php
	 * build_layout_rules() - nothing is added to save(), so existing
	 * blocks stay valid. Mobile inherits Tablet unless set separately.
	 */
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var RESPONSIVE_LAYOUT_BLOCKS = ['core/group', 'core/columns', 'core/column'];
	var INHERIT_OPTION = { value: '', label: __('Same as larger screen', 'omega-design') };
	var FLEX_VALUES = {
		left: 'flex-start', top: 'flex-start', center: 'center', right: 'flex-end',
		bottom: 'flex-end', stretch: 'stretch', 'space-between': 'space-between'
	};

	function getLayoutType(props) {
		var layout = props.attributes.layout || {};
		return layout.type || (props.name === 'core/group' ? 'flow' : '');
	}

	function getResponsiveSettings(attributes, device) {
		return getStyleGroup(attributes, 'omegaResponsive')[device] || {};
	}

	function setResponsiveSetting(props, device, key, value) {
		var style = props.attributes.style || {};
		var all = withDeviceValue(style.omegaResponsive, device, key, value, isBlankOrFalse);
		props.setAttributes({ style: withStyleGroup(style, 'omegaResponsive', all) });
	}

	/**
	 * Same rules as responsive_styles.php build_layout_rules(), for one
	 * device, against the given selector.
	 */
	function buildLayoutRules(settings, name, layout, sel) {
		var self = '';
		var children = '';
		var type = (layout && layout.type) || (name === 'core/group' ? 'flow' : '');

		if (name === 'core/group' && type === 'flex') {
			var orientation = settings.orientation;
			if (orientation) {
				self += 'flex-direction:' + (orientation === 'vertical' ? 'column' : 'row') + ' !important;';
			}
			var isVertical = (orientation || (layout && layout.orientation) || 'horizontal') === 'vertical';
			var justify = settings.justify || (orientation ? 'left' : '');
			var valign = settings.valign || (orientation ? (isVertical ? 'top' : 'center') : '');
			if (justify && FLEX_VALUES[justify]) {
				var justifyValue = isVertical && justify === 'space-between' ? 'flex-start' : FLEX_VALUES[justify];
				self += (isVertical ? 'align-items:' : 'justify-content:') + justifyValue + ' !important;';
			}
			if (valign && FLEX_VALUES[valign]) {
				var valignValue = !isVertical && valign === 'space-between' ? 'stretch' : FLEX_VALUES[valign];
				self += (isVertical ? 'justify-content:' : 'align-items:') + valignValue + ' !important;';
			}
			if (settings.wrap === 'wrap' || settings.wrap === 'nowrap') {
				self += 'flex-wrap:' + settings.wrap + ' !important;';
			}
		}

		if (name === 'core/group' && type === 'grid') {
			if (settings.gridColumns) {
				self += 'grid-template-columns:repeat(' + parseInt(settings.gridColumns, 10) + ',minmax(0,1fr)) !important;';
			}
			var gridMap = { left: 'start', top: 'start', center: 'center', right: 'end', bottom: 'end', stretch: 'stretch' };
			if (gridMap[settings.justify]) {
				self += 'justify-items:' + gridMap[settings.justify] + ' !important;';
			}
			if (gridMap[settings.valign]) {
				self += 'align-items:' + gridMap[settings.valign] + ' !important;';
			}
		}

		if ((name === 'core/group' && (type === 'flow' || type === 'constrained')) || name === 'core/column') {
			var flowJustify = ['left', 'center', 'right'].indexOf(settings.justify) !== -1 ? settings.justify : '';
			var flowValign = ['top', 'center', 'bottom'].indexOf(settings.valign) !== -1 ? settings.valign : '';

			if (type === 'constrained' && flowJustify) {
				var margins = { left: ['0', 'auto'], center: ['auto', 'auto'], right: ['auto', '0'] }[flowJustify];
				children += sel + '>:not(.alignleft):not(.alignright):not(.alignfull){margin-left:' + margins[0] + ' !important;margin-right:' + margins[1] + ' !important;}';
			}

			var useFlexJustify = flowJustify && type !== 'constrained';
			if (flowValign || useFlexJustify) {
				self += 'display:flex !important;flex-direction:column !important;';
				if (flowValign) {
					self += 'justify-content:' + FLEX_VALUES[flowValign] + ' !important;';
				}
				if (name === 'core/column' && flowValign) {
					self += 'align-self:stretch !important;';
				}
				if (useFlexJustify) {
					self += 'align-items:' + FLEX_VALUES[flowJustify] + ' !important;text-align:' + flowJustify + ' !important;';
					children += sel + '>*{max-width:100%;}';
				} else if (type === 'constrained') {
					children += sel + '>*{width:100%;box-sizing:border-box;}';
				}
			}
		}

		if (name === 'core/columns') {
			if (settings.valign && FLEX_VALUES[settings.valign]) {
				children += sel + '>.wp-block-column{align-self:' + FLEX_VALUES[settings.valign] + ' !important;}';
			}
			if (settings.justify && FLEX_VALUES[settings.justify]) {
				self += 'justify-content:' + FLEX_VALUES[settings.justify] + ' !important;';
			}
			if (settings.stack === 'stack') {
				self += 'flex-wrap:wrap !important;';
				children += sel + '>.wp-block-column{flex-basis:100% !important;flex-grow:0 !important;}';
			} else if (settings.stack === 'row') {
				self += 'flex-wrap:nowrap !important;';
				children += sel + '>.wp-block-column{flex-basis:0 !important;flex-grow:1 !important;min-width:0;}';
			}
		}

		if (name === 'core/column') {
			if (settings.width) {
				self += 'flex-basis:' + settings.width + ' !important;flex-grow:0 !important;max-width:100%;';
			}
		}

		if (settings.gap && (name === 'core/group' || name === 'core/columns')) {
			self += 'gap:' + settings.gap + ' !important;';
		}

		if (settings.padding) {
			['top', 'right', 'bottom', 'left'].forEach(function (side) {
				if (settings.padding[side]) {
					self += 'padding-' + side + ':' + settings.padding[side] + ' !important;';
				}
			});
		}

		if (settings.hide) {
			// Faded rather than removed in the editor, so the block can
			// still be selected and un-hidden.
			self += 'opacity:0.35 !important;outline:1px dashed #999;';
		}

		var selfSelector = name === 'core/column' ? '.wp-block-columns>' + sel : sel;

		return (self ? selfSelector + '{' + self + '}' : '') + children;
	}

	function selectField(props, device, settings, key, label, options) {
		return createElement(SelectControl, {
			key: key,
			label: label,
			value: settings[key] || '',
			options: [INHERIT_OPTION].concat(options),
			__nextHasNoMarginBottom: true,
			onChange: function (value) {
				setResponsiveSetting(props, device, key, value);
			}
		});
	}

	function unitField(props, device, settings, key, label) {
		return createElement(UnitControl, {
			key: key,
			label: label,
			units: SIZE_UNITS,
			value: settings[key] || '',
			onChange: function (value) {
				setResponsiveSetting(props, device, key, value);
			}
		});
	}

	/**
	 * Desktop tab: only what core itself has no control for - the plain
	 * Group's Content Position. Everything else on desktop is core's own
	 * layout/dimensions controls.
	 */
	function renderResponsiveLayoutDesktop(props) {
		if (props.name === 'core/group' && !isFlexOrGridLayout(props.attributes)) {
			return createElement('div', { key: 'desktop', style: { display: 'grid', gap: '8px', paddingTop: '12px' } },
				renderContentPosition(props, getCustomSize(props.attributes)),
				createElement('p', { style: { margin: 0, color: '#757575', fontSize: '12px' } },
					__('Moves the content up or down within the block\'s height. Tablet and Mobile use this unless their Content alignment is set.', 'omega-design'))
			);
		}

		return createElement('p', { key: 'desktop', style: { margin: '12px 0 0', color: '#757575', fontSize: '12px' } },
			__('Desktop uses this block\'s normal layout settings. Use the Tablet and Mobile tabs to change them on smaller screens.', 'omega-design'));
	}

	function renderResponsiveLayoutFields(props, device) {
		if (device === 'desktop') {
			return renderResponsiveLayoutDesktop(props);
		}

		var settings = getResponsiveSettings(props.attributes, device);
		var name = props.name;
		var type = getLayoutType(props);
		var fields = [];
		var opt = function (value, label) { return { value: value, label: label }; };
		var justifyOptions = [opt('left', __('Left', 'omega-design')), opt('center', __('Center', 'omega-design')), opt('right', __('Right', 'omega-design'))];
		var valignOptions = [opt('top', __('Top', 'omega-design')), opt('center', __('Middle', 'omega-design')), opt('bottom', __('Bottom', 'omega-design')), opt('stretch', __('Stretch', 'omega-design'))];

		var betweenOption = opt('space-between', __('Space between', 'omega-design'));
		var stretchOption = opt('stretch', __('Stretch', 'omega-design'));
		var noStretch = valignOptions.slice(0, 3);
		var justifyLabel = __('Content justification', 'omega-design');
		var valignLabel = __('Content alignment', 'omega-design');

		if (name === 'core/group' && type === 'flex') {
			fields.push(selectField(props, device, settings, 'orientation', __('Direction', 'omega-design'),
				[opt('horizontal', __('Horizontal (row)', 'omega-design')), opt('vertical', __('Vertical (stack)', 'omega-design'))]));
			fields.push(selectField(props, device, settings, 'justify', justifyLabel, justifyOptions.concat([betweenOption])));
			fields.push(selectField(props, device, settings, 'valign', valignLabel, valignOptions.concat([betweenOption])));
			fields.push(selectField(props, device, settings, 'wrap', __('Wrap', 'omega-design'),
				[opt('wrap', __('Wrap to multiple lines', 'omega-design')), opt('nowrap', __('Keep on one line', 'omega-design'))]));
		}

		if (name === 'core/group' && (type === 'constrained' || type === 'flow')) {
			fields.push(selectField(props, device, settings, 'justify', justifyLabel, justifyOptions));
			fields.push(selectField(props, device, settings, 'valign', valignLabel, noStretch));
		}

		if (name === 'core/group' && type === 'grid') {
			fields.push(selectField(props, device, settings, 'gridColumns', __('Columns', 'omega-design'),
				[1, 2, 3, 4, 5, 6].map(function (n) { return opt(String(n), String(n)); })));
			fields.push(selectField(props, device, settings, 'justify', justifyLabel, justifyOptions.concat([stretchOption])));
			fields.push(selectField(props, device, settings, 'valign', valignLabel, valignOptions));
		}

		if (name === 'core/columns') {
			fields.push(selectField(props, device, settings, 'stack', __('Layout', 'omega-design'),
				[opt('stack', __('Stack columns', 'omega-design')), opt('row', __('Side by side', 'omega-design'))]));
			fields.push(selectField(props, device, settings, 'justify', justifyLabel, justifyOptions.concat([betweenOption])));
			fields.push(selectField(props, device, settings, 'valign', valignLabel, valignOptions));
		}

		if (name === 'core/column') {
			fields.push(unitField(props, device, settings, 'width', __('Width', 'omega-design')));
			fields.push(selectField(props, device, settings, 'justify', justifyLabel, justifyOptions));
			fields.push(selectField(props, device, settings, 'valign', valignLabel, noStretch));
		}

		if ((name === 'core/group' && type !== 'flow') || name === 'core/columns') {
			fields.push(unitField(props, device, settings, 'gap', __('Gap', 'omega-design')));
		}

		fields.push(boxControl('padding', __('Padding', 'omega-design'), settings.padding || {}, function (box) {
			setResponsiveSetting(props, device, 'padding', box);
		}));

		if (ToggleControl) {
			fields.push(createElement(ToggleControl, {
				key: 'hide',
				label: device === 'mobile' ? __('Hide on mobile', 'omega-design') : __('Hide on tablet (and mobile)', 'omega-design'),
				checked: !!settings.hide,
				__nextHasNoMarginBottom: true,
				onChange: function (value) {
					setResponsiveSetting(props, device, 'hide', value);
				}
			}));
		}

		return createElement('div', { key: device, style: { display: 'grid', gap: '16px', paddingTop: '12px' } }, fields);
	}

	var withResponsiveLayoutControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (RESPONSIVE_LAYOUT_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Responsive Layout', 'omega-design'), initialOpen: false },
				deviceTabPanel(DEVICE_TABS, function (device) {
					return renderResponsiveLayoutFields(props, device);
				}));
		};
	}, 'withResponsiveLayoutControl');

	/**
	 * Editor canvas preview, keyed off the editor's own Desktop/Tablet/
	 * Mobile preview toggle rather than `@media` - the canvas can be
	 * narrower than 1024px even in Desktop view (sidebars open), which would
	 * otherwise wrongly apply tablet rules while editing on desktop.
	 */
	var withResponsiveLayoutPreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (RESPONSIVE_LAYOUT_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var device = getCurrentDeviceType();
			var all = (props.attributes.style && props.attributes.style.omegaResponsive) || {};
			if (device === 'desktop' || (!all.tablet && !all.mobile)) {
				return createElement(BlockListBlock, props);
			}

			var sel = '#block-' + props.clientId;
			var css = deviceCascadeCss(all, device, function (settings) {
				return buildLayoutRules(settings, props.name, props.attributes.layout, sel);
			});

			if (!css) {
				return createElement(BlockListBlock, props);
			}

			return withPreviewStyle(BlockListBlock, props, css);
		};
	}, 'withResponsiveLayoutPreview');

	if (SelectControl && TabPanel && UnitControl) {
		addFilter('editor.BlockEdit', 'omega-design/responsive-layout-control', withResponsiveLayoutControl);
		addFilter('editor.BlockListBlock', 'omega-design/responsive-layout-preview', withResponsiveLayoutPreview);
	}

	/**
	 * "Responsive Settings" - Tablet/Mobile text alignment, font size,
	 * width, margin, padding and hide for every other block (Paragraph,
	 * Heading, Image, Buttons, List...). Group/Columns/Column have the
	 * Responsive Layout panel above instead. Stored in a top-level
	 * omegaResponsive attribute registered on every block here and in
	 * includes/core/blocks.php add_responsive_attribute(); output by
	 * includes/core/responsive_styles.php inject_element_styles(). Nothing
	 * is added to save(), so existing blocks stay valid.
	 */
	var RESPONSIVE_EXCLUDED = [
		'core/group', 'core/columns', 'core/column', 'core/freeform',
		'core/block', 'core/template-part', 'core/missing', 'core/pattern'
	];

	addFilter('blocks.registerBlockType', 'omega-design/responsive-attribute', function (settings, name) {
		if (RESPONSIVE_EXCLUDED.indexOf(name) !== -1) {
			return settings;
		}

		return addBlockAttributes(settings, {
			omegaResponsive: { type: 'object' }
		});
	});

	// Only for blocks that actually got the attribute - a plugin block
	// registered before this script ran would silently drop it on save.
	function supportsResponsiveSettings(name) {
		var type = wp.blocks.getBlockType(name);
		return !!(type && type.attributes && type.attributes.omegaResponsive);
	}

	function setElementSetting(props, device, key, value) {
		var all = withDeviceValue(props.attributes.omegaResponsive, device, key, value, isBlankOrFalse);
		props.setAttributes({ omegaResponsive: objectOrUndefined(all) });
	}

	/**
	 * Same rules as responsive_styles.php build_element_rules().
	 */
	function buildElementRules(settings, name, sel) {
		var self = '';
		var extra = '';
		var align = settings.textAlign;

		if (['left', 'center', 'right', 'justify'].indexOf(align) !== -1) {
			self += 'text-align:' + align + ' !important;';
			if ((name === 'core/buttons' || name === 'core/social-links') && align !== 'justify') {
				self += 'justify-content:' + { left: 'flex-start', center: 'center', right: 'flex-end' }[align] + ' !important;';
			}
			if (name === 'core/image' && align !== 'justify') {
				self += 'float:none !important;display:block !important;margin-left:0 !important;margin-right:0 !important;';
			}
		}

		if (settings.fontSize) {
			self += 'font-size:' + settings.fontSize + ' !important;';
		}

		if (settings.width) {
			if (name === 'core/image') {
				extra += sel + ' img{width:' + settings.width + ' !important;max-width:100%;height:auto !important;}';
			} else {
				self += 'width:' + settings.width + ' !important;max-width:100%;';
			}
		}

		['margin', 'padding'].forEach(function (property) {
			var box = settings[property];
			if (box) {
				['top', 'right', 'bottom', 'left'].forEach(function (side) {
					if (box[side]) {
						self += property + '-' + side + ':' + box[side] + ' !important;';
					}
				});
			}
		});

		if (settings.hide) {
			self += 'opacity:0.35 !important;outline:1px dashed #999;';
		}

		return (self ? sel + '{' + self + '}' : '') + extra;
	}

	function boxField(props, device, settings, key, label) {
		return boxControl(key, label, settings[key] || {}, function (box) {
			setElementSetting(props, device, key, box);
		});
	}

	function renderElementFields(props, device) {
		var settings = ((props.attributes.omegaResponsive || {})[device]) || {};
		var alignLabel = props.name === 'core/image' || props.name === 'core/buttons'
			? __('Alignment', 'omega-design')
			: __('Text alignment', 'omega-design');

		return createElement('div', { key: device, style: { display: 'grid', gap: '16px', paddingTop: '12px' } },
			createElement(SelectControl, {
				label: alignLabel,
				value: settings.textAlign || '',
				options: [
					INHERIT_OPTION,
					{ value: 'left', label: __('Left', 'omega-design') },
					{ value: 'center', label: __('Center', 'omega-design') },
					{ value: 'right', label: __('Right', 'omega-design') },
					{ value: 'justify', label: __('Justify', 'omega-design') }
				],
				__nextHasNoMarginBottom: true,
				onChange: function (value) { setElementSetting(props, device, 'textAlign', value); }
			}),
			createElement(UnitControl, {
				label: __('Font size', 'omega-design'),
				units: SIZE_UNITS,
				value: settings.fontSize || '',
				onChange: function (value) { setElementSetting(props, device, 'fontSize', value); }
			}),
			createElement(UnitControl, {
				label: props.name === 'core/image' ? __('Image width', 'omega-design') : __('Width', 'omega-design'),
				units: SIZE_UNITS,
				value: settings.width || '',
				onChange: function (value) { setElementSetting(props, device, 'width', value); }
			}),
			boxField(props, device, settings, 'margin', __('Margin', 'omega-design')),
			boxField(props, device, settings, 'padding', __('Padding', 'omega-design')),
			ToggleControl && createElement(ToggleControl, {
				label: device === 'mobile' ? __('Hide on mobile', 'omega-design') : __('Hide on tablet (and mobile)', 'omega-design'),
				checked: !!settings.hide,
				__nextHasNoMarginBottom: true,
				onChange: function (value) { setElementSetting(props, device, 'hide', value); }
			})
		);
	}

	var withResponsiveSettingsControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!props.isSelected || !supportsResponsiveSettings(props.name)) {
				return createElement(BlockEdit, props);
			}

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Responsive Settings', 'omega-design'), initialOpen: false },
				deviceTabPanel(SMALL_DEVICE_TABS, function (device) {
					return renderElementFields(props, device);
				}));
		};
	}, 'withResponsiveSettingsControl');

	var withResponsiveSettingsPreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var all = props.attributes && props.attributes.omegaResponsive;
			var device = getCurrentDeviceType();

			if (!all || device === 'desktop' || (!all.tablet && !all.mobile)) {
				return createElement(BlockListBlock, props);
			}

			var sel = '#block-' + props.clientId;
			var css = deviceCascadeCss(all, device, function (settings) {
				return buildElementRules(settings, props.name, sel);
			});

			if (!css) {
				return createElement(BlockListBlock, props);
			}

			return withPreviewStyle(BlockListBlock, props, css);
		};
	}, 'withResponsiveSettingsPreview');

	if (SelectControl && TabPanel && UnitControl) {
		addFilter('editor.BlockEdit', 'omega-design/responsive-settings-control', withResponsiveSettingsControl);
		addFilter('editor.BlockListBlock', 'omega-design/responsive-settings-preview', withResponsiveSettingsPreview);
	}

	/**
	 * "Image Size" - full per-device sizing for the Image block wherever it
	 * sits (Group, Row, Stack, Grid, Columns...): width, max width, height,
	 * min height, aspect ratio, crop (object-fit), focal point and corner
	 * radius, on Desktop/Tablet/Mobile tabs. Tablet falls back to Desktop
	 * and Mobile to Tablet, like the other responsive panels.
	 *
	 * Stored in a top-level omegaImage attribute ({desktop:{}, tablet:{},
	 * mobile:{}}), registered here and in includes/core/blocks.php
	 * add_image_size_attribute(); output on the front end by
	 * includes/core/responsive_styles.php inject_image_size_styles(). Rules
	 * are !important so they win over the Image block's own inline size and
	 * over any pattern stylesheet that sizes its images by class. Nothing
	 * is added to save(), so existing Image blocks stay valid.
	 */
	var IMAGE_SIZE_BLOCK = 'core/image';

	var ASPECT_OPTIONS = [
		{ value: '', label: __('Default', 'omega-design') },
		{ value: 'auto', label: __('Original', 'omega-design') },
		{ value: '1/1', label: __('Square - 1:1', 'omega-design') },
		{ value: '4/5', label: __('Portrait - 4:5', 'omega-design') },
		{ value: '3/4', label: __('Portrait - 3:4', 'omega-design') },
		{ value: '2/3', label: __('Portrait - 2:3', 'omega-design') },
		{ value: '9/16', label: __('Tall - 9:16', 'omega-design') },
		{ value: '5/4', label: __('Landscape - 5:4', 'omega-design') },
		{ value: '4/3', label: __('Landscape - 4:3', 'omega-design') },
		{ value: '3/2', label: __('Landscape - 3:2', 'omega-design') },
		{ value: '16/9', label: __('Wide - 16:9', 'omega-design') },
		{ value: '21/9', label: __('Ultra wide - 21:9', 'omega-design') }
	];

	var FIT_OPTIONS = [
		{ value: '', label: __('Default', 'omega-design') },
		{ value: 'cover', label: __('Cover - fill and crop', 'omega-design') },
		{ value: 'contain', label: __('Contain - show whole image', 'omega-design') },
		{ value: 'fill', label: __('Stretch', 'omega-design') },
		{ value: 'none', label: __('Actual size', 'omega-design') },
		{ value: 'scale-down', label: __('Scale down', 'omega-design') }
	];

	var IMAGE_SIZE_FIELDS = ['width', 'maxWidth', 'height', 'minHeight', 'aspectRatio', 'objectFit', 'objectPosition', 'borderRadius'];

	function setImageSize(props, device, key, value) {
		var all = withDeviceValue(props.attributes.omegaImage, device, key, value, isBlank);
		props.setAttributes({ omegaImage: objectOrUndefined(all) });
	}

	/**
	 * Same rules as responsive_styles.php build_image_size_rules(). `sel` is
	 * the figure; `editor` adds the rules needed to also size the editor's
	 * own resize handle wrapper around the <img>.
	 */
	function buildImageSizeRules(settings, sel, editor, inheritedFit) {
		var fig = '';
		var img = '';

		if (settings.width) {
			fig += 'width:' + settings.width + ' !important;max-width:100%;';
			img += 'width:100% !important;';
		}
		if (settings.maxWidth) {
			fig += 'max-width:' + settings.maxWidth + ' !important;';
			img += 'max-width:100% !important;';
		}
		if (settings.height) {
			img += 'height:' + settings.height + ' !important;';
		}
		if (settings.minHeight) {
			img += 'min-height:' + settings.minHeight + ' !important;';
		}
		if (settings.aspectRatio) {
			img += 'aspect-ratio:' + settings.aspectRatio + ' !important;';
			if (!settings.height) {
				img += 'height:auto !important;';
			}
		}
		if (settings.height || settings.minHeight || settings.aspectRatio) {
			fig += 'height:auto !important;';
			if (!settings.width) {
				img += 'width:100% !important;';
			}
			if (!settings.objectFit && !inheritedFit) {
				img += 'object-fit:cover !important;';
			}
		}
		if (settings.objectFit) {
			img += 'object-fit:' + settings.objectFit + ' !important;';
		}
		if (settings.objectPosition) {
			img += 'object-position:' + settings.objectPosition + ' !important;';
		}
		if (settings.borderRadius) {
			img += 'border-radius:' + settings.borderRadius + ' !important;';
			fig += 'border-radius:' + settings.borderRadius + ' !important;';
		}

		var css = (fig ? sel + '{' + fig + '}' : '') + (img ? sel + ' img{' + img + '}' : '');
		if (editor && (settings.width || settings.maxWidth || settings.height || settings.aspectRatio)) {
			css += sel + ' .components-resizable-box__container{width:100% !important;height:auto !important;max-width:100% !important;}';
		}
		return css;
	}

	function positionToFocal(value) {
		var parts = String(value || '').match(/([\d.]+)%\s+([\d.]+)%/);
		return parts ? { x: parseFloat(parts[1]) / 100, y: parseFloat(parts[2]) / 100 } : { x: 0.5, y: 0.5 };
	}

	function renderImageSizeFields(props, device) {
		var settings = ((props.attributes.omegaImage || {})[device]) || {};
		var inheritNote = device === 'desktop'
			? null
			: createElement('p', { key: 'note', style: { margin: 0, fontSize: '12px', color: '#757575' } },
				device === 'tablet'
					? __('Leave a field empty to use the Desktop value.', 'omega-design')
					: __('Leave a field empty to use the Tablet (or Desktop) value.', 'omega-design'));

		function unitField(key, label, help) {
			return createElement(UnitControl, {
				key: key,
				label: label,
				help: help,
				units: SIZE_UNITS,
				value: settings[key] || '',
				onChange: function (value) { setImageSize(props, device, key, value); }
			});
		}

		var hasSettings = IMAGE_SIZE_FIELDS.some(function (key) { return settings[key]; });

		return createElement('div', { key: device, style: { display: 'grid', gap: '16px', paddingTop: '12px' } },
			inheritNote,
			createElement('div', { key: 'wh', style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' } },
				unitField('width', __('Width', 'omega-design')),
				unitField('maxWidth', __('Max width', 'omega-design')),
				unitField('height', __('Height', 'omega-design')),
				unitField('minHeight', __('Min height', 'omega-design'))
			),
			createElement(SelectControl, {
				key: 'aspect',
				label: __('Aspect ratio', 'omega-design'),
				value: settings.aspectRatio || '',
				options: ASPECT_OPTIONS,
				__nextHasNoMarginBottom: true,
				onChange: function (value) { setImageSize(props, device, 'aspectRatio', value); }
			}),
			createElement(SelectControl, {
				key: 'fit',
				label: __('Image fit', 'omega-design'),
				help: __('How the image fills its box once a height or aspect ratio is set.', 'omega-design'),
				value: settings.objectFit || '',
				options: FIT_OPTIONS,
				__nextHasNoMarginBottom: true,
				onChange: function (value) { setImageSize(props, device, 'objectFit', value); }
			}),
			FocalPointPicker && props.attributes.url && createElement(FocalPointPicker, {
				key: 'focal',
				label: __('Focal point (which part stays visible when cropped)', 'omega-design'),
				url: props.attributes.url,
				value: positionToFocal(settings.objectPosition),
				__nextHasNoMarginBottom: true,
				onChange: function (point) {
					setImageSize(props, device, 'objectPosition', Math.round(point.x * 100) + '% ' + Math.round(point.y * 100) + '%');
				}
			}),
			unitField('borderRadius', __('Corner radius', 'omega-design')),
			hasSettings && createElement(Button, {
				key: 'reset',
				variant: 'secondary',
				isDestructive: true,
				onClick: function () {
					var all = Object.assign({}, props.attributes.omegaImage);
					delete all[device];
					props.setAttributes({ omegaImage: objectOrUndefined(all) });
				}
			}, device === 'desktop' ? __('Reset Desktop size', 'omega-design') : device === 'tablet' ? __('Reset Tablet size', 'omega-design') : __('Reset Mobile size', 'omega-design'))
		);
	}

	addFilter('blocks.registerBlockType', 'omega-design/image-size-attribute', function (settings, name) {
		if (name !== IMAGE_SIZE_BLOCK) {
			return settings;
		}

		return addBlockAttributes(settings, {
			omegaImage: { type: 'object' }
		});
	});

	var withImageSizeControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (props.name !== IMAGE_SIZE_BLOCK || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			return inspectorPanel(BlockEdit, props, { group: 'styles' },
				{ title: __('Image Size', 'omega-design'), initialOpen: true },
				deviceTabPanel(DEVICE_TABS, function (device) {
					return renderImageSizeFields(props, device);
				}));
		};
	}, 'withImageSizeControl');

	/** Applies the settings for the editor's current device preview to the canvas. */
	var withImageSizePreview = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var all = props.name === IMAGE_SIZE_BLOCK && props.attributes && props.attributes.omegaImage;

			if (!all) {
				return createElement(BlockListBlock, props);
			}

			var device = getCurrentDeviceType();
			var sel = '#block-' + props.clientId;
			var desktopFit = (all.desktop || {}).objectFit || '';
			var tabletFit = (all.tablet || {}).objectFit || desktopFit;
			var css = buildImageSizeRules(all.desktop || {}, sel, true, '');
			if (device === 'tablet' || device === 'mobile') {
				css += buildImageSizeRules(all.tablet || {}, sel, true, desktopFit);
			}
			if (device === 'mobile') {
				css += buildImageSizeRules(all.mobile || {}, sel, true, tabletFit);
			}

			if (!css) {
				return createElement(BlockListBlock, props);
			}

			return withPreviewStyle(BlockListBlock, props, css);
		};
	}, 'withImageSizePreview');

	if (SelectControl && TabPanel && UnitControl) {
		addFilter('editor.BlockEdit', 'omega-design/image-size-control', withImageSizeControl);
		addFilter('editor.BlockListBlock', 'omega-design/image-size-preview', withImageSizePreview);
	}

	/**
	 * The "Choose a pattern" starter popup is only for EMPTY pages.
	 *
	 * Core decides whether to open it once, as the editor mounts, from
	 * isEditedPostEmpty(). In some flows (e.g. opening a page from the Site
	 * Editor) the page's content hasn't been loaded into the editor yet at
	 * that moment, so a page that already has content briefly looks empty
	 * and gets the popup anyway. This closes it as soon as the page turns
	 * out to have content - checked against both the editor's blocks and
	 * the page's saved content from the server - while a genuinely empty
	 * page still gets it as normal.
	 */
	var startModals = document.getElementsByClassName('editor-start-page-options__modal');

	function pageHasContent() {
		var editor = wp.data.select('core/editor');
		if (!editor || !editor.getCurrentPostId || !editor.getCurrentPostId()) {
			return false;
		}
		if (editor.isEditedPostEmpty && !editor.isEditedPostEmpty()) {
			return true;
		}
		var record = wp.data.select('core').getEntityRecord('postType', editor.getCurrentPostType(), editor.getCurrentPostId());
		var saved = record && record.content ? (typeof record.content === 'string' ? record.content : record.content.raw || '') : '';
		return saved.replace(/<!--\s*\/?wp:paragraph\s*-->|<p>\s*<\/p>|\s+/g, '') !== '';
	}

	(wp.domReady || function (callback) { callback(); })(function () {
		// Post editor and Site Editor only (the widgets editor has no post).
		if (!wp.data.subscribe || !wp.data.select('core/editor')) {
			return;
		}
		wp.data.subscribe(function () {
			if (!startModals.length || !pageHasContent()) {
				return;
			}
			var close = startModals[0].querySelector('.components-modal__header button');
			if (close) {
				close.click();
			}
		});
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
	var TextControl = wp.components.TextControl;
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

			return inspectorPanel(BlockEdit, props, {},
				{ title: __('Mega Menu', 'omega-design'), initialOpen: false },
				createElement(ComboboxControl, {
					label: __('Mega Menu', 'omega-design'),
					help: __('Attach one of your Mega Menus as a hover/click panel for this nav item. Manage them under Omega Design > Mega Menus in wp-admin.', 'omega-design'),
					value: props.attributes.megaMenuPattern || '',
					options: options,
					onChange: function (value) {
						props.setAttributes({ megaMenuPattern: value || '' });
					}
				}));
		};
	}, 'withMegaMenuControl');

	if (ComboboxControl) {
		addFilter('editor.BlockEdit', 'omega-design/megamenu-control', withMegaMenuControl);
	}

	/**
	 * Adds a "Link" panel to the Inspector for Group/Row/Stack/Grid, Columns
	 * and Column blocks, letting an editor paste a URL that makes the entire
	 * block clickable - not just a heading or button somewhere inside it
	 * (e.g. a mega menu grid tile). Stored under attributes.style.omegaLink -
	 * a custom key nested in the same "style" attribute core already
	 * auto-registers for these blocks' color/border supports - the same
	 * approach as `omegaHover`/`omegaAlign`/`dimensions` above, rather than
	 * new top-level attributes requiring their own `blocks.registerBlockType`
	 * registration.
	 *
	 * Rendered on the front end as a full-cover overlay <a> (see
	 * includes/core/hooks.php, modify_block_render()) absolutely positioned
	 * over the block, rather than wrapping the block's own markup in an <a>,
	 * so a card that already has its own inner link or button never ends up
	 * as invalid nested-<a> HTML - that inner element just needs a higher
	 * z-index (assets/css/style.css) to stay clickable above the overlay.
	 */
	var LINK_BLOCKS = ['core/group', 'core/columns', 'core/column'];

	function getBlockLink(attributes) {
		return getStyleGroup(attributes, 'omegaLink');
	}

	function setBlockLink(props, key, value) {
		setStyleGroupValue(props, 'omegaLink', key, value, isFalsy);
	}

	var withBlockLinkControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (LINK_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var link = getBlockLink(props.attributes);
			var hasUrl = !!link.url;

			var fields = [
				createElement(TextControl, {
					key: 'url',
					label: __('Link URL', 'omega-design'),
					type: 'url',
					value: link.url || '',
					onChange: function (value) {
						setBlockLink(props, 'url', value);
					}
				})
			];

			if (hasUrl) {
				fields.push(
					createElement(ToggleControl, {
						key: 'target',
						label: __('Open in new tab', 'omega-design'),
						checked: '_blank' === link.target,
						onChange: function (checked) {
							setBlockLink(props, 'target', checked ? '_blank' : '');
						}
					}),
					createElement(TextControl, {
						key: 'label',
						label: __('Accessible label', 'omega-design'),
						value: link.label || '',
						onChange: function (value) {
							setBlockLink(props, 'label', value);
						}
					})
				);
			}

			return inspectorPanel(BlockEdit, props, {},
				{ title: __('Link', 'omega-design'), initialOpen: false },
				fields);
		};
	}, 'withBlockLinkControl');

	if (TextControl && ToggleControl) {
		addFilter('editor.BlockEdit', 'omega-design/block-link-control', withBlockLinkControl);
	}

	/**
	 * Editor-canvas-only visual indicator (a dashed outline, see
	 * assets/css/style.css) that a block has a Link URL set. The actual
	 * clickable overlay above is only rendered on the front end - Group/
	 * Columns/Column are static blocks, so PHP's render_block filter never
	 * runs against the editor's own live canvas the way it does for a real
	 * page request.
	 */
	var withBlockLinkIndicator = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (LINK_BLOCKS.indexOf(props.name) === -1 || !getBlockLink(props.attributes).url) {
				return createElement(BlockListBlock, props);
			}

			return withWrapperOverrides(BlockListBlock, props, {
				className: mergedWrapperClassName(props, 'is-omega-linked-block')
			});
		};
	}, 'withBlockLinkIndicator');

	addFilter('editor.BlockListBlock', 'omega-design/block-link-indicator', withBlockLinkIndicator);

	/**
	 * Adds a "Text Style" button to the block toolbar (not tucked away in
	 * the Inspector sidebar) for every text-bearing block, opening a popover
	 * with Italic, Thickness (font-weight 400/500/600/700), Size, Line
	 * Height, Letter Spacing and Opacity controls in one place - requested
	 * as a single "everything in the toolbar" spot rather than the several
	 * separate native Typography sidebar panels core already splits these
	 * across. Thickness is a plain numeric font-weight picker rather than a
	 * Bold on/off toggle, since most of the theme's variable font files
	 * (DM Sans, Jost, Roboto - see theme.json) actually ship 500/600
	 * weights worth using, not just 400/700.
	 *
	 * Stored under attributes.style.omegaTypography - a custom key nested
	 * in the same "style" attribute core already auto-registers for every
	 * block below (each already supports at least color or fontSize),
	 * following the same convention as omegaHover/omegaAlign/omegaLink
	 * above rather than a new attribute registration. Opacity has no core
	 * equivalent at all; Italic/Thickness/Size/Line Height/Letter Spacing
	 * deliberately use this same custom key too (instead of core's own
	 * style.typography.fontWeight/fontStyle/fontSize/lineHeight/
	 * letterSpacing) so this one toolbar popover is a single source of
	 * truth, rather than fighting the native sidebar over the same
	 * attribute path.
	 */
	var TEXT_STYLE_BLOCKS = [
		'core/paragraph', 'core/heading', 'core/list', 'core/list-item',
		'core/quote', 'core/pullquote', 'core/verse', 'core/preformatted',
		'core/code', 'core/button'
	];

	var BlockControls = wp.blockEditor.BlockControls;
	var ToolbarGroup = wp.components.ToolbarGroup;
	var ToolbarButton = wp.components.ToolbarButton;
	var Dropdown = wp.components.Dropdown;

	var TEXT_STYLE_UNITS = [
		{ value: 'px', label: 'px', default: '' },
		{ value: 'em', label: 'em', default: '' },
		{ value: 'rem', label: 'rem', default: '' },
		{ value: '%', label: '%', default: '' }
	];

	var BOX_WIDTH_OPTIONS = [
		{ label: __('Text Only', 'omega-design'), value: false },
		{ label: __('Full Width', 'omega-design'), value: true }
	];

	var THICKNESS_OPTIONS = [
		{ label: '400', value: '400' },
		{ label: '500', value: '500' },
		{ label: '600', value: '600' },
		{ label: '700', value: '700' }
	];

	function getTextStyle(attributes) {
		return getStyleGroup(attributes, 'omegaTypography');
	}

	// Keeps false and 0 (italic off, opacity 0 are real values).
	function setTextStyle(props, key, value) {
		setStyleGroupValue(props, 'omegaTypography', key, value, isBlank);
	}

	/**
	 * Text Color / Background Color / Padding / Margin (added to the same
	 * popover below) deliberately style the block's own root element - for
	 * every block in TEXT_STYLE_BLOCKS that element (the <p>, <h2>, <li>...)
	 * already IS the text, with no separate wrapping div around it - rather
	 * than adding an inner wrapping <span>. The one thing that root element
	 * doesn't do on its own is hug the text: it's block-level, so a
	 * background/padding on it stretches across the full column width
	 * instead of sitting snugly behind the words. Switching it to
	 * `inline-block` the moment a background or padding is actually set
	 * (never otherwise, so plain text is untouched) is what makes it "only
	 * the text content, not the whole div".
	 *
	 * That shrink-to-fit switch loses the normal effect of the block's own
	 * Left/Center/Right text alignment (`attributes.align` on Paragraph/
	 * Heading - a block-level element has room to align text within its
	 * own full-width box; a shrink-wrapped one doesn't), so it's
	 * recompensated here as margin: center -> auto both sides, right ->
	 * auto on the left, so a centered/right-aligned heading doesn't
	 * suddenly jump to the left edge the moment a background is added.
	 *
	 * This hug-to-fit is only the DEFAULT, not forced - the "Box Width"
	 * choice below (typography.fullWidth) lets an admin explicitly keep
	 * the older full-width strip look (e.g. a colored banner paragraph)
	 * even with a background/padding set.
	 */
	function getTextBoxAutoMargin(attributes) {
		if (attributes.align === 'center') { return { marginLeft: 'auto', marginRight: 'auto' }; }
		if (attributes.align === 'right') { return { marginLeft: 'auto' }; }
		return {};
	}

	/**
	 * True whenever the block has ANY background set - through this custom
	 * Text Style popover, or through core's own native "Background color"
	 * swatch (Styles sidebar / block toolbar color picker core already
	 * ships for these blocks), which writes to a completely different
	 * attribute path (attributes.backgroundColor for a palette preset,
	 * attributes.style.color.background for a custom color) that this
	 * feature doesn't otherwise touch. Reacting to both is what actually
	 * makes "hug the text" apply no matter which color control the admin
	 * reaches for - most will use the familiar native swatch, not go
	 * looking for a second one here.
	 */
	function blockHasBackground(typography, attributes) {
		return !!(
			typography.backgroundColor ||
			attributes.backgroundColor ||
			(attributes.style && attributes.style.color && attributes.style.color.background)
		);
	}

	function getTextStyleCSS(typography, attributes) {
		attributes = attributes || {};

		var style = {};

		if (typography.fontWeight) { style.fontWeight = typography.fontWeight; }
		if (typography.italic) { style.fontStyle = 'italic'; }
		if (typography.fontSize) { style.fontSize = typography.fontSize; }
		if (typography.lineHeight) { style.lineHeight = typography.lineHeight; }
		if (typography.letterSpacing) { style.letterSpacing = typography.letterSpacing; }
		if (typography.opacity !== undefined && typography.opacity !== '') {
			style.opacity = typography.opacity / 100;
		}
		if (typography.textColor) { style.color = typography.textColor; }
		if (typography.backgroundColor) { style.backgroundColor = typography.backgroundColor; }
		if (typography.padding) { style.padding = typography.padding; }

		// Reacting to core's native background (blockHasBackground) is only
		// safe once this block already has SOME omegaTypography value of its
		// own - i.e. the admin has actually opened this popover for this
		// block before. Reacting to it unconditionally would silently change
		// the computed save() output of every OTHER pre-existing block that
		// merely already had a native background color, with no
		// omegaTypography attribute at all - a mismatch against what's
		// already stored in post_content that WordPress reports as "this
		// block contains unexpected or invalid content" the next time that
		// page/template loads in the editor, for every such block across the
		// whole site, not just ones this feature was ever used on.
		var touched = Object.keys(typography).length > 0;
		var shouldHug = touched && (blockHasBackground(typography, attributes) || typography.padding) && !typography.fullWidth;

		if (shouldHug) {
			style.display = 'inline-block';
			Object.assign(style, getTextBoxAutoMargin(attributes));
		}

		// An explicit margin always wins over the hug's alignment compensation above.
		if (typography.margin) { style.margin = typography.margin; }

		return style;
	}

	var withTextStyleControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (TEXT_STYLE_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var typography = getTextStyle(props.attributes);

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					BlockControls,
					{ group: 'block' },
					createElement(
						ToolbarGroup,
						{},
						createElement(Dropdown, {
							renderToggle: function (toggleProps) {
								return createElement(ToolbarButton, {
									icon: 'editor-textcolor',
									label: __('Text Style', 'omega-design'),
									isPressed: toggleProps.isOpen || !!Object.keys(typography).length,
									onClick: toggleProps.onToggle
								});
							},
							renderContent: function () {
								return createElement(
									'div',
									{ className: 'omega-toolbar-popover' },
									createElement(
										ButtonGroup,
										{ className: 'omega-toolbar-popover__row' },
										createElement(Button, {
											variant: typography.italic ? 'primary' : 'secondary',
											isPressed: !!typography.italic,
											onClick: function () {
												setTextStyle(props, 'italic', !typography.italic);
											}
										}, __('Italic', 'omega-design'))
									),
									createElement(
										BaseControl,
										{ label: __('Thickness', 'omega-design') },
										createElement(
											ButtonGroup,
											{},
											THICKNESS_OPTIONS.map(function (option) {
												var isActive = typography.fontWeight === option.value;
												return optionButton(option.value, option.label, isActive, function () {
													setTextStyle(props, 'fontWeight', isActive ? '' : option.value);
												});
											})
										)
									),
									createElement(UnitControl, {
										label: __('Size', 'omega-design'),
										units: TEXT_STYLE_UNITS,
										value: typography.fontSize || '',
										onChange: function (value) {
											setTextStyle(props, 'fontSize', value);
										}
									}),
									createElement(RangeControl, {
										label: __('Line Height', 'omega-design'),
										value: typography.lineHeight ? parseFloat(typography.lineHeight) : undefined,
										min: 0.8,
										max: 3,
										step: 0.05,
										allowReset: true,
										onChange: function (value) {
											setTextStyle(props, 'lineHeight', value === undefined ? '' : String(value));
										}
									}),
									createElement(UnitControl, {
										label: __('Letter Spacing', 'omega-design'),
										units: TEXT_STYLE_UNITS,
										value: typography.letterSpacing || '',
										onChange: function (value) {
											setTextStyle(props, 'letterSpacing', value);
										}
									}),
									createElement(RangeControl, {
										label: __('Opacity', 'omega-design'),
										value: typography.opacity !== undefined ? typography.opacity : 100,
										min: 0,
										max: 100,
										step: 1,
										allowReset: true,
										onChange: function (value) {
											setTextStyle(props, 'opacity', value === undefined ? '' : value);
										}
									}),
									createElement(
										BaseControl,
										{ label: __('Text Color', 'omega-design') },
										createElement(ColorPalette, {
											value: typography.textColor,
											onChange: function (value) {
												setTextStyle(props, 'textColor', value);
											}
										})
									),
									createElement(
										BaseControl,
										{ label: __('Background Color', 'omega-design') },
										createElement(ColorPalette, {
											value: typography.backgroundColor,
											onChange: function (value) {
												setTextStyle(props, 'backgroundColor', value);
											}
										})
									),
									createElement(
										BaseControl,
										{
											label: __('Box Width', 'omega-design'),
											help: __('Applies once a background color or padding is set (from either color control above).', 'omega-design')
										},
										createElement(
											ButtonGroup,
											{},
											BOX_WIDTH_OPTIONS.map(function (option) {
												return optionButton(String(option.value), option.label, !!typography.fullWidth === option.value, function () {
													setTextStyle(props, 'fullWidth', option.value);
												});
											})
										)
									),
									createElement(UnitControl, {
										label: __('Padding', 'omega-design'),
										units: TEXT_STYLE_UNITS,
										value: typography.padding || '',
										onChange: function (value) {
											setTextStyle(props, 'padding', value);
										}
									}),
									createElement(UnitControl, {
										label: __('Margin', 'omega-design'),
										units: TEXT_STYLE_UNITS,
										value: typography.margin || '',
										onChange: function (value) {
											setTextStyle(props, 'margin', value);
										}
									})
								);
							}
						})
					)
				)
			);
		};
	}, 'withTextStyleControl');

	if (BlockControls && Dropdown && UnitControl) {
		addFilter('editor.BlockEdit', 'omega-design/text-style-control', withTextStyleControl);
	}

	/**
	 * Mirrors the Text Style attributes onto the block's wrapper element in
	 * the editor canvas, so the toolbar popover above previews live.
	 */
	var withTextStyleEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (TEXT_STYLE_BLOCKS.indexOf(props.name) === -1) {
				return createElement(BlockListBlock, props);
			}

			var style = getTextStyleCSS(getTextStyle(props.attributes), props.attributes);

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			return withWrapperOverrides(BlockListBlock, props, { style: mergedWrapperStyle(props, style) });
		};
	}, 'withTextStyleEditor');

	addFilter('editor.BlockListBlock', 'omega-design/text-style-editor', withTextStyleEditor);

	/**
	 * Bakes the same Text Style attributes into the saved block markup, so
	 * they render on the front end without needing a render_block PHP
	 * filter (every block in TEXT_STYLE_BLOCKS is static, i.e. uses its own
	 * client save() rather than a PHP render callback).
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/text-style-save', function (extraProps, blockType, attributes) {
		if (TEXT_STYLE_BLOCKS.indexOf(blockType.name) === -1) {
			return extraProps;
		}

		var style = getTextStyleCSS(getTextStyle(attributes), attributes);
		if (!Object.keys(style).length) {
			return extraProps;
		}

		return addSaveStyle(extraProps, style);
	});

	/**
	 * Adds a "Grid Alignment" toolbar button to the Grid variation of
	 * core/group (Group > Grid in the block variation picker) - core ships
	 * this alignment toolbar for the Flex-based Row/Stack variations
	 * (Left/Center/Right/Space Between + Top/Middle/Bottom) but never built
	 * an equivalent for Grid, which otherwise only exposes column/row count
	 * and gap. This fills that specific gap: it controls how every item
	 * inside the grid sits within its own cell (CSS `justify-items` /
	 * `align-items` on the grid container), matching what Row's toolbar
	 * already does for a Flex row.
	 *
	 * Stored under attributes.style.omegaGridAlign - same convention as
	 * omegaHover/omegaAlign/omegaTypography above - rather than a new
	 * attribute registration.
	 */
	function isGridGroup(props) {
		return props.name === 'core/group' && !!props.attributes.layout && props.attributes.layout.type === 'grid';
	}

	var GRID_ALIGN_UNSET = '';

	var GRID_JUSTIFY_OPTIONS = [
		{ label: __('Left', 'omega-design'), value: 'start' },
		{ label: __('Center', 'omega-design'), value: 'center' },
		{ label: __('Right', 'omega-design'), value: 'end' },
		{ label: __('Stretch', 'omega-design'), value: 'stretch' }
	];

	var GRID_ALIGN_OPTIONS = [
		{ label: __('Top', 'omega-design'), value: 'start' },
		{ label: __('Middle', 'omega-design'), value: 'center' },
		{ label: __('Bottom', 'omega-design'), value: 'end' },
		{ label: __('Stretch', 'omega-design'), value: 'stretch' }
	];

	function getGridAlign(attributes) {
		return getStyleGroup(attributes, 'omegaGridAlign');
	}

	// GRID_ALIGN_UNSET is '' - falsy, so it removes the key.
	function setGridAlign(props, key, value) {
		setStyleGroupValue(props, 'omegaGridAlign', key, value, isFalsy);
	}

	function getGridAlignCSS(gridAlign) {
		var style = {};

		if (gridAlign.justifyItems) { style.justifyItems = gridAlign.justifyItems; }
		if (gridAlign.alignItems) { style.alignItems = gridAlign.alignItems; }

		return style;
	}

	var withGridAlignControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (!isGridGroup(props) || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var gridAlign = getGridAlign(props.attributes);

			function renderOptionGroup(options, key, currentValue) {
				var buttons = options.map(function (option) {
					var isActive = currentValue === option.value;
					return optionButton(option.value, option.label, isActive, function () {
						setGridAlign(props, key, isActive ? GRID_ALIGN_UNSET : option.value);
					});
				});

				return createElement(ButtonGroup, {}, buttons);
			}

			return createElement(
				Fragment,
				{},
				createElement(BlockEdit, props),
				createElement(
					BlockControls,
					{ group: 'block' },
					createElement(
						ToolbarGroup,
						{},
						createElement(Dropdown, {
							renderToggle: function (toggleProps) {
								return createElement(ToolbarButton, {
									icon: 'align-center',
									label: __('Grid Alignment', 'omega-design'),
									isPressed: toggleProps.isOpen || !!Object.keys(gridAlign).length,
									onClick: toggleProps.onToggle
								});
							},
							renderContent: function () {
								return createElement(
									'div',
									{ className: 'omega-toolbar-popover' },
									createElement(
										BaseControl,
										{ label: __('Horizontal Align', 'omega-design') },
										renderOptionGroup(GRID_JUSTIFY_OPTIONS, 'justifyItems', gridAlign.justifyItems)
									),
									createElement(
										BaseControl,
										{ label: __('Vertical Align', 'omega-design') },
										renderOptionGroup(GRID_ALIGN_OPTIONS, 'alignItems', gridAlign.alignItems)
									)
								);
							}
						})
					)
				)
			);
		};
	}, 'withGridAlignControl');

	if (BlockControls && Dropdown) {
		addFilter('editor.BlockEdit', 'omega-design/grid-align-control', withGridAlignControl);
	}

	/**
	 * Mirrors the Grid Alignment onto the block's wrapper element (the
	 * actual `display:grid` container) in the editor canvas, so the
	 * toolbar popover above previews live.
	 */
	var withGridAlignEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			if (!isGridGroup(props)) {
				return createElement(BlockListBlock, props);
			}

			var style = getGridAlignCSS(getGridAlign(props.attributes));

			if (!Object.keys(style).length) {
				return createElement(BlockListBlock, props);
			}

			return withWrapperOverrides(BlockListBlock, props, { style: mergedWrapperStyle(props, style) });
		};
	}, 'withGridAlignEditor');

	addFilter('editor.BlockListBlock', 'omega-design/grid-align-editor', withGridAlignEditor);

	/**
	 * Bakes the same Grid Alignment into the saved block markup - core/group
	 * is a static block (client save()), so no render_block PHP filter is
	 * needed.
	 */
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/grid-align-save', function (extraProps, blockType, attributes) {
		if (blockType.name !== 'core/group' || !attributes.layout || attributes.layout.type !== 'grid') {
			return extraProps;
		}

		var style = getGridAlignCSS(getGridAlign(attributes));
		if (!Object.keys(style).length) {
			return extraProps;
		}

		return addSaveStyle(extraProps, style);
	});
})(window.wp);
