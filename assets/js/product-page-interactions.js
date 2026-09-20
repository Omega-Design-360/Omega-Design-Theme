/**
 * Product Page Layouts - purely cosmetic micro-interactions layered on top
 * of WooCommerce's own add-to-cart-form (Interactivity API) and quantity
 * stepper. Neither listener calls preventDefault()/stopPropagation() or
 * touches WooCommerce's own state - they only toggle a class WooCommerce
 * doesn't use, so there's nothing here for WooCommerce's own handlers to
 * conflict with, and it degrades to "no animation" harmlessly if either
 * selector ever changes upstream.
 */
(function () {
	'use strict';

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	if (prefersReducedMotion()) {
		return;
	}

	function restartAnimation(el, className) {
		el.classList.remove(className);
		// Reading offsetWidth forces layout, so re-adding the class in the
		// same tick still restarts the CSS animation instead of no-op'ing.
		void el.offsetWidth;
		el.classList.add(className);
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.wc-block-components-quantity-selector__button');
		if (!button) {
			return;
		}

		var wrapper = button.closest('.wc-block-components-quantity-selector');
		var input = wrapper && wrapper.querySelector('.wc-block-components-quantity-selector__input');
		if (input) {
			restartAnimation(input, 'omega-qty-pop');
		}
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest ? event.target.closest('form.cart') : null;
		if (!form) {
			return;
		}

		var button = form.querySelector('.single_add_to_cart_button');
		if (!button) {
			return;
		}

		restartAnimation(button, 'omega-atc-success');
		window.setTimeout(function () {
			button.classList.remove('omega-atc-success');
		}, 900);
	});
})();
