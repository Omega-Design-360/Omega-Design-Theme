( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var __ = wp.i18n.__;

	/**
	 * The editor canvas is a snapshot of the full template (header/footer
	 * included, for a block theme) rendered once when it loads - toggling
	 * this switch only changes an *unsaved* meta value, which
	 * header_visibility.php's PHP check has no way to see until the post
	 * is actually saved, so the canvas gets an instant <style> preview.
	 *
	 * .site-header covers this theme's own classic/block headers.
	 * .wp-block-template-part is the wrapper Gutenberg itself renders
	 * around a template part fetched via the wp/v2/block-renderer REST
	 * endpoint (confirmed directly against this site's real header - it
	 * wraps as <header class="wp-block-template-part"> there regardless of
	 * the part's own markup/classes), which is how the editor canvas
	 * re-fetches the header - so this catches a Site-Editor-customized
	 * header (e.g. a WooCommerce pattern) too, not just this theme's own.
	 */
	function previewHiddenInCanvas( hidden ) {
		return editor.previewCanvasStyle(
			'omega-hide-header-preview',
			hidden ? '.site-header,.wp-block-template-part{display:none !important;}' : ''
		);
	}

	editor.registerPageSetting( 'HideHeaderControl', editor.metaToggleControl( {
		key: 'omega_hide_header',
		label: __( 'Hide header', 'omega-design' ),
		helpOn: __( 'The header is hidden on this page.', 'omega-design' ),
		helpOff: __( 'The header shows normally on this page.', 'omega-design' ),
		preview: previewHiddenInCanvas,
	} ) );
} )( window.wp, window.OmegaDesignEditor );
