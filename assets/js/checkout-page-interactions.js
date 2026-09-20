/**
 * Checkout page - purely cosmetic press feedback on the Place Order button,
 * relocated into the sidebar (see checkout-page.css). Never calls
 * preventDefault()/stopPropagation(), so it can't interfere with the real
 * order submission.
 */
(function () {
	'use strict';

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	if (prefersReducedMotion()) {
		return;
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.wc-block-components-checkout-place-order-button');
		if (!button || button.disabled) {
			return;
		}

		button.classList.remove('omega-checkout-press');
		void button.offsetWidth;
		button.classList.add('omega-checkout-press');
	});
})();
