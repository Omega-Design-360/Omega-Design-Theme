/**
 * Live-updates the Announcement Bar preview strip on the theme's own
 * Settings page as the admin edits content/colors/dismissible below it -
 * no fixed set of choices to pick between here (unlike the Color Scheme/
 * Mode/Sidebar/Header Style cards), so a re-rendering preview is the
 * equivalent instead of a static one.
 */
( function ( admin ) {
	'use strict';

	if ( ! admin ) {
		return;
	}

	const PREVIEW_IDS = {
		bar: 'omega-announcement-preview-bar',
		content: 'omega-announcement-preview-content',
		dismiss: 'omega-announcement-preview-dismiss',
	};
	const FIELD_IDS = {
		content: 'omega_announcement_content',
		background: 'omega_announcement_bg',
		textColor: 'omega_announcement_text_color',
	};

	class AnnouncementPreview {
		constructor( preview ) {
			this.preview = preview;
			this.fields = admin.elementsById( FIELD_IDS );
			this.fields.dismissible = document.querySelector( 'input[name="omega_announcement_dismissible"]' );
			this.emptyPlaceholder = preview.content.getAttribute( 'data-empty-text' ) || preview.content.textContent;

			admin.listen( this.fields.content, 'input', () => this.updateContent() );
			admin.listen( this.fields.background, 'input', () => this.updateColors() );
			admin.listen( this.fields.textColor, 'input', () => this.updateColors() );
			admin.listen( this.fields.dismissible, 'change', () => this.updateDismissible() );
		}

		static isBlankHtml( html ) {
			return '' === html.replace( /<[^>]*>/g, '' ).trim();
		}

		updateContent() {
			const html = admin.fieldValue( this.fields.content );
			if ( AnnouncementPreview.isBlankHtml( html ) ) {
				this.preview.content.textContent = this.emptyPlaceholder;
			} else {
				// Trusted the same way the live front-end bar trusts it (see
				// announcement_bar.php's own render_bar() docblock) - this
				// textarea is only ever reachable by a manage_options user in
				// the first place.
				this.preview.content.innerHTML = html;
			}
		}

		updateColors() {
			const bar = this.preview.bar;
			bar.style.backgroundColor = admin.fieldValue( this.fields.background );
			bar.style.color = admin.fieldValue( this.fields.textColor );
		}

		updateDismissible() {
			// The real front-end stylesheet (announcement-bar.css, reused
			// here for pixel parity) sets display:flex on this button via a
			// plain class selector - the `hidden` attribute's own implicit
			// display:none loses to that (author styles beat the UA
			// stylesheet), so toggling display directly here is what
			// actually wins the cascade instead.
			this.preview.dismiss.style.display = this.fields.dismissible.checked ? '' : 'none';
		}
	}

	admin.ready( () => {
		const preview = admin.elementsById( PREVIEW_IDS );
		if ( admin.hasAll( preview, Object.keys( PREVIEW_IDS ) ) ) {
			new AnnouncementPreview( preview );
		}
	} );
} )( window.OmegaDesignAdmin );
