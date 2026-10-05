/**
 * Real Estate landing page (pattern/landing-real-estate.php) - header
 * behaviour. The header sticks to the top (assets/css/landing-real-estate.css),
 * turns into a frosted bar once the page has scrolled, slides away while
 * the visitor scrolls down and comes straight back when they scroll up.
 * Loaded only on pages using the pattern - see enqueue_landing_page_assets()
 * in includes/core/hooks.php.
 */
( function () {
	'use strict';

	const HEADER_SELECTOR = '.omega-re-header';
	const SCROLLED_CLASS = 'is-scrolled';
	const HIDDEN_CLASS = 'is-hidden';
	// Core's Navigation block puts this on <html> while the phone menu is open.
	const MENU_OPEN_CLASS = 'has-modal-open';
	// Pixels scrolled before the bar turns frosted.
	const SCROLLED_AFTER = 24;
	// Pixels of travel before hide/show flips, so small jitters don't toggle it.
	const DIRECTION_THRESHOLD = 8;

	class HideOnScrollHeader {
		constructor( header ) {
			this.header = header;
			this.lastY = window.scrollY;
			this.ticking = false;

			this.update();
			window.addEventListener( 'scroll', () => this.requestUpdate(), { passive: true } );
			// Keyboard users tabbing into a hidden header bring it back.
			header.addEventListener( 'focusin', () => this.show() );
		}

		requestUpdate() {
			if ( this.ticking ) {
				return;
			}
			this.ticking = true;
			window.requestAnimationFrame( () => {
				this.ticking = false;
				this.update();
			} );
		}

		show() {
			this.header.classList.remove( HIDDEN_CLASS );
		}

		update() {
			const y = Math.max( 0, window.scrollY );
			this.header.classList.toggle( SCROLLED_CLASS, y > SCROLLED_AFTER );

			// Always visible near the top and while the phone menu is open.
			const menuOpen = document.documentElement.classList.contains( MENU_OPEN_CLASS );
			if ( menuOpen || y <= this.header.offsetHeight ) {
				this.show();
				this.lastY = y;
				return;
			}

			const delta = y - this.lastY;
			if ( Math.abs( delta ) < DIRECTION_THRESHOLD ) {
				return;
			}
			this.header.classList.toggle( HIDDEN_CLASS, delta > 0 );
			this.lastY = y;
		}
	}

	document.querySelectorAll( HEADER_SELECTOR ).forEach( ( header ) => new HideOnScrollHeader( header ) );
} )();
