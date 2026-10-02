/**
 * Site Logo pickers (Omega Design > Dashboard/Settings) - each
 * .omega-logo-picker opens its own media modal and stores the chosen
 * attachment ID in its hidden input, so any number can share one form.
 */
( function ( admin ) {
	'use strict';

	if ( ! admin ) {
		return;
	}

	const PLACEHOLDER_HTML = '<span class="omega-logo-placeholder dashicons dashicons-format-image"></span>';
	const PREVIEW_SIZE = 'medium';

	class LogoPicker extends admin.Component {
		constructor( root ) {
			super( root );
			this.chooseButton = this.find( '.omega-logo-picker__choose' );
			this.removeButton = this.find( '.omega-logo-picker__remove' );
			this.preview = this.find( '.omega-logo-picker__preview' );
			this.input = this.find( '.omega-logo-picker__input' );
			this.frame = null;

			if ( ! this.chooseButton || ! this.input || ! window.wp || ! window.wp.media ) {
				return;
			}

			this.chooseButton.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				this.open();
			} );

			if ( this.removeButton ) {
				this.removeButton.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					this.clear();
				} );
			}
		}

		/** The attachment's preview-size URL, falling back to the full image. */
		static previewUrl( attachment ) {
			const sizes = attachment.sizes || {};
			return sizes[ PREVIEW_SIZE ] ? sizes[ PREVIEW_SIZE ].url : attachment.url;
		}

		/** The media modal, created once on first open and reused after. */
		mediaFrame() {
			if ( ! this.frame ) {
				this.frame = window.wp.media( {
					title: this.chooseButton.getAttribute( 'data-title' ) || 'Select Logo',
					button: { text: this.chooseButton.getAttribute( 'data-button' ) || 'Use as logo' },
					library: { type: 'image' },
					multiple: false,
				} );
				this.frame.on( 'select', () => {
					this.select( this.frame.state().get( 'selection' ).first().toJSON() );
				} );
			}
			return this.frame;
		}

		open() {
			this.mediaFrame().open();
		}

		setLogo( id, previewHtml, hasLogo ) {
			this.input.value = id;
			if ( this.preview ) {
				this.preview.innerHTML = previewHtml;
			}
			if ( this.removeButton ) {
				this.removeButton.style.display = hasLogo ? '' : 'none';
			}
		}

		select( attachment ) {
			const image = document.createElement( 'img' );
			image.src = LogoPicker.previewUrl( attachment );
			image.alt = '';
			this.setLogo( attachment.id, image.outerHTML, true );
		}

		clear() {
			this.setLogo( '0', PLACEHOLDER_HTML, false );
		}
	}

	admin.mountAll( '.omega-logo-picker', LogoPicker );
} )( window.OmegaDesignAdmin );
