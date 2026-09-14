/**
 * "Omega Slider" block (omega-design/slider) - a generic InnerBlocks
 * carousel. Each direct child block is one slide; the front end (view.js)
 * handles the actual sliding/swipe/autoplay behaviour, so the editor here
 * only needs to lay slides out for editing, not simulate the carousel.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n || !wp.data) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createBlock = wp.blocks.createBlock;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var BlockControls = wp.blockEditor.BlockControls;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var ToolbarGroup = wp.components.ToolbarGroup;
	var ToolbarDropdownMenu = wp.components.ToolbarDropdownMenu;
	var useDispatch = wp.data.useDispatch;
	var __ = wp.i18n.__;

	var EFFECT_OPTIONS = [
		{ label: __('Slide (default)', 'omega-design'), value: 'slide', icon: 'align-wide' },
		{ label: __('Fade', 'omega-design'), value: 'fade', icon: 'visibility' },
		{ label: __('Zoom / Ken Burns', 'omega-design'), value: 'zoom', icon: 'search' },
		{ label: __('Vertical', 'omega-design'), value: 'vertical', icon: 'sort' },
		{ label: __('3D Coverflow', 'omega-design'), value: 'coverflow', icon: 'index-card' },
		{ label: __('Expanding Cards', 'omega-design'), value: 'cards', icon: 'images-alt2' }
	];

	var DEFAULT_TEMPLATE = [
		['core/group', { layout: { type: 'constrained' } }, [
			['core/paragraph', { placeholder: __('Slide content…', 'omega-design') }]
		]]
	];

	registerBlockType('omega-design/slider', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var clientId = props.clientId;

			var blockProps = useBlockProps({ className: 'omega-slider omega-slider--editing' });
			var innerBlocksProps = useInnerBlocksProps(
				blockProps,
				{
					template: DEFAULT_TEMPLATE,
					templateInsertUpdatesSelection: false,
					orientation: 'horizontal',
					renderAppender: InnerBlocks.ButtonBlockAppender
				}
			);

			var blockEditorDispatch = useDispatch('core/block-editor');

			/**
			 * Turns each picked media item into its own slide (a Group
			 * wrapping an Image, same shape as the default template's
			 * slides) and appends them to the end of this slider - so
			 * picking several images at once is the fast path for
			 * building an image carousel, without giving up the ability
			 * to add any other block as a slide via the "+" appender.
			 */
			function addImagesAsSlides(media) {
				var items = Array.isArray(media) ? media : [media];
				if (!items.length) {
					return;
				}

				var newSlides = items.map(function (item) {
					return createBlock('core/group', { layout: { type: 'constrained' } }, [
						createBlock('core/image', {
							url: item.url,
							id: item.id,
							alt: item.alt || '',
							sizeSlug: 'large'
						})
					]);
				});

				blockEditorDispatch.insertBlocks(newSlides, undefined, clientId);
			}

			var addImagesButton = createElement(
				MediaUploadCheck,
				null,
				createElement(MediaUpload, {
					multiple: true,
					gallery: false,
					allowedTypes: ['image'],
					onSelect: addImagesAsSlides,
					render: function (openMedia) {
						return createElement(Button, {
							variant: 'primary',
							icon: 'format-image',
							onClick: openMedia.open
						}, __('Add Images as Slides', 'omega-design'));
					}
				})
			);

			var currentEffect = attributes.effect || 'slide';
			var currentEffectOption = EFFECT_OPTIONS.filter(function (opt) { return opt.value === currentEffect; })[0] || EFFECT_OPTIONS[0];

			/**
			 * The effect picker lives in the toolbar too (not just the
			 * Inspector sidebar) so switching it is a one-click action right
			 * where the block is selected, the same way core's alignment
			 * picker works - the toggle icon itself reflects whichever
			 * effect is currently active.
			 */
			var effectDropdown = createElement(ToolbarDropdownMenu, {
				icon: currentEffectOption.icon,
				label: __('Slider effect: ', 'omega-design') + currentEffectOption.label,
				controls: EFFECT_OPTIONS.map(function (opt) {
					return {
						title: opt.label,
						icon: opt.icon,
						isActive: opt.value === currentEffect,
						onClick: function () { setAttributes({ effect: opt.value }); }
					};
				})
			});

			return createElement(
				Fragment,
				null,
				createElement(
					BlockControls,
					null,
					createElement(ToolbarGroup, null, addImagesButton),
					createElement(ToolbarGroup, null, effectDropdown)
				),
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __('Add Slides', 'omega-design') },
						createElement('p', null, __('Pick one or more images from the media library - each one becomes its own slide, added to the end of this slider.', 'omega-design')),
						addImagesButton
					),
					createElement(
						PanelBody,
						{ title: __('Slider Settings', 'omega-design') },
						createElement(ToggleControl, {
							label: __('Autoplay', 'omega-design'),
							checked: !!attributes.autoplay,
							onChange: function (value) { setAttributes({ autoplay: value }); }
						}),
						attributes.autoplay && createElement(RangeControl, {
							label: __('Autoplay speed (ms)', 'omega-design'),
							value: attributes.autoplaySpeed,
							min: 2000,
							max: 12000,
							step: 500,
							onChange: function (value) { setAttributes({ autoplaySpeed: value }); }
						}),
						createElement(ToggleControl, {
							label: __('Loop', 'omega-design'),
							checked: !!attributes.loop,
							onChange: function (value) { setAttributes({ loop: value }); }
						}),
						createElement(ToggleControl, {
							label: __('Show arrows', 'omega-design'),
							checked: !!attributes.showArrows,
							onChange: function (value) { setAttributes({ showArrows: value }); }
						}),
						createElement(ToggleControl, {
							label: __('Show dots', 'omega-design'),
							checked: !!attributes.showDots,
							onChange: function (value) { setAttributes({ showDots: value }); }
						}),
						createElement(RangeControl, {
							label: __('Slides per view (desktop)', 'omega-design'),
							value: attributes.slidesPerView,
							min: 1,
							max: 6,
							onChange: function (value) { setAttributes({ slidesPerView: value }); }
						}),
						createElement(RangeControl, {
							label: __('Slides per view (tablet)', 'omega-design'),
							value: attributes.slidesPerViewTablet,
							min: 1,
							max: 4,
							onChange: function (value) { setAttributes({ slidesPerViewTablet: value }); }
						}),
						createElement(RangeControl, {
							label: __('Slides per view (mobile)', 'omega-design'),
							value: attributes.slidesPerViewMobile,
							min: 1,
							max: 2,
							onChange: function (value) { setAttributes({ slidesPerViewMobile: value }); }
						})
					),
					createElement(
						PanelBody,
						{ title: __('Effect & Interactivity', 'omega-design'), initialOpen: true },
						createElement(SelectControl, {
							label: __('Transition effect', 'omega-design'),
							value: attributes.effect || 'slide',
							options: EFFECT_OPTIONS,
							onChange: function (value) { setAttributes({ effect: value }); },
							help: __('Fade/Zoom/Coverflow always show one slide at a time regardless of "Slides per view".', 'omega-design')
						}),
						createElement(ToggleControl, {
							label: __('Peek next slide', 'omega-design'),
							checked: !!attributes.showPeek,
							onChange: function (value) { setAttributes({ showPeek: value }); },
							help: __('Shows a sliver of the next slide at the edge, as a hint there is more to scroll to. Slide effect only.', 'omega-design')
						}),
						createElement(ToggleControl, {
							label: __('Show thumbnail navigation', 'omega-design'),
							checked: !!attributes.showThumbnails,
							onChange: function (value) { setAttributes({ showThumbnails: value }); }
						}),
						createElement(ToggleControl, {
							label: __('Show autoplay progress bar', 'omega-design'),
							checked: !!attributes.showProgressBar,
							onChange: function (value) { setAttributes({ showProgressBar: value }); },
							help: __('Replaces the dots with a countdown bar until the next slide.', 'omega-design')
						})
					)
				),
				createElement('div', innerBlocksProps)
			);
		},

		/**
		 * Deliberately the simplest possible shape: one wrapper element that
		 * is BOTH useBlockProps.save() and useInnerBlocksProps.save() merged
		 * into a single div, exactly like core/group - no wrapping track/
		 * viewport element around the slides, no baked-in arrows/dots.
		 *
		 * Nesting InnerBlocks on a non-root element (this block's earlier
		 * shape: root > track-div > slides, with arrows/dots as further
		 * siblings) is a normal, supported pattern in principle, but it hit
		 * a real bug: the editor's own "content generated by save()" vs
		 * "content retrieved from post body" comparison (validateBlock() in
		 * @wordpress/blocks) always computes the *expected* side with
		 * innerBlocks: [] (see getSaveContent -> getSaveElement's default
		 * parameter), relying on the *stored* side also reducing to an
		 * empty "shell" in the children region for the comparison to pass.
		 * For this custom block that shell apparently didn't reduce the
		 * same way core Group's does, so hand-authored slider markup (as
		 * used in this theme's own landing-*.php patterns, which embed
		 * pattern content as raw HTML rather than ever having been saved by
		 * the block editor itself) intermittently showed a false "block
		 * contains unexpected or invalid content" warning on first edit.
		 * The arrows/dots were also a real, separate mismatch: they used to
		 * be static markup with a hardcoded English label baked into
		 * save(), while several patterns overrode that label text by hand
		 * (e.g. "Previous testimonial") - content save() could never
		 * actually produce. Collapsing to one element (matching Group's
		 * proven-reliable shape) and moving arrows/dots entirely to runtime
		 * DOM built by view.js - reading their label text from real,
		 * always-attribute-driven data-* attributes - removes both sources
		 * of mismatch at once instead of working around them.
		 */
		save: function (props) {
			var attributes = props.attributes;
			var blockProps = useBlockProps.save({
				className: 'omega-slider',
				'data-autoplay': attributes.autoplay ? '1' : '0',
				'data-autoplay-speed': attributes.autoplaySpeed,
				'data-loop': attributes.loop ? '1' : '0',
				'data-arrows': attributes.showArrows ? '1' : '0',
				'data-dots': attributes.showDots ? '1' : '0',
				'data-spv': attributes.slidesPerView,
				'data-spv-tablet': attributes.slidesPerViewTablet,
				'data-spv-mobile': attributes.slidesPerViewMobile,
				'data-gap': attributes.gap,
				'data-prev-label': attributes.prevLabel,
				'data-next-label': attributes.nextLabel,
				'data-dots-label': attributes.dotsLabel,
				'data-effect': attributes.effect || 'slide',
				'data-thumbnails': attributes.showThumbnails ? '1' : '0',
				'data-progress-bar': attributes.showProgressBar ? '1' : '0',
				'data-peek': attributes.showPeek ? '1' : '0'
			});
			var innerBlocksProps = useInnerBlocksProps.save(blockProps);

			return createElement('div', innerBlocksProps);
		}
	});
})(window.wp);
