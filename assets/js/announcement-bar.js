/**
 * Omega Design - Announcement Bar dismiss button.
 * Remembers the dismissal in localStorage so it stays closed on later
 * page loads until the admin changes the bar's content (a different
 * message re-shows automatically - see storageKey below).
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const STORAGE_PREFIX = 'omega-announcement-dismissed:';
	const STORAGE_KEY_LENGTH = 100;
	const DISMISSED_CLASS = 'is-dismissed';

	class AnnouncementBar extends core.Component {
		constructor( root ) {
			super( root );

			// Keyed by the bar's own content, not a static key, so editing
			// the message (e.g. a new promo) makes it reappear for visitors
			// who already dismissed a previous one.
			this.storageKey = STORAGE_PREFIX + root.textContent.trim().slice( 0, STORAGE_KEY_LENGTH );

			if ( core.storage.get( this.storageKey, false ) ) {
				this.hide();
			}

			this.on( 'click', '.omega-announcement-bar__dismiss', () => this.dismiss() );
		}

		hide() {
			this.root.classList.add( DISMISSED_CLASS );
		}

		dismiss() {
			this.hide();
			core.storage.set( this.storageKey, 1 );
		}
	}

	core.mountAll( '#omega-announcement-bar', AnnouncementBar );
} )( window.OmegaDesign );
