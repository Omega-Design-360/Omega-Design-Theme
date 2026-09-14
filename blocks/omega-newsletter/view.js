/**
 * Front-end submit handler for the "Omega Newsletter Form" block
 * (omega-design/newsletter-form). Posts to admin-ajax.php via fetch, no
 * page reload. omegaNewsletter (ajaxUrl/nonce) is localized in
 * includes/core/blocks.php when this script is registered.
 */
(function () {
	'use strict';

	function setState(form, state, message) {
		var msg = form.querySelector('.omega-newsletter-form__message');
		form.classList.remove('is-success', 'is-error', 'is-loading');
		form.classList.add('is-' + state);
		if (msg) {
			msg.textContent = message || '';
		}
	}

	function handleSubmit(e) {
		e.preventDefault();
		var form = e.target;
		var wrapper = form.closest('.omega-newsletter-form-block');
		var emailField = form.querySelector('input[name="omega_newsletter_email"]');
		var honeypot = form.querySelector('input[name="omega_newsletter_company"]');
		var submitBtn = form.querySelector('.omega-newsletter-form__submit');

		if (!emailField || !emailField.value) {
			setState(form, 'error', 'Please enter your email address.');
			return;
		}

		if (typeof window.omegaNewsletter === 'undefined') {
			setState(form, 'error', 'Something went wrong. Please try again later.');
			return;
		}

		if (submitBtn) { submitBtn.disabled = true; }
		setState(form, 'loading', '');

		var body = new FormData();
		body.append('action', 'omega_newsletter_subscribe');
		body.append('nonce', window.omegaNewsletter.nonce);
		body.append('email', emailField.value);
		body.append('company', honeypot ? honeypot.value : '');
		body.append('source_url', window.location.href);

		fetch(window.omegaNewsletter.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (submitBtn) { submitBtn.disabled = false; }
				if (json && json.success) {
					var successMessage = (wrapper && wrapper.dataset.successMessage) || 'Thanks — you\'re on the list!';
					setState(form, 'success', successMessage);
					form.reset();
				} else {
					setState(form, 'error', (json && json.data && json.data.message) || 'Something went wrong. Please try again.');
				}
			})
			.catch(function () {
				if (submitBtn) { submitBtn.disabled = false; }
				setState(form, 'error', 'Something went wrong. Please try again.');
			});
	}

	function ready() {
		var forms = document.querySelectorAll('.omega-newsletter-form');
		for (var i = 0; i < forms.length; i++) {
			forms[i].addEventListener('submit', handleSubmit);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}
})();
