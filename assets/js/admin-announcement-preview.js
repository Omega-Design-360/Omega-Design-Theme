/**
 * Live-updates the Announcement Bar preview strip on the theme's own
 * Settings page as the admin edits content/colors/dismissible below it -
 * no fixed set of choices to pick between here (unlike the Color Scheme/
 * Mode/Sidebar/Header Style cards), so a re-rendering preview is the
 * equivalent instead of a static one.
 */
(function () {
	'use strict';

	function init() {
		var bar = document.getElementById('omega-announcement-preview-bar');
		var contentEl = document.getElementById('omega-announcement-preview-content');
		var dismissBtn = document.getElementById('omega-announcement-preview-dismiss');
		if (!bar || !contentEl || !dismissBtn) {
			return;
		}

		var contentField = document.getElementById('omega_announcement_content');
		var bgField = document.getElementById('omega_announcement_bg');
		var textColorField = document.getElementById('omega_announcement_text_color');
		var dismissibleField = document.querySelector('input[name="omega_announcement_dismissible"]');

		var emptyPlaceholder = contentEl.getAttribute('data-empty-text') || contentEl.textContent;

		function updateContent() {
			if (!contentField) {
				return;
			}
			var html = contentField.value;
			if ('' === html.replace(/<[^>]*>/g, '').trim()) {
				contentEl.textContent = emptyPlaceholder;
			} else {
				// Trusted the same way the live front-end bar trusts it
				// (see announcement_bar.php's own render_bar() docblock) -
				// this textarea is only ever reachable by a manage_options
				// user in the first place.
				contentEl.innerHTML = html;
			}
		}

		function updateColors() {
			bar.style.backgroundColor = bgField && bgField.value ? bgField.value : '';
			bar.style.color = textColorField && textColorField.value ? textColorField.value : '';
		}

		function updateDismissible() {
			if (!dismissibleField) {
				return;
			}
			// The real front-end stylesheet (announcement-bar.css, reused
			// here for pixel parity) sets display:flex on this button via
			// a plain class selector - the `hidden` attribute's own
			// implicit display:none loses to that (author styles beat the
			// UA stylesheet), so toggling display directly here is what
			// actually wins the cascade instead.
			dismissBtn.style.display = dismissibleField.checked ? '' : 'none';
		}

		if (contentField) {
			contentField.addEventListener('input', updateContent);
		}
		if (bgField) {
			bgField.addEventListener('input', updateColors);
		}
		if (textColorField) {
			textColorField.addEventListener('input', updateColors);
		}
		if (dismissibleField) {
			dismissibleField.addEventListener('change', updateDismissible);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
