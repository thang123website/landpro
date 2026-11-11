<?php
/**
 * Trang cài đặt: Nhà đất → Cài đặt.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Settings {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function menu() {
		add_submenu_page( 'edit.php?post_type=property', 'Cài đặt LandPro', 'Cài đặt', 'manage_options', 'lp-settings', array( __CLASS__, 'page' ) );
	}

	public static function register() {
		register_setting(
			'lp_settings',
			'lp_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	public static function fields() {
		return array(
			'agent_name' => array( 'Tên người / đơn vị tư vấn', 'text' ),
			'hotline'    => array( 'Hotline', 'text' ),
			'zalo'       => array( 'Số Zalo (để trống = dùng hotline)', 'text' ),
			'email'      => array( 'Email liên hệ', 'email' ),
			'address'    => array( 'Địa chỉ văn phòng', 'text' ),
			'form_id'    => array( 'Form liên hệ (Fluent Forms)', 'form' ),
			'per_page'   => array( 'Số tin mỗi trang ở trang danh sách', 'number' ),
			'demo_mode'  => array( 'Chế độ website demo', 'checkbox', 'Hiện thông báo "website demo", ô cảnh báo trong tin và dòng miễn trừ ở chân trang; không gửi dữ liệu cấu trúc rao bán cho Google. Tắt khi website chạy thật.' ),
			'demo_noindex' => array( 'Chặn Google lập chỉ mục (demo)', 'checkbox', 'Chỉ áp dụng khi bật chế độ demo: thêm noindex để tin giả lập không xuất hiện trên Google.' ),
		);
	}

	public static function sanitize( $input ) {
		// Giữ các khóa không có trên form (vd. form_id_en do bản tiếng Anh tạo).
		$old = get_option( 'lp_settings', array() );
		$out = array_diff_key( is_array( $old ) ? $old : array(), self::fields() );
		foreach ( self::fields() as $key => $field ) {
			$v = isset( $input[ $key ] ) ? $input[ $key ] : '';
			if ( 'checkbox' === $field[1] ) {
				$out[ $key ] = empty( $v ) ? '0' : '1'; // '0' = tắt (lp_option coi '' là chưa đặt).
			} elseif ( 'email' === $field[1] ) {
				$out[ $key ] = sanitize_email( $v );
			} elseif ( in_array( $field[1], array( 'number', 'form' ), true ) ) {
				$out[ $key ] = absint( $v ) ? absint( $v ) : '';
			} else {
				$out[ $key ] = sanitize_text_field( $v );
			}
		}
		return $out;
	}

	/** Danh sách form Fluent Forms. */
	public static function fluent_forms() {
		global $wpdb;
		$table = $wpdb->prefix . 'fluentform_forms';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return array();
		}
		return $wpdb->get_results( "SELECT id, title FROM {$table} WHERE status = 'published' ORDER BY id DESC" ); // phpcs:ignore WordPress.DB
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opts = get_option( 'lp_settings', array() );
		?>
		<div class="wrap">
			<h1>Cài đặt LandPro Real Estate</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'lp_settings' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( self::fields() as $key => $field ) : ?>
						<?php $value = isset( $opts[ $key ] ) ? $opts[ $key ] : ''; ?>
						<tr>
							<th scope="row"><label for="lp_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
							<td>
								<?php if ( 'form' === $field[1] ) : ?>
									<?php $forms = self::fluent_forms(); ?>
									<?php if ( $forms ) : ?>
										<select id="lp_<?php echo esc_attr( $key ); ?>" name="lp_settings[<?php echo esc_attr( $key ); ?>]">
											<option value="">— Không dùng form —</option>
											<?php foreach ( $forms as $form ) : ?>
												<option value="<?php echo (int) $form->id; ?>" <?php selected( (int) $value, (int) $form->id ); ?>><?php echo esc_html( $form->title ); ?> (#<?php echo (int) $form->id; ?>)</option>
											<?php endforeach; ?>
										</select>
										<p class="description">Form hiển thị ở trang chi tiết nhà đất / dự án và shortcode <code>[lp_contact_form]</code>.</p>
									<?php else : ?>
										<p>Chưa có form nào. Hãy cài và tạo form trong <strong>Fluent Forms</strong>.</p>
									<?php endif; ?>
								<?php elseif ( 'checkbox' === $field[1] ) : ?>
									<label><input type="checkbox" id="lp_<?php echo esc_attr( $key ); ?>" name="lp_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( '1', (string) lp_option( $key, '1' ) ); ?>> Bật</label>
									<?php if ( ! empty( $field[2] ) ) : ?><p class="description"><?php echo esc_html( $field[2] ); ?></p><?php endif; ?>
								<?php else : ?>
									<input class="regular-text" type="<?php echo esc_attr( $field[1] ); ?>" id="lp_<?php echo esc_attr( $key ); ?>" name="lp_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<h2>Kích thước ảnh khuyên dùng</h2>
			<p class="description">Ảnh đúng tỉ lệ hiển thị sắc nét, không bị cắt mất chủ thể và giúp trang tải nhanh. Nên lưu WebP (hoặc JPG chất lượng 80%).</p>
			<table class="widefat striped" style="max-width:980px">
				<thead><tr><th>Vị trí</th><th>Kích thước (px)</th><th>Tỉ lệ</th><th>Dung lượng</th><th>Ghi chú</th></tr></thead>
				<tbody>
					<tr><td>Slide trang chủ (widget Hero)</td><td>1920 × 1080</td><td>16:9</td><td>≤ 500 KB</td><td>Chủ thể ở giữa, điện thoại cắt hai bên.</td></tr>
					<tr><td>Banner trang con (Giao diện → Tùy biến)</td><td>1920 × 500</td><td>~4:1</td><td>≤ 300 KB</td><td>Ảnh tối nhẹ để chữ trắng dễ đọc.</td></tr>
					<tr><td>Ảnh đại diện + thư viện nhà đất</td><td>1600 × 1067 (hoặc 1600 × 1200)</td><td>3:2 (4:3)</td><td>≤ 400 KB/ảnh</td><td>Tối thiểu 1200 px chiều ngang, nên 5 – 15 ảnh.</td></tr>
					<tr><td>Ảnh đại diện dự án</td><td>1600 × 1067</td><td>3:2</td><td>≤ 400 KB</td><td>Thẻ dự án cắt dọc 2:3 – đặt tòa nhà ở giữa.</td></tr>
					<tr><td>Ảnh bài viết tin tức</td><td>1200 × 675</td><td>16:9</td><td>≤ 300 KB</td><td>Cũng là ảnh chia sẻ Facebook / Zalo.</td></tr>
					<tr><td>Ảnh chia sẻ trang (Yoast)</td><td>1200 × 630</td><td>1.91:1</td><td>≤ 300 KB</td><td></td></tr>
					<tr><td>Logo</td><td>cao 120 (rộng tùy logo)</td><td>—</td><td>≤ 50 KB</td><td>PNG / WebP / SVG nền trong suốt; hiển thị cao ~60 px.</td></tr>
					<tr><td>Ảnh khách gửi ở trang Ký gửi</td><td>≥ 1200 ngang</td><td>3:2 hoặc 4:3</td><td>≤ 5 MB/ảnh</td><td>Tối đa 10 ảnh, website lưu kèm bản thu nhỏ.</td></tr>
				</tbody>
			</table>

			<hr>
			<h2>Shortcode</h2>
			<p>Dùng trong widget <em>Shortcode</em> của Elementor hoặc trình soạn thảo. Trong Elementor nên dùng các widget nhóm <strong>LandPro</strong> để chỉnh trực quan.</p>
			<ul>
				<li><code>[lp_properties purpose="ban" per_page="6" columns="3" pagination="yes" use_filters="yes" show_toolbar="yes"]</code></li>
				<li><code>[lp_projects status="dang-ban" per_page="6" columns="3"]</code></li>
				<li><code>[lp_search tabs="yes" fields="kw,type,location,price"]</code></li>
				<li><code>[lp_contact_form]</code></li>
			</ul>
		</div>
		<?php
	}
}
