( function ( wp ) {
	if ( ! wp || ! wp.plugins || ! wp.editPost ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var TextControl = wp.components.TextControl;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;

	var META_KEY = 'omega_background_color';
	var DARK_META_KEY = 'omega_background_color_dark';
	var PREVIEW_STYLE_ID = 'omega-background-color-preview';

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

	/**
	 * The standalone editor canvas is a separate iframe document, so the
	 * front end's inline style never reaches it. Preview live by pushing
	 * the same rules straight into the canvas iframe's own stylesheet.
	 */
	function previewColorInCanvas( light, dark ) {
		var css = buildCss( light, dark );

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

	var BackgroundColorControl = compose(
		withSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return {
				light: meta[ META_KEY ] || '',
				dark: meta[ DARK_META_KEY ] || '',
			};
		} ),
		withDispatch( function ( dispatch ) {
			return {
				setLight: function ( value ) {
					var meta = {};
					meta[ META_KEY ] = value || '';
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
				setDark: function ( value ) {
					var meta = {};
					meta[ DARK_META_KEY ] = value || '';
					dispatch( 'core/editor' ).editPost( { meta: meta } );
				},
			};
		} )
	)( function ( props ) {
		useEffect( function () {
			return previewColorInCanvas( props.light, props.dark );
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

	function keepAtTop() {
		var parent = moveToTop();

		if ( parent ) {
			var observer = new MutationObserver( moveToTop );
			observer.observe( parent, { childList: true } );
			return function () {
				observer.disconnect();
			};
		}

		// The panel may not have mounted into the DOM yet on first load
		// (Slot/Fill renders a tick after this component does); keep
		// trying briefly, then attach the observer once it exists.
		var attempts = 0;
		var intervalId = setInterval( function () {
			attempts++;
			var found = moveToTop();
			if ( found ) {
				clearInterval( intervalId );
				var observer = new MutationObserver( moveToTop );
				observer.observe( found, { childList: true } );
			} else if ( attempts > 20 ) {
				clearInterval( intervalId );
			}
		}, 250 );

		return function () {
			clearInterval( intervalId );
		};
	}

	function PageSettingsPanel() {
		var settings = window.OmegaDesignPageSettings || {};

		useEffect( keepAtTop, [] );

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: 'omega-design-page-settings',
				title: __( 'Page Settings', 'omega-design' ),
				className: PANEL_CLASS,
				initialOpen: true,
			},
			settings.HideTitleControl ? createElement( settings.HideTitleControl ) : null,
			settings.HideHeaderControl ? createElement( settings.HideHeaderControl ) : null,
			settings.HideFooterControl ? createElement( settings.HideFooterControl ) : null,
			settings.FeaturedImageControl ? createElement( settings.FeaturedImageControl ) : null,
			settings.ContentWidthControl ? createElement( settings.ContentWidthControl ) : null,
			settings.SidebarControl ? createElement( settings.SidebarControl ) : null,
			createElement( BackgroundColorControl )
		);
	}

	// This script is enqueued last in the dependency chain (title-toggle ->
	// content-width -> hide-header -> hide-footer -> featured-image-toggle
	// -> sidebar-toggle -> background-color), so by the time it runs, every
	// other control has already registered itself on the shared namespace,
	// and PageSettingsPanel can combine all of them.
	registerPlugin( 'omega-design-page-settings', {
		render: PageSettingsPanel,
	} );
} )( window.wp );
