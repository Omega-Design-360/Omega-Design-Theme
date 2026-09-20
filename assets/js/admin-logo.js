( function () {
	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! window.wp || ! wp.media ) {
			return;
		}

		document.querySelectorAll( '.omega-logo-picker' ).forEach( function ( picker ) {
			var chooseBtn = picker.querySelector( '.omega-logo-picker__choose' );
			var removeBtn = picker.querySelector( '.omega-logo-picker__remove' );
			var preview = picker.querySelector( '.omega-logo-picker__preview' );
			var hiddenInput = picker.querySelector( '.omega-logo-picker__input' );

			if ( ! chooseBtn || ! hiddenInput ) {
				return;
			}

			var frame;

			chooseBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: chooseBtn.getAttribute( 'data-title' ) || 'Select Logo',
					button: { text: chooseBtn.getAttribute( 'data-button' ) || 'Use as logo' },
					library: { type: 'image' },
					multiple: false,
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					var url = ( attachment.sizes && attachment.sizes.medium )
						? attachment.sizes.medium.url
						: attachment.url;

					hiddenInput.value = attachment.id;
					preview.innerHTML = '<img src="' + url + '" alt="" />';

					if ( removeBtn ) {
						removeBtn.style.display = '';
					}
				} );

				frame.open();
			} );

			if ( removeBtn ) {
				removeBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					hiddenInput.value = '0';
					preview.innerHTML = '<span class="omega-logo-placeholder dashicons dashicons-format-image"></span>';
					removeBtn.style.display = 'none';
				} );
			}
		} );
	} );
} )();
