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
			bar.style.background = bgField && bgField.value ? bgField.value : '';
			bar.style.color = textColorField && textColorField.value ? textColorField.value : '';
			bar.style.fontFamily = fontFamilyField && fontFamilyField.value ? fontFamilyField.value : '';
		}

		function updateFontSize() {
			navEl.style.fontSize = fontSizeField && FONT_SIZE_MAP[fontSizeField.value] ? FONT_SIZE_MAP[fontSizeField.value] : '';
		}

		function updateHeight() {
			var pad = heightField && HEIGHT_MAP[heightField.value] ? HEIGHT_MAP[heightField.value] : '';
			inner.style.paddingTop = pad;
			inner.style.paddingBottom = pad;
		}

		renderNavItems();

		if (menuField) {
			menuField.addEventListener('change', renderNavItems);
		}
		if (bgField) {
			bgField.addEventListener('input', updateColors);
		}
		if (textColorField) {
			textColorField.addEventListener('input', updateColors);
		}
		if (fontFamilyField) {
			fontFamilyField.addEventListener('input', updateColors);
		}
		if (fontSizeField) {
			fontSizeField.addEventListener('change', updateFontSize);
		}
		if (heightField) {
			heightField.addEventListener('change', updateHeight);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
