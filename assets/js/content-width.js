( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var BaseControl = wp.components.BaseControl;
	var Button = wp.components.Button;
	var ButtonGroup = editor.ButtonGroup;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;

	var WIDTH_OPTIONS = [
		{ label: __( 'Standard', 'omega-design' ), value: '' },
		{ label: __( 'Wide', 'omega-design' ), value: 'wide' },
		{ label: __( 'Full', 'omega-design' ), value: 'full' }
	];

	var WIDTH_CSS = {
		wide: ':root{--wp--style--global--content-size: var(--wp--style--global--wide-size) !important;}',
		full: ':root{--wp--style--global--content-size: 100% !important;}'
	};

	/**
	 * core/post-content only ever renders through render_block_core/post-content
	 * on the front end (and when the Site Editor renders the template itself) -
	 * the standalone Page/Post editor canvas shows the post's own blocks
	 * directly, with no post-content wrapper for that PHP filter to touch. To
	 * preview "Page Width" live there, push the same custom-property override
	 * straight into the editor canvas iframe's stylesheet instead.
	 */
	function previewWidthInCanvas( width ) {
		var css = Object.prototype.hasOwnProperty.call( WIDTH_CSS, width ) ? WIDTH_CSS[ width ] : '';
		return editor.previewCanvasStyle( 'omega-content-width-preview', css );
	}

	var ContentWidthControl = editor.withPostMeta( {
		width: editor.textMeta( 'omega_content_width' ),
	} )( function ( props ) {
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
	editor.registerPageSetting( 'ContentWidthControl', ContentWidthControl );
} )( window.wp, window.OmegaDesignEditor );
