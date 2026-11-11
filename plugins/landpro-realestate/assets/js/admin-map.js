/**
 * LandPro – hộp "Vị trí trên bản đồ" trong trang sửa bài.
 * Tìm địa chỉ có gợi ý, bấm bản đồ hoặc kéo ghim để đặt vị trí; tọa độ ghi vào ô ẩn.
 * Nền bản đồ và dịch vụ tìm địa chỉ theo Nhà đất → Bản đồ (map-base.js).
 */
( function () {
	'use strict';
	var box = document.querySelector( '[data-lp-mapbox]' );
	if ( ! box || typeof L === 'undefined' || ! window.LPMapBase ) {
		return;
	}

	var q = box.querySelector( '#lp-map-q' );
	var list = box.querySelector( '.lp-mapbox__suggest' );
	var latEl = box.querySelector( '.lp-mapbox__lat' );
	var lngEl = box.querySelector( '.lp-mapbox__lng' );
	var addrEl = document.getElementById( 'lp_address' );
	var canvas = box.querySelector( '.lp-mapbox__map' );
	var center = JSON.parse( box.dataset.center );
	var geo = JSON.parse( box.dataset.geo || '{}' );
	var has = box.dataset.has === '1';

	var map = L.map( canvas ).setView( center, has ? 16 : +box.dataset.zoom || 12 );
	window.LPMapBase.add( map, JSON.parse( box.dataset.base || '{}' ), canvas );

	var marker = null;

	function setCoords( lat, lng ) {
		latEl.value = lat === '' ? '' : Number( lat ).toFixed( 7 );
		lngEl.value = lng === '' ? '' : Number( lng ).toFixed( 7 );
	}

	function place( lat, lng, zoom ) {
		if ( ! marker ) {
			marker = L.marker( [ lat, lng ], { draggable: true } ).addTo( map );
			marker.on( 'dragend', function () {
				var p = marker.getLatLng();
				setCoords( p.lat, p.lng );
				reverse( p.lat, p.lng );
			} );
		} else {
			marker.setLatLng( [ lat, lng ] );
		}
		setCoords( lat, lng );
		if ( zoom ) {
			map.setView( [ lat, lng ], zoom );
		}
	}

	function fillAddress( text ) {
		if ( ! text ) {
			return;
		}
		if ( addrEl ) {
			addrEl.value = text;
		}
		q.value = text;
	}

	function reverse( lat, lng ) {
		window.LPMapBase.reverse( geo, lat, lng ).then( fillAddress ).catch( function () {} );
	}

	// Gợi ý địa chỉ (chờ người dùng ngừng gõ 500ms để không gửi quá nhiều yêu cầu).
	var timer = null;
	var results = [];
	function hideList() {
		list.hidden = true;
		list.innerHTML = '';
	}
	function search( text ) {
		window.LPMapBase.search( geo, text, map.getCenter() )
			.then( function ( items ) {
				results = items || [];
				list.innerHTML = '';
				if ( ! results.length ) {
					hideList();
					return;
				}
				results.forEach( function ( it, i ) {
					var li = document.createElement( 'li' );
					li.textContent = it.label;
					li.dataset.i = i;
					list.appendChild( li );
				} );
				list.hidden = false;
			} )
			.catch( hideList );
	}
	function choose( i ) {
		var it = results[ i ];
		if ( ! it ) {
			return;
		}
		place( it.lat, it.lng, 17 );
		fillAddress( it.label );
		hideList();
	}

	q.addEventListener( 'input', function () {
		clearTimeout( timer );
		var t = q.value.trim();
		if ( t.length < 4 ) {
			hideList();
			return;
		}
		timer = setTimeout( function () {
			search( t );
		}, 500 );
	} );
	q.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault(); // Không gửi form bài viết.
			if ( results[ 0 ] && ! list.hidden ) {
				choose( 0 );
			} else if ( q.value.trim() ) {
				search( q.value.trim() );
			}
		} else if ( e.key === 'Escape' ) {
			hideList();
		}
	} );
	list.addEventListener( 'mousedown', function ( e ) {
		var li = e.target.closest( 'li' );
		if ( li ) {
			e.preventDefault();
			choose( +li.dataset.i );
		}
	} );
	q.addEventListener( 'blur', function () {
		setTimeout( hideList, 150 );
	} );

	map.on( 'click', function ( e ) {
		place( e.latlng.lat, e.latlng.lng );
		reverse( e.latlng.lat, e.latlng.lng );
	} );

	box.querySelector( '.lp-mapbox__clear' ).addEventListener( 'click', function () {
		if ( marker ) {
			map.removeLayer( marker );
			marker = null;
		}
		setCoords( '', '' );
	} );

	if ( has ) {
		place( center[ 0 ], center[ 1 ] );
	}
	// Hộp meta có thể đang thu gọn / bố cục chưa ổn định lúc tải trang: vẽ lại khi khung đổi cỡ.
	if ( 'ResizeObserver' in window ) {
		new ResizeObserver( function () {
			map.invalidateSize();
		} ).observe( canvas );
	}
	window.addEventListener( 'load', function () {
		map.invalidateSize();
		if ( marker ) {
			map.setView( marker.getLatLng(), map.getZoom() );
		}
	} );
} )();
