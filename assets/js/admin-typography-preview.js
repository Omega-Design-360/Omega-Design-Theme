/**
 * Re-fonts a Typography dropdown's single preview card live as the admin
 * changes their selection - see typography::render_font_picker() (used by
 * both the Settings page and the Customizer's Heading/Body Font controls),
 * which renders each <option> with its own data-font-family and marks the
 * <select> with data-preview pointing at the id of the card to update.
 *
 * Delegated on `document` rather than bound directly to each .omega-font-
 * select at DOMContentLoaded, since the Customizer only injects its
 * controls' markup into the DOM well after DOMContentLoaded already fired
 * (Backbone-rendered on section init) - a direct binding at that point
 * would silently find no elements there. Delegation only needs `document`
 * itself to exist, so it keeps working no matter when the select shows up.
 */
(function () {
	'use strict';

	document.addEventListener('change', function (event) {
		var select = event.target;
		if (!select || !select.classList || !select.classList.contains('omega-font-select')) {
			return;
		}

		var previewId = select.getAttribute('data-preview');
		var preview = previewId ? document.getElementById(previewId) : null;
		if (!preview) {
			return;
		}

		var option = select.options[select.selectedIndex];
		var fontFamily = option ? option.getAttribute('data-font-family') : '';
		preview.style.fontFamily = fontFamily || '';
	});
})();
