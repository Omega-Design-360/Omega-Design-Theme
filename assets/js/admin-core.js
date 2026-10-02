/**
 * Shared helpers for the theme's own admin screens (Omega Design >
 * Dashboard/Settings and the Customizer controls), exposed as
 * window.OmegaDesignAdmin on top of window.OmegaDesign (omega-core.js).
 */
( function ( window, document, core ) {
	'use strict';

	/** A field's current value, or '' when the field is missing or empty. */
	function fieldValue( field ) {
		return field && field.value ? field.value : '';
	}

	/** map[ field's value ], or '' when the field is missing or the value isn't mapped. */
	function mappedValue( field, map ) {
		return field && map[ field.value ] ? map[ field.value ] : '';
	}

	/** Binds handler to a field's event - fields a given form omits are skipped. */
	function listen( field, eventName, handler ) {
		if ( field ) {
			field.addEventListener( eventName, handler );
		}
	}

	/**
	 * { name: element } for each { name: id } entry - missing elements come
	 * back as null, so callers can check only the ones they require.
	 */
	function elementsById( ids ) {
		const elements = {};
		Object.keys( ids ).forEach( function ( name ) {
			elements[ name ] = document.getElementById( ids[ name ] );
		} );
		return elements;
	}

	/** True when every named element in elements was found. */
	function hasAll( elements, names ) {
		return names.every( function ( name ) {
			return !! elements[ name ];
		} );
	}

	/** Parses the JSON text of the element with this id, or returns fallback. */
	function jsonFromElement( id, fallback ) {
		const el = document.getElementById( id );
		try {
			return el ? JSON.parse( el.textContent || '' ) : fallback;
		} catch ( e ) {
			return fallback;
		}
	}

	window.OmegaDesignAdmin = Object.assign( {}, core, {
		elementsById,
		fieldValue,
		hasAll,
		jsonFromElement,
		listen,
		mappedValue,
	} );
} )( window, document, window.OmegaDesign );
