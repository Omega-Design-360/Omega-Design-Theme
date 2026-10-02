/**
 * Writes the active color scheme's CSS (window.omegaColorSchemeCSS, from
 * customizer/color_scheme.php) into the editor canvas iframe, which never
 * receives the front end's own <style>.
 */
( function ( css, editor ) {
	if ( ! css || ! editor ) {
		return;
	}

	editor.previewCanvasStyle( 'omega-color-scheme-editor', css );
} )( window.omegaColorSchemeCSS, window.OmegaDesignEditor );
