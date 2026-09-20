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

	var META_KEY = 'omega_hide_header';
	var PREVIEW_STYLE_ID = 'omega-hide-header-preview';

	/**
	 * The editor canvas is a snapshot of the full template (header/footer
	 * included, for a block theme) rendered once when it loads - toggling
	 * this switch only changes an *unsaved* meta value, which
	 * header_visibility.php's PHP check has no way to see until the post
	 * is actually saved. Mirrors background-color.js's own
	 * previewColorInCanvas(): push a <style> straight into the canvas
	 * iframe's own document for an instant preview instead.
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
			// .site-header covers this theme's own classic/block headers.
			// .wp-block-template-part is the wrapper Gutenberg itself
			// renders around a template part fetched via the
			// wp/v2/block-renderer REST endpoint (confirmed directly
			// against this site's real header - it wraps as
			// <header class="wp-block-template-part"> there regardless
			// of the part's own markup/classes), which is how the editor
			// canvas re-fetches the header - so this catches a
			// Site-Editor-customized header (e.g. a WooCommerce pattern)
			// too, not just this theme's own.
			styleTag.textContent = hidden
				? '.site-header,.wp-block-template-part{display:none !important;}'
				: '';
			return true;
		}

		if ( apply() ) {
			return function () {};
		}

		// The iframe may not have mounted yet on first load; keep trying briefly.
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

	var HideHeaderControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return { hideHeader: !! meta[ META_KEY ] };
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setHideHeader: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value;
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		useEffect( function () {
			return previewHiddenInCanvas( props.hideHeader );
		}, [ props.hideHeader ] );

		return createElement( ToggleControl, {
			label: __( 'Hide header', 'omega-design' ),
			help: props.hideHeader
				? __( 'The header is hidden on this page.', 'omega-design' )
				: __( 'The header shows normally on this page.', 'omega-design' ),
			checked: props.hideHeader,
			onChange: props.setHideHeader,
		} );
	} );

	// Exposed on the same shared namespace title-toggle.js uses -
	// background-color.js (loaded last) combines every registered control
	// into one PluginDocumentSettingPanel.
	window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
	window.OmegaDesignPageSettings.HideHeaderControl = HideHeaderControl;
} )( window.wp );
