(function () {
	'use strict';

	function slugify(text) {
		return text.toLowerCase().trim()
			.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
			.replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-') || 'section';
	}

	document.querySelectorAll('[data-omega-post-toc]').forEach(function (toc) {
		var main = toc.closest('.omega-sidebar-layout__aside');
		var layout = main && main.closest('.omega-sidebar-layout');
		var content = layout && layout.querySelector('.omega-sidebar-layout__main .wp-block-post-content');
		var list = toc.querySelector('.omega-post-toc__list');
		var tags = (toc.getAttribute('data-heading-tags') || 'h2,h3').split(',').map(function (tag) { return tag.trim(); });
		if (!content || !list) {
			toc.hidden = true;
			return;
		}

		var headings = Array.prototype.slice.call(content.querySelectorAll(tags.join(',')))
			.filter(function (heading) {
				return heading.textContent.trim() !== '' && !heading.classList.contains('omega-toc-exclude');
			});
		var used = Object.create(null);

		headings.forEach(function (heading) {
			var id = heading.id || slugify(heading.textContent);
			var base = id;
			var suffix = 2;
			while (used[id] || (document.getElementById(id) && document.getElementById(id) !== heading)) {
				id = base + '-' + suffix++;
			}
			used[id] = true;
			heading.id = id;
			heading.style.scrollMarginTop = '24px';

			var item = document.createElement('li');
			if (/^h[3-6]$/.test(heading.tagName.toLowerCase())) item.className = 'omega-post-toc__item--nested';
			var link = document.createElement('a');
			link.href = '#' + encodeURIComponent(id);
			var checkIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
			checkIcon.setAttribute('viewBox', '0 -960 960 960');
			checkIcon.setAttribute('width', '17');
			checkIcon.setAttribute('height', '17');
			checkIcon.setAttribute('fill', 'currentColor');
			checkIcon.setAttribute('aria-hidden', 'true');
			var checkPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
			checkPath.setAttribute('d', 'm424-312 282-282-56-56-226 226-114-114-56 56 170 170ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Z');
			checkIcon.appendChild(checkPath);
			var label = document.createElement('span');
			label.textContent = heading.textContent.trim();
			link.appendChild(checkIcon);
			link.appendChild(label);
			item.appendChild(link);
			list.appendChild(item);
		});

		if (!headings.length) {
			toc.hidden = true;
		} else {
			document.documentElement.classList.add('omega-has-post-toc');
		}
	});

	document.addEventListener('click', function (event) {
		var clickedElement = event.target.nodeType === 1 ? event.target : event.target.parentElement;
		var link = clickedElement && clickedElement.closest('.omega-post-toc__list a[href^="#"]');
		if (!link) return;

		var id;
		try {
			id = decodeURIComponent(link.getAttribute('href').slice(1));
		} catch (error) {
			return;
		}
		var target = document.getElementById(id);
		if (!target) return;

		event.preventDefault();
		var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var startY = window.pageYOffset;
		var endY = target.getBoundingClientRect().top + startY - 24;
		var distance = endY - startY;
		if (reducedMotion || Math.abs(distance) < 2) {
			window.scrollTo(0, endY);
		} else {
			var startTime = null;
			var duration = Math.min(850, Math.max(400, Math.abs(distance) * 0.45));
			function animateScroll(timestamp) {
				if (startTime === null) startTime = timestamp;
				var progress = Math.min(1, (timestamp - startTime) / duration);
				var eased = progress < 0.5
					? 4 * progress * progress * progress
					: 1 - Math.pow(-2 * progress + 2, 3) / 2;
				window.scrollTo(0, startY + distance * eased);
				if (progress < 1) window.requestAnimationFrame(animateScroll);
			}
			window.requestAnimationFrame(animateScroll);
		}
		if (window.history && window.history.pushState) {
			window.history.pushState(null, '', '#' + encodeURIComponent(id));
		}
	});
})();
