/* ============================================================
   Omega Design — Scroll reveal (fade + zoom, staggered)
   Reveals the blog page / archive cards (.omega-post-cards
   .wp-block-post) as they enter the viewport. Styles in
   assets/css/blog-cards.css.
   ============================================================ */
( function ( core ) {
	'use strict';

	// Reduced motion or no IntersectionObserver: cards simply stay visible.
	if ( ! core || ! core.canRevealOnScroll() ) {
		return;
	}

	const CARD_SELECTOR = '.wp-block-post';
	const STAGGER_MS = 100;
	const OBSERVER_OPTIONS = {
		threshold: 0.15,
		rootMargin: '0px 0px -40px 0px', // fire slightly before fully in view
	};

	class BlogCardsReveal extends core.Component {
		constructor( root ) {
			super( root );

			const cards = this.findAll( CARD_SELECTOR );
			if ( ! cards.length ) {
				return;
			}

			// Only now hide the cards for the reveal (see blog-cards.css).
			root.classList.add( 'is-reveal-ready' );

			// Stagger cards that enter in the same batch.
			core.revealOnScroll( cards, ( card, batchIndex ) => {
				window.setTimeout( () => card.classList.add( 'is-revealed' ), batchIndex * STAGGER_MS );
			}, OBSERVER_OPTIONS );
		}
	}

	core.mountAll( '.omega-post-cards', BlogCardsReveal );
} )( window.OmegaDesign );
