/**
 * "Omega Content Slider" block (omega-design/content-slider) - a fully
 * self-contained slide builder, deliberately NOT built from InnerBlocks or
 * ordinary nested WordPress blocks (unlike the rest of this theme, which
 * sticks to native block composition everywhere else). The admin picks how
 * many slides they want, then clicks directly on a slide's background, its
 * image, or its text/button panel in the canvas preview to jump to that
 * region's own controls - background color or image, image size/borders/
 * radius, text/button colors, and per-slide entrance animation. It's a
 * dynamic block (see render.php): save() returns null and PHP renders
 * straight from `attributes.slides` on every request, so there's no static
 * markup to keep in sync and no "attempt recovery" risk.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useRef = wp.element.useRef;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var Button = wp.components.Button;
	var ButtonGroup = wp.components.ButtonGroup;
	var __ = wp.i18n.__;

	var EFFECT_OPTIONS = [
		{ label: __('Slide', 'omega-design'), value: 'slide' },
		{ label: __('Fade', 'omega-design'), value: 'fade' },
		{ label: __('Zoom / Ken Burns', 'omega-design'), value: 'zoom' },
		{ label: __('Vertical', 'omega-design'), value: 'vertical' },
		{ label: __('3D Coverflow (best with a background image, not side-by-side text)', 'omega-design'), value: 'coverflow' },
		{ label: __('Expanding Cards (best with a background image - a preview card visually morphs into the full-bleed photo)', 'omega-design'), value: 'cards' }
	];

	var ANIMATION_OPTIONS = [
		{ label: __('None', 'omega-design'), value: '' },
		{ label: __('Fade Up', 'omega-design'), value: 'fade-up' },
		{ label: __('Fade Down', 'omega-design'), value: 'fade-down' },
		{ label: __('Fade In', 'omega-design'), value: 'fade-in' },
		{ label: __('Slide From Left', 'omega-design'), value: 'slide-left' },
		{ label: __('Slide From Right', 'omega-design'), value: 'slide-right' },
		{ label: __('Zoom In', 'omega-design'), value: 'zoom-in' }
	];

	var OBJECT_FIT_OPTIONS = [
		{ label: __('Cover', 'omega-design'), value: 'cover' },
		{ label: __('Contain', 'omega-design'), value: 'contain' },
		{ label: __('Fill', 'omega-design'), value: 'fill' },
		{ label: __('None', 'omega-design'), value: 'none' }
	];

	var BG_SIZE_OPTIONS = [
		{ label: __('Cover', 'omega-design'), value: 'cover' },
		{ label: __('Contain', 'omega-design'), value: 'contain' }
	];

	var BORDER_STYLE_OPTIONS = [
		{ label: __('Solid', 'omega-design'), value: 'solid' },
		{ label: __('Dashed', 'omega-design'), value: 'dashed' },
		{ label: __('Dotted', 'omega-design'), value: 'dotted' },
		{ label: __('Double', 'omega-design'), value: 'double' }
	];

	var CONTENT_POSITION_OPTIONS = [
		{ label: __('Image left, text right', 'omega-design'), value: 'left' },
		{ label: __('Text left, image right', 'omega-design'), value: 'right' }
	];

	var VERTICAL_ALIGN_OPTIONS = [
		{ label: __('Top', 'omega-design'), value: 'top' },
		{ label: __('Center', 'omega-design'), value: 'center' },
		{ label: __('Bottom', 'omega-design'), value: 'bottom' }
	];

	var TEXT_ALIGN_OPTIONS = [
		{ label: __('Left', 'omega-design'), value: 'left' },
		{ label: __('Center', 'omega-design'), value: 'center' },
		{ label: __('Right', 'omega-design'), value: 'right' }
	];

	function createDefaultSlide() {
		return {
			id: 'slide-' + Date.now().toString(36) + '-' + Math.floor(Math.random() * 10000).toString(36),
			imageUrl: '', imageId: 0, imageAlt: '',
			imageWidth: '100%', imageHeight: '420px', imageObjectFit: 'cover',
			imageBorderWidth: 0, imageBorderColor: '#1c1c1c', imageBorderStyle: 'solid', imageBorderRadius: 12,
			contentPosition: 'left',
			contentVerticalAlign: 'center',
			textAlign: 'left',
			backgroundColor: '#f7f3f0',
			backgroundImageUrl: '', backgroundImageId: 0, backgroundImageSize: 'cover',
			backgroundOverlayColor: '#000000', backgroundOverlayOpacity: 0,
			heading: __('Slide heading', 'omega-design'), headingColor: '#1c1c1c',
			bodyText: __('A short line of supporting copy for this slide.', 'omega-design'), bodyColor: '#1c1c1c',
			buttonText: __('Learn More', 'omega-design'), buttonUrl: '#',
			buttonBackgroundColor: '#1fbb00', buttonTextColor: '#ffffff', buttonBorderRadius: 6,
			animation: 'fade-up'
		};
	}

	function slideStyle(slide) {
		return slide.backgroundColor ? { backgroundColor: slide.backgroundColor } : {};
	}

	function bgImageStyle(slide) {
		if (!slide.backgroundImageUrl) { return null; }
		return {
			position: 'absolute', inset: 0,
			backgroundImage: 'url(' + slide.backgroundImageUrl + ')',
			backgroundSize: slide.backgroundImageSize || 'cover',
			backgroundPosition: 'center', backgroundRepeat: 'no-repeat',
			zIndex: 0
		};
	}

	function overlayStyle(slide) {
		var opacity = (slide.backgroundOverlayOpacity || 0) / 100;
		if (opacity <= 0) { return null; }
		return {
			position: 'absolute', inset: 0, zIndex: 1,
			backgroundColor: slide.backgroundOverlayColor || '#000000',
			opacity: opacity
		};
	}

	function imageStyle(slide) {
		var style = {
			width: slide.imageWidth || '100%',
			height: slide.imageHeight || '420px',
			objectFit: slide.imageObjectFit || 'cover',
			borderRadius: (slide.imageBorderRadius || 0) + 'px'
		};
		if (slide.imageBorderWidth > 0) {
			style.border = slide.imageBorderWidth + 'px ' + (slide.imageBorderStyle || 'solid') + ' ' + (slide.imageBorderColor || '#000000');
		}
		return style;
	}

	function buttonStyle(slide) {
		var style = { borderRadius: (slide.buttonBorderRadius || 0) + 'px' };
		if (slide.buttonBackgroundColor) { style.backgroundColor = slide.buttonBackgroundColor; }
		if (slide.buttonTextColor) { style.color = slide.buttonTextColor; }
		return style;
	}

	function contentStyle(slide) {
		var selfMap = { top: 'flex-start', center: 'center', bottom: 'flex-end' };
		var itemsMap = { left: 'flex-start', center: 'center', right: 'flex-end' };
		var vAlign = slide.contentVerticalAlign || 'center';
		var hAlign = slide.textAlign || 'left';
		return {
			alignSelf: selfMap[vAlign] || 'center',
			alignItems: itemsMap[hAlign] || 'flex-start',
			textAlign: hAlign
		};
	}

	/** A compact labeled native color input - simpler and more predictable across WP versions than a Popover-based color picker, and every field here needs one. */
	function colorField(label, value, onChange) {
		return createElement(
			'div',
			{ style: { marginBottom: '16px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' } },
			createElement('label', { style: { fontSize: '12px' } }, label),
			createElement('input', {
				type: 'color',
				value: /^#[0-9a-fA-F]{6}$/.test(value) ? value : '#000000',
				onChange: function (e) { onChange(e.target.value); },
				style: { width: '48px', height: '28px', border: '1px solid #ddd', borderRadius: '2px', cursor: 'pointer', padding: '0' }
			})
		);
	}

	registerBlockType('omega-design/content-slider', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var slides = attributes.slides && attributes.slides.length ? attributes.slides : [createDefaultSlide()];

			var indexHook = useState(0);
			var currentIndex = Math.min(indexHook[0], slides.length - 1);
			var setCurrentIndex = indexHook[1];

			var regionHook = useState('background');
			var activeRegion = regionHook[0];
			var setActiveRegion = regionHook[1];

			var panelsHook = useState({ background: true, image: false, text: false, behavior: false });
			var openPanels = panelsHook[0];
			var setOpenPanels = panelsHook[1];

			var panelRefs = {
				background: useRef(null),
				image: useRef(null),
				text: useRef(null)
			};

			var blockProps = useBlockProps({ className: 'omega-content-slider--editor-preview' });
			var currentSlide = slides[currentIndex] || slides[0];

			function updateSlides(newSlides) {
				setAttributes({ slides: newSlides });
			}

			function updateCurrentSlide(changes) {
				var newSlides = slides.slice();
				newSlides[currentIndex] = Object.assign({}, currentSlide, changes);
				updateSlides(newSlides);
			}

			function setSlideCount(count) {
				count = Math.max(1, Math.min(12, count));
				var newSlides = slides.slice();
				if (count > newSlides.length) {
					while (newSlides.length < count) { newSlides.push(createDefaultSlide()); }
				} else if (count < newSlides.length) {
					newSlides = newSlides.slice(0, count);
				}
				updateSlides(newSlides);
				if (currentIndex >= count) { setCurrentIndex(count - 1); }
			}

			function removeCurrentSlide() {
				if (slides.length <= 1) { return; }
				var newSlides = slides.slice();
				newSlides.splice(currentIndex, 1);
				updateSlides(newSlides);
				setCurrentIndex(Math.max(0, currentIndex - 1));
			}

			function duplicateCurrentSlide() {
				var newSlides = slides.slice();
				var copy = Object.assign({}, currentSlide, { id: createDefaultSlide().id });
				newSlides.splice(currentIndex + 1, 0, copy);
				updateSlides(newSlides);
				setCurrentIndex(currentIndex + 1);
			}

			/** Clicking a region in the canvas preview jumps straight to its own panel - opens it (without closing the others) and scrolls it into view. */
			function focusRegion(key) {
				setActiveRegion(key);
				setOpenPanels(function (prev) {
					var next = Object.assign({}, prev);
					next[key] = true;
					return next;
				});
				setTimeout(function () {
					if (panelRefs[key] && panelRefs[key].current && panelRefs[key].current.scrollIntoView) {
						panelRefs[key].current.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				}, 60);
			}

			function regionClass(key) {
				return 'omega-content-slider__region' + (activeRegion === key ? ' omega-content-slider__region--active' : '');
			}

			var slideSwitcher = createElement(
				'div',
				{ style: { display: 'flex', flexWrap: 'wrap', gap: '4px', marginBottom: '12px' } },
				slides.map(function (slide, i) {
					return createElement(Button, {
						key: slide.id,
						variant: i === currentIndex ? 'primary' : 'secondary',
						size: 'small',
						onClick: function () { setCurrentIndex(i); }
					}, String(i + 1));
				})
			);

			var slideActions = createElement(
				ButtonGroup,
				{ style: { marginBottom: '16px' } },
				createElement(Button, { variant: 'secondary', size: 'small', onClick: duplicateCurrentSlide }, __('Duplicate', 'omega-design')),
				createElement(Button, { variant: 'secondary', size: 'small', isDestructive: true, disabled: slides.length <= 1, onClick: removeCurrentSlide }, __('Delete', 'omega-design'))
			);

			// ---- Canvas preview: three clickable regions ----

			var mediaRegion = createElement(
				'div',
				{
					className: 'omega-content-slider__media ' + regionClass('image'),
					onClick: function (e) { e.stopPropagation(); focusRegion('image'); }
				},
				currentSlide.imageUrl
					? createElement('img', { src: currentSlide.imageUrl, alt: currentSlide.imageAlt || '', style: imageStyle(currentSlide) })
					: createElement('div', {
						style: Object.assign({
							display: 'flex', alignItems: 'center', justifyContent: 'center',
							background: '#e0e0e0', color: '#757575', fontSize: '13px'
						}, imageStyle(currentSlide))
					}, __('Click to add an image', 'omega-design'))
			);

			var contentRegion = createElement(
				'div',
				{
					className: 'omega-content-slider__content omega-content-slider__content--' + (currentSlide.contentPosition || 'left') + ' ' + regionClass('text'),
					style: contentStyle(currentSlide),
					onClick: function (e) { e.stopPropagation(); focusRegion('text'); }
				},
				createElement('h2', { className: 'omega-content-slider__heading', style: currentSlide.headingColor ? { color: currentSlide.headingColor } : {} }, currentSlide.heading),
				currentSlide.bodyText ? createElement('p', { className: 'omega-content-slider__body', style: currentSlide.bodyColor ? { color: currentSlide.bodyColor } : {} }, currentSlide.bodyText) : null,
				currentSlide.buttonText ? createElement('span', { className: 'omega-content-slider__button', style: buttonStyle(currentSlide) }, currentSlide.buttonText) : null
			);

			var bg = bgImageStyle(currentSlide);
			var overlay = overlayStyle(currentSlide);

			var previewSlide = createElement(
				'div',
				{
					className: 'omega-content-slider__slide ' + regionClass('background'),
					style: Object.assign({ height: attributes.sliderHeight || '480px' }, slideStyle(currentSlide)),
					onClick: function () { focusRegion('background'); }
				},
				bg ? createElement('div', { style: bg }) : null,
				overlay ? createElement('div', { style: overlay }) : null,
				mediaRegion,
				contentRegion
			);

			// ---- Inspector panels ----

			var backgroundPanel = createElement(
				PanelBody,
				{
					title: __('Selected Slide: Background', 'omega-design'),
					opened: openPanels.background,
					onToggle: function (isOpen) { setOpenPanels(Object.assign({}, openPanels, { background: isOpen })); }
				},
				createElement('div', { ref: panelRefs.background },
					colorField(__('Background color', 'omega-design'), currentSlide.backgroundColor, function (v) { updateCurrentSlide({ backgroundColor: v }); }),
					createElement(
						MediaUploadCheck,
						null,
						createElement(MediaUpload, {
							allowedTypes: ['image'],
							value: currentSlide.backgroundImageId,
							onSelect: function (media) { updateCurrentSlide({ backgroundImageUrl: media.url, backgroundImageId: media.id }); },
							render: function (openMedia) {
								return createElement(
									'div',
									{ style: { marginBottom: '12px' } },
									currentSlide.backgroundImageUrl
										? createElement('img', { src: currentSlide.backgroundImageUrl, style: { width: '100%', display: 'block', marginBottom: '6px', borderRadius: '4px' } })
										: null,
									createElement(Button, { variant: 'secondary', onClick: openMedia.open }, currentSlide.backgroundImageUrl ? __('Replace Background Image', 'omega-design') : __('Add Background Image', 'omega-design')),
									currentSlide.backgroundImageUrl ? createElement(Button, {
										variant: 'tertiary', isDestructive: true,
										onClick: function () { updateCurrentSlide({ backgroundImageUrl: '', backgroundImageId: 0 }); }
									}, __('Remove', 'omega-design')) : null
								);
							}
						})
					),
					currentSlide.backgroundImageUrl ? createElement(SelectControl, {
						label: __('Background image size', 'omega-design'),
						value: currentSlide.backgroundImageSize,
						options: BG_SIZE_OPTIONS,
						onChange: function (v) { updateCurrentSlide({ backgroundImageSize: v }); }
					}) : null,
					currentSlide.backgroundImageUrl ? colorField(__('Darken/tint overlay color', 'omega-design'), currentSlide.backgroundOverlayColor, function (v) { updateCurrentSlide({ backgroundOverlayColor: v }); }) : null,
					currentSlide.backgroundImageUrl ? createElement(RangeControl, {
						label: __('Overlay opacity (%)', 'omega-design'),
						value: currentSlide.backgroundOverlayOpacity,
						min: 0, max: 100,
						onChange: function (v) { updateCurrentSlide({ backgroundOverlayOpacity: v }); }
					}) : null,
					createElement(TextControl, {
						label: __('Slider height (CSS value, applies to all slides)', 'omega-design'),
						value: attributes.sliderHeight,
						help: __('e.g. 480px, 60vh', 'omega-design'),
						onChange: function (v) { setAttributes({ sliderHeight: v }); }
					})
				)
			);

			var imagePanel = createElement(
				PanelBody,
				{
					title: __('Selected Slide: Image', 'omega-design'),
					opened: openPanels.image,
					onToggle: function (isOpen) { setOpenPanels(Object.assign({}, openPanels, { image: isOpen })); }
				},
				createElement('div', { ref: panelRefs.image },
					createElement(
						MediaUploadCheck,
						null,
						createElement(MediaUpload, {
							allowedTypes: ['image'],
							value: currentSlide.imageId,
							onSelect: function (media) { updateCurrentSlide({ imageUrl: media.url, imageId: media.id, imageAlt: media.alt || '' }); },
							render: function (openMedia) {
								return createElement(
									'div',
									{ style: { marginBottom: '12px' } },
									currentSlide.imageUrl
										? createElement('img', { src: currentSlide.imageUrl, style: { width: '100%', display: 'block', marginBottom: '6px', borderRadius: '4px' } })
										: null,
									createElement(Button, { variant: 'primary', onClick: openMedia.open }, currentSlide.imageUrl ? __('Replace Image', 'omega-design') : __('Select Image', 'omega-design'))
								);
							}
						})
					),
					createElement(TextControl, {
						label: __('Alt text', 'omega-design'),
						value: currentSlide.imageAlt,
						onChange: function (v) { updateCurrentSlide({ imageAlt: v }); }
					}),
					createElement(TextControl, {
						label: __('Image width (CSS value)', 'omega-design'),
						value: currentSlide.imageWidth,
						help: __('e.g. 100%, 480px', 'omega-design'),
						onChange: function (v) { updateCurrentSlide({ imageWidth: v }); }
					}),
					createElement(TextControl, {
						label: __('Image height (CSS value)', 'omega-design'),
						value: currentSlide.imageHeight,
						help: __('e.g. 420px, 60vh', 'omega-design'),
						onChange: function (v) { updateCurrentSlide({ imageHeight: v }); }
					}),
					createElement(SelectControl, {
						label: __('Image fit', 'omega-design'),
						value: currentSlide.imageObjectFit,
						options: OBJECT_FIT_OPTIONS,
						onChange: function (v) { updateCurrentSlide({ imageObjectFit: v }); }
					}),
					createElement(RangeControl, {
						label: __('Border radius (px)', 'omega-design'),
						value: currentSlide.imageBorderRadius,
						min: 0, max: 100,
						onChange: function (v) { updateCurrentSlide({ imageBorderRadius: v }); }
					}),
					createElement(RangeControl, {
						label: __('Border width (px)', 'omega-design'),
						value: currentSlide.imageBorderWidth,
						min: 0, max: 20,
						onChange: function (v) { updateCurrentSlide({ imageBorderWidth: v }); }
					}),
					currentSlide.imageBorderWidth > 0 ? createElement(SelectControl, {
						label: __('Border style', 'omega-design'),
						value: currentSlide.imageBorderStyle,
						options: BORDER_STYLE_OPTIONS,
						onChange: function (v) { updateCurrentSlide({ imageBorderStyle: v }); }
					}) : null,
					currentSlide.imageBorderWidth > 0 ? colorField(__('Border color', 'omega-design'), currentSlide.imageBorderColor, function (v) { updateCurrentSlide({ imageBorderColor: v }); }) : null
				)
			);

			var textPanel = createElement(
				PanelBody,
				{
					title: __('Selected Slide: Text & Button', 'omega-design'),
					opened: openPanels.text,
					onToggle: function (isOpen) { setOpenPanels(Object.assign({}, openPanels, { text: isOpen })); }
				},
				createElement('div', { ref: panelRefs.text },
					createElement(TextControl, {
						label: __('Heading', 'omega-design'),
						value: currentSlide.heading,
						onChange: function (v) { updateCurrentSlide({ heading: v }); }
					}),
					colorField(__('Heading color', 'omega-design'), currentSlide.headingColor, function (v) { updateCurrentSlide({ headingColor: v }); }),
					createElement(TextareaControl, {
						label: __('Body text', 'omega-design'),
						value: currentSlide.bodyText,
						onChange: function (v) { updateCurrentSlide({ bodyText: v }); }
					}),
					colorField(__('Body text color', 'omega-design'), currentSlide.bodyColor, function (v) { updateCurrentSlide({ bodyColor: v }); }),
					createElement(TextControl, {
						label: __('Button text', 'omega-design'),
						value: currentSlide.buttonText,
						onChange: function (v) { updateCurrentSlide({ buttonText: v }); }
					}),
					createElement(TextControl, {
						label: __('Button link', 'omega-design'),
						value: currentSlide.buttonUrl,
						onChange: function (v) { updateCurrentSlide({ buttonUrl: v }); }
					}),
					colorField(__('Button background', 'omega-design'), currentSlide.buttonBackgroundColor, function (v) { updateCurrentSlide({ buttonBackgroundColor: v }); }),
					colorField(__('Button text color', 'omega-design'), currentSlide.buttonTextColor, function (v) { updateCurrentSlide({ buttonTextColor: v }); }),
					createElement(RangeControl, {
						label: __('Button border radius (px)', 'omega-design'),
						value: currentSlide.buttonBorderRadius,
						min: 0, max: 60,
						onChange: function (v) { updateCurrentSlide({ buttonBorderRadius: v }); }
					}),
					createElement(SelectControl, {
						label: __('Content position', 'omega-design'),
						value: currentSlide.contentPosition || 'left',
						options: CONTENT_POSITION_OPTIONS,
						help: __('Set independently per slide, so you can alternate left/right for visual rhythm.', 'omega-design'),
						onChange: function (v) { updateCurrentSlide({ contentPosition: v }); }
					}),
					createElement(SelectControl, {
						label: __('Text vertical position', 'omega-design'),
						value: currentSlide.contentVerticalAlign || 'center',
						options: VERTICAL_ALIGN_OPTIONS,
						help: __('Where the text/button panel sits within the slide - top, center or bottom.', 'omega-design'),
						onChange: function (v) { updateCurrentSlide({ contentVerticalAlign: v }); }
					}),
					createElement(SelectControl, {
						label: __('Text alignment', 'omega-design'),
						value: currentSlide.textAlign || 'left',
						options: TEXT_ALIGN_OPTIONS,
						onChange: function (v) { updateCurrentSlide({ textAlign: v }); }
					}),
					createElement(SelectControl, {
						label: __('Entrance animation', 'omega-design'),
						value: currentSlide.animation,
						options: ANIMATION_OPTIONS,
						help: __('Plays every time this slide becomes active.', 'omega-design'),
						onChange: function (v) { updateCurrentSlide({ animation: v }); }
					})
				)
			);

			var behaviorPanel = createElement(
				PanelBody,
				{
					title: __('Slider Behavior', 'omega-design'),
					opened: openPanels.behavior,
					onToggle: function (isOpen) { setOpenPanels(Object.assign({}, openPanels, { behavior: isOpen })); }
				},
				createElement(SelectControl, {
					label: __('Transition effect', 'omega-design'),
					value: attributes.effect,
					options: EFFECT_OPTIONS,
					onChange: function (v) { setAttributes({ effect: v }); }
				}),
				createElement(ToggleControl, {
					label: __('Autoplay', 'omega-design'),
					checked: !!attributes.autoplay,
					onChange: function (v) { setAttributes({ autoplay: v }); }
				}),
				attributes.autoplay ? createElement(RangeControl, {
					label: __('Autoplay speed (ms)', 'omega-design'),
					value: attributes.autoplaySpeed,
					min: 2000, max: 12000, step: 500,
					onChange: function (v) { setAttributes({ autoplaySpeed: v }); }
				}) : null,
				createElement(ToggleControl, {
					label: __('Loop', 'omega-design'),
					checked: !!attributes.loop,
					onChange: function (v) { setAttributes({ loop: v }); }
				}),
				createElement(ToggleControl, {
					label: __('Show arrows', 'omega-design'),
					checked: !!attributes.showArrows,
					onChange: function (v) { setAttributes({ showArrows: v }); }
				}),
				createElement(ToggleControl, {
					label: __('Show dots', 'omega-design'),
					checked: !!attributes.showDots,
					onChange: function (v) { setAttributes({ showDots: v }); }
				})
			);

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __('Slides', 'omega-design'), initialOpen: true },
						createElement(RangeControl, {
							label: __('How many slides?', 'omega-design'),
							value: slides.length,
							min: 1, max: 12,
							onChange: setSlideCount
						}),
						createElement('p', { style: { marginTop: 0 } }, __('Editing slide:', 'omega-design')),
						slideSwitcher,
						slideActions,
						createElement('p', { style: { fontSize: '12px', color: '#757575' } }, __('Tip: click directly on the slide background, its image, or its text in the preview to jump straight to that panel below.', 'omega-design'))
					),
					backgroundPanel,
					imagePanel,
					textPanel,
					behaviorPanel
				),
				createElement(
					'div',
					blockProps,
					createElement(
						'div',
						{ style: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' } },
						createElement(Button, { variant: 'secondary', size: 'small', disabled: currentIndex === 0, onClick: function () { setCurrentIndex(currentIndex - 1); } }, __('← Prev', 'omega-design')),
						createElement('span', { style: { fontSize: '12px', color: '#757575' } }, __('Editing slide', 'omega-design') + ' ' + (currentIndex + 1) + ' / ' + slides.length),
						createElement(Button, { variant: 'secondary', size: 'small', disabled: currentIndex === slides.length - 1, onClick: function () { setCurrentIndex(currentIndex + 1); } }, __('Next →', 'omega-design'))
					),
					previewSlide
				)
			);
		},

		save: function () {
			// Dynamic block - PHP (render.php) renders everything from
			// attributes.slides on every request, so there's no static
			// markup to keep in sync and no block-validation risk.
			return null;
		}
	});
})(window.wp);
