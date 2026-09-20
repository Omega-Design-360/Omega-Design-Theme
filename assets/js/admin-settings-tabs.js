/**
 * Settings screen tab switcher (Omega Design > Settings). Driven entirely
 * by the URL hash rather than manual click handling, so the same logic
 * covers first load, clicking a tab, browser back/forward, and landing
 * back on the right panel after a form save redirects to #<tab> (see
 * menus.php's $tab_url helper) - one code path, no state to keep in sync.
 */
(function () {
	'use strict';

	var tabs = document.querySelectorAll('.omega-settings-nav__item');
	var panels = document.querySelectorAll('.omega-settings-panel');
	if (!tabs.length || !panels.length) {
		return;
	}

	var validTabs = [];
	tabs.forEach(function (tab) {
		validTabs.push(tab.dataset.tab);
	});

	function activate(tab) {
		if (validTabs.indexOf(tab) === -1) {
			tab = validTabs[0];
		}

		tabs.forEach(function (t) {
			t.classList.toggle('is-active', t.dataset.tab === tab);
		});
		panels.forEach(function (p) {
			p.classList.toggle('is-active', p.dataset.panel === tab);
		});
	}

	function tabFromHash() {
		return (window.location.hash || '').replace('#', '');
	}

	activate(tabFromHash());
	window.addEventListener('hashchange', function () {
		activate(tabFromHash());
	});
})();
