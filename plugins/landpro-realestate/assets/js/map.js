/**
 * LandPro – bản đồ nhà đất (Leaflet + OpenStreetMap).
 * Ghim hiện giá rút gọn, bấm ghim mở thẻ nhà. Bản đồ chỉ khởi tạo khi cuộn tới.
 */
( function () {
	'use strict';
	if ( typeof L === 'undefined' || ! window.LPMapBase ) {
		return;
	}

	var esc = function ( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	};

	function pinIcon( m, single ) {
		if ( single ) {
			return L.divIcon( {
				className: 'lp-pin-wrap',
				html: '<span class="lp-pin lp-pin--dot" aria-hidden="true"><svg viewBox="0 0 34 42" width="34" height="42"><path d="M17 41s14-13.2 14-24A14 14 0 0 0 3 17c0 10.8 14 24 14 24z" fill="var(--lp-pin-ban, #C02038)" stroke="#fff" stroke-width="2"/><circle cx="17" cy="17" r="5.5" fill="#fff"/></svg></span>',
				iconSize: [ 34, 42 ],
				iconAnchor: [ 17, 40 ],
				popupAnchor: [ 0, -38 ],
			} );
		}
		return L.divIcon( {
			className: 'lp-pin-wrap',
			html: '<span class="lp-pin lp-pin--' + esc( m.kind ) + '">' + esc( m.short ) + '</span>',
			iconSize: null,
			iconAnchor: [ 0, 0 ],
			popupAnchor: [ 0, -30 ],
		} );
	}

	function card( m, labels ) {
		return (
			'<a class="lp-mcard" href="' + esc( m.url ) + '">' +
			( m.img ? '<img class="lp-mcard__img" src="' + esc( m.img ) + '" alt="" loading="lazy" width="300" height="200">' : '' ) +
			'<span class="lp-mcard__body">' +
			'<span class="lp-mcard__title">' + esc( m.title ) + '</span>' +
			( m.price ? '<span class="lp-mcard__price">' + esc( m.price ) + '</span>' : '' ) +
			( m.meta ? '<span class="lp-mcard__meta">' + esc( m.meta ) + '</span>' : '' ) +
			'<span class="lp-mcard__more">' + esc( labels.view ) + ' →</span>' +
			'</span></a>'
		);
	}

	function fit( map, markers, single, el ) {
		var bounds = markers.map( function ( m ) {
			return [ m.lat, m.lng ];
		} );
		if ( ! bounds.length ) {
			map.setView( JSON.parse( el.dataset.center || '[10.7769,106.7009]' ), +el.dataset.zoom || 12 );
		} else if ( bounds.length === 1 ) {
			map.setView( bounds[ 0 ], single ? 16 : 15 );
		} else {
			map.fitBounds( bounds, { padding: [ 48, 48 ], maxZoom: 15 } );
		}
	}

	function init( el ) {
		if ( el._lp ) {
			return el._lp;
		}
		var state = {
			markers: JSON.parse( el.dataset.markers || '[]' ),
			labels: JSON.parse( el.dataset.labels || '{}' ),
		};
		var single = el.classList.contains( 'lp-lmap--single' );
		var cluster = el.dataset.cluster !== '0';
		el.innerHTML = '';

		var map = L.map( el, {
			scrollWheelZoom: false, // Không chiếm thao tác cuộn trang; bấm vào bản đồ mới bật.
			zoomControl: false,
		} );
		L.control.zoom( { position: 'bottomright' } ).addTo( map );
		window.LPMapBase.add( map, JSON.parse( el.dataset.base || '{}' ), el );

		// Gợi ý "Phóng to…" khi đang nhìn quá rộng.
		var hint = null;
		if ( ! single && state.labels.zoom ) {
			hint = document.createElement( 'p' );
			hint.className = 'lp-lmap__hint';
			hint.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01" stroke-linecap="round"/></svg><span></span>';
			hint.lastChild.textContent = state.labels.zoom;
			el.appendChild( hint );
			map.on( 'zoomend', function () {
				hint.hidden = map.getZoom() >= 11;
			} );
		}

		var layer = L.layerGroup().addTo( map );
		var empty = null;
		function pin( m ) {
			var mk = L.marker( [ m.lat, m.lng ], { icon: pinIcon( m, single ), title: m.title, riseOnHover: true } );
			if ( ! single ) {
				mk.bindPopup( card( m, state.labels ), { className: 'lp-mpopup', maxWidth: 260, minWidth: 240 } );
			}
			return mk;
		}
		// Gom các ghim nằm gần nhau (< 60px trên màn hình) thành một bong bóng số lượng.
		function draw() {
			layer.clearLayers();
			if ( single ) {
				state.markers.forEach( function ( m ) {
					layer.addLayer( pin( m ) );
				} );
				return;
			}
			var groups = [];
			state.markers.forEach( function ( m ) {
				var pt = map.latLngToLayerPoint( [ m.lat, m.lng ] );
				for ( var i = 0; cluster && i < groups.length; i++ ) {
					if ( groups[ i ].pt.distanceTo( pt ) < 60 ) {
						groups[ i ].items.push( m );
						return;
					}
				}
				groups.push( { pt: pt, items: [ m ] } );
			} );
			groups.forEach( function ( g ) {
				if ( g.items.length === 1 || map.getZoom() >= map.getMaxZoom() ) {
					g.items.forEach( function ( m ) {
						layer.addLayer( pin( m ) );
					} );
					return;
				}
				var gb = L.latLngBounds( g.items.map( function ( m ) {
					return [ m.lat, m.lng ];
				} ) );
				var n = g.items.length;
				var size = n < 10 ? 40 : 48;
				L.marker( gb.getCenter(), {
					icon: L.divIcon( {
						className: 'lp-cluster-wrap',
						html: '<span class="lp-cluster">' + n + '</span>',
						iconSize: [ size, size ],
						iconAnchor: [ size / 2, size / 2 ],
					} ),
					title: String( n ),
				} )
					.on( 'click', function () {
						map.fitBounds( gb, { padding: [ 60, 60 ], maxZoom: map.getMaxZoom() } );
					} )
					.addTo( layer );
			} );
		}
		function showEmpty() {
			if ( empty ) {
				empty.remove();
				empty = null;
			}
			if ( ! state.markers.length ) {
				empty = document.createElement( 'p' );
				empty.className = 'lp-lmap__empty';
				empty.textContent = state.labels.empty || '';
				el.appendChild( empty );
			}
		}

		fit( map, state.markers, single, el );
		draw();
		showEmpty();
		if ( hint ) {
			hint.hidden = map.getZoom() >= 11;
		}
		map.on( 'zoomend', draw );
		map.once( 'focus click', function () {
			map.scrollWheelZoom.enable();
		} );
		// Khung đổi kích thước (mở/đóng bản đồ, xoay màn hình…) thì vẽ lại.
		if ( 'ResizeObserver' in window ) {
			new ResizeObserver( function () {
				map.invalidateSize();
			} ).observe( el );
		}

		el._lp = {
			map: map,
			/** Thay danh sách ghim (sau khi lọc không tải lại trang). */
			update: function ( markers ) {
				state.markers = markers || [];
				map.closePopup();
				fit( map, state.markers, single, el );
				draw();
				showEmpty();
			},
			refit: function () {
				map.invalidateSize();
				fit( map, state.markers, single, el );
			},
		};
		return el._lp;
	}

	/* ---------- Nút "Xem bản đồ" / "Đóng bản đồ" */
	function setupToggle( wrap ) {
		var openBtn = wrap.querySelector( '[data-lp-map-open]' );
		var panel = wrap.querySelector( '.lp-mapwrap__panel' );
		var closeBtn = wrap.querySelector( '[data-lp-map-close]' );
		var el = panel.querySelector( '[data-lp-map]' );
		// Khu vực chia đôi: khung Elementor chứa bản đồ (danh sách bên trái, bản đồ bên phải).
		var scope = wrap.closest( '[data-lp-split]' ) || wrap.closest( '.e-con-inner' ) || wrap.closest( '.e-con' ) || wrap.parentElement;
		var widget = wrap.closest( '.elementor-widget' ) || wrap;
		// Vị trí nút: máy tính – cạnh phải thanh tìm kiếm; điện thoại/tablet – cạnh ô sắp xếp.
		var desk = window.matchMedia( '(min-width: 1025px)' );
		function placeBtn() {
			var form = scope.querySelector( 'form.lp-search' );
			var panel = form && form.querySelector( '.lp-search__panel' );
			var bar = scope.querySelector( '.lp-toolbar' );
			if ( desk.matches && panel ) {
				panel.after( openBtn );
				form.classList.add( 'has-mapbtn' );
			} else if ( bar ) {
				bar.appendChild( openBtn );
				if ( form ) {
					form.classList.remove( 'has-mapbtn' );
				}
			}
		}
		placeBtn();
		if ( desk.addEventListener ) {
			desk.addEventListener( 'change', placeBtn );
		}
		document.addEventListener( 'lp:listing-updated', placeBtn );

		function open( auto ) {
			panel.hidden = false;
			wrap.classList.add( 'is-open' );
			scope.classList.add( 'lp-mapsplit' );
			scope.style.setProperty( '--lp-split-rows', scope.children.length );
			widget.classList.add( 'lp-mapsplit__map' );
			document.documentElement.classList.add( 'lp-map-open' );
			openBtn.setAttribute( 'aria-expanded', 'true' );
			openBtn.hidden = true;
			var inst = init( el );
			requestAnimationFrame( function () {
				inst.refit();
			} );
			if ( auto !== true ) {
				closeBtn.focus( { preventScroll: true } );
			}
		}
		function close() {
			panel.hidden = true;
			wrap.classList.remove( 'is-open' );
			scope.classList.remove( 'lp-mapsplit' );
			widget.classList.remove( 'lp-mapsplit__map' );
			document.documentElement.classList.remove( 'lp-map-open' );
			openBtn.setAttribute( 'aria-expanded', 'false' );
			openBtn.hidden = false;
			openBtn.focus( { preventScroll: true } );
		}
		openBtn.addEventListener( 'click', open );

		/* Bộ lọc trên bản đồ toàn màn hình: mượn form tìm kiếm của trang đặt vào bảng trượt. */
		var fBtn = panel.querySelector( '[data-lp-map-filter]' );
		var fBox = panel.querySelector( '.lp-mapfilter' );
		var fBody = fBox && fBox.querySelector( '.lp-mapfilter__body' );
		var form = scope.querySelector( 'form.lp-search' );
		var holder = document.createComment( 'lp-search' );
		function countFilters() {
			if ( ! form || ! fBtn ) {
				return;
			}
			var n = 0;
			form.querySelectorAll( 'select, input[name="lp_kw"]' ).forEach( function ( f ) {
				if ( f.value && f.value.trim() ) {
					n++;
				}
			} );
			var b = fBtn.querySelector( '.lp-mapbtn__count' );
			b.textContent = n;
			b.hidden = ! n;
		}
		function openFilter() {
			form.parentNode.insertBefore( holder, form );
			fBody.appendChild( form );
			fBox.hidden = false;
			fBtn.setAttribute( 'aria-expanded', 'true' );
		}
		function closeFilter() {
			if ( fBox.hidden ) {
				return;
			}
			if ( holder.parentNode ) {
				holder.parentNode.insertBefore( form, holder );
				holder.remove();
			}
			fBox.hidden = true;
			fBtn.setAttribute( 'aria-expanded', 'false' );
			countFilters();
		}
		if ( fBtn && form ) {
			countFilters();
			fBtn.addEventListener( 'click', function () {
				if ( fBox.hidden ) {
					openFilter();
				} else {
					closeFilter();
				}
			} );
			fBox.querySelector( '[data-lp-mapfilter-close]' ).addEventListener( 'click', closeFilter );
			form.addEventListener( 'change', countFilters );
			// Bấm "Lọc kết quả" trong bảng: đóng bảng để xem ghim mới.
			form.addEventListener( 'submit', function () {
				setTimeout( closeFilter, 0 );
			} );
			closeBtn.addEventListener( 'click', closeFilter );
			document.addEventListener( 'lp:listing-updated', countFilters );
		} else if ( fBtn ) {
			fBtn.remove();
		}
		if ( wrap.dataset.open === '1' && desk.matches ) {
			open( true ); // Cài đặt "Mở sẵn bản đồ" (chỉ máy tính).
		}
		closeBtn.addEventListener( 'click', close );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && wrap.classList.contains( 'is-open' ) && window.matchMedia( '(max-width: 1024px)' ).matches ) {
				close();
			}
		} );
	}

	function boot() {
		document.querySelectorAll( '[data-lp-mapwrap]' ).forEach( setupToggle );
		var maps = Array.prototype.filter.call( document.querySelectorAll( '[data-lp-map]' ), function ( el ) {
			return ! el.closest( '[data-lp-mapwrap]' ); // Bản đồ ẩn chỉ khởi tạo khi bấm mở.
		} );
		if ( ! maps.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			maps.forEach( init );
			return;
		}
		var io = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( e ) {
					if ( e.isIntersecting ) {
						io.unobserve( e.target );
						init( e.target );
					}
				} );
			},
			{ rootMargin: '300px 0px' }
		);
		maps.forEach( function ( el ) {
			io.observe( el );
		} );
	}

	/** Gọi từ bộ lọc: cập nhật ghim của mọi bản đồ trên trang. */
	window.lpMapUpdate = function ( el, markers ) {
		el.dataset.markers = JSON.stringify( markers );
		if ( el._lp ) {
			el._lp.update( markers );
		}
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
