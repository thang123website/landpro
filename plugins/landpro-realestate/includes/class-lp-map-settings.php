<?php
/**
 * Trang cài đặt: Nhà đất → Bản đồ.
 * Chọn nguồn bản đồ (miễn phí hoặc có key), dịch vụ tìm địa chỉ, vị trí mặc định, màu ghim; có bản đồ xem trước.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Map_Settings {

	const PAGE = 'lp-map-settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page( 'edit.php?post_type=property', 'Cài đặt bản đồ', 'Bản đồ', 'manage_options', self::PAGE, array( __CLASS__, 'page' ) );
	}

	public static function register() {
		register_setting(
			'lp_map',
			LP_Map::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => LP_Map::defaults(),
			)
		);
	}

	public static function sanitize( $in ) {
		$d   = LP_Map::defaults();
		$in  = is_array( $in ) ? $in : array();
		$out = array();
		$pick = function ( $key, $allowed ) use ( $in, $d ) {
			return isset( $in[ $key ] ) && in_array( $in[ $key ], $allowed, true ) ? $in[ $key ] : $d[ $key ];
		};
		$out['provider']       = $pick( 'provider', array_keys( LP_Map::providers() ) );
		$out['ofm_style']      = $pick( 'ofm_style', array_keys( self::ofm_styles() ) );
		$out['maptiler_style'] = $pick( 'maptiler_style', array_keys( self::maptiler_styles() ) );
		$out['google_api']     = $pick( 'google_api', array( 'js', 'tiles' ) );
		$out['google_type']    = $pick( 'google_type', array( 'roadmap', 'satellite', 'terrain', 'hybrid' ) );
		$out['google_style']   = $pick( 'google_style', array( 'default', 'silver', 'clean' ) );
		$out['custom_type']    = $pick( 'custom_type', array( 'raster', 'vector' ) );
		$out['geocoder']       = $pick( 'geocoder', array( 'photon', 'maptiler', 'google' ) );
		foreach ( array( 'maptiler_key', 'google_key' ) as $k ) {
			$out[ $k ] = isset( $in[ $k ] ) ? preg_replace( '/[^A-Za-z0-9_\-]/', '', $in[ $k ] ) : '';
		}
		$out['custom_url']  = isset( $in['custom_url'] ) ? esc_url_raw( str_replace( array( '{s}', '{z}', '{x}', '{y}', '{r}' ), array( '__S__', '__Z__', '__X__', '__Y__', '__R__' ), trim( $in['custom_url'] ) ) ) : '';
		$out['custom_url']  = str_replace( array( '__S__', '__Z__', '__X__', '__Y__', '__R__' ), array( '{s}', '{z}', '{x}', '{y}', '{r}' ), $out['custom_url'] );
		$out['custom_attr'] = isset( $in['custom_attr'] ) ? wp_kses( $in['custom_attr'], array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ) : '';
		$lat                = isset( $in['center_lat'] ) ? (float) $in['center_lat'] : $d['center_lat'];
		$lng                = isset( $in['center_lng'] ) ? (float) $in['center_lng'] : $d['center_lng'];
		$out['center_lat']  = $lat >= -90 && $lat <= 90 ? round( $lat, 6 ) : $d['center_lat'];
		$out['center_lng']  = $lng >= -180 && $lng <= 180 ? round( $lng, 6 ) : $d['center_lng'];
		$out['zoom']        = min( 18, max( 3, (int) ( $in['zoom'] ?? $d['zoom'] ) ) );
		$out['height']      = min( 900, max( 240, (int) ( $in['height'] ?? $d['height'] ) ) );
		$out['cluster']     = empty( $in['cluster'] ) ? '' : '1';
		foreach ( array_merge( array_keys( LP_Map::places() ), array( 'open_default' ) ) as $k ) {
			$out[ $k ] = empty( $in[ $k ] ) ? '' : '1';
		}
		foreach ( array( 'color_ban', 'color_rent', 'color_project' ) as $k ) {
			$out[ $k ] = sanitize_hex_color( $in[ $k ] ?? '' ) ?: $d[ $k ];
		}
		// Đổi key / kiểu Google thì tạo phiên mới.
		delete_transient( 'lp_gmt_' . md5( $out['google_key'] . $out['google_type'] . 'vi' ) );
		delete_transient( 'lp_gmt_' . md5( $out['google_key'] . $out['google_type'] . 'en' ) );
		return $out;
	}

	public static function ofm_styles() {
		return array(
			'liberty'  => 'Liberty – nhiều màu, giống Google Maps',
			'bright'   => 'Bright – tươi sáng, rõ đường',
			'positron' => 'Positron – xám nhạt tối giản',
		);
	}

	public static function maptiler_styles() {
		return array(
			'streets-v2'   => 'Streets – đường phố (giống Google)',
			'basic-v2'     => 'Basic – đơn giản',
			'bright-v2'    => 'Bright – tươi sáng',
			'pastel'       => 'Pastel – màu nhẹ',
			'dataviz'      => 'Dataviz – xám nhạt, làm nổi ghim',
			'outdoor-v2'   => 'Outdoor – địa hình',
			'hybrid'       => 'Satellite hybrid – vệ tinh có tên đường',
		);
	}

	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		// Bản đồ xem trước dùng đúng mã của trang ngoài.
		wp_enqueue_style( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );
		wp_enqueue_style( 'lp-frontend', LP_URL . 'assets/css/frontend.css', array(), filemtime( LP_DIR . 'assets/css/frontend.css' ) );
		wp_enqueue_script( 'leaflet', LP_URL . 'assets/vendor/leaflet/leaflet.js', array(), '1.9.4', true );
		wp_enqueue_script( 'lp-map-base', LP_URL . 'assets/js/map-base.js', array( 'leaflet' ), filemtime( LP_DIR . 'assets/js/map-base.js' ), true );
		wp_enqueue_script( 'lp-map', LP_URL . 'assets/js/map.js', array( 'lp-map-base' ), filemtime( LP_DIR . 'assets/js/map.js' ), true );
	}

	/** Kiểm tra nguồn đang chọn sau khi lưu: [loại notice, nội dung]. */
	private static function check( $o ) {
		switch ( $o['provider'] ) {
			case 'maptiler':
				if ( ! $o['maptiler_key'] ) {
					return array( 'warning', 'Chưa nhập key MapTiler – đang dùng OpenFreeMap miễn phí thay thế.' );
				}
				$r = wp_remote_get( 'https://api.maptiler.com/maps/' . rawurlencode( $o['maptiler_style'] ) . '/style.json?key=' . rawurlencode( $o['maptiler_key'] ), array( 'timeout' => 8, 'headers' => array( 'Referer' => home_url( '/' ) ) ) );
				$c = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
				return 200 === $c
					? array( 'success', 'Key MapTiler hợp lệ.' )
					: array( 'error', sprintf( 'Key MapTiler không dùng được (mã %s). Kiểm tra key và danh sách website được phép trong MapTiler Cloud. Bản đồ đang tự dùng nền miễn phí dự phòng.', $c ? $c : 'mạng' ) );
			case 'google':
				if ( ! $o['google_key'] ) {
					return array( 'warning', 'Chưa nhập key Google – đang dùng OpenFreeMap miễn phí thay thế.' );
				}
				if ( 'js' === $o['google_api'] ) {
					// Key Maps JavaScript API thường giới hạn theo website nên chỉ kiểm tra được trong trình duyệt (bản xem trước bên dưới).
					return array( 'info', 'Đã lưu. Key Google (Maps JavaScript API) được kiểm tra ở bản đồ xem trước bên dưới – nếu key lỗi sẽ hiện thông báo và bản đồ tự dùng nền miễn phí.' );
				}
				return LP_Map::google_session( true )
					? array( 'success', 'Key Google hợp lệ, đã bật Map Tiles API.' )
					: array( 'error', 'Key Google không dùng được. Cần bật "Map Tiles API" (và "Geocoding API" nếu tìm địa chỉ bằng Google) trong Google Cloud, bật thanh toán, và cho phép website này. Bản đồ đang dùng OpenFreeMap thay thế.' );
			case 'custom':
				return $o['custom_url'] ? null : array( 'warning', 'Chưa nhập URL nền tùy chỉnh – đang dùng OpenFreeMap.' );
		}
		return null;
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o    = LP_Map::opts();
		$name = function ( $k ) {
			return esc_attr( LP_Map::OPTION . '[' . $k . ']' );
		};
		$sel  = function ( $k, $choices ) use ( $o, $name ) {
			printf( '<select id="lpm-%1$s" name="%2$s">', esc_attr( $k ), $name( $k ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			foreach ( $choices as $v => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $v ), selected( $o[ $k ], $v, false ), esc_html( $label ) );
			}
			echo '</select>';
		};
		$txt  = function ( $k, $type = 'text', $attrs = '' ) use ( $o, $name ) {
			printf( '<input type="%1$s" id="lpm-%2$s" name="%3$s" value="%4$s" %5$s>', esc_attr( $type ), esc_attr( $k ), $name( $k ), esc_attr( $o[ $k ] ), $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
		};
		$notice = isset( $_GET['settings-updated'] ) ? self::check( $o ) : null; // phpcs:ignore WordPress.Security.NonceVerification
		?>
		<div class="wrap lpm">
			<h1>Cài đặt bản đồ</h1>
			<p class="description">Bản đồ hiển thị ở trang Mua nhà / Thuê nhà, chi tiết nhà đất, dự án và hộp "Vị trí trên bản đồ" khi sửa tin. Nguồn có key mà key lỗi sẽ tự quay về bản đồ miễn phí, website không bị trống bản đồ.</p>
			<?php if ( $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice[0] ); ?> is-dismissible"><p><?php echo esc_html( $notice[1] ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'lp_map' ); ?>

				<h2 class="title">Hiện bản đồ ở</h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Trang</th>
						<td><fieldset>
							<?php foreach ( LP_Map::places() as $k => $label ) : ?>
								<label style="display:block;margin-bottom:8px"><input type="checkbox" name="<?php echo $name( $k ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" value="1" <?php checked( $o[ $k ], '1' ); ?>> <?php echo esc_html( $label ); ?></label>
							<?php endforeach; ?>
						</fieldset></td>
					</tr>
					<tr>
						<th scope="row">Cách mở</th>
						<td><label><input type="checkbox" name="<?php echo $name( 'open_default' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" value="1" <?php checked( $o['open_default'], '1' ); ?>> Mở sẵn bản đồ chia đôi khi vào trang (máy tính)</label>
							<p class="description">Bỏ chọn: bản đồ ẩn, khách bấm nút "Xem bản đồ" để mở (trang tải nhẹ hơn). Điện thoại luôn bấm nút mới mở.</p></td>
					</tr>
				</table>

				<h2 class="title">Nguồn bản đồ</h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="lpm-provider">Nhà cung cấp</label></th>
						<td><?php $sel( 'provider', LP_Map::providers() ); ?></td>
					</tr>
					<tr data-lpm="ofm">
						<th scope="row"><label for="lpm-ofm_style">Kiểu bản đồ</label></th>
						<td><?php $sel( 'ofm_style', self::ofm_styles() ); ?>
							<p class="description">OpenFreeMap: miễn phí, không cần đăng ký, được dùng cho website thương mại.</p></td>
					</tr>
					<tr data-lpm="osm">
						<th scope="row">Ghi chú</th>
						<td><p class="description">Nền ảnh nhiều màu, nhẹ, chạy cả trên máy không hỗ trợ WebGL. Phù hợp site ít truy cập.</p></td>
					</tr>
					<tr data-lpm="maptiler">
						<th scope="row"><label for="lpm-maptiler_key">Key MapTiler</label></th>
						<td><?php $txt( 'maptiler_key', 'text', 'class="regular-text code" autocomplete="off"' ); ?>
							<p class="description">Lấy miễn phí tại <a href="https://cloud.maptiler.com/account/keys/" target="_blank" rel="noopener">cloud.maptiler.com</a> (100.000 lượt tải/tháng). Nên giới hạn key theo tên miền website.</p></td>
					</tr>
					<tr data-lpm="maptiler">
						<th scope="row"><label for="lpm-maptiler_style">Kiểu bản đồ</label></th>
						<td><?php $sel( 'maptiler_style', self::maptiler_styles() ); ?></td>
					</tr>
					<tr data-lpm="google">
						<th scope="row"><label for="lpm-google_key">Key Google Maps</label></th>
						<td><?php $txt( 'google_key', 'text', 'class="regular-text code" autocomplete="off"' ); ?>
							<p class="description">Tạo tại <a href="https://console.cloud.google.com/google/maps-apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>, bật <strong>Maps JavaScript API</strong> (thêm <strong>Geocoding API</strong> nếu tìm địa chỉ bằng Google). Google yêu cầu bật thanh toán; có hạn mức miễn phí hằng tháng.</p></td>
					</tr>
					<tr data-lpm="google">
						<th scope="row"><label for="lpm-google_api">Cách tải bản đồ</label></th>
						<td><?php $sel( 'google_api', array( 'js' => 'Maps JavaScript API (phổ biến – khuyên dùng)', 'tiles' => 'Map Tiles API (cần bật riêng trong Google Cloud)' ) ); ?></td>
					</tr>
					<tr data-lpm="google">
						<th scope="row"><label for="lpm-google_type">Kiểu bản đồ</label></th>
						<td><?php $sel( 'google_type', array( 'roadmap' => 'Bản đồ thường', 'satellite' => 'Vệ tinh', 'hybrid' => 'Vệ tinh có tên đường', 'terrain' => 'Địa hình' ) ); ?></td>
					</tr>
					<tr data-lpm="google">
						<th scope="row"><label for="lpm-google_style">Màu bản đồ</label></th>
						<td><?php $sel( 'google_style', array( 'default' => 'Mặc định của Google', 'silver' => 'Xám bạc – làm nổi ghim giá', 'clean' => 'Gọn – ẩn cửa hàng, địa điểm' ) ); ?>
							<p class="description">Áp dụng cho "Bản đồ thường" khi tải bằng Maps JavaScript API.</p></td>
					</tr>
					<tr data-lpm="custom">
						<th scope="row"><label for="lpm-custom_type">Loại nền</label></th>
						<td><?php $sel( 'custom_type', array( 'raster' => 'Ảnh (URL dạng .../{z}/{x}/{y}.png)', 'vector' => 'Vector (URL style.json)' ) ); ?></td>
					</tr>
					<tr data-lpm="custom">
						<th scope="row"><label for="lpm-custom_url">URL nền</label></th>
						<td><?php $txt( 'custom_url', 'text', 'class="large-text code" placeholder="https://{s}.example.com/{z}/{x}/{y}.png?key=..."' ); ?>
							<p class="description">Dán URL từ Stadia Maps, Thunderforest, Mapbox (style URL dạng https)… Giữ nguyên các ký hiệu {z} {x} {y} {s}.</p></td>
					</tr>
					<tr data-lpm="custom">
						<th scope="row"><label for="lpm-custom_attr">Ghi nguồn</label></th>
						<td><?php $txt( 'custom_attr', 'text', 'class="large-text" placeholder="&copy; OpenStreetMap contributors"' ); ?>
							<p class="description">Hầu hết nhà cung cấp bắt buộc hiện dòng ghi nguồn ở góc bản đồ.</p></td>
					</tr>
				</table>

				<h2 class="title">Tìm địa chỉ (khi sửa tin)</h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="lpm-geocoder">Dịch vụ</label></th>
						<td><?php $sel( 'geocoder', array( 'photon' => 'Photon – miễn phí, không cần key', 'maptiler' => 'MapTiler – dùng key MapTiler ở trên', 'google' => 'Google – dùng key Google ở trên (chính xác nhất cho số nhà VN)' ) ); ?>
							<p class="description">Key Google chỉ dùng phía máy chủ khi tìm địa chỉ, không lộ ra trình duyệt.</p></td>
					</tr>
				</table>

				<h2 class="title">Hiển thị</h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Vị trí mặc định</th>
						<td>
							<label>Vĩ độ <?php $txt( 'center_lat', 'number', 'step="any" class="small-text" style="width:120px"' ); ?></label>
							<label style="margin-left:12px">Kinh độ <?php $txt( 'center_lng', 'number', 'step="any" class="small-text" style="width:120px"' ); ?></label>
							<label style="margin-left:12px">Zoom <?php $txt( 'zoom', 'number', 'min="3" max="18" class="small-text"' ); ?></label>
							<p class="description">Dùng khi chưa có ghim nào và khi mở hộp chọn vị trí cho tin mới. Mặc định: trung tâm TP. Hồ Chí Minh.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpm-height">Chiều cao bản đồ (px)</label></th>
						<td><?php $txt( 'height', 'number', 'min="240" max="900" class="small-text"' ); ?>
							<p class="description">Áp dụng khi bản đồ nằm trong trang. Chế độ chia đôi màn hình luôn cao bằng màn hình.</p></td>
					</tr>
					<tr>
						<th scope="row">Gom ghim gần nhau</th>
						<td><label><input type="checkbox" name="<?php echo $name( 'cluster' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" value="1" <?php checked( $o['cluster'], '1' ); ?>> Gom thành bong bóng có số lượng khi thu nhỏ bản đồ</label></td>
					</tr>
					<tr>
						<th scope="row">Màu ghim giá</th>
						<td class="lpm-colors">
							<label>Mua bán <?php $txt( 'color_ban', 'text', 'class="lpm-color" data-default-color="#C02038"' ); ?></label>
							<label>Cho thuê <?php $txt( 'color_rent', 'text', 'class="lpm-color" data-default-color="#0F766E"' ); ?></label>
							<label>Dự án <?php $txt( 'color_project', 'text', 'class="lpm-color" data-default-color="#111111"' ); ?></label>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Lưu và xem trước' ); ?>
			</form>

			<h2 class="title">Xem trước</h2>
			<p class="description">Bản đồ theo cài đặt đã lưu, với các tin đang đăng.</p>
			<div class="notice notice-error inline" id="lpm-google-fail" hidden><p>Google từ chối key ở bản đồ xem trước: kiểm tra đã bật <strong>Maps JavaScript API</strong>, bật thanh toán và thêm website này (<?php echo esc_html( home_url( '/*' ) ); ?>) vào danh sách được phép của key. Bản đồ đang dùng nền miễn phí thay thế.</p></div>
			<?php
			$ids = get_posts(
				array(
					'post_type'      => array( 'property', 'project' ),
					'posts_per_page' => 60,
					'fields'         => 'ids',
					'lang'           => function_exists( 'pll_default_language' ) ? pll_default_language() : '',
				)
			);
			echo LP_Map::render( array_values( array_filter( array_map( array( 'LP_Map', 'marker' ), $ids ) ) ), 520 ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</div>
		<style>
			.lpm .form-table th { width: 220px; }
			.lpm-colors { display: flex; flex-wrap: wrap; gap: 20px; }
			.lpm-colors label { display: flex; align-items: center; gap: 8px; }
			.lpm .lp-lmap { max-width: 1100px; margin-top: 8px; }
			.lpm .wp-picker-container { display: inline-block; }
		</style>
		<script>
			jQuery( function ( $ ) {
				$( '.lpm-color' ).wpColorPicker();
				var $p = $( '#lpm-provider' );
				function sync() {
					$( '[data-lpm]' ).hide().filter( '[data-lpm="' + $p.val() + '"]' ).show();
				}
				$p.on( 'change', sync );
				sync();
				document.addEventListener( 'lp:google-auth-failed', function () {
					document.getElementById( 'lpm-google-fail' ).hidden = false;
				} );
			} );
		</script>
		<?php
	}
}
