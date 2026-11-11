<?php
/**
 * Thông tin pháp lý cho website demo (bật / tắt ở Nhà đất → Cài đặt → "Chế độ website demo"):
 *  - Ô cảnh báo trong tin nhà đất / dự án, dòng miễn trừ ở chân trang.
 *  - Trang "Miễn trừ trách nhiệm" và "Chính sách bảo mật" (Nghị định 13/2023/NĐ-CP) – tiếng Việt + tiếng Anh.
 *  - Ô đồng ý xử lý dữ liệu cá nhân (bắt buộc) trong form liên hệ Fluent Forms.
 *  - Chế độ demo: không gửi schema rao bán (tránh Google hiểu là tin thật), tùy chọn chặn lập chỉ mục.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Legal {

	/** Slug trang (vi => en). */
	const PAGES = array(
		'mien-tru-trach-nhiem' => 'disclaimer',
		'chinh-sach-bao-mat'   => 'privacy-policy',
	);

	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'footer_note' ), 1 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'wpseo_robots', array( __CLASS__, 'yoast_robots' ) );
		add_action( 'lp_demo_built', array( __CLASS__, 'install' ), 5 );
	}

	/** Đang ở chế độ website demo (mặc định bật). */
	public static function demo() {
		return '1' === (string) lp_option( 'demo_mode', '1' );
	}

	private static function noindex() {
		return self::demo() && '1' === (string) lp_option( 'demo_noindex', '1' );
	}

	/** Link trang chính sách theo ngôn ngữ hiện tại. */
	public static function url( $vi_slug ) {
		$page = get_page_by_path( $vi_slug );
		if ( ! $page ) {
			return '';
		}
		$id = function_exists( 'pll_get_post' ) ? ( pll_get_post( $page->ID ) ?: $page->ID ) : $page->ID;
		return get_permalink( $id );
	}

	/* ================================================================ Hiển thị */

	/** Ô cảnh báo trong trang chi tiết nhà đất / dự án. */
	public static function listing_notice() {
		if ( ! self::demo() ) {
			return;
		}
		printf(
			'<div class="lp-demo-notice" role="note"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/></svg><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html( lp_t( 'Tin minh họa.' ) ),
			esc_html( lp_t( 'Thông tin, giá, pháp lý và hình ảnh (tạo bằng AI) chỉ dùng để trình diễn website, không tương ứng với bất động sản có thật và không phải lời chào bán.' ) )
		);
	}

	public static function footer_note() {
		if ( is_admin() ) {
			return;
		}
		$links = array();
		foreach ( array( 'mien-tru-trach-nhiem' => lp_t( 'Miễn trừ trách nhiệm' ), 'chinh-sach-bao-mat' => lp_t( 'Chính sách bảo mật' ) ) as $slug => $label ) {
			$u = self::url( $slug );
			if ( $u ) {
				$links[] = '<a href="' . esc_url( $u ) . '">' . esc_html( $label ) . '</a>';
			}
		}
		$text = self::demo()
			? lp_t( 'Website demo phục vụ trình diễn giao diện. Toàn bộ tin đăng, dự án, giá và chủ đầu tư là dữ liệu giả lập; hình ảnh minh họa được tạo bằng AI; dữ liệu bản đồ © OpenStreetMap contributors.' )
			: lp_t( 'Thông tin trên website chỉ mang tính tham khảo, vui lòng kiểm tra pháp lý trước khi giao dịch.' );
		printf(
			'<div class="lp-legal-foot"><div class="lp-container"><p>%1$s</p>%2$s</div></div>',
			esc_html( $text ),
			$links ? '<p class="lp-legal-foot__links">' . implode( '<span aria-hidden="true">·</span>', $links ) . '</p>' : '' // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}

	/* ================================================================ SEO */

	public static function robots( $robots ) {
		if ( self::noindex() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['index'], $robots['max-image-preview'] );
		}
		return $robots;
	}

	public static function yoast_robots( $value ) {
		return self::noindex() ? 'noindex, follow' : $value;
	}

	/* ================================================================ Cài đặt nội dung */

	/** Tạo trang chính sách + ô đồng ý trong form (chạy khi dựng demo, gọi lại an toàn). */
	public static function install() {
		$ids = self::pages();
		if ( ! empty( $ids['chinh-sach-bao-mat'] ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids['chinh-sach-bao-mat'] );
		}
		$privacy_vi = self::url( 'chinh-sach-bao-mat' );
		$privacy_en = '';
		if ( function_exists( 'pll_get_post' ) && ! empty( $ids['chinh-sach-bao-mat'] ) ) {
			$en         = pll_get_post( $ids['chinh-sach-bao-mat'], 'en' );
			$privacy_en = $en ? get_permalink( $en ) : '';
		}
		self::form_consent(
			(int) lp_option( 'form_id' ),
			sprintf( 'Tôi đồng ý để LandPro xử lý họ tên, số điện thoại, email và nội dung tôi gửi nhằm liên hệ tư vấn, theo <a href="%s" target="_blank" rel="noopener">Chính sách bảo mật</a>.', esc_url( $privacy_vi ) ),
			'Vui lòng đồng ý với chính sách bảo mật để gửi thông tin'
		);
		self::form_consent(
			(int) lp_option( 'form_id_en' ),
			sprintf( 'I agree that LandPro may process my name, phone number, email and message to contact me, as described in the <a href="%s" target="_blank" rel="noopener">Privacy Policy</a>.', esc_url( $privacy_en ? $privacy_en : $privacy_vi ) ),
			'Please accept the privacy policy to submit'
		);
	}

	/** Thêm ô đồng ý (bắt buộc) trước nút gửi của form Fluent Forms, nếu chưa có. */
	public static function form_consent( $form_id, $html, $message ) {
		global $wpdb;
		if ( ! $form_id ) {
			return;
		}
		$table = $wpdb->prefix . 'fluentform_forms';
		$json  = $wpdb->get_var( $wpdb->prepare( "SELECT form_fields FROM {$table} WHERE id = %d", $form_id ) ); // phpcs:ignore WordPress.DB
		$data  = $json ? json_decode( $json, true ) : null;
		if ( ! $data || empty( $data['fields'] ) ) {
			return;
		}
		$field = array(
			'element'        => 'gdpr_agreement',
			'attributes'     => array(
				'type'  => 'checkbox',
				'name'  => 'dong_y_bao_mat',
				'value' => false,
				'class' => 'ff_gdpr_field',
			),
			'settings'       => array(
				'label'             => '',
				'tnc_html'          => $html,
				'admin_field_label' => 'Đồng ý chính sách bảo mật',
				'has_checkbox'      => true,
				'container_class'   => 'lp-consent',
				'validation_rules'  => array(
					'required' => array(
						'value'   => true,
						'message' => $message,
					),
				),
				'conditional_logics' => array(),
			),
			'editor_options' => array(
				'title'      => 'GDPR Agreement',
				'icon_class' => 'ff-edit-gdpr',
				'template'   => 'termsCheckbox',
			),
			'uniqElKey'      => 'el_lpconsent',
		);
		$found = false;
		foreach ( $data['fields'] as $i => $f ) {
			if ( isset( $f['element'] ) && 'gdpr_agreement' === $f['element'] ) {
				$data['fields'][ $i ]['settings']['tnc_html']                              = $html; // Cập nhật link chính sách.
				$data['fields'][ $i ]['settings']['validation_rules']['required']['message'] = $message;
				$found = true;
			}
		}
		if ( ! $found ) {
			$field['index']   = count( $data['fields'] );
			$data['fields'][] = $field;
		}
		$wpdb->update( $table, array( 'form_fields' => wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ), array( 'id' => $form_id ) ); // phpcs:ignore WordPress.DB
	}

	/** Tạo / cập nhật 2 trang chính sách (vi + en), trả về ID trang tiếng Việt. */
	private static function pages() {
		$c     = self::content();
		$email = lp_option( 'email', get_option( 'admin_email' ) );
		$out   = array();
		foreach ( self::PAGES as $vi_slug => $en_slug ) {
			$vi = self::upsert( $vi_slug, $c[ $vi_slug ]['vi'][0], str_replace( '{email}', esc_html( $email ), $c[ $vi_slug ]['vi'][1] ), 'vi' );
			$en = self::upsert( $en_slug, $c[ $vi_slug ]['en'][0], str_replace( '{email}', esc_html( $email ), $c[ $vi_slug ]['en'][1] ), 'en' );
			if ( $vi && $en && function_exists( 'pll_save_post_translations' ) ) {
				pll_save_post_translations( array( 'vi' => $vi, 'en' => $en ) );
			}
			$out[ $vi_slug ] = $vi;
		}
		return $out;
	}

	private static function upsert( $slug, $title, $html, $lang ) {
		$existing = get_posts(
			array(
				'post_type'      => 'page',
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'lang'           => '',
				'fields'         => 'ids',
			)
		);
		$args = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $html,
		);
		if ( $existing ) {
			$args['ID'] = $existing[0];
			$id         = wp_update_post( $args );
		} else {
			$id = wp_insert_post( $args );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, $lang );
		}
		update_post_meta( $id, '_lp_demo', 1 );
		return (int) $id;
	}

	/** Nội dung 2 trang (HTML đơn giản, sửa được trong trình soạn thảo). */
	private static function content() {
		$h = function ( $t ) {
			return '<h2>' . $t . '</h2>';
		};
		$ul = function ( $items ) {
			return '<ul><li>' . implode( '</li><li>', $items ) . '</li></ul>';
		};
		$updated = wp_date( 'd/m/Y' );

		$dis_vi = '<p><em>Cập nhật: ' . $updated . '</em></p>'
			. '<p>Website LandPro là <strong>website demo</strong> được xây dựng để trình diễn giao diện và tính năng của một website bất động sản. Website không phải sàn giao dịch bất động sản, không hoạt động môi giới, không nhận đặt cọc hay thu bất kỳ khoản tiền nào.</p>'
			. $h( '1. Nội dung tin đăng và dự án' )
			. $ul(
				array(
					'Toàn bộ tin mua bán, cho thuê, dự án, giá, diện tích, chủ đầu tư, tiến độ, pháp lý, tiện ích và chính sách bán hàng là <strong>dữ liệu giả lập</strong>, không tương ứng với bất động sản hay dự án có thật.',
					'Tên dự án, tên chủ đầu tư và địa chỉ được đặt để minh họa; mọi sự trùng hợp với tổ chức, cá nhân hoặc dự án thực tế là ngẫu nhiên.',
					'Vị trí ghim trên bản đồ là vị trí gần đúng, chỉ để minh họa tính năng bản đồ.',
				)
			)
			. $h( '2. Hình ảnh' )
			. '<p>Hình ảnh nhà đất, dự án và banner là <strong>ảnh minh họa được tạo bằng công cụ AI</strong>, không phải ảnh chụp thực tế của bất kỳ tài sản nào.</p>'
			. $h( '3. Bài viết và công cụ' )
			. $ul(
				array(
					'Bài viết tin tức chỉ mang tính tham khảo, không phải tư vấn pháp lý, tài chính hay đầu tư. Vui lòng tham khảo cơ quan nhà nước có thẩm quyền và chuyên gia trước khi quyết định.',
					'Công cụ phong thủy (thước Lỗ Ban, xem tuổi, hướng nhà) chỉ mang tính tham khảo văn hóa.',
				)
			)
			. $h( '4. Bản đồ và dịch vụ bên thứ ba' )
			. '<p>Dữ liệu bản đồ © OpenStreetMap contributors (giấy phép ODbL), nền bản đồ OpenFreeMap / OpenMapTiles hoặc nhà cung cấp được cấu hình. Các nhãn hiệu, tên sản phẩm thuộc về chủ sở hữu tương ứng.</p>'
			. $h( '5. Giới hạn trách nhiệm' )
			. '<p>Chủ website không chịu trách nhiệm cho bất kỳ quyết định hay thiệt hại nào phát sinh từ việc sử dụng nội dung trên website demo này.</p>'
			. $h( '6. Liên hệ' )
			. '<p>Mọi thắc mắc hoặc yêu cầu gỡ nội dung, vui lòng gửi email tới <a href="mailto:{email}">{email}</a>.</p>';

		$dis_en = '<p><em>Last updated: ' . $updated . '</em></p>'
			. '<p>LandPro is a <strong>demo website</strong> built to showcase the design and features of a real estate website. It is not a real estate exchange, does not provide brokerage services, and does not accept deposits or any payments.</p>'
			. $h( '1. Listings and projects' )
			. $ul(
				array(
					'All sale and rental listings, projects, prices, areas, developers, timelines, legal status, amenities and sales policies are <strong>fictional sample data</strong> and do not correspond to any real property or project.',
					'Project names, developer names and addresses are illustrative; any resemblance to real organizations, people or projects is coincidental.',
					'Map pins show approximate locations for demonstration only.',
				)
			)
			. $h( '2. Images' )
			. '<p>Property, project and banner images are <strong>illustrations generated with AI tools</strong>, not photographs of any real property.</p>'
			. $h( '3. Articles and tools' )
			. $ul(
				array(
					'News articles are for general reference only and are not legal, financial or investment advice.',
					'Feng shui tools are provided for cultural reference only.',
				)
			)
			. $h( '4. Maps and third-party services' )
			. '<p>Map data © OpenStreetMap contributors (ODbL), basemap by OpenFreeMap / OpenMapTiles or the configured provider. Trademarks belong to their respective owners.</p>'
			. $h( '5. Limitation of liability' )
			. '<p>The site owner is not liable for any decision or loss arising from the use of content on this demo website.</p>'
			. $h( '6. Contact' )
			. '<p>For questions or content removal requests, email <a href="mailto:{email}">{email}</a>.</p>';

		$pri_vi = '<p><em>Cập nhật: ' . $updated . '</em></p>'
			. '<p>Chính sách này giải thích cách website demo LandPro thu thập và xử lý dữ liệu cá nhân, phù hợp Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân.</p>'
			. $h( '1. Dữ liệu được thu thập' )
			. $ul(
				array(
					'Khi bạn gửi form liên hệ: họ tên, số điện thoại, email (nếu có), nhu cầu và nội dung tin nhắn.',
					'Khi bạn bình luận: tên, email và nội dung bình luận.',
					'Dữ liệu kỹ thuật cơ bản do máy chủ ghi nhận (địa chỉ IP, trình duyệt) phục vụ bảo mật.',
				)
			)
			. $h( '2. Mục đích và cơ sở xử lý' )
			. '<p>Dữ liệu chỉ dùng để liên hệ, trả lời yêu cầu tư vấn của bạn. Việc xử lý dựa trên <strong>sự đồng ý</strong> bạn thể hiện khi đánh dấu ô đồng ý trong form. Chúng tôi không bán, không cho thuê và không dùng dữ liệu của bạn cho quảng cáo.</p>'
			. $h( '3. Thời gian lưu trữ' )
			. '<p>Vì đây là website demo, dữ liệu gửi qua form được xóa định kỳ và lưu tối đa 12 tháng, hoặc xóa sớm hơn khi bạn yêu cầu.</p>'
			. $h( '4. Chia sẻ dữ liệu' )
			. '<p>Dữ liệu được lưu trên máy chủ lưu trữ website. Khi xem bản đồ, trình duyệt của bạn tải ô bản đồ từ nhà cung cấp bản đồ (OpenFreeMap, OpenStreetMap hoặc Google), các bên này có thể nhận địa chỉ IP theo chính sách riêng của họ.</p>'
			. $h( '5. Cookie và lưu trữ trình duyệt' )
			. '<p>Website dùng cookie cần thiết để ghi nhớ ngôn ngữ và đăng nhập, và bộ nhớ trình duyệt để ghi nhớ một số lựa chọn hiển thị. Website không dùng cookie quảng cáo.</p>'
			. $h( '6. Quyền của bạn' )
			. '<p>Bạn có quyền được biết, truy cập, chỉnh sửa, yêu cầu xóa dữ liệu, rút lại sự đồng ý và phản đối việc xử lý. Gửi yêu cầu tới <a href="mailto:{email}">{email}</a>; chúng tôi phản hồi trong vòng 72 giờ làm việc.</p>'
			. $h( '7. Thay đổi chính sách' )
			. '<p>Chính sách có thể được cập nhật; ngày cập nhật ghi ở đầu trang.</p>';

		$pri_en = '<p><em>Last updated: ' . $updated . '</em></p>'
			. '<p>This policy explains how the LandPro demo website collects and processes personal data, in line with Vietnam\'s Decree 13/2023/ND-CP on personal data protection.</p>'
			. $h( '1. Data we collect' )
			. $ul(
				array(
					'When you submit the contact form: name, phone number, email (optional), your needs and message.',
					'When you comment: name, email and the comment.',
					'Basic technical data logged by the server (IP address, browser) for security.',
				)
			)
			. $h( '2. Purpose and legal basis' )
			. '<p>Data is used only to contact you and answer your enquiry, based on the <strong>consent</strong> you give by ticking the agreement box. We do not sell, rent or use your data for advertising.</p>'
			. $h( '3. Retention' )
			. '<p>As this is a demo website, form submissions are deleted regularly and kept for no longer than 12 months, or earlier on request.</p>'
			. $h( '4. Sharing' )
			. '<p>Data is stored on the website hosting server. When you view a map, your browser loads map tiles from the map provider (OpenFreeMap, OpenStreetMap or Google), who may receive your IP address under their own policies.</p>'
			. $h( '5. Cookies and browser storage' )
			. '<p>We use essential cookies to remember your language and login, and browser storage to remember some display preferences. No advertising cookies are used.</p>'
			. $h( '6. Your rights' )
			. '<p>You may request to know, access, correct or delete your data, withdraw consent or object to processing by emailing <a href="mailto:{email}">{email}</a>. We respond within 72 working hours.</p>'
			. $h( '7. Changes' )
			. '<p>This policy may be updated; the date at the top shows the latest version.</p>';

		return array(
			'mien-tru-trach-nhiem' => array(
				'vi' => array( 'Miễn trừ trách nhiệm', $dis_vi ),
				'en' => array( 'Disclaimer', $dis_en ),
			),
			'chinh-sach-bao-mat'   => array(
				'vi' => array( 'Chính sách bảo mật', $pri_vi ),
				'en' => array( 'Privacy Policy', $pri_en ),
			),
		);
	}
}
