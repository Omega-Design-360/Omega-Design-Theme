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
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const STORAGE_KEYS = {
		view: 'omegaShopView',
		wishlist: 'omegaWishlist',
		collapsed: 'omegaFilterCollapsed',
	};
	const VIEW_BUTTON_SELECTOR = '.omega-view-toggle__btn';
	const WISH_SELECTOR = '.omega-glass-wish';
	const FILTER_HEADING_SELECTOR = '.omega-glass-filters h3.wp-block-heading';
	const WISH_POP_CLASS = 'is-popping';
	const WISH_POP_MS = 400;

	/** A toggle button's active look plus its aria-pressed state. */
	function setPressed( button, isPressed ) {
		core.setToggleState( button, isPressed, 'aria-pressed' );
	}

	class GlassShop extends core.Component {
		constructor( root ) {
			super( root );

			this.view = core.storage.get( STORAGE_KEYS.view, 'grid' );
			this.wishlist = core.storage.get( STORAGE_KEYS.wishlist, [] );
			this.collapsed = core.storage.get( STORAGE_KEYS.collapsed, {} );

			this.on( 'click', VIEW_BUTTON_SELECTOR, ( event, button ) => this.setView( button.getAttribute( 'data-view' ) ) );
			this.on( 'click', WISH_SELECTOR, ( event, button ) => {
				event.preventDefault();
				this.toggleWish( button );
			} );
			this.on( 'click', FILTER_HEADING_SELECTOR, ( event, heading ) => this.toggleGroup( heading ) );
			this.on( 'keydown', FILTER_HEADING_SELECTOR, ( event, heading ) => {
				if ( core.isActivationKey( event ) ) {
					event.preventDefault();
					this.toggleGroup( heading );
				}
			} );

			core.observeMutations( root, () => this.applyAll() );
			this.applyAll();
		}

		static groupKey( heading ) {
			return heading.textContent.trim().toLowerCase();
		}

		applyAll() {
			this.applyView();
			this.applyWishlist();
			this.applyCollapsed();
		}

		applyView() {
			this.root.classList.toggle( 'is-list-view', 'list' === this.view );
			this.findAll( VIEW_BUTTON_SELECTOR ).forEach( ( button ) => {
				setPressed( button, button.getAttribute( 'data-view' ) === this.view );
			} );
		}

		applyWishlist() {
			this.findAll( WISH_SELECTOR ).forEach( ( button ) => {
				setPressed( button, this.isWished( button.getAttribute( 'data-product' ) ) );
			} );
		}

		applyCollapsed() {
			this.findAll( FILTER_HEADING_SELECTOR ).forEach( ( heading ) => {
				const group = heading.parentElement;
				const isCollapsed = !! this.collapsed[ GlassShop.groupKey( heading ) ];

				group.classList.add( 'omega-filter-group' );
				group.classList.toggle( 'is-collapsed', isCollapsed );
				if ( ! heading.hasAttribute( 'tabindex' ) ) {
					heading.setAttribute( 'tabindex', '0' );
					heading.setAttribute( 'role', 'button' );
				}
				heading.setAttribute( 'aria-expanded', isCollapsed ? 'false' : 'true' );
			} );
		}

		setView( view ) {
			this.view = view;
			core.storage.set( STORAGE_KEYS.view, view );
			this.applyView();
		}

		isWished( productId ) {
			return -1 !== this.wishlist.indexOf( productId );
		}

		toggleWish( button ) {
			const productId = button.getAttribute( 'data-product' );

			if ( this.isWished( productId ) ) {
				this.wishlist.splice( this.wishlist.indexOf( productId ), 1 );
			} else {
				this.wishlist.push( productId );
				core.flashClass( button, WISH_POP_CLASS, WISH_POP_MS );
			}

			core.storage.set( STORAGE_KEYS.wishlist, this.wishlist );
			this.applyWishlist();
		}

		toggleGroup( heading ) {
			const key = GlassShop.groupKey( heading );
			this.collapsed[ key ] = ! this.collapsed[ key ];
			core.storage.set( STORAGE_KEYS.collapsed, this.collapsed );
			this.applyCollapsed();
		}
	}

	core.mountAll( '.omega-shop-layout--glass', GlassShop );
} )( window.OmegaDesign );
