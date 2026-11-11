/**
 * LandPro – lớp nền bản đồ & tìm địa chỉ dùng chung (trang ngoài + admin + xem trước cài đặt).
 * Cấu hình do PHP tạo (LP_Map::base_config / geo_config), nhà cung cấp chọn ở Nhà đất → Bản đồ.
 *
 * window.LPMapBase.add( map, cfg, el )  – gắn nền (vector MapLibre hoặc ảnh), tự quay về nền dự phòng khi lỗi.
 * window.LPMapBase.search( geo, q, center ) → Promise<[{label, lat, lng}]>
 * window.LPMapBase.reverse( geo, lat, lng ) → Promise<string>
 */
( function () {
	'use strict';
	if ( typeof L === 'undefined' ) {
		return;
	}

	/* ---------------------------------------------------------------- Nền bản đồ */
	var libPromise = null;

	function webgl() {
		try {
			var c = document.createElement( 'canvas' );
			return !! ( window.WebGLRenderingContext && ( c.getContext( 'webgl2' ) || c.getContext( 'webgl' ) ) );
		} catch ( e ) {
			return false;
		}
	}

	function loadScript( src ) {
		return new Promise( function ( ok, fail ) {
			var s = document.createElement( 'script' );
			s.src = src;
			s.onload = ok;
			s.onerror = fail;
			document.head.appendChild( s );
		} );
	}

	/** Thư viện MapLibre chỉ tải khi cần nền vector. */
	function loadLib( lib ) {
		if ( window.maplibregl && L.maplibreGL ) {
			return Promise.resolve();
		}
		if ( ! libPromise ) {
			var link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.href = lib.css;
			document.head.appendChild( link );
			libPromise = lib.js.reduce( function ( p, src ) {
				return p.then( function () {
					return loadScript( src );
				} );
			}, Promise.resolve() );
		}
		return libPromise;
	}

	/** Tên địa danh theo ngôn ngữ trang, không có thì chữ Latin (tránh chữ Trung/Thái/Lào khi thu nhỏ). */
	function localize( gl, lang ) {
		var field = [ 'coalesce', [ 'get', 'name:' + lang ], [ 'get', 'name:latin' ], [ 'get', 'name' ] ];
		( gl.getStyle().layers || [] ).forEach( function ( l ) {
			if ( l.type !== 'symbol' || ! l.layout || ! l.layout[ 'text-field' ] ) {
				return;
			}
			if ( JSON.stringify( l.layout[ 'text-field' ] ).indexOf( 'name' ) === -1 ) {
				return; // Biển số đường (ref), mũi tên một chiều…
			}
			try {
				gl.setLayoutProperty( l.id, 'text-field', field );
			} catch ( e ) {}
		} );
	}

	function raster( map, r ) {
		return L.tileLayer( r.url, {
			subdomains: r.subdomains || 'abc',
			attribution: r.attribution || '',
			maxZoom: r.maxZoom || 19,
		} ).addTo( map );
	}

	/* Google Maps JavaScript API (qua GoogleMutant): tải một lần, key lỗi thì mọi bản đồ quay về nền miễn phí. */
	var gPromise = null;
	var gLayers = [];
	var gFailed = false;
	function loadGoogle( cfg ) {
		if ( ! gPromise ) {
			gPromise = new Promise( function ( ok, fail ) {
				if ( window.google && window.google.maps && window.google.maps.Map ) {
					ok();
					return;
				}
				window.lpGoogleReady = ok;
				var s = document.createElement( 'script' );
				s.src = cfg.api;
				s.async = true;
				s.onerror = fail;
				document.head.appendChild( s );
			} ).then( function () {
				return L.gridLayer && L.gridLayer.googleMutant ? null : loadScript( cfg.mutant );
			} );
			// Google gọi hàm toàn cục này khi key sai / chưa bật API / website không được phép.
			var prev = window.gm_authFailure;
			window.gm_authFailure = function () {
				gFailed = true;
				gLayers.forEach( function ( g ) {
					g.map.removeLayer( g.layer );
					g.fallback();
				} );
				gLayers = [];
				document.dispatchEvent( new CustomEvent( 'lp:google-auth-failed' ) );
				if ( typeof prev === 'function' ) {
					prev();
				}
			};
		}
		return gPromise;
	}

	function add( map, cfg, el ) {
		cfg = cfg || {};
		var fallback = function () {
			if ( el ) {
				el.classList.remove( 'is-vector', 'is-google' );
			}
			raster( map, cfg.fallback || cfg );
		};
		if ( cfg.type === 'google' ) {
			if ( gFailed ) {
				fallback();
				return;
			}
			if ( el ) {
				el.classList.add( 'is-google' );
			}
			loadGoogle( cfg )
				.then( function () {
					var layer = L.gridLayer.googleMutant( { type: cfg.maptype || 'roadmap', styles: cfg.styles || [], maxZoom: 21 } ).addTo( map );
					gLayers.push( { map: map, layer: layer, fallback: fallback } );
				} )
				.catch( fallback );
			return;
		}
		if ( cfg.type !== 'vector' ) {
			raster( map, cfg );
			return;
		}
		if ( ! webgl() ) {
			fallback();
			return;
		}
		if ( el ) {
			el.classList.add( 'is-vector' );
		}
		loadLib( cfg.lib )
			.then( function () {
				var layer = L.maplibreGL( { style: cfg.style, attribution: cfg.attribution } ).addTo( map );
				var gl = layer.getMaplibreMap();
				var done = function () {
					if ( cfg.localize !== false ) {
						localize( gl, cfg.lang || 'vi' );
					}
				};
				gl.on( 'error', function ( e ) {
					// Key sai / hết hạn mức: style không tải được thì dùng nền dự phòng.
					if ( e && e.error && /style|401|403/i.test( String( e.error.message || e.error.status ) ) && ! gl._lpFailed ) {
						gl._lpFailed = true;
						map.removeLayer( layer );
						fallback();
					}
				} );
				if ( gl.isStyleLoaded() ) {
					done();
				} else {
					gl.once( 'style.load', done );
				}
			} )
			.catch( fallback );
	}

	/* ---------------------------------------------------------------- Tìm địa chỉ */
	var VN_BBOX = [ 102.1, 8.4, 109.6, 23.4 ];

	function json( url, opts ) {
		return fetch( url, opts || { headers: { Accept: 'application/json' } } ).then( function ( r ) {
			if ( ! r.ok ) {
				throw new Error( r.status );
			}
			return r.json();
		} );
	}

	function uniqJoin( parts ) {
		var seen = {};
		return parts
			.filter( function ( x ) {
				if ( ! x || seen[ x ] ) {
					return false;
				}
				seen[ x ] = 1;
				return true;
			} )
			.join( ', ' );
	}

	function photonLabel( f ) {
		var p = f.properties || {};
		var street = [ p.housenumber, p.street ].filter( Boolean ).join( ' ' );
		return uniqJoin( [ p.name !== p.street ? p.name : '', street, p.locality, p.district, p.city, p.county, p.state ] );
	}

	function stripCountry( s ) {
		return String( s || '' ).replace( /,\s*\d{5,6}(?=,|$)/, '' ).replace( /,\s*(Việt Nam|Vietnam|Viet Nam)$/i, '' );
	}

	var providers = {
		photon: {
			search: function ( g, q, c ) {
				var p = new URLSearchParams( { q: q, limit: 6, bbox: VN_BBOX.join( ',' ) } );
				if ( c ) {
					p.set( 'lat', c.lat );
					p.set( 'lon', c.lng );
				}
				return json( 'https://photon.komoot.io/api/?' + p ).then( function ( d ) {
					return ( d.features || [] ).map( function ( f ) {
						return { label: photonLabel( f ), lat: f.geometry.coordinates[ 1 ], lng: f.geometry.coordinates[ 0 ] };
					} );
				} );
			},
			reverse: function ( g, lat, lng ) {
				return json( 'https://photon.komoot.io/reverse?lat=' + lat + '&lon=' + lng ).then( function ( d ) {
					return d.features && d.features[ 0 ] ? photonLabel( d.features[ 0 ] ) : '';
				} );
			},
		},
		maptiler: {
			search: function ( g, q, c ) {
				var p = new URLSearchParams( { key: g.key, country: 'vn', language: g.lang || 'vi', limit: 6 } );
				if ( c ) {
					p.set( 'proximity', c.lng + ',' + c.lat );
				}
				return json( 'https://api.maptiler.com/geocoding/' + encodeURIComponent( q ) + '.json?' + p ).then( function ( d ) {
					return ( d.features || [] ).map( function ( f ) {
						return { label: stripCountry( f.place_name ), lat: f.center[ 1 ], lng: f.center[ 0 ] };
					} );
				} );
			},
			reverse: function ( g, lat, lng ) {
				return json( 'https://api.maptiler.com/geocoding/' + lng + ',' + lat + '.json?key=' + encodeURIComponent( g.key ) + '&language=' + ( g.lang || 'vi' ) ).then( function ( d ) {
					return d.features && d.features[ 0 ] ? stripCountry( d.features[ 0 ].place_name ) : '';
				} );
			},
		},
		// Google: gọi qua máy chủ WordPress để không lộ key tìm địa chỉ.
		google: {
			search: function ( g, q ) {
				return ajax( g, { mode: 'search', q: q } );
			},
			reverse: function ( g, lat, lng ) {
				return ajax( g, { mode: 'reverse', lat: lat, lng: lng } ).then( function ( list ) {
					return list[ 0 ] ? list[ 0 ].label : '';
				} );
			},
		},
	};

	function ajax( g, params ) {
		var body = new URLSearchParams( params );
		body.set( 'action', 'lp_geocode' );
		body.set( '_wpnonce', g.nonce );
		return json( g.ajax, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( d ) {
			if ( ! d.success ) {
				throw new Error( d.data || 'geocode' );
			}
			return ( d.data || [] ).map( function ( r ) {
				r.label = stripCountry( r.label );
				return r;
			} );
		} );
	}

	function pick( geo ) {
		return providers[ geo && geo.provider ] || providers.photon;
	}

	window.LPMapBase = {
		add: add,
		search: function ( geo, q, center ) {
			return pick( geo ).search( geo || {}, q, center );
		},
		reverse: function ( geo, lat, lng ) {
			return pick( geo ).reverse( geo || {}, lat, lng );
		},
	};
} )();
