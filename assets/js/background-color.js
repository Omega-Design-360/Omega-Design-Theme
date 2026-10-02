( function ( wp, editor ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost || ! editor ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = editor.documentSettingPanel();
	var TextControl = wp.components.TextControl;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;

	/**
	 * Mirrors background_color.php's enqueue_front_style(): the dark value
	 * only applies inside the same "omega-color-mode-*" body classes
	 * color_mode.php uses site-wide, so a page-level dark override still
	 * respects "Customize > Omega Design > Color Mode".
	 */
	function buildCss( light, dark ) {
		var css = '';
		if ( light ) {
			css += 'body{background-color:' + light + ' !important;}';
		}
		if ( dark ) {
			css += '@media (prefers-color-scheme: dark){body.omega-color-mode-auto{background-color:' + dark + ' !important;}}';
			css += 'body.omega-color-mode-dark{background-color:' + dark + ' !important;}';
		}
		return css;
	}

	function orEmpty( value ) {
		return value || '';
	}

	/**
	 * The standalone editor canvas is a separate iframe document, so the
	 * front end's inline style never reaches it. Preview live by pushing
	 * the same rules straight into the canvas iframe's own stylesheet.
	 */
	var BackgroundColorControl = editor.withPostMeta( {
		light: editor.textMeta( 'omega_background_color', orEmpty ),
		dark: editor.textMeta( 'omega_background_color_dark', orEmpty ),
	} )( function ( props ) {
		useEffect( function () {
			return editor.previewCanvasStyle( 'omega-background-color-preview', buildCss( props.light, props.dark ) );
		}, [ props.light, props.dark ] );

		return createElement(
			'div',
			{ className: 'omega-background-color-control' },
			createElement( TextControl, {
				className: 'omega-background-color-control__variable',
				label: __( 'Background Color', 'omega-design' ),
				help: __( 'CSS value, e.g. var(--wp--custom--page-background) or #ffffff.', 'omega-design' ),
				placeholder: 'var(--wp--custom--page-background)',
				value: props.light,
				onChange: props.setLight,
			} ),
			createElement( TextControl, {
				className: 'omega-background-color-control__variable',
				label: __( 'Background Color (Dark Mode)', 'omega-design' ),
				help: __( 'Used instead whenever the site is showing dark mode.', 'omega-design' ),
				placeholder: 'var(--wp--preset--color--page-background-dark)',
				value: props.dark,
				onChange: props.setDark,
			} )
		);
	} );

	var PANEL_CLASS = 'omega-design-page-settings-panel';

	/**
	 * WordPress has no public API to relocate its built-in Summary card
	 * (Status, Publish, Slug, Author, Template, Discussion, Revisions,
	 * Parent) - PluginDocumentSettingPanel only guarantees custom panels
	 * render after it. Instead of guessing at WP's internal class names
	 * with CSS, physically move our panel's real DOM node to be the first
	 * child of its parent, using only our own known class name. A
	 * MutationObserver keeps re-applying this, since the sidebar re-renders
	 * on nearly every editor state change (each keystroke, meta update,
	 * etc.) and could otherwise put a newly-added native row back above us.
	 */
	function moveToTop() {
		var panel = document.querySelector( '.' + PANEL_CLASS );
		if ( ! panel || ! panel.parentElement ) {
			return null;
		}
		if ( panel.parentElement.firstElementChild !== panel ) {
			panel.parentElement.insertBefore( panel, panel.parentElement.firstElementChild );
		}
		return panel.parentElement;
	}

	/**
	 * The panel may not have mounted into the DOM yet on first load
	 * (Slot/Fill renders a tick after this component does), so this keeps
	 * trying briefly, then attaches the observer once it exists.
	 */
	function keepAtTop() {
		var observer = null;
		var stopRetrying = editor.retryUntilApplied( function () {
			var parent = moveToTop();
			if ( ! parent ) {
				return false;
			}
			observer = new MutationObserver( moveToTop );
			observer.observe( parent, { childList: true } );
			return true;
		} );

		return function () {
			stopRetrying();
			if ( observer ) {
				observer.disconnect();
			}
		};
	}

	/** Every control registered on the shared Page Settings namespace, in panel order. */
	var PANEL_CONTROLS = [
		'HideTitleControl',
		'HideHeaderControl',
		'HideFooterControl',
		'FeaturedImageControl',
		'ContentWidthControl',
		'SidebarControl',
	];

	function PageSettingsPanel() {
		var settings = window.OmegaDesignPageSettings || {};

		useEffect( keepAtTop, [] );

		return createElement.apply( null, [
			PluginDocumentSettingPanel,
			{
				name: 'omega-design-page-settings',
				title: __( 'Page Settings', 'omega-design' ),
				className: PANEL_CLASS,
				initialOpen: true,
			},
		].concat( PANEL_CONTROLS.map( function ( name ) {
			return settings[ name ] ? createElement( settings[ name ] ) : null;
		} ), [ createElement( BackgroundColorControl ) ] ) );
	}

	// This script is enqueued last in the dependency chain (title-toggle ->
	// content-width -> hide-header -> hide-footer -> featured-image-toggle
	// -> sidebar-toggle -> background-color), so by the time it runs, every
	// other control has already registered itself on the shared namespace,
	// and PageSettingsPanel can combine all of them.
	registerPlugin( 'omega-design-page-settings', {
		render: PageSettingsPanel,
	} );
} )( window.wp, window.OmegaDesignEditor );
