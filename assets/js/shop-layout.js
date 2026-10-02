/**
 * "Shop Layout" document-settings panel - see
 * includes/customizer/shop_layouts.php. Only ever enqueued while editing
 * the WooCommerce Shop page, so it never shows up on any other page.
 */
( function ( wp, config ) {
	if ( ! wp || ! wp.plugins || ! config ) {
		return;
	}

	var PluginDocumentSettingPanel = window.OmegaDesignEditor && window.OmegaDesignEditor.documentSettingPanel();
	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	var createElement = wp.element.createElement;
	var RadioControl = wp.components.RadioControl;
	var ExternalLink = wp.components.ExternalLink;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var __ = wp.i18n.__;

	var options = [ {
		value: '',
		label: __( 'Default', 'omega-design' ),
		description: __( 'The theme\'s standard Shop template.', 'omega-design' )
	} ].concat( config.layouts );

	function ShopLayoutPanel() {
		var layout = useSelect( function ( select ) {
			var meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
			return meta[ config.metaKey ] || '';
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		function setLayout( value ) {
			var meta = {};
			meta[ config.metaKey ] = value;
			editPost( { meta: meta } );
		}

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: 'omega-design-shop-layout',
				title: __( 'Shop Layout', 'omega-design' ),
				className: 'omega-shop-layout-panel'
			},
			createElement( RadioControl, {
				label: __( 'Layout for the Shop page', 'omega-design' ),
				hideLabelFromVision: true,
				selected: layout,
				options: options.map( function ( option ) {
					return { value: option.value, label: option.label, description: option.description };
				} ),
				onChange: setLayout
			} ),
			layout ? createElement(
				'p',
				{ style: { marginTop: '12px' } },
				createElement(
					ExternalLink,
					{ href: config.siteEditorUrl + encodeURIComponent( layout ) + '&canvas=edit' },
					__( 'Customize this layout in the Site Editor', 'omega-design' )
				)
			) : null,
			config.shopUrl ? createElement(
				'p',
				{ style: { marginTop: '8px' } },
				createElement( ExternalLink, { href: config.shopUrl }, __( 'View the Shop', 'omega-design' ) )
			) : null
		);
	}

	wp.plugins.registerPlugin( 'omega-design-shop-layout', { render: ShopLayoutPanel } );
} )( window.wp, window.OmegaShopLayouts );
