<?php
/**
 * Đa ngôn ngữ (Polylang): chuỗi giao diện của plugin + bản dịch tiếng Anh mặc định.
 *
 * - Chuỗi được đăng ký với Polylang (Ngôn ngữ → Dịch chuỗi, nhóm "LandPro") để sửa trong admin.
 * - Bản dịch tiếng Anh dưới đây được nạp sẵn khi chạy dữ liệu demo.
 * - Thêm ngôn ngữ khác: thêm ngôn ngữ trong Polylang rồi dịch chuỗi trong admin.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chuỗi giao diện tiếng Việt => tiếng Anh.
 *
 * @return array<string,string>
 */
function lp_strings() {
	$strings = array(
		// Chung.
		'Trang chủ'                     => 'Home',
		'Nhà đất'                       => 'Properties',
		'Tất cả'                        => 'All',
		'Mua nhà'                       => 'Buy',
		'Thuê nhà'                      => 'Rent',
		'Nhu cầu'                       => 'Purpose',
		'Từ khóa'                       => 'Keyword',
		'Nhập địa điểm, dự án, tên đường...' => 'Enter a location, project or street...',
		'Loại nhà đất'                  => 'Property type',
		'Khu vực'                       => 'Location',
		'Mức giá'                       => 'Price',
		'Diện tích'                     => 'Area',
		'Phòng ngủ'                     => 'Bedrooms',
		'Phòng tắm'                     => 'Bathrooms',
		'Phòng tắm / WC'                => 'Bathrooms',
		'1+ phòng ngủ'                  => '1+ bedrooms',
		'2+ phòng ngủ'                  => '2+ bedrooms',
		'3+ phòng ngủ'                  => '3+ bedrooms',
		'4+ phòng ngủ'                  => '4+ bedrooms',
		'Tìm kiếm'                      => 'Search',
		'Lọc kết quả'                   => 'Filter',
		'Sắp xếp'                       => 'Sort by',
		'Mới nhất'                      => 'Newest',
		'Giá thấp đến cao'              => 'Price: low to high',
		'Giá cao đến thấp'              => 'Price: high to low',
		'Diện tích lớn nhất'            => 'Largest area',
		'Ngẫu nhiên'                    => 'Random',
		'Tìm thấy <strong>%s</strong> bất động sản' => '<strong>%s</strong> properties found',
		'Phân trang'                    => 'Pagination',
		'Trang trước'                   => 'Previous page',
		'Trang sau'                     => 'Next page',
		'Lọc theo khu vực'              => 'Filter by location',
		'%s tin đăng'                   => '%s listings',
		'%s bất động sản'               => '%s properties',
		'Bất động sản %s'               => 'Properties in %s',
		'Chưa có tin nhà đất phù hợp.'  => 'No matching properties yet.',
		'Chưa có dự án phù hợp.'        => 'No matching projects yet.',
		'Không tìm thấy bất động sản phù hợp. Hãy thử bỏ bớt điều kiện lọc.' => 'No matching properties found. Try removing some filters.',
		'Chưa có bài viết.'             => 'No posts yet.',
		// Đơn vị giá.
		'tỷ'                            => 'billion',
		'triệu'                         => 'million',
		'đ'                             => 'VND',
		'/tháng'                        => '/month',
		'Thỏa thuận'                    => 'Negotiable',
		'PN'                            => 'BR',
		'WC'                            => 'BA',
		// Khoảng giá / diện tích.
		'Dưới 1 tỷ'                     => 'Under 1 billion',
		'1 - 2 tỷ'                      => '1 - 2 billion',
		'2 - 3 tỷ'                      => '2 - 3 billion',
		'3 - 5 tỷ'                      => '3 - 5 billion',
		'5 - 7 tỷ'                      => '5 - 7 billion',
		'7 - 10 tỷ'                     => '7 - 10 billion',
		'Trên 10 tỷ'                    => 'Over 10 billion',
		'Dưới 5 triệu'                  => 'Under 5 million',
		'5 - 10 triệu'                  => '5 - 10 million',
		'10 - 20 triệu'                 => '10 - 20 million',
		'20 - 50 triệu'                 => '20 - 50 million',
		'Trên 50 triệu'                 => 'Over 50 million',
		'Dưới 30 m²'                    => 'Under 30 m²',
		'30 - 50 m²'                    => '30 - 50 m²',
		'50 - 80 m²'                    => '50 - 80 m²',
		'80 - 100 m²'                   => '80 - 100 m²',
		'100 - 150 m²'                  => '100 - 150 m²',
		'150 - 300 m²'                  => '150 - 300 m²',
		'Trên 300 m²'                   => 'Over 300 m²',
		// Giá trị lựa chọn.
		'Đông'                          => 'East',
		'Tây'                           => 'West',
		'Nam'                           => 'South',
		'Bắc'                           => 'North',
		'Đông Nam'                      => 'Southeast',
		'Đông Bắc'                      => 'Northeast',
		'Tây Nam'                       => 'Southwest',
		'Tây Bắc'                       => 'Northwest',
		'Sổ hồng'                       => 'Pink book (ownership certificate)',
		'Sổ đỏ'                         => 'Red book (land use certificate)',
		'Hợp đồng mua bán'              => 'Sale contract',
		'Đang chờ sổ'                   => 'Certificate pending',
		'Đang mở bán'                   => 'On sale',
		'Sắp mở bán'                    => 'Coming soon',
		'Đã bàn giao'                   => 'Handed over',
		// Trang chi tiết.
		'Thông tin chính'               => 'Key details',
		'Mô tả chi tiết'                => 'Description',
		'Video'                         => 'Video',
		'Vị trí'                        => 'Location',
		'Số tầng'                       => 'Floors',
		'Mặt tiền'                      => 'Frontage',
		'Hướng nhà'                     => 'Direction',
		'Pháp lý'                       => 'Legal status',
		'Nội thất'                      => 'Furniture',
		'Loại hình'                     => 'Type',
		'Dự án'                         => 'Project',
		'Liên hệ xem nhà'               => 'Book a viewing',
		'Liên hệ tư vấn'                => 'Contact us',
		'Bất động sản tương tự'         => 'Similar properties',
		'Chủ đầu tư'                    => 'Developer',
		'Chủ đầu tư: %s'                => 'Developer: %s',
		'Tổng diện tích'                => 'Total area',
		'Quy mô'                        => 'Scale',
		'Bàn giao'                      => 'Handover',
		'Trạng thái'                    => 'Status',
		'Giá bán'                       => 'Price',
		'Tổng quan dự án'               => 'Project overview',
		'Giới thiệu dự án'              => 'About the project',
		'Tiện ích nổi bật'              => 'Amenities',
		'Video dự án'                   => 'Project video',
		'Vị trí dự án'                  => 'Project location',
		'Nhận bảng giá & tư vấn'        => 'Get the price list',
		'Sản phẩm đang giao dịch tại %s' => 'Listings at %s',
		'Dự án khác'                    => 'Other projects',
		'Chat Zalo'                     => 'Chat on Zalo',
		'%1$s - ảnh %2$d'               => '%1$s - photo %2$d',
		'+%d ảnh'                       => '+%d photos',
		'Phát video %s'                 => 'Play video %s',
		'Bản đồ vị trí'                 => 'Location map',
		'Bản đồ nhà đất'                => 'Property map',
		'Xem bản đồ'                    => 'Show map',
		'Website demo'                  => 'Demo website',
		'Tin đăng, giá, dự án và hình ảnh chỉ mang tính minh họa, không phải giao dịch thật.' => 'Listings, prices, projects and images are for illustration only – not real offers.',
		'Xem miễn trừ trách nhiệm'      => 'Read disclaimer',
		'Tin minh họa.'                 => 'Sample listing.',
		'Thông tin, giá, pháp lý và hình ảnh (tạo bằng AI) chỉ dùng để trình diễn website, không tương ứng với bất động sản có thật và không phải lời chào bán.' => 'Details, price, legal status and AI-generated images are for demonstration only, do not describe a real property and are not an offer for sale.',
		'Miễn trừ trách nhiệm'          => 'Disclaimer',
		'Chính sách bảo mật'            => 'Privacy Policy',
		'Website demo phục vụ trình diễn giao diện. Toàn bộ tin đăng, dự án, giá và chủ đầu tư là dữ liệu giả lập; hình ảnh minh họa được tạo bằng AI; dữ liệu bản đồ © OpenStreetMap contributors.' => 'Demo website for showcasing design. All listings, projects, prices and developers are fictional; images are AI-generated illustrations; map data © OpenStreetMap contributors.',
		'Thông tin trên website chỉ mang tính tham khảo, vui lòng kiểm tra pháp lý trước khi giao dịch.' => 'Information on this website is for reference only; please verify legal status before any transaction.',
		'Trang %d'                      => 'Page %d',
		'Bộ lọc'                        => 'Filters',
		'Đóng'                          => 'Close',
		'Ảnh trước'                     => 'Previous image',
		'%d ảnh'                        => '%d photos',
		'Xem tất cả ảnh'                => 'View all photos',
		'Ảnh sau'                       => 'Next image',
		'Phóng to'                      => 'Zoom',
		'Trình chiếu'                   => 'Slideshow',
		'Dừng trình chiếu'              => 'Pause slideshow',
		'Ẩn / hiện ảnh nhỏ'             => 'Toggle thumbnails',
		'Toàn màn hình'                 => 'Fullscreen',
		'Phóng to bản đồ để xem thêm tin đăng' => 'Zoom in to see more listings',
		'Đóng bản đồ'                   => 'Close map',
		'Đang lọc:'                     => 'Filters:',
		'Xóa lọc'                       => 'Clear filters',
		'Gợi ý:'                        => 'Popular:',
		'Căn hộ Quận 7'                 => 'Apartment District 7',
		'Nhà phố Thủ Đức'               => 'Townhouse Thu Duc',
		'Biệt thự ven sông'             => 'Riverside villa',
		'Đất nền Bình Dương'            => 'Land Binh Duong',
		'Căn hộ Vũng Tàu'               => 'Apartment Vung Tau',
		'Văn phòng Cầu Giấy'            => 'Office Cau Giay',
		'Bỏ lọc'                        => 'Remove filter',
		'Xem chi tiết'                  => 'View details',
		'%s tỷ'                         => '%sB',
		'%s tr'                         => '%sM',
		'Chưa có bất động sản nào có vị trí trên bản đồ.' => 'No listings with a map location yet.',
		'Bật JavaScript để xem bản đồ.' => 'Enable JavaScript to view the map.',
		'Xem ảnh'                       => 'View photo',
		'Kết quả tìm kiếm: %s'          => 'Search results: %s',
		'Không tìm thấy trang'          => 'Page not found',
		'Trang bạn tìm không tồn tại hoặc đã được chuyển sang địa chỉ khác.' => 'The page you are looking for does not exist or has moved.',
		'Về trang chủ'                  => 'Back to home',
		'Xem nhà đất'                   => 'Browse properties',
		'Chuyên viên tư vấn LandPro'    => 'LandPro property consultant',
		'Tổng quan'                     => 'Overview',
		'Tiện ích'                      => 'Amenities',
		'Thiết kế'                      => 'Design',
		'Sản phẩm'                      => 'Units',
		'Chính sách'                    => 'Offers',
		'Chính sách bán hàng'           => 'Sales policy',
		'Liên hệ'                       => 'Contact',
		'Mục lục dự án'                 => 'Project sections',
		'Liên hệ nhanh'                 => 'Quick contact',
		'Gọi hotline'                   => 'Call hotline',
		'Về đầu trang'                  => 'Back to top',
		'Nội dung chính'                => 'Contents',
		'Ẩn'                            => 'Hide',
		'Hiện'                          => 'Show',
		'Thanh bên'                     => 'Sidebar',
		'Nhập từ khóa...'               => 'Enter keywords...',
		'Chuyên mục'                    => 'Categories',
		'Bài viết mới'                  => 'Latest posts',
		'Cần tư vấn bất động sản?'      => 'Need property advice?',
		'Chuyên viên LandPro hỗ trợ miễn phí 8:00 - 20:00 mỗi ngày.' => 'LandPro consultants help free of charge, 8:00 - 20:00 every day.',
		'Slide trước'                   => 'Previous slide',
		'Slide sau'                     => 'Next slide',
		'Đóng menu'                     => 'Close menu',
		'Hiện chưa có sản phẩm đang giao dịch. Liên hệ để nhận danh sách căn mới nhất.' => 'No units listed right now. Contact us for the latest list.',
	);
	// Module khác (vd. Ký gửi) bổ sung chuỗi qua filter này.
	return apply_filters( 'lp_strings', $strings );
}

/** Đăng ký chuỗi với Polylang (hiện ở Ngôn ngữ → Dịch chuỗi). */
add_action(
	'init',
	function () {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
		foreach ( array_keys( lp_strings() ) as $i => $string ) {
			pll_register_string( 'lp_' . md5( $string ), $string, 'LandPro', strlen( $string ) > 60 );
		}
	}
);

/**
 * Slug của term theo ngôn ngữ mặc định (VD: 'for-rent' tiếng Anh => 'cho-thue').
 * Giúp logic dựa trên slug (giá thuê/tháng, màu nhãn, khoảng giá) chạy đúng ở mọi ngôn ngữ.
 *
 * @param WP_Term|null $term Term.
 * @return string
 */
function lp_base_slug( $term ) {
	if ( ! $term ) {
		return '';
	}
	if ( function_exists( 'pll_get_term' ) && function_exists( 'pll_default_language' ) ) {
		$base = pll_get_term( $term->term_id, pll_default_language() );
		if ( $base && (int) $base !== (int) $term->term_id ) {
			$base_term = get_term( $base, $term->taxonomy );
			if ( $base_term && ! is_wp_error( $base_term ) ) {
				return $base_term->slug;
			}
		}
	}
	return $term->slug;
}

/** Slug ngôn ngữ mặc định từ slug bất kỳ (dùng cho bộ lọc trên URL). */
function lp_base_slug_from( $slug, $taxonomy ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	return $term ? lp_base_slug( $term ) : $slug;
}

/** Bản dịch của bài theo ngôn ngữ hiện tại (nếu có). */
function lp_translated_id( $post_id ) {
	if ( $post_id && function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( $post_id );
		return $tr ? (int) $tr : (int) $post_id;
	}
	return (int) $post_id;
}

/**
 * Slug term theo ngôn ngữ hiện tại từ slug tiếng Việt (VD: 'cho-thue' => 'for-rent' khi đang xem tiếng Anh).
 *
 * @param string $slug     Slug tiếng Việt.
 * @param string $taxonomy Taxonomy.
 * @return string
 */
function lp_lang_slug( $slug, $taxonomy ) {
	if ( ! $slug || ! function_exists( 'pll_get_term' ) ) {
		return $slug;
	}
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( ! $term ) {
		return $slug;
	}
	$tr = pll_get_term( $term->term_id );
	if ( $tr && (int) $tr !== (int) $term->term_id ) {
		$tr_term = get_term( $tr, $taxonomy );
		return $tr_term && ! is_wp_error( $tr_term ) ? $tr_term->slug : $slug;
	}
	return $slug;
}

/* Header/footer Elementor theo ngôn ngữ đang xem. */
add_filter( 'get_hfe_header_id', 'lp_translated_id' );
add_filter( 'get_hfe_footer_id', 'lp_translated_id' );

/** [lp_languages] – nút chuyển ngôn ngữ (cờ + tên). */
add_shortcode(
	'lp_languages',
	function () {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}
		$items = pll_the_languages(
			array(
				'echo'          => 0,
				'raw'           => 1,
				'hide_if_empty' => 0,
			)
		);
		if ( ! $items ) {
			return '';
		}
		$out = '<ul class="lp-langs" aria-label="Language">';
		foreach ( $items as $item ) {
			// Chỉ hiện cờ; tên ngôn ngữ dùng cho trình đọc màn hình và tooltip.
			$out .= sprintf(
				'<li class="%1$s"><a href="%2$s" hreflang="%3$s" lang="%3$s" title="%4$s" aria-label="%4$s"%5$s><img src="%6$s" alt="" width="24" height="16"></a></li>',
				$item['current_lang'] ? 'is-current' : '',
				esc_url( $item['url'] ),
				esc_attr( $item['locale'] ),
				esc_attr( $item['name'] ),
				$item['current_lang'] ? ' aria-current="true"' : '',
				esc_url( $item['flag'] )
			);
		}
		return $out . '</ul>';
	}
);

/**
 * Breadcrumb Yoast: mục phân trang "Page 2" → "Trang 2" (bản tiếng Anh giữ "Page 2").
 */
add_filter(
	'wpseo_breadcrumb_links',
	function ( $links ) {
		foreach ( $links as &$link ) {
			if ( isset( $link['text'] ) && preg_match( '/^(?:Page|Trang)\s+(\d+)$/u', trim( wp_strip_all_tags( $link['text'] ) ), $m ) ) {
				$link['text'] = sprintf( lp_t( 'Trang %d' ), (int) $m[1] );
			}
		}
		return $links;
	}
);
