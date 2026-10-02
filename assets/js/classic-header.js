/**
 * Omega Design - Classic Header mobile toggle.
 * A single delegated click listener handles the main hamburger, each item's
 * submenu/Mega-Menu arrow, and outside-click closing, in that explicit
 * priority order - rather than several separate listeners each trying to
 * stopPropagation() past one another, which is exactly the kind of setup
 * where one case (the arrow) can end up also triggering another (the main
 * nav closing) depending on event timing.
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const HEADER_SELECTOR = '.omega-classic-header';
	const NAV_SELECTOR = '.omega-classic-header__nav';
	const TOGGLE_SELECTOR = '.omega-classic-header__toggle';
	const SUBMENU_TOGGLE_SELECTOR = '.omega-classic-header__submenu-toggle';
	const OPEN_CLASS = 'is-open';
	const EXPANDED_CLASS = 'is-expanded';
	const HIDDEN_CLASS = 'is-header-hidden';
	const SCROLLED_CLASS = 'is-scrolled';
	const SCROLLED_THRESHOLD_PX = 8;

	/** A toggle button's pressed look plus its aria-expanded state. */
	function setExpanded( button, isExpanded ) {
		core.setToggleState( button, isExpanded, 'aria-expanded' );
	}

	/** Every classic header's mobile menu and submenu arrows - one instance per page. */
	class ClassicHeaderMenu {
		constructor() {
			document.addEventListener( 'click', ( event ) => this.onClick( event ) );
			core.onEscape( () => this.closeMainNav() );
		}

		// The mobile menu is a full-screen overlay (classic-header.css),
		// locked to the viewport rather than scrolling away with the page -
		// reflects that in <html>'s own scroll state too, so the page behind
		// it can't be dragged around while it's open.
		static syncBodyScrollLock() {
			const anyOpen = !! document.querySelector( NAV_SELECTOR + '.' + OPEN_CLASS );
			document.documentElement.classList.toggle( 'omega-nav-open', anyOpen );
		}

		closeMainNav( exceptHeader ) {
			core.all( HEADER_SELECTOR ).forEach( ( header ) => {
				if ( header === exceptHeader ) {
					return;
				}
				const nav = header.querySelector( NAV_SELECTOR + '.' + OPEN_CLASS );
				const button = header.querySelector( TOGGLE_SELECTOR + '.is-active' );
				if ( nav ) {
					nav.classList.remove( OPEN_CLASS );
				}
				if ( button ) {
					setExpanded( button, false );
				}
			} );
			ClassicHeaderMenu.syncBodyScrollLock();
		}

		onClick( event ) {
			// 1) A per-item arrow (megamenu.php's inject_classic_submenu_toggle()).
			const submenuToggle = event.target.closest( SUBMENU_TOGGLE_SELECTOR );
			if ( submenuToggle ) {
				event.preventDefault();
				this.toggleSubmenu( submenuToggle );
				return;
			}

			// 2) The main hamburger.
			const mainToggle = event.target.closest( TOGGLE_SELECTOR );
			if ( mainToggle ) {
				this.toggleMainNav( mainToggle );
				return;
			}

			// 3) Anything else outside every header: close the main nav.
			if ( ! event.target.closest( HEADER_SELECTOR ) ) {
				this.closeMainNav();
			}
		}

		/**
		 * Toggles the arrow's own sub-menu/Mega-Menu panel (its next
		 * sibling) only - never the main nav, and never navigates anywhere.
		 */
		toggleSubmenu( submenuToggle ) {
			const target = submenuToggle.nextElementSibling;
			if ( ! target ) {
				return;
			}

			const expanding = ! target.classList.contains( EXPANDED_CLASS );
			target.classList.toggle( EXPANDED_CLASS, expanding );
			setExpanded( submenuToggle, expanding );

			// Explicit, rather than relying on the browser's own "scroll the
			// newly-focused button into view" behavior (inconsistent across
			// browsers, and was the actual source of the instant,
			// no-animation "jump to the top" - scroll-behavior:smooth on the
			// nav (classic-header.css) is what makes this glide instead of
			// snap. block: 'nearest' only scrolls if the row isn't already
			// fully visible, never re-centers something already on screen.
			const row = expanding && submenuToggle.closest( 'li' );
			if ( row && row.scrollIntoView ) {
				row.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
			}
		}

		toggleMainNav( mainToggle ) {
			const header = mainToggle.closest( HEADER_SELECTOR );
			const nav = header ? header.querySelector( NAV_SELECTOR ) : null;
			if ( ! nav ) {
				return;
			}

			const opening = ! nav.classList.contains( OPEN_CLASS );
			this.closeMainNav( opening ? header : null );
			nav.classList.toggle( OPEN_CLASS, opening );
			setExpanded( mainToggle, opening );
			ClassicHeaderMenu.syncBodyScrollLock();
		}
	}

	/**
	 * One header's measured height, exposed as a CSS custom property so the
	 * mobile full-screen menu (classic-header.css) can start exactly below
	 * it - varies per style (Minimal Bar vs Centered vs Boxed) and per site
	 * (logo size, tagline), so this has to be measured, never a fixed
	 * guessed number.
	 */
	class ClassicHeader extends core.Component {
		constructor( root ) {
			super( root );
			this.syncHeight = this.syncHeight.bind( this );
			this.syncHeight();
			window.addEventListener( 'resize', this.syncHeight );
		}

		syncHeight() {
			this.root.style.setProperty( '--omega-ch-height', this.root.offsetHeight + 'px' );
		}
	}

	/**
	 * Sticky header (opt-in, Settings > Header Navigation "Sticky header"):
	 * hides on scroll down, reveals on scroll up. Only present at all when
	 * $sticky was true at render time (each template file's own
	 * omega-classic-header--sticky class + the spacer right after
	 * </header> - see classic-header.css for why the spacer exists).
	 */
	class StickyHeader extends ClassicHeader {
		constructor( root ) {
			super( root );

			const spacer = root.nextElementSibling;
			this.spacer = spacer && spacer.classList.contains( 'omega-classic-header__spacer' ) ? spacer : null;
			this.lastY = window.scrollY;
			this.syncSpacerHeight();

			window.addEventListener( 'resize', () => this.syncSpacerHeight() );
			window.addEventListener( 'scroll', core.throttleToFrame( () => this.onScroll() ), { passive: true } );
		}

		syncSpacerHeight() {
			if ( this.spacer ) {
				this.spacer.style.height = this.root.offsetHeight + 'px';
			}
		}

		isMenuOpen() {
			return !! this.find( NAV_SELECTOR + '.' + OPEN_CLASS );
		}

		onScroll() {
			const header = this.root;
			const y = window.scrollY;

			// Never hide while at the very top (nothing scrolled past it yet
			// to justify hiding) or while this header's own mobile menu is
			// open (hiding it out from under an open menu would be jarring
			// and would strand the close button).
			if ( y <= header.offsetHeight || this.isMenuOpen() ) {
				header.classList.remove( HIDDEN_CLASS );
			} else if ( y > this.lastY ) {
				header.classList.add( HIDDEN_CLASS ); // scrolling down
			} else if ( y < this.lastY ) {
				header.classList.remove( HIDDEN_CLASS ); // scrolling up
			}

			// Independent of the show/hide state above - a small elevation
			// cue (classic-header.css's .is-scrolled) the moment page content
			// is actually sliding underneath it, gone again the instant it's
			// scrolled back to the top.
			header.classList.toggle( SCROLLED_CLASS, y > SCROLLED_THRESHOLD_PX );

			this.lastY = y;
		}
	}

	new ClassicHeaderMenu();

	core.ready( () => {
		core.all( HEADER_SELECTOR ).forEach( ( header ) => {
			const HeaderClass = header.classList.contains( 'omega-classic-header--sticky' ) ? StickyHeader : ClassicHeader;
			new HeaderClass( header );
		} );
	} );
} )( window.OmegaDesign );
