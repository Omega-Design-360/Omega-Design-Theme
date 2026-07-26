(function () {
	'use strict';

	var header = document.querySelector('.site-header');
	if (!header) return;

	var triggers = header.querySelectorAll('.omega-megamenu-trigger');
	var panels   = header.querySelectorAll('.omega-megamenu-panel');
	var closeTimer = null;

	function getPanelClass(triggerEl) {
		var classes = triggerEl.className.split(' ');
		for (var i = 0; i < classes.length; i++) {
			if (classes[i].indexOf('omega-panel--') === 0) return classes[i];
		}
		return null;
	}

	function showPanel(panelClass) {
		panels.forEach(function (p) { p.classList.remove('is-active'); });
		var panel = header.querySelector('.omega-megamenu-panel.' + panelClass);
		if (panel) panel.classList.add('is-active');
	}

	function hideAll() {
		panels.forEach(function (p) { p.classList.remove('is-active'); });
	}

	function scheduleHide() {
		closeTimer = setTimeout(function () {
			var hovered = header.querySelector('.omega-megamenu-panel:hover');
			if (!hovered) hideAll();
		}, 150);
	}

	triggers.forEach(function (trigger) {
		var panelClass = getPanelClass(trigger);

		trigger.addEventListener('mouseenter', function () {
			clearTimeout(closeTimer);
			if (panelClass) showPanel(panelClass);
		});

		trigger.addEventListener('mouseleave', scheduleHide);

		trigger.addEventListener('click', function (e) {
			if (!panelClass) return;
			var panel = header.querySelector('.omega-megamenu-panel.' + panelClass);
			if (panel && panel.classList.contains('is-active')) {
				hideAll();
			} else {
				showPanel(panelClass);
			}
			e.preventDefault();
		});

		// Keyboard: open on Enter/Space, close on Escape
		trigger.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				var panel = header.querySelector('.omega-megamenu-panel.' + panelClass);
				if (panel && panel.classList.contains('is-active')) {
					hideAll();
				} else {
					if (panelClass) showPanel(panelClass);
				}
			}
			if (e.key === 'Escape') hideAll();
		});
	});

	panels.forEach(function (panel) {
		panel.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
		panel.addEventListener('mouseleave', scheduleHide);
	});

	// Close on outside click
	document.addEventListener('click', function (e) {
		if (!header.contains(e.target)) hideAll();
	});

	// Close on Escape anywhere
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') hideAll();
	});
})();
