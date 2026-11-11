<?php
/**
 * Dữ liệu demo đa ngôn ngữ (Polylang): tạo bản tiếng Anh cho phân loại, dự án,
 * nhà đất, trang Elementor, menu, header/footer, form và chuỗi giao diện.
 *
 * Trang tiếng Anh được tạo bằng cách dịch dữ liệu Elementor của trang tiếng Việt
 * (chữ, đường dẫn, bộ lọc) nên bố cục hai ngôn ngữ luôn giống nhau.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Demo_I18n {

	const LANG = 'en';

	/** @var array<string,int> Ánh xạ "taxonomy:slug_vi" => term_id tiếng Anh. */
	private static $terms = array();

	/** @var array<string,string> Ánh xạ "taxonomy:slug_vi" => slug tiếng Anh. */
	private static $slugs = array();

	/** @var array<string,string> URL tiếng Việt => URL tiếng Anh. */
	private static $urls = array();

	public static function available() {
		return function_exists( 'pll_set_post_language' ) && function_exists( 'PLL' );
	}

	/**
	 * Chạy toàn bộ bước dịch.
	 *
	 * @param array    $pages   Trang tiếng Việt (khóa => ID) từ LP_Demo::pages().
	 * @param int      $form_id Form Fluent Forms tiếng Việt.
	 * @param callable $log     Hàm ghi log.
	 */
	public static function run( $pages, $form_id, $log ) {
		self::languages();
		self::assign_default_language();
		self::terms();
		$projects = self::posts( 'project' );
		self::posts( 'property', $projects );
		$en_pages = self::pages( $pages );
		$menu_en  = self::menus( $pages, $en_pages );
		self::header_footer( $menu_en );
		self::form( $form_id );
		self::strings();
		// Polylang nạp quy tắc URL & trang chủ theo ngôn ngữ ở lượt tải sau: xóa cache để tự tạo lại.
		PLL()->model->clean_languages_cache();
		delete_option( 'rewrite_rules' );
		$log( 'Đã tạo bản tiếng Anh (Polylang): phân loại, dự án, nhà đất, trang, menu, header/footer, form, chuỗi giao diện.' );
	}

	/* ================================================================ Ngôn ngữ */

	private static function languages() {
		$model = PLL()->model;
		$have  = $model->get_languages_list( array( 'fields' => 'slug' ) );
		if ( ! in_array( 'vi', $have, true ) ) {
			$model->languages->add(
				array(
					'name'       => 'Tiếng Việt',
					'slug'       => 'vi',
					'locale'     => 'vi',
					'flag'       => 'vn',
					'term_group' => 0,
				)
			);
		}
		if ( ! in_array( self::LANG, $have, true ) ) {
			$model->languages->add(
				array(
					'name'       => 'English',
					'slug'       => self::LANG,
					'locale'     => 'en_US',
					'flag'       => 'us',
					'term_group' => 1,
				)
			);
		}
		$opts = PLL()->options;
		$opts->set( 'default_lang', 'vi' );
		$opts->set( 'hide_default', true );   // Tiếng Việt không có /vi/ trên URL.
		$opts->set( 'force_lang', 1 );        // Ngôn ngữ theo thư mục: /en/...
		$opts->set( 'rewrite', true );        // Bỏ /language/ khỏi URL.
		$opts->set( 'browser', false );       // Không tự chuyển theo ngôn ngữ trình duyệt.
		$opts->set( 'redirect_lang', true );  // Trang chủ tiếng Anh là /en/ (không phải /en/home/).
		$opts->set( 'media_support', false );
		$opts->set( 'post_types', array( 'property', 'project', 'elementor-hf' ) );
		$opts->set( 'taxonomies', array( 'lp_purpose', 'lp_type', 'lp_location' ) );
		$opts->save();
		$model->clean_languages_cache();
	}

	/** Gán tiếng Việt cho mọi nội dung chưa có ngôn ngữ. */
	private static function assign_default_language() {
		PLL()->model->set_language_in_mass( 'vi' );
	}

	/* ================================================================ Phân loại */

	private static function term_names() {
		return array(
			'lp_purpose'  => array(
				'ban'      => array( 'For sale', 'for-sale' ),
				'cho-thue' => array( 'For rent', 'for-rent' ),
			),
			'lp_type'     => array(
				'can-ho'    => array( 'Apartment', 'apartment' ),
				'nha-pho'   => array( 'Townhouse', 'townhouse' ),
				'biet-thu'  => array( 'Villa', 'villa' ),
				'dat-nen'   => array( 'Land plot', 'land-plot' ),
				'van-phong' => array( 'Office', 'office' ),
				'shophouse' => array( 'Shop house', 'shop-house' ),
			),
			'lp_location' => array(
				'ho-chi-minh'     => array( 'Ho Chi Minh City', 'ho-chi-minh-city' ),
				'quan-1'          => array( 'District 1', 'district-1' ),
				'quan-7'          => array( 'District 7', 'district-7' ),
				'thu-duc'         => array( 'Thu Duc City', 'thu-duc-city' ),
				'binh-thanh'      => array( 'Binh Thanh', 'binh-thanh-district' ),
				'go-vap'          => array( 'Go Vap', 'go-vap-district' ),
				'ha-noi'          => array( 'Hanoi', 'hanoi' ),
				'cau-giay'        => array( 'Cau Giay', 'cau-giay-district' ),
				'tay-ho'          => array( 'Tay Ho', 'tay-ho-district' ),
				'nam-tu-liem'     => array( 'Nam Tu Liem', 'nam-tu-liem-district' ),
				'binh-duong'      => array( 'Binh Duong', 'binh-duong-province' ),
				'thu-dau-mot'     => array( 'Thu Dau Mot', 'thu-dau-mot-city' ),
				'ba-ria-vung-tau' => array( 'Ba Ria - Vung Tau', 'ba-ria-vung-tau-province' ),
				'vung-tau'        => array( 'Vung Tau', 'vung-tau-city' ),
			),
		);
	}

	private static function terms() {
		foreach ( self::term_names() as $tax => $items ) {
			foreach ( $items as $slug_vi => $en ) {
				$vi = get_term_by( 'slug', $slug_vi, $tax );
				if ( ! $vi ) {
					continue;
				}
				pll_set_term_language( $vi->term_id, 'vi' );
				$parent = 0;
				if ( $vi->parent ) {
					$parent = (int) pll_get_term( $vi->parent, self::LANG );
				}
				$existing = get_term_by( 'slug', $en[1], $tax );
				if ( $existing ) {
					$en_id = (int) $existing->term_id;
					wp_update_term( $en_id, $tax, array( 'name' => $en[0], 'parent' => $parent ) );
				} else {
					$res   = wp_insert_term( $en[0], $tax, array( 'slug' => $en[1], 'parent' => $parent ) );
					$en_id = is_wp_error( $res ) ? 0 : (int) $res['term_id'];
				}
				if ( ! $en_id ) {
					continue;
				}
				pll_set_term_language( $en_id, self::LANG );
				pll_save_term_translations(
					array(
						'vi'       => $vi->term_id,
						self::LANG => $en_id,
					)
				);
				self::$terms[ $tax . ':' . $slug_vi ] = $en_id;
				self::$slugs[ $tax . ':' . $slug_vi ] = $en[1];
			}
		}
	}

	/** Danh sách term tiếng Anh tương ứng của một bài tiếng Việt. */
	private static function en_terms( $post_id, $tax ) {
		$ids = array();
		foreach ( (array) get_the_terms( $post_id, $tax ) as $term ) {
			if ( $term && ! is_wp_error( $term ) && isset( self::$terms[ $tax . ':' . $term->slug ] ) ) {
				$ids[] = self::$terms[ $tax . ':' . $term->slug ];
			}
		}
		return $ids;
	}

	/* ================================================================ Dự án, nhà đất */

	/** Nội dung tiếng Anh của dự án theo slug tiếng Việt. */
	private static function project_en() {
		return array(
			'landpro-riverside'        => array( 'landpro-riverside-apartments', 'LandPro Riverside', 'A riverside apartment community at the eastern gateway of Thu Duc City, with fast links to the city centre via Ring Road 3 and Metro Line 1.', 'VND 45 - 58 million/m²', '3 towers, 1,250 apartments', '4.8 ha', 'Q2/2027', 'Building permit issued', "50 m infinity pool\n1.2 ha riverside park\nOn-site kindergarten\nGym & yoga studio\nOutdoor BBQ area\n24/7 security, key-card lifts" ),
			'sunrise-garden'           => array( 'sunrise-garden-district-7', 'Sunrise Garden', 'Green living in the heart of District 7, minutes from Phu My Hung and District 1.', 'VND 62 - 75 million/m²', '2 towers, 820 apartments', '2.1 ha', 'Q4/2026', 'Individual pink books', "Rooftop pool\nSky bar on level 35\nHanging gardens every 5 floors\nRetail podium\n3-level basement parking" ),
			'green-valley-villas'      => array( 'green-valley-villas-thu-duc', 'Green Valley Villas', 'A gated villa compound with low building density, surrounded by canals and greenery.', 'From VND 18 billion/villa', '96 semi-detached and detached villas', '6.5 ha', 'Q1/2028', 'Freehold pink book', "Private clubhouse\nTennis court\nCanal-side walkway\n3-layer security gate" ),
			'ocean-park-residence'     => array( 'ocean-park-residence-vung-tau', 'Ocean Park Residence', 'Direct sea views over Back Beach, ideal for holidays and rental income.', 'VND 38 - 50 million/m²', '1 tower, 540 resort apartments', '1.4 ha', 'Phase 1 handed over', '50-year pink book', "Sea-view pool\nSeafood restaurant\nRental management service\nPrivate beach" ),
			'golden-hills-thu-dau-mot' => array( 'golden-hills-binh-duong', 'Golden Hills Thu Dau Mot', 'A land-plot township with complete infrastructure next to the Binh Duong administrative centre.', 'VND 22 - 30 million/m²', '420 land plots, 60 shophouses', '12 ha', 'Plots ready for handover', 'Red book for each plot', "2 ha central park\nInternational school\nMarket and supermarket\nUnderground power, clean water" ),
			'saigon-skyline-residences' => array( 'saigon-skyline-residences-district-1', 'Saigon Skyline Residences', 'Luxury residences in the heart of District 1 with panoramic views of the city and the Saigon River.', 'VND 120 - 150 million/m²', '2 towers, 480 apartments', '0.8 ha', 'Q3/2028', 'Building permit issued', "Infinity pool on level 30\nPanoramic gym\nFive-star lobby\nSmart basement parking" ),
			'riverview-binh-thanh'      => array( 'riverview-binh-thanh-apartments', 'Riverview Binh Thanh', 'On the bank of the Saigon River, minutes from District 1, with a sky lounge overlooking Phu My Bridge.', 'VND 85 - 110 million/m²', '1 tower, 45 floors, 620 apartments', '1.1 ha', 'Q1/2027', 'Freehold pink book', "River-view sky lounge\nInfinity pool\nZen garden\nChildren's playground" ),
			'skyline-tower-cau-giay'   => array( 'skyline-tower-hanoi', 'Skyline Tower Cau Giay', 'A Grade-A office tower on Tran Duy Hung, suited to businesses that need a professional workplace.', 'Rent from VND 350,000/m²/month', '32 floors of Grade-A offices', '0.9 ha', 'Handed over in 2024', 'Completion certificate', "Double-height lobby\nHigh-speed lifts\nShared meeting rooms\n4-level basement parking" ),
		);
	}

	/** Tiêu đề tiếng Anh của tin nhà đất theo slug tiếng Việt. */
	private static function property_titles() {
		return array(
			'can-ho-2pn-view-song-landpro-riverside'                       => '2-bedroom river-view apartment at LandPro Riverside',
			'can-ho-3pn-goc-sunrise-garden-noi-that-cao-cap'               => 'Luxury 3-bedroom corner apartment at Sunrise Garden',
			'nha-pho-4-tang-hem-xe-hoi-phan-van-tri'                       => '4-storey townhouse with car access on Phan Van Tri',
			'biet-thu-song-lap-green-valley-220m2'                         => '220 m² semi-detached villa at Green Valley',
			'dat-nen-100m2-mat-tien-duong-16m-golden-hills'                => '100 m² land plot on a 16 m road at Golden Hills',
			'can-ho-1pn-1-gan-cau-sai-gon-da-co-so'                        => '1+1 bedroom apartment near Saigon Bridge, with pink book',
			'shophouse-khoi-de-sunrise-garden-dang-co-hop-dong-thue'       => 'Sunrise Garden podium shophouse with an active lease',
			'can-ho-3pn-110m2-gan-cong-vien-cau-giay'                      => '110 m² 3-bedroom apartment near Cau Giay Park',
			'nha-pho-5-tang-view-ho-tay-co-thang-may'                      => '5-storey West Lake view townhouse with lift',
			'can-ho-nghi-duong-2pn-ocean-park-view-bien'                   => '2-bedroom sea-view resort apartment at Ocean Park',
			'cho-thue-can-ho-2pn-sunrise-garden-du-noi-that'               => 'Fully furnished 2-bedroom apartment for rent at Sunrise Garden',
			'cho-thue-studio-cao-cap-trung-tam-quan-1'                     => 'Premium studio for rent in central District 1',
			'van-phong-120m2-tang-18-skyline-tower'                        => '120 m² office on level 18 of Skyline Tower',
			'cho-thue-nha-nguyen-can-3-tang-lam-van-phong'                 => '3-storey house for rent, suitable as an office',
			'cho-thue-can-ho-3pn-gan-trung-tam-hoi-nghi'                   => '3-bedroom apartment for rent near the National Convention Center',
			'cho-thue-biet-thu-san-vuon-thao-dien'                         => 'Garden villa for rent in Thao Dien',
			'cho-thue-shophouse-mat-tien-dai-lo-binh-duong'                => 'Shophouse for rent on Binh Duong Boulevard',
			'cho-thue-can-ho-1pn-landpro-riverside-view-song'              => '1-bedroom river-view apartment for rent at LandPro Riverside',
		);
	}

	private static function tr_value( $value ) {
		$map = array(
			'Cơ bản'          => 'Basic',
			'Đầy đủ'          => 'Fully furnished',
			'Bàn giao thô'    => 'Unfurnished',
			'Giá tốt'         => 'Great price',
			'Căn góc'         => 'Corner unit',
			'Hẻm xe hơi'      => 'Car access',
			'Mặt tiền'        => 'Street front',
			'Đang cho thuê'   => 'Leased',
			'View hồ'         => 'Lake view',
			'View biển'       => 'Sea view',
			'Dọn vào ở ngay'  => 'Move-in ready',
			'Hạng A'          => 'Grade A',
			'Có hồ bơi'       => 'With pool',
		);
		return isset( $map[ $value ] ) ? $map[ $value ] : $value;
	}

	/**
	 * Tạo bản tiếng Anh cho dự án hoặc nhà đất.
	 *
	 * @param string $post_type project|property.
	 * @param array  $projects  Ánh xạ ID dự án tiếng Việt => tiếng Anh.
	 * @return array<int,int>
	 */
	private static function posts( $post_type, $projects = array() ) {
		$map = array();
		$ids = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'lang'           => 'vi',
				'post_status'    => 'publish',
			)
		);
		foreach ( $ids as $vi ) {
			if ( 'project' === $post_type ) {
				$info = self::project_en();
				if ( empty( $info[ $vi->post_name ] ) ) {
					continue;
				}
				list( $slug, $title, $intro, $price, $scale, $area, $handover, $legal, $amenities ) = $info[ $vi->post_name ];
				$content  = '<p>' . esc_html( $intro ) . '</p>';
				$excerpt  = $intro;
				$meta     = array(
					'price_text' => $price,
					'scale'      => $scale,
					'area_total' => $area,
					'handover'   => $handover,
					'legal'      => $legal,
					'amenities'  => $amenities,
					'location_desc' => 'Prime location at ' . lp_get( 'address', $vi->ID ) . "\nEasy access to the city centre and main roads\nClose to schools, hospitals and shopping",
					'design'     => $scale . ' on ' . $area . ', modern architecture designed for natural light and ventilation.',
					'policy'     => "Flexible payment schedule\nBank loans of up to 70%\nDiscount for early payment",
				);
			} else {
				$titles = self::property_titles();
				$key = str_replace( 'm%c2%b2', 'm2', $vi->post_name ); // Slug có m² được mã hóa.
				if ( empty( $titles[ $key ] ) ) {
					continue;
				}
				$title   = $titles[ $key ];
				$slug    = sanitize_title( $title );
				$rent    = lp_is_rent( $vi->ID );
				$bed     = (int) lp_get( 'bedrooms', $vi->ID );
				$bath    = (int) lp_get( 'bathrooms', $vi->ID );
				$floors  = (int) lp_get( 'floors', $vi->ID );
				$type    = lp_first_term( $vi->ID, 'lp_type' );
				$names   = self::term_names();
				$type_en = $type && isset( $names['lp_type'][ $type->slug ] ) ? strtolower( $names['lp_type'][ $type->slug ][0] ) : 'property';
				$money   = 'VND ' . number_format( (float) lp_get( 'price', $vi->ID ), 0, '.', ',' ) . ( $rent ? ' per month' : '' );

				$content  = '<p>' . esc_html( ( $rent ? 'For rent: ' : 'For sale: ' ) . $type_en . ' of ' . lp_number( lp_get( 'area', $vi->ID ) ) . ' m² at ' . lp_get( 'address', $vi->ID ) . ', priced at ' . $money . '.' ) . '</p>';
				$content .= '<h3>Highlights</h3><ul>';
				if ( $bed ) {
					$content .= '<li>' . $bed . ' bedrooms and ' . $bath . ' bathrooms with a practical layout.</li>';
				}
				if ( $floors ) {
					$content .= '<li>Solid ' . $floors . '-storey structure.</li>';
				}
				if ( lp_get( 'furniture', $vi->ID ) ) {
					$content .= '<li>Furniture: ' . esc_html( strtolower( self::tr_value( lp_get( 'furniture', $vi->ID ) ) ) ) . '.</li>';
				}
				$content .= '<li>Established, safe neighbourhood close to markets, schools and hospitals.</li><li>Easy access to the city centre.</li></ul>';
				$content .= $rent
					? '<h3>Lease terms</h3><p>Minimum 12-month lease, 2-month deposit, quarterly payments. Viewings available every day of the week.</p>'
					: '<h3>Legal & payment</h3><p>Clear legal status. We assist with the title transfer and bank loans of up to 70%. Price negotiable for serious buyers.</p>';
				$content .= '<p><em>Illustrative information (demo data).</em></p>';
				$excerpt  = '';
				$meta     = array(
					'furniture'  => self::tr_value( lp_get( 'furniture', $vi->ID ) ),
					'badge'      => self::tr_value( lp_get( 'badge', $vi->ID ) ),
					'project_id' => isset( $projects[ (int) lp_get( 'project_id', $vi->ID ) ] ) ? $projects[ (int) lp_get( 'project_id', $vi->ID ) ] : '',
				);
			}

			$en_id = (int) pll_get_post( $vi->ID, self::LANG );
			$arr   = array(
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_date'    => $vi->post_date,
				'post_author'  => $vi->post_author,
			);
			if ( $en_id ) {
				$arr['ID'] = $en_id;
				wp_update_post( $arr );
			} else {
				$en_id = (int) wp_insert_post( $arr );
			}
			pll_set_post_language( $vi->ID, 'vi' );
			pll_set_post_language( $en_id, self::LANG );
			pll_save_post_translations(
				array(
					'vi'       => $vi->ID,
					self::LANG => $en_id,
				)
			);

			// Thông số giữ nguyên, ghi đè các trường cần dịch.
			foreach ( get_post_meta( $vi->ID ) as $key => $values ) {
				if ( 0 === strpos( $key, '_lp_' ) || '_thumbnail_id' === $key ) {
					update_post_meta( $en_id, $key, maybe_unserialize( $values[0] ) );
				}
			}
			foreach ( $meta as $key => $value ) {
				if ( '' === $value || null === $value ) {
					delete_post_meta( $en_id, '_lp_' . $key );
				} else {
					update_post_meta( $en_id, '_lp_' . $key, $value );
				}
			}
			foreach ( array( 'lp_purpose', 'lp_type', 'lp_location' ) as $tax ) {
				if ( is_object_in_taxonomy( $post_type, $tax ) ) {
					wp_set_object_terms( $en_id, self::en_terms( $vi->ID, $tax ), $tax );
				}
			}
			$map[ $vi->ID ] = $en_id;
		}
		return $map;
	}

	/* ================================================================ Trang Elementor */

	/** Chữ trên các trang demo: tiếng Việt => tiếng Anh. */
	private static function texts() {
		return array(
			'Mua bán, cho thuê nhà đất' => 'Buy, sell and rent property',
			'Căn hộ, nhà phố, đất nền và dự án tại TP. Hồ Chí Minh, Hà Nội, Bình Dương. Tin đăng được kiểm tra pháp lý trước khi hiển thị.' => 'Apartments, townhouses, land plots and projects in Ho Chi Minh City, Hanoi and Binh Duong. Every listing is legally checked before it goes live.',
			'Tìm kiếm' => 'Search',
			'Tìm theo loại hình' => 'Browse by property type',
			'Tất cả nhà đất' => 'All properties',
			'Tìm hiểu thêm' => 'Learn more',
			'Dịch vụ của LandPro' => 'Our services',
			'Đồng hành cùng bạn từ lúc tìm nhà đến khi nhận sổ' => 'With you from house hunting to handing over the keys',
			'Tư vấn mua bán' => 'Buying & selling advice',
			'Tìm đúng căn nhà theo nhu cầu và ngân sách, kiểm tra pháp lý, đàm phán giá và đồng hành đến khi nhận sổ.' => 'We find the right home for your needs and budget, check the legal status, negotiate the price and stay with you until you receive the title.',
			'Ký gửi & cho thuê' => 'Listing & leasing',
			'Nhận ký gửi bán, cho thuê nhà đất. Chụp ảnh, đăng tin, dẫn khách xem và soạn hợp đồng thuê minh bạch.' => 'List your property for sale or rent with us. We take photos, publish the listing, show it to buyers and prepare a clear lease.',
			'Hỗ trợ vay & pháp lý' => 'Loans & legal support',
			'Kết nối ngân hàng vay đến 70% giá trị, hỗ trợ thủ tục công chứng, sang tên và đóng thuế phí.' => 'Bank loans of up to 70% of the value, plus help with notarisation, title transfer and taxes.',
			'Dự án' => 'Projects',
			'Dự án đang mở bán và sắp ra mắt tại các tỉnh, thành' => 'Projects on sale and coming soon across Vietnam',
			'Xem tất cả dự án' => 'View all projects',
			'Khám phá theo khu vực' => 'Explore by location',
			'Tìm nhà đất tại những khu vực sôi động nhất' => 'Find property in the most vibrant areas',
			'Xem tất cả nhà đất' => 'View all properties',
			'Nhà đất đang bán' => 'Properties for sale',
			'Căn hộ, nhà phố, biệt thự, đất nền mới cập nhật' => 'Latest apartments, townhouses, villas and land plots',
			'Xem tất cả nhà bán' => 'View all for sale',
			'Nhà đất cho thuê' => 'Properties for rent',
			'Căn hộ, văn phòng, mặt bằng kinh doanh giá tốt' => 'Apartments, offices and retail space at good prices',
			'Xem tất cả nhà cho thuê' => 'View all for rent',
			'Bạn cần mua, bán hay cho thuê nhà đất?' => 'Buying, selling or renting a property?',
			'Để lại thông tin, chuyên viên LandPro sẽ gọi lại trong 15 phút để định giá, kiểm tra pháp lý, lên kế hoạch tài chính và hẹn lịch xem nhà.' => 'Leave your details and a LandPro consultant will call you back within 15 minutes to value the property, check the paperwork, plan your finances and book a viewing.',
			'Miễn phí tư vấn và dẫn xem nhà' => 'Free consultation and viewings',
			'Bảo mật thông tin khách hàng' => 'Your information stays private',
			'Hỗ trợ thủ tục pháp lý trọn gói' => 'End-to-end legal support',
			'Lọc kết quả' => 'Filter',
			'Không tìm thấy bất động sản phù hợp. Hãy thử bỏ bớt điều kiện lọc.' => 'No matching properties found. Try removing some filters.',
			'Mua bán nhà đất' => 'Property for sale',
			'Căn hộ, nhà phố, biệt thự, đất nền chính chủ, pháp lý rõ ràng' => 'Apartments, townhouses, villas and land plots from owners, with clear legal status',
			'Cho thuê nhà đất' => 'Property for rent',
			'Căn hộ, nhà nguyên căn, văn phòng, mặt bằng kinh doanh' => 'Apartments, whole houses, offices and retail space',
			'Dự án bất động sản' => 'Real estate projects',
			'Thông tin, bảng giá và tiến độ các dự án căn hộ, biệt thự, đất nền' => 'Details, price lists and progress of apartment, villa and land projects',
			'Liên hệ' => 'Contact',
			'Chúng tôi luôn sẵn sàng hỗ trợ bạn' => 'We are always here to help',
			'Thông tin liên hệ' => 'Contact details',
			'Văn phòng làm việc từ 8:00 đến 20:00, tất cả các ngày trong tuần.' => 'Our office is open from 8:00 to 20:00, seven days a week.',
			'Gửi yêu cầu tư vấn' => 'Send us a request',
			'Thứ 2 - Chủ nhật: 8:00 - 20:00' => 'Mon - Sun: 8:00 - 20:00',
			'Ký gửi nhà đất' => 'List your property',
			'Bán nhanh, cho thuê nhanh cùng đội ngũ chuyên viên LandPro – đăng ký miễn phí' => 'Sell or rent faster with LandPro consultants – free to list',
			'LandPro cung cấp dịch vụ môi giới, tư vấn và quản lý bất động sản chuyên nghiệp: thông tin xác thực, pháp lý minh bạch, đồng hành cùng khách hàng trong từng giao dịch.' => 'LandPro provides professional brokerage, advisory and property management services: verified listings, transparent paperwork and support at every step of the deal.',
			'Nhà đất' => 'Properties',
			'Hỗ trợ' => 'Support',
			'Mua nhà' => 'Buy',
			'Thuê nhà' => 'Rent',
			'Tin tức' => 'News',
			'Thước lỗ ban' => 'Lo Ban ruler (Vietnamese)',
			'Xem tuổi - Hướng nhà' => 'Feng shui calculator (Vietnamese)',
			'Copyright © [hfe_current_year] LandPro. Bảo lưu mọi quyền.' => 'Copyright © [hfe_current_year] LandPro. All rights reserved.',
			'Tầng 8, 123 Nguyễn Hữu Thọ, Quận 7, TP. Hồ Chí Minh' => 'Floor 8, 123 Nguyen Huu Tho, District 7, Ho Chi Minh City',
		);
	}

	/** Dịch một giá trị chuỗi: cả chuỗi, hoặc từng đoạn chữ giữa các thẻ HTML. */
	private static function tr_string( $value ) {
		$texts = self::texts();
		if ( isset( $texts[ $value ] ) ) {
			return $texts[ $value ];
		}
		if ( isset( self::$urls[ $value ] ) ) {
			return self::$urls[ $value ];
		}
		if ( false !== strpos( $value, '<' ) ) {
			return preg_replace_callback(
				'/>([^<]+)</u',
				function ( $m ) use ( $texts ) {
					$t = trim( $m[1] );
					return isset( $texts[ $t ] ) ? '>' . str_replace( $t, $texts[ $t ], $m[1] ) . '<' : $m[0];
				},
				$value
			);
		}
		return $value;
	}

	/** Dịch slug term trong cài đặt widget LandPro. */
	private static function tr_slug( $tax, $slug ) {
		return isset( self::$slugs[ $tax . ':' . $slug ] ) ? self::$slugs[ $tax . ':' . $slug ] : $slug;
	}

	/** Duyệt và dịch toàn bộ dữ liệu Elementor. */
	private static function tr_elements( $elements, $menu_slug = '' ) {
		foreach ( $elements as &$el ) {
			$el['id'] = substr( md5( 'en-' . $el['id'] ), 0, 7 );
			$s        = isset( $el['settings'] ) ? $el['settings'] : array();
			array_walk_recursive(
				$s,
				function ( &$v, $k ) {
					if ( is_string( $v ) && '' !== $v && 'url' !== $k ) {
						$v = self::tr_string( $v );
					} elseif ( is_string( $v ) && 'url' === $k && isset( self::$urls[ $v ] ) ) {
						$v = self::$urls[ $v ];
					}
				}
			);
			$type = isset( $el['widgetType'] ) ? $el['widgetType'] : '';
			if ( in_array( $type, array( 'lp-properties', 'lp-search' ), true ) && ! empty( $s['purpose'] ) ) {
				$s['purpose'] = self::tr_slug( 'lp_purpose', $s['purpose'] );
			}
			if ( 'lp-locations' === $type && ! empty( $s['locations'] ) ) {
				$s['locations'] = array_map(
					function ( $slug ) {
						return self::tr_slug( 'lp_location', $slug );
					},
					(array) $s['locations']
				);
			}
			if ( 'lp-types' === $type && ! empty( $s['types'] ) ) {
				$s['types'] = array_map(
					function ( $slug ) {
						return self::tr_slug( 'lp_type', $slug );
					},
					(array) $s['types']
				);
			}
			if ( 'navigation-menu' === $type && $menu_slug ) {
				$s['menu'] = $menu_slug;
			}
			if ( isset( $s['_id'] ) ) {
				$s['_id'] = substr( md5( 'en-' . $s['_id'] ), 0, 7 );
			}
			$el['settings'] = $s;
			if ( ! empty( $el['elements'] ) ) {
				$el['elements'] = self::tr_elements( $el['elements'], $menu_slug );
			}
		}
		return $elements;
	}

	/** Sao chép một bài Elementor (trang, header/footer) sang tiếng Anh. */
	private static function copy_elementor( $vi_id, $slug, $title, $menu_slug = '' ) {
		$vi    = get_post( $vi_id );
		$en_id = (int) pll_get_post( $vi_id, self::LANG );
		$arr   = array(
			'post_type'    => $vi->post_type,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => self::tr_string( $vi->post_excerpt ),
			'post_content' => '',
			'post_author'  => $vi->post_author,
		);
		if ( $en_id ) {
			$arr['ID'] = $en_id;
			wp_update_post( $arr );
		} else {
			$en_id = (int) wp_insert_post( $arr );
		}
		pll_set_post_language( $vi_id, 'vi' );
		pll_set_post_language( $en_id, self::LANG );
		pll_save_post_translations(
			array(
				'vi'       => $vi_id,
				self::LANG => $en_id,
			)
		);
		return $en_id;
	}

	private static function save_elementor_copy( $vi_id, $en_id, $menu_slug = '' ) {
		$data = json_decode( (string) get_post_meta( $vi_id, '_elementor_data', true ), true );
		foreach ( get_post_meta( $vi_id ) as $key => $values ) {
			if ( 0 === strpos( $key, '_elementor_' ) || 0 === strpos( $key, 'ehf_' ) || '_wp_page_template' === $key ) {
				update_post_meta( $en_id, $key, maybe_unserialize( $values[0] ) );
			}
		}
		delete_post_meta( $en_id, '_elementor_css' );
		delete_post_meta( $en_id, '_elementor_element_cache' );
		if ( is_array( $data ) ) {
			update_post_meta( $en_id, '_elementor_data', wp_slash( wp_json_encode( self::tr_elements( $data, $menu_slug ), JSON_UNESCAPED_UNICODE ) ) );
		}
	}

	private static function pages( $pages ) {
		$plan = array(
			'home'     => array( 'home', 'Home', 'LandPro - professional real estate services: buy, sell and rent apartments, townhouses, villas, land plots and new projects with verified information and transparent paperwork.' ),
			'buy'      => array( 'buy', 'Buy', 'Apartments, townhouses, villas and land plots for sale in Ho Chi Minh City, Hanoi and Binh Duong. Filter by location, price, area and bedrooms.' ),
			'rent'     => array( 'rent', 'Rent', 'Apartments, houses, offices and retail space for rent. Filter by location, price, area and bedrooms.' ),
			'projects' => array( 'projects', 'Projects', 'Real estate projects on sale, coming soon and handed over: developer, location, prices, amenities and progress.' ),
			'contact'  => array( 'contact', 'Contact', 'Contact LandPro for advice on buying, selling and renting property and projects.' ),
			'consign'  => array( 'list-your-property', 'List your property', 'List your property for sale or rent for free: send details and photos, our consultants verify, value and publish within 24 working hours.' ),
			'news'     => array( 'news', 'News', 'Real estate market news and tips on buying, renting and investing.' ),
		);
		$en = array();
		// Lượt 1: tạo trang để có đường dẫn tiếng Anh.
		foreach ( $plan as $key => $info ) {
			if ( empty( $pages[ $key ] ) ) {
				continue;
			}
			$en[ $key ] = self::copy_elementor( $pages[ $key ], $info[0], $info[1] );
			update_post_meta( $en[ $key ], '_lp_demo', 1 );
			if ( defined( 'WPSEO_VERSION' ) ) {
				update_post_meta( $en[ $key ], '_yoast_wpseo_metadesc', $info[2] );
			}
		}
		foreach ( $en as $key => $id ) {
			self::$urls[ get_permalink( $pages[ $key ] ) ] = get_permalink( $id );
		}
		self::$urls[ home_url( '/' ) ]        = home_url( '/' . self::LANG . '/' );
		self::$urls[ home_url( '/nha-dat/' ) ] = home_url( '/' . self::LANG . '/nha-dat/' );
		self::$urls[ home_url( '/mua-nha/' ) ] = isset( $en['buy'] ) ? get_permalink( $en['buy'] ) : '';
		self::$urls[ home_url( '/thue-nha/' ) ] = isset( $en['rent'] ) ? get_permalink( $en['rent'] ) : '';
		self::$urls[ home_url( '/du-an/' ) ]   = isset( $en['projects'] ) ? get_permalink( $en['projects'] ) : '';
		self::$urls[ home_url( '/lien-he/' ) ] = isset( $en['contact'] ) ? get_permalink( $en['contact'] ) : '';
		self::$urls[ home_url( '/tin-tuc/' ) ] = isset( $en['news'] ) ? get_permalink( $en['news'] ) : '';

		// Lượt 2: dịch nội dung Elementor.
		foreach ( $en as $key => $id ) {
			if ( 'news' !== $key ) {
				self::save_elementor_copy( $pages[ $key ], $id );
			}
		}
		return $en;
	}

	/* ================================================================ Menu */

	private static function menus( $pages, $en ) {
		$name = 'Main menu (EN)';
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = (int) wp_create_nav_menu( $name );
		}
		foreach ( array( 'home', 'buy', 'rent', 'projects', 'news', 'contact' ) as $key ) {
			if ( ! empty( $en[ $key ] ) ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-object-id' => $en[ $key ],
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
		}
		self::add_switcher( $menu_id );

		$vi_menu = wp_get_nav_menu_object( 'Menu chính' );
		if ( $vi_menu ) {
			self::add_switcher( (int) $vi_menu->term_id );
		}

		// Vị trí menu theo ngôn ngữ (Polylang).
		$opts  = PLL()->options;
		$menus = (array) $opts->get( 'nav_menus' );
		$theme = get_option( 'stylesheet' );
		$menus[ $theme ]['menu-1'] = array(
			'vi'       => $vi_menu ? (int) $vi_menu->term_id : 0,
			self::LANG => $menu_id,
		);
		$opts->set( 'nav_menus', $menus );
		$opts->save();

		return wp_get_nav_menu_object( $menu_id );
	}

	/** Thêm mục "Chuyển ngôn ngữ" của Polylang vào menu (nếu chưa có). */
	private static function add_switcher( $menu_id ) {
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( '#pll_switcher' === $item->url ) {
				return;
			}
		}
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Languages',
				'menu-item-url'    => '#pll_switcher',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			)
		);
		update_post_meta(
			$item_id,
			'_pll_menu_item',
			array(
				'hide_if_no_translation' => 0,
				'hide_current'           => 0,
				'force_home'             => 0,
				'show_flags'             => 1,
				'show_names'             => 1, // Menu mobile/tablet: cờ + tên.
				'dropdown'               => 0,
			)
		);
	}

	/* ================================================================ Header / Footer */

	private static function header_footer( $menu_en ) {
		foreach ( array( 'landpro-header' => 'LandPro - Header (EN)', 'landpro-footer' => 'LandPro - Footer (EN)' ) as $slug => $title ) {
			$vi = get_page_by_path( $slug, OBJECT, 'elementor-hf' );
			if ( ! $vi ) {
				continue;
			}
			$en_id = self::copy_elementor( $vi->ID, $slug . '-en', $title );
			self::save_elementor_copy( $vi->ID, $en_id, $menu_en ? $menu_en->slug : '' );
		}
	}

	/* ================================================================ Form */

	private static function form( $form_id ) {
		global $wpdb;
		if ( ! $form_id ) {
			return;
		}
		$table = $wpdb->prefix . 'fluentform_forms';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $form_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		if ( ! $row ) {
			return;
		}
		$dict   = array(
			'Họ và tên'                    => 'Full name',
			'Nguyễn Văn A'                 => 'John Smith',
			'Vui lòng nhập họ tên'         => 'Please enter your name',
			'Số điện thoại'                => 'Phone number',
			'Vui lòng nhập số điện thoại'  => 'Please enter your phone number',
			'Vui lòng nhập email'          => 'Please enter your email',
			'Email không hợp lệ'           => 'Invalid email address',
			'Nhu cầu'                      => 'Interested in',
			'- Chọn nhu cầu -'             => '- Select -',
			'Mua nhà'                      => 'Buying',
			'Thuê nhà'                     => 'Renting',
			'Ký gửi bán / cho thuê'        => 'Listing my property',
			'Ký gửi'                       => 'Listing',
			'Tư vấn đầu tư'                => 'Investment advice',
			'Lời nhắn'                     => 'Message',
			'Bạn cần tư vấn gì? Thời gian thuận tiện để xem nhà?' => 'How can we help? When would you like to view?',
			'Vui lòng nhập trường này'     => 'This field is required',
			'Trang gửi (BĐS quan tâm)'     => 'Page sent from',
			'Gửi yêu cầu tư vấn'           => 'Send request',
		);
		$fields = json_decode( $row['form_fields'], true );
		array_walk_recursive(
			$fields,
			function ( &$v ) use ( $dict ) {
				if ( is_string( $v ) && isset( $dict[ $v ] ) ) {
					$v = $dict[ $v ];
				}
			}
		);
		$existing = (int) lp_option( 'form_id_' . self::LANG );
		$data     = array(
			'title'       => 'Consultation request (English)',
			'status'      => 'published',
			'form_fields' => wp_json_encode( $fields, JSON_UNESCAPED_UNICODE ),
			'has_payment' => 0,
			'type'        => 'form',
			'created_by'  => $row['created_by'],
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		);
		if ( $existing && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $existing ) ) ) { // phpcs:ignore WordPress.DB
			$wpdb->update( $table, $data, array( 'id' => $existing ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$en_form = $existing;
		} else {
			$wpdb->insert( $table, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$en_form = (int) $wpdb->insert_id;
			$meta    = $wpdb->prefix . 'fluentform_form_meta';
			foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, value FROM {$meta} WHERE form_id = %d", $form_id ) ) as $m ) { // phpcs:ignore WordPress.DB
				$value = $m->value;
				if ( 'formSettings' === $m->meta_key ) {
					$st = json_decode( $value, true );
					$st['confirmation']['messageToShow'] = 'Thank you! A consultant will contact you within 15 minutes during business hours.';
					$value = wp_json_encode( $st, JSON_UNESCAPED_UNICODE );
				}
				$wpdb->insert( $meta, array( 'form_id' => $en_form, 'meta_key' => $m->meta_key, 'value' => $value ) ); // phpcs:ignore WordPress.DB
			}
		}
		$opts                          = get_option( 'lp_settings', array() );
		$opts[ 'form_id_' . self::LANG ] = $en_form;
		update_option( 'lp_settings', $opts );
	}

	/* ================================================================ Chuỗi giao diện */

	private static function strings() {
		$lang = PLL()->model->get_language( self::LANG );
		if ( ! $lang || ! class_exists( 'PLL_MO' ) ) {
			return;
		}
		$mo = new PLL_MO();
		$mo->import_from_db( $lang );
		$extra = array(
			'LandPro'                              => 'LandPro',
			'Dịch vụ bất động sản chuyên nghiệp'   => 'Professional real estate services',
			'Chuyên viên tư vấn LandPro'           => 'LandPro property consultant',
			// Mẫu tiêu đề / mô tả SEO của Yoast (Polylang đăng ký sẵn để dịch).
			'Lưu trữ %%term_title%% %%page%% %%sep%% %%sitename%%'  => '%%term_title%% Archives %%page%% %%sep%% %%sitename%%',
			'%%name%%, Tác giả tại %%sitename%% %%page%%'            => '%%name%%, Author at %%sitename%% %%page%%',
			'Bạn đã tìm %%searchphrase%% %%page%% %%sep%% %%sitename%%' => 'You searched for %%searchphrase%% %%page%% %%sep%% %%sitename%%',
			'Không tìm thấy trang %%sep%% %%sitename%%'              => 'Page not found %%sep%% %%sitename%%',
			'Lưu trữ cho'                                            => 'Archives for',
			'Bạn đã tìm'                                             => 'You searched for',
			'Lỗi 404: Không tìm thấy trang'                          => 'Error 404: Page not found',
			'Bài viết %%POSTLINK%% đã xuất hiện đầu tiên vào ngày %%BLOGLINK%%.' => 'The post %%POSTLINK%% appeared first on %%BLOGLINK%%.',
			'Nhà đất mua bán, cho thuê %%page%% %%sep%% %%sitename%%' => 'Properties for sale and rent %%page%% %%sep%% %%sitename%%',
			'Dự án %%title%% %%sep%% %%sitename%%'                   => '%%title%% project %%sep%% %%sitename%%',
			'%%term_title%% nhà đất %%page%% %%sep%% %%sitename%%'   => 'Property %%term_title%% %%page%% %%sep%% %%sitename%%',
			'Nhà đất %%term_title%% %%page%% %%sep%% %%sitename%%'   => 'Property in %%term_title%% %%page%% %%sep%% %%sitename%%',
			'Lưu trữ %%pt_plural%% %%page%% %%sep%% %%sitename%%'    => '%%pt_plural%% Archive %%page%% %%sep%% %%sitename%%',
			'Tìm nhà đất mua bán, cho thuê: căn hộ, nhà phố, biệt thự, đất nền. Lọc theo khu vực, mức giá, diện tích, số phòng ngủ.' => 'Find property for sale and rent: apartments, townhouses, villas and land plots. Filter by location, price, area and bedrooms.',
			'Danh sách %%term_title%% đang bán và cho thuê, lọc theo khu vực, giá, diện tích.' => '%%term_title%% for sale and rent. Filter by location, price and area.',
			'Mua bán, cho thuê nhà đất, căn hộ, nhà phố tại %%term_title%%. Thông tin xác thực, giá cập nhật, pháp lý rõ ràng.' => 'Property, apartments and townhouses for sale and rent in %%term_title%%. Verified listings, current prices, clear legal status.',
		);
		foreach ( array_merge( lp_strings(), $extra ) as $vi => $en ) {
			$mo->add_entry( $mo->make_entry( $vi, $en ) );
		}
		$mo->export_to_db( $lang );
	}
}
