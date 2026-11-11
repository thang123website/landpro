<?php
/**
 * Hàm dùng chung: định nghĩa trường dữ liệu, định dạng giá, icon, nạp template.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Định nghĩa trường dữ liệu của từng loại nội dung (nguồn duy nhất cho meta box, REST, demo).
 *
 * type: text | number | select | textarea | url | checkbox | gallery | post
 *
 * @param string $post_type property|project.
 * @return array<string, array>
 */
function lp_fields( $post_type ) {
	$directions = array(
		''          => '— Chọn —',
		'dong'      => 'Đông',
		'tay'       => 'Tây',
		'nam'       => 'Nam',
		'bac'       => 'Bắc',
		'dong-nam'  => 'Đông Nam',
		'dong-bac'  => 'Đông Bắc',
		'tay-nam'   => 'Tây Nam',
		'tay-bac'   => 'Tây Bắc',
	);

	if ( 'project' === $post_type ) {
		return array(
			'status'     => array( 'label' => 'Trạng thái', 'type' => 'select', 'options' => lp_project_statuses() ),
			'developer'  => array( 'label' => 'Chủ đầu tư', 'type' => 'text' ),
			'address'    => array( 'label' => 'Địa chỉ', 'type' => 'text' ),
			'price_text' => array( 'label' => 'Giá bán', 'type' => 'text', 'placeholder' => 'VD: 45 - 60 triệu/m²' ),
			'scale'      => array( 'label' => 'Quy mô', 'type' => 'text', 'placeholder' => 'VD: 2 tháp, 1.200 căn' ),
			'area_total' => array( 'label' => 'Tổng diện tích', 'type' => 'text', 'placeholder' => 'VD: 5,2 ha' ),
			'handover'   => array( 'label' => 'Bàn giao', 'type' => 'text', 'placeholder' => 'VD: Quý IV/2027' ),
			'legal'      => array( 'label' => 'Pháp lý', 'type' => 'text' ),
			'location_desc' => array( 'label' => 'Vị trí & kết nối (mỗi dòng một ý)', 'type' => 'textarea' ),
			'amenities'  => array( 'label' => 'Tiện ích (mỗi dòng một tiện ích)', 'type' => 'textarea' ),
			'design'     => array( 'label' => 'Thiết kế (mô tả kiến trúc, mặt bằng, căn hộ)', 'type' => 'textarea' ),
			'policy'     => array( 'label' => 'Chính sách bán hàng (mỗi dòng một ý)', 'type' => 'textarea' ),
			'gallery'    => array( 'label' => 'Thư viện ảnh', 'type' => 'gallery' ),
			'video_url'  => array( 'label' => 'Video YouTube', 'type' => 'url' ),
			'featured'   => array( 'label' => 'Dự án nổi bật', 'type' => 'checkbox' ),
		);
	}

	return array(
		'price'       => array( 'label' => 'Giá (VNĐ)', 'type' => 'number', 'help' => 'Bán: tổng giá. Cho thuê: giá mỗi tháng. Để trống nếu thỏa thuận.' ),
		'price_label' => array( 'label' => 'Ghi chú giá', 'type' => 'text', 'placeholder' => 'VD: Thỏa thuận, Đã gồm VAT' ),
		'area'        => array( 'label' => 'Diện tích (m²)', 'type' => 'number', 'step' => '0.1' ),
		'bedrooms'    => array( 'label' => 'Phòng ngủ', 'type' => 'number' ),
		'bathrooms'   => array( 'label' => 'Phòng tắm / WC', 'type' => 'number' ),
		'floors'      => array( 'label' => 'Số tầng', 'type' => 'number' ),
		'frontage'    => array( 'label' => 'Mặt tiền (m)', 'type' => 'number', 'step' => '0.1' ),
		'direction'   => array( 'label' => 'Hướng nhà', 'type' => 'select', 'options' => $directions ),
		'legal'       => array(
			'label'   => 'Pháp lý',
			'type'    => 'select',
			'options' => array(
				''          => '— Chọn —',
				'so-hong'   => 'Sổ hồng',
				'so-do'     => 'Sổ đỏ',
				'hdmb'      => 'Hợp đồng mua bán',
				'cho-so'    => 'Đang chờ sổ',
			),
		),
		'furniture'   => array( 'label' => 'Nội thất', 'type' => 'text', 'placeholder' => 'VD: Đầy đủ, Cơ bản, Bàn giao thô' ),
		'address'     => array( 'label' => 'Địa chỉ', 'type' => 'text' ),
		'project_id'  => array( 'label' => 'Thuộc dự án', 'type' => 'post', 'post_type' => 'project' ),
		'badge'       => array( 'label' => 'Nhãn nổi bật', 'type' => 'text', 'placeholder' => 'VD: Mặt tiền đẹp, Giá tốt' ),
		'gallery'     => array( 'label' => 'Thư viện ảnh', 'type' => 'gallery' ),
		'video_url'   => array( 'label' => 'Video YouTube', 'type' => 'url' ),
		'featured'    => array( 'label' => 'Tin nổi bật', 'type' => 'checkbox' ),
	);
}

/** Trạng thái dự án. */
function lp_project_statuses() {
	return array(
		''           => '— Chọn —',
		'dang-ban'   => 'Đang mở bán',
		'sap-mo-ban' => 'Sắp mở bán',
		'da-ban-giao' => 'Đã bàn giao',
	);
}

/**
 * Đọc một trường của bài (meta key có tiền tố _lp_).
 *
 * @param string   $key     Tên trường.
 * @param int|null $post_id ID bài.
 * @return mixed
 */
function lp_get( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return get_post_meta( $post_id, '_lp_' . $key, true );
}

/**
 * Nhãn hiển thị của trường select (VD: 'dong-nam' -> 'Đông Nam').
 *
 * @param string   $key     Tên trường.
 * @param int|null $post_id ID bài.
 * @return string
 */
function lp_get_label( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$value   = lp_get( $key, $post_id );
	$fields  = lp_fields( get_post_type( $post_id ) );
	if ( isset( $fields[ $key ]['options'][ $value ] ) && '' !== $value ) {
		return lp_t( $fields[ $key ]['options'][ $value ] );
	}
	return is_scalar( $value ) ? (string) $value : '';
}

/**
 * Định dạng số tiền VNĐ kiểu Việt Nam: 6,8 tỷ / 850 triệu / 15 triệu/tháng.
 *
 * @param float|string $amount Số tiền VNĐ.
 * @param bool         $rent   Có phải giá thuê theo tháng.
 * @return string
 */
function lp_format_money( $amount, $rent = false ) {
	$amount = (float) $amount;
	if ( $amount <= 0 ) {
		return '';
	}
	if ( $amount >= 1e9 ) {
		$text = lp_number( $amount / 1e9 ) . ' ' . lp_t( 'tỷ' );
	} elseif ( $amount >= 1e6 ) {
		$text = lp_number( $amount / 1e6 ) . ' ' . lp_t( 'triệu' );
	} else {
		$text = number_format( $amount, 0, ',', '.' ) . ' ' . lp_t( 'đ' );
	}
	return $rent ? $text . lp_t( '/tháng' ) : $text;
}

/** Số tối đa 2 chữ số thập phân, dấu phẩy thập phân, bỏ số 0 thừa. */
function lp_number( $n ) {
	$s = number_format( (float) $n, 2, ',', '.' );
	return rtrim( rtrim( $s, '0' ), ',' );
}

/** Bài nhà đất có phải tin cho thuê. */
function lp_is_rent( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return 'cho-thue' === lp_base_slug( lp_first_term( $post_id, 'lp_purpose' ) );
}

/** Giá hiển thị của một nhà đất. */
function lp_price_html( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$price   = lp_format_money( lp_get( 'price', $post_id ), lp_is_rent( $post_id ) );
	$label   = lp_get( 'price_label', $post_id );
	if ( '' === $price ) {
		return esc_html( $label ? $label : lp_t( 'Thỏa thuận' ) );
	}
	return esc_html( $price );
}

/** Tên term đầu tiên của bài trong taxonomy. */
function lp_first_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/** Chuỗi khu vực "Quận 7, TP. Hồ Chí Minh" (từ con lên cha). */
function lp_location_text( $post_id ) {
	$term = lp_first_term( $post_id, 'lp_location' );
	if ( ! $term ) {
		return '';
	}
	// Ưu tiên term sâu nhất (quận/huyện).
	$terms = get_the_terms( $post_id, 'lp_location' );
	foreach ( $terms as $t ) {
		if ( $t->parent ) {
			$term = $t;
			break;
		}
	}
	$names = array( $term->name );
	$parent = $term->parent;
	while ( $parent ) {
		$p = get_term( $parent, 'lp_location' );
		if ( ! $p || is_wp_error( $p ) ) {
			break;
		}
		$names[] = $p->name;
		$parent  = $p->parent;
	}
	return implode( ', ', $names );
}

/**
 * Icon SVG nội tuyến (không cần thư viện icon ngoài).
 *
 * @param string $name Tên icon.
 * @return string
 */
function lp_icon( $name ) {
	$paths = array(
		'area'     => '<path d="M3 3h7v2H6.4l4.3 4.3-1.4 1.4L5 6.4V10H3V3zm18 0v7h-2V6.4l-4.3 4.3-1.4-1.4L17.6 5H14V3h7zM3 21v-7h2v3.6l4.3-4.3 1.4 1.4L6.4 19H10v2H3zm18 0h-7v-2h3.6l-4.3-4.3 1.4-1.4 4.3 4.3V14h2v7z"/>',
		'bed'      => '<path d="M3 6h2v7h6V8h7a3 3 0 0 1 3 3v7h-2v-3H5v3H3V6zm4 1.5A2.5 2.5 0 1 1 7 12.5a2.5 2.5 0 0 1 0-5z"/>',
		'bath'     => '<path d="M7 4a2 2 0 0 1 2 2h-2v1h4V6a4 4 0 0 0-8 0v6H2v2h1v1a5 5 0 0 0 3 4.6V21h2v-1h8v1h2v-1.4a5 5 0 0 0 3-4.6v-1h1v-2H5V6a2 2 0 0 1 2-2z"/>',
		'pin'      => '<path d="M12 2a7 7 0 0 1 7 7c0 5.2-7 13-7 13S5 14.2 5 9a7 7 0 0 1 7-7zm0 4.5A2.5 2.5 0 1 0 12 11.5 2.5 2.5 0 0 0 12 6.5z"/>',
		'phone'    => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8z"/>',
		'compass'  => '<path d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20zm0 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16zm4.5 3.5-2.8 6.2-6.2 2.8 2.8-6.2 6.2-2.8zM12 11a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>',
		'layers'   => '<path d="M12 2 1 8l11 6 11-6-11-6zm-8.6 9.6L1 13l11 6 11-6-2.4-1.4L12 16.3l-8.6-4.7z"/>',
		'doc'      => '<path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5zM8 12h8v2H8v-2zm0 4h8v2H8v-2z"/>',
		'building' => '<path d="M4 21V3h11v6h5v12h-7v-4h-2v4H4zm3-15v2h2V6H7zm4 0v2h2V6h-2zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm-4 4v2h2v-2H7zm4 0v2h2v-2h-2zm5-1v2h2v-2h-2z"/>',
		'calendar' => '<path d="M7 2h2v2h6V2h2v2h3v18H4V4h3V2zm-1 8v10h12V10H6z"/>',
		'zalo'     => '<path d="M12 3C6.5 3 2 6.9 2 11.7c0 2.6 1.3 4.9 3.4 6.5L4.6 21l3.3-1.6c1.3.4 2.6.6 4.1.6 5.5 0 10-3.9 10-8.7S17.5 3 12 3zm-3.9 10.9H5.3v-.7l2-2.6H5.4v-.9h2.8v.7l-2 2.6h1.9v.9zm2.8 0h-.9V9.7h.9v4.2zm3.8 0h-.8l-.1-.3a1.6 1.6 0 1 1 0-2.7v-.2h.9v3.2zm3.1.1a1.7 1.7 0 1 1 0-3.3 1.7 1.7 0 0 1 0 3.3z"/>',
		'mail'     => '<path d="M3 5h18v14H3V5zm2 2v.5l7 4.5 7-4.5V7H5zm14 2.9-7 4.5-7-4.5V17h14V9.9z"/>',
		'search'   => '<path d="M10 3a7 7 0 0 1 5.6 11.2l5.1 5.1-1.4 1.4-5.1-5.1A7 7 0 1 1 10 3zm0 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/>',
		'check'    => '<path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>',
		'play'     => '<path d="M8 5v14l11-7z"/>',
	);
	// Logo Zalo chính thức (SVG lưu trong plugin).
	if ( 'zalo' === $name ) {
		return '<img class="lp-icon lp-icon-zalo" src="' . esc_url( LP_URL . 'assets/img/zalo.svg' ) . '" width="20" height="20" alt="" aria-hidden="true">';
	}
	// Icon dạng nét mảnh (dùng trong nút): kính lúp.
	$stroke = array(
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4.5-4.5"/>',
	);
	if ( isset( $stroke[ $name ] ) ) {
		return '<svg class="lp-icon lp-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $stroke[ $name ] . '</svg>';
	}
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="lp-icon lp-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Tìm template: ưu tiên theme (thư mục landpro/), sau đó plugin.
 *
 * @param string $name Đường dẫn tương đối, VD 'parts/card-property.php'.
 * @return string
 */
function lp_locate_template( $name ) {
	$theme = locate_template( array( 'landpro/' . $name ) );
	return $theme ? $theme : LP_DIR . 'templates/' . $name;
}

/**
 * Nạp template kèm biến.
 *
 * @param string $name Template.
 * @param array  $args Biến truyền vào ($args trong template).
 */
function lp_template( $name, $args = array() ) {
	$file = lp_locate_template( $name );
	if ( file_exists( $file ) ) {
		include $file;
	}
}

/** Lấy cài đặt plugin. */
function lp_option( $key, $default = '' ) {
	$opts = get_option( 'lp_settings', array() );
	return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
}

/** Danh sách ID ảnh gallery (gồm ảnh đại diện ở đầu). */
function lp_gallery_ids( $post_id ) {
	$ids   = array_filter( array_map( 'absint', explode( ',', (string) lp_get( 'gallery', $post_id ) ) ) );
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		array_unshift( $ids, (int) $thumb );
	}
	return array_values( array_unique( $ids ) );
}

/** Lấy ID video YouTube từ URL. */
function lp_youtube_id( $url ) {
	if ( preg_match( '~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/** Số điện thoại chỉ còn chữ số (dùng cho tel: và zalo). */
function lp_phone_digits( $phone ) {
	return preg_replace( '/\D+/', '', (string) $phone );
}

/**
 * Dịch chuỗi giao diện qua Polylang (Ngôn ngữ → Dịch chuỗi, nhóm "LandPro").
 * Không có Polylang thì trả nguyên chuỗi tiếng Việt.
 *
 * @param string $text Chuỗi gốc tiếng Việt.
 * @return string
 */
function lp_t( $text ) {
	if ( ! function_exists( 'pll__' ) ) {
		return $text;
	}
	$out = pll__( $text );
	// Chuỗi mới chưa có trong Polylang: dùng từ điển của plugin (lp_strings) cho bản tiếng Anh.
	$cur = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	if ( $out === $text && $cur && 'vi' !== $cur && function_exists( 'lp_strings' ) ) {
		static $dict = null;
		if ( null === $dict ) {
			$dict = lp_strings();
		}
		if ( isset( $dict[ $text ] ) ) {
			return $dict[ $text ];
		}
	}
	return $out;
}
