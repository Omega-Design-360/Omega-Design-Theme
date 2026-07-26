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

	if (!wp || !wp.hooks || !wp.blockEditor || !wp.components || !wp.element || !wp.compose) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var ButtonGroup = wp.components.ButtonGroup;
	var __ = wp.i18n.__;

	var TARGET_BLOCKS = ['core/group', 'core/columns'];

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
	 * Adds custom "Width" and "Height" number+unit inputs to the same
	 * Inspector Styles panel, for the same Group/Row/Stack/Columns blocks.
	 * Stored under attributes.style.dimensions.{width,height} - the same
	 * attribute core already uses for minHeight/aspectRatio - so it needs
	 * no new attribute registration and won't collide with the preset
	 * Standard/Wide/Full align buttons above.
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

	var withCustomSizeControl = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (TARGET_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
				return createElement(BlockEdit, props);
			}

			var size = getCustomSize(props.attributes);

			var fields = [
				createElement(UnitControl, {
					key: 'width',
					label: __('Width', 'omega-design'),
					units: SIZE_UNITS,
					value: size.width || '',
					onChange: function (value) {
						setCustomSize(props, 'width', value);
					}
				}),
				createElement(UnitControl, {
					key: 'height',
					label: __('Height', 'omega-design'),
					units: SIZE_UNITS,
					value: size.height || '',
					onChange: function (value) {
						setCustomSize(props, 'height', value);
					}
				})
			];

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
						fields
					)
				)
			);
		};
	}, 'withCustomSizeControl');

	if (UnitControl) {
		addFilter('editor.BlockEdit', 'omega-design/custom-size-control', withCustomSizeControl);
	}

	/**
	 * Mirrors the custom width/height onto the block's wrapper element in
	 * the editor canvas, so the visual result matches what gets saved.
	 */
	var withCustomSizeStyleEditor = createHigherOrderComponent(function (BlockListBlock) {
		return function (props) {
			var size = getCustomSize(props.attributes);

			if (TARGET_BLOCKS.indexOf(props.name) === -1 || (!size.width && !size.height)) {
				return createElement(BlockListBlock, props);
			}

			var style = Object.assign({}, props.wrapperProps && props.wrapperProps.style);
			if (size.width) { style.width = size.width; }
			if (size.height) { style.height = size.height; }

			var wrapperProps = Object.assign({}, props.wrapperProps, { style: style });

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
})(window.wp);
