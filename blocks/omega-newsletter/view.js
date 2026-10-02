/**
 * Front-end submit handler for the "Omega Newsletter Form" block
 * (omega-design/newsletter-form). Posts to admin-ajax.php via fetch, no
 * page reload. omegaNewsletter (ajaxUrl/nonce) is localized in
 * includes/core/blocks.php when this script is registered.
 */
( function ( core ) {
	'use strict';

	if ( ! core ) {
		return;
	}

	const STATES = [ 'success', 'error', 'loading' ];
	const MESSAGES = {
		missingEmail: 'Please enter your email address.',
		unavailable: 'Something went wrong. Please try again later.',
		failed: 'Something went wrong. Please try again.',
		success: 'Thanks — you\'re on the list!',
	};

	class NewsletterForm extends core.Component {
		constructor( form ) {
			super( form );
			this.wrapper = form.closest( '.omega-newsletter-form-block' );
			this.emailField = this.find( 'input[name="omega_newsletter_email"]' );
			this.honeypot = this.find( 'input[name="omega_newsletter_company"]' );
			this.submitButton = this.find( '.omega-newsletter-form__submit' );
			this.message = this.find( '.omega-newsletter-form__message' );

			form.addEventListener( 'submit', ( event ) => this.onSubmit( event ) );
		}

		setState( state, message ) {
			const classList = this.root.classList;
			classList.remove.apply( classList, STATES.map( ( name ) => 'is-' + name ) );
			classList.add( 'is-' + state );
			if ( this.message ) {
				this.message.textContent = message || '';
			}
		}

		setBusy( isBusy ) {
			if ( this.submitButton ) {
				this.submitButton.disabled = isBusy;
			}
		}

		successMessage() {
			return ( this.wrapper && this.wrapper.dataset.successMessage ) || MESSAGES.success;
		}

		requestBody( config ) {
			const body = new FormData();
			body.append( 'action', 'omega_newsletter_subscribe' );
			body.append( 'nonce', config.nonce );
			body.append( 'email', this.emailField.value );
			body.append( 'company', this.honeypot ? this.honeypot.value : '' );
			body.append( 'source_url', window.location.href );
			return body;
		}

		onSubmit( event ) {
			event.preventDefault();

			if ( ! this.emailField || ! this.emailField.value ) {
				this.setState( 'error', MESSAGES.missingEmail );
				return;
			}

			const config = window.omegaNewsletter;
			if ( ! config ) {
				this.setState( 'error', MESSAGES.unavailable );
				return;
			}

			this.setBusy( true );
			this.setState( 'loading', '' );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: this.requestBody( config ),
			} )
				.then( ( response ) => response.json() )
				.then( ( json ) => this.onResponse( json ) )
				.catch( () => this.onResponse( null ) );
		}

		onResponse( json ) {
			this.setBusy( false );

			if ( json && json.success ) {
				this.setState( 'success', this.successMessage() );
				this.root.reset();
			} else {
				this.setState( 'error', ( json && json.data && json.data.message ) || MESSAGES.failed );
			}
		}
	}

	core.mountAll( '.omega-newsletter-form', NewsletterForm );
} )( window.OmegaDesign );
