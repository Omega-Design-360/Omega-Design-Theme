/**
 * Front-end playback for the site-wide "Scroll Animation" block control -
 * every element carrying an .omega-animate class (stamped on by either the
 * editor's save() output or, for dynamic blocks, includes/core/
 * scroll_animations.php's render_block filter) starts hidden/offset via
 * assets/css/scroll-animations.css and reveals once it actually scrolls
 * into view.
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const ANIMATE_SELECTOR = '.omega-animate';
	const ANIMATED_IN_CLASS = 'omega-animate--in';
	const DEFAULT_DURATION_MS = '600';
	const DEFAULT_DELAY_MS = '0';
	const OBSERVER_OPTIONS = { threshold: 0.15, rootMargin: '0px 0px -8% 0px' };

	class ScrollAnimations {
		constructor( elements ) {
			this.revealImmediately = ! core.canRevealOnScroll();

			const toObserve = elements.filter( ( el ) => this.prepare( el ) );
			if ( toObserve.length ) {
				core.revealOnScroll( toObserve, ScrollAnimations.reveal, OBSERVER_OPTIONS );
			}
		}

		static reveal( el ) {
			el.classList.add( ANIMATED_IN_CLASS );
		}

		/**
		 * The slider clips its slides with overflow:hidden on
		 * .omega-slider__track so only the active slide shows - per the
		 * IntersectionObserver spec that clipping ancestor makes any slide's
		 * *content* (even the currently visible slide's) always compute as
		 * non-intersecting, so it would otherwise sit invisible forever.
		 * Only content nested INSIDE a slider is affected - starting the
		 * search at the parent (not el itself) means the slider block's own
		 * wrapper can still play a normal scroll-reveal for the section as a
		 * whole.
		 */
		static isInsideSlider( el ) {
			return !! ( el.parentElement && el.parentElement.closest( '.omega-slider' ) );
		}

		/**
		 * Reveals el right away when it can't (or shouldn't) wait for
		 * scrolling, otherwise applies its timing and returns true so it
		 * gets observed.
		 */
		prepare( el ) {
			if ( this.revealImmediately || ScrollAnimations.isInsideSlider( el ) ) {
				ScrollAnimations.reveal( el );
				return false;
			}

			el.style.setProperty( '--omega-animate-duration', ( el.getAttribute( 'data-omega-animate-duration' ) || DEFAULT_DURATION_MS ) + 'ms' );
			el.style.setProperty( '--omega-animate-delay', ( el.getAttribute( 'data-omega-animate-delay' ) || DEFAULT_DELAY_MS ) + 'ms' );
			return true;
		}
	}

	core.ready( () => {
		const elements = core.all( ANIMATE_SELECTOR );
		if ( elements.length ) {
			new ScrollAnimations( elements );
		}
	} );
} )( window.OmegaDesign );
