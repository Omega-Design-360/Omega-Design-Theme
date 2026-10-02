/**
 * Product Page Layouts - purely cosmetic micro-interactions layered on top
 * of WooCommerce's own add-to-cart-form (Interactivity API) and quantity
 * stepper. Neither listener calls preventDefault()/stopPropagation() or
 * touches WooCommerce's own state - they only toggle a class WooCommerce
 * doesn't use, so there's nothing here for WooCommerce's own handlers to
 * conflict with, and it degrades to "no animation" harmlessly if either
 * selector ever changes upstream.
 */
( function ( core ) {
	'use strict';

	if ( ! core || core.prefersReducedMotion() ) {
		return;
	}

	const QTY_BUTTON_SELECTOR = '.wc-block-components-quantity-selector__button';
	const QTY_WRAPPER_SELECTOR = '.wc-block-components-quantity-selector';
	const QTY_INPUT_SELECTOR = '.wc-block-components-quantity-selector__input';
	const QTY_POP_CLASS = 'omega-qty-pop';
	const ATC_SUCCESS_CLASS = 'omega-atc-success';
	const ATC_SUCCESS_DURATION_MS = 900;

	class ProductPageInteractions extends core.Component {
		constructor( root ) {
			super( root );
			this.on( 'click', QTY_BUTTON_SELECTOR, ( event, button ) => this.popQuantity( button ) );
			this.on( 'submit', 'form.cart', ( event, form ) => this.celebrateAddToCart( form ) );
		}

		popQuantity( button ) {
			const wrapper = button.closest( QTY_WRAPPER_SELECTOR );
			const input = wrapper && wrapper.querySelector( QTY_INPUT_SELECTOR );
			if ( input ) {
				core.restartAnimation( input, QTY_POP_CLASS );
			}
		}

		celebrateAddToCart( form ) {
			const button = form.querySelector( '.single_add_to_cart_button' );
			if ( button ) {
				core.flashClass( button, ATC_SUCCESS_CLASS, ATC_SUCCESS_DURATION_MS );
			}
		}
	}

	core.mount( ProductPageInteractions );
} )( window.OmegaDesign );
