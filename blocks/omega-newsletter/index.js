/**
 * "Omega Newsletter Form" block (omega-design/newsletter-form). A static
 * block - the editor renders the same markup the front end submits via
 * AJAX (see view.js + includes/core/leads.php), so text stays editable
 * per-instance via RichText with no PHP render_callback needed.
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components || !wp.i18n) {
		return;
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var RichText = wp.blockEditor.RichText;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var __ = wp.i18n.__;

	function formMarkup(props, isEdit) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;

		var emailInput = createElement('input', {
			type: 'email',
			className: 'omega-newsletter-form__input',
			placeholder: attributes.placeholder,
			name: 'omega_newsletter_email',
			required: true,
			disabled: isEdit
		});

		var honeypot = !isEdit && createElement('input', {
			type: 'text',
			name: 'omega_newsletter_company',
			className: 'omega-newsletter-form__honeypot',
			tabIndex: -1,
			autoComplete: 'off',
			'aria-hidden': 'true'
		});

		var button = isEdit
			? createElement(RichText, {
				tagName: 'button',
				className: 'omega-newsletter-form__submit wp-element-button',
				value: attributes.buttonText,
				onChange: function (v) { setAttributes({ buttonText: v }); },
				allowedFormats: []
			})
			: createElement('button', { type: 'submit', className: 'omega-newsletter-form__submit wp-element-button' }, attributes.buttonText);

		return createElement(
			'form',
			{ className: 'omega-newsletter-form', noValidate: true },
			honeypot,
			createElement('div', { className: 'omega-newsletter-form__row' }, emailInput, button),
			createElement('p', { className: 'omega-newsletter-form__message', 'aria-live': 'polite' })
		);
	}

	registerBlockType('omega-design/newsletter-form', {
		edit: function (props) {
			var blockProps = useBlockProps({ className: 'omega-newsletter-form-block' });

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __('Newsletter Form', 'omega-design') },
						createElement(TextControl, {
							label: __('Email field placeholder', 'omega-design'),
							value: props.attributes.placeholder,
							onChange: function (v) { props.setAttributes({ placeholder: v }); }
						}),
						createElement(TextControl, {
							label: __('Success message', 'omega-design'),
							value: props.attributes.successMessage,
							onChange: function (v) { props.setAttributes({ successMessage: v }); }
						})
					)
				),
				createElement('div', blockProps, formMarkup(props, true))
			);
		},

		save: function (props) {
			var blockProps = useBlockProps.save({
				className: 'omega-newsletter-form-block',
				'data-success-message': props.attributes.successMessage
			});
			return createElement('div', blockProps, formMarkup(props, false));
		}
	});
})(window.wp);
