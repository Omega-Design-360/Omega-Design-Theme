/**
 * Front-end behaviour for the "Omega Slider" block (omega-design/slider).
 * Vanilla JS, no dependencies - reads its own settings from the data-*
 * attributes save() wrote onto .omega-slider, so every instance on the page
 * (hero slider, testimonial slider, etc.) can be configured independently
 * from the block inspector without touching this file.
 *
 * Two different positioning mechanisms, chosen by data-effect:
 *  - "slide" / "vertical": native scroll (track.scrollLeft/scrollTop +
 *    scrollTo()) with CSS scroll-snap - see style.css for why this replaced
 *    a JS-driven CSS transform (a real Chromium bug painted transformed
 *    content blank).
 *  - "fade" / "zoom" / "coverflow": slides are absolutely stacked in the
 *    same spot and repositioned with opacity/transform per slide. This
 *    never clips an overflowing transformed element behind overflow:hidden
 *    (the specific combination that triggered the bug above), so it's a
 *    structurally different, safe pattern rather than a workaround.
 */
(function () {
	'use strict';

	function buildChrome(root) {
		var slides = Array.prototype.slice.call(root.children);

		var track = document.createElement('div');
		track.className = 'omega-slider__track';
		slides.forEach(function (slide) { track.appendChild(slide); });
		root.appendChild(track);

		var prevBtn = null;
		var nextBtn = null;
		if (root.dataset.arrows === '1') {
			var arrowsWrap = document.createElement('div');
			arrowsWrap.className = 'omega-slider__arrows';

			prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'omega-slider__arrow omega-slider__arrow--prev';
			prevBtn.setAttribute('aria-label', root.dataset.prevLabel || 'Previous slide');

			nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'omega-slider__arrow omega-slider__arrow--next';
			nextBtn.setAttribute('aria-label', root.dataset.nextLabel || 'Next slide');

			arrowsWrap.appendChild(prevBtn);
			arrowsWrap.appendChild(nextBtn);
			root.appendChild(arrowsWrap);
		}

		var dotsWrap = null;
		var progressBar = null;
		var progressBarFill = null;
		if (root.dataset.progressBar === '1') {
			progressBar = document.createElement('div');
			progressBar.className = 'omega-slider__progressbar';
			progressBarFill = document.createElement('div');
			progressBarFill.className = 'omega-slider__progressbar-fill';
			progressBar.appendChild(progressBarFill);
			root.appendChild(progressBar);
		} else if (root.dataset.dots === '1') {
			dotsWrap = document.createElement('div');
			dotsWrap.className = 'omega-slider__dots';
			dotsWrap.setAttribute('role', 'tablist');
			dotsWrap.setAttribute('aria-label', root.dataset.dotsLabel || 'Slides');
			root.appendChild(dotsWrap);
		}

		var thumbsWrap = null;
		if (root.dataset.thumbnails === '1') {
			thumbsWrap = document.createElement('div');
			thumbsWrap.className = 'omega-slider__thumbnails';
			root.appendChild(thumbsWrap);
		}

		var peekCardsWrap = null;
		if (root.dataset.effect === 'coverflow') {
			peekCardsWrap = document.createElement('div');
			peekCardsWrap.className = 'omega-slider__peek-cards';
			root.appendChild(peekCardsWrap);
		}

		var cardRowWrap = null;
		if (root.dataset.effect === 'cards') {
			cardRowWrap = document.createElement('div');
			cardRowWrap.className = 'omega-slider__card-row';
			root.appendChild(cardRowWrap);
		}

		return {
			track: track, slides: slides, prevBtn: prevBtn, nextBtn: nextBtn,
			dotsWrap: dotsWrap, progressBar: progressBar, progressBarFill: progressBarFill,
			thumbsWrap: thumbsWrap, peekCardsWrap: peekCardsWrap, cardRowWrap: cardRowWrap
		};
	}

	/**
	 * omega-design/content-slider marks its text/button panel with
	 * data-slide-animation="fade-up" (etc.) - "arming" it here (rather than
	 * hiding it by default in CSS) means a slide's content is never stuck
	 * invisible if this script fails to load. Ordinary omega-design/slider
	 * instances have no such elements, so this is a no-op for them.
	 *
	 * A plain core block (core/column, used by e.g. the Fashion Store hero's
	 * text column) can't carry that data-slide-animation attribute in its
	 * OWN saved HTML, though - core/column's save() only ever emits the
	 * attributes it explicitly knows about, so a hand-added data-* one would
	 * never round-trip through the block editor and would flag that block
	 * for "attempt recovery" (confirmed: this is exactly what was
	 * happening). A plain className IS part of every block's normal,
	 * editor-supported schema, so `.omega-slide-animate` is safe to hand-
	 * author in a pattern's saved markup - this fills in the same
	 * data-slide-animation attribute purely at runtime instead, so
	 * everything below (the armed/play classes, the CSS keyed on the
	 * attribute) treats it exactly like a "real" one.
	 */
	function armSlideAnimations(slides) {
		for (var i = 0; i < slides.length; i++) {
			var classBased = slides[i].querySelectorAll('.omega-slide-animate:not([data-slide-animation])');
			for (var c = 0; c < classBased.length; c++) {
				classBased[c].setAttribute('data-slide-animation', 'fade-up');
			}

			var els = slides[i].querySelectorAll('[data-slide-animation]');
			for (var j = 0; j < els.length; j++) {
				els[j].classList.add('omega-content-slider--armed');
			}
		}
	}

	function findAnimatedEl(slide) {
		return slide ? slide.querySelector('[data-slide-animation]') : null;
	}

	function playAnimatedEl(el) {
		if (!el) {
			return;
		}
		el.classList.remove('omega-content-slider--play');
		void el.offsetWidth; // force reflow so the transition replays
		el.classList.add('omega-content-slider--play');
	}

	/**
	 * Once a slide has played, its --play class was never removed again -
	 * so looping back to it later found it already sitting at opacity:1
	 * from that stale state, and the "replay" (which only touches the class
	 * on the way IN) had nothing to visibly animate from. Called the moment
	 * a slide stops being current (immediately, no delay - it's scrolling
	 * out of view at the same time, so the reset itself is never seen), this
	 * puts it back in its armed/hidden starting state so the next time it
	 * becomes active it has something to fade in from again.
	 */
	function resetAnimatedEl(el) {
		if (el) {
			el.classList.remove('omega-content-slider--play');
		}
	}

	function playSlideAnimation(slides, slideIndex) {
		playAnimatedEl(findAnimatedEl(slides[slideIndex]));
	}

	function resetSlideAnimation(slides, slideIndex) {
		resetAnimatedEl(findAnimatedEl(slides[slideIndex]));
	}

	function init(root) {
		var chrome = buildChrome(root);
		var track = chrome.track;
		var slides = chrome.slides;
		var N = slides.length;

		armSlideAnimations(slides);

		if (N < 2) {
			if (chrome.prevBtn) { chrome.prevBtn.parentElement.style.display = 'none'; }
			if (chrome.dotsWrap) { chrome.dotsWrap.style.display = 'none'; }
			if (chrome.progressBar) { chrome.progressBar.style.display = 'none'; }
			if (chrome.thumbsWrap) { chrome.thumbsWrap.style.display = 'none'; }
			if (chrome.peekCardsWrap) { chrome.peekCardsWrap.style.display = 'none'; }
			if (chrome.cardRowWrap) { chrome.cardRowWrap.style.display = 'none'; }
			playSlideAnimation(slides, 0);
			return;
		}

		var autoplay = root.dataset.autoplay === '1';
		var autoplaySpeed = parseInt(root.dataset.autoplaySpeed, 10) || 6000;
		var loop = root.dataset.loop === '1';
		var gap = root.dataset.gap || '0px';
		var effect = root.dataset.effect || 'slide';
		var peek = root.dataset.peek === '1' && effect === 'slide';
		var cardsEffect = effect === 'cards';
		var stacked = effect === 'fade' || effect === 'zoom' || effect === 'coverflow' || cardsEffect;
		var vertical = effect === 'vertical';

		/*
		 * True infinite-scroll: clicking "next" past the last slide used to
		 * jump index straight to 0 and smooth-scroll there, which for a
		 * track already sitting at the far end meant scrolling BACKWARD
		 * across the whole thing - a visible "snap back" rather than a
		 * continuation. The standard fix (used by every major carousel
		 * library) is to keep one clone of the first slide appended after
		 * the last, and one clone of the last slide prepended before the
		 * first: a forward wrap can then scroll FORWARD into the trailing
		 * clone (indistinguishable from a real slide), then silently hop
		 * (no transition) onto the real slide it's a pixel-identical copy
		 * of once that scroll settles - invisible to the viewer, but leaves
		 * a clone available to scroll into again next time. Only safe to
		 * do with exactly one slide per view (spv>1 would need a clone
		 * block per breakpoint's worth of slides, not implemented), and
		 * only for the horizontal scroll effect - this theme's own sliders
		 * (hero, testimonials) are always single-slide/horizontal, so that
		 * covers real usage; anything else quietly falls back to the old
		 * wrap-to-zero behavior instead of guessing.
		 */
		var spvAlways1 = (parseInt(root.dataset.spv, 10) || 1) === 1
			&& (parseInt(root.dataset.spvTablet, 10) || 1) === 1
			&& (parseInt(root.dataset.spvMobile, 10) || 1) === 1;
		var infiniteLoop = loop && !stacked && !vertical && spvAlways1 && N > 1;
		var trackOffset = infiniteLoop ? 1 : 0;
		var firstClone = null;
		var lastClone = null;
		if (infiniteLoop) {
			firstClone = slides[0].cloneNode(true);
			lastClone = slides[N - 1].cloneNode(true);
			[firstClone, lastClone].forEach(function (clone) {
				clone.classList.add('omega-slider__clone');
				// aria-hidden alone leaves any link/button inside still
				// keyboard-focusable, which is an accessibility trap (focus
				// can land somewhere assistive tech was told doesn't exist) -
				// inert covers focus and pointer interaction too. Both are
				// set together since older browsers understand aria-hidden
				// without necessarily supporting inert yet.
				clone.setAttribute('aria-hidden', 'true');
				clone.setAttribute('inert', '');
			});
			track.insertBefore(lastClone, track.firstChild);
			track.appendChild(firstClone);
		}

		var dotsWrap = chrome.dotsWrap;
		var prevBtn = chrome.prevBtn;
		var nextBtn = chrome.nextBtn;
		var progressBar = chrome.progressBar;
		var progressBarFill = chrome.progressBarFill;
		var thumbsWrap = chrome.thumbsWrap;
		var peekCardsWrap = chrome.peekCardsWrap;
		var cardRowWrap = chrome.cardRowWrap;
		var PEEK_CARD_VISIBLE_COUNT = 3;
		var CARD_VISIBLE_COUNT = 3;
		var CARD_STEP = 125;
		var CARD_ROTATIONS = [-1, 1, 1.5];
		var flipBusy = false;

		var spv = 1;
		var timer = null;
		var index = 0;
		var isPointerDown = false;
		var pointerStartX = 0;
		var pointerStartY = 0;
		var pointerScrollStart = 0;

		root.classList.add('omega-slider--effect-' + effect);
		if (peek) { root.classList.add('omega-slider--peek'); }
		track.classList.add(stacked ? 'omega-slider__track--stacked' : 'omega-slider__track--scroll');
		if (vertical) { track.classList.add('omega-slider__track--vertical'); }

		function getSlidesPerView() {
			if (stacked || vertical) {
				return 1;
			}
			var w = window.innerWidth;
			if (w <= 600) {
				return Math.max(1, parseInt(root.dataset.spvMobile, 10) || 1);
			}
			if (w <= 900) {
				return Math.max(1, parseInt(root.dataset.spvTablet, 10) || 1);
			}
			return Math.max(1, parseInt(root.dataset.spv, 10) || 1);
		}

		function maxIndex() {
			return Math.max(0, N - spv);
		}

		function buildDots() {
			if (!dotsWrap) {
				return;
			}
			dotsWrap.innerHTML = '';
			var pages = maxIndex() + 1;
			for (var i = 0; i < pages; i++) {
				var dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'omega-slider__dot';
				dot.setAttribute('role', 'tab');
				dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
				(function (target) {
					dot.addEventListener('click', function () { goTo(target, true); });
				})(i);
				dotsWrap.appendChild(dot);
			}
			updateDots();
		}

		function buildThumbnails() {
			if (!thumbsWrap) {
				return;
			}
			thumbsWrap.innerHTML = '';
			slides.forEach(function (slide, i) {
				var thumb = document.createElement('button');
				thumb.type = 'button';
				thumb.className = 'omega-slider__thumbnail';
				thumb.setAttribute('aria-label', 'Go to slide ' + (i + 1));

				var img = slide.querySelector('img');
				if (img && img.src) {
					var thumbImg = document.createElement('img');
					thumbImg.src = img.src;
					thumbImg.alt = '';
					thumb.appendChild(thumbImg);
				} else {
					thumb.textContent = String(i + 1);
				}

				thumb.addEventListener('click', function () { goTo(i, true); });
				thumbsWrap.appendChild(thumb);
			});
			updateThumbnails();
		}

		function updateDots() {
			if (!dotsWrap) {
				return;
			}
			var dots = dotsWrap.children;
			for (var i = 0; i < dots.length; i++) {
				dots[i].classList.toggle('is-active', i === index);
				dots[i].setAttribute('aria-selected', i === index ? 'true' : 'false');
			}
		}

		function updateThumbnails() {
			if (!thumbsWrap) {
				return;
			}
			var thumbs = thumbsWrap.children;
			for (var i = 0; i < thumbs.length; i++) {
				thumbs[i].classList.toggle('is-active', i === index);
			}
		}

		/**
		 * "Coverflow" is really the destination-style card stack: the
		 * active slide fills the whole slider full-bleed (handled by
		 * positionStacked() below), and this builds a separate strip of
		 * small portrait cards - one per UPCOMING slide - stacked with a
		 * staggered offset on top of it, each clickable to jump straight
		 * to that slide.
		 */
		function buildPeekCards() {
			if (!peekCardsWrap) {
				return;
			}
			peekCardsWrap.innerHTML = '';
			slides.forEach(function (slide, i) {
				var card = document.createElement('button');
				card.type = 'button';
				card.className = 'omega-slider__peek-card';
				card.setAttribute('aria-label', 'Go to slide ' + (i + 1));

				var img = slide.querySelector('img');
				if (img && img.src) {
					var cardImg = document.createElement('img');
					cardImg.src = img.src;
					cardImg.alt = '';
					card.appendChild(cardImg);
				}

				var heading = slide.querySelector('h1, h2, h3, h4, h5, h6');
				if (heading && heading.textContent) {
					var label = document.createElement('span');
					label.className = 'omega-slider__peek-card-label';
					label.textContent = heading.textContent;
					card.appendChild(label);
				}

				card.addEventListener('click', function () { goTo(i, true); });
				peekCardsWrap.appendChild(card);
			});
			updatePeekCards();
		}

		function updatePeekCards() {
			if (!peekCardsWrap) {
				return;
			}
			var cards = peekCardsWrap.children;
			for (var i = 0; i < cards.length; i++) {
				var card = cards[i];
				var distance = i - index;
				if (loop && distance <= 0) {
					distance += N;
				}
				if (distance >= 1 && distance <= PEEK_CARD_VISIBLE_COUNT) {
					card.classList.add('is-visible');
					card.style.setProperty('--omega-peek-distance', String(distance));
					card.style.zIndex = String(PEEK_CARD_VISIBLE_COUNT - distance + 1);
				} else {
					card.classList.remove('is-visible');
					card.style.removeProperty('--omega-peek-distance');
					card.style.zIndex = '';
				}
			}
		}

		/**
		 * "Cards" effect: a full-bleed hero photo with a horizontal row of
		 * up to 3 upcoming-slide preview cards - clicking one "expands" it
		 * into the new hero image via a cloned element animated from the
		 * card's own screen position/size to the hero's (a FLIP transition:
		 * measure First, apply Last state, Invert with a transform/rect
		 * jump, then let it Play via a transition), rather than a plain
		 * cross-fade. next()/prev() reuse the same transition - next()
		 * targets whichever slide the first card represents, prev() runs
		 * it in reverse (the current hero shrinks back into a card).
		 */
		function heroBgEl(slide) {
			return slide.querySelector('.omega-content-slider__slide-bg');
		}

		function heroImageSrc(slide) {
			var bgEl = heroBgEl(slide);
			if (bgEl) {
				var raw = bgEl.style.backgroundImage || getComputedStyle(bgEl).backgroundImage;
				var m = /url\((['"]?)(.*?)\1\)/.exec(raw || '');
				if (m && m[2]) {
					return m[2];
				}
			}
			var img = slide.querySelector('img');
			return img ? img.src : '';
		}

		function heroRectEl(slide) {
			return heroBgEl(slide) || slide.querySelector('img') || slide;
		}

		function slideTitleText(slide) {
			var h = slide.querySelector('h1, h2, h3, h4, h5, h6');
			return h && h.textContent ? h.textContent : '';
		}

		function makeCard(idx, position) {
			var card = document.createElement('button');
			card.type = 'button';
			card.className = 'omega-slider__card';
			card.dataset.index = String(idx);
			card.style.left = (position * CARD_STEP) + 'px';
			card.style.transform = 'rotate(' + CARD_ROTATIONS[position] + 'deg)';

			var src = heroImageSrc(slides[idx]);
			if (src) {
				var img = document.createElement('img');
				img.src = src;
				img.alt = '';
				card.appendChild(img);
			}

			var title = slideTitleText(slides[idx]);
			if (title) {
				var label = document.createElement('b');
				label.className = 'omega-slider__card-label';
				label.textContent = title;
				card.appendChild(label);
			}

			card.addEventListener('click', function () { goTo(idx, true); });
			return card;
		}

		function buildCardRow() {
			if (!cardRowWrap) {
				return;
			}
			cardRowWrap.innerHTML = '';
			var count = Math.min(CARD_VISIBLE_COUNT, N - 1);
			for (var n = 1; n <= count; n++) {
				cardRowWrap.appendChild(makeCard((index + n) % N, n - 1));
			}
		}

		function findCardForIndex(target) {
			if (!cardRowWrap) {
				return null;
			}
			var cards = cardRowWrap.children;
			for (var i = 0; i < cards.length; i++) {
				if (parseInt(cards[i].dataset.index, 10) === target) {
					return cards[i];
				}
			}
			return null;
		}

		function finishFlip(target) {
			slides[index].classList.remove('is-active');
			slides[target].classList.remove('is-target-cards');
			slides[target].style.opacity = '';
			slides[target].style.visibility = '';
			slides[target].classList.add('is-active');
			index = target;

			buildCardRow();
			updateDots();
			updateThumbnails();
			playSlideAnimation(slides, index);
			if (prevBtn) { prevBtn.disabled = !loop && index === 0; }
			if (nextBtn) { nextBtn.disabled = !loop && index === maxIndex(); }

			flipBusy = false;
			restartAutoplay();
		}

		function expandToSlide(target, originCard) {
			if (flipBusy || target === index) {
				return;
			}
			flipBusy = true;
			stopAutoplay();

			if (originCard) {
				originCard.classList.add('omega-slider__card--hide');
			}

			// Positioned/appended relative to the TRACK (not root) since
			// track is what actually clips overflow for stacked effects -
			// root has no overflow:hidden, so a clone mid-transition (its
			// rect somewhere between a small card and the full hero) would
			// otherwise visibly spill past the slider's own edges.
			var rootRect = track.getBoundingClientRect();
			var originRect = originCard ? originCard.getBoundingClientRect() : rootRect;
			var targetRect = heroRectEl(slides[target]).getBoundingClientRect();

			slides[target].classList.add('is-target-cards');
			slides[target].style.opacity = '1';
			slides[target].style.visibility = 'visible';

			var clone = document.createElement('div');
			clone.className = 'omega-slider__expander';
			var cloneImg = document.createElement('img');
			cloneImg.src = heroImageSrc(slides[target]);
			clone.appendChild(cloneImg);
			track.appendChild(clone);

			clone.style.left = (originRect.left - rootRect.left) + 'px';
			clone.style.top = (originRect.top - rootRect.top) + 'px';
			clone.style.width = originRect.width + 'px';
			clone.style.height = originRect.height + 'px';
			clone.style.borderRadius = originCard ? getComputedStyle(originCard).borderRadius : '0px';
			clone.getBoundingClientRect();

			var cards = cardRowWrap ? Array.prototype.slice.call(cardRowWrap.children) : [];
			if (cards[0]) {
				cards[0].style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), opacity 350ms ease 500ms';
				cards[0].style.left = (-CARD_STEP) + 'px';
				cards[0].classList.add('omega-slider__card--hide');
			}
			for (var i = 1; i < cards.length; i++) {
				cards[i].style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), transform 850ms cubic-bezier(.22,.61,.36,1)';
				cards[i].style.left = ((i - 1) * CARD_STEP) + 'px';
				cards[i].style.transform = 'rotate(' + CARD_ROTATIONS[i - 1] + 'deg)';
			}

			var incoming = null;
			if (N > CARD_VISIBLE_COUNT) {
				incoming = makeCard((target + CARD_VISIBLE_COUNT) % N, CARD_VISIBLE_COUNT - 1);
				incoming.style.left = (CARD_VISIBLE_COUNT * CARD_STEP) + 'px';
				incoming.style.opacity = '0';
				if (cardRowWrap) { cardRowWrap.appendChild(incoming); }
				incoming.getBoundingClientRect();
			}

			requestAnimationFrame(function () {
				clone.style.transition = 'left 950ms cubic-bezier(.16,1,.3,1), top 950ms cubic-bezier(.16,1,.3,1), width 950ms cubic-bezier(.16,1,.3,1), height 950ms cubic-bezier(.16,1,.3,1), border-radius 950ms cubic-bezier(.16,1,.3,1)';
				clone.style.left = (targetRect.left - rootRect.left) + 'px';
				clone.style.top = (targetRect.top - rootRect.top) + 'px';
				clone.style.width = targetRect.width + 'px';
				clone.style.height = targetRect.height + 'px';
				clone.style.borderRadius = '0px';

				if (incoming) {
					incoming.style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), opacity 600ms ease 150ms';
					incoming.style.left = ((CARD_VISIBLE_COUNT - 1) * CARD_STEP) + 'px';
					incoming.style.opacity = '1';
				}
			});

			setTimeout(function () {
				clone.remove();
				finishFlip(target);
			}, 1000);
		}

		function shrinkToSlide(target) {
			if (flipBusy || target === index) {
				return;
			}
			flipBusy = true;
			stopAutoplay();

			// Positioned/appended relative to the TRACK (not root) since
			// track is what actually clips overflow for stacked effects -
			// root has no overflow:hidden, so a clone mid-transition (its
			// rect somewhere between a small card and the full hero) would
			// otherwise visibly spill past the slider's own edges.
			var rootRect = track.getBoundingClientRect();
			var currentRect = heroRectEl(slides[index]).getBoundingClientRect();

			slides[target].classList.add('is-target-cards');
			slides[target].style.opacity = '1';
			slides[target].style.visibility = 'visible';

			// Measure where slot 0 of the card row would sit, without
			// permanently inserting a real card there yet.
			var temp = makeCard(target, 0);
			temp.style.visibility = 'hidden';
			if (cardRowWrap) { cardRowWrap.appendChild(temp); }
			var destRect = temp.getBoundingClientRect();
			var destRadius = getComputedStyle(temp).borderRadius;
			if (cardRowWrap) { cardRowWrap.removeChild(temp); }

			var clone = document.createElement('div');
			clone.className = 'omega-slider__expander';
			var cloneImg = document.createElement('img');
			cloneImg.src = heroImageSrc(slides[index]);
			clone.appendChild(cloneImg);
			track.appendChild(clone);

			clone.style.left = (currentRect.left - rootRect.left) + 'px';
			clone.style.top = (currentRect.top - rootRect.top) + 'px';
			clone.style.width = currentRect.width + 'px';
			clone.style.height = currentRect.height + 'px';
			clone.style.borderRadius = '0px';
			clone.getBoundingClientRect();

			var cards = cardRowWrap ? Array.prototype.slice.call(cardRowWrap.children) : [];
			var lastIdx = cards.length - 1;
			cards.forEach(function (card, i) {
				if (i === lastIdx) {
					card.style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), opacity 350ms ease 500ms';
					card.style.left = (CARD_VISIBLE_COUNT * CARD_STEP) + 'px';
					card.classList.add('omega-slider__card--hide');
				} else {
					card.style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), transform 850ms cubic-bezier(.22,.61,.36,1)';
					card.style.left = ((i + 1) * CARD_STEP) + 'px';
					card.style.transform = 'rotate(' + CARD_ROTATIONS[i + 1] + 'deg)';
				}
			});

			var incoming = makeCard(target, 0);
			incoming.style.left = (-CARD_STEP) + 'px';
			incoming.style.opacity = '0';
			if (cardRowWrap) { cardRowWrap.appendChild(incoming); }
			incoming.getBoundingClientRect();

			requestAnimationFrame(function () {
				clone.style.transition = 'left 950ms cubic-bezier(.7,0,.84,.15), top 950ms cubic-bezier(.7,0,.84,.15), width 950ms cubic-bezier(.7,0,.84,.15), height 950ms cubic-bezier(.7,0,.84,.15), border-radius 950ms cubic-bezier(.7,0,.84,.15)';
				clone.style.left = (destRect.left - rootRect.left) + 'px';
				clone.style.top = (destRect.top - rootRect.top) + 'px';
				clone.style.width = destRect.width + 'px';
				clone.style.height = destRect.height + 'px';
				clone.style.borderRadius = destRadius;

				incoming.style.transition = 'left 850ms cubic-bezier(.22,.61,.36,1), opacity 600ms ease 150ms';
				incoming.style.left = '0px';
				incoming.style.opacity = '1';
			});

			setTimeout(function () {
				clone.remove();
				finishFlip(target);
			}, 1000);
		}

		function goToCards(target) {
			var originCard = findCardForIndex(target);
			if (originCard) {
				expandToSlide(target, originCard);
			} else {
				shrinkToSlide(target);
			}
		}

		function slideSize() {
			return vertical ? track.clientHeight / spv : track.clientWidth / spv;
		}

		/**
		 * fade/zoom/coverflow all position every slide absolutely in the
		 * same spot - the track needs an explicit height since absolutely
		 * positioned children don't otherwise contribute to it.
		 */
		function syncStackedHeight() {
			if (!stacked) {
				return;
			}
			var tallest = 0;
			slides.forEach(function (slide) {
				tallest = Math.max(tallest, slide.scrollHeight);
			});
			track.style.height = tallest + 'px';
		}

		/*
		 * overridePx lets the infinite-loop wrap sequence in goTo() scroll to
		 * a raw track pixel position (a clone, sitting just past the real
		 * track) instead of the current index's own position - everything
		 * else just omits it and gets the normal (index + trackOffset)
		 * position, trackOffset being 1 whenever the leading clone-of-last
		 * exists and 0 otherwise.
		 */
		function positionScroll(withTransition, overridePx) {
			var px = (typeof overridePx === 'number') ? overridePx : (index + trackOffset) * slideSize();
			var left = vertical ? 0 : px;
			var top = vertical ? px : 0;
			if (withTransition && 'scrollBehavior' in document.documentElement.style) {
				track.scrollTo({ left: left, top: top, behavior: 'smooth' });
			} else {
				track.scrollLeft = left;
				track.scrollTop = top;
			}
		}

		function positionStacked() {
			slides.forEach(function (slide, i) {
				slide.classList.remove('is-active', 'is-prev', 'is-next');
				if (i === index) {
					slide.classList.add('is-active');
				} else if (i === index - 1 || (loop && index === 0 && i === N - 1)) {
					slide.classList.add('is-prev');
				} else if (i === index + 1 || (loop && index === N - 1 && i === 0)) {
					slide.classList.add('is-next');
				}
			});
		}

		/*
		 * For a "slide" transition, the incoming text was fading in AT THE
		 * SAME TIME the track was still smoothly scrolling it into place -
		 * both motions run roughly 400-600ms, so by the time the slide
		 * actually arrived the fade was already finished, reading as "no
		 * animation" on every navigation after the first (the initial load
		 * has no competing scroll, so it alone looked fine). Delaying the
		 * animation until just after the scroll settles makes the two read
		 * as sequential instead of racing each other. A pending delay is
		 * cancelled on every call so rapid next()/prev() clicks never queue
		 * up stale replays on slides that aren't current anymore.
		 */
		var slideAnimationTimer = null;
		var loopSettleTimer = null;
		var previousAnimatedIndex = 0;

		function updateChromeForIndex() {
			updateDots();
			updateThumbnails();
			updatePeekCards();
			if (prevBtn) { prevBtn.disabled = !loop && index === 0; }
			if (nextBtn) { nextBtn.disabled = !loop && index === maxIndex(); }
		}

		function render(withTransition) {
			if (stacked) {
				positionStacked();
			} else {
				positionScroll(withTransition);
			}
			updateChromeForIndex();
			clearTimeout(slideAnimationTimer);
			clearTimeout(loopSettleTimer);
			if (previousAnimatedIndex !== index) {
				resetSlideAnimation(slides, previousAnimatedIndex);
			}
			if (withTransition && !stacked) {
				slideAnimationTimer = setTimeout(function () {
					playSlideAnimation(slides, index);
				}, 400);
			} else {
				playSlideAnimation(slides, index);
			}
			previousAnimatedIndex = index;
		}

		function goTo(target, userInitiated) {
			var last = maxIndex();
			var wrapForward = false;
			var wrapBackward = false;

			if (loop) {
				if (target > last) {
					wrapForward = true;
					target = 0;
				} else if (target < 0) {
					wrapBackward = true;
					target = last;
				}
			} else {
				target = Math.max(0, Math.min(target, last));
			}

			if (cardsEffect) {
				// finishFlip() restarts autoplay itself once the transition
				// actually completes (~1s later) - restarting it here too
				// would reset the progress bar mid-flight.
				if (target !== index && !flipBusy) {
					goToCards(target);
				}
				return;
			}

			/*
			 * Wrapping past either end: scroll FORWARD into the trailing
			 * clone (or backward into the leading one) instead of jumping
			 * straight to the real slide's own position, which for e.g. the
			 * last-to-first case would mean scrolling backward across the
			 * entire track - the "snap back" this whole thing replaces. The
			 * clone plays the same delayed fade-in a real incoming slide
			 * would (so it still reads as "the next slide arriving", not a
			 * static jump-cut); once that's had time to finish, an instant,
			 * transition-less hop lands on the real slide it's a pixel-
			 * identical copy of - invisible to the viewer, but leaves the
			 * clone free to scroll into again next time.
			 */
			if (infiniteLoop && (wrapForward || wrapBackward)) {
				clearTimeout(slideAnimationTimer);
				clearTimeout(loopSettleTimer);

				var cloneEl = wrapForward ? firstClone : lastClone;
				var cloneAnimEl = findAnimatedEl(cloneEl);

				if (previousAnimatedIndex !== target) {
					resetSlideAnimation(slides, previousAnimatedIndex);
				}
				resetAnimatedEl(cloneAnimEl);

				/*
				 * The native-scroll sync listener normally reclaims control
				 * after its own 500ms suppression window (matching a plain
				 * one-slide transition) - too short for this whole clone-
				 * scroll-then-settle sequence (1050ms below), so left alone
				 * it would fire mid-sequence with its own, less careful
				 * "landed on a clone" handling and fight this one for the
				 * same element.
				 */
				positionScroll(true, wrapForward ? (N + trackOffset) * slideSize() : 0, 1150);
				index = target;
				updateChromeForIndex();

				slideAnimationTimer = setTimeout(function () {
					playAnimatedEl(cloneAnimEl);
				}, 400);

				loopSettleTimer = setTimeout(function () {
					positionScroll(false);
					var realAnimEl = findAnimatedEl(slides[index]);
					if (realAnimEl) {
						/*
						 * Already faded in via the clone above, so this needs
						 * to land at opacity:1 with no animation of its own -
						 * but the element still has the transition CSS from
						 * being --armed, so a plain classList.add() here
						 * doesn't skip it, it just starts ANOTHER 600ms fade
						 * from 0 at the exact moment the (now fully visible)
						 * clone gets swapped out, which reads as a visible
						 * dip right at the handoff. Turning the transition
						 * off, forcing the opacity/transform to jump straight
						 * to their end state, then turning it back on is what
						 * actually makes this instant.
						 */
						realAnimEl.style.transition = 'none';
						realAnimEl.classList.add('omega-content-slider--play');
						void realAnimEl.offsetWidth;
						realAnimEl.style.transition = '';
					}
					resetAnimatedEl(cloneAnimEl);
					previousAnimatedIndex = index;
				}, 1050);

				if (userInitiated) {
					restartAutoplay();
				}
				return;
			}

			index = target;
			render(true);

			if (userInitiated) {
				restartAutoplay();
			}
		}

		function next() { goTo(index + 1); }
		function prev() { goTo(index - 1); }

		function startProgressBar() {
			if (!progressBarFill || !autoplay) {
				return;
			}
			progressBarFill.style.transition = 'none';
			progressBarFill.style.width = '0%';
			void progressBarFill.offsetWidth;
			progressBarFill.style.transition = 'width ' + autoplaySpeed + 'ms linear';
			progressBarFill.style.width = '100%';
		}

		function restartAutoplay() {
			// Several call sites (arrow clicks, keyboard, swipe) call this
			// immediately after triggering navigation - for the FLIP-based
			// "cards" effect that fires too early (finishFlip() already
			// calls this itself once the ~1s transition actually
			// completes), so skip it here and let that later call win.
			if (!autoplay || flipBusy) {
				return;
			}
			if (timer) {
				clearInterval(timer);
			}
			startProgressBar();
			timer = setInterval(function () {
				next();
				startProgressBar();
			}, autoplaySpeed);
		}

		function stopAutoplay() {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
			if (progressBarFill) {
				progressBarFill.style.transition = 'none';
			}
		}

		function layout() {
			var newSpv = getSlidesPerView();
			spv = newSpv;

			if (!stacked) {
				// The two clones need the exact same sizing as every real
				// slide, or the track's per-slide pixel width (slideSize())
				// stops matching what's actually on screen at their position.
				var sizedSlides = infiniteLoop ? [lastClone].concat(slides, [firstClone]) : slides;
				sizedSlides.forEach(function (slide) {
					if (vertical) {
						slide.style.flex = '0 0 calc(' + (100 / spv) + '% - ' + gap + ')';
						slide.style.marginBottom = gap;
					} else {
						slide.style.flex = '0 0 calc(' + (100 / spv) + '% - ' + gap + ')';
						slide.style.marginRight = gap;
					}
				});
			}

			syncStackedHeight();
			index = Math.max(0, Math.min(index, maxIndex()));
			buildDots();
			render(false);
		}

		if (prevBtn) { prevBtn.addEventListener('click', function () { prev(); restartAutoplay(); }); }
		if (nextBtn) { nextBtn.addEventListener('click', function () { next(); restartAutoplay(); }); }

		root.addEventListener('mouseenter', stopAutoplay);
		root.addEventListener('mouseleave', restartAutoplay);
		root.addEventListener('focusin', stopAutoplay);
		root.addEventListener('focusout', restartAutoplay);

		root.setAttribute('tabindex', '0');
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') { prev(); restartAutoplay(); }
			if (e.key === 'ArrowRight') { next(); restartAutoplay(); }
		});

		// Native-scroll effects (slide/vertical) stay in sync with the
		// visitor's own touch swipe / trackpad scroll, which moves
		// scrollLeft/scrollTop directly without going through goTo().
		if (!stacked) {
			var scrollSyncTimer = null;
			var isSyncingFromCode = false;
			var syncFromCodeTimer = null;
			var originalRenderScroll = positionScroll;
			positionScroll = function (withTransition, overridePx, suppressMs) {
				isSyncingFromCode = true;
				clearTimeout(syncFromCodeTimer);
				var duration = (typeof suppressMs === 'number') ? suppressMs : (withTransition ? 500 : 50);
				syncFromCodeTimer = setTimeout(function () { isSyncingFromCode = false; }, duration);
				originalRenderScroll(withTransition, overridePx);
			};

			track.addEventListener('scroll', function () {
				if (isSyncingFromCode) {
					return;
				}
				clearTimeout(scrollSyncTimer);
				scrollSyncTimer = setTimeout(function () {
					var size = slideSize();
					if (size <= 0) {
						return;
					}
					var pos = vertical ? track.scrollTop : track.scrollLeft;
					var rawTrackIndex = Math.round(pos / size);
					// A native swipe/trackpad scroll can land directly on a
					// clone (there's nothing stopping it - the clone is a
					// real, scrollable track child) - re-home onto the real
					// slide it's a pixel-identical copy of so there's clone
					// left to scroll into again next time.
					var onClone = infiniteLoop && (rawTrackIndex === 0 || rawTrackIndex === N + trackOffset);
					var newIndex = onClone
						? (rawTrackIndex === 0 ? N - 1 : 0)
						: Math.max(0, Math.min(rawTrackIndex - trackOffset, maxIndex()));
					if (previousAnimatedIndex !== newIndex) {
						resetSlideAnimation(slides, previousAnimatedIndex);
					}
					index = newIndex;
					if (onClone) {
						positionScroll(false);
					}
					updateDots();
					updateThumbnails();
					playSlideAnimation(slides, index);
					previousAnimatedIndex = index;
				}, 80);
			});

			track.addEventListener('pointerdown', function (e) {
				if (e.pointerType === 'touch') {
					return;
				}
				isPointerDown = true;
				pointerStartX = e.clientX;
				pointerStartY = e.clientY;
				pointerScrollStart = vertical ? track.scrollTop : track.scrollLeft;
				track.style.scrollSnapType = 'none';
				stopAutoplay();
			});
			track.addEventListener('pointermove', function (e) {
				if (!isPointerDown) {
					return;
				}
				if (vertical) {
					track.scrollTop = pointerScrollStart - (e.clientY - pointerStartY);
				} else {
					track.scrollLeft = pointerScrollStart - (e.clientX - pointerStartX);
				}
			});
			function endDrag() {
				if (!isPointerDown) {
					return;
				}
				isPointerDown = false;
				track.style.scrollSnapType = '';
				var size = slideSize();
				if (size > 0) {
					var pos = vertical ? track.scrollTop : track.scrollLeft;
					var rawTrackIndex = Math.round(pos / size);
					if (infiniteLoop && (rawTrackIndex === 0 || rawTrackIndex === N + trackOffset)) {
						// Dragged past the real track onto a clone - silently
						// re-home before render(true) below, which will then
						// just "scroll" to the position it's already at.
						index = rawTrackIndex === 0 ? N - 1 : 0;
						positionScroll(false);
					} else {
						index = Math.max(0, Math.min(rawTrackIndex - trackOffset, maxIndex()));
					}
				}
				render(true);
				restartAutoplay();
			}
			track.addEventListener('pointerup', endDrag);
			track.addEventListener('pointerleave', endDrag);
			track.addEventListener('pointercancel', endDrag);
		} else {
			// Stacked effects (fade/zoom/coverflow): a simple horizontal
			// swipe gesture on touch/mouse, since there's no native scroll
			// position to read from.
			var dragDeltaX = 0;
			track.addEventListener('pointerdown', function (e) {
				isPointerDown = true;
				pointerStartX = e.clientX;
				dragDeltaX = 0;
				stopAutoplay();
			});
			track.addEventListener('pointermove', function (e) {
				if (!isPointerDown) {
					return;
				}
				dragDeltaX = e.clientX - pointerStartX;
			});
			function endStackedDrag() {
				if (!isPointerDown) {
					return;
				}
				isPointerDown = false;
				var threshold = 40;
				if (dragDeltaX > threshold) {
					prev();
				} else if (dragDeltaX < -threshold) {
					next();
				}
				restartAutoplay();
			}
			track.addEventListener('pointerup', endStackedDrag);
			track.addEventListener('pointerleave', endStackedDrag);
			track.addEventListener('pointercancel', endStackedDrag);
		}

		buildThumbnails();
		buildPeekCards();
		buildCardRow();

		window.addEventListener('resize', layout);

		layout();
		restartAutoplay();
	}

	function ready() {
		var sliders = document.querySelectorAll('.omega-slider');
		for (var i = 0; i < sliders.length; i++) {
			init(sliders[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}
})();
