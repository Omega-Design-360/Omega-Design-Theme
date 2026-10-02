/**
 * Applies the current color-mode class inside the editor canvas iframe,
 * since that document never runs the front end's body_class() filter.
 */
( function ( mode, editor ) {
	if ( ! mode || ! editor ) {
		return;
	}

	var MODE_CLASSES = [ 'omega-color-mode-auto', 'omega-color-mode-light', 'omega-color-mode-dark' ];
	var targetClass = 'omega-color-mode-' + mode;

	editor.retryUntilApplied( function () {
		var doc = editor.canvasDocument();
		var body = doc && doc.body;
		if ( ! body ) {
			return false;
		}
		body.classList.remove.apply( body.classList, MODE_CLASSES );
		body.classList.add( targetClass );
		return true;
	} );
} )( window.omegaColorMode, window.OmegaDesignEditor );
