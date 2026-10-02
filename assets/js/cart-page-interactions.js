/**
 * Cart page - purely cosmetic micro-interaction layered on top of
 * WooCommerce's own quantity stepper (Store API/Interactivity-driven).
 * Never calls preventDefault()/stopPropagation() and only toggles a class
 * WooCommerce doesn't use, so it can't interfere with the real quantity
 * update - worst case it silently does nothing if a selector ever changes
 * upstream.
 */
( function ( core ) {
	'use strict';

	if ( ! core || core.prefersReducedMotion() ) {
		return;
	}

	const QTY_BUTTON_SELECTOR = '.wc-block-components-quantity-selector__button';
	const QTY_WRAPPER_SELECTOR = '.wc-block-components-quantity-selector';
	const QTY_INPUT_SELECTOR = '.wc-block-components-quantity-selector__input';
	const TOTAL_VALUE_SELECTOR = '.wc-block-components-totals-footer-item .wc-block-components-totals-item__value';
	const TOTAL_MOUNT_POLL_MS = 500;
	const TOTAL_MOUNT_TIMEOUT_MS = 15000;

	class CartInteractions extends core.Component {
		constructor( root ) {
			super( root );
			this.totalsObserver = null;

			this.on( 'click', QTY_BUTTON_SELECTOR, ( event, button ) => this.popQuantity( button ) );

			// Optimistic fade the instant "Remove" is clicked - see the CSS
			// comment on .omega-cart-row-removing. Never calls
			// preventDefault(), so WooCommerce's own click handler (and the
			// actual removal) still runs.
			this.on( 'click', '.wc-block-cart-item__remove-link', ( event, link ) => this.fadeRow( link ) );

			this.waitForTotal();
		}

		popQuantity( button ) {
			const wrapper = button.closest( QTY_WRAPPER_SELECTOR );
			const input = wrapper && wrapper.querySelector( QTY_INPUT_SELECTOR );
			if ( input ) {
				core.restartAnimation( input, 'omega-cart-qty-pop' );
			}
		}

		fadeRow( removeLink ) {
			const row = removeLink.closest( '.wc-block-cart-items__row' );
			if ( row ) {
				row.classList.add( 'omega-cart-row-removing' );
			}
		}

		/**
		 * Pulses the grand total whenever its text actually changes
		 * (quantity update, coupon applied, item removed) - read-only
		 * observation, so it can't interfere with the real update either.
		 * Returns whether the total was found and is now being watched.
		 */
		watchTotal() {
			if ( this.totalsObserver ) {
				return true;
			}

			const totalValue = document.querySelector( TOTAL_VALUE_SELECTOR );
			if ( ! totalValue ) {
				return false;
			}

			let lastText = totalValue.textContent;
			this.totalsObserver = new MutationObserver( () => {
				if ( totalValue.textContent === lastText ) {
					return;
				}
				lastText = totalValue.textContent;
				core.restartAnimation( totalValue, 'omega-cart-total-pulse' );
			} );
			this.totalsObserver.observe( totalValue, { childList: true, characterData: true, subtree: true } );
			return true;
		}

		/**
		 * The totals block itself mounts asynchronously (Store API fetch),
		 * so keep checking until it exists rather than assuming it's there
		 * at load - giving up after a while either way.
		 */
		waitForTotal() {
			if ( this.watchTotal() ) {
				return;
			}

			const mountCheck = window.setInterval( () => {
				if ( this.watchTotal() ) {
					window.clearInterval( mountCheck );
				}
			}, TOTAL_MOUNT_POLL_MS );
			window.setTimeout( () => window.clearInterval( mountCheck ), TOTAL_MOUNT_TIMEOUT_MS );
		}
	}

	core.mount( CartInteractions );
} )( window.OmegaDesign );
