/**
 * Block-Navigation Mega Menu panels: open on hover/click/Enter, close on
 * leaving, outside click or Escape.
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const TRIGGER_SELECTOR = '.omega-megamenu-trigger';
	const PANEL_SELECTOR = '.omega-megamenu-panel';
	const PANEL_CLASS_PREFIX = 'omega-panel--';
	const ACTIVE_CLASS = 'is-active';
	const CLOSE_DELAY_MS = 150;

	class MegaMenu extends core.Component {
		constructor( header ) {
			super( header );
			this.panels = this.findAll( PANEL_SELECTOR );
			this.closeTimer = null;

			this.findAll( TRIGGER_SELECTOR ).forEach( ( trigger ) => this.bindTrigger( trigger ) );
			this.panels.forEach( ( panel ) => this.bindHoverIntent( panel ) );

			document.addEventListener( 'click', ( event ) => {
				if ( ! header.contains( event.target ) ) {
					this.hideAll();
				}
			} );
			core.onEscape( () => this.hideAll() );
		}

		/** The trigger's "omega-panel--{slug}" class, naming the panel it opens. */
		static panelClassOf( trigger ) {
			return Array.prototype.find.call( trigger.classList, ( name ) => 0 === name.indexOf( PANEL_CLASS_PREFIX ) ) || null;
		}

		findPanel( panelClass ) {
			return this.find( PANEL_SELECTOR + '.' + panelClass );
		}

		hideAll() {
			this.panels.forEach( ( panel ) => panel.classList.remove( ACTIVE_CLASS ) );
		}

		showPanel( panelClass ) {
			this.hideAll();
			const panel = this.findPanel( panelClass );
			if ( panel ) {
				panel.classList.add( ACTIVE_CLASS );
			}
		}

		/** Opens the trigger's panel - or closes everything if it's already the open one. */
		togglePanel( panelClass ) {
			if ( ! panelClass ) {
				return;
			}
			const panel = this.findPanel( panelClass );
			if ( panel && panel.classList.contains( ACTIVE_CLASS ) ) {
				this.hideAll();
			} else {
				this.showPanel( panelClass );
			}
		}

		cancelHide() {
			clearTimeout( this.closeTimer );
		}

		scheduleHide() {
			this.closeTimer = setTimeout( () => {
				if ( ! this.find( PANEL_SELECTOR + ':hover' ) ) {
					this.hideAll();
				}
			}, CLOSE_DELAY_MS );
		}

		/** Keeps a panel/trigger open while hovered, closing shortly after leaving. */
		bindHoverIntent( el, onEnter ) {
			el.addEventListener( 'mouseenter', () => {
				this.cancelHide();
				if ( onEnter ) {
					onEnter();
				}
			} );
			el.addEventListener( 'mouseleave', () => this.scheduleHide() );
		}

		bindTrigger( trigger ) {
			// Classic headers (assets/css/classic-header.css) reveal Mega Menu
			// panels with hover/focus-within CSS instead, the same way their
			// plain sub-menus already work with no JS at all - so the
			// trigger's own link stays a normal, navigable link. Wiring this
			// same click-intercepting JS to them would permanently block that
			// link (preventDefault() below runs on every click,
			// unconditionally), since a classic top-level item generally has
			// a real destination page as well as a Mega Menu, unlike a block
			// Navigation trigger.
			if ( trigger.closest( '.omega-classic-header' ) ) {
				return;
			}

			const panelClass = MegaMenu.panelClassOf( trigger );

			this.bindHoverIntent( trigger, () => {
				if ( panelClass ) {
					this.showPanel( panelClass );
				}
			} );

			trigger.addEventListener( 'click', ( event ) => {
				if ( ! panelClass ) {
					return;
				}
				this.togglePanel( panelClass );
				event.preventDefault();
			} );

			// Keyboard: open on Enter/Space, close on Escape.
			trigger.addEventListener( 'keydown', ( event ) => {
				if ( core.isActivationKey( event ) ) {
					event.preventDefault();
					this.togglePanel( panelClass );
				}
			} );
		}
	}

	core.ready( () => {
		const header = document.querySelector( '.site-header' );
		if ( header ) {
			new MegaMenu( header );
		}
	} );
} )( window.OmegaDesign );
