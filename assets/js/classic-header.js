/**
 * Omega Design - Classic Header mobile toggle.
 * Vanilla, no dependencies. A single delegated click listener handles the
 * main hamburger, each item's submenu/Mega-Menu arrow, and outside-click
 * closing, in that explicit priority order - rather than several separate
 * listeners each trying to stopPropagation() past one another, which is
 * exactly the kind of setup where one case (the arrow) can end up also
 * triggering another (the main nav closing) depending on event timing.
 */
(function () {
	'use strict';

	// The mobile menu is a full-screen overlay (classic-header.css), locked
	// to the viewport rather than scrolling away with the page - reflects
	// that in <html>'s own scroll state too, so the page behind it can't
	// be dragged around while it's open.
	function syncBodyScrollLock() {
		var anyOpen = !!document.querySelector('.omega-classic-header__nav.is-open');
		document.documentElement.classList.toggle('omega-nav-open', anyOpen);
	}

	function closeMainNav(exceptHeader) {
		document.querySelectorAll('.omega-classic-header').forEach(function (header) {
			if (header === exceptHeader) return;
			var nav = header.querySelector('.omega-classic-header__nav.is-open');
			var btn = header.querySelector('.omega-classic-header__toggle.is-active');
			if (nav) nav.classList.remove('is-open');
			if (btn) {
				btn.classList.remove('is-active');
				btn.setAttribute('aria-expanded', 'false');
			}
		});
		syncBodyScrollLock();
	}

	document.addEventListener('click', function (e) {
		// 1) A per-item arrow (megamenu.php's inject_classic_submenu_toggle()):
		// toggles its own sub-menu/Mega-Menu panel (its next sibling) only -
		// never the main nav, and never navigates anywhere.
		var submenuToggle = e.target.closest('.omega-classic-header__submenu-toggle');
		if (submenuToggle) {
			e.preventDefault();
			var target = submenuToggle.nextElementSibling;
			if (target) {
				var expanding = !target.classList.contains('is-expanded');
				target.classList.toggle('is-expanded', expanding);
				submenuToggle.classList.toggle('is-active', expanding);
				submenuToggle.setAttribute('aria-expanded', expanding ? 'true' : 'false');
			}
			return;
		}

		// 2) The main hamburger.
		var mainToggle = e.target.closest('.omega-classic-header__toggle');
		if (mainToggle) {
			var header = mainToggle.closest('.omega-classic-header');
			var nav = header ? header.querySelector('.omega-classic-header__nav') : null;
			if (nav) {
				var opening = !nav.classList.contains('is-open');
				closeMainNav(opening ? header : null);
				nav.classList.toggle('is-open', opening);
				mainToggle.classList.toggle('is-active', opening);
				mainToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
				syncBodyScrollLock();
			}
			return;
		}

		// 3) Anything else outside every header: close the main nav.
		if (!e.target.closest('.omega-classic-header')) {
			closeMainNav();
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeMainNav();
	});

	// Every header's own rendered height, exposed as a CSS custom property
	// so the mobile full-screen menu (classic-header.css) can start exactly
	// below it - varies per style (Minimal Bar vs Centered vs Boxed) and per
	// site (logo size, tagline), so this has to be measured, never a fixed
	// guessed number.
	function initMobileNavOffsets() {
		var headers = document.querySelectorAll('.omega-classic-header');
		if (!headers.length) return;

		function syncOffsets() {
			headers.forEach(function (header) {
				header.style.setProperty('--omega-ch-height', header.offsetHeight + 'px');
			});
		}
		syncOffsets();
		window.addEventListener('resize', syncOffsets);
	}

	// Sticky header (opt-in, Settings > Header Navigation "Sticky header"):
	// hides on scroll down, reveals on scroll up. Only present at all when
	// $sticky was true at render time (each template file's own
	// omega-classic-header--sticky class + the spacer right after
	// </header> - see classic-header.css for why the spacer exists).
	function initStickyHeaders() {
		var headers = document.querySelectorAll('.omega-classic-header--sticky');
		if (!headers.length) return;

		headers.forEach(function (header) {
			var spacer = header.nextElementSibling;
			if (!spacer || !spacer.classList.contains('omega-classic-header__spacer')) {
				spacer = null;
			}

			function syncSpacerHeight() {
				if (spacer) spacer.style.height = header.offsetHeight + 'px';
			}
			syncSpacerHeight();
			window.addEventListener('resize', syncSpacerHeight);

			var lastY = window.scrollY;
			var ticking = false;

			function onScroll() {
				var y = window.scrollY;

				// Never hide while at the very top (nothing scrolled past
				// it yet to justify hiding) or while this header's own
				// mobile menu is open (hiding it out from under an open
				// menu would be jarring and would strand the close button).
				if (y <= header.offsetHeight || header.querySelector('.omega-classic-header__nav.is-open')) {
					header.classList.remove('is-header-hidden');
				} else if (y > lastY) {
					header.classList.add('is-header-hidden'); // scrolling down
				} else if (y < lastY) {
					header.classList.remove('is-header-hidden'); // scrolling up
				}

				// Independent of the show/hide state above - a small
				// elevation cue (classic-header.css's .is-scrolled) the
				// moment page content is actually sliding underneath it,
				// gone again the instant it's scrolled back to the top.
				header.classList.toggle('is-scrolled', y > 8);

				lastY = y;
				ticking = false;
			}

			window.addEventListener(
				'scroll',
				function () {
					if (!ticking) {
						requestAnimationFrame(onScroll);
						ticking = true;
					}
				},
				{ passive: true }
			);
		});
	}

	function init() {
		initMobileNavOffsets();
		initStickyHeaders();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
})();
