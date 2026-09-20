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

	var META_KEY = 'omega_hide_page_title';

	var HideTitleControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return { hideTitle: !! meta[ META_KEY ] };
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setHideTitle: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value;
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		return createElement( ToggleControl, {
			label: __( 'Hide page title', 'omega-design' ),
			help: props.hideTitle
				? __( 'The title is hidden on the front end.', 'omega-design' )
				: __( 'The title is visible on the front end.', 'omega-design' ),
			checked: props.hideTitle,
			onChange: props.setHideTitle,
		} );
	} );

	// Exposed on a shared namespace instead of self-registering a plugin:
	// background-color.js (loaded last in the dependency chain) combines
	// this with the other Omega Design controls into a single
	// PluginDocumentSettingPanel, which WordPress renders below all the
	// built-in panels (Summary, Slug, Author, Template, Discussion,
	// Revisions, Parent) at the bottom of the editor sidebar.
	window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
	window.OmegaDesignPageSettings.HideTitleControl = HideTitleControl;
} )( window.wp );
