( function ( mode ) {
	if ( ! mode ) {
		return;
	}

	var MODE_CLASSES = [ 'omega-color-mode-auto', 'omega-color-mode-light', 'omega-color-mode-dark' ];
	var targetClass = 'omega-color-mode-' + mode;

	function apply() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		var body = iframe && iframe.contentDocument && iframe.contentDocument.body;
		if ( ! body ) {
			return false;
		}
		body.classList.remove.apply( body.classList, MODE_CLASSES );
		body.classList.add( targetClass );
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
} )( window.omegaColorMode );
