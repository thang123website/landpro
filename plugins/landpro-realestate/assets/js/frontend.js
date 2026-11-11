/* LandPro Real Estate – lightbox ảnh, video YouTube tải khi bấm. Không phụ thuộc jQuery. */
(function () {
	'use strict';

	/* ------------------------------------------------------------ Lightbox
	 * Bộ đếm, thanh công cụ (phóng to, trình chiếu, ảnh nhỏ, toàn màn hình, đóng),
	 * mũi tên, dải ảnh nhỏ, phím tắt, vuốt trên điện thoại. */
	var L10n = window.lpL10n || {};
	var t = function (k, d) { return L10n[k] || d; };
	var ICON = {
		zoom: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2M11 8.5v5M8.5 11h5"/></svg>',
		play: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l10.5-6.5z"/></svg>',
		pause: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6.5" y="5.5" width="4" height="13" rx="1"/><rect x="13.5" y="5.5" width="4" height="13" rx="1"/></svg>',
		thumbs: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="4" y="4" width="4.5" height="4.5" rx="1"/><rect x="9.75" y="4" width="4.5" height="4.5" rx="1"/><rect x="15.5" y="4" width="4.5" height="4.5" rx="1"/><rect x="4" y="9.75" width="4.5" height="4.5" rx="1"/><rect x="9.75" y="9.75" width="4.5" height="4.5" rx="1"/><rect x="15.5" y="9.75" width="4.5" height="4.5" rx="1"/><rect x="4" y="15.5" width="4.5" height="4.5" rx="1"/><rect x="9.75" y="15.5" width="4.5" height="4.5" rx="1"/><rect x="15.5" y="15.5" width="4.5" height="4.5" rx="1"/></svg>',
		full: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/></svg>',
		close: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
		prev: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>',
		next: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
	};
	var SLIDE_MS = 3500;
	var box, img, counter, strip, bar, playBtn, items = [], index = 0, lastFocus = null, timer = null, zoomed = false;

	function btn(cls, icon, label) {
		return '<button type="button" class="lp-lb__btn ' + cls + '" aria-label="' + label + '" title="' + label + '">' + icon + '</button>';
	}

	function build() {
		box = document.createElement('div');
		box.className = 'lp-lightbox lp-lb';
		box.hidden = true;
		box.setAttribute('role', 'dialog');
		box.setAttribute('aria-modal', 'true');
		box.setAttribute('aria-label', t('viewer', 'Xem ảnh'));
		box.innerHTML =
			'<div class="lp-lb__progress"><span></span></div>' +
			'<div class="lp-lb__top">' +
				'<p class="lp-lb__count" aria-live="polite"></p>' +
				'<div class="lp-lb__tools">' +
					btn('lp-lb__zoom', ICON.zoom, t('zoom', 'Phóng to')) +
					btn('lp-lb__play', ICON.play, t('play', 'Trình chiếu')) +
					btn('lp-lb__thumbs-toggle', ICON.thumbs, t('thumbs', 'Ẩn / hiện ảnh nhỏ')) +
					(document.fullscreenEnabled ? btn('lp-lb__full', ICON.full, t('full', 'Toàn màn hình')) : '') +
					btn('lp-lb__close', ICON.close, t('close', 'Đóng')) +
				'</div>' +
			'</div>' +
			'<div class="lp-lb__stage">' +
				btn('lp-lb__nav lp-lb__prev', ICON.prev, t('prev', 'Ảnh trước')) +
				'<figure class="lp-lb__figure"><img class="lp-lb__img" alt="" draggable="false"></figure>' +
				btn('lp-lb__nav lp-lb__next', ICON.next, t('next', 'Ảnh sau')) +
			'</div>' +
			'<div class="lp-lb__strip" role="tablist"></div>';
		document.body.appendChild(box);
		img = box.querySelector('.lp-lb__img');
		counter = box.querySelector('.lp-lb__count');
		strip = box.querySelector('.lp-lb__strip');
		bar = box.querySelector('.lp-lb__progress span');
		playBtn = box.querySelector('.lp-lb__play');

		box.querySelector('.lp-lb__close').addEventListener('click', close);
		box.querySelector('.lp-lb__prev').addEventListener('click', function () { go(index - 1); });
		box.querySelector('.lp-lb__next').addEventListener('click', function () { go(index + 1); });
		box.querySelector('.lp-lb__zoom').addEventListener('click', function () { zoom(!zoomed); });
		playBtn.addEventListener('click', function () { if (timer) { stop(); } else { play(); } });
		box.querySelector('.lp-lb__thumbs-toggle').addEventListener('click', function () {
			box.classList.toggle('no-thumbs');
			try { localStorage.setItem('lpLbThumbs', box.classList.contains('no-thumbs') ? '0' : '1'); } catch (e) {}
		});
		var fullBtn = box.querySelector('.lp-lb__full');
		if (fullBtn) {
			fullBtn.addEventListener('click', function () {
				if (document.fullscreenElement) { document.exitFullscreen(); } else { box.requestFullscreen().catch(function () {}); }
			});
		}
		strip.addEventListener('click', function (e) {
			var th = e.target.closest('.lp-lb__thumb');
			if (th) { go(+th.dataset.i); }
		});
		// Bấm vùng trống quanh ảnh để đóng.
		box.querySelector('.lp-lb__stage').addEventListener('click', function (e) {
			if (e.target.classList.contains('lp-lb__stage') || e.target.classList.contains('lp-lb__figure')) { close(); }
		});
		// Phóng to: bấm vào ảnh; di chuột để xem chi tiết.
		img.addEventListener('click', function (e) { zoom(!zoomed, e); });
		img.addEventListener('mousemove', function (e) { if (zoomed) { origin(e); } });

		var sx = null, sy = null;
		box.addEventListener('touchstart', function (e) { if (zoomed) { return; } sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
		box.addEventListener('touchend', function (e) {
			if (sx === null) { return; }
			var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
			if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) { go(index + (dx < 0 ? 1 : -1)); }
			sx = null;
		});
		try { if (localStorage.getItem('lpLbThumbs') === '0') { box.classList.add('no-thumbs'); } } catch (e) {}
	}

	function src(link) { return link.getAttribute('href'); }
	function thumbOf(link) {
		var im = link.querySelector('img');
		return link.getAttribute('data-thumb') || (im ? (im.currentSrc || im.src) : src(link));
	}
	function altOf(link) {
		var im = link.querySelector('img');
		return link.getAttribute('data-alt') || (im ? im.alt : '');
	}

	function origin(e) {
		var r = img.getBoundingClientRect();
		img.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
	}
	function zoom(on, e) {
		zoomed = on;
		box.classList.toggle('is-zoomed', on);
		if (on && e) { origin(e); }
		if (!on) { img.style.transformOrigin = ''; }
		if (on) { stop(); }
	}

	function renderStrip() {
		strip.innerHTML = '';
		items.forEach(function (link, i) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'lp-lb__thumb';
			b.dataset.i = i;
			b.setAttribute('role', 'tab');
			b.setAttribute('aria-label', (i + 1) + ' / ' + items.length);
			b.innerHTML = '<img src="' + thumbOf(link) + '" alt="" loading="lazy" draggable="false">';
			strip.appendChild(b);
		});
		box.classList.toggle('is-single', items.length < 2);
	}

	function go(i) {
		if (!items.length) { return; }
		zoom(false);
		index = (i + items.length) % items.length;
		var link = items[index];
		box.classList.add('is-loading');
		var pre = new Image();
		pre.onload = pre.onerror = function () {
			if (items[index] !== link) { return; }
			img.src = src(link);
			img.alt = altOf(link);
			box.classList.remove('is-loading');
		};
		pre.src = src(link);
		counter.textContent = (index + 1) + ' / ' + items.length;
		strip.querySelectorAll('.lp-lb__thumb').forEach(function (b, k) {
			var on = k === index;
			b.classList.toggle('is-active', on);
			b.setAttribute('aria-selected', on ? 'true' : 'false');
			if (on && b.scrollIntoView) { b.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }); }
		});
		// Tải trước ảnh kế tiếp.
		[index + 1, index - 1].forEach(function (k) { (new Image()).src = src(items[(k + items.length) % items.length]); });
		if (timer) { restartBar(); }
	}

	function restartBar() {
		bar.style.transition = 'none';
		bar.style.width = '0';
		void bar.offsetWidth;
		bar.style.transition = 'width ' + SLIDE_MS + 'ms linear';
		bar.style.width = '100%';
	}
	function play() {
		if (items.length < 2) { return; }
		zoom(false);
		timer = setInterval(function () { go(index + 1); }, SLIDE_MS);
		box.classList.add('is-playing');
		playBtn.innerHTML = ICON.pause;
		playBtn.setAttribute('aria-label', t('pause', 'Dừng trình chiếu'));
		playBtn.title = t('pause', 'Dừng trình chiếu');
		restartBar();
	}
	function stop() {
		if (!timer) { return; }
		clearInterval(timer);
		timer = null;
		box.classList.remove('is-playing');
		playBtn.innerHTML = ICON.play;
		playBtn.setAttribute('aria-label', t('play', 'Trình chiếu'));
		playBtn.title = t('play', 'Trình chiếu');
		bar.style.transition = 'none';
		bar.style.width = '0';
	}

	function open(list, i) {
		if (!box) { build(); }
		items = list;
		lastFocus = document.activeElement;
		renderStrip();
		box.hidden = false;
		document.documentElement.style.overflow = 'hidden';
		go(i);
		box.querySelector('.lp-lb__close').focus();
	}

	function close() {
		stop();
		zoom(false);
		if (document.fullscreenElement) { document.exitFullscreen().catch(function () {}); }
		box.hidden = true;
		img.removeAttribute('src');
		document.documentElement.style.overflow = '';
		if (lastFocus) { lastFocus.focus(); }
	}

	document.addEventListener('keydown', function (e) {
		if (!box || box.hidden) { return; }
		if (e.key === 'Escape') { if (zoomed) { zoom(false); } else { close(); } }
		else if (e.key === 'ArrowLeft') { go(index - 1); }
		else if (e.key === 'ArrowRight') { go(index + 1); }
		else if (e.key === ' ' && e.target === document.body) { e.preventDefault(); if (timer) { stop(); } else { play(); } }
		else if (e.key === 'Tab') {
			// Giữ con trỏ bàn phím trong trình xem ảnh.
			var f = Array.prototype.filter.call(box.querySelectorAll('button'), function (b) { return b.offsetParent !== null; });
			if (!f.length) { return; }
			if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
			else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
		}
	});

	document.addEventListener('click', function (e) {
		var link = e.target.closest && e.target.closest('[data-lp-gallery] .lp-gallery__item');
		if (link) {
			e.preventDefault();
			var list = Array.prototype.slice.call(link.closest('[data-lp-gallery]').querySelectorAll('.lp-gallery__item'));
			open(list, list.indexOf(link));
			return;
		}

		/* -------------------------------------------------------- Video */
		var video = e.target.closest && e.target.closest('[data-lp-youtube]');
		if (video && !video.querySelector('iframe')) {
			var frame = document.createElement('iframe');
			frame.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(video.getAttribute('data-lp-youtube')) + '?autoplay=1&rel=0';
			frame.title = video.getAttribute('aria-label') || 'Video';
			frame.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
			frame.allowFullscreen = true;
			video.appendChild(frame);
		}
	});
})();

/* Tab lọc dự án theo tỉnh/thành. */
document.addEventListener('click', function (e) {
	var btn = e.target.closest && e.target.closest('[data-lp-filter]');
	if (!btn) { return; }
	var wrap = btn.closest('[data-lp-tabs]');
	var value = btn.getAttribute('data-lp-filter');
	wrap.querySelectorAll('[data-lp-filter]').forEach(function (b) {
		var on = b === btn;
		b.classList.toggle('is-active', on);
		b.setAttribute('aria-pressed', on ? 'true' : 'false');
	});
	wrap.querySelectorAll('[data-lp-loc]').forEach(function (card) {
		card.hidden = value !== '' && (' ' + card.getAttribute('data-lp-loc') + ' ').indexOf(' ' + value + ' ') === -1;
	});
});

/* Ô chọn tùy biến cho form tìm kiếm (máy tính có chuột). Ô <select> gốc vẫn giữ để gửi form;
   điện thoại/máy tính bảng dùng bộ chọn gốc của hệ điều hành (dễ thao tác hơn). */
(function () {
	'use strict';
	if (!window.matchMedia || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) { return; }
	var NBSP = /^[  ]+/;
	var uid = 0;

	function depthOf(text) {
		var m = text.match(NBSP);
		return m ? Math.round(m[0].length / 4) : 0;
	}

	function build(select) {
		var field = select.closest('.lp-search__field');
		if (!field || field.classList.contains('lp-select')) { return; }
		field.classList.add('lp-select');
		var id = 'lp-select-' + (++uid);
		var label = field.querySelector('label');

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'lp-select__btn';
		btn.setAttribute('aria-haspopup', 'listbox');
		btn.setAttribute('aria-expanded', 'false');
		btn.setAttribute('aria-controls', id);
		if (label) { label.id = label.id || id + '-label'; btn.setAttribute('aria-labelledby', label.id + ' ' + id + '-value'); }
		btn.innerHTML = '<span class="lp-select__value" id="' + id + '-value"></span>';

		var list = document.createElement('ul');
		list.className = 'lp-select__list';
		list.id = id;
		list.setAttribute('role', 'listbox');
		list.tabIndex = -1;
		list.hidden = true;

		var opts = Array.prototype.slice.call(select.options);
		opts.forEach(function (o, i) {
			var li = document.createElement('li');
			var d = depthOf(o.text);
			var next = opts[i + 1];
			li.setAttribute('role', 'option');
			li.id = id + '-o' + i;
			li.dataset.value = o.value;
			li.className = 'lp-select__opt lp-select__opt--d' + d +
				(o.value === '' ? ' lp-select__opt--all' : '') +
				(next && depthOf(next.text) > d ? ' lp-select__opt--parent' : '');
			li.textContent = o.text.replace(NBSP, '');
			list.appendChild(li);
		});

		select.classList.add('lp-select__native');
		select.tabIndex = -1;
		select.setAttribute('aria-hidden', 'true');
		field.appendChild(btn);
		field.appendChild(list);

		var items = Array.prototype.slice.call(list.children);
		var active = -1;

		function sync() {
			var idx = select.selectedIndex < 0 ? 0 : select.selectedIndex;
			btn.querySelector('.lp-select__value').textContent = items[idx].textContent;
			field.classList.toggle('is-placeholder', select.value === '');
			items.forEach(function (li, i) { li.setAttribute('aria-selected', i === idx ? 'true' : 'false'); });
		}
		function setActive(i) {
			if (active >= 0) { items[active].classList.remove('is-active'); }
			active = Math.max(0, Math.min(items.length - 1, i));
			items[active].classList.add('is-active');
			list.setAttribute('aria-activedescendant', items[active].id);
			var li = items[active];
			if (li.offsetTop < list.scrollTop) { list.scrollTop = li.offsetTop; }
			else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight) { list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight; }
		}
		function open() {
			document.querySelectorAll('.lp-select.is-open').forEach(function (f) { if (f !== field && f._lpClose) { f._lpClose(); } });
			list.hidden = false;
			field.classList.add('is-open');
			btn.setAttribute('aria-expanded', 'true');
			setActive(select.selectedIndex < 0 ? 0 : select.selectedIndex);
			list.focus();
		}
		function close(focusBtn) {
			list.hidden = true;
			field.classList.remove('is-open');
			btn.setAttribute('aria-expanded', 'false');
			if (focusBtn) { btn.focus(); }
		}
		function choose(i) {
			select.selectedIndex = i;
			select.dispatchEvent(new Event('change', { bubbles: true }));
			sync();
			close(true);
		}
		select.addEventListener('change', sync); // Đổi giá trị bằng code (Xóa lọc…) cũng cập nhật nút.
		field._lpClose = function () { close(false); };

		btn.addEventListener('click', function () { if (list.hidden) { open(); } else { close(true); } });
		btn.addEventListener('keydown', function (e) {
			if (['ArrowDown', 'ArrowUp', 'Enter', ' '].indexOf(e.key) !== -1) { e.preventDefault(); open(); }
		});
		list.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
			else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
			else if (e.key === 'Home') { e.preventDefault(); setActive(0); }
			else if (e.key === 'End') { e.preventDefault(); setActive(items.length - 1); }
			else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); choose(active); }
			else if (e.key === 'Escape') { e.preventDefault(); close(true); }
			else if (e.key === 'Tab') { close(false); }
		});
		list.addEventListener('mousemove', function (e) {
			var li = e.target.closest('.lp-select__opt');
			if (li) { setActive(items.indexOf(li)); }
		});
		list.addEventListener('click', function (e) {
			var li = e.target.closest('.lp-select__opt');
			if (li) { choose(items.indexOf(li)); }
		});
		select.addEventListener('change', sync);
		sync();
	}

	function init() {
		document.querySelectorAll('.lp-search select').forEach(build);
	}
	document.addEventListener('click', function (e) {
		document.querySelectorAll('.lp-select.is-open').forEach(function (f) {
			if (!f.contains(e.target) && f._lpClose) { f._lpClose(); }
		});
	});
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
	window.addEventListener('elementor/frontend/init', function () { setTimeout(init, 0); });
})();

/* Chiều cao header dính → biến CSS (thanh mục lục dự án dính ngay dưới header). */
(function () {
	'use strict';
	function setH() {
		var h = document.querySelector('#masthead, .ehf-header header, header.site-header');
		var top = h ? Math.max(0, Math.round(h.getBoundingClientRect().height)) : 0;
		var bar = document.getElementById('wpadminbar');
		if (bar && getComputedStyle(bar).position === 'fixed') { top += bar.offsetHeight; }
		document.documentElement.style.setProperty('--lp-header-h', top + 'px');
	}
	setH(); window.addEventListener('resize', setH); window.addEventListener('load', setH);

	/* Mục lục dự án: tô mục đang xem, cuộn tab đang chọn vào giữa trên mobile. */
	var nav = document.querySelector('[data-lp-subnav]');
	if (nav && 'IntersectionObserver' in window) {
		var links = [].slice.call(nav.querySelectorAll('a'));
		var secs = links.map(function (a) { return document.getElementById(a.hash.slice(1)); });
		var current = null;
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) { return; }
				var i = secs.indexOf(e.target);
				if (i < 0 || links[i] === current) { return; }
				if (current) { current.classList.remove('is-active'); current.removeAttribute('aria-current'); }
				current = links[i]; current.classList.add('is-active'); current.setAttribute('aria-current', 'true');
				var list = nav.querySelector('.lp-subnav__list');
				list.scrollTo({ left: current.offsetLeft - (list.clientWidth - current.offsetWidth) / 2, behavior: 'smooth' });
			});
		}, { rootMargin: '-45% 0px -50% 0px' });
		secs.forEach(function (s) { if (s) { io.observe(s); } });
	}

	/* Nút về đầu trang. */
	var top = document.querySelector('.lp-totop');
	if (top) {
		top.hidden = false;
		var onScroll = function () { top.classList.toggle('is-visible', window.scrollY > 600); };
		window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
		top.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
		});
	}
})();

/* Slider đầu trang: mờ dần, tự chạy, dừng khi rê chuột / tab ẩn / giảm chuyển động. */
(function () {
	'use strict';
	document.querySelectorAll('[data-lp-hero]').forEach(function (hero) {
		var slides = [].slice.call(hero.querySelectorAll('.lp-hero__slide'));
		var dots = [].slice.call(hero.querySelectorAll('.lp-hero__dot'));
		var idx = 0, timer = null;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var interval = parseInt(hero.getAttribute('data-interval'), 10) || 6000;
		var conn = navigator.connection || {};
		var allowVideo = !reduce && !conn.saveData && !/(^|-)2g$/.test(conn.effectiveType || '');
		var pageLoaded = document.readyState === 'complete';
		var visible = true;
		function playVideo(slide) {
			var v = slide.querySelector('video');
			if (!v || !allowVideo || !pageLoaded || !visible) { return; }
			if (!v.src) {
				var mobile = window.matchMedia('(max-width: 767px)').matches;
				v.src = (mobile && v.dataset.srcMobile) ? v.dataset.srcMobile : v.dataset.src;
				v.addEventListener('playing', function () { v.classList.add('is-playing'); }, { once: true });
			}
			var p = v.play(); if (p && p.catch) { p.catch(function () {}); }
		}
		// Tải video sau khi trang đã tải xong (không tranh băng thông với nội dung chính).
		if (!pageLoaded) { window.addEventListener('load', function () { pageLoaded = true; setTimeout(function () { playVideo(slides[idx]); }, 300); }); }
		// Tạm dừng khi slider ra khỏi màn hình.
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (es) { visible = es[0].isIntersecting; var v = slides[idx] && slides[idx].querySelector('video'); if (!v) { return; } if (visible) { playVideo(slides[idx]); } else { v.pause(); } }).observe(hero);
		}
		function pauseVideo(slide) { var v = slide.querySelector('video'); if (v) { v.pause(); } }
		function go(n) {
			if (!slides.length) { return; }
			pauseVideo(slides[idx]);
			slides[idx].classList.remove('is-active'); slides[idx].setAttribute('aria-hidden', 'true');
			if (dots[idx]) { dots[idx].classList.remove('is-active'); }
			idx = (n + slides.length) % slides.length;
			slides[idx].classList.add('is-active'); slides[idx].removeAttribute('aria-hidden');
			if (dots[idx]) { dots[idx].classList.add('is-active'); }
			playVideo(slides[idx]);
		}
		function start() { stop(); if (!reduce && slides.length > 1) { timer = setInterval(function () { go(idx + 1); }, interval); } }
		function stop() { if (timer) { clearInterval(timer); timer = null; } }
		hero.addEventListener('click', function (e) {
			var a = e.target.closest('[data-dir]'), d = e.target.closest('[data-go]');
			if (a) { go(idx + parseInt(a.getAttribute('data-dir'), 10)); start(); }
			if (d) { go(parseInt(d.getAttribute('data-go'), 10)); start(); }
		});
		hero.addEventListener('mouseenter', stop);
		hero.addEventListener('mouseleave', start);
		hero.addEventListener('focusin', stop);
		document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });
		var sx = null;
		hero.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
		hero.addEventListener('touchend', function (e) { if (sx === null) { return; } var dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 50) { go(idx + (dx < 0 ? 1 : -1)); start(); } sx = null; });
		if (slides[0]) { playVideo(slides[0]); }
		start();
	});
})();

/* Slider lưới: nút ‹ › cuộn một card, tự khóa nút ở hai đầu. */
(function () {
	'use strict';
	document.querySelectorAll('[data-lp-slider]').forEach(function (sl) {
		var track = sl.querySelector('.lp-slider__track');
		var btns = sl.querySelectorAll('.lp-slider__btn');
		if (!track) { return; }
		function step() { var c = track.firstElementChild; return c ? c.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth; }
		function update() {
			var max = track.scrollWidth - track.clientWidth - 2;
			btns[0].disabled = track.scrollLeft <= 2;
			btns[1].disabled = track.scrollLeft >= max;
		}
		btns.forEach(function (b) { b.addEventListener('click', function () { track.scrollBy({ left: step() * parseInt(b.getAttribute('data-dir'), 10), behavior: 'smooth' }); }); });
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	});
})();

/* ---------------------------------------------------------------- Gợi ý từ khóa ở thanh tìm kiếm */
document.addEventListener('click', function (e) {
	var chip = e.target.closest('[data-lp-suggest]');
	if (!chip) { return; }
	var form = chip.closest('form');
	var kw = form && form.querySelector('input[name="lp_kw"]');
	if (!kw) { return; }
	kw.value = chip.getAttribute('data-lp-suggest');
	kw.focus();
	form.querySelectorAll('[data-lp-suggest]').forEach(function (c) { c.classList.toggle('is-active', c === chip); });
});

/* ---------------------------------------------------------------- Lọc tức thì (không tải lại trang) */
(function () {
	'use strict';
	var form = document.querySelector('form[data-lp-live]');
	if (!form || !window.fetch || !window.DOMParser) { return; }
	var base = form.getAttribute('action').split('?')[0];
	var active = form.querySelector('[data-lp-active]');
	var chips = form.querySelector('[data-lp-chips]');
	var reset = form.querySelector('[data-lp-reset]');
	var ctrl = null;
	var timer = null;

	function query() {
		var params = new URLSearchParams();
		new FormData(form).forEach(function (v, k) {
			if (String(v).trim() !== '') { params.append(k, String(v).trim()); }
		});
		// Giữ thứ tự sắp xếp hiện tại.
		var sort = document.querySelector('.lp-toolbar__sort select');
		if (sort && sort.value && sort.value !== 'newest') { params.set('lp_sort', sort.value); }
		return params.toString();
	}

	function renderChips() {
		if (!active) { return; }
		chips.innerHTML = '';
		form.querySelectorAll('select, input[name="lp_kw"]').forEach(function (f) {
			if (!f.value) { return; }
			var text = f.tagName === 'SELECT' ? f.options[f.selectedIndex].text.replace(/^[\s ]+/, '') : '“' + f.value + '”';
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'lp-search__chip lp-search__chip--on';
			b.innerHTML = '<span></span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
			b.firstChild.textContent = text;
			b.setAttribute('aria-label', (reset.dataset.removeLabel || '×') + ': ' + text);
			b.addEventListener('click', function () {
				f.value = '';
				f.dispatchEvent(new Event('change', { bubbles: true }));
				if (f.tagName !== 'SELECT') { apply(); }
			});
			chips.appendChild(b);
		});
		active.hidden = !chips.children.length;
	}

	function apply(url) {
		var qs = query();
		url = url || (base + (qs ? '?' + qs : ''));
		if (ctrl) { ctrl.abort(); }
		ctrl = new AbortController();
		var listings = document.querySelectorAll('.lp-listing');
		listings.forEach(function (l) { l.classList.add('is-loading'); l.setAttribute('aria-busy', 'true'); });
		fetch(url, { signal: ctrl.signal, credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var fresh = doc.querySelectorAll('.lp-listing');
				listings.forEach(function (l, i) {
					if (!fresh[i]) { return; }
					// Giữ nút "Xem bản đồ" đã được chuyển vào thanh công cụ.
					var mapBtn = l.querySelector('[data-lp-map-open]');
					var neu = document.importNode(fresh[i], true);
					l.replaceWith(neu);
					var bar = neu.querySelector('.lp-toolbar');
					if (mapBtn && bar) { bar.appendChild(mapBtn); }
				});
				var maps = document.querySelectorAll('[data-lp-map]');
				var freshMaps = doc.querySelectorAll('[data-lp-map]');
				maps.forEach(function (m, i) {
					if (freshMaps[i] && window.lpMapUpdate) { window.lpMapUpdate(m, JSON.parse(freshMaps[i].dataset.markers || '[]')); }
				});
				if (url !== location.href) { history.pushState({ lpLive: 1 }, '', url); }
				renderChips();
				var first = document.querySelector('.lp-listing');
				if (first && first.getBoundingClientRect().top < 0) {
					first.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
				document.dispatchEvent(new CustomEvent('lp:listing-updated'));
			})
			.catch(function (err) {
				if (err.name !== 'AbortError') { location.href = url; }
			});
	}

	form.addEventListener('change', function (e) {
		if (e.target.matches('select')) { apply(); return; }
		// Đổi tab Mua / Thuê: tải lại trang vì danh sách mức giá của bán và thuê khác nhau.
		if (e.target.matches('input[name="lp_purpose"]')) {
			var qs = query();
			location.href = base + (qs ? '?' + qs : '');
		}
	});
	form.addEventListener('submit', function (e) { e.preventDefault(); apply(); });
	form.addEventListener('input', function (e) {
		if (e.target.name !== 'lp_kw') { return; }
		clearTimeout(timer);
		var v = e.target.value.trim();
		if (v.length === 0 || v.length >= 2) { timer = setTimeout(function () { apply(); }, 600); }
	});
	if (reset) {
		reset.addEventListener('click', function (e) {
			e.preventDefault();
			form.querySelectorAll('select').forEach(function (s) { s.value = ''; });
			form.querySelectorAll('select').forEach(function (s) { s.dispatchEvent(new Event('change')); }); // Không nổi bọt: chỉ cập nhật nút chọn.
			var kw = form.querySelector('input[name="lp_kw"]');
			if (kw) { kw.value = ''; }
			apply(base);
		});
	}
	// Sắp xếp và phân trang trong danh sách cũng không tải lại trang.
	document.addEventListener('change', function (e) {
		if (e.target.matches('.lp-toolbar__sort select')) { e.stopImmediatePropagation(); apply(); }
	}, true);
	document.addEventListener('click', function (e) {
		var a = e.target.closest('.lp-listing .lp-pagination a');
		if (a) { e.preventDefault(); apply(a.href); }
	});
	window.addEventListener('popstate', function () { location.reload(); });
	renderChips();
})();
