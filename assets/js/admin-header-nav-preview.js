/**
 * Live-updates the Header Navigation preview strip on the theme's own
 * Settings page as the admin picks a classic menu and edits the appearance
 * overrides below it - see render_header_nav_form() in menus.php, which
 * renders the initial state and embeds each classic menu's top-level item
 * titles as JSON so this file never needs an AJAX round trip to redraw the
 * nav items.
 */
(function () {
	'use strict';

	function init() {
		var bar = document.getElementById('omega-header-preview-bar');
		var inner = document.getElementById('omega-header-preview-inner');
		var navEl = document.getElementById('omega-header-preview-nav');
		var dataEl = document.getElementById('omega-header-preview-menus-data');
		if (!bar || !inner || !navEl || !dataEl) {
			return;
		}

		var menusData = {};
		try {
			menusData = JSON.parse(dataEl.textContent || '{}');
		} catch (e) {
			menusData = {};
		}

		var DEFAULT_ITEMS = ['Home', 'About', 'Services', 'Contact'];

		// Mirrors classic_header::custom_style_css()'s own font_size_map/
		// height_map exactly, so the preview lands on the same steps as
		// what actually ships to the front end.
		var FONT_SIZE_MAP = { small: '0.875rem', medium: '1rem', large: '1.25rem', 'x-large': '1.75rem' };
		var HEIGHT_MAP = { compact: '0.55rem', regular: '1rem', spacious: '1.6rem' };

		var menuField = document.getElementById('omega_classic_menu_id');
		var bgField = document.getElementById('omega_header_bg_color');
		var textColorField = document.getElementById('omega_header_text_color');
		var fontFamilyField = document.getElementById('omega_header_font_family');
		var fontSizeField = document.getElementById('omega_header_font_size');
		var heightField = document.getElementById('omega_header_height');

		/** A field's current value, or '' when the field is missing or empty. */
		function fieldValue(field) {
			return field && field.value ? field.value : '';
		}

		/** map[field's value], or '' when the field is missing or the value isn't mapped. */
		function mappedValue(field, map) {
			return field && map[field.value] ? map[field.value] : '';
		}

		/** Binds handler to a field's event - fields a given form omits are skipped. */
		function listen(field, eventName, handler) {
			if (field) {
				field.addEventListener(eventName, handler);
			}
		}

		function renderNavItems() {
			var items = DEFAULT_ITEMS;
			if (menuField && menuField.value && menusData[menuField.value] && menusData[menuField.value].length) {
				items = menusData[menuField.value];
			}
			navEl.innerHTML = '';
			items.forEach(function (label) {
				var span = document.createElement('span');
				span.className = 'omega-header-preview__nav-item';
				span.textContent = label;
				navEl.appendChild(span);
			});
		}

		function updateColors() {
			bar.style.background = fieldValue(bgField);
			bar.style.color = fieldValue(textColorField);
			bar.style.fontFamily = fieldValue(fontFamilyField);
		}

		function updateFontSize() {
			navEl.style.fontSize = mappedValue(fontSizeField, FONT_SIZE_MAP);
		}

		function updateHeight() {
			var pad = mappedValue(heightField, HEIGHT_MAP);
			inner.style.paddingTop = pad;
			inner.style.paddingBottom = pad;
		}

		renderNavItems();

		listen(menuField, 'change', renderNavItems);
		listen(bgField, 'input', updateColors);
		listen(textColorField, 'input', updateColors);
		listen(fontFamilyField, 'input', updateColors);
		listen(fontSizeField, 'change', updateFontSize);
		listen(heightField, 'change', updateHeight);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
