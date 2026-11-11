<?php
/**
 * Bộ icon SVG nhiều màu (tự vẽ cho LandPro, dùng tự do).
 * Phong cách: nền tròn nhạt + mảng màu + viền navy bo tròn. viewBox 48x48.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Danh sách icon: tên => nội dung SVG bên trong thẻ <svg>.
 *
 * @return array<string,string>
 */
function lp_color_icon_paths() {
	$n = '#0F1F3D'; // Viền navy.
	$s = 'stroke="' . $n . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

	return array(
		// Khiên xác thực.
		'shield'    => '<circle cx="24" cy="24" r="22" fill="#FDE2E2"/><path d="M24 7 37 12v10c0 9-5.6 15.6-13 19-7.4-3.4-13-10-13-19V12z" fill="#E53935" ' . $s . '/><path d="M24 7v34c-7.4-3.4-13-10-13-19V12z" fill="#F87171"/><path d="M24 7 37 12v10c0 9-5.6 15.6-13 19-7.4-3.4-13-10-13-19V12z" fill="none" ' . $s . '/><path d="m18 24 4.5 4.5L31 20" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>',
		// Hồ sơ pháp lý có chữ ký.
		'document'  => '<circle cx="24" cy="24" r="22" fill="#DBEAFE"/><path d="M13 7h16l7 7v27H13z" fill="#fff" ' . $s . '/><path d="M29 7v7h7" fill="#BFDBFE" ' . $s . '/><path d="M18 19h12M18 24h12M18 29h7" ' . $s . ' fill="none"/><circle cx="31" cy="34" r="6" fill="#F59E0B" ' . $s . '/><path d="m28.5 34 1.8 1.8 3.4-3.6" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		// Vay ngân hàng: đồng xu + phần trăm.
		'loan'      => '<circle cx="24" cy="24" r="22" fill="#FEF3C7"/><ellipse cx="20" cy="33" rx="10" ry="4" fill="#F59E0B" ' . $s . '/><path d="M10 29v4c0 2.2 4.5 4 10 4s10-1.8 10-4v-4" fill="#FBBF24" ' . $s . '/><ellipse cx="20" cy="29" rx="10" ry="4" fill="#FCD34D" ' . $s . '/><circle cx="32" cy="17" r="9" fill="#22C55E" ' . $s . '/><path d="m28.5 20.5 7-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/><circle cx="29" cy="14" r="1.6" fill="#fff"/><circle cx="35" cy="20" r="1.6" fill="#fff"/>',
		// Tai nghe tư vấn.
		'headset'   => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><path d="M11 26v-3a13 13 0 0 1 26 0v3" fill="none" ' . $s . '/><rect x="8" y="24" width="8" height="12" rx="3" fill="#22C55E" ' . $s . '/><rect x="32" y="24" width="8" height="12" rx="3" fill="#22C55E" ' . $s . '/><path d="M36 36c0 3-3 5-8 5h-2" fill="none" ' . $s . '/><rect x="20" y="38" width="7" height="5" rx="2.5" fill="#E53935" ' . $s . '/>',
		// Diện tích: mặt bằng + mũi tên.
		'area'      => '<circle cx="24" cy="24" r="22" fill="#DBEAFE"/><rect x="11" y="11" width="26" height="26" rx="3" fill="#fff" ' . $s . '/><path d="M11 24h12V11" fill="#93C5FD" ' . $s . '/><path d="m29 16 4-0M33 16v4M33 16l-6 6" fill="none" stroke="#E53935" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 31h-0M17 31v-4M17 31l6-6" fill="none" stroke="#E53935" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
		// Giường ngủ.
		'bed'       => '<circle cx="24" cy="24" r="22" fill="#EDE9FE"/><path d="M8 34V14" ' . $s . ' fill="none"/><rect x="8" y="24" width="32" height="8" rx="2" fill="#8B5CF6" ' . $s . '/><rect x="11" y="18" width="9" height="6" rx="3" fill="#fff" ' . $s . '/><path d="M22 24v-3a3 3 0 0 1 3-3h11a4 4 0 0 1 4 4v2" fill="#C4B5FD" ' . $s . '/><path d="M8 32v4M40 32v4" ' . $s . ' fill="none"/>',
		// Bồn tắm.
		'bath'      => '<circle cx="24" cy="24" r="22" fill="#CFFAFE"/><path d="M14 22V12a4 4 0 0 1 8 0" fill="none" ' . $s . '/><path d="M19 15h6" ' . $s . ' fill="none"/><path d="M8 22h32v4a10 10 0 0 1-10 10H18A10 10 0 0 1 8 26z" fill="#06B6D4" ' . $s . '/><path d="M8 22h32" ' . $s . '/><path d="M15 36l-2 4M33 36l2 4" ' . $s . ' fill="none"/><circle cx="31" cy="15" r="2" fill="#67E8F9"/><circle cx="35" cy="11" r="1.5" fill="#67E8F9"/>',
		// Số tầng.
		'layers'    => '<circle cx="24" cy="24" r="22" fill="#FEF3C7"/><path d="m24 30-15-7 15-7 15 7z" fill="#FCD34D" ' . $s . '/><path d="m9 29 15 7 15-7" fill="none" ' . $s . '/><path d="m24 23-15-7 15-7 15 7z" fill="#F59E0B" ' . $s . '/>',
		// La bàn hướng nhà.
		'compass'   => '<circle cx="24" cy="24" r="22" fill="#FDE2E2"/><circle cx="24" cy="24" r="15" fill="#fff" ' . $s . '/><path d="m24 13 4 11h-8z" fill="#E53935" ' . $s . '/><path d="m24 35-4-11h8z" fill="#CBD5E1" ' . $s . '/><circle cx="24" cy="24" r="2" fill="' . $n . '"/>',
		// Ghế sofa – nội thất.
		'sofa'      => '<circle cx="24" cy="24" r="22" fill="#FFEDD5"/><rect x="12" y="14" width="24" height="12" rx="3" fill="#FB923C" ' . $s . '/><rect x="7" y="20" width="8" height="14" rx="3" fill="#F97316" ' . $s . '/><rect x="33" y="20" width="8" height="14" rx="3" fill="#F97316" ' . $s . '/><rect x="15" y="25" width="18" height="9" rx="2" fill="#FDBA74" ' . $s . '/><path d="M10 34v4M38 34v4" ' . $s . ' fill="none"/>',
		// Ngôi nhà – loại hình.
		'house'     => '<circle cx="24" cy="24" r="22" fill="#FDE2E2"/><path d="M10 22 24 10l14 12" fill="none" ' . $s . '/><path d="M13 20v18h22V20" fill="#fff" ' . $s . '/><path d="M10 22 24 10l14 12" fill="#E53935" ' . $s . '/><rect x="20" y="27" width="8" height="11" rx="1" fill="#F59E0B" ' . $s . '/><rect x="29" y="23" width="4" height="4" fill="#93C5FD" stroke="' . $n . '" stroke-width="1.5"/>',
		// Tòa nhà – dự án.
		'building'  => '<circle cx="24" cy="24" r="22" fill="#DBEAFE"/><rect x="10" y="17" width="12" height="23" fill="#93C5FD" ' . $s . '/><rect x="22" y="8" width="16" height="32" fill="#3B82F6" ' . $s . '/><path d="M26 14h3M32 14h2M26 20h3M32 20h2M26 26h3M32 26h2M14 23h4M14 29h4" stroke="#fff" stroke-width="2" stroke-linecap="round"/><rect x="28" y="32" width="5" height="8" fill="#F59E0B" ' . $s . '/><path d="M6 40h36" ' . $s . '/>',
		// Lịch bàn giao.
		'calendar'  => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><rect x="9" y="12" width="30" height="27" rx="3" fill="#fff" ' . $s . '/><path d="M9 15a3 3 0 0 1 3-3h24a3 3 0 0 1 3 3v6H9z" fill="#22C55E" ' . $s . '/><path d="M16 9v6M32 9v6" ' . $s . '/><rect x="15" y="26" width="5" height="4" rx="1" fill="#E53935"/><rect x="22" y="26" width="5" height="4" rx="1" fill="#CBD5E1"/><rect x="29" y="26" width="5" height="4" rx="1" fill="#CBD5E1"/><rect x="15" y="32" width="5" height="4" rx="1" fill="#CBD5E1"/>',
		// Ghim vị trí.
		'pin'       => '<circle cx="24" cy="24" r="22" fill="#FDE2E2"/><ellipse cx="24" cy="39" rx="9" ry="3" fill="#FCA5A5"/><path d="M24 39s-12-10.5-12-20a12 12 0 0 1 24 0c0 9.5-12 20-12 20z" fill="#E53935" ' . $s . '/><circle cx="24" cy="19" r="5" fill="#fff" ' . $s . '/>',
		// Điện thoại.
		'phone'     => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><rect x="15" y="7" width="18" height="34" rx="4" fill="#22C55E" ' . $s . '/><rect x="18" y="12" width="12" height="20" rx="1" fill="#fff"/><circle cx="24" cy="36.5" r="1.8" fill="#fff"/><path d="M35 14a6 6 0 0 1 0 8M38 11a10 10 0 0 1 0 14" fill="none" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>',
		// Dấu tích – tiện ích.
		'check'     => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><circle cx="24" cy="24" r="14" fill="#22C55E" ' . $s . '/><path d="m17.5 24.5 4.5 4.5 8.5-9" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>',
		// Dấu tích không viền (danh sách tiện ích).
		'tick'      => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><circle cx="24" cy="24" r="15" fill="#22C55E"/><path d="m17 24.5 5 5 9-10" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>',
		// Ghim vị trí không viền (danh sách vị trí).
		'pin-soft'  => '<circle cx="24" cy="24" r="22" fill="#FDE2E2"/><path d="M24 38s-10-8.8-10-16.6a10 10 0 0 1 20 0C34 29.2 24 38 24 38z" fill="#C02038"/><circle cx="24" cy="21.5" r="4" fill="#fff"/>',
		// Thẻ giá.
		'price'     => '<circle cx="24" cy="24" r="22" fill="#FEF3C7"/><path d="M9 25V11a2 2 0 0 1 2-2h14l15 15-16 16z" fill="#F59E0B" ' . $s . '/><circle cx="17" cy="17" r="3" fill="#fff" ' . $s . '/><path d="M22 27h10M24 23h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/>',
		// Phong bì email.
		'mail'      => '<circle cx="24" cy="24" r="22" fill="#DBEAFE"/><rect x="8" y="13" width="32" height="22" rx="3" fill="#3B82F6" ' . $s . '/><path d="m9 15 15 11 15-11" fill="#93C5FD" ' . $s . '/><circle cx="37" cy="14" r="5" fill="#E53935" stroke="#fff" stroke-width="2"/>',
		// Biệt thự: nhà 2 mái + cây.
		'villa'     => '<circle cx="24" cy="24" r="22" fill="#DCFCE7"/><path d="M8 24 18 15l10 9" fill="#E53935" ' . $s . '/><path d="M10 22v16h26V24H28" fill="#fff" ' . $s . '/><path d="M24 20l7-6 7 6" fill="#F87171" ' . $s . '/><rect x="15" y="29" width="6" height="9" fill="#F59E0B" ' . $s . '/><rect x="28" y="27" width="5" height="4" fill="#93C5FD" stroke="' . $n . '" stroke-width="1.5"/><circle cx="41" cy="30" r="4" fill="#22C55E" ' . $s . '/><path d="M41 34v4" ' . $s . '/>',
		// Đất nền: mảnh đất có cọc mốc.
		'land'      => '<circle cx="24" cy="24" r="22" fill="#FEF3C7"/><path d="m6 30 12-6 24 6-12 7z" fill="#86EFAC" ' . $s . '/><path d="m12 31 6-3 14 3.5-6 3.5z" fill="#FCD34D"/><path d="M30 26V12" ' . $s . '/><path d="M30 12h9l-3 3.5 3 3.5h-9" fill="#E53935" ' . $s . '/>',
		// Văn phòng: cặp tài liệu.
		'office'    => '<circle cx="24" cy="24" r="22" fill="#E0E7FF"/><path d="M19 15v-3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v3" fill="none" ' . $s . '/><rect x="8" y="15" width="32" height="22" rx="3" fill="#6366F1" ' . $s . '/><path d="M8 24h32" ' . $s . '/><rect x="21" y="21" width="6" height="6" rx="1" fill="#FCD34D" ' . $s . '/>',
		// Shophouse: cửa hàng có mái hiên.
		'shop'      => '<circle cx="24" cy="24" r="22" fill="#FFE4E6"/><path d="M11 20v18h26V20" fill="#fff" ' . $s . '/><path d="M9 12h30l2 8H7z" fill="#E53935" ' . $s . '/><path d="M15 12l-2 8M21 12l-1 8M27 12l1 8M33 12l2 8" stroke="#fff" stroke-width="2"/><rect x="15" y="26" width="8" height="12" fill="#F59E0B" ' . $s . '/><rect x="26" y="26" width="7" height="6" fill="#93C5FD" stroke="' . $n . '" stroke-width="1.5"/>',
		// Chìa khóa – cho thuê / bàn giao.
		'key'       => '<circle cx="24" cy="24" r="22" fill="#EDE9FE"/><circle cx="17" cy="20" r="8" fill="#8B5CF6" ' . $s . '/><circle cx="15" cy="18" r="2.5" fill="#fff"/><path d="m22.5 25.5 14 14M30 33l3-3M33.5 36.5l3-3" fill="none" ' . $s . '/>',
	);
}

/**
 * In icon nhiều màu.
 *
 * @param string $name Tên icon.
 * @param int    $size Kích thước px.
 * @return string
 */
function lp_color_icon( $name, $size = 24 ) {
	$paths = lp_color_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="lp-cicon lp-cicon-%1$s" viewBox="0 0 48 48" width="%2$d" height="%2$d" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/** File SVG hoàn chỉnh (để lưu vào thư viện media dùng trong Elementor). */
function lp_color_icon_file( $name ) {
	$paths = lp_color_icon_paths();
	return isset( $paths[ $name ] ) ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="96" height="96">' . $paths[ $name ] . '</svg>' : '';
}

/**
 * Icon nét mảnh cỡ lớn cho khối dịch vụ (viền đỏ + chấm nhấn xanh ngọc). viewBox 64x64.
 *
 * @return array<string,string>
 */
function lp_line_icon_paths() {
	$bg = '<rect x="2" y="2" width="60" height="60" rx="16" fill="#FDF0F2"/>';
	$k  = 'fill="none" stroke="#1F2937" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"';
	$r  = 'fill="#C02038"';
	return array(
		// Tư vấn mua bán: kính lúp soi ngôi nhà.
		'svc-consult' => $bg . '<g transform="translate(-1.5 -3)"><path d="M14 30 26 20l12 10" ' . $k . '/><path d="M17 28v14h18" ' . $k . '/><rect x="23" y="33" width="6" height="9" rx="1" ' . $r . '/><circle cx="41" cy="38" r="8" fill="#fff" stroke="#1F2937" stroke-width="2.4"/><path d="m47 44 6 6" ' . $k . '/><circle cx="41" cy="38" r="3" ' . $r . '/></g>',
		// Ký gửi & cho thuê: chìa khóa có thẻ treo.
		'svc-rent'    => $bg . '<g transform="translate(0 1)"><circle cx="22" cy="26" r="9" fill="#fff" stroke="#1F2937" stroke-width="2.4"/><circle cx="22" cy="26" r="3" ' . $r . '/><path d="M28.5 32.5 44 48M38 42l3-3M42 46l3-3" ' . $k . '/><path d="M40 14h12v10l-6 4-6-4z" ' . $r . '/><path d="M46 18v2" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/><path d="M31 21c3-4 6-6 9-7" fill="none" stroke="#1F2937" stroke-width="2" stroke-dasharray="2 3" stroke-linecap="round"/></g>',
		// Hỗ trợ vay & pháp lý: hồ sơ + khiên bảo vệ có dấu %.
		'svc-loan'    => $bg . '<g transform="translate(-3 -2)"><path d="M16 12h20l8 8v14" ' . $k . '/><path d="M36 12v8h8" ' . $k . '/><path d="M16 12v40h16" ' . $k . '/><path d="M21 24h10M21 30h12M21 36h8" ' . $k . '/><path d="M44 34l10 4v6c0 6-4 9.5-10 12-6-2.5-10-6-10-12v-6z" ' . $r . '/><path d="m40.5 49 7-8" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/><circle cx="41" cy="42" r="1.6" fill="#fff"/><circle cx="47" cy="48" r="1.6" fill="#fff"/></g>',
	);
}

/** File SVG của icon nét (để lưu vào thư viện media dùng trong Elementor). */
function lp_line_icon_file( $name ) {
	$paths = lp_line_icon_paths();
	return isset( $paths[ $name ] ) ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="128" height="128">' . $paths[ $name ] . '</svg>' : '';
}
