/* ============================================================
   Omega Design — Scroll reveal (fade + zoom, staggered)
   Reveals the blog page / archive cards (.omega-post-cards
   .wp-block-post) as they enter the viewport. Styles in
   assets/css/blog-cards.css.
   ============================================================ */
( function () {
	const loops = document.querySelectorAll( '.omega-post-cards' );
	const cards = document.querySelectorAll( '.omega-post-cards .wp-block-post' );
	if ( ! cards.length ) {
		return;
	}

	// Reduced motion or no IntersectionObserver: show everything immediately.
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	// Only now hide the cards for the reveal (see blog-cards.css).
	loops.forEach( ( loop ) => loop.classList.add( 'is-reveal-ready' ) );

	const observer = new IntersectionObserver( ( entries, obs ) => {
		let batch = 0;
		entries.forEach( ( entry ) => {
			if ( ! entry.isIntersecting ) {
				return;
			}
			// Stagger cards that enter in the same batch.
			const delay = batch++ * 100;
			setTimeout( () => entry.target.classList.add( 'is-revealed' ), delay );
			obs.unobserve( entry.target ); // reveal once, then stop watching
		} );
	}, {
		threshold: 0.15,
		rootMargin: '0px 0px -40px 0px', // fire slightly before fully in view
	} );

	cards.forEach( ( card ) => observer.observe( card ) );
} )();
