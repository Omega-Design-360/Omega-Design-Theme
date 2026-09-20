( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost ) {
		return;
	}

	var ToggleControl = wp.components.ToggleControl;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;

	var META_KEY = 'omega_hide_footer';
	var PREVIEW_STYLE_ID = 'omega-hide-footer-preview';

	/**
	 * Same technique as hide-header-toggle.js's previewHiddenInCanvas():
	 * an instant <style> injected into the canvas iframe, since the
	 * unsaved toggle state has no way to reach header_visibility.php's
	 * sibling PHP check until the post is actually saved.
	 */
	function previewHiddenInCanvas( hidden ) {
		function apply() {
			var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
			var doc = iframe && iframe.contentDocument;
			if ( ! doc || ! doc.head ) {
				return false;
			}
			var styleTag = doc.getElementById( PREVIEW_STYLE_ID );
			if ( ! styleTag ) {
				styleTag = doc.createElement( 'style' );
				styleTag.id = PREVIEW_STYLE_ID;
				doc.head.appendChild( styleTag );
			}
			// .site-footer covers this theme's own classic/block footers;
			// .wp-block-template-part is the wrapper Gutenberg renders
			// around a template part fetched via the wp/v2/block-renderer
			// REST endpoint (confirmed against this same site's header -
			// see hide-header-toggle.js), which is how the editor canvas
			// re-fetches the footer too.
			styleTag.textContent = hidden
				? '.site-footer,.wp-block-template-part{display:none !important;}'
				: '';
			return true;
		}

		if ( apply() ) {
			return function () {};
		}

		var attempts = 0;
		var intervalId = setInterval( function () {
			attempts++;
			if ( apply() || attempts > 20 ) {
				clearInterval( intervalId );
			}
		}, 250 );

		return function () {
			clearInterval( intervalId );
		};
	}

	var HideFooterControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return { hideFooter: !! meta[ META_KEY ] };
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setHideFooter: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value;
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		useEffect( function () {
			return previewHiddenInCanvas( props.hideFooter );
		}, [ props.hideFooter ] );

		return createElement( ToggleControl, {
			label: __( 'Hide footer', 'omega-design' ),
			help: props.hideFooter
				? __( 'The footer is hidden on this page.', 'omega-design' )
				: __( 'The footer shows normally on this page.', 'omega-design' ),
			checked: props.hideFooter,
			onChange: props.setHideFooter,
		} );
	} );

	window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
	window.OmegaDesignPageSettings.HideFooterControl = HideFooterControl;
} )( window.wp );
