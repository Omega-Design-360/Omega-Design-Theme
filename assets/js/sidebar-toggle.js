( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var SelectControl = wp.components.SelectControl;
	var BaseControl = wp.components.BaseControl;
	var createElement = wp.element.createElement;
	var __ = wp.i18n.__;

	var MODE_OPTIONS = [
		{ label: __( 'Use site default', 'omega-design' ), value: '' },
		{ label: __( 'Show', 'omega-design' ), value: 'show' },
		{ label: __( 'Hide', 'omega-design' ), value: 'hide' },
	];

	/**
	 * Filled in from OmegaSidebarTemplates, localized by
	 * sidebar.php's enqueue_editor_assets() from the exact same
	 * get_template_choices() array the PHP-side resolver validates
	 * against - so this list can never drift out of sync with what
	 * a saved value is allowed to be.
	 */
	function templateOptions() {
		var choices = window.OmegaSidebarTemplates || {};
		var options = [ { label: __( 'Use site default', 'omega-design' ), value: '' } ];
		Object.keys( choices ).forEach( function ( slug ) {
			options.push( { label: choices[ slug ], value: slug } );
		} );
		return options;
	}

	/**
	 * Unlike header/footer visibility or background color, the sidebar
	 * only ever renders through the front-end template - the standalone
	 * post/page editor canvas never shows it, so there's nothing to live-
	 * preview here. The help text says so plainly instead of pretending.
	 */
	var SidebarControl = editor.withPostMeta( {
		mode: editor.textMeta( 'omega_sidebar_mode' ),
		template: editor.textMeta( 'omega_sidebar_template' ),
	} )( function ( props ) {
		return createElement(
			BaseControl,
			{ label: __( 'Sidebar', 'omega-design' ), __nextHasNoMarginBottom: true },
			createElement( SelectControl, {
				label: __( 'Visibility on this page', 'omega-design' ),
				value: props.mode,
				options: MODE_OPTIONS,
				onChange: props.setMode,
				__nextHasNoMarginBottom: true,
			} ),
			'hide' !== props.mode
				? createElement( SelectControl, {
						label: __( 'Sidebar content', 'omega-design' ),
						value: props.template,
						options: templateOptions(),
						onChange: props.setTemplate,
						__nextHasNoMarginBottom: true,
					} )
				: null,
			createElement(
				'p',
				{ className: 'components-base-control__help' },
				__( 'Not shown in this preview - check the front end after saving.', 'omega-design' )
			)
		);
	} );

	editor.registerPageSetting( 'SidebarControl', SidebarControl );
} )( window.wp, window.OmegaDesignEditor );
