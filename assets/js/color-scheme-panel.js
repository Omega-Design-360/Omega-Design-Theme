/**
 * "Color Scheme" panel in the editor sidebar's Page/Post/Template tab -
 * chooses the palette for the WHOLE page (header, content, footer):
 * Default, the site's Global palette, or this page's own Custom colors.
 * Pages/posts store it in post meta, templates in one site option keyed by
 * template slug; a page's own setting wins over its template's. Front-end
 * output: includes/core/color_scheme.php.
 *
 * The editor canvas gets the same body classes/inline CSS the front end
 * does, so the preview matches the live page while editing.
 */
(function (wp, config) {
	'use strict';

	if (!wp || !wp.plugins || !config) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var useEntityProp = wp.coreData.useEntityProp;
	var components = wp.components;
	var __ = wp.i18n.__;
	var PluginDocumentSettingPanel = window.OmegaDesignEditor && window.OmegaDesignEditor.documentSettingPanel();

	if (!PluginDocumentSettingPanel || !components.Dropdown || !components.ColorPicker) {
		return;
	}

	var LABELS = {
		'primary': __('Primary', 'omega-design'),
		'secondary': __('Secondary', 'omega-design'),
		'accent': __('Accent', 'omega-design'),
		'heading': __('Headings', 'omega-design'),
		'body-text': __('Body text', 'omega-design'),
		'background': __('Background', 'omega-design'),
		'page-background': __('Page background', 'omega-design'),
		'surface': __('Surface / cards', 'omega-design'),
		'border': __('Border', 'omega-design'),
		'button-background': __('Button background', 'omega-design'),
		'button-text': __('Button text', 'omega-design'),
		'success': __('Success', 'omega-design'),
		'warning': __('Warning / stars', 'omega-design'),
		'danger': __('Danger', 'omega-design')
	};

	var MODE_CLASSES = ['omega-page-scheme-global', 'omega-page-scheme-custom', 'omega-scheme-active'];
	var STYLE_ID = 'omega-page-scheme-css';

	function normalize(scheme) {
		return scheme && typeof scheme === 'object' && !Array.isArray(scheme) ? scheme : {};
	}

	function canvasDocument() {
		var frame = document.querySelector('iframe[name="editor-canvas"]');
		try {
			return frame && frame.contentDocument ? frame.contentDocument : document;
		} catch (e) {
			return document;
		}
	}

	function canvasRoot(doc) {
		return doc.querySelector('.editor-styles-wrapper') || doc.body;
	}

	/** Same output as color_scheme.php build_custom_css(). */
	function buildCustomCss(colors) {
		var declarations = '';
		config.slugs.forEach(function (slug) {
			if (colors && colors[slug]) {
				declarations += '--wp--preset--color--' + slug + ':' + colors[slug] + ' !important;';
			}
		});
		return declarations ? '.omega-page-scheme-custom,.omega-page-scheme-custom .omega-landing{' + declarations + '}' : '';
	}

	function applyToCanvas(scheme) {
		var doc = canvasDocument();
		var root = canvasRoot(doc);
		if (!root) {
			return;
		}

		MODE_CLASSES.forEach(function (name) { root.classList.remove(name); });
		var style = doc.getElementById(STYLE_ID);

		if (scheme.mode === 'global' || scheme.mode === 'custom') {
			root.classList.add('omega-page-scheme-' + scheme.mode, 'omega-scheme-active');
		}

		var css = scheme.mode === 'custom' ? buildCustomCss(scheme.colors) : '';
		if (!css) {
			if (style) { style.remove(); }
			return;
		}
		if (!style) {
			style = doc.createElement('style');
			style.id = STYLE_ID;
			(doc.head || doc.body).appendChild(style);
		}
		if (style.textContent !== css) {
			style.textContent = css;
		}
	}

	/** Palette values currently in effect in the canvas (content wrapper if any, else the page). */
	function readCanvasColors() {
		var doc = canvasDocument();
		var target = doc.querySelector('.omega-landing') || canvasRoot(doc);
		var out = {};
		if (!target) {
			return out;
		}
		var computed = (doc.defaultView || window).getComputedStyle(target);
		config.slugs.forEach(function (slug) {
			var value = computed.getPropertyValue('--wp--preset--color--' + slug).trim();
			if (value) { out[slug] = value; }
		});
		return out;
	}

	/** The template's own colors - read with this page's scheme briefly switched off. */
	function readDefaultColors() {
		applyToCanvas({});
		return readCanvasColors();
	}

	function toHex(value) {
		if (!value || value.charAt(0) === '#') {
			return value;
		}
		var match = value.match(/rgba?\(\s*(\d+)[,\s]+(\d+)[,\s]+(\d+)/);
		if (!match) {
			return value;
		}
		return '#' + [match[1], match[2], match[3]].map(function (n) {
			return ('0' + parseInt(n, 10).toString(16)).slice(-2);
		}).join('');
	}

	function swatch(color) {
		return el('span', {
			style: {
				display: 'inline-block', width: '20px', height: '20px', borderRadius: '50%', flexShrink: 0,
				background: color || 'transparent', boxShadow: 'inset 0 0 0 1px rgba(0,0,0,0.2)'
			}
		});
	}

	function ColorRow(props) {
		var own = props.value || '';
		var shown = own || props.effective || '';

		return el(components.Dropdown, {
			popoverProps: { placement: 'left-start' },
			renderToggle: function (toggle) {
				return el(components.Button, {
					onClick: toggle.onToggle,
					'aria-expanded': toggle.isOpen,
					style: { width: '100%', justifyContent: 'flex-start', gap: '8px', height: 'auto', padding: '6px 8px', boxShadow: 'inset 0 0 0 1px #ddd' }
				},
				swatch(shown),
				el('span', { style: { flex: 1, textAlign: 'left' } }, props.label),
				el('code', { style: { fontSize: '11px', opacity: own ? 1 : 0.55 } }, own || toHex(props.effective) || '-'));
			},
			renderContent: function () {
				return el('div', { style: { padding: '8px' } },
					el(components.ColorPicker, {
						color: toHex(shown) || '#ffffff',
						enableAlpha: true,
						onChange: props.onChange
					}),
					own && el(components.Button, {
						variant: 'secondary',
						isDestructive: true,
						style: { marginTop: '8px' },
						onClick: function () { props.onChange(''); }
					}, __('Reset to default', 'omega-design'))
				);
			}
		});
	}

	function SchemeFields(props) {
		var scheme = props.scheme;
		var colors = scheme.colors || {};
		var effective = props.effective;

		function update(changes) {
			var next = Object.assign({}, scheme, changes);
			if (!next.mode) { delete next.mode; }
			if (next.colors && !Object.keys(next.colors).length) { delete next.colors; }
			props.onChange(next);
		}

		function setColor(slug, value) {
			var next = Object.assign({}, colors);
			if (value) { next[slug] = value; } else { delete next[slug]; }
			update({ colors: next });
		}

		var modes = [
			{ value: '', label: props.isTemplate ? __('Default', 'omega-design') : __('Default (use template setting)', 'omega-design') },
			{ value: 'global', label: __('Global - site colors', 'omega-design') },
			{ value: 'custom', label: props.isTemplate ? __('Custom - this template\'s colors', 'omega-design') : __('Custom - this page\'s colors', 'omega-design') }
		];

		var help = {
			'': props.isTemplate
				? __('Landing page designs keep their own built-in colors; everything else uses the site colors.', 'omega-design')
				: __('Uses the template\'s setting. Landing page designs keep their own built-in colors.', 'omega-design'),
			'global': __('Uses the site-wide palette (Appearance > Editor > Styles > Colors) for the whole page, including inside landing page designs.', 'omega-design'),
			'custom': __('Recolors the whole page - header, content and footer. Colors left unset keep their default.', 'omega-design')
		};

		return el('div', { style: { display: 'grid', gap: '12px' } },
			el(components.SelectControl, {
				label: __('Color scheme', 'omega-design'),
				value: scheme.mode || '',
				options: modes,
				help: help[scheme.mode || ''],
				__nextHasNoMarginBottom: true,
				onChange: function (value) { update({ mode: value }); }
			}),
			scheme.mode === 'custom' && el('div', { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' } },
				el(components.Button, {
					variant: 'secondary',
					onClick: function () {
						var defaults = readDefaultColors();
						var next = {};
						Object.keys(defaults).forEach(function (slug) { next[slug] = toHex(defaults[slug]); });
						update({ colors: next });
					}
				}, __('Start from template colors', 'omega-design')),
				Object.keys(colors).length > 0 && el(components.Button, {
					variant: 'tertiary',
					isDestructive: true,
					onClick: function () { update({ colors: {} }); }
				}, __('Reset all', 'omega-design'))
			),
			scheme.mode === 'custom' && config.slugs.map(function (slug) {
				return el(ColorRow, {
					key: slug,
					label: LABELS[slug] || slug,
					value: colors[slug],
					effective: effective[slug],
					onChange: function (value) { setColor(slug, value); }
				});
			})
		);
	}

	/** Re-applies the scheme to the canvas whenever it changes (and after the canvas iframe reloads). */
	function useCanvasScheme(scheme) {
		var key = JSON.stringify(scheme);
		var state = useState({});
		var effective = state[0];
		var setEffective = state[1];

		useEffect(function () {
			applyToCanvas(scheme);
			setEffective(readCanvasColors());
			var timer = setInterval(function () {
				var root = canvasRoot(canvasDocument());
				var wanted = scheme.mode ? 'omega-page-scheme-' + scheme.mode : '';
				if (root && wanted && !root.classList.contains(wanted)) {
					applyToCanvas(scheme);
				}
			}, 1000);
			return function () { clearInterval(timer); };
		}, [key]);

		return effective;
	}

	function TemplatePanel(props) {
		var entity = useEntityProp('root', 'site', config.optionKey);
		var all = normalize(entity[0]);
		var scheme = normalize(all[props.slug]);
		var effective = useCanvasScheme(scheme);

		if (!config.canManage) {
			return el('p', {}, __('Only administrators can change a template\'s color scheme.', 'omega-design'));
		}

		return el(SchemeFields, {
			scheme: scheme,
			effective: effective,
			isTemplate: true,
			onChange: function (next) {
				var updated = Object.assign({}, all);
				if (next.mode) { updated[props.slug] = next; } else { delete updated[props.slug]; }
				entity[1](updated);
			}
		});
	}

	function PostPanel(props) {
		var editPost = useDispatch('core/editor').editPost;
		var own = normalize(props.meta[config.metaKey]);
		// Only administrators can read site settings; others just preview their own setting.
		var templates = normalize(useEntityProp('root', 'site', config.optionKey)[0]);

		// Preview: this page's own setting, else its template's.
		var inherited = normalize(templates[props.templateSlug]);
		var effective = useCanvasScheme(own.mode ? own : inherited);

		return el(SchemeFields, {
			scheme: own,
			effective: effective,
			isTemplate: false,
			onChange: function (next) {
				var meta = {};
				meta[config.metaKey] = next;
				editPost({ meta: meta });
			}
		});
	}

	function ColorSchemePanel() {
		var data = useSelect(function (select) {
			var editor = select('core/editor');
			var type = editor.getCurrentPostType();
			var id = editor.getCurrentPostId();
			return {
				type: type,
				id: id,
				meta: editor.getEditedPostAttribute('meta'),
				template: editor.getEditedPostAttribute('template'),
				slug: editor.getEditedPostAttribute('slug')
			};
		}, []);

		if (!data.type || data.type === 'wp_template_part' || data.type === 'wp_navigation' || data.type === 'wp_block') {
			return null;
		}

		var isTemplate = data.type === 'wp_template';
		if (!isTemplate && (!data.meta || typeof data.meta !== 'object')) {
			return null;
		}

		var templateSlug = '';
		if (isTemplate) {
			templateSlug = data.slug || (typeof data.id === 'string' && data.id.indexOf('//') !== -1 ? data.id.split('//')[1] : '');
		} else {
			templateSlug = data.template || (data.type === 'page' ? 'page' : data.type === 'post' ? 'single' : 'single-' + data.type);
		}

		return el(PluginDocumentSettingPanel, {
			name: 'omega-color-scheme',
			title: __('Color Scheme', 'omega-design'),
			className: 'omega-color-scheme-panel'
		},
		isTemplate
			? el(TemplatePanel, { slug: templateSlug })
			: el(PostPanel, { meta: data.meta, templateSlug: templateSlug }));
	}

	wp.plugins.registerPlugin('omega-color-scheme', { render: ColorSchemePanel });
})(window.wp, window.omegaColorScheme);
