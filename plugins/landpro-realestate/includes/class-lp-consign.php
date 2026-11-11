<?php
/**
 * Ký gửi nhà đất:
 *  - Trang [lp_consign]: lợi ích, quy trình, form ký gửi (thông tin + ảnh), danh sách "Tin ký gửi của bạn", câu hỏi thường gặp.
 *  - REST /landpro/v1/consign: nhận tin (tạo tin nhà đất "Chờ duyệt" + ảnh), trả danh sách tin theo mã trình duyệt.
 *    Không dùng nonce trên trang công khai → tương thích plugin cache; chống spam bằng bẫy bot, thời gian điền và giới hạn lượt gửi.
 *  - Admin: Nhà đất → Ký gửi (Chờ duyệt / Đã duyệt / Từ chối), duyệt – từ chối có lý do – xóa; email cho admin và người gửi.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Consign {

	const STATUS_REJECTED = 'lp_rejected';
	const MAX_FILES       = 10;
	const MAX_BYTES       = 5242880; // 5 MB / ảnh.
	const PAGE            = 'lp-consign';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_status' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_shortcode( 'lp_consign', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 7 );
		add_filter( 'lp_strings', array( __CLASS__, 'strings' ) );

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_lp_consign_action', array( __CLASS__, 'admin_action' ) );
		add_action( 'add_meta_boxes_property', array( __CLASS__, 'owner_metabox' ) );
		add_action( 'transition_post_status', array( __CLASS__, 'notify_owner' ), 20, 3 );
		add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 10, 2 );
	}

	public static function register_status() {
		register_post_status(
			self::STATUS_REJECTED,
			array(
				'label'                     => 'Bị từ chối',
				'public'                    => false,
				'internal'                  => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				/* translators: %s: số tin */
				'label_count'               => _n_noop( 'Bị từ chối <span class="count">(%s)</span>', 'Bị từ chối <span class="count">(%s)</span>' ),
			)
		);
	}

	/* ================================================================ Trang công khai */

	public static function register_assets() {
		wp_register_script( 'lp-consign', LP_URL . 'assets/js/consign.js', array(), filemtime( LP_DIR . 'assets/js/consign.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	private static function lang() {
		return ( function_exists( 'pll_current_language' ) && 'en' === pll_current_language() ) ? 'en' : 'vi';
	}

	private static function icon( $path ) {
		return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
	}

	public static function shortcode() {
		LP_Render::enqueue();
		wp_enqueue_script( 'lp-consign' );

		$hotline   = lp_option( 'hotline', '0898 614 962' );
		$privacy   = class_exists( 'LP_Legal' ) ? LP_Legal::url( 'chinh-sach-bao-mat' ) : '';
		$benefits  = array(
			array( 'headset', 'Tiếp cận khách thật', 'Tin được giới thiệu tới khách hàng đang tìm mua, thuê trong cơ sở dữ liệu của LandPro.' ),
			array( 'price', 'Định giá miễn phí', 'Chuyên viên so sánh giá giao dịch khu vực, tư vấn mức giá hợp lý để bán nhanh.' ),
			array( 'shield', 'Hỗ trợ pháp lý', 'Kiểm tra sổ, quy hoạch, hướng dẫn hồ sơ công chứng và thủ tục sang tên.' ),
			array( 'house', 'Chụp ảnh & đăng tin', 'Chụp ảnh chuyên nghiệp, viết tin chuẩn SEO, hiển thị trên bản đồ và bộ lọc.' ),
		);
		$steps     = array(
			array( 'Gửi thông tin', 'Điền form bên dưới kèm ảnh thực tế của nhà đất.' ),
			array( 'Thẩm định & duyệt', 'Chuyên viên gọi xác minh và kiểm tra pháp lý trong 24 giờ làm việc.' ),
			array( 'Đăng tin & quảng bá', 'Tin được duyệt hiển thị trên website, bản đồ và gửi tới khách phù hợp.' ),
			array( 'Dẫn khách & giao dịch', 'Hỗ trợ dẫn khách xem nhà, đàm phán và hoàn tất thủ tục.' ),
		);
		$criteria  = array(
			'Thông tin chính xác, đúng chủ sở hữu hoặc được ủy quyền.',
			'Ảnh thực tế, rõ nét, không chèn logo hay số điện thoại.',
			'Giá và diện tích hợp lý, mô tả đầy đủ tình trạng nhà.',
			'Không đăng trùng một bất động sản nhiều lần.',
		);
		$faq       = array(
			array( 'Ký gửi có mất phí không?', 'Đăng ký ký gửi và đăng tin trên website hoàn toàn miễn phí. Phí dịch vụ môi giới (nếu có) chỉ thỏa thuận khi giao dịch thành công.' ),
			array( 'Bao lâu thì tin được duyệt?', 'Thường trong 24 giờ làm việc. Bạn theo dõi trạng thái ở mục "Tin ký gửi của bạn" ngay trên trang này.' ),
			array( 'Tôi muốn sửa hoặc gỡ tin thì làm sao?', 'Gọi hotline hoặc gửi email kèm tiêu đề tin, chuyên viên sẽ cập nhật giúp bạn.' ),
			array( 'Vì sao tin bị từ chối?', 'Thường do thiếu thông tin, ảnh không rõ hoặc chưa xác minh được chủ sở hữu. Lý do được ghi kèm để bạn bổ sung và gửi lại.' ),
		);
		$directions = lp_fields( 'property' )['direction']['options'];
		$legal      = lp_fields( 'property' )['legal']['options'];
		$labels     = array(
			'pending'  => lp_t( 'Chờ duyệt' ),
			'publish'  => lp_t( 'Đã duyệt' ),
			'rejected' => lp_t( 'Bị từ chối' ),
			'view'     => lp_t( 'Xem tin' ),
			'reason'   => lp_t( 'Lý do' ),
			'sending'  => lp_t( 'Đang gửi…' ),
			'success'  => lp_t( 'Đã nhận tin ký gửi! Chuyên viên sẽ liên hệ xác minh trong 24 giờ làm việc.' ),
			'error'    => lp_t( 'Chưa gửi được, vui lòng thử lại hoặc gọi hotline.' ),
			'required' => lp_t( 'Vui lòng điền các ô bắt buộc.' ),
			'tooMany'  => sprintf( lp_t( 'Tối đa %d ảnh.' ), self::MAX_FILES ),
			'tooBig'   => lp_t( 'Ảnh lớn hơn 5 MB đã được bỏ qua.' ),
			'remove'   => lp_t( 'Xóa ảnh' ),
			'billion'  => lp_t( 'tỷ' ),
			'million'  => lp_t( 'triệu' ),
			'month'    => lp_t( '/tháng' ),
			'empty'    => lp_t( 'Bạn chưa ký gửi tin nào từ trình duyệt này.' ),
			'check'    => lp_t( 'Vui lòng kiểm tra:' ),
			'consent'  => lp_t( 'Ô xác nhận đồng ý chính sách bảo mật' ),
			'minChars' => lp_t( 'cần ít nhất %1$d ký tự, hiện có %2$d' ),
			'counter'  => lp_t( '%1$d / %2$d ký tự tối thiểu' ),
		);

		ob_start();
		?>
		<div class="lp-cs" data-lp-consign data-api="<?php echo esc_url( rest_url( 'landpro/v1/consign' ) ); ?>" data-lang="<?php echo esc_attr( self::lang() ); ?>" data-labels="<?php echo esc_attr( wp_json_encode( $labels, JSON_UNESCAPED_UNICODE ) ); ?>">

			<section class="lp-cs__benefits" aria-label="<?php echo esc_attr( lp_t( 'Lợi ích khi ký gửi' ) ); ?>">
				<?php foreach ( $benefits as $b ) : ?>
					<div class="lp-cs__benefit">
						<span class="lp-cs__icon"><?php echo lp_color_icon( $b[0], 52 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h3><?php echo esc_html( lp_t( $b[1] ) ); ?></h3>
						<p><?php echo esc_html( lp_t( $b[2] ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</section>

			<section class="lp-cs__steps" aria-labelledby="lp-cs-steps-title">
				<h2 id="lp-cs-steps-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Quy trình ký gửi' ) ); ?></h2>
				<ol>
					<?php foreach ( $steps as $i => $st ) : ?>
						<li><span class="lp-cs__num"><?php echo (int) ( $i + 1 ); ?></span><strong><?php echo esc_html( lp_t( $st[0] ) ); ?></strong><p><?php echo esc_html( lp_t( $st[1] ) ); ?></p></li>
					<?php endforeach; ?>
				</ol>
			</section>

			<div class="lp-cs__layout">
				<form class="lp-cs__form" novalidate>
					<h2 class="lp-section__title" id="lp-cs-form"><?php echo esc_html( lp_t( 'Thông tin ký gửi' ) ); ?></h2>

					<fieldset class="lp-cs__group">
						<legend><span>1</span><?php echo esc_html( lp_t( 'Người ký gửi' ) ); ?></legend>
						<div class="lp-cs__grid">
							<?php self::input( 'name', lp_t( 'Họ và tên' ), 'text', true, array( 'autocomplete' => 'name' ) ); ?>
							<?php self::input( 'phone', lp_t( 'Số điện thoại' ), 'tel', true, array( 'autocomplete' => 'tel', 'placeholder' => '09xx xxx xxx' ) ); ?>
							<?php self::input( 'email', 'Email', 'email', false, array( 'autocomplete' => 'email' ) ); ?>
							<div class="lp-cs__field">
								<span class="lp-cs__label"><?php echo esc_html( lp_t( 'Bạn là' ) ); ?></span>
								<div class="lp-cs__chips">
									<label><input type="radio" name="cs_role" value="owner" checked><span><?php echo esc_html( lp_t( 'Chủ nhà' ) ); ?></span></label>
									<label><input type="radio" name="cs_role" value="agent"><span><?php echo esc_html( lp_t( 'Môi giới / được ủy quyền' ) ); ?></span></label>
								</div>
							</div>
						</div>
					</fieldset>

					<fieldset class="lp-cs__group">
						<legend><span>2</span><?php echo esc_html( lp_t( 'Thông tin nhà đất' ) ); ?></legend>
						<div class="lp-cs__field lp-cs__field--full">
							<span class="lp-cs__label"><?php echo esc_html( lp_t( 'Nhu cầu' ) ); ?> <em>*</em></span>
							<div class="lp-cs__chips lp-cs__chips--big">
								<label><input type="radio" name="cs_purpose" value="<?php echo esc_attr( lp_lang_slug( 'ban', 'lp_purpose' ) ); ?>" checked><span><?php echo esc_html( lp_t( 'Cần bán' ) ); ?></span></label>
								<label><input type="radio" name="cs_purpose" value="<?php echo esc_attr( lp_lang_slug( 'cho-thue', 'lp_purpose' ) ); ?>"><span><?php echo esc_html( lp_t( 'Cho thuê' ) ); ?></span></label>
							</div>
						</div>
						<div class="lp-cs__grid">
							<?php self::select( 'type', lp_t( 'Loại nhà đất' ), LP_Render::term_options( 'lp_type' ), true ); ?>
							<?php self::select( 'location', lp_t( 'Khu vực' ), LP_Render::term_options( 'lp_location' ), true ); ?>
							<?php self::input( 'address', lp_t( 'Địa chỉ' ), 'text', true, array( 'placeholder' => lp_t( 'Số nhà, đường, phường/xã' ) ), 'full' ); ?>
							<?php self::input( 'title', lp_t( 'Tiêu đề tin' ), 'text', true, array( 'maxlength' => 120, 'placeholder' => lp_t( 'VD: Bán nhà 3 tầng hẻm xe hơi, gần chợ' ) ), 'full' ); ?>
							<div class="lp-cs__field">
								<label for="lp-cs-price"><?php echo esc_html( lp_t( 'Giá (VNĐ)' ) ); ?> <em>*</em></label>
								<input id="lp-cs-price" name="cs_price" type="number" min="0" step="1000000" inputmode="numeric" required>
								<small class="lp-cs__hint" data-lp-price-hint><?php echo esc_html( lp_t( 'Bán: tổng giá. Cho thuê: giá mỗi tháng.' ) ); ?></small>
								<label class="lp-cs__check"><input type="checkbox" name="cs_negotiable" value="1"> <?php echo esc_html( lp_t( 'Giá thỏa thuận' ) ); ?></label>
							</div>
							<?php self::input( 'area', lp_t( 'Diện tích (m²)' ), 'number', true, array( 'min' => 1, 'step' => '0.1', 'inputmode' => 'decimal' ) ); ?>
							<?php self::input( 'bedrooms', lp_t( 'Phòng ngủ' ), 'number', false, array( 'min' => 0, 'max' => 50 ) ); ?>
							<?php self::input( 'bathrooms', lp_t( 'Phòng tắm / WC' ), 'number', false, array( 'min' => 0, 'max' => 50 ) ); ?>
							<?php self::input( 'floors', lp_t( 'Số tầng' ), 'number', false, array( 'min' => 0, 'max' => 100 ) ); ?>
							<?php self::input( 'frontage', lp_t( 'Mặt tiền (m)' ), 'number', false, array( 'min' => 0, 'step' => '0.1' ) ); ?>
							<?php self::select( 'direction', lp_t( 'Hướng nhà' ), array_map( 'lp_t', array_diff_key( $directions, array( '' => 1 ) ) ), false ); ?>
							<?php self::select( 'legal', lp_t( 'Pháp lý' ), array_map( 'lp_t', array_diff_key( $legal, array( '' => 1 ) ) ), false ); ?>
							<?php self::input( 'furniture', lp_t( 'Nội thất' ), 'text', false, array( 'placeholder' => lp_t( 'VD: Đầy đủ, Cơ bản, Bàn giao thô' ) ), 'full' ); ?>
							<div class="lp-cs__field lp-cs__field--full">
								<label for="lp-cs-desc"><?php echo esc_html( lp_t( 'Mô tả chi tiết' ) ); ?> <em>*</em></label>
								<textarea id="lp-cs-desc" name="cs_desc" rows="6" minlength="30" maxlength="5000" required placeholder="<?php echo esc_attr( lp_t( 'Hiện trạng, tiện ích xung quanh, đường trước nhà, thời gian xem nhà…' ) ); ?>"></textarea>
								<small class="lp-cs__hint lp-cs__count" data-lp-desc-count aria-live="polite"></small>
							</div>
						</div>
					</fieldset>

					<fieldset class="lp-cs__group">
						<legend><span>3</span><?php echo esc_html( lp_t( 'Hình ảnh' ) ); ?></legend>
						<label class="lp-cs__drop" data-lp-drop>
							<input type="file" accept="image/jpeg,image/png,image/webp" multiple data-lp-files>
							<?php echo self::icon( '<path d="M4 16v3h16v-3M12 4v11M7.5 8.5 12 4l4.5 4.5"/>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<strong><?php echo esc_html( lp_t( 'Kéo thả ảnh vào đây hoặc bấm để chọn' ) ); ?></strong>
							<small><?php echo esc_html( sprintf( lp_t( 'JPG, PNG, WebP · tối đa %d ảnh · mỗi ảnh ≤ 5 MB. Ảnh đầu tiên là ảnh đại diện.' ), self::MAX_FILES ) ); ?></small>
						</label>
						<ul class="lp-cs__previews" data-lp-previews></ul>
					</fieldset>

					<div class="lp-cs__hp" aria-hidden="true"><label>Website <input type="text" name="cs_website" tabindex="-1" autocomplete="off"></label></div>
					<label class="lp-cs__consent">
						<input type="checkbox" name="cs_consent" value="1" required>
						<span>
							<?php
							echo wp_kses(
								sprintf(
									lp_t( 'Tôi xác nhận thông tin là chính xác và đồng ý để LandPro xử lý dữ liệu cá nhân nhằm liên hệ, theo <a href="%s" target="_blank" rel="noopener">Chính sách bảo mật</a>.' ),
									esc_url( $privacy ? $privacy : '#' )
								),
								array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
							);
							?>
						</span>
					</label>
					<p class="lp-cs__msg" role="status" aria-live="polite" hidden></p>
					<div class="lp-cs__progress" hidden><span></span></div>
					<button type="submit" class="lp-btn lp-cs__submit">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-7 9 7v9H3z"/><path d="M9 20v-6h6v6"/></svg>
						<span><?php echo esc_html( lp_t( 'Gửi ký gửi' ) ); ?></span>
					</button>
				</form>

				<aside class="lp-cs__aside">
					<div class="lp-cs__card">
						<h3><?php echo esc_html( lp_t( 'Tiêu chí duyệt tin' ) ); ?></h3>
						<ul>
							<?php foreach ( $criteria as $c ) : ?>
								<li><?php echo lp_color_icon( 'tick', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( lp_t( $c ) ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="lp-cs__card lp-cs__card--call">
						<h3><?php echo esc_html( lp_t( 'Cần hỗ trợ nhanh?' ) ); ?></h3>
						<p><?php echo esc_html( lp_t( 'Gọi chuyên viên để được hướng dẫn ký gửi qua điện thoại.' ) ); ?></p>
						<a class="lp-btn" href="tel:<?php echo esc_attr( lp_phone_digits( $hotline ) ); ?>"><?php echo lp_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $hotline ); ?></span></a>
					</div>
				</aside>
			</div>

			<section class="lp-cs__mine" data-lp-mine hidden aria-labelledby="lp-cs-mine-title">
				<h2 id="lp-cs-mine-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Tin ký gửi của bạn' ) ); ?></h2>
				<p class="lp-cs__mine-note"><?php echo esc_html( lp_t( 'Danh sách được ghi nhớ trên trình duyệt này. Trạng thái cập nhật ngay khi tin được duyệt.' ) ); ?></p>
				<ul class="lp-cs__list" data-lp-list></ul>
			</section>

			<section class="lp-cs__faq" aria-labelledby="lp-cs-faq-title">
				<h2 id="lp-cs-faq-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Câu hỏi thường gặp' ) ); ?></h2>
				<?php foreach ( $faq as $i => $f ) : ?>
					<details name="lp-cs-faq" <?php echo 0 === $i ? 'open' : ''; ?>>
						<summary><?php echo esc_html( lp_t( $f[0] ) ); ?></summary>
						<p><?php echo esc_html( lp_t( $f[1] ) ); ?></p>
					</details>
				<?php endforeach; ?>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function input( $name, $label, $type, $required, $attrs = array(), $width = '' ) {
		$extra = '';
		foreach ( $attrs as $k => $v ) {
			$extra .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
		}
		printf(
			'<div class="lp-cs__field%1$s"><label for="lp-cs-%2$s">%3$s%4$s</label><input id="lp-cs-%2$s" name="cs_%2$s" type="%5$s"%6$s%7$s></div>',
			'full' === $width ? ' lp-cs__field--full' : '',
			esc_attr( $name ),
			esc_html( $label ),
			$required ? ' <em>*</em>' : '',
			esc_attr( $type ),
			$required ? ' required' : '',
			$extra // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}

	private static function select( $name, $label, $options, $required ) {
		printf( '<div class="lp-cs__field"><label for="lp-cs-%1$s">%2$s%3$s</label><select id="lp-cs-%1$s" name="cs_%1$s"%4$s><option value="">%5$s</option>', esc_attr( $name ), esc_html( $label ), $required ? ' <em>*</em>' : '', $required ? ' required' : '', esc_html( lp_t( '— Chọn —' ) ) );
		foreach ( $options as $v => $t ) {
			printf( '<option value="%s">%s</option>', esc_attr( $v ), esc_html( $t ) );
		}
		echo '</select></div>';
	}

	/* ================================================================ REST */

	public static function routes() {
		register_rest_route(
			'landpro/v1',
			'/consign',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'rest_submit' ),
					'permission_callback' => '__return_true', // Form công khai: chống spam bên trong.
				),
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'rest_mine' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'token' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						),
					),
				),
			)
		);
	}

	private static function token_hash( $token ) {
		return hash_hmac( 'sha256', (string) $token, wp_salt( 'auth' ) );
	}

	private static function error( $msg, $code = 400 ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => $msg ), $code );
	}

	public static function rest_submit( WP_REST_Request $req ) {
		$p    = $req->get_body_params();
		$get  = function ( $k ) use ( $p ) {
			return isset( $p[ 'cs_' . $k ] ) ? trim( (string) wp_unslash( $p[ 'cs_' . $k ] ) ) : '';
		};
		$lang = 'en' === $get( 'lang' ) ? 'en' : 'vi';
		self::set_lang( $lang ); // Chuỗi thông báo theo ngôn ngữ người gửi.

		// Chống spam: bẫy bot, điền quá nhanh, giới hạn 5 tin / giờ / IP.
		if ( '' !== $get( 'website' ) || (int) $get( 'elapsed' ) < 4 ) {
			return self::error( lp_t( 'Chưa gửi được, vui lòng thử lại hoặc gọi hotline.' ) );
		}
		$ip_key = 'lp_cs_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$count  = (int) get_transient( $ip_key );
		if ( $count >= 5 ) {
			return self::error( lp_t( 'Bạn đã gửi nhiều tin, vui lòng thử lại sau 1 giờ hoặc gọi hotline.' ), 429 );
		}

		$name    = sanitize_text_field( $get( 'name' ) );
		$phone   = preg_replace( '/[^0-9+]/', '', $get( 'phone' ) );
		$email   = sanitize_email( $get( 'email' ) );
		$title   = sanitize_text_field( $get( 'title' ) );
		$address = sanitize_text_field( $get( 'address' ) );
		$desc    = sanitize_textarea_field( $get( 'desc' ) );
		$token   = sanitize_key( $get( 'token' ) );
		$purpose = get_term_by( 'slug', sanitize_title( $get( 'purpose' ) ), 'lp_purpose' );
		$type    = get_term_by( 'slug', sanitize_title( $get( 'type' ) ), 'lp_type' );
		$loc     = get_term_by( 'slug', sanitize_title( $get( 'location' ) ), 'lp_location' );
		$price   = (float) $get( 'price' );
		$neg     = '1' === $get( 'negotiable' );
		$area    = (float) $get( 'area' );

		$missing = ! $name || strlen( $phone ) < 9 || ! $title || ! $address || ! $purpose || ! $type || ! $loc || $area <= 0 || mb_strlen( $desc ) < 30 || ( ! $neg && $price <= 0 ) || '1' !== $get( 'consent' ) || strlen( $token ) < 16;
		if ( $missing ) {
			return self::error( lp_t( 'Vui lòng điền các ô bắt buộc.' ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'property',
				'post_status'  => 'pending',
				'post_title'   => mb_substr( $title, 0, 120 ),
				'post_content' => wpautop( esc_html( $desc ) ),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return self::error( lp_t( 'Chưa gửi được, vui lòng thử lại hoặc gọi hotline.' ), 500 );
		}
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $post_id, $lang );
		}
		wp_set_object_terms( $post_id, array( (int) $purpose->term_id ), 'lp_purpose' );
		wp_set_object_terms( $post_id, array( (int) $type->term_id ), 'lp_type' );
		$locs = array( (int) $loc->term_id );
		if ( $loc->parent ) {
			$locs[] = (int) $loc->parent; // Gắn cả tỉnh/thành để lọc theo cấp cha.
		}
		wp_set_object_terms( $post_id, $locs, 'lp_location' );

		$fields = lp_fields( 'property' );
		$meta   = array(
			'price'       => $neg ? '' : $price,
			'price_label' => $neg ? lp_t( 'Thỏa thuận' ) : '',
			'area'        => $area,
			'bedrooms'    => absint( $get( 'bedrooms' ) ) ?: '',
			'bathrooms'   => absint( $get( 'bathrooms' ) ) ?: '',
			'floors'      => absint( $get( 'floors' ) ) ?: '',
			'frontage'    => (float) $get( 'frontage' ) ?: '',
			'direction'   => isset( $fields['direction']['options'][ $get( 'direction' ) ] ) ? $get( 'direction' ) : '',
			'legal'       => isset( $fields['legal']['options'][ $get( 'legal' ) ] ) ? $get( 'legal' ) : '',
			'furniture'   => sanitize_text_field( $get( 'furniture' ) ),
			'address'     => $address,
		);
		foreach ( $meta as $k => $v ) {
			if ( '' !== $v && null !== $v ) {
				update_post_meta( $post_id, '_lp_' . $k, $v );
			}
		}
		update_post_meta( $post_id, '_lp_consign', 1 );
		update_post_meta( $post_id, '_lp_consign_token', self::token_hash( $token ) );
		update_post_meta( $post_id, '_lp_owner_name', $name );
		update_post_meta( $post_id, '_lp_owner_phone', $phone );
		update_post_meta( $post_id, '_lp_owner_email', $email );
		update_post_meta( $post_id, '_lp_owner_role', 'agent' === $get( 'role' ) ? 'agent' : 'owner' );

		$images = self::save_images( $post_id, $title );
		if ( $images ) {
			set_post_thumbnail( $post_id, $images[0] );
			if ( count( $images ) > 1 ) {
				update_post_meta( $post_id, '_lp_gallery', implode( ',', array_slice( $images, 1 ) ) );
			}
		}

		set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );
		self::notify_admin( $post_id );

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'id'      => $post_id,
				'message' => lp_t( 'Đã nhận tin ký gửi! Chuyên viên sẽ liên hệ xác minh trong 24 giờ làm việc.' ),
			),
			201
		);
	}

	/** Lưu ảnh tải lên (kiểm tra loại & dung lượng), trả về danh sách ID. */
	private static function save_images( $post_id, $title ) {
		if ( empty( $_FILES['cs_images'] ) || ! is_array( $_FILES['cs_images']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return array();
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$files   = $_FILES['cs_images']; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		$ids     = array();
		$allowed = array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' );
		$total   = min( count( $files['name'] ), self::MAX_FILES );
		for ( $i = 0; $i < $total; $i++ ) {
			if ( UPLOAD_ERR_OK !== (int) $files['error'][ $i ] || (int) $files['size'][ $i ] > self::MAX_BYTES ) {
				continue;
			}
			$check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $files['name'][ $i ], $allowed );
			if ( empty( $check['type'] ) || ! @getimagesize( $files['tmp_name'][ $i ] ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
				continue;
			}
			$file = array(
				'name'     => sanitize_file_name( $post_id . '-' . ( $i + 1 ) . '-' . $files['name'][ $i ] ),
				'type'     => $check['type'],
				'tmp_name' => $files['tmp_name'][ $i ],
				'error'    => 0,
				'size'     => (int) $files['size'][ $i ],
			);
			$id = media_handle_sideload( $file, $post_id, sprintf( '%s - %d', $title, $i + 1 ) );
			if ( ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_attachment_image_alt', sprintf( '%s - %d', $title, $i + 1 ) );
				$ids[] = (int) $id;
			}
		}
		return $ids;
	}

	/** Đặt ngôn ngữ Polylang cho request REST (không có ngữ cảnh trang). */
	private static function set_lang( $lang ) {
		if ( function_exists( 'PLL' ) && PLL()->model->get_language( $lang ) ) {
			PLL()->curlang = PLL()->model->get_language( $lang );
		}
	}

	public static function rest_mine( WP_REST_Request $req ) {
		self::set_lang( 'en' === $req->get_param( 'lang' ) ? 'en' : 'vi' );
		$token = $req->get_param( 'token' );
		if ( strlen( $token ) < 16 ) {
			return new WP_REST_Response( array( 'items' => array() ) );
		}
		$posts = get_posts(
			array(
				'post_type'      => 'property',
				'post_status'    => array( 'pending', 'publish', 'draft', self::STATUS_REJECTED ),
				'posts_per_page' => 30,
				'meta_key'       => '_lp_consign_token', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => self::token_hash( $token ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
			)
		);
		$items = array();
		foreach ( $posts as $post ) {
			$status  = 'publish' === $post->post_status ? 'publish' : ( self::STATUS_REJECTED === $post->post_status ? 'rejected' : 'pending' );
			$items[] = array(
				'id'     => $post->ID,
				'title'  => get_the_title( $post ),
				'date'   => get_the_date( 'd/m/Y H:i', $post ),
				'status' => $status,
				'url'    => 'publish' === $status ? get_permalink( $post ) : '',
				'thumb'  => (string) get_the_post_thumbnail_url( $post, 'thumbnail' ),
				'price'  => wp_strip_all_tags( lp_price_html( $post->ID ) ),
				'note'   => 'rejected' === $status ? (string) get_post_meta( $post->ID, '_lp_consign_note', true ) : '',
			);
		}
		$res = new WP_REST_Response( array( 'items' => $items ) );
		$res->header( 'Cache-Control', 'no-store' );
		return $res;
	}

	/* ================================================================ Email */

	private static function notify_admin( $post_id ) {
		$to = lp_option( 'email', get_option( 'admin_email' ) );
		if ( ! $to ) {
			return;
		}
		$url  = admin_url( 'edit.php?post_type=property&page=' . self::PAGE );
		$body = sprintf(
			"Có tin ký gửi mới chờ duyệt:\n\n%s\nNgười gửi: %s – %s %s\n\nDuyệt tin: %s",
			get_the_title( $post_id ),
			get_post_meta( $post_id, '_lp_owner_name', true ),
			get_post_meta( $post_id, '_lp_owner_phone', true ),
			get_post_meta( $post_id, '_lp_owner_email', true ),
			$url
		);
		wp_mail( $to, '[' . get_bloginfo( 'name' ) . '] Tin ký gửi mới: ' . get_the_title( $post_id ), $body );
	}

	/** Báo người gửi khi tin được duyệt / từ chối (kể cả khi duyệt từ màn hình sửa tin). */
	public static function notify_owner( $new, $old, $post ) {
		if ( 'property' !== $post->post_type || $new === $old || ! get_post_meta( $post->ID, '_lp_consign', true ) ) {
			return;
		}
		$email = get_post_meta( $post->ID, '_lp_owner_email', true );
		if ( ! $email || ! in_array( $new, array( 'publish', self::STATUS_REJECTED ), true ) ) {
			return;
		}
		$name = get_post_meta( $post->ID, '_lp_owner_name', true );
		if ( 'publish' === $new ) {
			$subject = 'Tin ký gửi của bạn đã được duyệt';
			$body    = sprintf( "Chào %s,\n\nTin \"%s\" đã được duyệt và hiển thị tại:\n%s\n\nCảm ơn bạn đã ký gửi cùng %s.", $name, get_the_title( $post ), get_permalink( $post ), get_bloginfo( 'name' ) );
		} else {
			$note    = get_post_meta( $post->ID, '_lp_consign_note', true );
			$subject = 'Tin ký gửi của bạn chưa được duyệt';
			$body    = sprintf( "Chào %s,\n\nTin \"%s\" chưa được duyệt.%s\n\nBạn có thể bổ sung thông tin và gửi lại, hoặc gọi hotline %s để được hỗ trợ.", $name, get_the_title( $post ), $note ? "\nLý do: " . $note : '', lp_option( 'hotline', '' ) );
		}
		wp_mail( $email, '[' . get_bloginfo( 'name' ) . '] ' . $subject, $body );
	}

	/* ================================================================ Admin */

	private static function count( $status ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'property',
				'post_status'    => $status,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_lp_consign', // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
			)
		);
		return (int) $q->found_posts;
	}

	public static function admin_menu() {
		$pending = self::count( 'pending' );
		$bubble  = $pending ? ' <span class="awaiting-mod"><span class="pending-count">' . $pending . '</span></span>' : '';
		add_submenu_page( 'edit.php?post_type=property', 'Tin ký gửi', 'Ký gửi' . $bubble, 'edit_others_posts', self::PAGE, array( __CLASS__, 'admin_page' ), 2 );
		if ( $pending ) {
			// Hiện số tin chờ ở menu cha "Nhà đất".
			global $menu;
			foreach ( (array) $menu as $k => $item ) {
				if ( isset( $item[2] ) && 'edit.php?post_type=property' === $item[2] ) {
					$menu[ $k ][0] .= $bubble; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				}
			}
		}
	}

	public static function admin_page() {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}
		$tabs   = array(
			'pending'               => 'Chờ duyệt',
			'publish'               => 'Đã duyệt',
			self::STATUS_REJECTED   => 'Bị từ chối',
		);
		$tab    = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'pending'; // phpcs:ignore WordPress.Security.NonceVerification
		$paged  = max( 1, absint( $_GET['paged'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$q      = new WP_Query(
			array(
				'post_type'      => 'property',
				'post_status'    => $tab,
				'posts_per_page' => 20,
				'paged'          => $paged,
				'meta_key'       => '_lp_consign', // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		$base   = admin_url( 'edit.php?post_type=property&page=' . self::PAGE );
		$done   = isset( $_GET['done'] ) ? sanitize_key( $_GET['done'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$msgs   = array(
			'approved' => 'Đã duyệt và đăng tin.',
			'rejected' => 'Đã từ chối tin.',
			'deleted'  => 'Đã chuyển tin vào thùng rác.',
			'pending'  => 'Đã chuyển tin về chờ duyệt.',
		);
		?>
		<div class="wrap lp-csa">
			<h1 class="wp-heading-inline">Tin ký gửi</h1>
			<?php $page_url = self::page_url(); ?>
			<?php if ( $page_url ) : ?>
				<a class="page-title-action" href="<?php echo esc_url( $page_url ); ?>" target="_blank" rel="noopener">Xem trang ký gửi</a>
			<?php endif; ?>
			<hr class="wp-header-end">
			<?php if ( isset( $msgs[ $done ] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msgs[ $done ] ); ?></p></div>
			<?php endif; ?>
			<p class="description">Khách gửi tin ở trang Ký gửi sẽ vào mục <strong>Chờ duyệt</strong>. Bấm <strong>Sửa tin</strong> để chỉnh nội dung, đặt vị trí bản đồ trước khi duyệt. Khi duyệt / từ chối, người gửi nhận email (nếu có).</p>
			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $k => $label ) : ?>
					<a class="nav-tab <?php echo $k === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', $k, $base ) ); ?>"><?php echo esc_html( $label ); ?> <span class="count">(<?php echo (int) self::count( $k ); ?>)</span></a>
				<?php endforeach; ?>
			</nav>

			<?php if ( ! $q->have_posts() ) : ?>
				<p class="lp-csa__empty">Không có tin nào.</p>
			<?php else : ?>
				<table class="widefat striped lp-csa__table">
					<thead><tr><th style="width:92px">Ảnh</th><th>Tin ký gửi</th><th style="width:220px">Người gửi</th><th style="width:130px">Ngày gửi</th><th style="width:260px">Thao tác</th></tr></thead>
					<tbody>
					<?php
					foreach ( $q->posts as $post ) :
						$id     = $post->ID;
						$terms  = array_filter( array( lp_first_term( $id, 'lp_purpose' ), lp_first_term( $id, 'lp_type' ) ) );
						$imgs   = count( lp_gallery_ids( $id ) );
						$role   = 'agent' === get_post_meta( $id, '_lp_owner_role', true ) ? 'Môi giới' : 'Chủ nhà';
						$action = function ( $do ) use ( $id ) {
							return wp_nonce_url( admin_url( 'admin-post.php?action=lp_consign_action&do=' . $do . '&post=' . $id ), 'lp_consign_' . $do . '_' . $id );
						};
						?>
						<tr>
							<td><?php echo get_the_post_thumbnail( $id, array( 80, 60 ), array( 'class' => 'lp-csa__thumb' ) ) ?: '<span class="lp-csa__noimg">—</span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
							<td>
								<strong><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></strong>
								<div class="lp-csa__meta">
									<?php echo esc_html( implode( ' · ', wp_list_pluck( $terms, 'name' ) ) ); ?>
									· <?php echo esc_html( lp_location_text( $id ) ); ?>
									· <strong><?php echo wp_kses_post( lp_price_html( $id ) ); ?></strong>
									· <?php echo esc_html( lp_number( lp_get( 'area', $id ) ) ); ?> m²
									· <?php echo (int) $imgs; ?> ảnh
								</div>
								<div class="lp-csa__addr"><?php echo esc_html( lp_get( 'address', $id ) ); ?></div>
								<?php if ( self::STATUS_REJECTED === $post->post_status && get_post_meta( $id, '_lp_consign_note', true ) ) : ?>
									<div class="lp-csa__note">Lý do từ chối: <?php echo esc_html( get_post_meta( $id, '_lp_consign_note', true ) ); ?></div>
								<?php endif; ?>
							</td>
							<td>
								<strong><?php echo esc_html( get_post_meta( $id, '_lp_owner_name', true ) ); ?></strong> <span class="lp-csa__role"><?php echo esc_html( $role ); ?></span><br>
								<a href="tel:<?php echo esc_attr( get_post_meta( $id, '_lp_owner_phone', true ) ); ?>"><?php echo esc_html( get_post_meta( $id, '_lp_owner_phone', true ) ); ?></a><br>
								<?php $mail = get_post_meta( $id, '_lp_owner_email', true ); ?>
								<?php if ( $mail ) : ?><a href="mailto:<?php echo esc_attr( $mail ); ?>"><?php echo esc_html( $mail ); ?></a><?php endif; ?>
							</td>
							<td><?php echo esc_html( get_the_date( 'd/m/Y H:i', $post ) ); ?></td>
							<td class="lp-csa__actions">
								<?php if ( 'publish' !== $post->post_status ) : ?>
									<a class="button button-primary" href="<?php echo esc_url( $action( 'approve' ) ); ?>">Duyệt</a>
								<?php else : ?>
									<a class="button" href="<?php echo esc_url( get_permalink( $id ) ); ?>" target="_blank" rel="noopener">Xem tin</a>
									<a class="button" href="<?php echo esc_url( $action( 'pending' ) ); ?>">Gỡ về chờ duyệt</a>
								<?php endif; ?>
								<a class="button" href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">Sửa tin</a>
								<?php if ( self::STATUS_REJECTED !== $post->post_status ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lp-csa__reject">
										<?php wp_nonce_field( 'lp_consign_reject_' . $id ); ?>
										<input type="hidden" name="action" value="lp_consign_action">
										<input type="hidden" name="do" value="reject">
										<input type="hidden" name="post" value="<?php echo (int) $id; ?>">
										<details>
											<summary class="button">Từ chối</summary>
											<textarea name="note" rows="2" placeholder="Lý do (gửi cho người ký gửi)"></textarea>
											<button type="submit" class="button button-secondary">Xác nhận từ chối</button>
										</details>
									</form>
								<?php endif; ?>
								<a class="lp-csa__delete" href="<?php echo esc_url( $action( 'delete' ) ); ?>" onclick="return confirm('Chuyển tin này vào thùng rác?');">Xóa</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php
				echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%', add_query_arg( 'tab', $tab, $base ) ),
							'format'  => '',
							'current' => $paged,
							'total'   => $q->max_num_pages,
						)
					)
				) . '</div></div>';
				?>
			<?php endif; ?>
		</div>
		<style>
			.lp-csa__table td { vertical-align: top; }
			.lp-csa__thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; display: block; }
			.lp-csa__meta { margin-top: 4px; color: #50575e; }
			.lp-csa__addr { margin-top: 2px; color: #787c82; font-size: 12px; }
			.lp-csa__note { margin-top: 6px; padding: 6px 8px; background: #fcf0f1; border-left: 3px solid #d63638; font-size: 12px; }
			.lp-csa__role { display: inline-block; padding: 1px 6px; font-size: 11px; background: #f0f0f1; border-radius: 3px; }
			.lp-csa__actions { display: flex; flex-wrap: wrap; gap: 6px; align-items: flex-start; }
			.lp-csa__reject details[open] { flex-basis: 100%; }
			.lp-csa__reject summary { list-style: none; }
			.lp-csa__reject summary::-webkit-details-marker { display: none; }
			.lp-csa__reject textarea { display: block; width: 100%; min-width: 220px; margin: 6px 0; }
			.lp-csa__delete { color: #b32d2e; align-self: center; }
			.lp-csa__empty { padding: 24px; background: #fff; border: 1px solid #dcdcde; }
		</style>
		<?php
	}

	public static function admin_action() {
		$id = absint( $_REQUEST['post'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$do = sanitize_key( $_REQUEST['do'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		check_admin_referer( 'lp_consign_' . $do . '_' . $id );
		if ( ! $id || 'property' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Không có quyền.' );
		}
		$tab = 'pending';
		switch ( $do ) {
			case 'approve':
				wp_update_post( array( 'ID' => $id, 'post_status' => 'publish', 'post_date' => current_time( 'mysql' ), 'post_date_gmt' => current_time( 'mysql', true ) ) );
				$done = 'approved';
				$tab  = 'publish';
				break;
			case 'reject':
				update_post_meta( $id, '_lp_consign_note', sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				wp_update_post( array( 'ID' => $id, 'post_status' => self::STATUS_REJECTED ) );
				$done = 'rejected';
				$tab  = self::STATUS_REJECTED;
				break;
			case 'pending':
				wp_update_post( array( 'ID' => $id, 'post_status' => 'pending' ) );
				$done = 'pending';
				break;
			case 'delete':
				wp_trash_post( $id );
				$done = 'deleted';
				break;
			default:
				wp_die( 'Thao tác không hợp lệ.' );
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=property&page=' . self::PAGE . '&tab=' . $tab . '&done=' . $done ) );
		exit;
	}

	/** Hộp "Người ký gửi" trong trang sửa tin. */
	public static function owner_metabox( $post ) {
		if ( ! get_post_meta( $post->ID, '_lp_consign', true ) ) {
			return;
		}
		add_meta_box(
			'lp_consign_owner',
			'Người ký gửi',
			function ( $post ) {
				$role = 'agent' === get_post_meta( $post->ID, '_lp_owner_role', true ) ? 'Môi giới / được ủy quyền' : 'Chủ nhà';
				$mail = get_post_meta( $post->ID, '_lp_owner_email', true );
				echo '<p><strong>' . esc_html( get_post_meta( $post->ID, '_lp_owner_name', true ) ) . '</strong><br>' . esc_html( $role ) . '</p>';
				echo '<p><a href="tel:' . esc_attr( get_post_meta( $post->ID, '_lp_owner_phone', true ) ) . '">' . esc_html( get_post_meta( $post->ID, '_lp_owner_phone', true ) ) . '</a>';
				if ( $mail ) {
					echo '<br><a href="mailto:' . esc_attr( $mail ) . '">' . esc_html( $mail ) . '</a>';
				}
				echo '</p><p><a href="' . esc_url( admin_url( 'edit.php?post_type=property&page=' . self::PAGE ) ) . '">Danh sách tin ký gửi →</a></p>';
			},
			'property',
			'side',
			'high'
		);
	}

	public static function post_states( $states, $post ) {
		if ( 'property' === $post->post_type && get_post_meta( $post->ID, '_lp_consign', true ) ) {
			$states['lp_consign'] = 'Ký gửi';
			if ( self::STATUS_REJECTED === $post->post_status ) {
				$states['lp_rejected'] = 'Bị từ chối';
			}
		}
		return $states;
	}

	private static function page_url() {
		$page = get_page_by_path( 'ky-gui-nha-dat' );
		return $page ? get_permalink( $page ) : '';
	}

	/* ================================================================ Bản dịch tiếng Anh */

	public static function strings( $s ) {
		return $s + array(
			'Lợi ích khi ký gửi'          => 'Why list with us',
			'Tiếp cận khách thật'         => 'Reach real buyers',
			'Tin được giới thiệu tới khách hàng đang tìm mua, thuê trong cơ sở dữ liệu của LandPro.' => 'Your listing is introduced to active buyers and tenants in the LandPro database.',
			'Định giá miễn phí'           => 'Free valuation',
			'Chuyên viên so sánh giá giao dịch khu vực, tư vấn mức giá hợp lý để bán nhanh.' => 'Our consultants compare local transactions and suggest a price that sells fast.',
			'Hỗ trợ pháp lý'              => 'Legal support',
			'Kiểm tra sổ, quy hoạch, hướng dẫn hồ sơ công chứng và thủ tục sang tên.' => 'Title and zoning checks, notarisation paperwork and transfer guidance.',
			'Chụp ảnh & đăng tin'         => 'Photos & listing',
			'Chụp ảnh chuyên nghiệp, viết tin chuẩn SEO, hiển thị trên bản đồ và bộ lọc.' => 'Professional photos, SEO-ready copy, shown on the map and in search filters.',
			'Quy trình ký gửi'            => 'How it works',
			'Gửi thông tin'               => 'Submit details',
			'Điền form bên dưới kèm ảnh thực tế của nhà đất.' => 'Fill in the form below with real photos of the property.',
			'Thẩm định & duyệt'           => 'Review & approval',
			'Chuyên viên gọi xác minh và kiểm tra pháp lý trong 24 giờ làm việc.' => 'A consultant calls to verify and checks legal status within 24 working hours.',
			'Đăng tin & quảng bá'         => 'Publish & promote',
			'Tin được duyệt hiển thị trên website, bản đồ và gửi tới khách phù hợp.' => 'Approved listings appear on the website and map and are sent to matching clients.',
			'Dẫn khách & giao dịch'       => 'Viewings & closing',
			'Hỗ trợ dẫn khách xem nhà, đàm phán và hoàn tất thủ tục.' => 'We arrange viewings, negotiate and complete the paperwork.',
			'Thông tin ký gửi'            => 'Listing details',
			'Người ký gửi'                => 'Your details',
			'Họ và tên'                   => 'Full name',
			'Số điện thoại'               => 'Phone number',
			'Bạn là'                      => 'You are',
			'Chủ nhà'                     => 'Owner',
			'Môi giới / được ủy quyền'    => 'Agent / authorised',
			'Thông tin nhà đất'           => 'Property details',
			'Nhu cầu'                     => 'Purpose',
			'Cần bán'                     => 'For sale',
			'Số nhà, đường, phường/xã'    => 'House number, street, ward',
			'Tiêu đề tin'                 => 'Listing title',
			'VD: Bán nhà 3 tầng hẻm xe hơi, gần chợ' => 'e.g. 3-storey house on a car-accessible alley, near the market',
			'Giá (VNĐ)'                   => 'Price (VND)',
			'Bán: tổng giá. Cho thuê: giá mỗi tháng.' => 'Sale: total price. Rent: monthly price.',
			'Giá thỏa thuận'              => 'Price negotiable',
			'Diện tích (m²)'              => 'Area (m²)',
			'Phòng tắm / WC'              => 'Bathrooms',
			'Số tầng'                     => 'Floors',
			'Mặt tiền (m)'                => 'Frontage (m)',
			'Hướng nhà'                   => 'Facing',
			'Nội thất'                    => 'Furniture',
			'VD: Đầy đủ, Cơ bản, Bàn giao thô' => 'e.g. Fully furnished, Basic, Unfurnished',
			'Mô tả chi tiết'              => 'Description',
			'Hiện trạng, tiện ích xung quanh, đường trước nhà, thời gian xem nhà…' => 'Condition, nearby amenities, road access, viewing times…',
			'Hình ảnh'                    => 'Photos',
			'Kéo thả ảnh vào đây hoặc bấm để chọn' => 'Drag photos here or click to choose',
			'JPG, PNG, WebP · tối đa %d ảnh · mỗi ảnh ≤ 5 MB. Ảnh đầu tiên là ảnh đại diện.' => 'JPG, PNG, WebP · up to %d photos · max 5 MB each. The first photo is the cover.',
			'Tôi xác nhận thông tin là chính xác và đồng ý để LandPro xử lý dữ liệu cá nhân nhằm liên hệ, theo <a href="%s" target="_blank" rel="noopener">Chính sách bảo mật</a>.' => 'I confirm the information is accurate and agree that LandPro may process my personal data to contact me, under the <a href="%s" target="_blank" rel="noopener">Privacy Policy</a>.',
			'Gửi ký gửi'                  => 'Submit listing',
			'Tiêu chí duyệt tin'          => 'Approval criteria',
			'Thông tin chính xác, đúng chủ sở hữu hoặc được ủy quyền.' => 'Accurate details from the owner or an authorised person.',
			'Ảnh thực tế, rõ nét, không chèn logo hay số điện thoại.' => 'Real, sharp photos without logos or phone numbers.',
			'Giá và diện tích hợp lý, mô tả đầy đủ tình trạng nhà.' => 'Realistic price and area with a full description.',
			'Không đăng trùng một bất động sản nhiều lần.' => 'No duplicate listings for the same property.',
			'Cần hỗ trợ nhanh?'           => 'Need help now?',
			'Gọi chuyên viên để được hướng dẫn ký gửi qua điện thoại.' => 'Call a consultant to list your property by phone.',
			'Tin ký gửi của bạn'          => 'Your listings',
			'Danh sách được ghi nhớ trên trình duyệt này. Trạng thái cập nhật ngay khi tin được duyệt.' => 'This list is remembered in this browser. Status updates as soon as a listing is reviewed.',
			'Câu hỏi thường gặp'          => 'FAQ',
			'Ký gửi có mất phí không?'    => 'Is listing free?',
			'Đăng ký ký gửi và đăng tin trên website hoàn toàn miễn phí. Phí dịch vụ môi giới (nếu có) chỉ thỏa thuận khi giao dịch thành công.' => 'Submitting and publishing your listing is free. Any brokerage fee is agreed only when the deal closes.',
			'Bao lâu thì tin được duyệt?' => 'How long does approval take?',
			'Thường trong 24 giờ làm việc. Bạn theo dõi trạng thái ở mục "Tin ký gửi của bạn" ngay trên trang này.' => 'Usually within 24 working hours. Track the status under "Your listings" on this page.',
			'Tôi muốn sửa hoặc gỡ tin thì làm sao?' => 'How do I edit or remove my listing?',
			'Gọi hotline hoặc gửi email kèm tiêu đề tin, chuyên viên sẽ cập nhật giúp bạn.' => 'Call the hotline or email us with the listing title and we will update it.',
			'Vì sao tin bị từ chối?'      => 'Why was my listing rejected?',
			'Thường do thiếu thông tin, ảnh không rõ hoặc chưa xác minh được chủ sở hữu. Lý do được ghi kèm để bạn bổ sung và gửi lại.' => 'Usually missing details, unclear photos or unverified ownership. The reason is shown so you can update and resubmit.',
			'Chờ duyệt'                   => 'Pending review',
			'Đã duyệt'                    => 'Approved',
			'Bị từ chối'                  => 'Rejected',
			'Xem tin'                     => 'View listing',
			'Lý do'                       => 'Reason',
			'Đang gửi…'                   => 'Sending…',
			'Đã nhận tin ký gửi! Chuyên viên sẽ liên hệ xác minh trong 24 giờ làm việc.' => 'Listing received! A consultant will contact you within 24 working hours.',
			'Chưa gửi được, vui lòng thử lại hoặc gọi hotline.' => 'Could not submit, please try again or call the hotline.',
			'Vui lòng điền các ô bắt buộc.' => 'Please fill in the required fields.',
			'Tối đa %d ảnh.'              => 'Up to %d photos.',
			'Ảnh lớn hơn 5 MB đã được bỏ qua.' => 'Photos larger than 5 MB were skipped.',
			'Xóa ảnh'                     => 'Remove photo',
			'Bạn chưa ký gửi tin nào từ trình duyệt này.' => 'You have not submitted any listing from this browser.',
			'Vui lòng kiểm tra:'          => 'Please check:',
			'Ô xác nhận đồng ý chính sách bảo mật' => 'Privacy policy consent',
			'cần ít nhất %1$d ký tự, hiện có %2$d' => 'at least %1$d characters needed, currently %2$d',
			'%1$d / %2$d ký tự tối thiểu' => '%1$d / %2$d characters minimum',
			'Bạn đã gửi nhiều tin, vui lòng thử lại sau 1 giờ hoặc gọi hotline.' => 'Too many submissions, please try again in an hour or call the hotline.',
			'— Chọn —'                    => '— Select —',
			'Ký gửi nhà đất'              => 'List your property',
		);
	}
}
