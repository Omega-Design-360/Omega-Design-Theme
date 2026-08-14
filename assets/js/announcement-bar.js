/**
 * Omega Design - Announcement Bar dismiss button.
 * Remembers the dismissal in localStorage so it stays closed on later
 * page loads until the admin changes the bar's content (a different
 * message re-shows automatically - see the storage key below).
 */
(function () {
	'use strict';

	var bar = document.getElementById('omega-announcement-bar');
	if (!bar) return;

	var button = bar.querySelector('.omega-announcement-bar__dismiss');
	if (!button) return;

	// Keyed by the bar's own content, not a static key, so editing the
	// message (e.g. a new promo) makes it reappear for visitors who
	// already dismissed a previous one.
	var storageKey = 'omega-announcement-dismissed:' + bar.textContent.trim().slice(0, 100);

	try {
		if (window.localStorage.getItem(storageKey) === '1') {
			bar.classList.add('is-dismissed');
		}
	} catch (e) {}

	button.addEventListener('click', function () {
		bar.classList.add('is-dismissed');
		try {
			window.localStorage.setItem(storageKey, '1');
		} catch (e) {}
	});
})();
