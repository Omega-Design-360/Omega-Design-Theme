/**
 * Adds a "Scroll Animation" control (Advanced panel) to every block in the
 * editor - block-editor-only half of the site-wide scroll animation
 * feature. See includes/core/scroll_animations.php and
 * assets/js/scroll-animations.js for the attribute-injection (dynamic
 * blocks) and front-end playback (IntersectionObserver) halves.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.hooks || !wp.element || !wp.blockEditor || !wp.components || !wp.compose || !wp.i18n) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var __ = wp.i18n.__;

	var ANIMATION_OPTIONS = [
		{ label: __('None', 'omega-design'), value: '' },
		{ label: __('Fade Up', 'omega-design'), value: 'fade-up' },
		{ label: __('Fade Down', 'omega-design'), value: 'fade-down' },
		{ label: __('Fade In', 'omega-design'), value: 'fade-in' },
		{ label: __('Slide From Left', 'omega-design'), value: 'slide-left' },
		{ label: __('Slide From Right', 'omega-design'), value: 'slide-right' },
		{ label: __('Zoom In', 'omega-design'), value: 'zoom-in' }
	];

	/**
	 * A generic scroll-reveal wrapper (opacity 0 until IntersectionObserver
	 * fires) would fight blocks that already own their own visibility/
	 * motion: inactive tab panels are already display:none until clicked,
	 * and structural blocks like navigation shouldn't ever start hidden.
	 * The slider block itself is NOT excluded - it can still fade/slide in
	 * as a whole section - but assets/js/scroll-animations.js separately
	 * skips (reveals immediately, no wait-for-scroll) anything nested
	 * *inside* a slider's own slides, since the carousel's own
	 * overflow:hidden clipping makes that content impossible to correctly
	 * detect via IntersectionObserver.
	 */
	var EXCLUDED_BLOCKS = [
		'omega-design/tabs-item',
		'core/template-part',
		'core/navigation'
	];

	function addAnimationAttributes(settings, name) {
		if (EXCLUDED_BLOCKS.indexOf(name) !== -1 || !settings.attributes) {
			return settings;
		}
		settings.attributes = Object.assign({}, settings.attributes, {
			omegaAnimation: { type: 'string', default: '' },
			omegaAnimationDuration: { type: 'number', default: 600 },
			omegaAnimationDelay: { type: 'number', default: 0 }
		});
		return settings;
	}
	addFilter('blocks.registerBlockType', 'omega-design/scroll-animation-attrs', addAnimationAttributes);

	var withAnimationControls = createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			if (EXCLUDED_BLOCKS.indexOf(props.name) !== -1 || !props.attributes || !('omegaAnimation' in props.attributes)) {
				return createElement(BlockEdit, props);
			}

			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return createElement(
				Fragment,
				null,
				createElement(BlockEdit, props),
				createElement(
					InspectorControls,
					{ group: 'advanced' },
					createElement(
						PanelBody,
						{ title: __('Scroll Animation', 'omega-design'), initialOpen: false },
						createElement(SelectControl, {
							label: __('Animate on scroll', 'omega-design'),
							value: attributes.omegaAnimation || '',
							options: ANIMATION_OPTIONS,
							onChange: function (value) { setAttributes({ omegaAnimation: value }); }
						}),
						attributes.omegaAnimation ? createElement(RangeControl, {
							label: __('Duration (ms)', 'omega-design'),
							value: attributes.omegaAnimationDuration,
							min: 150,
							max: 2000,
							step: 50,
							onChange: function (value) { setAttributes({ omegaAnimationDuration: value }); }
						}) : null,
						attributes.omegaAnimation ? createElement(RangeControl, {
							label: __('Delay (ms)', 'omega-design'),
							value: attributes.omegaAnimationDelay,
							min: 0,
							max: 2000,
							step: 50,
							onChange: function (value) { setAttributes({ omegaAnimationDelay: value }); }
						}) : null
					)
				)
			);
		};
	}, 'withAnimationControls');
	addFilter('editor.BlockEdit', 'omega-design/scroll-animation-controls', withAnimationControls);

	function addAnimationSaveProps(extraProps, blockType, attributes) {
		if (!attributes || !attributes.omegaAnimation) {
			return extraProps;
		}
		extraProps.className = (extraProps.className ? extraProps.className + ' ' : '') + 'omega-animate';
		extraProps['data-omega-animate'] = attributes.omegaAnimation;
		extraProps['data-omega-animate-duration'] = attributes.omegaAnimationDuration || 600;
		extraProps['data-omega-animate-delay'] = attributes.omegaAnimationDelay || 0;
		return extraProps;
	}
	addFilter('blocks.getSaveContent.extraProps', 'omega-design/scroll-animation-save-props', addAnimationSaveProps);
})(window.wp);
