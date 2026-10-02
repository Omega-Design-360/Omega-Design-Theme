/**
 * Glass Boutique / Glass Showcase shop layouts (templates/shop-glass*.html):
 * grid/list view toggle, wishlist hearts and collapsible filter groups.
 *
 * WooCommerce re-renders the product grid (and the filters inside it) in
 * place whenever a filter, sort or page changes, so everything here uses
 * event delegation plus a MutationObserver that re-applies state to fresh
 * markup - never listeners bound to one specific element.
 *
 * The wishlist is stored in this browser only (localStorage): the theme has
 * no server-side wishlist, so a heart marks a product for the visitor on
 * this device.
 */
( function () {
	var root = document.querySelector( '.omega-shop-layout--glass' );
	if ( ! root ) {
		return;
	}

	function read( key, fallback ) {
		try {
			var value = window.localStorage.getItem( key );
			return null === value ? fallback : JSON.parse( value );
		} catch ( e ) {
			return fallback;
		}
	}

	function write( key, value ) {
		try {
			window.localStorage.setItem( key, JSON.stringify( value ) );
		} catch ( e ) {}
	}

	var view = read( 'omegaShopView', 'grid' );
	var wishlist = read( 'omegaWishlist', [] );
	var collapsed = read( 'omegaFilterCollapsed', {} );

	/** A toggle button's active look plus its aria-pressed state. */
	function setPressed( btn, isPressed ) {
		btn.classList.toggle( 'is-active', isPressed );
		btn.setAttribute( 'aria-pressed', isPressed ? 'true' : 'false' );
	}

	function applyView() {
		root.classList.toggle( 'is-list-view', 'list' === view );
		root.querySelectorAll( '.omega-view-toggle__btn' ).forEach( function ( btn ) {
			setPressed( btn, btn.getAttribute( 'data-view' ) === view );
		} );
	}

	function applyWishlist() {
		root.querySelectorAll( '.omega-glass-wish' ).forEach( function ( btn ) {
			setPressed( btn, wishlist.indexOf( btn.getAttribute( 'data-product' ) ) !== -1 );
		} );
	}

	function groupKey( heading ) {
		return heading.textContent.trim().toLowerCase();
	}

	function applyCollapsed() {
		root.querySelectorAll( '.omega-glass-filters h3.wp-block-heading' ).forEach( function ( heading ) {
			var group = heading.parentElement;
			var isCollapsed = !! collapsed[ groupKey( heading ) ];
			group.classList.add( 'omega-filter-group' );
			group.classList.toggle( 'is-collapsed', isCollapsed );
			if ( ! heading.hasAttribute( 'tabindex' ) ) {
				heading.setAttribute( 'tabindex', '0' );
				heading.setAttribute( 'role', 'button' );
			}
			heading.setAttribute( 'aria-expanded', isCollapsed ? 'false' : 'true' );
		} );
	}

	function applyAll() {
		applyView();
		applyWishlist();
		applyCollapsed();
	}

	function toggleGroup( heading ) {
		var key = groupKey( heading );
		collapsed[ key ] = ! collapsed[ key ];
		write( 'omegaFilterCollapsed', collapsed );
		applyCollapsed();
	}

	root.addEventListener( 'click', function ( event ) {
		var viewBtn = event.target.closest( '.omega-view-toggle__btn' );
		if ( viewBtn ) {
			view = viewBtn.getAttribute( 'data-view' );
			write( 'omegaShopView', view );
			applyView();
			return;
		}

		var wish = event.target.closest( '.omega-glass-wish' );
		if ( wish ) {
			event.preventDefault();
			var id = wish.getAttribute( 'data-product' );
			var index = wishlist.indexOf( id );
			if ( -1 === index ) {
				wishlist.push( id );
				wish.classList.add( 'is-popping' );
				setTimeout( function () {
					wish.classList.remove( 'is-popping' );
				}, 400 );
			} else {
				wishlist.splice( index, 1 );
			}
			write( 'omegaWishlist', wishlist );
			applyWishlist();
			return;
		}

		var heading = event.target.closest( '.omega-glass-filters h3.wp-block-heading' );
		if ( heading ) {
			toggleGroup( heading );
		}
	} );

	root.addEventListener( 'keydown', function ( event ) {
		var heading = event.target.closest && event.target.closest( '.omega-glass-filters h3.wp-block-heading' );
		if ( heading && ( 'Enter' === event.key || ' ' === event.key ) ) {
			event.preventDefault();
			toggleGroup( heading );
		}
	} );

	var scheduled = false;
	new MutationObserver( function () {
		if ( scheduled ) {
			return;
		}
		scheduled = true;
		window.requestAnimationFrame( function () {
			scheduled = false;
			applyAll();
		} );
	} ).observe( root, { childList: true, subtree: true } );

	applyAll();
} )();
