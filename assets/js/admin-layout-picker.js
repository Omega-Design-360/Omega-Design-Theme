/**
 * Product Page Layout picker (Omega Design > Settings) - the .is-selected
 * class PHP renders only reflects the saved theme_mod, so without this a
 * click updates the radio input but the card itself never highlights until
 * the form is saved and the page reloads.
 */
( function ( admin ) {
	'use strict';

	if ( ! admin ) {
		return;
	}

	const CARD_SELECTOR = '.omega-layout-card';
	const SELECTED_CLASS = 'is-selected';

	class LayoutPicker extends admin.Component {
		constructor( root ) {
			super( root );
			this.on( 'change', 'input[type="radio"]', ( event, input ) => this.select( input.closest( CARD_SELECTOR ) ) );
		}

		select( selectedCard ) {
			this.findAll( CARD_SELECTOR ).forEach( ( card ) => {
				card.classList.toggle( SELECTED_CLASS, card === selectedCard );
			} );
		}
	}

	admin.mountAll( '.omega-layout-picker', LayoutPicker );
} )( window.OmegaDesignAdmin );
