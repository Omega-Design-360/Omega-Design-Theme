( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var __ = wp.i18n.__;

	/**
	 * Same instant canvas preview as hide-header-toggle.js, since the
	 * unsaved toggle state has no way to reach footer_visibility.php's
	 * PHP check until the post is actually saved. .site-footer covers this
	 * theme's own classic/block footers; .wp-block-template-part is the
	 * wrapper Gutenberg renders around a template part re-fetched via the
	 * wp/v2/block-renderer REST endpoint (see hide-header-toggle.js).
	 */
	function previewHiddenInCanvas( hidden ) {
		return editor.previewCanvasStyle(
			'omega-hide-footer-preview',
			hidden ? '.site-footer,.wp-block-template-part{display:none !important;}' : ''
		);
	}

	editor.registerPageSetting( 'HideFooterControl', editor.metaToggleControl( {
		key: 'omega_hide_footer',
		label: __( 'Hide footer', 'omega-design' ),
		helpOn: __( 'The footer is hidden on this page.', 'omega-design' ),
		helpOff: __( 'The footer shows normally on this page.', 'omega-design' ),
		preview: previewHiddenInCanvas,
	} ) );
} )( window.wp, window.OmegaDesignEditor );
