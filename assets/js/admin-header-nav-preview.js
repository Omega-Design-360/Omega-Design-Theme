/**
 * Live-updates the Header Navigation preview strip on the theme's own
 * Settings page as the admin picks a classic menu and edits the appearance
 * overrides below it - see render_header_nav_form() in menus.php, which
 * renders the initial state and embeds each classic menu's top-level item
 * titles as JSON so this file never needs an AJAX round trip to redraw the
 * nav items.
 */
( function ( admin ) {
	'use strict';

	if ( ! admin ) {
		return;
	}

	const PREVIEW_IDS = {
		bar: 'omega-header-preview-bar',
		inner: 'omega-header-preview-inner',
		nav: 'omega-header-preview-nav',
		data: 'omega-header-preview-menus-data',
	};
	const FIELD_IDS = {
		menu: 'omega_classic_menu_id',
		background: 'omega_header_bg_color',
		textColor: 'omega_header_text_color',
		fontFamily: 'omega_header_font_family',
		fontSize: 'omega_header_font_size',
		height: 'omega_header_height',
	};
	const DEFAULT_ITEMS = [ 'Home', 'About', 'Services', 'Contact' ];

	// Mirrors classic_header::custom_style_css()'s own font_size_map/
	// height_map exactly, so the preview lands on the same steps as what
	// actually ships to the front end.
	const FONT_SIZE_MAP = { small: '0.875rem', medium: '1rem', large: '1.25rem', 'x-large': '1.75rem' };
	const HEIGHT_MAP = { compact: '0.55rem', regular: '1rem', spacious: '1.6rem' };

	class HeaderNavPreview {
		constructor( preview ) {
			this.preview = preview;
			this.fields = admin.elementsById( FIELD_IDS );
			this.menusData = admin.jsonFromElement( PREVIEW_IDS.data, {} );

			const updateColors = () => this.updateColors();
			admin.listen( this.fields.menu, 'change', () => this.renderNavItems() );
			admin.listen( this.fields.background, 'input', updateColors );
			admin.listen( this.fields.textColor, 'input', updateColors );
			admin.listen( this.fields.fontFamily, 'input', updateColors );
			admin.listen( this.fields.fontSize, 'change', () => this.updateFontSize() );
			admin.listen( this.fields.height, 'change', () => this.updateHeight() );

			this.renderNavItems();
		}

		/** The picked classic menu's top-level titles, or placeholder items. */
		navItems() {
			const items = this.menusData[ admin.fieldValue( this.fields.menu ) ];
			return items && items.length ? items : DEFAULT_ITEMS;
		}

		renderNavItems() {
			const nav = this.preview.nav;
			nav.innerHTML = '';
			this.navItems().forEach( ( label ) => {
				const item = document.createElement( 'span' );
				item.className = 'omega-header-preview__nav-item';
				item.textContent = label;
				nav.appendChild( item );
			} );
		}

		updateColors() {
			const style = this.preview.bar.style;
			style.background = admin.fieldValue( this.fields.background );
			style.color = admin.fieldValue( this.fields.textColor );
			style.fontFamily = admin.fieldValue( this.fields.fontFamily );
		}

		updateFontSize() {
			this.preview.nav.style.fontSize = admin.mappedValue( this.fields.fontSize, FONT_SIZE_MAP );
		}

		updateHeight() {
			const padding = admin.mappedValue( this.fields.height, HEIGHT_MAP );
			const style = this.preview.inner.style;
			style.paddingTop = padding;
			style.paddingBottom = padding;
		}
	}

	admin.ready( () => {
		const preview = admin.elementsById( PREVIEW_IDS );
		if ( admin.hasAll( preview, Object.keys( PREVIEW_IDS ) ) ) {
			new HeaderNavPreview( preview );
		}
	} );
} )( window.OmegaDesignAdmin );
