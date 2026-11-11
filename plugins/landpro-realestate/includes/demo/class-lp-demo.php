<?php
/**
 * Tạo dữ liệu demo: phân loại, dự án, nhà đất, form Fluent Forms, menu,
 * header/footer (Elementor Header & Footer Builder), các trang dựng bằng Elementor.
 *
 * Chạy: wp landpro demo [--reset]    hoặc    Nhà đất → Dữ liệu demo.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Demo {

	/** Bộ đếm để sinh ID phần tử Elementor ổn định. */
	private static $el = 0;

	/** Log từng bước. */
	private static $log = array();

	public static function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'landpro demo', array( __CLASS__, 'cli' ) );
		}
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_lp_demo_import', array( __CLASS__, 'handle_admin' ) );
	}

	/**
	 * Tạo dữ liệu demo.
	 *
	 * ## OPTIONS
	 *
	 * [--reset]
	 * : Xóa toàn bộ nhà đất, dự án và phân loại hiện có trước khi tạo.
	 *
	 * @param array $args       Tham số.
	 * @param array $assoc_args Tùy chọn.
	 */
	public static function cli( $args, $assoc_args ) {
		self::run( ! empty( $assoc_args['reset'] ) );
		do_action( 'lp_demo_built' ); // Xóa cache trang (nếu có plugin cache).
		foreach ( self::$log as $line ) {
			WP_CLI::log( $line );
		}
		WP_CLI::success( 'Đã tạo dữ liệu demo.' );
	}

	public static function menu() {
		add_submenu_page( 'edit.php?post_type=property', 'Dữ liệu demo', 'Dữ liệu demo', 'manage_options', 'lp-demo', array( __CLASS__, 'admin_page' ) );
	}

	public static function admin_page() {
		?>
		<div class="wrap">
			<h1>Dữ liệu demo LandPro</h1>
			<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success"><p>Đã tạo dữ liệu demo.</p></div>
			<?php endif; ?>
			<p>Tạo: khu vực, loại nhà đất, 6 dự án, 18 tin nhà đất (bán và cho thuê), form liên hệ Fluent Forms, menu chính, header/footer và các trang Trang chủ, Mua nhà, Thuê nhà, Dự án, Liên hệ dựng bằng Elementor.</p>
			<p><strong>Lưu ý:</strong> chọn "Xóa dữ liệu cũ" sẽ xóa toàn bộ nhà đất, dự án và phân loại hiện có. Các trang demo cùng đường dẫn sẽ bị ghi đè.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="lp_demo_import">
				<?php wp_nonce_field( 'lp_demo_import' ); ?>
				<p><label><input type="checkbox" name="reset" value="1"> Xóa dữ liệu nhà đất / dự án cũ trước khi tạo</label></p>
				<?php submit_button( 'Tạo dữ liệu demo', 'primary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Không có quyền.' );
		}
		check_admin_referer( 'lp_demo_import' );
		self::run( ! empty( $_POST['reset'] ) );
		do_action( 'lp_demo_built' );
		wp_safe_redirect( admin_url( 'edit.php?post_type=property&page=lp-demo&done=1' ) );
		exit;
	}

	private static function log( $msg ) {
		self::$log[] = $msg;
	}

	/* ================================================================ Chạy */

	public static function run( $reset = false ) {
		$data = require __DIR__ . '/data.php';
		wp_suspend_cache_invalidation( false );

		if ( $reset ) {
			self::reset();
		}
		$terms    = self::terms( $data );
		$projects = self::projects( $data['projects'], $terms );
		self::properties( $data['properties'], $terms, $projects );
		$form_id  = self::form();
		self::settings( $form_id );
		$pages    = self::pages( $data );
		$menu     = self::nav_menu( $pages );
		self::header_footer( $menu );
		self::kit();
		self::site_options( $pages );

		if ( LP_Demo_I18n::available() ) {
			LP_Demo_I18n::run(
				$pages,
				$form_id,
				function ( $msg ) {
					self::log( $msg );
				}
			);
		}

		self::news();

		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		flush_rewrite_rules();
	}

	/** Xóa nhà đất, dự án, phân loại. */
	private static function reset() {
		$ids = get_posts(
			array(
				'post_type'      => array( 'property', 'project' ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		foreach ( array( 'lp_purpose', 'lp_type', 'lp_location' ) as $tax ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $tax,
					'hide_empty' => false,
					'fields'     => 'ids',
				)
			);
			foreach ( (array) $terms as $term_id ) {
				wp_delete_term( $term_id, $tax );
			}
		}
		self::log( sprintf( 'Đã xóa %d bài nhà đất / dự án cũ.', count( $ids ) ) );
	}

	/* ================================================================ Phân loại */

	private static function term( $name, $slug, $taxonomy, $parent = 0 ) {
		$found = get_term_by( 'slug', $slug, $taxonomy );
		if ( $found ) {
			return (int) $found->term_id;
		}
		$res = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'   => $slug,
				'parent' => $parent,
			)
		);
		return is_wp_error( $res ) ? 0 : (int) $res['term_id'];
	}

	private static function terms( $data ) {
		$out = array();
		foreach ( $data['purposes'] as $slug => $name ) {
			$out['lp_purpose'][ $slug ] = self::term( $name, $slug, 'lp_purpose' );
		}
		foreach ( $data['types'] as $slug => $name ) {
			$out['lp_type'][ $slug ] = self::term( $name, $slug, 'lp_type' );
		}
		foreach ( $data['locations'] as $slug => $info ) {
			$parent                        = $info[1] ? $out['lp_location'][ $info[1] ] : 0;
			$out['lp_location'][ $slug ]   = self::term( $info[0], $slug, 'lp_location', $parent );
		}
		self::log( 'Đã tạo phân loại: nhu cầu, loại nhà đất, khu vực.' );
		return $out;
	}

	/** Gắn khu vực con + cha. */
	private static function location_ids( $terms, $slug ) {
		$ids  = array( $terms['lp_location'][ $slug ] );
		$term = get_term( $ids[0], 'lp_location' );
		if ( $term && $term->parent ) {
			$ids[] = $term->parent;
		}
		return $ids;
	}

	/* ================================================================ Bài viết */

	private static function upsert_post( $post_type, $slug, $postarr ) {
		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		$postarr  = array_merge(
			array(
				'post_type'   => $post_type,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_author' => self::author(),
			),
			$postarr
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			return (int) wp_update_post( $postarr );
		}
		return (int) wp_insert_post( $postarr );
	}

	private static function author() {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'fields'  => 'ID',
			)
		);
		return $admins ? (int) $admins[0] : 1;
	}

	/** ID ảnh mới (đã nhập vào thư viện) theo tên vị trí, hoặc ảnh dự phòng. */
	private static function img( $slot, $fallback = 0 ) {
		$map = get_option( 'lp_new_images', array() );
		return ! empty( $map[ $slot ] ) && get_post( $map[ $slot ] ) ? (int) $map[ $slot ] : (int) $fallback;
	}

	/** Bộ 4 ảnh mới (đại diện + 3 ảnh thư viện) theo tiền tố, hoặc danh sách cũ. */
	private static function img_set( $prefix, $fallback ) {
		$ids = array();
		foreach ( array( 'cover', '1', '2', '3' ) as $k ) {
			$id = self::img( $prefix . '-' . $k );
			if ( $id ) {
				$ids[] = $id;
			}
		}
		return $ids ? $ids : $fallback;
	}

	/** Chỉ giữ ảnh còn tồn tại trong thư viện; gắn alt nếu đang trống. */
	private static function images( $ids, $alt ) {
		$ok = array();
		foreach ( $ids as $id ) {
			if ( 'attachment' === get_post_type( $id ) ) {
				$ok[] = (int) $id;
				if ( ! get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
					update_post_meta( $id, '_wp_attachment_image_alt', $alt );
				}
			}
		}
		return $ok;
	}

	/**
	 * Tọa độ demo: tâm quận/huyện + độ lệch cố định theo tên (để các ghim không chồng nhau).
	 *
	 * @return array{lat: float|string, lng: float|string}
	 */
	private static function geo( $loc, $seed ) {
		$centers = array(
			'quan-1'      => array( 10.7756, 106.7019 ),
			'quan-7'      => array( 10.7340, 106.7215 ),
			'thu-duc'     => array( 10.8020, 106.7600 ),
			'binh-thanh'  => array( 10.8050, 106.7110 ),
			'go-vap'      => array( 10.8380, 106.6650 ),
			'cau-giay'    => array( 21.0310, 105.7940 ),
			'tay-ho'      => array( 21.0680, 105.8190 ),
			'nam-tu-liem' => array( 21.0170, 105.7650 ),
			'thu-dau-mot' => array( 10.9800, 106.6550 ),
			'vung-tau'    => array( 10.3460, 107.0840 ),
		);
		if ( ! isset( $centers[ $loc ] ) ) {
			return array( 'lat' => '', 'lng' => '' );
		}
		$h = crc32( $seed );
		return array(
			'lat' => round( $centers[ $loc ][0] + ( ( $h % 1000 ) / 1000 - 0.5 ) * 0.024, 7 ),
			'lng' => round( $centers[ $loc ][1] + ( ( intdiv( $h, 1000 ) % 1000 ) / 1000 - 0.5 ) * 0.024, 7 ),
		);
	}

	private static function set_meta( $post_id, $meta ) {
		foreach ( $meta as $key => $value ) {
			if ( '' === $value || null === $value || ( is_int( $value ) && 0 === $value ) ) {
				delete_post_meta( $post_id, '_lp_' . $key );
			} else {
				update_post_meta( $post_id, '_lp_' . $key, $value );
			}
		}
	}

	private static function projects( $items, $terms ) {
		$ids = array();
		foreach ( $items as $i => $p ) {
			$content = '<p>' . esc_html( $p['intro'] ) . '</p>';

			$id = self::upsert_post(
				'project',
				$p['slug'],
				array(
					'post_title'   => $p['title'],
					'post_content' => $content,
					'post_excerpt' => $p['intro'],
					'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 30 + $i ) * DAY_IN_SECONDS ),
				)
			);
			$imgs = self::images( self::img_set( 'du-an-' . $p['slug'], $p['images'] ), $p['title'] );
			if ( $imgs ) {
				set_post_thumbnail( $id, $imgs[0] );
			}
			wp_set_object_terms( $id, array( $terms['lp_type'][ $p['type'] ] ), 'lp_type' );
			wp_set_object_terms( $id, self::location_ids( $terms, $p['location'] ), 'lp_location' );
			self::set_meta(
				$id,
				array(
					'status'     => $p['status'],
					'developer'  => $p['developer'],
					'address'    => $p['address'],
					'price_text' => $p['price'],
					'scale'      => $p['scale'],
					'area_total' => $p['area'],
					'handover'   => $p['handover'],
					'legal'      => $p['legal'],
					'amenities'  => $p['amenities'],
					'location_desc' => isset( $p['location_desc'] ) ? $p['location_desc'] : '',
					'design'     => isset( $p['design'] ) ? $p['design'] : '',
					'policy'     => isset( $p['policy'] ) ? $p['policy'] : '',
					'gallery'    => implode( ',', array_slice( $imgs, 1 ) ),
					'featured'   => $p['featured'],
				) + self::geo( $p['location'], $p['slug'] )
			);
			$ids[ $p['slug'] ] = $id;
		}
		self::log( sprintf( 'Đã tạo %d dự án.', count( $ids ) ) );
		return $ids;
	}

	private static function properties( $items, $terms, $projects ) {
		$data      = require __DIR__ . '/data.php';
		$type_name = $data['types'];
		$count     = 0;
		foreach ( $items as $i => $row ) {
			list( $purpose, $type, $loc, $title, $price, $area, $bed, $bath, $floors, $dir, $legal, $furniture, $address, $project, $badge, $featured, $images ) = $row;

			$slug   = sanitize_title( $title );
			$rent   = 'cho-thue' === $purpose;
			$money  = lp_format_money( $price, $rent );
			$loc_nm = $data['locations'][ $loc ][0];

			$content  = '<p>' . esc_html( ( $rent ? 'Cho thuê ' : 'Cần bán ' ) . mb_strtolower( $type_name[ $type ] ) . ' diện tích ' . lp_number( $area ) . ' m² tại ' . $address . ', mức giá ' . $money . '.' ) . '</p>';
			$content .= '<h3>Đặc điểm nổi bật</h3><ul>';
			if ( $bed ) {
				$content .= '<li>' . (int) $bed . ' phòng ngủ, ' . (int) $bath . ' phòng tắm, bố trí công năng hợp lý.</li>';
			}
			if ( $floors ) {
				$content .= '<li>Kết cấu ' . (int) $floors . ' tầng kiên cố, xây dựng chắc chắn.</li>';
			}
			if ( $furniture ) {
				$content .= '<li>Nội thất: ' . esc_html( mb_strtolower( $furniture ) ) . '.</li>';
			}
			$content .= '<li>Khu dân cư hiện hữu, an ninh tốt, gần chợ, trường học và bệnh viện.</li>';
			$content .= '<li>Giao thông thuận tiện, dễ dàng di chuyển tới trung tâm ' . esc_html( $loc_nm ) . '.</li>';
			$content .= '</ul>';
			$content .= $rent
				? '<h3>Điều kiện thuê</h3><p>Hợp đồng tối thiểu 12 tháng, đặt cọc 2 tháng, thanh toán theo quý. Có thể xem nhà vào tất cả các ngày trong tuần.</p>'
				: '<h3>Pháp lý & thanh toán</h3><p>Pháp lý rõ ràng, hỗ trợ thủ tục sang tên và vay ngân hàng đến 70% giá trị. Giá có thương lượng cho khách thiện chí.</p>';
			$content .= '<p><em>Thông tin mang tính minh họa (dữ liệu demo).</em></p>';

			$id = self::upsert_post(
				'property',
				$slug,
				array(
					'post_title'   => $title,
					'post_content' => $content,
					'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * DAY_IN_SECONDS ),
				)
			);
			$imgs = self::images( self::img_set( 'nha-dat-' . str_replace( 'm%c2%b2', 'm2', $slug ), $images ), $title );
			if ( $imgs ) {
				set_post_thumbnail( $id, $imgs[0] );
			}
			wp_set_object_terms( $id, array( $terms['lp_purpose'][ $purpose ] ), 'lp_purpose' );
			wp_set_object_terms( $id, array( $terms['lp_type'][ $type ] ), 'lp_type' );
			wp_set_object_terms( $id, self::location_ids( $terms, $loc ), 'lp_location' );
			self::set_meta(
				$id,
				array(
					'price'      => $price,
					'area'       => $area,
					'bedrooms'   => $bed,
					'bathrooms'  => $bath,
					'floors'     => $floors,
					'direction'  => $dir,
					'legal'      => $legal,
					'furniture'  => $furniture,
					'address'    => $address,
					'project_id' => $project && isset( $projects[ $project ] ) ? $projects[ $project ] : 0,
					'badge'      => $badge,
					'featured'   => $featured,
					'gallery'    => implode( ',', array_slice( $imgs, 1 ) ),
				) + self::geo( $loc, $title )
			);
			++$count;
		}
		self::log( sprintf( 'Đã tạo %d tin nhà đất.', $count ) );
	}

	/* ================================================================ Tin tức */

	/** Tạo bài viết demo (VI + EN nếu có Polylang), xóa bài & chuyên mục không thuộc bộ demo. */
	private static function news() {
		$data = require __DIR__ . '/news.php';
		$pll  = function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' );
		$cats = array();
		foreach ( $data['categories'] as $slug => $c ) {
			$vi = self::term( $c[0], $slug, 'category' );
			$cats[ $slug ]['vi'] = $vi;
			if ( $pll ) {
				pll_set_term_language( $vi, 'vi' );
				$en = self::term( $c[2], $c[1], 'category' );
				pll_set_term_language( $en, 'en' );
				pll_save_term_translations( array( 'vi' => $vi, 'en' => $en ) );
				$cats[ $slug ]['en'] = $en;
			}
		}
		$keep = array();
		foreach ( $data['posts'] as $p ) {
			$date = gmdate( 'Y-m-d H:i:s', time() - $p['days'] * DAY_IN_SECONDS );
			$img  = self::img( $p['image'] );
			$vi   = self::upsert_post( 'post', $p['slug'], array( 'post_title' => $p['title'], 'post_excerpt' => $p['excerpt'], 'post_content' => $p['body'], 'post_date' => $date, 'comment_status' => 'open' ) );
			wp_set_post_categories( $vi, array( $cats[ $p['cat'] ]['vi'] ) );
			if ( $img ) {
				set_post_thumbnail( $vi, $img );
			}
			$keep[] = $vi;
			if ( $pll ) {
				pll_set_post_language( $vi, 'vi' );
				$en_body = ! empty( $p['en_body'] ) ? $p['en_body'] : '<p>' . esc_html( $p['en_excerpt'] ) . '</p><h2>Overview</h2><p>This article is available in full in Vietnamese. Switch the language using the flag at the top of the page.</p><h2>Need advice?</h2><p>Contact a LandPro consultant for a free consultation.</p><p><em>Illustrative content (demo data).</em></p>';
				$en = self::upsert_post( 'post', sanitize_title( $p['en_title'] ), array( 'post_title' => $p['en_title'], 'post_excerpt' => $p['en_excerpt'], 'post_content' => $en_body, 'post_date' => $date, 'comment_status' => 'open' ) );
				pll_set_post_language( $en, 'en' );
				pll_save_post_translations( array( 'vi' => $vi, 'en' => $en ) );
				wp_set_post_categories( $en, array( $cats[ $p['cat'] ]['en'] ) );
				if ( $img ) {
					set_post_thumbnail( $en, $img );
				}
				$keep[] = $en;
			}
		}
		// Xóa bài cũ không thuộc bộ demo.
		$old = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'post__not_in' => $keep, 'lang' => '' ) );
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
		// Xóa chuyên mục cũ.
		$keep_cats = array();
		foreach ( $cats as $c ) {
			$keep_cats = array_merge( $keep_cats, array_values( $c ) );
		}
		foreach ( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'ids', 'lang' => '' ) ) as $tid ) {
			if ( ! in_array( (int) $tid, array_map( 'intval', $keep_cats ), true ) && (int) $tid !== (int) get_option( 'default_category' ) ) {
				wp_delete_term( $tid, 'category' );
			}
		}
		update_option( 'default_category', $cats['thi-truong']['vi'] );
		foreach ( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'ids', 'lang' => '' ) ) as $tid ) {
			if ( ! in_array( (int) $tid, array_map( 'intval', $keep_cats ), true ) ) {
				wp_delete_term( $tid, 'category' );
			}
		}
		self::log( sprintf( 'Đã tạo %d bài viết, xóa %d bài cũ.', count( $keep ), count( $old ) ) );
	}

	/* ================================================================ Fluent Forms */

	private static function form() {
		global $wpdb;
		$table = $wpdb->prefix . 'fluentform_forms';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::log( 'Bỏ qua form: chưa cài Fluent Forms.' );
			return 0;
		}
		$existing = (int) get_option( 'lp_demo_form_id' );
		if ( $existing && $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $existing ) ) ) { // phpcs:ignore WordPress.DB
			return $existing;
		}

		$req   = function ( $msg ) {
			return array(
				'required' => array(
					'value'   => true,
					'message' => $msg,
				),
			);
		};
		$opt   = array(
			'required' => array(
				'value'   => false,
				'message' => 'Vui lòng nhập trường này',
			),
		);
		$k     = 0;
		$key   = function () use ( &$k ) {
			return 'el_lp' . ( ++$k );
		};
		$text  = function ( $name, $label, $placeholder, $rules, $type = 'text' ) use ( $key ) {
			return array(
				'element'        => 'input_text',
				'attributes'     => array(
					'type'        => $type,
					'name'        => $name,
					'value'       => '',
					'class'       => '',
					'placeholder' => $placeholder,
				),
				'settings'       => array(
					'container_class'    => '',
					'label'              => $label,
					'label_placement'    => '',
					'admin_field_label'  => $label,
					'help_message'       => '',
					'validation_rules'   => $rules,
					'conditional_logics' => array(),
				),
				'editor_options' => array(
					'title'      => 'Simple Text',
					'icon_class' => 'ff-edit-text',
					'template'   => 'inputText',
				),
				'uniqElKey'      => $key(),
			);
		};

		$fields = array(
			'fields'       => array(
				$text( 'ho_ten', 'Họ và tên', 'Nguyễn Văn A', $req( 'Vui lòng nhập họ tên' ) ),
				$text(
					'so_dien_thoai',
					'Số điện thoại',
					'09xx xxx xxx',
					array_merge(
						$req( 'Vui lòng nhập số điện thoại' ),
						array()
					),
					'tel'
				),
				array(
					'element'        => 'input_email',
					'attributes'     => array(
						'type'        => 'email',
						'name'        => 'email',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'email@example.com',
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Email',
						'label_placement'    => '',
						'help_message'       => '',
						'admin_field_label'  => 'Email',
						'validation_rules'   => array(
							'required' => array(
								'value'   => false,
								'message' => 'Vui lòng nhập email',
							),
							'email'    => array(
								'value'   => true,
								'message' => 'Email không hợp lệ',
							),
						),
						'conditional_logics' => array(),
					),
					'editor_options' => array(
						'title'      => 'Email Address',
						'icon_class' => 'ff-edit-email',
						'template'   => 'inputText',
					),
					'uniqElKey'      => $key(),
				),
				array(
					'element'        => 'select',
					'attributes'     => array(
						'name'  => 'nhu_cau',
						'value' => '',
						'id'    => '',
						'class' => '',
					),
					'settings'       => array(
						'label'              => 'Nhu cầu',
						'admin_field_label'  => 'Nhu cầu',
						'help_message'       => '',
						'container_class'    => '',
						'label_placement'    => '',
						'placeholder'        => '- Chọn nhu cầu -',
						'advanced_options'   => array(
							array( 'label' => 'Mua nhà', 'value' => 'Mua nhà', 'calc_value' => '' ),
							array( 'label' => 'Thuê nhà', 'value' => 'Thuê nhà', 'calc_value' => '' ),
							array( 'label' => 'Ký gửi bán / cho thuê', 'value' => 'Ký gửi', 'calc_value' => '' ),
							array( 'label' => 'Tư vấn đầu tư', 'value' => 'Tư vấn đầu tư', 'calc_value' => '' ),
						),
						'calc_value_status'  => false,
						'enable_image_input' => false,
						'enable_select_2'    => 'no',
						'randomize_options'  => 'no',
						'validation_rules'   => $opt,
						'conditional_logics' => array(),
					),
					'editor_options' => array(
						'title'      => 'Dropdown',
						'icon_class' => 'ff-edit-dropdown',
						'element'    => 'select',
						'template'   => 'select',
					),
					'uniqElKey'      => $key(),
				),
				array(
					'element'        => 'textarea',
					'attributes'     => array(
						'name'        => 'loi_nhan',
						'value'       => '',
						'id'          => '',
						'class'       => '',
						'placeholder' => 'Bạn cần tư vấn gì? Thời gian thuận tiện để xem nhà?',
						'rows'        => 3,
						'cols'        => 2,
					),
					'settings'       => array(
						'container_class'    => '',
						'label'              => 'Lời nhắn',
						'admin_field_label'  => 'Lời nhắn',
						'label_placement'    => '',
						'help_message'       => '',
						'validation_rules'   => $opt,
						'conditional_logics' => array(),
					),
					'editor_options' => array(
						'title'      => 'Text Area',
						'icon_class' => 'ff-edit-textarea',
						'template'   => 'inputTextarea',
					),
					'uniqElKey'      => $key(),
				),
				array(
					'element'        => 'input_hidden',
					'attributes'     => array(
						'type'  => 'hidden',
						'name'  => 'trang_gui',
						'value' => '{embed_post.title} - {embed_post.permalink}',
					),
					'settings'       => array( 'admin_field_label' => 'Trang gửi (BĐS quan tâm)' ),
					'editor_options' => array(
						'title'      => 'Hidden Field',
						'icon_class' => 'ff-edit-hidden-field',
						'template'   => 'inputHidden',
					),
					'uniqElKey'      => $key(),
				),
			),
			'submitButton' => array(
				'uniqElKey'      => 'el_lp_submit',
				'element'        => 'button',
				'attributes'     => array(
					'type'  => 'submit',
					'class' => '',
				),
				'settings'       => array(
					'align'            => 'left',
					'button_style'     => 'default',
					'container_class'  => '',
					'help_message'     => '',
					'background_color' => '#c02038',
					'button_size'      => 'md',
					'color'            => '#ffffff',
					'button_ui'        => array(
						'type'    => 'default',
						'text'    => 'Gửi yêu cầu tư vấn',
						'img_url' => '',
					),
				),
				'editor_options' => array( 'title' => 'Submit Button' ),
			),
		);
		foreach ( $fields['fields'] as $i => &$f ) {
			$f['index'] = $i;
		}
		unset( $f );

		$now = current_time( 'mysql' );
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array(
				'title'       => 'Đăng ký tư vấn bất động sản',
				'status'      => 'published',
				'appearance_settings' => null,
				'form_fields' => wp_json_encode( $fields, JSON_UNESCAPED_UNICODE ),
				'has_payment' => 0,
				'type'        => 'form',
				'conditions'  => null,
				'created_by'  => self::author(),
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);
		$form_id = (int) $wpdb->insert_id;

		// Sao chép cài đặt mặc định từ form mẫu của Fluent Forms (nếu có), đổi thông báo sang tiếng Việt.
		$meta_table = $wpdb->prefix . 'fluentform_form_meta';
		$template   = (int) $wpdb->get_var( "SELECT MIN(id) FROM {$table} WHERE id <> {$form_id}" ); // phpcs:ignore WordPress.DB
		$rows       = $template ? $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, value FROM {$meta_table} WHERE form_id = %d", $template ) ) : array(); // phpcs:ignore WordPress.DB
		foreach ( $rows as $row ) {
			$value = $row->value;
			if ( 'formSettings' === $row->meta_key ) {
				$settings = json_decode( $value, true );
				$settings['confirmation']['messageToShow']        = 'Cảm ơn bạn! Chuyên viên tư vấn sẽ liên hệ trong vòng 15 phút (giờ hành chính).';
				$settings['confirmation']['samePageFormBehavior'] = 'hide_form';
				$value = wp_json_encode( $settings, JSON_UNESCAPED_UNICODE );
			}
			if ( 'template_name' === $row->meta_key ) {
				continue;
			}
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$meta_table,
				array(
					'form_id'  => $form_id,
					'meta_key' => $row->meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery
					'value'    => $value,
				)
			);
		}
		update_option( 'lp_demo_form_id', $form_id, false );
		self::log( 'Đã tạo form Fluent Forms #' . $form_id . '.' );
		return $form_id;
	}

	private static function settings( $form_id ) {
		$opts = get_option( 'lp_settings', array() );
		$opts = array_merge(
			array(
				'agent_name' => 'Chuyên viên tư vấn LandPro',
				'hotline'    => '0898 614 962',
				'zalo'       => '',
				'email'      => get_option( 'admin_email' ),
				'address'    => 'Tầng 8, 123 Nguyễn Hữu Thọ, Quận 7, TP. Hồ Chí Minh',
				'per_page'   => 12,
			),
			array_filter( $opts )
		);
		if ( $form_id ) {
			$opts['form_id'] = $form_id;
		}
		update_option( 'lp_settings', $opts );
	}

	/* ================================================================ Elementor: phần tử */

	private static function id() {
		return substr( md5( 'landpro-' . ( ++self::$el ) ), 0, 7 );
	}

	private static function box( $settings, $children = array(), $inner = false ) {
		// Khung con phải là "full width" thì độ rộng % mới có tác dụng (khung "boxed" luôn rộng bằng boxed_width).
		if ( $inner && ! isset( $settings['content_width'] ) ) {
			$settings['content_width'] = 'full';
		}
		return array(
			'id'       => self::id(),
			'elType'   => 'container',
			'isInner'  => $inner,
			'settings' => $settings,
			'elements' => $children,
		);
	}

	private static function w( $type, $settings ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'isInner'    => false,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	private static function px( $top, $right = null, $bottom = null, $left = null ) {
		$right  = null === $right ? $top : $right;
		$bottom = null === $bottom ? $top : $bottom;
		$left   = null === $left ? $right : $left;
		return array(
			'unit'     => 'px',
			'top'      => (string) $top,
			'right'    => (string) $right,
			'bottom'   => (string) $bottom,
			'left'     => (string) $left,
			'isLinked' => false,
		);
	}

	private static function size( $n, $unit = 'px' ) {
		return array(
			'unit' => $unit,
			'size' => $n,
		);
	}

	private static function heading( $text, $tag = 'h2', $extra = array() ) {
		// Cỡ chữ theo thang chữ của theme (H1–H6, class lp-display...). Không đặt cỡ riêng từng tiêu đề.
		$extra = self::strip_font_size( $extra );
		return self::w(
			'heading',
			array_merge(
				array(
					'title'       => $text,
					'header_size' => $tag,
					'align'       => 'center',
				),
				$extra
			)
		);
	}

	/** Bỏ các thiết lập cỡ chữ / giãn dòng riêng để dùng thang chữ chung. */
	private static function strip_font_size( $settings ) {
		foreach ( array_keys( $settings ) as $k ) {
			if ( preg_match( '/typography_(font_size|line_height)/', $k ) ) {
				unset( $settings[ $k ] );
			}
		}
		if ( isset( $settings['typography_typography'] ) && ! preg_grep( '/^typography_(?!typography)/', array_keys( $settings ) ) ) {
			unset( $settings['typography_typography'] );
		}
		return $settings;
	}

	private static function text( $html, $extra = array() ) {
		return self::w( 'text-editor', array_merge( array( 'editor' => $html ), self::strip_font_size( $extra ) ) );
	}

	private static function button( $text, $url, $extra = array() ) {
		return self::w(
			'button',
			array_merge(
				array(
					'text'             => $text,
					'link'             => array( 'url' => $url ),
					'align'            => 'center',
					'size'             => 'md',
					'background_color' => '#c02038',
					'button_text_color' => '#ffffff',
					'button_background_hover_color' => '#9e1a2e',
					'border_radius'    => array_merge( self::px( 8 ), array( 'isLinked' => true ) ),
					'typography_typography'  => 'custom',
					'typography_font_size'   => self::size( 16 ),
					'typography_font_weight' => '500',
					'text_padding'     => self::px( 12, 22 ),
				),
				$extra
			)
		);
	}

	/**
	 * Lưu icon nhiều màu thành file SVG trong thư viện media (để chọn/đổi trong Elementor).
	 *
	 * @param string $name Tên icon trong lp_color_icon_paths().
	 * @return array{value: array{id:int,url:string}, library: string}
	 */
	private static function svg_icon( $name ) {
		$map = get_option( 'lp_icon_attachments', array() );
		if ( empty( $map[ $name ] ) || 'attachment' !== get_post_type( $map[ $name ] ) || ! file_exists( get_attached_file( $map[ $name ] ) ) ) {
			$upload = wp_upload_dir();
			$dir    = trailingslashit( $upload['basedir'] ) . 'landpro-icons';
			wp_mkdir_p( $dir );
			$file = $dir . '/' . $name . '.svg';
			file_put_contents( $file, 0 === strpos( $name, 'svc-' ) ? lp_line_icon_file( $name ) : lp_color_icon_file( $name ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$id = wp_insert_attachment(
				array(
					'post_title'     => 'Icon ' . $name,
					'post_mime_type' => 'image/svg+xml',
					'post_status'    => 'inherit',
					'guid'           => trailingslashit( $upload['baseurl'] ) . 'landpro-icons/' . $name . '.svg',
				),
				$file
			);
			update_post_meta( $id, '_wp_attachment_image_alt', '' );
			$map[ $name ] = (int) $id;
			update_option( 'lp_icon_attachments', $map, false );
		}
		return array(
			'value'   => array(
				'id'  => (int) $map[ $name ],
				'url' => (string) wp_get_attachment_url( $map[ $name ] ),
			),
			'library' => 'svg',
		);
	}

	/** Khối nội dung có tiêu đề + mô tả + nội dung + nút "Xem tất cả". */
	private static function section( $title, $subtitle, $content, $more = null, $bg = '#ffffff', $eyebrow = '' ) {
		return self::box(
			array(
				'content_width'         => 'boxed',
				'flex_gap'              => array( 'unit' => 'px', 'size' => 28, 'column' => '28', 'row' => '28' ),
				'padding'               => self::px( 72, 16 ),
				'padding_mobile'        => self::px( 44, 16 ),
				'background_background' => 'classic',
				'background_color'      => $bg,
				'html_tag'              => 'section',
			),
			array(
				self::section_head( $title, $subtitle, $more, $eyebrow ),
				$content,
			)
		);
	}

	/** Đầu khối: nhãn nhỏ + tiêu đề + mô tả bên trái, link "Xem tất cả" bên phải. */
	private static function section_head( $title, $subtitle, $more = null, $eyebrow = '', $dark = false ) {
		$left = array();
		if ( $eyebrow ) {
			$left[] = self::eyebrow( $eyebrow );
		}
		$left[] = self::heading(
			$title,
			'h2',
			array(
				'align'                       => 'left',
				'title_color'                 => $dark ? '#ffffff' : '#0f1f3d',
				'typography_font_size'        => self::size( 34 ),
				'typography_font_size_mobile' => self::size( 26 ),
				'typography_line_height'      => array( 'unit' => 'em', 'size' => 1.2 ),
			)
		);
		if ( $subtitle ) {
			$left[] = self::text( '<p>' . esc_html( $subtitle ) . '</p>', array( 'text_color' => $dark ? '#cbd5e1' : '#6b7280', '_margin' => self::px( 0 ) ) );
		}
		$row = array(
			self::box(
				array(
					'width'        => array( 'unit' => '%', 'size' => 70 ),
					'width_mobile' => array( 'unit' => '%', 'size' => 100 ),
					'flex_gap'     => array( 'unit' => 'px', 'size' => 6, 'column' => '6', 'row' => '6' ),
					'padding'      => self::px( 0 ),
				),
				$left,
				true
			),
		);
		if ( $more ) {
			$row[] = self::button(
				$more[0],
				$more[1],
				array(
					'align'                         => 'right',
					'align_mobile'                  => 'left',
					'background_color'              => 'transparent',
					'button_text_color'             => '#c02038',
					'button_background_hover_color' => 'transparent',
					'hover_color'                   => '#9e1a2e',
					'selected_icon'                 => array( 'value' => 'fas fa-arrow-right', 'library' => 'fa-solid' ),
					'icon_align'                    => 'row-reverse',
					'icon_indent'                   => self::size( 8 ),
					'text_padding'                  => self::px( 8, 0 ),
					'typography_typography'         => 'custom',
					'typography_font_weight'        => '700',
				)
			);
		}
		return self::box(
			array(
				'flex_direction'        => 'row',
				'flex_direction_mobile' => 'column',
				'flex_justify_content'  => 'space-between',
				'flex_align_items'      => 'flex-end',
				'flex_align_items_mobile' => 'flex-start',
				'flex_gap'              => array( 'unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '12' ),
				'padding'               => self::px( 0 ),
			),
			$row,
			true
		);
	}

	/** Nhãn nhỏ phía trên tiêu đề. */
	private static function eyebrow( $text, $align = 'left' ) {
		return self::heading(
			$text,
			'div',
			array(
				'align'                          => $align,
				'title_color'                    => '#c02038',
				'typography_font_size'           => self::size( 13 ),
				'typography_font_size_mobile'    => self::size( 13 ),
				'typography_font_weight'         => '700',
				'typography_text_transform'      => 'uppercase',
				'typography_letter_spacing'      => self::size( 1.5 ),
			)
		);
	}

	/** Tiêu đề trang (banner nhỏ) cho các trang con. */
	private static function page_hero( $title, $subtitle, $image ) {
		return self::box(
			array(
				'content_width'                 => 'boxed',
				'flex_justify_content'          => 'center',
				'flex_align_items'              => 'flex-start',
				'flex_gap'                      => array( 'unit' => 'px', 'size' => 8, 'column' => '8', 'row' => '8' ),
				'min_height'                    => self::size( 340 ),
				'min_height_mobile'             => self::size( 220 ),
				'padding'                       => self::px( 48, 16 ),
				'padding_mobile'                => self::px( 32, 16 ),
				'background_background'         => 'classic',
				'background_image'              => array( 'id' => $image, 'url' => (string) wp_get_attachment_image_url( $image, 'full' ) ),
				'background_position'           => 'center center',
				'background_size'               => 'cover',
				'background_overlay_background' => 'classic',
				'background_overlay_color'      => '#000000',
				'background_overlay_opacity'    => self::size( 0.55 ),
				'html_tag'                      => 'header',
			),
			array(
				self::w( 'shortcode', array( 'shortcode' => '[lp_breadcrumb style="light"]' ) ),
				self::heading(
					$title,
					'h1',
					array(
						'align'                       => 'left',
						'title_color'                 => '#ffffff',
						'typography_font_size'        => self::size( 44 ),
						'typography_font_size_mobile' => self::size( 28 ),
						'typography_line_height'      => array( 'unit' => 'em', 'size' => 1.2 ),
					)
				),
				self::text( '<p>' . esc_html( $subtitle ) . '</p>', array( 'text_color' => '#e2e8f0', '_margin' => self::px( 0 ) ) ),
			)
		);
	}

	/* ================================================================ Trang */

	private static function save_elementor( $post_id, $elements, $type = 'wp-page' ) {
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements, JSON_UNESCAPED_UNICODE ) ) );
		if ( 'wp-page' === $type ) {
			update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
		}
		delete_post_meta( $post_id, '_elementor_css' );
		delete_post_meta( $post_id, '_elementor_element_cache' );
	}

	/** Tạo/cập nhật trang theo slug; nội dung văn bản thuần lưu vào post_content cho tìm kiếm & SEO. */
	private static function page( $slug, $title, $elements, $excerpt = '' ) {
		$id = self::upsert_post(
			'page',
			$slug,
			array(
				'post_title'   => $title,
				'post_content' => $excerpt ? '<p>' . esc_html( $excerpt ) . '</p>' : '',
				'post_excerpt' => $excerpt,
			)
		);
		if ( $elements ) {
			self::save_elementor( $id, $elements );
		}
		update_post_meta( $id, '_lp_demo', 1 );
		if ( $excerpt && defined( 'WPSEO_VERSION' ) ) {
			update_post_meta( $id, '_yoast_wpseo_metadesc', $excerpt );
		}
		return $id;
	}

	private static function pages( $data ) {
		$p    = array();
		$hero = self::img( 'home-hero', 688 );

		/* ----- Trang chủ */
		$home = array(
			// Slider đầu trang toàn màn hình (thêm/bớt slide ảnh hoặc video trong Elementor).
			self::box(
				array(
					'content_width' => 'full',
					'padding'       => self::px( 0 ),
					'html_tag'      => 'section',
				),
				array(
					self::w(
						'lp-hero',
						array(
							'slides'   => array_merge(
								// Slide 1: video (nếu đã nhập), ảnh poster hiện ngay khi tải trang.
								self::img( 'home-hero-video-1080' ) ? array(
									array(
										'_id'          => self::id(),
										'media_type'   => 'video',
										'image'        => array( 'id' => self::img( 'home-hero-video-poster' ), 'url' => (string) wp_get_attachment_image_url( self::img( 'home-hero-video-poster' ), 'full' ) ),
										'video'        => array( 'id' => self::img( 'home-hero-video-1080' ), 'url' => (string) wp_get_attachment_url( self::img( 'home-hero-video-1080' ) ) ),
										'video_mobile' => array( 'id' => self::img( 'home-hero-video-720' ), 'url' => (string) wp_get_attachment_url( self::img( 'home-hero-video-720' ) ) ),
										'alt'          => 'Toàn cảnh thành phố ven sông nhìn từ trên cao',
									),
								) : array(),
								array_map(
									function ( $slot ) {
										$id = self::img( $slot, 688 );
										return array(
											'_id'        => self::id(),
											'media_type' => 'image',
											'image'      => array( 'id' => $id, 'url' => (string) wp_get_attachment_image_url( $id, 'full' ) ),
											'alt'        => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
										);
									},
									self::img( 'home-hero-video-1080' ) ? array( 'banner-du-an', 'banner-tin-tuc' ) : array( 'home-hero', 'banner-du-an', 'banner-tin-tuc' )
								)
							),
							'autoplay' => 6,
							'title'    => 'Mua bán, cho thuê nhà đất',
							'subtitle' => 'Căn hộ, nhà phố, đất nền và dự án tại TP. Hồ Chí Minh, Hà Nội, Bình Dương. Tin đăng được kiểm tra pháp lý trước khi hiển thị.',
							'search'   => 'yes',
							'suggest_items' => array_map(
								function ( $t ) {
									return array( '_id' => self::id(), 'text' => $t );
								},
								array( 'Căn hộ Quận 7', 'Nhà phố Thủ Đức', 'Biệt thự ven sông', 'Đất nền Bình Dương', 'Căn hộ Vũng Tàu', 'Văn phòng Cầu Giấy' )
							),
							'suggest_label' => 'Gợi ý:',
						)
					),
				)
			),
			// Lối tắt theo loại hình.
			self::section(
				'Tìm theo loại hình',
				'',
				self::w( 'lp-types', array( 'columns' => 6, 'columns_tablet' => 3, 'columns_mobile' => 2 ) ),
				array( 'Tất cả nhà đất', home_url( '/nha-dat/' ) )
			),
			// Tiêu đề khối Dịch vụ.
			self::box(
				array(
					'content_width' => 'boxed',
					'padding'       => self::px( 8, 16, 36, 16 ),
				),
				array( self::section_head( 'Dịch vụ của LandPro', 'Đồng hành cùng bạn từ lúc tìm nhà đến khi nhận sổ' ) )
			),
			// Dịch vụ: icon nét cỡ lớn, căn giữa.
			self::box(
				array(
					'content_width'         => 'boxed',
					'flex_direction'        => 'row',
					'flex_wrap'             => 'wrap',
					'flex_gap'              => array( 'unit' => 'px', 'size' => 32, 'column' => '32', 'row' => '40' ),
					'padding'               => self::px( 16, 16, 72, 16 ),
					'padding_mobile'        => self::px( 8, 16, 48, 16 ),
					'html_tag'              => 'section',
					'css_classes'           => 'lp-services',
				),
				array_map(
					function ( $svc ) {
						return self::box(
							array(
								'width'            => array( 'unit' => '%', 'size' => 31 ),
								'width_tablet'     => array( 'unit' => '%', 'size' => 100 ),
								'flex_grow'        => 1,
								'flex_align_items' => 'center',
								'flex_gap'         => array( 'unit' => 'px', 'size' => 14, 'column' => '14', 'row' => '14' ),
								'padding'          => self::px( 0 ),
							),
							array(
								self::w(
									'icon-box',
									array(
										'selected_icon'      => self::svg_icon( $svc[0] ),
										'position'           => 'top',
										'text_align'         => 'center',
										'title_text'         => $svc[1],
										'description_text'   => $svc[2],
										'title_size'         => 'h3',
										'icon_size'          => self::size( 84 ),
										'icon_space'         => self::size( 18 ),
										'title_color'        => '#1f2937',
										'description_color'  => '#4b5563',
										'title_typography_typography'     => 'custom',
										'title_typography_font_weight'    => '700',
										'title_typography_text_transform' => 'uppercase',
										'title_bottom_space' => self::size( 10 ),
									)
								),
								self::button(
									'Tìm hiểu thêm',
									home_url( '/lien-he/' ),
									array(
										'background_color'              => '#ffffff',
										'button_text_color'             => '#111827',
										'border_border'                 => 'solid',
										'border_width'                  => array_merge( self::px( 1 ), array( 'isLinked' => true ) ),
										'border_color'                  => '#d0d5dd',
										'button_background_hover_color' => '#ffffff',
										'button_hover_border_color'     => '#c02038',
										'hover_color'                   => '#c02038',
										'text_padding'                  => self::px( 11, 24 ),
										'_css_classes'                  => 'lp-btn-outline',
									)
								),
							),
							true
						);
					},
					array(
						array( 'svc-consult', 'Tư vấn mua bán', 'Tìm đúng căn nhà theo nhu cầu và ngân sách, kiểm tra pháp lý, đàm phán giá và đồng hành đến khi nhận sổ.' ),
						array( 'svc-rent', 'Ký gửi & cho thuê', 'Nhận ký gửi bán, cho thuê nhà đất. Chụp ảnh, đăng tin, dẫn khách xem và soạn hợp đồng thuê minh bạch.' ),
						array( 'svc-loan', 'Hỗ trợ vay & pháp lý', 'Kết nối ngân hàng vay đến 70% giá trị, hỗ trợ thủ tục công chứng, sang tên và đóng thuế phí.' ),
					)
				)
			),
			self::section(
				'Dự án',
				'Dự án đang mở bán và sắp ra mắt tại các tỉnh, thành',
				self::w( 'lp-projects', array( 'per_page' => 8, 'columns' => 4, 'columns_tablet' => 3, 'columns_mobile' => 2, 'tabs' => 'yes' ) ),
				array( 'Xem tất cả dự án', home_url( '/du-an/' ) ),
				'#ffffff'
			),
			self::section(
				'Khám phá theo khu vực',
				'Tìm nhà đất tại những khu vực sôi động nhất',
				self::w( 'lp-locations', array( 'locations' => array( 'ho-chi-minh', 'ha-noi', 'quan-7', 'thu-duc', 'binh-duong', 'ba-ria-vung-tau', 'cau-giay' ), 'columns' => 4, 'columns_tablet' => 2, 'columns_mobile' => 2 ) ),
				array( 'Xem tất cả nhà đất', home_url( '/nha-dat/' ) ),
				'#f6f7f9'
			),
			self::section(
				'Nhà đất đang bán',
				'Căn hộ, nhà phố, biệt thự, đất nền mới cập nhật',
				self::w( 'lp-properties', array( 'purpose' => 'ban', 'per_page' => 8, 'columns' => 4, 'columns_tablet' => 3, 'columns_mobile' => 2 ) ),
				array( 'Xem tất cả nhà bán', home_url( '/mua-nha/' ) ),
				'#ffffff'
			),
			self::section(
				'Nhà đất cho thuê',
				'Căn hộ, văn phòng, mặt bằng kinh doanh giá tốt',
				self::w( 'lp-properties', array( 'purpose' => 'cho-thue', 'per_page' => 4, 'columns' => 4, 'columns_tablet' => 3, 'columns_mobile' => 2 ) ),
				array( 'Xem tất cả nhà cho thuê', home_url( '/thue-nha/' ) ),
				'#f6f7f9'
			),
			// Đăng ký tư vấn.
			self::box(
				array(
					'content_width'         => 'boxed',
					'flex_direction'        => 'row',
					'flex_direction_tablet' => 'column',
					'flex_align_items'      => 'center',
					'flex_gap'              => array( 'unit' => 'px', 'size' => 48, 'column' => '48', 'row' => '28' ),
					'padding'               => self::px( 80, 16 ),
					'padding_mobile'        => self::px( 48, 16 ),
					'background_background'         => 'classic',
					'background_image'              => array( 'id' => self::img( 'cta-tu-van', 567 ), 'url' => (string) wp_get_attachment_image_url( self::img( 'cta-tu-van', 567 ), 'full' ) ),
					'background_position'           => 'center center',
					'background_size'               => 'cover',
					'background_overlay_background' => 'classic',
					'background_overlay_color'      => '#000000',
					'background_overlay_opacity'    => self::size( 0.6 ),
					'html_tag'                      => 'section',
					'css_classes'                   => 'lp-cta',
				),
				array(
					self::box(
						array(
							'width'        => array( 'unit' => '%', 'size' => 50 ),
							'width_tablet' => array( 'unit' => '%', 'size' => 100 ),
							'flex_gap'     => array( 'unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '12' ),
							'padding'      => self::px( 0 ),
						),
						array(
							self::heading( 'Bạn cần mua, bán hay cho thuê nhà đất?', 'h2', array( 'align' => 'left', 'title_color' => '#ffffff', 'typography_font_size' => self::size( 36 ), 'typography_font_size_mobile' => self::size( 26 ), 'typography_line_height' => array( 'unit' => 'em', 'size' => 1.2 ) ) ),
							self::text( '<p>Để lại thông tin, chuyên viên LandPro sẽ gọi lại trong 15 phút để định giá, kiểm tra pháp lý, lên kế hoạch tài chính và hẹn lịch xem nhà.</p>', array( 'text_color' => '#e5e7eb', '_css_classes' => 'lp-lead' ) ),
							self::w(
								'icon-list',
								array(
									'icon_list'     => array(
										array( '_id' => self::id(), 'text' => 'Miễn phí tư vấn và dẫn xem nhà', 'selected_icon' => array( 'value' => 'fas fa-check-circle', 'library' => 'fa-solid' ) ),
										array( '_id' => self::id(), 'text' => 'Bảo mật thông tin khách hàng', 'selected_icon' => array( 'value' => 'fas fa-check-circle', 'library' => 'fa-solid' ) ),
										array( '_id' => self::id(), 'text' => 'Hỗ trợ thủ tục pháp lý trọn gói', 'selected_icon' => array( 'value' => 'fas fa-check-circle', 'library' => 'fa-solid' ) ),
									),
									'icon_color'    => '#ffffff',
									'text_color'    => '#ffffff',
									'space_between' => self::size( 12 ),
								)
							),
						),
						true
					),
					self::box(
						array(
							'width'                 => array( 'unit' => '%', 'size' => 50 ),
							'width_tablet'          => array( 'unit' => '%', 'size' => 100 ),
							'padding'               => self::px( 32 ),
							'padding_mobile'        => self::px( 20 ),
							'background_background' => 'classic',
							'background_color'      => '#ffffff',
							'border_radius'         => array_merge( self::px( 16 ), array( 'isLinked' => true ) ),
							'box_shadow_box_shadow_type' => 'yes',
							'box_shadow_box_shadow' => array( 'horizontal' => 0, 'vertical' => 20, 'blur' => 50, 'spread' => 0, 'color' => 'rgba(0,0,0,0.25)' ),
						),
						array( self::w( 'shortcode', array( 'shortcode' => '[lp_contact_form]' ) ) ),
						true
					),
				)
			),
		);
		$p['home'] = self::page( 'trang-chu', 'Trang chủ', $home, 'LandPro - dịch vụ bất động sản chuyên nghiệp: mua bán, cho thuê nhà đất, căn hộ, biệt thự, đất nền và dự án mới với thông tin xác thực, pháp lý minh bạch.' );

		/* ----- Mua nhà / Thuê nhà */
		$listing = function ( $purpose, $title, $sub, $img ) {
			return array(
				self::page_hero( $title, $sub, $img ),
				self::box(
					array(
						'content_width' => 'boxed',
						'padding'       => self::px( 32, 16, 64, 16 ),
						'flex_gap'      => array( 'unit' => 'px', 'size' => 8, 'column' => '8', 'row' => '8' ),
					),
					array(
						self::w(
							'lp-search',
							array(
								'tabs'        => '',
								'purpose'     => $purpose,
								'fields'      => array( 'kw', 'type', 'location', 'price', 'area', 'bed' ),
								'action_type' => 'current',
								'button_text' => 'Lọc kết quả',
							)
						),
						self::w(
							'lp-map',
							array(
								'purpose'     => $purpose,
								'use_filters' => 'yes',
								'toggle'      => 'yes',
							)
						),
						self::w(
							'lp-properties',
							array(
								'purpose'        => $purpose,
								'per_page'       => 12,
								'columns'        => 4,
								'columns_tablet' => 3,
								'columns_mobile' => 2,
								'pagination'     => 'yes',
								'use_filters'    => 'yes',
								'show_toolbar'   => 'yes',
								'empty_text'     => 'Không tìm thấy bất động sản phù hợp. Hãy thử bỏ bớt điều kiện lọc.',
							)
						),
					)
				),
			);
		};
		$p['buy']  = self::page( 'mua-nha', 'Mua nhà', $listing( 'ban', 'Mua bán nhà đất', 'Căn hộ, nhà phố, biệt thự, đất nền chính chủ, pháp lý rõ ràng', self::img( 'banner-mua-nha', 565 ) ), 'Mua bán nhà đất, căn hộ chung cư, nhà phố, biệt thự, đất nền tại TP. Hồ Chí Minh, Hà Nội, Bình Dương. Lọc theo khu vực, giá, diện tích, số phòng ngủ.' );
		$p['rent'] = self::page( 'thue-nha', 'Thuê nhà', $listing( 'cho-thue', 'Cho thuê nhà đất', 'Căn hộ, nhà nguyên căn, văn phòng, mặt bằng kinh doanh', self::img( 'banner-thue-nha', 687 ) ), 'Cho thuê căn hộ, nhà nguyên căn, văn phòng và mặt bằng kinh doanh. Lọc theo khu vực, mức giá, diện tích, số phòng ngủ.' );

		/* ----- Dự án */
		$p['projects'] = self::page(
			'du-an',
			'Dự án',
			array(
				self::page_hero( 'Dự án bất động sản', 'Thông tin, bảng giá và tiến độ các dự án căn hộ, biệt thự, đất nền', self::img( 'banner-du-an', 688 ) ),
				self::box(
					array(
						'content_width' => 'boxed',
						'padding'       => self::px( 40, 16, 64, 16 ),
					),
					array( self::w( 'lp-projects', array( 'per_page' => 12, 'columns' => 4, 'columns_tablet' => 3, 'columns_mobile' => 2, 'pagination' => 'yes', 'tabs' => 'yes' ) ) )
				),
			),
			'Danh sách dự án bất động sản đang mở bán, sắp mở bán và đã bàn giao: thông tin chủ đầu tư, vị trí, giá bán, tiện ích và tiến độ.'
		);

		/* ----- Ký gửi nhà đất */
		$p['consign'] = self::page(
			'ky-gui-nha-dat',
			'Ký gửi nhà đất',
			array(
				self::page_hero( 'Ký gửi nhà đất', 'Bán nhanh, cho thuê nhanh cùng đội ngũ chuyên viên LandPro – đăng ký miễn phí', self::img( 'banner-lien-he', 688 ) ),
				self::box(
					array(
						'content_width' => 'boxed',
						'padding'       => self::px( 56, 16, 72, 16 ),
					),
					array( self::w( 'shortcode', array( 'shortcode' => '[lp_consign]' ) ) )
				),
			),
			'Ký gửi bán, cho thuê nhà đất miễn phí: gửi thông tin và hình ảnh, chuyên viên thẩm định, định giá và đăng tin trong 24 giờ làm việc.'
		);

		/* ----- Liên hệ */
		$hotline = lp_option( 'hotline', '0898 614 962' );
		$email   = lp_option( 'email', get_option( 'admin_email' ) );
		$address = lp_option( 'address', '' );
		$p['contact'] = self::page(
			'lien-he',
			'Liên hệ',
			array(
				self::page_hero( 'Liên hệ', 'Chúng tôi luôn sẵn sàng hỗ trợ bạn', self::img( 'banner-lien-he', 688 ) ),
				self::box(
					array(
						'content_width'         => 'boxed',
						'flex_direction'        => 'row',
						'flex_direction_tablet' => 'column',
						'flex_gap'              => array( 'unit' => 'px', 'size' => 40, 'column' => '40', 'row' => '32' ),
						'padding'               => self::px( 56, 16 ),
					),
					array(
						self::box(
							array(
								'width'        => array( 'unit' => '%', 'size' => 40 ),
								'width_tablet' => array( 'unit' => '%', 'size' => 100 ),
								'padding'      => self::px( 0 ),
							),
							array(
								self::heading( 'Thông tin liên hệ', 'h2', array( 'align' => 'left' ) ),
								self::text( '<p>Văn phòng làm việc từ 8:00 đến 20:00, tất cả các ngày trong tuần.</p>' ),
								self::w(
									'icon-list',
									array(
										'icon_list'  => array(
											array( '_id' => self::id(), 'text' => $hotline, 'selected_icon' => self::svg_icon( 'phone' ), 'link' => array( 'url' => 'tel:' . lp_phone_digits( $hotline ) ) ),
											array( '_id' => self::id(), 'text' => $email, 'selected_icon' => self::svg_icon( 'mail' ), 'link' => array( 'url' => 'mailto:' . $email ) ),
											array( '_id' => self::id(), 'text' => $address, 'selected_icon' => self::svg_icon( 'pin' ) ),
										),
										'icon_size'     => self::size( 40 ),
										'space_between' => self::size( 16 ),
										'text_indent'   => self::size( 12 ),
										'icon_typography_typography' => 'custom',
									)
								),
							),
							true
						),
						self::box(
							array(
								'width'                 => array( 'unit' => '%', 'size' => 60 ),
								'width_tablet'          => array( 'unit' => '%', 'size' => 100 ),
								'padding'               => self::px( 28 ),
								'padding_mobile'        => self::px( 18 ),
								'background_background' => 'classic',
								'background_color'      => '#f6f7f9',
								'border_radius'         => array_merge( self::px( 12 ), array( 'isLinked' => true ) ),
							),
							array(
								self::heading( 'Gửi yêu cầu tư vấn', 'h3', array( 'align' => 'left' ) ),
								self::w( 'shortcode', array( 'shortcode' => '[lp_contact_form]' ) ),
							),
							true
						),
					)
				),
				self::box(
					array(
						'content_width' => 'full',
						'padding'       => self::px( 0 ),
					),
					array(
						self::w(
							'google_maps',
							array(
								'address' => $address ? $address : 'Quận 7, TP. Hồ Chí Minh',
								'zoom'    => self::size( 15 ),
								'height'  => self::size( 400 ),
							)
						),
					)
				),
			),
			'Liên hệ LandPro để được tư vấn mua bán, cho thuê nhà đất và dự án. Hotline ' . $hotline . '.'
		);

		/* ----- Công cụ phong thủy (giữ trang cũ, chuyển sang Elementor) */
		$tool = function ( $title, $sub, $shortcodes ) {
			$cards = array();
			foreach ( $shortcodes as $sc ) {
				$inner = array();
				if ( $sc[0] ) {
					$inner[] = self::w(
						'icon-box',
						array(
							'selected_icon'     => self::svg_icon( $sc[2] ),
							'position'          => 'left',
							'title_text'        => $sc[0],
							'description_text'  => $sc[3],
							'title_size'        => 'h2',
							'icon_size'         => self::size( 48 ),
							'icon_space'        => self::size( 16 ),
							'title_color'       => '#111827',
							'description_color' => '#6b7280',
							'title_bottom_space' => self::size( 4 ),
							'_css_classes'      => 'lp-tool-head',
						)
					);
				}
				$inner[] = self::w( 'shortcode', array( 'shortcode' => $sc[1] ) );
				$cards[] = self::box(
					array(
						'width'                 => array( 'unit' => '%', 'size' => count( $shortcodes ) > 1 ? 50 : 100 ),
						'width_tablet'          => array( 'unit' => '%', 'size' => 100 ),
						'flex_grow'             => 1,
						'flex_gap'              => array( 'unit' => 'px', 'size' => 20, 'column' => '20', 'row' => '20' ),
						'padding'               => self::px( 28 ),
						'padding_mobile'        => self::px( 16 ),
						'background_background' => 'classic',
						'background_color'      => '#ffffff',
						'border_border'         => 'solid',
						'border_width'          => array_merge( self::px( 1 ), array( 'isLinked' => true ) ),
						'border_color'          => '#e5e7eb',
						'border_radius'         => array_merge( self::px( 14 ), array( 'isLinked' => true ) ),
						'css_classes'           => 'lp-tool-card',
					),
					$inner,
					true
				);
			}
			return array(
				self::page_hero( $title, $sub, self::img( 'banner-cong-cu', 688 ) ),
				self::box(
					array(
						'content_width'         => 'boxed',
						'flex_direction'        => 'row',
						'flex_direction_tablet' => 'column',
						'flex_align_items'      => 'stretch',
						'flex_gap'              => array( 'unit' => 'px', 'size' => 24, 'column' => '24', 'row' => '24' ),
						'padding'               => self::px( 48, 16, 72, 16 ),
						'background_background' => 'classic',
						'background_color'      => '#f4f5f7',
					),
					$cards
				),
			);
		};
		$p['loban'] = self::page( 'thuoc-lo-ban', 'Thước lỗ ban', $tool( 'Thước lỗ ban', 'Tra cứu kích thước đẹp theo thước lỗ ban 52,2 cm, 42,9 cm và 38,8 cm', array( array( '', '[wp_thuocloban]', '', '' ) ) ), 'Thước lỗ ban online: tra cứu kích thước cửa, bếp, bàn thờ theo thước 52,2 cm, 42,9 cm và 38,8 cm.' );
		$p['tuoi']  = self::page( 'xem-tuoi-xay-dung-huong-nha', 'Xem tuổi xây dựng - Hướng nhà', $tool( 'Xem tuổi xây dựng - Hướng nhà', 'Công cụ phong thủy hỗ trợ chọn năm làm nhà và hướng nhà hợp tuổi', array( array( 'Xem hướng nhà', '[wp_xemhuongnha]', 'compass', 'Chọn năm sinh, giới tính và hướng nhà để xem hướng tốt, xấu.' ), array( 'Xem tuổi xây dựng', '[wp_tuoixaydung]', 'calendar', 'Kiểm tra năm dự định xây có phạm Kim Lâu, Hoang Ốc, Tam Tai không.' ) ) ), 'Xem tuổi làm nhà và hướng nhà hợp tuổi theo năm sinh, giới tính. Công cụ phong thủy miễn phí.' );

		/* ----- Tin tức (trang bài viết của WordPress) */
		$p['news'] = self::page( 'tin-tuc', 'Tin tức', array(), 'Tin tức thị trường bất động sản, kinh nghiệm mua bán, cho thuê và đầu tư nhà đất.' );
		delete_post_meta( $p['news'], '_elementor_edit_mode' );

		self::log( 'Đã tạo các trang: Trang chủ, Mua nhà, Thuê nhà, Dự án, Liên hệ, Thước lỗ ban, Xem tuổi, Tin tức.' );
		return $p;
	}

	/* ================================================================ Menu */

	private static function nav_menu( $pages ) {
		$name = 'Menu chính';
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = (int) wp_create_nav_menu( $name );
		}
		$add = function ( $page_id, $parent = 0, $title = '' ) use ( $menu_id ) {
			return wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $page_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent,
					'menu-item-title'     => $title,
				)
			);
		};
		$add( $pages['home'] );
		$add( $pages['buy'] );
		$add( $pages['rent'] );
		$add( $pages['projects'] );
		$tools = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Công cụ',
				'menu-item-url'    => get_permalink( $pages['loban'] ),
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			)
		);
		$add( $pages['loban'], $tools );
		$add( $pages['tuoi'], $tools, 'Xem tuổi - Hướng nhà' );
		$add( $pages['news'] );
		$add( $pages['contact'] );

		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['menu-1'] = $menu_id; // Vị trí menu của Hello Elementor.
		set_theme_mod( 'nav_menu_locations', $locations );
		self::log( 'Đã tạo menu chính.' );
		return wp_get_nav_menu_object( $menu_id );
	}

	/* ================================================================ Header / Footer */

	private static function hf_template( $slug, $title, $type, $elements ) {
		$id = self::upsert_post( 'elementor-hf', $slug, array( 'post_title' => $title ) );
		self::save_elementor( $id, $elements, 'wp-post' );
		update_post_meta( $id, 'ehf_template_type', $type );
		update_post_meta(
			$id,
			'ehf_target_include_locations',
			array(
				'rule'     => array( 'basic-global' ),
				'specific' => array(),
			)
		);
		update_post_meta( $id, 'ehf_target_exclude_locations', array() );
		update_post_meta( $id, 'ehf_target_user_roles', array( 'all' ) );
		return $id;
	}

	private static function header_footer( $menu ) {
		if ( ! post_type_exists( 'elementor-hf' ) ) {
			self::log( 'Bỏ qua header/footer: chưa bật Elementor Header & Footer Builder.' );
			return;
		}
		$hotline = lp_option( 'hotline', '0898 614 962' );
		$email   = lp_option( 'email', get_option( 'admin_email' ) );

		$logo_widget = function ( $width ) {
			return self::w(
				'site-logo',
				array(
					'site_logo_fallback' => 'no',
					'site_logo_size_size' => 'full',
					'align'              => 'left',
					'width'              => array( 'unit' => 'px', 'size' => $width ),
					'width_mobile'       => array( 'unit' => 'px', 'size' => 140 ),
					'space'              => array( 'unit' => '%', 'size' => 100 ),
					'link_to'            => 'default',
					'_flex_size'         => 'none',
				)
			);
		};

		/* ----- Header: thanh trên + thanh chính */
		$inline_list = function ( $items, $align = 'left' ) {
			return self::w(
				'icon-list',
				array(
					'view'          => 'inline',
					'icon_align'    => $align,
					'icon_list'     => array_map(
						function ( $it ) {
							$row = array(
								'_id'           => self::id(),
								'text'          => $it[0],
								'selected_icon' => array( 'value' => $it[2], 'library' => 'fa-solid' ),
							);
							if ( $it[1] ) {
								$row['link'] = array( 'url' => $it[1] );
							}
							return $row;
						},
						$items
					),
					'icon_color'    => '#9ca3af',
					'text_color'    => '#e2e8f0',
					'text_color_hover' => '#ffffff',
					'space_between' => self::size( 24 ),
					'icon_typography_typography' => 'custom',
					'icon_typography_font_size'  => self::size( 13 ),
					'_flex_size'    => 'none',
				)
			);
		};
		$header = array(
			self::box(
				array(
					'content_width'         => 'boxed',
					'flex_direction'        => 'row',
					'flex_justify_content'  => 'space-between',
					'flex_align_items'      => 'center',
					'padding'               => self::px( 6, 16 ),
					'background_background' => 'classic',
					'background_color'      => '#111111',
					'hide_mobile'           => 'hidden-mobile',
					'css_classes'           => 'lp-topbar',
					'html_tag'              => 'div',
				),
				array(
					$inline_list(
						array(
							array( $hotline, 'tel:' . lp_phone_digits( $hotline ), 'fas fa-phone-alt' ),
							array( $email, 'mailto:' . $email, 'fas fa-envelope' ),
						)
					),
					self::box(
						array(
							'flex_direction'       => 'row',
							'flex_align_items'     => 'center',
							'flex_justify_content' => 'flex-end',
							'flex_gap'             => array( 'unit' => 'px', 'size' => 20, 'column' => '20', 'row' => '8' ),
							'padding'              => self::px( 0 ),
							'css_classes'          => 'lp-topbar__right',
						),
						array(
							$inline_list( array( array( 'Thứ 2 - Chủ nhật: 8:00 - 20:00', '', 'fas fa-clock' ) ) ),
							self::w( 'shortcode', array( 'shortcode' => '[lp_languages]', '_flex_size' => 'none' ) ),
						),
						true
					),
				)
			),
			self::box(
				array(
					'content_width'         => 'boxed',
					'flex_direction'        => 'row',
					'flex_align_items'      => 'center',
					'flex_justify_content'  => 'space-between',
					'flex_wrap'             => 'nowrap',
					'flex_gap'              => array( 'unit' => 'px', 'size' => 20, 'column' => '20', 'row' => '20' ),
					'padding'               => self::px( 0, 16 ),
					'min_height'            => self::size( 80 ),
					'min_height_mobile'     => self::size( 64 ),
					'background_background' => 'classic',
					'background_color'      => '#ffffff',
					'border_border'         => 'solid',
					'border_width'          => self::px( 0, 0, 1, 0 ),
					'border_color'          => '#eef0f3',
					'css_classes'           => 'lp-site-header',
					'html_tag'              => 'div',
				),
				array(
					$logo_widget( 170 ),
					self::w(
						'navigation-menu',
						array(
							'menu'                           => $menu ? $menu->slug : '',
							'layout'                         => 'horizontal',
							'navmenu_align'                  => 'right',
							'dropdown'                       => 'tablet',
							'resp_align'                     => 'left',
							'full_width_dropdown'            => 'yes',
							'pointer'                        => 'underline',
							'color_menu_item'                => '#1f2937',
							'color_menu_item_hover'          => '#c02038',
							'pointer_color_menu_item_hover'  => '#c02038',
							'color_menu_item_active'         => '#c02038',
							'pointer_color_menu_item_active' => '#c02038',
							'padding_horizontal_menu_item'   => self::size( 11 ),
							'padding_vertical_menu_item'     => self::size( 30 ),
							'toggle_color'                   => '#0f1f3d',
							'menu_typography_typography'     => 'custom',
							'menu_typography_font_weight'    => '600',
							'menu_typography_font_size'      => self::size( 16 ),
							'_flex_size'                     => 'grow',
						)
					),
					self::button(
						'Ký gửi nhà đất',
						home_url( '/ky-gui-nha-dat/' ),
						array(
							'size'          => 'sm',
							'text_padding'  => self::px( 11, 20 ),
							'hide_mobile'   => 'hidden-mobile',
							'hide_tablet'   => 'hidden-tablet',
							'_flex_size'    => 'none',
						)
					),
				)
			),
		);
		self::hf_template( 'landpro-header', 'LandPro - Header', 'type_header', $header );

		/* ----- Footer */
		$link_list = function ( $items ) {
			return self::w(
				'icon-list',
				array(
					'icon_list'     => array_map(
						function ( $it ) {
							return array(
								'_id'           => self::id(),
								'text'          => $it[0],
								'selected_icon' => array( 'value' => isset( $it[2] ) ? $it[2] : 'fas fa-angle-right', 'library' => 'fa-solid' ),
								'link'          => array( 'url' => $it[1] ),
							);
						},
						$items
					),
					'icon_color'    => '#c02038',
					'text_color'    => '#d1d5db',
					'text_color_hover' => '#ffffff',
					'space_between' => self::size( 10 ),
				)
			);
		};
		$col = function ( $children, $width = 25 ) {
			return self::box(
				array(
					'width'        => array( 'unit' => '%', 'size' => $width ),
					'width_tablet' => array( 'unit' => '%', 'size' => 47 ),
					'width_mobile' => array( 'unit' => '%', 'size' => 100 ),
					'flex_gap'     => array( 'unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '12' ),
				),
				$children,
				true
			);
		};
		$fh = function ( $text ) {
			return self::heading( $text, 'h2', array( 'align' => 'left', 'title_color' => '#ffffff', '_css_classes' => 'lp-footer-title' ) );
		};

		$footer = array(
			self::box(
				array(
					'content_width'         => 'boxed',
					'flex_direction'        => 'row',
					'flex_wrap'             => 'wrap',
					'flex_gap'              => array( 'unit' => 'px', 'size' => 32, 'column' => '32', 'row' => '32' ),
					'padding'               => self::px( 56, 16, 32, 16 ),
					'background_background' => 'classic',
					'background_color'      => '#111111',
					'html_tag'              => 'div',
				),
				array(
					$col(
						array(
							self::box(
								array(
									'padding'     => self::px( 0 ),
									'width'       => array( 'unit' => 'px', 'size' => 210 ),
									'css_classes' => 'lp-footer-logo',
								),
								array( $logo_widget( 200 ) ),
								true
							),
							self::text( '<p>LandPro cung cấp dịch vụ môi giới, tư vấn và quản lý bất động sản chuyên nghiệp: thông tin xác thực, pháp lý minh bạch, đồng hành cùng khách hàng trong từng giao dịch.</p>', array( 'text_color' => '#9ca3af' ) ),
						),
						28
					),
					$col(
						array(
							$fh( 'Nhà đất' ),
							$link_list(
								array(
									array( 'Mua nhà', home_url( '/mua-nha/' ) ),
									array( 'Thuê nhà', home_url( '/thue-nha/' ) ),
									array( 'Dự án', home_url( '/du-an/' ) ),
									array( 'Tất cả nhà đất', home_url( '/nha-dat/' ) ),
								)
							),
						),
						18
					),
					$col(
						array(
							$fh( 'Hỗ trợ' ),
							$link_list(
								array(
									array( 'Thước lỗ ban', home_url( '/thuoc-lo-ban/' ) ),
									array( 'Xem tuổi - Hướng nhà', home_url( '/xem-tuoi-xay-dung-huong-nha/' ) ),
									array( 'Tin tức', home_url( '/tin-tuc/' ) ),
									array( 'Liên hệ', home_url( '/lien-he/' ) ),
								)
							),
						),
						18
					),
					$col(
						array(
							$fh( 'Liên hệ' ),
							$link_list(
								array(
									array( $hotline, 'tel:' . lp_phone_digits( $hotline ), 'fas fa-phone-alt' ),
									array( $email, 'mailto:' . $email, 'fas fa-envelope' ),
									array( lp_option( 'address', '' ), home_url( '/lien-he/' ), 'fas fa-map-marker-alt' ),
								)
							),
						),
						28
					),
				)
			),
			self::box(
				array(
					'content_width'         => 'boxed',
					'padding'               => self::px( 16 ),
					'background_background' => 'classic',
					'background_color'      => '#0a0a0a',
					'html_tag'              => 'div',
				),
				array(
					self::w(
						'copyright',
						array(
							'shortcode'   => 'Copyright © [hfe_current_year] LandPro. Bảo lưu mọi quyền.',
							'align'       => 'center',
							'title_color' => '#9ca3af',
						)
					),
				)
			),
		);
		self::hf_template( 'landpro-footer', 'LandPro - Footer', 'type_footer', $footer );
		self::log( 'Đã tạo header và footer.' );
	}

	/* ================================================================ Kit Elementor (màu, font) */

	private static function kit() {
		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) {
			return;
		}
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		$settings = is_array( $settings ) ? $settings : array();

		$settings['system_colors'] = array(
			array( '_id' => 'primary', 'title' => 'Chính', 'color' => '#C02038' ),
			array( '_id' => 'secondary', 'title' => 'Phụ', 'color' => '#111111' ),
			array( '_id' => 'text', 'title' => 'Chữ', 'color' => '#2B2F36' ),
			array( '_id' => 'accent', 'title' => 'Nhấn', 'color' => '#B45309' ),
		);
		$font = 'Be Vietnam Pro';
		$settings['system_typography'] = array(
			array( '_id' => 'primary', 'title' => 'Tiêu đề', 'typography_typography' => 'custom', 'typography_font_family' => $font, 'typography_font_weight' => '700' ),
			array( '_id' => 'secondary', 'title' => 'Tiêu đề phụ', 'typography_typography' => 'custom', 'typography_font_family' => $font, 'typography_font_weight' => '600' ),
			array( '_id' => 'text', 'title' => 'Nội dung', 'typography_typography' => 'custom', 'typography_font_family' => $font, 'typography_font_weight' => '400' ),
			array( '_id' => 'accent', 'title' => 'Nhấn', 'typography_typography' => 'custom', 'typography_font_family' => $font, 'typography_font_weight' => '600' ),
		);
		$settings['body_typography_typography']  = 'custom';
		$settings['body_typography_font_family'] = $font;
		$settings['body_color']                  = '#2B2F36';
		$settings['h1_typography_typography']    = 'custom';
		$settings['h1_typography_font_family']   = $font;
		$settings['h2_typography_typography']    = 'custom';
		$settings['h2_typography_font_family']   = $font;
		$settings['h3_typography_typography']    = 'custom';
		$settings['h3_typography_font_family']   = $font;
		$settings['link_normal_color']           = '#C02038';
		$settings['container_width']             = array( 'unit' => 'px', 'size' => 1200, 'sizes' => array() );
		$settings['site_name']                   = 'LandPro';
		$settings['site_description']            = 'Dịch vụ bất động sản chuyên nghiệp';
		update_post_meta( $kit_id, '_elementor_page_settings', $settings );
		self::log( 'Đã cấu hình màu sắc, font chữ toàn site trong Elementor.' );
	}

	/* ================================================================ Tùy chọn site */

	private static function site_options( $pages ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		update_option( 'page_for_posts', $pages['news'] );
		update_option( 'blogname', 'LandPro' );
		update_option( 'blogdescription', 'Dịch vụ bất động sản chuyên nghiệp' );
		// Hello Elementor: tắt header/footer mặc định của theme (dùng header/footer Elementor).
		update_option( 'hello_elementor_settings_header_footer', 'false' );
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		update_option( 'elementor_load_fa4_shim', '' );
		update_option( 'elementor_unfiltered_files_upload', '1' );
		self::log( 'Đã đặt Trang chủ, trang Tin tức và tên website.' );
	}
}
