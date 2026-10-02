/**
 * "Omega Product View Toggle" block (omega-design/view-toggle) - rendered
 * on the server (render.php), which the editor reuses for its preview so
 * the markup lives in one place.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.serverSideRender ) {
		return;
	}

	const el = wp.element.createElement;

	wp.blocks.registerBlockType( 'omega-design/view-toggle', {
		edit: function () {
			return el( 'div', wp.blockEditor.useBlockProps(), el( wp.serverSideRender, { block: 'omega-design/view-toggle' } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
