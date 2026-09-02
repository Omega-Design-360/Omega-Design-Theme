/**
 * Product Page Layout picker (Omega Design > Settings) - the .is-selected
 * class PHP renders only reflects the saved theme_mod, so without this a
 * click updates the radio input but the card itself never highlights until
 * the form is saved and the page reloads.
 */
(function () {
	'use strict';

	document.querySelectorAll('.omega-layout-picker').forEach(function (picker) {
		picker.addEventListener('change', function (event) {
			var input = event.target;
			if (!input.matches('input[type="radio"]')) {
				return;
			}

			picker.querySelectorAll('.omega-layout-card').forEach(function (card) {
				card.classList.remove('is-selected');
			});

			var card = input.closest('.omega-layout-card');
			if (card) {
				card.classList.add('is-selected');
			}
		});
	});
})();
