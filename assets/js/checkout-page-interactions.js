/**
 * Checkout page - purely cosmetic press feedback on the Place Order button,
 * relocated into the sidebar (see checkout-page.css). Never calls
 * preventDefault()/stopPropagation(), so it can't interfere with the real
 * order submission.
 */
( function ( core ) {
	'use strict';

	if ( ! core || core.prefersReducedMotion() ) {
		return;
	}

	const PLACE_ORDER_SELECTOR = '.wc-block-components-checkout-place-order-button';
	const PRESS_CLASS = 'omega-checkout-press';

	class CheckoutInteractions extends core.Component {
		constructor( root ) {
			super( root );
			this.on( 'click', PLACE_ORDER_SELECTOR, ( event, button ) => this.press( button ) );
		}

		press( button ) {
			if ( ! button.disabled ) {
				core.restartAnimation( button, PRESS_CLASS );
			}
		}
	}

	core.mount( CheckoutInteractions );
} )( window.OmegaDesign );
