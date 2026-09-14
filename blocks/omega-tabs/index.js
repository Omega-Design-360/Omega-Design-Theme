/**
 * "Omega Tabs" block (omega-design/tabs) - the tab bar + panel wrapper.
 * It only ever contains omega-design/tabs-item children; the PHP
 * render_callback (see includes/core/blocks.php) reads each child's
 * "label" attribute to build the clickable tab bar server-side, since a
 * static save() has no access to child block attributes.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.i18n) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var __ = wp.i18n.__;

	var DEFAULT_TEMPLATE = [
		['omega-design/tabs-item', { label: __('Tab 1', 'omega-design') }],
		['omega-design/tabs-item', { label: __('Tab 2', 'omega-design') }]
	];

	registerBlockType('omega-design/tabs', {
		edit: function () {
			var blockProps = useBlockProps({ className: 'omega-tabs omega-tabs--editing' });
			var innerBlocksProps = useInnerBlocksProps(
				{ className: 'omega-tabs__panels' },
				{
					allowedBlocks: ['omega-design/tabs-item'],
					template: DEFAULT_TEMPLATE,
					orientation: 'horizontal',
					renderAppender: InnerBlocks.ButtonBlockAppender
				}
			);

			return createElement('div', blockProps, createElement('div', innerBlocksProps));
		},

		save: function () {
			var blockProps = useBlockProps.save({ className: 'omega-tabs' });
			var innerBlocksProps = useInnerBlocksProps.save({ className: 'omega-tabs__panels' });
			return createElement('div', blockProps, createElement('div', innerBlocksProps));
		}
	});
})(window.wp);
