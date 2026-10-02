( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var __ = wp.i18n.__;

	// Exposed on a shared namespace instead of self-registering a plugin:
	// background-color.js (loaded last in the dependency chain) combines
	// this with the other Omega Design controls into a single
	// PluginDocumentSettingPanel, which WordPress renders below all the
	// built-in panels (Summary, Slug, Author, Template, Discussion,
	// Revisions, Parent) at the bottom of the editor sidebar.
	editor.registerPageSetting( 'HideTitleControl', editor.metaToggleControl( {
		key: 'omega_hide_page_title',
		label: __( 'Hide page title', 'omega-design' ),
		helpOn: __( 'The title is hidden on the front end.', 'omega-design' ),
		helpOff: __( 'The title is visible on the front end.', 'omega-design' ),
	} ) );
} )( window.wp, window.OmegaDesignEditor );
