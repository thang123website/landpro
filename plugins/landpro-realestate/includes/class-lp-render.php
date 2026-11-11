<?php
/**
 * Hàm dựng HTML: lưới nhà đất, lưới dự án, form tìm kiếm, phân trang.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Render {

	/** Nạp CSS/JS giao diện (chỉ khi cần). */
	public static function enqueue() {
		wp_enqueue_style( 'lp-frontend' );
		wp_enqueue_script( 'lp-frontend' );
	}

	/**
	 * Lưới nhà đất.
	 *
	 * @param array $atts {
	 *   purpose, type, location, project, featured, per_page, columns, columns_tablet, columns_mobile,
	 *   orderby, pagination (yes/no), use_filters (yes/no: đọc bộ lọc từ URL), show_toolbar (yes/no),
	 *   empty_text, exclude
	 * }
	 * @return string
	 */
	public static function properties( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'purpose'        => '',
				'type'           => '',
				'location'       => '',
				'project'        => '',
				'featured'       => '',
				'per_page'       => 8,
				'columns'        => 4,
				'columns_tablet' => 3,
				'columns_mobile' => 2,
				'orderby'        => 'newest',
				'pagination'     => 'no',
				'use_filters'    => 'no',
				'show_toolbar'   => 'no',
				'empty_text'     => 'Chưa có tin nhà đất phù hợp.',
				'exclude'        => array(),
			)
		);
		self::enqueue();

		$filters = 'yes' === $atts['use_filters'] ? LP_Query::request_filters() : array();
		$args    = LP_Query::property_args( $atts, $filters );
		$paged   = 'yes' === $atts['pagination'] ? self::current_page() : 1;
		if ( $paged > 1 ) {
			$args['paged'] = $paged;
		}
		$query = new WP_Query( $args );

		ob_start();
		echo '<div class="lp-listing">';
		if ( 'yes' === $atts['show_toolbar'] ) {
			self::toolbar( $query->found_posts, $filters );
		}
		if ( $query->have_posts() ) {
			echo '<div class="lp-grid" style="' . esc_attr( self::grid_style( $atts ) ) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				lp_template( 'parts/card-property.php', array( 'post_id' => get_the_ID() ) );
			}
			echo '</div>';
			if ( 'yes' === $atts['pagination'] ) {
				self::pagination( $query->max_num_pages, $paged );
			}
		} else {
			echo '<p class="lp-empty">' . esc_html( lp_t( $atts['empty_text'] ) ) . '</p>';
		}
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Lưới dự án.
	 *
	 * @param array $atts {type, location, status, featured, per_page, columns, columns_tablet, columns_mobile, pagination, orderby, exclude}.
	 * @return string
	 */
	public static function projects( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'type'           => '',
				'location'       => '',
				'status'         => '',
				'featured'       => '',
				'per_page'       => 6,
				'columns'        => 4,
				'columns_tablet' => 3,
				'columns_mobile' => 2,
				'orderby'        => 'date',
				'pagination'     => 'no',
				'tabs'           => 'no',
				'slider'         => 'no', // yes | auto (slider khi nhiều hơn số cột desktop).
				'empty_text'     => 'Chưa có dự án phù hợp.',
				'exclude'        => array(),
			)
		);
		self::enqueue();

		$args  = LP_Query::project_args( $atts );
		$paged = 'yes' === $atts['pagination'] ? self::current_page() : 1;
		if ( $paged > 1 ) {
			$args['paged'] = $paged;
		}
		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="lp-projects" data-lp-tabs>';
			if ( 'yes' === $atts['tabs'] ) {
				self::location_tabs( wp_list_pluck( $query->posts, 'ID' ) );
			}
			$slider = 'yes' === $atts['slider'] || ( 'auto' === $atts['slider'] && $query->post_count > (int) $atts['columns'] );
			if ( $slider ) {
				printf(
					'<div class="lp-slider" data-lp-slider><div class="lp-slider__nav"><button type="button" class="lp-slider__btn" data-dir="-1" aria-label="%1$s"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg></button><button type="button" class="lp-slider__btn" data-dir="1" aria-label="%2$s"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg></button></div>',
					esc_attr( lp_t( 'Slide trước' ) ),
					esc_attr( lp_t( 'Slide sau' ) )
				);
			}
			echo '<div class="lp-grid lp-grid--projects' . ( $slider ? ' lp-slider__track' : '' ) . '" style="' . esc_attr( self::grid_style( $atts ) ) . '">';
			while ( $query->have_posts() ) {
				$query->the_post();
				lp_template( 'parts/card-project.php', array( 'post_id' => get_the_ID() ) );
			}
			echo '</div>' . ( $slider ? '</div>' : '' ) . '</div>';
			if ( 'yes' === $atts['pagination'] ) {
				self::pagination( $query->max_num_pages, $paged );
			}
		} else {
			echo '<p class="lp-empty">' . esc_html( lp_t( $atts['empty_text'] ) ) . '</p>';
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Form tìm kiếm.
	 *
	 * @param array $atts {
	 *   action: URL trang kết quả ('' = trang hiện tại nếu use_tabs=no, ngược lại trang lưu trữ),
	 *   purpose: cố định nhu cầu (ban|cho-thue|''), tabs (yes/no: hiện tab Mua/Thuê),
	 *   fields: danh sách trường "kw,type,location,price,area,bed", layout (bar|stacked), button_text
	 * }
	 * @return string
	 */
	public static function search_form( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'action'      => '',
				'purpose'     => '',
				'tabs'        => 'no',
				'fields'      => 'kw,type,location,price',
				'layout'      => 'bar',
				'button_text' => 'Tìm kiếm', // Dịch qua lp_t() khi hiển thị.
				'suggest'       => '', // Gợi ý từ khóa, mỗi dòng một mục.
				'suggest_label' => 'Gợi ý:',
			)
		);
		$atts['suggest'] = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $atts['suggest'] ) ) ) );
		self::enqueue();
		$atts['fields'] = array_filter( array_map( 'trim', explode( ',', $atts['fields'] ) ) );
		$atts['values'] = LP_Query::request_filters();
		if ( '' === $atts['action'] ) {
			$atts['action'] = 'yes' === $atts['tabs'] ? get_post_type_archive_link( 'property' ) : self::current_url();
		}
		// Form lọc ngay trên trang kết quả: áp dụng tức thì (không tải lại trang) + nút "Xóa lọc".
		$atts['live'] = untrailingslashit( strtok( $atts['action'], '?' ) ) === untrailingslashit( self::current_url() );
		ob_start();
		lp_template( 'parts/search-form.php', $atts );
		return ob_get_clean();
	}

	/**
	 * Lưới khu vực: ô ảnh nền + tên + số tin, dẫn tới trang khu vực.
	 *
	 * @param array $atts { locations: slug cách nhau dấu phẩy ('' = các tỉnh/thành cấp 1), number, columns, columns_tablet, columns_mobile }.
	 * @return string
	 */
	public static function locations( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'locations'      => '',
				'number'         => 6,
				'columns'        => 3,
				'columns_tablet' => 2,
				'columns_mobile' => 2,
			)
		);
		self::enqueue();
		$args = array(
			'taxonomy'   => 'lp_location',
			'hide_empty' => true,
		);
		if ( $atts['locations'] ) {
			$args['slug']    = array_map( 'trim', explode( ',', $atts['locations'] ) );
			$args['orderby'] = 'slug__in';
		} else {
			$args['parent']  = 0;
			$args['orderby'] = 'count';
			$args['order']   = 'DESC';
			$args['number']  = max( 1, (int) $atts['number'] );
		}
		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		ob_start();
		echo '<div class="lp-grid lp-locations" style="' . esc_attr( self::grid_style( $atts ) ) . '">';
		foreach ( $terms as $i => $term ) {
			$image = self::location_image( $term );
			echo '<a class="lp-location' . ( 0 === $i && count( $terms ) > 3 ? ' lp-location--wide' : '' ) . '" href="' . esc_url( get_term_link( $term ) ) . '">';
			if ( $image ) {
				echo wp_get_attachment_image(
					$image,
					'medium_large',
					false,
					array(
						'loading' => 'lazy',
						'alt'     => sprintf( lp_t( 'Bất động sản %s' ), $term->name ),
						'class'   => 'lp-location__img',
					)
				);
			}
			echo '<span class="lp-location__body"><span class="lp-location__name">' . esc_html( $term->name ) . '</span>';
			echo '<span class="lp-location__count">' . esc_html( sprintf( lp_t( '%s bất động sản' ), number_format_i18n( $term->count ) ) ) . '</span></span>';
			echo '</a>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Thanh tab lọc theo tỉnh/thành (lọc ngay trên trình duyệt).
	 *
	 * @param int[] $post_ids Bài đang hiển thị.
	 */
	public static function location_tabs( $post_ids ) {
		$roots = array();
		foreach ( $post_ids as $pid ) {
			foreach ( (array) get_the_terms( $pid, 'lp_location' ) as $term ) {
				if ( $term && ! is_wp_error( $term ) ) {
					$root                 = $term->parent ? get_term( $term->parent, 'lp_location' ) : $term;
					$roots[ $root->slug ] = $root->name;
				}
			}
		}
		if ( count( $roots ) < 2 ) {
			return;
		}
		echo '<div class="lp-tabs" role="toolbar" aria-label="' . esc_attr( lp_t( 'Lọc theo khu vực' ) ) . '">';
		echo '<button type="button" class="lp-tabs__btn is-active" data-lp-filter="" aria-pressed="true">' . esc_html( lp_t( 'Tất cả' ) ) . '</button>';
		foreach ( $roots as $slug => $name ) {
			printf( '<button type="button" class="lp-tabs__btn" data-lp-filter="%s" aria-pressed="false">%s</button>', esc_attr( $slug ), esc_html( $name ) );
		}
		echo '</div>';
	}

	/** Icon mặc định theo slug loại nhà đất. */
	public static function type_icon( $slug ) {
		$map = array(
			'can-ho'    => 'building',
			'nha-pho'   => 'house',
			'biet-thu'  => 'villa',
			'dat-nen'   => 'land',
			'van-phong' => 'office',
			'shophouse' => 'shop',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : 'house';
	}

	/**
	 * Lối tắt theo loại hình: icon + tên + số tin, dẫn tới trang loại nhà đất.
	 *
	 * @param array $atts { types: slug cách nhau dấu phẩy ('' = tất cả), columns, columns_tablet, columns_mobile }.
	 * @return string
	 */
	public static function types( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'types'          => '',
				'columns'        => 6,
				'columns_tablet' => 3,
				'columns_mobile' => 2,
			)
		);
		self::enqueue();
		$args = array(
			'taxonomy'   => 'lp_type',
			'hide_empty' => true,
		);
		if ( $atts['types'] ) {
			$args['slug']    = array_map( 'trim', explode( ',', $atts['types'] ) );
			$args['orderby'] = 'slug__in';
		}
		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		// Thứ tự cố định theo loại gốc (giống nhau ở mọi ngôn ngữ) nếu không chọn riêng.
		if ( ! $atts['types'] ) {
			$order = array( 'can-ho', 'nha-pho', 'biet-thu', 'dat-nen', 'shophouse', 'van-phong' );
			usort(
				$terms,
				function ( $a, $b ) use ( $order ) {
					$ia = array_search( lp_base_slug( $a ), $order, true );
					$ib = array_search( lp_base_slug( $b ), $order, true );
					return ( false === $ia ? 99 : $ia ) - ( false === $ib ? 99 : $ib );
				}
			);
		}
		ob_start();
		echo '<div class="lp-grid lp-types" style="' . esc_attr( self::grid_style( $atts ) ) . '">';
		foreach ( $terms as $term ) {
			printf(
				'<a class="lp-type" href="%1$s">%2$s<span class="lp-type__name">%3$s</span><span class="lp-type__count">%4$s</span></a>',
				esc_url( get_term_link( $term ) ),
				lp_color_icon( self::type_icon( lp_base_slug( $term ) ), 44 ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $term->name ),
				esc_html( sprintf( lp_t( '%s tin đăng' ), number_format_i18n( self::listing_count( $term ) ) ) )
			);
		}
		echo '</div>';
		return ob_get_clean();
	}

	/** Số tin nhà đất (không tính dự án) thuộc loại hình, theo ngôn ngữ hiện tại. */
	private static function listing_count( $term ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'property',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
				'tax_query'      => array( array( 'taxonomy' => $term->taxonomy, 'terms' => $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		return (int) $q->found_posts;
	}

	/** Ảnh đại diện khu vực: ảnh của tin/dự án mới nhất trong khu vực. */
	private static function location_image( $term ) {
		foreach ( array( 'project', 'property' ) as $type ) { // Ưu tiên ảnh dự án (ảnh toàn cảnh) hơn ảnh nội thất.
			$img = self::location_image_from( $term, $type );
			if ( $img ) {
				return $img;
			}
		}
		return 0;
	}

	private static function location_image_from( $term, $type ) {
		$ids = get_posts(
			array(
				'post_type'      => $type,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'lp_location',
						'terms'    => $term->term_id,
					),
				),
			)
		);
		return $ids ? (int) get_post_thumbnail_id( $ids[0] ) : 0;
	}

	/* ------------------------------------------------------------ Tiện ích */

	/** Biến CSS số cột theo thiết bị. */
	public static function grid_style( $atts ) {
		return sprintf(
			'--lp-cols:%d;--lp-cols-tablet:%d;--lp-cols-mobile:%d',
			max( 1, min( 6, (int) $atts['columns'] ) ),
			max( 1, min( 4, (int) $atts['columns_tablet'] ) ),
			max( 1, min( 2, (int) $atts['columns_mobile'] ) )
		);
	}

	public static function current_page() {
		return isset( $_GET['lp_page'] ) ? max( 1, absint( $_GET['lp_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/** URL trang hiện tại, không kèm tham số. */
	public static function current_url() {
		if ( is_singular() ) {
			return get_permalink();
		}
		global $wp;
		// Bỏ "/page/N" để lọc / sắp xếp luôn quay về trang 1.
		return home_url( trailingslashit( preg_replace( '#(^|/)page/\d+/?$#', '', (string) $wp->request ) ) );
	}

	/** Phân trang dùng tham số ?lp_page= (không xung đột với phân trang của trang tĩnh). */
	public static function pagination( $max, $current ) {
		if ( $max < 2 ) {
			return;
		}
		$links = paginate_links(
			array(
				'base'      => esc_url_raw( add_query_arg( 'lp_page', '%#%' ) ),
				'format'    => '',
				'current'   => $current,
				'total'     => $max,
				'prev_text' => '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html( lp_t( 'Trang trước' ) ) . '</span>',
				'next_text' => '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html( lp_t( 'Trang sau' ) ) . '</span>',
				'type'      => 'list',
				'mid_size'  => 1,
			)
		);
		echo '<nav class="lp-pagination" aria-label="' . esc_attr( lp_t( 'Phân trang' ) ) . '">' . wp_kses_post( $links ) . '</nav>';
	}

	/** Thanh "Tìm thấy N kết quả" + sắp xếp. */
	public static function toolbar( $found, $filters ) {
		$current = isset( $filters['lp_sort'] ) ? $filters['lp_sort'] : 'newest';
		echo '<div class="lp-toolbar">';
		echo '<p class="lp-toolbar__count">' . wp_kses_post( sprintf( lp_t( 'Tìm thấy <strong>%s</strong> bất động sản' ), esc_html( number_format_i18n( $found ) ) ) ) . '</p>';
		echo '<form method="get" class="lp-toolbar__sort">';
		foreach ( $filters as $key => $val ) {
			if ( 'lp_sort' !== $key ) {
				printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $key ), esc_attr( $val ) );
			}
		}
		echo '<label for="lp-sort" class="screen-reader-text">' . esc_html( lp_t( 'Sắp xếp' ) ) . '</label><select id="lp-sort" name="lp_sort" onchange="this.form.submit()">';
		foreach ( LP_Query::sorts() as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
		}
		echo '</select><noscript><button type="submit">' . esc_html( lp_t( 'Sắp xếp' ) ) . '</button></noscript></form></div>';
	}

	/** Danh sách term cho ô chọn (khu vực có thụt lề theo cấp). */
	public static function term_options( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}
		$by_parent = array();
		foreach ( $terms as $t ) {
			$by_parent[ $t->parent ][] = $t;
		}
		$out  = array();
		$walk = function ( $parent, $depth ) use ( &$walk, &$out, $by_parent ) {
			if ( empty( $by_parent[ $parent ] ) ) {
				return;
			}
			foreach ( $by_parent[ $parent ] as $t ) {
				$out[ $t->slug ] = str_repeat( "\u{00A0}\u{00A0}\u{00A0}\u{00A0}", $depth ) . $t->name; // Thụt lề bằng khoảng trắng cứng (hiển thị đẹp ở cả ô chọn gốc lẫn danh sách tùy biến).
				$walk( $t->term_id, $depth + 1 );
			}
		};
		$walk( 0, 0 );
		return $out;
	}
}
