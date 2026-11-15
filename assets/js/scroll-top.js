(function () {
	'use strict';

	var button = document.querySelector('.omega-scroll-top');
	if (!button) return;

	var ticking = false;
	var showAfter = 240;

	function update() {
		var doc = document.documentElement;
		var maxScroll = Math.max(0, doc.scrollHeight - window.innerHeight);
		var progress = maxScroll > 0 ? Math.min(1, Math.max(0, window.scrollY / maxScroll)) : 0;
		var visible = window.scrollY > showAfter;

		button.style.setProperty('--omega-scroll-progress', String(progress));
		button.classList.toggle('is-visible', visible);
		button.setAttribute('aria-hidden', visible ? 'false' : 'true');
		button.tabIndex = visible ? 0 : -1;
		ticking = false;
	}

	function requestUpdate() {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(update);
		}
	}

	window.addEventListener('scroll', requestUpdate, { passive: true });
	window.addEventListener('resize', requestUpdate, { passive: true });
	window.addEventListener('load', requestUpdate, { once: true });
	button.addEventListener('click', function () {
		window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
	});
	update();
})();
