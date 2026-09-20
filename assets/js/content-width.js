( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost ) {
		return;
	}

	var BaseControl = wp.components.BaseControl;
	var Button = wp.components.Button;
	var ButtonGroup = wp.components.ButtonGroup;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;

	var META_KEY = 'omega_content_width';
	var PREVIEW_STYLE_ID = 'omega-content-width-preview';

	var WIDTH_OPTIONS = [
		{ label: __( 'Standard', 'omega-design' ), value: '' },
		{ label: __( 'Wide', 'omega-design' ), value: 'wide' },
		{ label: __( 'Full', 'omega-design' ), value: 'full' }
	];

	/**
	 * core/post-content only ever renders through render_block_core/post-content
	 * on the front end (and when the Site Editor renders the template itself) -
	 * the standalone Page/Post editor canvas shows the post's own blocks
	 * directly, with no post-content wrapper for that PHP filter to touch. To
	 * preview "Page Width" live there, push the same custom-property override
	 * straight into the editor canvas iframe's stylesheet instead.
	 */
	function previewWidthInCanvas( width ) {
		var css = '';
		if ( 'wide' === width ) {
			css = ':root{--wp--style--global--content-size: var(--wp--style--global--wide-size) !important;}';
		} else if ( 'full' === width ) {
			css = ':root{--wp--style--global--content-size: 100% !important;}';
		}

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
			styleTag.textContent = css;
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

	var ContentWidthControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return { width: meta[ META_KEY ] || '' };
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setWidth: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value;
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		useEffect( function () {
			return previewWidthInCanvas( props.width );
		}, [ props.width ] );

		var buttons = WIDTH_OPTIONS.map( function ( option ) {
			var isActive = props.width === option.value;
			return createElement(
				Button,
				{
					key: option.label,
					variant: isActive ? 'primary' : 'secondary',
					isPressed: isActive,
					onClick: function () {
						props.setWidth( option.value );
					}
				},
				option.label
			);
		} );

		return createElement(
			BaseControl,
			{
				label: __( 'Page Width', 'omega-design' ),
				help: __( 'Controls how wide the page content area is on the front end.', 'omega-design' )
			},
			createElement( ButtonGroup, {}, buttons )
		);
	} );

	// Exposed on a shared namespace instead of self-registering a plugin;
	// see title-toggle.js for why.
	window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
	window.OmegaDesignPageSettings.ContentWidthControl = ContentWidthControl;
} )( window.wp );
