/**
 * Shared helpers for the theme's block editor scripts - the Page Settings
 * sidebar controls (title-toggle.js, content-width.js, hide-header-toggle.js,
 * hide-footer-toggle.js, featured-image-toggle.js, sidebar-toggle.js,
 * background-color.js) and the canvas preview scripts (color-mode-editor.js,
 * color-scheme-editor.js). Exposed as window.OmegaDesignEditor; loaded
 * first via each of those scripts' dependencies.
 */
( function ( window, document ) {
	var CANVAS_SELECTOR = 'iframe[name="editor-canvas"]';
	var RETRY_INTERVAL_MS = 250;
	var MAX_RETRIES = 20;

	/**
	 * The editor canvas iframe's document, or null until it has mounted.
	 * The canvas is a separate document (an iframe since WP 5.9), so
	 * nothing printed in the outer editor page reaches it.
	 */
	function canvasDocument() {
		var iframe = document.querySelector( CANVAS_SELECTOR );
		return ( iframe && iframe.contentDocument ) || null;
	}

	/**
	 * Runs apply() now and, while it returns false (the canvas iframe may
	 * not have mounted yet on first load), retries it briefly. Returns a
	 * cleanup function that stops any pending retries - suitable as a
	 * useEffect() return value.
	 */
	function retryUntilApplied( apply ) {
		if ( apply() ) {
			return function () {};
		}

		var attempts = 0;
		var intervalId = setInterval( function () {
			attempts++;
			if ( apply() || attempts > MAX_RETRIES ) {
				clearInterval( intervalId );
			}
		}, RETRY_INTERVAL_MS );

		return function () {
			clearInterval( intervalId );
		};
	}

	/**
	 * Writes css into a <style id="{id}"> inside the canvas document,
	 * creating it once. Returns false when the canvas isn't ready yet.
	 */
	function setCanvasStyle( id, css ) {
		var doc = canvasDocument();
		if ( ! doc || ! doc.head ) {
			return false;
		}

		var styleTag = doc.getElementById( id );
		if ( ! styleTag ) {
			styleTag = doc.createElement( 'style' );
			styleTag.id = id;
			doc.head.appendChild( styleTag );
		}
		styleTag.textContent = css;
		return true;
	}

	/**
	 * Instant canvas preview of an unsaved setting: pushes css into the
	 * canvas as soon as it exists. Returns a retry cleanup function.
	 */
	function previewCanvasStyle( id, css ) {
		return retryUntilApplied( function () {
			return setCanvasStyle( id, css );
		} );
	}

	/**
	 * Higher-order component wiring post meta values into props.
	 * fields: { propName: { key: metaKey, read: fn, write: fn? } } - each
	 * becomes a `propName` prop (read( meta[key] )) and a
	 * `setPropName( value )` prop that saves write( value ) (or the value
	 * as-is) to the edited post's meta.
	 */
	function withPostMeta( fields ) {
		var wp = window.wp;
		var names = Object.keys( fields );

		return wp.compose.compose(
			wp.data.withSelect( function ( select ) {
				var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
				var props = {};
				names.forEach( function ( name ) {
					props[ name ] = fields[ name ].read( meta[ fields[ name ].key ] );
				} );
				return props;
			} ),
			wp.data.withDispatch( function ( dispatch ) {
				var setters = {};
				names.forEach( function ( name ) {
					var field = fields[ name ];
					setters[ 'set' + name.charAt( 0 ).toUpperCase() + name.slice( 1 ) ] = function ( value ) {
						var meta = {};
						meta[ field.key ] = field.write ? field.write( value ) : value;
						dispatch( 'core/editor' ).editPost( { meta: meta } );
					};
				} );
				return setters;
			} )
		);
	}

	/** A boolean meta field (read as true/false). */
	function flagMeta( key ) {
		return {
			key: key,
			read: function ( value ) {
				return !! value;
			},
		};
	}

	/** A string meta field (read as '' when unset); write is optional. */
	function textMeta( key, write ) {
		return {
			key: key,
			read: function ( value ) {
				return value || '';
			},
			write: write,
		};
	}

	/**
	 * A "Hide …" on/off switch bound to a boolean meta key, with help text
	 * for each state. preview( isOn ), when given, runs as an effect and
	 * should return its cleanup (e.g. a previewCanvasStyle() result).
	 */
	function metaToggleControl( options ) {
		var wp = window.wp;
		var createElement = wp.element.createElement;
		var useEffect = wp.element.useEffect;

		return withPostMeta( { isOn: flagMeta( options.key ) } )( function ( props ) {
			if ( options.preview ) {
				useEffect( function () {
					return options.preview( props.isOn );
				}, [ props.isOn ] );
			}

			return createElement( wp.components.ToggleControl, {
				label: options.label,
				help: props.isOn ? options.helpOn : options.helpOff,
				checked: props.isOn,
				onChange: props.setIsOn,
			} );
		} );
	}

	/**
	 * Adds a control to the shared Page Settings panel - background-color.js
	 * (loaded last in the dependency chain) combines every registered
	 * control into one PluginDocumentSettingPanel.
	 */
	function registerPageSetting( name, component ) {
		window.OmegaDesignPageSettings = window.OmegaDesignPageSettings || {};
		window.OmegaDesignPageSettings[ name ] = component;
	}

	/**
	 * value/onChange props binding a control to one block attribute -
	 * spread into a SelectControl, RangeControl, TextControl, ...
	 */
	function bindAttribute( props, key ) {
		return {
			value: props.attributes[ key ],
			onChange: function ( value ) {
				var change = {};
				change[ key ] = value;
				props.setAttributes( change );
			},
		};
	}

	/** checked/onChange props binding a ToggleControl to one boolean block attribute. */
	function bindToggle( props, key ) {
		var binding = bindAttribute( props, key );
		return { checked: !! binding.value, onChange: binding.onChange };
	}

	/**
	 * Drop-in for wp.components.ButtonGroup (deprecated since WP 6.8),
	 * which only ever rendered this same <div role="group"> wrapper - so
	 * existing rows of Buttons keep their exact look without the
	 * deprecation warning.
	 */
	function ButtonGroup( props ) {
		var className = 'components-button-group' + ( props.className ? ' ' + props.className : '' );
		return window.wp.element.createElement( 'div', Object.assign( {}, props, { className: className, role: 'group' } ) );
	}

	/**
	 * PluginDocumentSettingPanel from wp.editor (WP 6.6+), falling back to
	 * the deprecated wp.editPost copy on older versions.
	 */
	function documentSettingPanel() {
		var wp = window.wp;
		return ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel ) || null;
	}

	window.OmegaDesignEditor = {
		ButtonGroup: ButtonGroup,
		documentSettingPanel: documentSettingPanel,
		bindAttribute: bindAttribute,
		bindToggle: bindToggle,
		canvasDocument: canvasDocument,
		retryUntilApplied: retryUntilApplied,
		setCanvasStyle: setCanvasStyle,
		previewCanvasStyle: previewCanvasStyle,
		withPostMeta: withPostMeta,
		flagMeta: flagMeta,
		textMeta: textMeta,
		metaToggleControl: metaToggleControl,
		registerPageSetting: registerPageSetting,
	};
} )( window, document );
