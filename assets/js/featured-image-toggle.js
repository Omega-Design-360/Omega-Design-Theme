( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var __ = wp.i18n.__;

	editor.registerPageSetting( 'FeaturedImageControl', editor.metaToggleControl( {
		key: 'omega_hide_featured_image',
		label: __( 'Hide featured image', 'omega-design' ),
		helpOn: __( 'The automatic featured image at the top of this post is hidden. A Featured Image block placed inside the content itself still shows.', 'omega-design' ),
		helpOff: __( 'The featured image shows automatically at the top of this post.', 'omega-design' ),
	} ) );
} )( window.wp, window.OmegaDesignEditor );
