/**
 * Shared front-end core for the theme's scripts, exposed as
 * window.OmegaDesign and loaded first via each script's dependencies (see
 * the assets trait's core_script()). Every feature script is a small class
 * extending OmegaDesign.Component and is started with mountAll()/mount(),
 * reusing the helpers below instead of carrying its own copy of each.
 */
( function ( window, document ) {
	'use strict';

	const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';
	const IS_ACTIVE_CLASS = 'is-active';

	/** Runs callback once the DOM is parsed (immediately if it already is). */
	function ready( callback ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', callback, { once: true } );
		} else {
			callback();
		}
	}

	function prefersReducedMotion() {
		return !! ( window.matchMedia && window.matchMedia( REDUCED_MOTION_QUERY ).matches );
	}

	/** True when motion is allowed and IntersectionObserver is available. */
	function canRevealOnScroll() {
		return ! prefersReducedMotion() && 'IntersectionObserver' in window;
	}

	/** Every element matching selector inside root (document by default), as an array. */
	function all( selector, root ) {
		return Array.prototype.slice.call( ( root || document ).querySelectorAll( selector ) );
	}

	/**
	 * Re-adds className so its CSS animation restarts even when it's already
	 * present - reading offsetWidth forces layout, so removing and re-adding
	 * in the same tick still counts as a fresh start instead of a no-op.
	 */
	function restartAnimation( el, className ) {
		el.classList.remove( className );
		void el.offsetWidth;
		el.classList.add( className );
	}

	/** restartAnimation(), then removes the class again after duration ms. */
	function flashClass( el, className, duration ) {
		restartAnimation( el, className );
		window.setTimeout( function () {
			el.classList.remove( className );
		}, duration );
	}

	/**
	 * A toggle button's active look plus its ARIA state attribute
	 * ('aria-expanded', 'aria-pressed', 'aria-selected', ...).
	 */
	function setToggleState( el, isOn, ariaAttribute ) {
		el.classList.toggle( IS_ACTIVE_CLASS, isOn );
		el.setAttribute( ariaAttribute, isOn ? 'true' : 'false' );
	}

	/**
	 * One listener on root for every current and future element matching
	 * selector - survives markup being re-rendered in place. handler gets
	 * ( event, matchedElement ).
	 */
	function delegate( root, eventName, selector, handler, options ) {
		root.addEventListener( eventName, function ( event ) {
			const match = event.target && event.target.closest ? event.target.closest( selector ) : null;
			if ( match && root.contains( match ) ) {
				handler( event, match );
			}
		}, options );
	}

	function isActivationKey( event ) {
		return 'Enter' === event.key || ' ' === event.key;
	}

	/** Runs handler whenever Escape is pressed anywhere on the page. */
	function onEscape( handler ) {
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				handler( event );
			}
		} );
	}

	/** Wraps fn so repeated calls within one frame run it only once, on the next frame. */
	function throttleToFrame( fn ) {
		let scheduled = false;
		return function () {
			if ( scheduled ) {
				return;
			}
			scheduled = true;
			window.requestAnimationFrame( function () {
				scheduled = false;
				fn();
			} );
		};
	}

	/** Calls fn on any DOM change under target, at most once per frame. */
	function observeMutations( target, fn, options ) {
		const observer = new MutationObserver( throttleToFrame( fn ) );
		observer.observe( target, options || { childList: true, subtree: true } );
		return observer;
	}

	/**
	 * Calls onReveal( el, batchIndex ) once for each element as it first
	 * scrolls into view, then stops watching it. batchIndex counts elements
	 * revealed by the same observer callback, for staggering.
	 */
	function revealOnScroll( elements, onReveal, observerOptions ) {
		const observer = new IntersectionObserver( function ( entries ) {
			let batchIndex = 0;
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}
				onReveal( entry.target, batchIndex++ );
				observer.unobserve( entry.target );
			} );
		}, observerOptions );

		elements.forEach( function ( el ) {
			observer.observe( el );
		} );
		return observer;
	}

	/**
	 * JSON-backed localStorage that never throws (private windows and
	 * blocked storage just fall back to the default / skip the write).
	 */
	const storage = {
		get( key, fallback ) {
			try {
				const value = window.localStorage.getItem( key );
				return null === value ? fallback : JSON.parse( value );
			} catch ( e ) {
				return fallback;
			}
		},

		set( key, value ) {
			try {
				window.localStorage.setItem( key, JSON.stringify( value ) );
			} catch ( e ) {}
		},
	};

	/** Base class for every front-end feature: one instance per root element. */
	class Component {
		constructor( root ) {
			this.root = root;
		}

		/** First match inside this component's root. */
		find( selector ) {
			return this.root.querySelector( selector );
		}

		/** Every match inside this component's root, as an array. */
		findAll( selector ) {
			return all( selector, this.root );
		}

		/** delegate() scoped to this component's root. */
		on( eventName, selector, handler, options ) {
			delegate( this.root, eventName, selector, handler, options );
		}
	}

	/** Once the DOM is ready, creates one ComponentClass instance per element matching selector. */
	function mountAll( selector, ComponentClass ) {
		ready( function () {
			all( selector ).forEach( function ( el ) {
				new ComponentClass( el );
			} );
		} );
	}

	/** Once the DOM is ready, creates a single ComponentClass instance on root (document by default). */
	function mount( ComponentClass, root ) {
		ready( function () {
			new ComponentClass( root || document );
		} );
	}

	window.OmegaDesign = {
		Component,
		all,
		canRevealOnScroll,
		delegate,
		flashClass,
		isActivationKey,
		mount,
		mountAll,
		observeMutations,
		onEscape,
		prefersReducedMotion,
		ready,
		restartAnimation,
		revealOnScroll,
		setToggleState,
		storage,
		throttleToFrame,
	};
} )( window, document );
