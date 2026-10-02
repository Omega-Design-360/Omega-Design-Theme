/**
 * Settings screen tab switcher (Omega Design > Settings). Driven entirely
 * by the URL hash rather than manual click handling, so the same logic
 * covers first load, clicking a tab, browser back/forward, and landing
 * back on the right panel after a form save redirects to #<tab> (see
 * menus.php's $tab_url helper) - one code path, no state to keep in sync.
 */
( function ( admin ) {
	'use strict';

	if ( ! admin ) {
		return;
	}

	const ACTIVE_CLASS = 'is-active';

	class SettingsTabs {
		constructor( tabs, panels ) {
			this.tabs = tabs;
			this.panels = panels;
			this.validTabs = tabs.map( ( tab ) => tab.dataset.tab );

			this.activate( SettingsTabs.tabFromHash() );
			window.addEventListener( 'hashchange', () => this.activate( SettingsTabs.tabFromHash() ) );
		}

		static tabFromHash() {
			return ( window.location.hash || '' ).replace( '#', '' );
		}

		/** Shows the named tab, or the first tab when the name isn't one of them. */
		activate( tab ) {
			const activeTab = -1 === this.validTabs.indexOf( tab ) ? this.validTabs[ 0 ] : tab;

			this.tabs.forEach( ( el ) => el.classList.toggle( ACTIVE_CLASS, el.dataset.tab === activeTab ) );
			this.panels.forEach( ( el ) => el.classList.toggle( ACTIVE_CLASS, el.dataset.panel === activeTab ) );
		}
	}

	admin.ready( () => {
		const tabs = admin.all( '.omega-settings-nav__item' );
		const panels = admin.all( '.omega-settings-panel' );
		if ( tabs.length && panels.length ) {
			new SettingsTabs( tabs, panels );
		}
	} );
} )( window.OmegaDesignAdmin );
