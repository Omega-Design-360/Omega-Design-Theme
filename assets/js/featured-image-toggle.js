( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost ) {
		return;
	}

	var ToggleControl = wp.components.ToggleControl;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var createElement = wp.element.createElement;
	var __ = wp.i18n.__;

	var META_KEY = 'omega_hide_featured_image';

	var HideFeaturedImageControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return { hideFeaturedImage: !! meta[ META_KEY ] };
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setHideFeaturedImage: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value;
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		return createElement( ToggleControl, {
			label: __( 'Hide featured image', 'omega-design' ),
			help: props.hideFeaturedImage
				? __( 'The automatic featured image at the top of this post is hidden. A Featured Image block placed inside the content itself still shows.', 'omega-design' )
				: __( 'The featured image shows automatically at the top of this post.', 'omega-design' ),
			checked: props.hideFeaturedImage,
			onChange: props.setHideFeaturedImage,
		} );
	} );

	// Exposed on the same shared namespace title-toggle.js uses -
	// background-color.js (loaded last) combines every registered control
	// into one PluginDocumentSettingPanel.
	window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
	window.OmegaDesignPageSettings.FeaturedImageControl = HideFeaturedImageControl;
} )( window.wp );
