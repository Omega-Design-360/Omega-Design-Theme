( function ( css ) {
	if ( ! css ) {
		return;
	}

	var STYLE_ID = 'omega-color-scheme-editor';

	function apply() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		var doc = iframe && iframe.contentDocument;
		if ( ! doc || ! doc.head ) {
			return false;
		}
		var styleTag = doc.getElementById( STYLE_ID );
		if ( ! styleTag ) {
			styleTag = doc.createElement( 'style' );
			styleTag.id = STYLE_ID;
			doc.head.appendChild( styleTag );
		}
		styleTag.textContent = css;
		return true;
	}

	if ( apply() ) {
		return;
	}

	// The iframe may not have mounted yet on first load; keep trying briefly.
	var attempts = 0;
	var intervalId = setInterval( function () {
		attempts++;
		if ( apply() || attempts > 20 ) {
			clearInterval( intervalId );
		}
	}, 250 );
} )( window.omegaColorSchemeCSS );
