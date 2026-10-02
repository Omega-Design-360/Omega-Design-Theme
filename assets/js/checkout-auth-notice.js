/**
 * Replaces WooCommerce's plain-text "must be logged in to checkout" prompt,
 * rendered client-side by the woocommerce/checkout block (wc-block-must-login-prompt),
 * with the theme's friendly login/register notice UI. The block's markup only
 * exists in a compiled JS bundle with no PHP template or filter to override it,
 * so a MutationObserver is used to catch it once React mounts it into the DOM.
 */
( function ( core, settings ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const PROMPT_SELECTOR = '.wc-block-must-login-prompt';
	const NOTICE_CLASS = 'omega-checkout-auth-notice';
	const BUTTON_CLASS = NOTICE_CLASS + '__button';
	const LOCK_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>';

	class CheckoutAuthNotice {
		constructor( options ) {
			this.settings = options || {};
			this.scan();
			core.observeMutations( document.body, () => this.scan() );
		}

		/** One "__{element}" child of the notice, as markup. */
		static part( tag, element, html ) {
			return '<' + tag + ' class="' + NOTICE_CLASS + '__' + element + '">' + html + '</' + tag + '>';
		}

		static button( href, variant, extraClass, label ) {
			return '<a href="' + href + '" class="' + BUTTON_CLASS + ' ' + BUTTON_CLASS + '--' + variant + extraClass + '">' + label + '</a>';
		}

		scan() {
			const prompt = document.querySelector( PROMPT_SELECTOR );
			if ( prompt && ! prompt.dataset.omegaEnhanced ) {
				this.enhance( prompt );
			}
		}

		actionsHtml( prompt ) {
			const settings = this.settings;
			const existingLink = prompt.querySelector( 'a' );
			const loginHref = settings.loginUrl || ( existingLink && existingLink.getAttribute( 'href' ) ) || '#';

			let actions = CheckoutAuthNotice.button( loginHref, 'primary', ' wp-element-button', settings.loginLabel );
			if ( settings.showRegister && settings.registerUrl ) {
				actions += CheckoutAuthNotice.button( settings.registerUrl, 'secondary', '', settings.registerLabel );
			}
			return actions;
		}

		enhance( prompt ) {
			prompt.dataset.omegaEnhanced = 'true';

			const part = CheckoutAuthNotice.part;
			const actions = this.actionsHtml( prompt );

			prompt.className = NOTICE_CLASS;
			prompt.innerHTML =
				'<div class="' + NOTICE_CLASS + '__icon" aria-hidden="true">' + LOCK_ICON + '</div>' +
				part( 'p', 'title', this.settings.title ) +
				part( 'p', 'message', this.settings.message ) +
				part( 'div', 'actions', actions );
		}
	}

	core.ready( () => new CheckoutAuthNotice( settings ) );
} )( window.OmegaDesign, window.omegaCheckoutAuth );
