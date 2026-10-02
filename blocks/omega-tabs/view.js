/**
 * Front-end behaviour for the "Omega Tabs" block (omega-design/tabs).
 * The tab bar buttons are rendered server-side (includes/core/blocks.php);
 * this just wires up click-to-switch and keeps everything keyboard-
 * accessible.
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const ACTIVE_CLASS = 'is-active';
	const ARROW_STEPS = { ArrowRight: 1, ArrowLeft: -1 };

	class Tabs extends core.Component {
		constructor( root ) {
			super( root );
			this.buttons = this.findAll( ':scope > .omega-tabs__list > .omega-tabs__tab' );
			this.panels = this.findAll( ':scope > .omega-tabs__panels > .omega-tabs__panel' );

			if ( ! this.buttons.length || ! this.panels.length ) {
				return;
			}

			this.buttons.forEach( ( button, index ) => {
				button.addEventListener( 'click', () => this.activate( index ) );
				button.addEventListener( 'keydown', ( event ) => this.onKeydown( event, index ) );
			} );

			this.activate( 0 );
		}

		activate( activeIndex ) {
			this.buttons.forEach( ( button, index ) => {
				const isActive = index === activeIndex;
				core.setToggleState( button, isActive, 'aria-selected' );
				button.setAttribute( 'tabindex', isActive ? '0' : '-1' );
			} );
			this.panels.forEach( ( panel, index ) => {
				panel.classList.toggle( ACTIVE_CLASS, index === activeIndex );
			} );
		}

		/** Left/Right arrows move focus (and selection) to the neighbouring tab, wrapping around. */
		onKeydown( event, index ) {
			const step = ARROW_STEPS[ event.key ];
			if ( ! step ) {
				return;
			}

			const count = this.buttons.length;
			const next = ( index + step + count ) % count;
			event.preventDefault();
			this.buttons[ next ].focus();
			this.activate( next );
		}
	}

	core.mountAll( '.omega-tabs', Tabs );
} )( window.OmegaDesign );
