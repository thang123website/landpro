/**
 * LandPro – trang Ký gửi: chọn/kéo thả ảnh có xem trước, gợi ý giá, gửi tin qua REST (có tiến trình tải ảnh),
 * danh sách "Tin ký gửi của bạn" theo mã lưu trên trình duyệt (không phụ thuộc cache trang).
 */
( function () {
	'use strict';
	var root = document.querySelector( '[data-lp-consign]' );
	if ( ! root ) {
		return;
	}
	var api = root.dataset.api;
	var lang = root.dataset.lang || 'vi';
	var L = JSON.parse( root.dataset.labels || '{}' );
	var form = root.querySelector( '.lp-cs__form' );
	var msg = form.querySelector( '.lp-cs__msg' );
	var bar = form.querySelector( '.lp-cs__progress' );
	var btn = form.querySelector( '.lp-cs__submit' );
	var input = form.querySelector( '[data-lp-files]' );
	var drop = form.querySelector( '[data-lp-drop]' );
	var previews = form.querySelector( '[data-lp-previews]' );
	var mine = root.querySelector( '[data-lp-mine]' );
	var list = root.querySelector( '[data-lp-list]' );
	var MAX = 10;
	var MAX_BYTES = 5 * 1024 * 1024;
	var files = [];
	var started = Date.now();

	/* ---------- Mã trình duyệt (để xem lại tin đã gửi) */
	var KEY = 'lpConsignToken';
	function token() {
		var t = null;
		try {
			t = localStorage.getItem( KEY );
		} catch ( e ) {}
		if ( ! t ) {
			var a = new Uint8Array( 16 );
			( window.crypto || window.msCrypto ).getRandomValues( a );
			t = Array.prototype.map.call( a, function ( b ) {
				return ( '0' + b.toString( 16 ) ).slice( -2 );
			} ).join( '' );
			try {
				localStorage.setItem( KEY, t );
			} catch ( e ) {}
		}
		return t;
	}
	function hasToken() {
		try {
			return !! localStorage.getItem( KEY );
		} catch ( e ) {
			return false;
		}
	}

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function say( text, type ) {
		msg.hidden = ! text;
		msg.textContent = text || '';
		msg.className = 'lp-cs__msg' + ( type ? ' is-' + type : '' );
	}

	/* ---------- Ảnh */
	function render() {
		previews.innerHTML = '';
		files.forEach( function ( f, i ) {
			var li = document.createElement( 'li' );
			var url = URL.createObjectURL( f );
			li.innerHTML =
				'<img src="' + url + '" alt="">' +
				( i === 0 ? '<span class="lp-cs__cover">★</span>' : '' ) +
				'<button type="button" aria-label="' + esc( L.remove ) + '" data-i="' + i + '">×</button>';
			li.querySelector( 'img' ).onload = function () {
				URL.revokeObjectURL( url );
			};
			previews.appendChild( li );
		} );
	}
	function add( list ) {
		var skipped = false;
		Array.prototype.forEach.call( list, function ( f ) {
			if ( ! /^image\/(jpeg|png|webp)$/.test( f.type ) ) {
				return;
			}
			if ( f.size > MAX_BYTES ) {
				skipped = true;
				return;
			}
			if ( files.length < MAX ) {
				files.push( f );
			}
		} );
		say( skipped ? L.tooBig : ( files.length >= MAX ? L.tooMany : '' ), skipped ? 'warn' : '' );
		render();
	}
	input.addEventListener( 'change', function () {
		add( input.files );
		input.value = '';
	} );
	[ 'dragenter', 'dragover' ].forEach( function ( ev ) {
		drop.addEventListener( ev, function ( e ) {
			e.preventDefault();
			drop.classList.add( 'is-over' );
		} );
	} );
	[ 'dragleave', 'drop' ].forEach( function ( ev ) {
		drop.addEventListener( ev, function ( e ) {
			e.preventDefault();
			drop.classList.remove( 'is-over' );
		} );
	} );
	drop.addEventListener( 'drop', function ( e ) {
		add( e.dataTransfer.files );
	} );
	previews.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( 'button[data-i]' );
		if ( b ) {
			files.splice( +b.dataset.i, 1 );
			render();
		}
	} );

	/* ---------- Gợi ý giá dạng "3,85 tỷ" */
	var price = form.querySelector( '[name="cs_price"]' );
	var hint = form.querySelector( '[data-lp-price-hint]' );
	var hintDefault = hint.textContent;
	var neg = form.querySelector( '[name="cs_negotiable"]' );
	function fmt( n ) {
		var dec = lang === 'en' ? '.' : ',';
		return ( Math.round( n * 100 ) / 100 ).toString().replace( '.', dec );
	}
	function priceHint() {
		var v = parseFloat( price.value );
		var rent = form.querySelector( '[name="cs_purpose"]:checked' ) === form.querySelectorAll( '[name="cs_purpose"]' )[ 1 ];
		if ( ! v ) {
			hint.textContent = hintDefault;
			return;
		}
		var t = v >= 1e9 ? fmt( v / 1e9 ) + ' ' + L.billion : fmt( v / 1e6 ) + ' ' + L.million;
		hint.textContent = '≈ ' + t + ( rent ? L.month : '' );
	}
	price.addEventListener( 'input', priceHint );
	form.addEventListener( 'change', function ( e ) {
		if ( e.target.name === 'cs_purpose' ) {
			priceHint();
		}
		if ( e.target === neg ) {
			price.required = ! neg.checked;
			price.disabled = neg.checked;
		}
	} );

	/* ---------- Gửi */
	/* Kiểm tra ô bắt buộc; ghi lại tên ô lỗi (kèm số ký tự còn thiếu) để báo rõ cho khách. */
	var problems = [];
	function labelOf( el ) {
		var box = el.closest( '.lp-cs__field' );
		var lb = box && box.querySelector( 'label, .lp-cs__label' );
		if ( el.type === 'checkbox' ) {
			return L.consent;
		}
		return lb ? lb.textContent.replace( '*', '' ).trim() : el.name;
	}
	function invalid() {
		var bad = null;
		problems = [];
		form.querySelectorAll( '[required]' ).forEach( function ( el ) {
			var v = el.type === 'checkbox' ? '' : el.value.trim();
			var tooShort = el.minLength > 0 && v !== '' && v.length < el.minLength;
			var ok = el.type === 'checkbox' ? el.checked : v !== '' && ! tooShort;
			var box = el.closest( '.lp-cs__field, .lp-cs__consent' );
			if ( box ) {
				box.classList.toggle( 'is-invalid', ! ok );
			}
			if ( ! ok ) {
				problems.push( labelOf( el ) + ( tooShort ? ' (' + L.minChars.replace( '%1$d', el.minLength ).replace( '%2$d', v.length ) + ')' : '' ) );
				bad = bad || el;
			}
		} );
		return bad;
	}

	/* Bộ đếm ký tự mô tả */
	var desc = form.querySelector( '[name="cs_desc"]' );
	var counter = form.querySelector( '[data-lp-desc-count]' );
	function countDesc() {
		var n = desc.value.trim().length;
		var min = desc.minLength || 30;
		counter.textContent = L.counter.replace( '%1$d', n ).replace( '%2$d', min );
		counter.classList.toggle( 'is-ok', n >= min );
	}
	if ( desc && counter ) {
		desc.addEventListener( 'input', countDesc );
		countDesc();
	}
	form.addEventListener( 'input', function ( e ) {
		var f = e.target.closest( '.is-invalid' );
		if ( f ) {
			f.classList.remove( 'is-invalid' );
		}
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var bad = invalid();
		if ( bad ) {
			say( L.check + ' ' + problems.join( '; ' ) + '.', 'error' );
			bad.focus();
			return;
		}
		var fd = new FormData( form );
		fd.append( 'cs_token', token() );
		fd.append( 'cs_lang', lang );
		fd.append( 'cs_elapsed', Math.round( ( Date.now() - started ) / 1000 ) );
		files.forEach( function ( f ) {
			fd.append( 'cs_images[]', f, f.name );
		} );

		btn.disabled = true;
		say( L.sending, '' );
		bar.hidden = false;
		var span = bar.querySelector( 'span' );
		span.style.width = '0';

		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', api );
		xhr.upload.onprogress = function ( ev ) {
			if ( ev.lengthComputable ) {
				span.style.width = Math.round( ( ev.loaded / ev.total ) * 100 ) + '%';
			}
		};
		xhr.onload = function () {
			btn.disabled = false;
			bar.hidden = true;
			var res = {};
			try {
				res = JSON.parse( xhr.responseText );
			} catch ( err ) {}
			if ( xhr.status >= 200 && xhr.status < 300 && res.ok ) {
				form.reset();
				files = [];
				render();
				hint.textContent = hintDefault;
				price.disabled = false;
				if ( counter ) {
					countDesc();
				}
				started = Date.now();
				say( res.message || L.success, 'success' );
				load( true );
			} else {
				say( res.message || L.error, 'error' );
			}
		};
		xhr.onerror = function () {
			btn.disabled = false;
			bar.hidden = true;
			say( L.error, 'error' );
		};
		xhr.send( fd );
	} );

	/* ---------- Tin ký gửi của bạn */
	function load( scroll ) {
		if ( ! hasToken() ) {
			return;
		}
		fetch( api + ( api.indexOf( '?' ) > -1 ? '&' : '?' ) + 'token=' + encodeURIComponent( token() ) + '&lang=' + lang, { credentials: 'same-origin', cache: 'no-store' } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( d ) {
				var items = ( d && d.items ) || [];
				mine.hidden = false;
				if ( ! items.length ) {
					list.innerHTML = '<li class="lp-cs__none">' + esc( L.empty ) + '</li>';
					return;
				}
				list.innerHTML = items
					.map( function ( it ) {
						var badge = '<span class="lp-cs__badge is-' + it.status + '">' + esc( L[ it.status ] || it.status ) + '</span>';
						return (
							'<li class="lp-cs__item">' +
							( it.thumb ? '<img src="' + esc( it.thumb ) + '" alt="" loading="lazy">' : '<span class="lp-cs__noimg"></span>' ) +
							'<div class="lp-cs__info"><strong>' + esc( it.title ) + '</strong>' +
							'<span>' + esc( it.date ) + ( it.price ? ' · ' + esc( it.price ) : '' ) + '</span>' +
							( it.note ? '<span class="lp-cs__reason">' + esc( L.reason ) + ': ' + esc( it.note ) + '</span>' : '' ) +
							'</div>' + badge +
							( it.url ? '<a class="lp-cs__view" href="' + esc( it.url ) + '">' + esc( L.view ) + ' →</a>' : '' ) +
							'</li>'
						);
					} )
					.join( '' );
				if ( scroll ) {
					mine.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}
			} )
			.catch( function () {} );
	}
	load( false );

	/* ---------- Câu hỏi thường gặp: mở câu này thì đóng các câu khác (dự phòng cho trình duyệt chưa hỗ trợ details[name]) */
	root.querySelectorAll( '.lp-cs__faq details' ).forEach( function ( d, i, all ) {
		d.addEventListener( 'toggle', function () {
			if ( d.open ) {
				Array.prototype.forEach.call( all, function ( o ) {
					if ( o !== d ) {
						o.open = false;
					}
				} );
			}
		} );
	} );
} )();
