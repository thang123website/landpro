<?php
/**
 * Bản đồ (Leaflet + OpenStreetMap, miễn phí, không cần API key).
 * Viết lại từ plugin "etutor-map-picker" (Google Maps):
 *  - Admin: hộp "Vị trí trên bản đồ" – tìm địa chỉ có gợi ý, bấm bản đồ / kéo ghim để lấy tọa độ.
 *  - Frontend: [lp_map] – bản đồ nhà đất (mua / thuê) với ghim giá, bấm ghim xem thẻ nhà.
 *  - LP_Map::find_nearby() – tìm tin trong bán kính (công thức Haversine).
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Map {

	const POST_TYPES = array( 'property', 'project' );

	const OPTION = 'lp_map';

	/** Cấu hình mặc định (Nhà đất → Bản đồ). */
	public static function defaults() {
		return array(
			'provider'       => 'ofm',      // ofm | osm | maptiler | google | custom.
			'ofm_style'      => 'liberty',  // liberty | bright | positron.
			'maptiler_key'   => '',
			'maptiler_style' => 'streets-v2',
			'google_key'     => '',
			'google_api'     => 'js',       // js (Maps JavaScript API) | tiles (Map Tiles API).
			'google_type'    => 'roadmap',  // roadmap | satellite | terrain | hybrid.
			'google_style'   => 'default',  // default | silver | clean (chỉ bản đồ thường).
			'custom_type'    => 'raster',   // raster | vector.
			'custom_url'     => '',
			'custom_attr'    => '',
			'geocoder'       => 'photon',   // photon | maptiler | google.
			'center_lat'     => 10.7769,
			'center_lng'     => 106.7009,
			'zoom'           => 12,
			'height'         => 460,
			'cluster'        => '1',
			'color_ban'      => '#C02038',
			'color_rent'     => '#0F766E',
			'color_project'  => '#111111',
			// Bật / tắt theo trang.
			'show_listing'   => '1', // Trang Mua nhà / Thuê nhà (widget Elementor).
			'show_archive'   => '1', // Trang kết quả tìm kiếm, khu vực, loại nhà đất.
			'show_property'  => '1', // Chi tiết nhà đất.
			'show_project'   => '1', // Chi tiết dự án.
			'show_admin'     => '1', // Hộp "Vị trí trên bản đồ" khi sửa tin.
			'open_default'   => '',  // Mở sẵn bản đồ chia đôi khi vào trang (máy tính).
		);
	}

	/** Bản đồ có bật ở vị trí $where (listing | archive | property | project | admin) không. */
	public static function enabled( $where ) {
		$o = self::opts();
		return ! empty( $o[ 'show_' . $where ] );
	}

	/** Danh sách công tắc cho trang cài đặt. */
	public static function places() {
		return array(
			'show_listing'  => 'Trang Mua nhà / Thuê nhà (widget "Bản đồ nhà đất")',
			'show_archive'  => 'Trang kết quả tìm kiếm, trang khu vực, loại nhà đất',
			'show_property' => 'Trang chi tiết nhà đất',
			'show_project'  => 'Trang chi tiết dự án',
			'show_admin'    => 'Hộp "Vị trí trên bản đồ" khi sửa tin (tắt thì tọa độ cũ vẫn giữ)',
		);
	}

	public static function opts() {
		$o = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $o ) ? $o : array(), self::defaults() );
	}

	/** Tâm mặc định khi chưa có ghim. */
	public static function center() {
		$o = self::opts();
		return array( (float) $o['center_lat'], (float) $o['center_lng'] );
	}

	private static function lang() {
		return ( function_exists( 'pll_current_language' ) && 'en' === pll_current_language() ) ? 'en' : 'vi';
	}

	/** Thư viện MapLibre (nền vector) – chỉ tải khi bản đồ được mở. */
	private static function vector_lib() {
		return array(
			'css' => LP_URL . 'assets/vendor/maplibre/maplibre-gl.css?ver=4.7.1',
			'js'  => array(
				LP_URL . 'assets/vendor/maplibre/maplibre-gl.js?ver=4.7.1',
				LP_URL . 'assets/vendor/maplibre/leaflet-maplibre-gl.js?ver=0.0.22',
			),
		);
	}

	/** Nền ảnh miễn phí, dùng khi máy không có WebGL hoặc nguồn chính lỗi (key sai…). */
	private static function fallback() {
		return array(
			'type'        => 'raster',
			'url'         => 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
			'subdomains'  => 'abc',
			'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>, tiles: <a href="https://www.hotosm.org/" target="_blank" rel="noopener">HOT</a> / <a href="https://openstreetmap.fr/" target="_blank" rel="noopener">OSM France</a>',
			'maxZoom'     => 19,
		);
	}

	/** Danh sách nguồn bản đồ cho trang cài đặt. */
	public static function providers() {
		return array(
			'ofm'      => 'OpenFreeMap – miễn phí, không cần key (khuyên dùng)',
			'osm'      => 'OpenStreetMap HOT – miễn phí, không cần key (nền ảnh)',
			'maptiler' => 'MapTiler – cần key (miễn phí 100.000 lượt tải/tháng)',
			'google'   => 'Google Maps – cần key (Maps JavaScript API)',
			'custom'   => 'Tùy chỉnh – dán URL nền của nhà cung cấp khác',
		);
	}

	/**
	 * Cấu hình nền cho JS (map-base.js). Lọc thêm bằng filter "lp_map_base".
	 *
	 * @return array{type:string}
	 */
	public static function base_config() {
		$o    = self::opts();
		$lang = self::lang();
		$osm  = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>';
		$cfg  = null;

		switch ( $o['provider'] ) {
			case 'osm':
				$cfg = self::fallback();
				break;

			case 'maptiler':
				if ( $o['maptiler_key'] ) {
					$cfg = array(
						'type'        => 'vector',
						'style'       => sprintf( 'https://api.maptiler.com/maps/%s/style.json?key=%s', rawurlencode( $o['maptiler_style'] ), rawurlencode( $o['maptiler_key'] ) ),
						'attribution' => '<a href="https://www.maptiler.com/copyright/" target="_blank" rel="noopener">&copy; MapTiler</a> ' . $osm,
					);
				}
				break;

			case 'google':
				if ( $o['google_key'] && 'js' === $o['google_api'] ) {
					$cfg = array(
						'type'    => 'google',
						'maptype' => $o['google_type'],
						'styles'  => 'roadmap' === $o['google_type'] ? self::google_styles( $o['google_style'] ) : array(),
						'api'     => add_query_arg(
							array(
								'key'      => rawurlencode( $o['google_key'] ),
								'language' => 'en' === $lang ? 'en' : 'vi',
								'region'   => 'VN',
								'loading'  => 'async',
								'callback' => 'lpGoogleReady',
							),
							'https://maps.googleapis.com/maps/api/js'
						),
						'mutant'  => LP_URL . 'assets/vendor/googlemutant/Leaflet.GoogleMutant.js?ver=0.16.0',
					);
					break;
				}
				$session = 'tiles' === $o['google_api'] ? self::google_session() : '';
				if ( $session ) {
					$cfg = array(
						'type'        => 'raster',
						'url'         => 'https://tile.googleapis.com/v1/2dtiles/{z}/{x}/{y}?session=' . rawurlencode( $session ) . '&key=' . rawurlencode( $o['google_key'] ),
						'attribution' => 'Map data &copy;' . gmdate( 'Y' ) . ' Google',
						'maxZoom'     => 21,
						'google'      => 1,
					);
				}
				break;

			case 'custom':
				if ( $o['custom_url'] ) {
					$cfg = 'vector' === $o['custom_type']
						? array( 'type' => 'vector', 'style' => $o['custom_url'], 'attribution' => $o['custom_attr'] )
						: array( 'type' => 'raster', 'url' => $o['custom_url'], 'attribution' => $o['custom_attr'], 'subdomains' => 'abc', 'maxZoom' => 20 );
				}
				break;
		}

		if ( ! $cfg ) { // OpenFreeMap (mặc định) hoặc nguồn có key nhưng chưa nhập key.
			$cfg = array(
				'type'        => 'vector',
				'style'       => 'https://tiles.openfreemap.org/styles/' . ( in_array( $o['ofm_style'], array( 'liberty', 'bright', 'positron' ), true ) ? $o['ofm_style'] : 'liberty' ),
				'attribution' => '<a href="https://openfreemap.org" target="_blank" rel="noopener">OpenFreeMap</a> &copy; <a href="https://www.openmaptiles.org/" target="_blank" rel="noopener">OpenMapTiles</a> ' . $osm,
			);
		}
		$cfg['lang']     = $lang;
		$cfg['fallback'] = self::fallback();
		if ( 'vector' === $cfg['type'] ) {
			$cfg['lib'] = self::vector_lib();
		}
		return apply_filters( 'lp_map_base', $cfg, $o );
	}

	/**
	 * Phiên Google Map Tiles API (bắt buộc trước khi tải ô bản đồ), lưu tạm 10 ngày.
	 *
	 * @return string Mã phiên hoặc '' nếu key sai / chưa bật Map Tiles API.
	 */
	public static function google_session( $force = false ) {
		$o = self::opts();
		if ( ! $o['google_key'] ) {
			return '';
		}
		$lang = self::lang();
		$tkey = 'lp_gmt_' . md5( $o['google_key'] . $o['google_type'] . $lang );
		$s    = $force ? false : get_transient( $tkey );
		if ( false !== $s ) {
			return $s;
		}
		$body = array(
			'mapType'  => $o['google_type'],
			'language' => 'en' === $lang ? 'en-US' : 'vi-VN',
			'region'   => 'VN',
		);
		if ( 'terrain' === $o['google_type'] ) {
			$body['layerTypes'] = array( 'layerRoadmap' );
		}
		$res = wp_remote_post(
			'https://tile.googleapis.com/v1/createSession?key=' . rawurlencode( $o['google_key'] ),
			array(
				'timeout' => 8,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Referer'      => home_url( '/' ), // Key giới hạn theo website vẫn dùng được.
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$data = is_wp_error( $res ) ? null : json_decode( wp_remote_retrieve_body( $res ), true );
		$s    = ! empty( $data['session'] ) ? $data['session'] : '';
		// Lỗi thì nhớ 10 phút để không gọi lại liên tục.
		set_transient( $tkey, $s, $s ? 10 * DAY_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
		return $s;
	}

	/** Kiểu màu cho Google Maps JavaScript API. */
	public static function google_styles( $name ) {
		$hide_poi = array(
			array( 'featureType' => 'poi.business', 'stylers' => array( array( 'visibility' => 'off' ) ) ),
			array( 'featureType' => 'poi', 'elementType' => 'labels.icon', 'stylers' => array( array( 'visibility' => 'off' ) ) ),
			array( 'featureType' => 'transit', 'elementType' => 'labels.icon', 'stylers' => array( array( 'visibility' => 'off' ) ) ),
		);
		if ( 'clean' === $name ) {
			return $hide_poi;
		}
		if ( 'silver' === $name ) { // Xám bạc: ghim giá nổi bật trên nền trung tính.
			return array_merge(
				array(
					array( 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#f5f5f5' ) ) ),
					array( 'elementType' => 'labels.icon', 'stylers' => array( array( 'visibility' => 'off' ) ) ),
					array( 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#616161' ) ) ),
					array( 'elementType' => 'labels.text.stroke', 'stylers' => array( array( 'color' => '#f5f5f5' ) ) ),
					array( 'featureType' => 'administrative.land_parcel', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#bdbdbd' ) ) ),
					array( 'featureType' => 'poi', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#eeeeee' ) ) ),
					array( 'featureType' => 'poi', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#757575' ) ) ),
					array( 'featureType' => 'poi.park', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#e5e5e5' ) ) ),
					array( 'featureType' => 'road', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#ffffff' ) ) ),
					array( 'featureType' => 'road.arterial', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#757575' ) ) ),
					array( 'featureType' => 'road.highway', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#dadada' ) ) ),
					array( 'featureType' => 'road.highway', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#616161' ) ) ),
					array( 'featureType' => 'road.local', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#9e9e9e' ) ) ),
					array( 'featureType' => 'transit.line', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#e5e5e5' ) ) ),
					array( 'featureType' => 'water', 'elementType' => 'geometry', 'stylers' => array( array( 'color' => '#c9e3f2' ) ) ),
					array( 'featureType' => 'water', 'elementType' => 'labels.text.fill', 'stylers' => array( array( 'color' => '#9e9e9e' ) ) ),
				),
				$hide_poi
			);
		}
		return array();
	}

	/** Cấu hình tìm địa chỉ cho hộp bản đồ trong admin. */
	public static function geo_config() {
		$o   = self::opts();
		$geo = array( 'provider' => 'photon', 'lang' => 'vi' );
		if ( 'maptiler' === $o['geocoder'] && $o['maptiler_key'] ) {
			$geo = array( 'provider' => 'maptiler', 'key' => $o['maptiler_key'], 'lang' => 'vi' );
		} elseif ( 'google' === $o['geocoder'] && $o['google_key'] ) {
			$geo = array(
				'provider' => 'google',
				'ajax'     => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'lp_geocode' ),
			);
		}
		return $geo;
	}

	/** Tìm địa chỉ bằng Google Geocoding API phía máy chủ (key không lộ ra trình duyệt). */
	public static function ajax_geocode() {
		check_ajax_referer( 'lp_geocode' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		$o = self::opts();
		if ( ! $o['google_key'] ) {
			wp_send_json_error( 'no-key' );
		}
		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'search';
		$args = array( 'key' => $o['google_key'], 'language' => 'vi', 'region' => 'vn' );
		if ( 'reverse' === $mode ) {
			$args['latlng'] = (float) ( $_POST['lat'] ?? 0 ) . ',' . (float) ( $_POST['lng'] ?? 0 );
		} else {
			$args['address']    = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
			$args['components'] = 'country:VN';
		}
		$res  = wp_remote_get( add_query_arg( array_map( 'rawurlencode', $args ), 'https://maps.googleapis.com/maps/api/geocode/json' ), array( 'timeout' => 8, 'headers' => array( 'Referer' => home_url( '/' ) ) ) );
		$data = is_wp_error( $res ) ? array() : json_decode( wp_remote_retrieve_body( $res ), true );
		if ( empty( $data['results'] ) ) {
			wp_send_json_success( array() );
		}
		$out = array();
		foreach ( array_slice( $data['results'], 0, 6 ) as $r ) {
			$out[] = array(
				'label' => $r['formatted_address'],
				'lat'   => $r['geometry']['location']['lat'],
				'lng'   => $r['geometry']['location']['lng'],
			);
		}
		wp_send_json_success( $out );
	}

	/** Biến CSS màu ghim. */
	public static function color_style() {
		$o = self::opts();
		return sprintf(
			'--lp-pin-ban:%s;--lp-pin-rent:%s;--lp-pin-project:%s;',
			sanitize_hex_color( $o['color_ban'] ) ?: '#C02038',
			sanitize_hex_color( $o['color_rent'] ) ?: '#0F766E',
			sanitize_hex_color( $o['color_project'] ) ?: '#111111'
		);
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metabox' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 20, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_front_assets' ), 6 );
		add_shortcode( 'lp_map', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_ajax_lp_geocode', array( __CLASS__, 'ajax_geocode' ) );
	}

	public static function register_meta() {
		foreach ( self::POST_TYPES as $pt ) {
			foreach ( array( '_lp_lat', '_lp_lng' ) as $key ) {
				register_post_meta(
					$pt,
					$key,
					array(
						'type'              => 'number',
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => array( __CLASS__, 'sanitize_coord' ),
						'auth_callback'     => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		}
	}

	public static function sanitize_coord( $v ) {
		return is_numeric( $v ) ? round( (float) $v, 7 ) : '';
	}

	/** Tọa độ của bài: [lat, lng] hoặc null. */
	public static function coords( $post_id ) {
		$lat = get_post_meta( $post_id, '_lp_lat', true );
		$lng = get_post_meta( $post_id, '_lp_lng', true );
		if ( '' === $lat || '' === $lng || ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
			return null;
		}
		return array( (float) $lat, (float) $lng );
	}

	/* ================================================================ Admin */

	public static function add_metabox() {
		if ( ! self::enabled( 'admin' ) ) {
			return;
		}
		foreach ( self::POST_TYPES as $pt ) {
			add_meta_box( 'lp_map_box', 'Vị trí trên bản đồ', array( __CLASS__, 'render_metabox' ), $pt, 'normal', 'high' );
		}
	}

	public static function render_metabox( $post ) {
		$c = self::coords( $post->ID );
		wp_nonce_field( 'lp_map_save', 'lp_map_nonce' );
		?>
		<div class="lp-mapbox" data-lp-mapbox data-center="<?php echo esc_attr( wp_json_encode( $c ? $c : self::center() ) ); ?>" data-zoom="<?php echo (int) self::opts()['zoom']; ?>" data-has="<?php echo $c ? '1' : '0'; ?>" data-base="<?php echo esc_attr( wp_json_encode( self::base_config() ) ); ?>" data-geo="<?php echo esc_attr( wp_json_encode( self::geo_config() ) ); ?>">
			<div class="lp-mapbox__search">
				<label for="lp-map-q">Tìm địa chỉ</label>
				<input type="text" id="lp-map-q" class="regular-text" autocomplete="off" placeholder="VD: 123 Nguyễn Hữu Thọ, Quận 7, TP. Hồ Chí Minh" value="<?php echo esc_attr( lp_get( 'address', $post->ID ) ); ?>">
				<ul class="lp-mapbox__suggest" hidden></ul>
				<p class="description">Chọn gợi ý, bấm trực tiếp lên bản đồ hoặc kéo ghim. Ô "Địa chỉ" ở phần thông tin sẽ được điền theo vị trí bạn chọn.</p>
			</div>
			<div class="lp-mapbox__map"></div>
			<div class="lp-mapbox__coords">
				<label>Vĩ độ <input type="text" name="lp_lat" class="lp-mapbox__lat" value="<?php echo esc_attr( $c ? $c[0] : '' ); ?>" readonly></label>
				<label>Kinh độ <input type="text" name="lp_lng" class="lp-mapbox__lng" value="<?php echo esc_attr( $c ? $c[1] : '' ); ?>" readonly></label>
				<button type="button" class="button lp-mapbox__clear">Xóa vị trí</button>
			</div>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			return;
		}
		if ( ! isset( $_POST['lp_map_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lp_map_nonce'] ), 'lp_map_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$lat = isset( $_POST['lp_lat'] ) ? self::sanitize_coord( wp_unslash( $_POST['lp_lat'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$lng = isset( $_POST['lp_lng'] ) ? self::sanitize_coord( wp_unslash( $_POST['lp_lng'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( '' === $lat || '' === $lng ) {
			delete_post_meta( $post_id, '_lp_lat' );
			delete_post_meta( $post_id, '_lp_lng' );
		} else {
			update_post_meta( $post_id, '_lp_lat', $lat );
			update_post_meta( $post_id, '_lp_lng', $lng );
		}
	}

	public static function admin_assets() {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
			return;
		}
		wp_enqueue_style( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.js', array(), '1.9.4', true );
		wp_enqueue_script( 'lp-map-base', LP_URL . 'assets/js/map-base.js', array( 'leaflet' ), filemtime( LP_DIR . 'assets/js/map-base.js' ), true );
		wp_enqueue_style( 'lp-admin-map', LP_URL . 'assets/css/admin-map.css', array( 'leaflet' ), filemtime( LP_DIR . 'assets/css/admin-map.css' ) );
		wp_enqueue_script( 'lp-admin-map', LP_URL . 'assets/js/admin-map.js', array( 'lp-map-base' ), filemtime( LP_DIR . 'assets/js/admin-map.js' ), true );
	}

	/* ================================================================ Frontend */

	public static function register_front_assets() {
		wp_register_style( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );
		wp_register_script( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.js', array(), '1.9.4', array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_register_script( 'lp-map-base', LP_URL . 'assets/js/map-base.js', array( 'leaflet' ), filemtime( LP_DIR . 'assets/js/map-base.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_register_script( 'lp-map', LP_URL . 'assets/js/map.js', array( 'lp-map-base' ), filemtime( LP_DIR . 'assets/js/map.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	public static function enqueue() {
		wp_enqueue_style( 'leaflet' );
		wp_enqueue_script( 'lp-map' );
		LP_Render::enqueue();
	}

	/** Dữ liệu ghim cho một bài. */
	public static function marker( $post_id ) {
		$c = self::coords( $post_id );
		if ( ! $c ) {
			return null;
		}
		$is_project = 'project' === get_post_type( $post_id );
		$price      = $is_project ? (string) lp_get( 'price_text', $post_id ) : wp_strip_all_tags( lp_price_html( $post_id ) );
		$purpose    = $is_project ? 'project' : lp_base_slug( lp_first_term( $post_id, 'lp_purpose' ) );
		return array(
			'lat'   => $c[0],
			'lng'   => $c[1],
			'title' => get_the_title( $post_id ),
			'url'   => get_permalink( $post_id ),
			'img'   => (string) get_the_post_thumbnail_url( $post_id, 'medium' ),
			'price' => $price,
			'short' => $is_project ? lp_t( 'Dự án' ) : self::short_price( $post_id ),
			'kind'  => $purpose ? $purpose : 'ban',
			'meta'  => $is_project ? lp_location_text( $post_id ) : trim( ( lp_get( 'area', $post_id ) ? lp_number( lp_get( 'area', $post_id ) ) . ' m²' : '' ) . ( lp_get( 'bedrooms', $post_id ) ? ' · ' . lp_get( 'bedrooms', $post_id ) . ' ' . lp_t( 'PN' ) : '' ), ' ·' ),
		);
	}

	/** Giá rút gọn cho ghim: "3,85 tỷ", "18 tr". */
	private static function short_price( $post_id ) {
		$p = (float) lp_get( 'price', $post_id );
		if ( $p <= 0 ) {
			return lp_t( 'Thỏa thuận' );
		}
		if ( $p >= 1e9 ) {
			return sprintf( lp_t( '%s tỷ' ), lp_number( $p / 1e9 ) );
		}
		return sprintf( lp_t( '%s tr' ), lp_number( $p / 1e6 ) );
	}

	/**
	 * [lp_map purpose="ban|cho-thue" type="" location="" projects="no|yes" use_filters="yes|no" height="460"]
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'purpose'     => '',
				'type'        => '',
				'location'    => '',
				'projects'    => 'no',
				'use_filters' => 'yes',
				'height'      => 0, // 0 = theo cài đặt Nhà đất → Bản đồ.
				'limit'       => 200,
				'toggle'      => 'yes',
				'context'     => '', // listing | archive: tôn trọng công tắc bật/tắt trong cài đặt.
			),
			$atts
		);
		if ( $atts['context'] && ! self::enabled( $atts['context'] ) ) {
			return '';
		}
		$filters = 'yes' === $atts['use_filters'] ? LP_Query::request_filters() : array();
		$args    = LP_Query::property_args(
			array(
				'purpose'  => $atts['purpose'],
				'type'     => $atts['type'],
				'location' => $atts['location'],
				'per_page' => (int) $atts['limit'],
			),
			$filters
		);
		$args['fields']        = 'ids';
		$args['no_found_rows'] = true;
		$ids                   = get_posts( $args );
		if ( 'yes' === $atts['projects'] ) {
			$ids = array_merge( $ids, get_posts( array( 'post_type' => 'project', 'posts_per_page' => 50, 'fields' => 'ids' ) ) );
		}
		$markers = array_values( array_filter( array_map( array( __CLASS__, 'marker' ), $ids ) ) );
		$map = self::render( $markers, (int) $atts['height'] );
		if ( 'yes' !== $atts['toggle'] ) {
			return $map;
		}
		$id   = wp_unique_id( 'lp-mappanel-' );
		$icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4 3 6.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5L9 4z"/><path d="M9 4v13.5M15 6.5V20"/></svg>';
		$x    = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
		$fil  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/></svg>';
		// Bản đồ toàn màn hình (tablet/điện thoại): nút "Bộ lọc" mở bảng lọc ngay trên bản đồ.
		$filter = sprintf(
			'<button type="button" class="lp-mapbtn lp-mapbtn--filter" data-lp-map-filter aria-expanded="false">%1$s<span>%2$s</span><b class="lp-mapbtn__count" hidden></b></button><div class="lp-mapfilter" role="dialog" aria-label="%2$s" hidden><div class="lp-mapfilter__head"><strong>%2$s</strong><button type="button" class="lp-mapfilter__close" data-lp-mapfilter-close aria-label="%3$s">%4$s</button></div><div class="lp-mapfilter__body"></div></div>',
			$fil,
			esc_html( lp_t( 'Bộ lọc' ) ),
			esc_attr( lp_t( 'Đóng' ) ),
			$x
		);
		return sprintf(
			'<div class="lp-mapwrap" data-lp-mapwrap%7$s><button type="button" class="lp-mapbtn" data-lp-map-open aria-expanded="false" aria-controls="%1$s">%2$s<span>%3$s</span></button><div class="lp-mapwrap__panel" id="%1$s" hidden><button type="button" class="lp-mapbtn lp-mapbtn--close" data-lp-map-close>%4$s<span>%5$s</span></button>%8$s%6$s</div></div>',
			esc_attr( $id ),
			$icon,
			esc_html( lp_t( 'Xem bản đồ' ) ),
			$x,
			esc_html( lp_t( 'Đóng bản đồ' ) ),
			$map,
			self::opts()['open_default'] ? ' data-open="1"' : '',
			$filter
		);
	}

	/** In khung bản đồ với danh sách ghim. $height = 0: theo cài đặt. */
	public static function render( $markers, $height = 0, $single = false ) {
		self::enqueue();
		$o      = self::opts();
		$labels = array(
			'view'  => lp_t( 'Xem chi tiết' ),
			'empty' => lp_t( 'Chưa có bất động sản nào có vị trí trên bản đồ.' ),
			'zoom'  => lp_t( 'Phóng to bản đồ để xem thêm tin đăng' ),
		);
		$height = $height > 0 ? $height : (int) $o['height'];
		return sprintf(
			'<div class="lp-lmap%1$s" data-lp-map data-markers="%2$s" data-labels="%3$s" data-center="%4$s" data-zoom="%5$d" data-cluster="%6$s" data-base="%7$s" style="--lp-map-h:%8$dpx;%9$s" role="region" aria-label="%10$s"><noscript>%11$s</noscript></div>',
			$single ? ' lp-lmap--single' : '',
			esc_attr( wp_json_encode( $markers, JSON_UNESCAPED_UNICODE ) ),
			esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ),
			esc_attr( wp_json_encode( self::center() ) ),
			(int) $o['zoom'],
			$o['cluster'] ? '1' : '0',
			esc_attr( wp_json_encode( self::base_config() ) ),
			max( 240, $height ),
			esc_attr( self::color_style() ),
			esc_attr( lp_t( 'Bản đồ vị trí' ) ),
			esc_html( lp_t( 'Bật JavaScript để xem bản đồ.' ) )
		);
	}

	/* ================================================================ Tìm gần (Haversine) */

	/** Khoảng cách (km) giữa 2 tọa độ. */
	public static function distance( $lat1, $lng1, $lat2, $lng2 ) {
		$r    = 6371;
		$dlat = deg2rad( $lat2 - $lat1 );
		$dlng = deg2rad( $lng2 - $lng1 );
		$a    = sin( $dlat / 2 ) ** 2 + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dlng / 2 ) ** 2;
		return $r * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
	}

	/**
	 * Tin trong bán kính $radius_km quanh (lat, lng), sắp theo khoảng cách.
	 *
	 * @return array<int, array{id:int, distance:float}>
	 */
	public static function find_nearby( $post_type, $lat, $lng, $radius_km = 5.0, $limit = 12, $exclude = 0 ) {
		global $wpdb;
		// Lọc thô theo khung vuông rồi tính chính xác bằng Haversine.
		$dlat = $radius_km / 111.0;
		$dlng = $radius_km / ( 111.0 * max( 0.1, cos( deg2rad( $lat ) ) ) );
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT p.ID, la.meta_value AS lat, lo.meta_value AS lng
				FROM {$wpdb->posts} p
				JOIN {$wpdb->postmeta} la ON la.post_id = p.ID AND la.meta_key = '_lp_lat'
				JOIN {$wpdb->postmeta} lo ON lo.post_id = p.ID AND lo.meta_key = '_lp_lng'
				WHERE p.post_type = %s AND p.post_status = 'publish' AND p.ID <> %d
				AND CAST(la.meta_value AS DECIMAL(10,7)) BETWEEN %f AND %f
				AND CAST(lo.meta_value AS DECIMAL(10,7)) BETWEEN %f AND %f",
				$post_type,
				(int) $exclude,
				$lat - $dlat,
				$lat + $dlat,
				$lng - $dlng,
				$lng + $dlng
			)
		);
		$out = array();
		foreach ( $rows as $r ) {
			$d = self::distance( $lat, $lng, (float) $r->lat, (float) $r->lng );
			if ( $d <= $radius_km ) {
				$out[] = array( 'id' => (int) $r->ID, 'distance' => round( $d, 2 ) );
			}
		}
		usort(
			$out,
			function ( $a, $b ) {
				return $a['distance'] <=> $b['distance'];
			}
		);
		return array_slice( $out, 0, $limit );
	}
}
