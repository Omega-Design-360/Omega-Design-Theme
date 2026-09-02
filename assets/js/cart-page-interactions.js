/**
 * Cart page - purely cosmetic micro-interaction layered on top of
 * WooCommerce's own quantity stepper (Store API/Interactivity-driven).
 * Never calls preventDefault()/stopPropagation() and only toggles a class
 * WooCommerce doesn't use, so it can't interfere with the real quantity
 * update - worst case it silently does nothing if a selector ever changes
 * upstream.
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
		var button = event.target.closest('.wc-block-components-quantity-selector__button');
		if (!button) {
			return;
		}

		var wrapper = button.closest('.wc-block-components-quantity-selector');
		var input = wrapper && wrapper.querySelector('.wc-block-components-quantity-selector__input');
		if (!input) {
			return;
		}

		input.classList.remove('omega-cart-qty-pop');
		void input.offsetWidth;
		input.classList.add('omega-cart-qty-pop');
	});

	// Optimistic fade the instant "Remove" is clicked - see the CSS comment
	// on .omega-cart-row-removing. Never calls preventDefault(), so
	// WooCommerce's own click handler (and the actual removal) still runs.
	document.addEventListener('click', function (event) {
		var removeLink = event.target.closest('.wc-block-cart-item__remove-link');
		if (!removeLink) {
			return;
		}

		var row = removeLink.closest('.wc-block-cart-items__row');
		if (row) {
			row.classList.add('omega-cart-row-removing');
		}
	});

	// Pulse the grand total whenever its text actually changes (quantity
	// update, coupon applied, item removed) - read-only observation, so it
	// can't interfere with the real update either.
	var totalsObserver = null;
	function watchTotal() {
		var totalValue = document.querySelector('.wc-block-components-totals-footer-item .wc-block-components-totals-item__value');
		if (!totalValue || totalsObserver) {
			return;
		}

		var lastText = totalValue.textContent;
		totalsObserver = new MutationObserver(function () {
			if (totalValue.textContent === lastText) {
				return;
			}
			lastText = totalValue.textContent;
			totalValue.classList.remove('omega-cart-total-pulse');
			void totalValue.offsetWidth;
			totalValue.classList.add('omega-cart-total-pulse');
		});
		totalsObserver.observe(totalValue, { childList: true, characterData: true, subtree: true });
	}

	watchTotal();
	// The totals block itself mounts asynchronously (Store API fetch), so
	// keep checking until it exists rather than assuming it's there at load.
	var mountCheck = window.setInterval(function () {
		watchTotal();
		if (totalsObserver) {
			window.clearInterval(mountCheck);
		}
	}, 500);
	window.setTimeout(function () {
		window.clearInterval(mountCheck);
	}, 15000);
})();
