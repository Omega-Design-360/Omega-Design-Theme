/**
 * "Tab" block (omega-design/tabs-item) - a single panel inside an
 * Omega Tabs block. Its label is edited inline via RichText right in the
 * canvas; the parent's PHP render_callback reads the saved "label"
 * attribute to build the tab bar (see blocks/omega-tabs/index.js).
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var RichText = wp.blockEditor.RichText;
	var __ = wp.i18n.__;

	var DEFAULT_TEMPLATE = [
		['core/paragraph', { placeholder: __('Tab content…', 'omega-design') }]
	];

	registerBlockType('omega-design/tabs-item', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var blockProps = useBlockProps({ className: 'omega-tabs__panel' });
			var innerBlocksProps = useInnerBlocksProps(
				{ className: 'omega-tabs__panel-content' },
				{ template: DEFAULT_TEMPLATE, templateInsertUpdatesSelection: false }
			);

			return createElement(
				'div',
				blockProps,
				createElement(RichText, {
					tagName: 'div',
					className: 'omega-tabs__item-label-edit',
					value: attributes.label,
					onChange: function (label) { setAttributes({ label: label }); },
					placeholder: __('Tab label', 'omega-design'),
					allowedFormats: []
				}),
				createElement('div', innerBlocksProps)
			);
		},

		save: function () {
			var blockProps = useBlockProps.save({ className: 'omega-tabs__panel' });
			var innerBlocksProps = useInnerBlocksProps.save({ className: 'omega-tabs__panel-content' });
			return createElement('div', blockProps, createElement('div', innerBlocksProps));
		}
	});
})(window.wp);
