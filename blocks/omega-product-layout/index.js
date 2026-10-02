/**
 * "Omega Product Layout" block (omega-design/product-layout) - rendered on
 * the server (render.php) from the real product being viewed, so the
 * editor shows a placeholder pointing at where the layout is chosen.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.i18n ) {
		return;
	}

	const el = wp.element.createElement;
	const __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'omega-design/product-layout', {
		edit: function () {
			return el(
				'div',
				wp.blockEditor.useBlockProps(),
				el( wp.components.Placeholder, {
					icon: 'products',
					label: __( 'Omega Product Layout', 'omega-design' ),
					instructions: __( 'The product page renders here on the front end, in the layout chosen under Omega Design > Settings > Product Page.', 'omega-design' ),
				} )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
