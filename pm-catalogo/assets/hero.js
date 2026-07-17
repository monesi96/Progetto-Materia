/**
 * Parallax scroll-driven dell'hero Progetto Materia.
 * Zero dipendenze: rAF + transform sui piani [data-pm-depth],
 * IntersectionObserver per l'ingresso sfalsato delle card.
 */
(function () {
	'use strict';

	var hero = document.querySelector('.pm-hero');
	if (!hero) return;

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* Su mobile il video pesa: play solo se la connessione lo consente. */
	var video = hero.querySelector('video.pm-hero__video');
	if (video) {
		var conn = navigator.connection || {};
		var slow = conn.saveData || /(^|\b)(slow-2g|2g|3g)\b/.test(conn.effectiveType || '');
		if (reduced || slow) {
			video.removeAttribute('autoplay');
			video.pause();
		}
	}

	/* ---- parallax sui piani ---- */
	if (!reduced) {
		var layers = [].slice.call(hero.querySelectorAll('[data-pm-depth]'));
		var ticking = false;

		var update = function () {
			ticking = false;
			var rect = hero.getBoundingClientRect();
			/* progress: 0 quando l'hero è a inizio viewport, 1 quando è scrollato via */
			var progress = Math.min(1, Math.max(0, -rect.top / Math.max(1, rect.height)));
			layers.forEach(function (el) {
				var depth = parseFloat(el.getAttribute('data-pm-depth')) || 0;
				var shift = progress * depth * rect.height;
				el.style.transform = 'translate3d(0,' + shift.toFixed(1) + 'px,0)';
			});
		};

		var onScroll = function () {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(update);
			}
		};

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		update();
	}

	/* ---- ingresso sfalsato delle card ---- */
	var cards = [].slice.call(hero.querySelectorAll('[data-pm-card]'));
	if ('IntersectionObserver' in window && cards.length) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				var el = entry.target;
				var i = parseInt(el.getAttribute('data-pm-card'), 10) || 0;
				setTimeout(function () { el.classList.add('is-in'); }, reduced ? 0 : i * 110);
				io.unobserve(el);
			});
		}, { threshold: 0.2 });
		cards.forEach(function (c) { io.observe(c); });
	} else {
		cards.forEach(function (c) { c.classList.add('is-in'); });
	}
})();
