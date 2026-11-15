(function (wp) {
	'use strict';
	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) return;

	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var __ = wp.i18n.__;
	var tocIcon = el('svg', { viewBox: '0 -960 960 960', width: 24, height: 24, fill: 'currentColor' },
		el('path', { d: 'M80-200v-80h400v80H80Zm0-200v-80h200v80H80Zm0-200v-80h200v80H80Zm744 400L670-354q-24 17-52.5 25.5T560-320q-83 0-141.5-58.5T360-520q0-83 58.5-141.5T560-720q83 0 141.5 58.5T760-520q0 29-8.5 57.5T726-410l154 154-56 56ZM560-400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Z' })
	);

	registerBlockType('omega-design/post-toc', {
		icon: tocIcon,
		edit: function (props) {
			var attributes = props.attributes;
			var levels = attributes.levels || [];
			var blockProps = useBlockProps({ className: 'omega-post-toc-editor' });

			function toggleLevel(level, enabled) {
				var next = levels.filter(function (value) { return value !== level; });
				if (enabled) next.push(level);
				next.sort();
				props.setAttributes({ levels: next });
			}

			return el(wp.element.Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Overview settings', 'omega-design'), initialOpen: true },
						el(TextControl, {
							label: __('Overview heading', 'omega-design'),
							value: attributes.title || '',
							onChange: function (value) { props.setAttributes({ title: value }); }
						}),
						[2, 3, 4, 5, 6].map(function (level) {
							return el(CheckboxControl, {
								key: level,
								label: __('Heading level %d', 'omega-design').replace('%d', level),
								checked: levels.indexOf(level) !== -1,
								onChange: function (checked) { toggleLevel(level, checked); }
							});
						})
					)
				),
				el('div', blockProps,
					el('strong', null, attributes.title || __('Quick Overview', 'omega-design')),
					el('p', null, __('Links to the selected heading levels in the current post. The links appear on individual blog posts.', 'omega-design'))
				)
			);
		},
		save: function () { return null; }
	});
})(window.wp);
