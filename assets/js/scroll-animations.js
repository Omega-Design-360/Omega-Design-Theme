/**
 * Front-end playback for the site-wide "Scroll Animation" block control -
 * every element carrying an .omega-animate class (stamped on by either the
 * editor's save() output or, for dynamic blocks, includes/core/
 * scroll_animations.php's render_block filter) starts hidden/offset via
 * assets/css/scroll-animations.css and reveals once it actually scrolls
 * into view.
 */
(function () {
	'use strict';

	var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function ready() {
		var all = document.querySelectorAll('.omega-animate');
		if (!all.length) {
			return;
		}

		var revealImmediately = prefersReducedMotion || !('IntersectionObserver' in window);
		var toObserve = [];

		for (var i = 0; i < all.length; i++) {
			var el = all[i];

			// The slider clips its slides with overflow:hidden on
			// .omega-slider__track so only the active slide shows - per the
			// IntersectionObserver spec that clipping ancestor makes any
			// slide's *content* (even the currently visible slide's) always
			// compute as non-intersecting, so it would otherwise sit
			// invisible forever. Only content nested INSIDE a slider is
			// affected - starting the search at the parent (not el itself)
			// means the slider block's own wrapper can still play a normal
			// scroll-reveal for the section as a whole.
			var insideSlider = el.parentElement && el.parentElement.closest('.omega-slider');
			if (revealImmediately || insideSlider) {
				el.classList.add('omega-animate--in');
				continue;
			}

			var duration = el.getAttribute('data-omega-animate-duration') || '600';
			var delay = el.getAttribute('data-omega-animate-delay') || '0';
			el.style.setProperty('--omega-animate-duration', duration + 'ms');
			el.style.setProperty('--omega-animate-delay', delay + 'ms');
			toObserve.push(el);
		}

		if (!toObserve.length) {
			return;
		}

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('omega-animate--in');
					observer.unobserve(entry.target);
				}
			});
		}, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

		for (var k = 0; k < toObserve.length; k++) {
			observer.observe(toObserve[k]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}
})();
