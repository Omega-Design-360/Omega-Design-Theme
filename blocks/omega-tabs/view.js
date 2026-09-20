/**
 * Front-end behaviour for the "Omega Tabs" block (omega-design/tabs).
 * The tab bar buttons are rendered server-side (includes/core/blocks.php);
 * this just wires up click-to-switch and keeps everything keyboard-
 * accessible.
 */
(function () {
	'use strict';

	function init(root) {
		var buttons = Array.prototype.slice.call(root.querySelectorAll(':scope > .omega-tabs__list > .omega-tabs__tab'));
		var panels = Array.prototype.slice.call(root.querySelectorAll(':scope > .omega-tabs__panels > .omega-tabs__panel'));

		if (!buttons.length || !panels.length) {
			return;
		}

		function activate(idx) {
			buttons.forEach(function (btn, i) {
				var active = i === idx;
				btn.classList.toggle('is-active', active);
				btn.setAttribute('aria-selected', active ? 'true' : 'false');
				btn.setAttribute('tabindex', active ? '0' : '-1');
			});
			panels.forEach(function (panel, i) {
				panel.classList.toggle('is-active', i === idx);
			});
		}

		buttons.forEach(function (btn, i) {
			btn.addEventListener('click', function () { activate(i); });
			btn.addEventListener('keydown', function (e) {
				var next = null;
				if (e.key === 'ArrowRight') { next = (i + 1) % buttons.length; }
				if (e.key === 'ArrowLeft') { next = (i - 1 + buttons.length) % buttons.length; }
				if (next !== null) {
					e.preventDefault();
					buttons[next].focus();
					activate(next);
				}
			});
		});

		activate(0);
	}

	function ready() {
		var groups = document.querySelectorAll('.omega-tabs');
		for (var i = 0; i < groups.length; i++) {
			init(groups[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}
})();
