<?php
/**
 * Truy vấn nhà đất / dự án theo bộ lọc (dùng chung cho widget, shortcode và trang lưu trữ).
 *
 * Tham số URL bộ lọc: lp_kw, lp_purpose, lp_type, lp_location, lp_price, lp_area, lp_bed, lp_sort, lp_page.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Query {

	const FILTER_KEYS = array( 'lp_kw', 'lp_purpose', 'lp_type', 'lp_location', 'lp_price', 'lp_area', 'lp_bed', 'lp_sort' );

	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'archive_query' ) );
	}

	/** Khoảng giá cho bộ lọc (giá trị "min-max" theo VNĐ). */
	public static function price_ranges( $purpose = '' ) {
		$purpose = $purpose ? lp_base_slug_from( $purpose, 'lp_purpose' ) : '';
		if ( 'cho-thue' === $purpose ) {
			return array_map( 'lp_t', array(
				'0-5000000'         => 'Dưới 5 triệu',
				'5000000-10000000'  => '5 - 10 triệu',
				'10000000-20000000' => '10 - 20 triệu',
				'20000000-50000000' => '20 - 50 triệu',
				'50000000-'         => 'Trên 50 triệu',
			) );
		}
		return array_map( 'lp_t', array(
			'0-1000000000'           => 'Dưới 1 tỷ',
			'1000000000-2000000000'  => '1 - 2 tỷ',
			'2000000000-3000000000'  => '2 - 3 tỷ',
			'3000000000-5000000000'  => '3 - 5 tỷ',
			'5000000000-7000000000'  => '5 - 7 tỷ',
			'7000000000-10000000000' => '7 - 10 tỷ',
			'10000000000-'           => 'Trên 10 tỷ',
		) );
	}

	/** Khoảng diện tích cho bộ lọc. */
	public static function area_ranges() {
		return array_map( 'lp_t', array(
			'0-30'    => 'Dưới 30 m²',
			'30-50'   => '30 - 50 m²',
			'50-80'   => '50 - 80 m²',
			'80-100'  => '80 - 100 m²',
			'100-150' => '100 - 150 m²',
			'150-300' => '150 - 300 m²',
			'300-'    => 'Trên 300 m²',
		) );
	}

	/** Các kiểu sắp xếp. */
	public static function sorts() {
		return array_map( 'lp_t', array(
			'newest'     => 'Mới nhất',
			'price_asc'  => 'Giá thấp đến cao',
			'price_desc' => 'Giá cao đến thấp',
			'area_desc'  => 'Diện tích lớn nhất',
		) );
	}

	/** Đọc bộ lọc từ URL (đã làm sạch). */
	public static function request_filters() {
		$out = array();
		foreach ( self::FILTER_KEYS as $key ) {
			if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification -- bộ lọc công khai, chỉ đọc.
				$raw         = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
				$out[ $key ] = 'lp_kw' === $key ? sanitize_text_field( $raw ) : preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $raw ) );
			}
		}
		return $out;
	}

	/**
	 * Tạo tham số WP_Query cho nhà đất.
	 *
	 * @param array $atts {purpose, type, location, project, featured, per_page, orderby, ids}.
	 * @param array $filters Bộ lọc từ URL (ghi đè $atts).
	 * @return array
	 */
	public static function property_args( $atts, $filters = array() ) {
		$args = array(
			'post_type'           => 'property',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) ( isset( $atts['per_page'] ) ? $atts['per_page'] : 9 ) ),
			'ignore_sticky_posts' => true,
			'tax_query'           => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_query'          => array(), // phpcs:ignore WordPress.DB.SlowDBQuery
		);

		$map = array(
			'lp_purpose'  => 'purpose',
			'lp_type'     => 'type',
			'lp_location' => 'location',
		);
		foreach ( $map as $tax_key => $att_key ) {
			$taxonomy = 'lp_' . $att_key;
			$value    = ! empty( $filters[ $tax_key ] ) ? $filters[ $tax_key ] : ( isset( $atts[ $att_key ] ) ? $atts[ $att_key ] : '' );
			if ( $value ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => array_map( 'trim', explode( ',', $value ) ),
				);
			}
		}

		if ( ! empty( $atts['project'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_lp_project_id',
				'value' => absint( $atts['project'] ),
			);
		}
		if ( ! empty( $atts['featured'] ) && 'no' !== $atts['featured'] ) {
			$args['meta_query'][] = array(
				'key'   => '_lp_featured',
				'value' => '1',
			);
		}
		if ( ! empty( $atts['exclude'] ) ) {
			$args['post__not_in'] = array_map( 'absint', (array) $atts['exclude'] );
		}

		if ( ! empty( $filters['lp_kw'] ) ) {
			$args['s'] = $filters['lp_kw'];
		}
		if ( ! empty( $filters['lp_price'] ) ) {
			self::range_meta( $args, '_lp_price', $filters['lp_price'] );
		}
		if ( ! empty( $filters['lp_area'] ) ) {
			self::range_meta( $args, '_lp_area', $filters['lp_area'] );
		}
		if ( ! empty( $filters['lp_bed'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_lp_bedrooms',
				'value'   => absint( $filters['lp_bed'] ),
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}

		$sort = ! empty( $filters['lp_sort'] ) ? $filters['lp_sort'] : ( isset( $atts['orderby'] ) ? $atts['orderby'] : 'newest' );
		self::apply_sort( $args, $sort );

		if ( count( $args['tax_query'] ) > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}
		if ( empty( $args['tax_query'] ) ) {
			unset( $args['tax_query'] );
		}
		if ( empty( $args['meta_query'] ) ) {
			unset( $args['meta_query'] );
		}
		return $args;
	}

	/** Tham số WP_Query cho dự án. */
	public static function project_args( $atts ) {
		$args = array(
			'post_type'           => 'project',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) ( isset( $atts['per_page'] ) ? $atts['per_page'] : 6 ) ),
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
		);
		$tax = array();
		foreach ( array( 'type', 'location' ) as $key ) {
			if ( ! empty( $atts[ $key ] ) ) {
				$tax[] = array(
					'taxonomy' => 'lp_' . $key,
					'field'    => 'slug',
					'terms'    => array_map( 'trim', explode( ',', $atts[ $key ] ) ),
				);
			}
		}
		if ( $tax ) {
			$args['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$meta = array();
		if ( ! empty( $atts['status'] ) ) {
			$meta[] = array(
				'key'   => '_lp_status',
				'value' => sanitize_key( $atts['status'] ),
			);
		}
		if ( ! empty( $atts['featured'] ) && 'no' !== $atts['featured'] ) {
			$meta[] = array(
				'key'   => '_lp_featured',
				'value' => '1',
			);
		}
		if ( $meta ) {
			$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		if ( ! empty( $atts['exclude'] ) ) {
			$args['post__not_in'] = array_map( 'absint', (array) $atts['exclude'] );
		}
		if ( isset( $atts['orderby'] ) && 'title' === $atts['orderby'] ) {
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
		}
		return $args;
	}

	private static function range_meta( &$args, $key, $range ) {
		$parts = explode( '-', $range, 2 );
		$min   = isset( $parts[0] ) && '' !== $parts[0] ? (float) $parts[0] : null;
		$max   = isset( $parts[1] ) && '' !== $parts[1] ? (float) $parts[1] : null;
		if ( null !== $min && $min > 0 ) {
			$args['meta_query'][] = array(
				'key'     => $key,
				'value'   => $min,
				'compare' => '>=',
				'type'    => 'DECIMAL(20,2)',
			);
		}
		if ( null !== $max ) {
			$args['meta_query'][] = array(
				'key'     => $key,
				'value'   => $max,
				'compare' => '<=',
				'type'    => 'DECIMAL(20,2)',
			);
		}
	}

	private static function apply_sort( &$args, $sort ) {
		switch ( $sort ) {
			case 'price_asc':
			case 'price_desc':
				$args['meta_key'] = '_lp_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'price_asc' === $sort ? 'ASC' : 'DESC';
				break;
			case 'area_desc':
				$args['meta_key'] = '_lp_area'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'random':
				$args['orderby'] = 'rand';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
		}
	}

	/**
	 * Áp bộ lọc URL vào trang lưu trữ /nha-dat/ và trang phân loại.
	 *
	 * @param WP_Query $q Truy vấn chính.
	 */
	public static function archive_query( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( ! ( $q->is_post_type_archive( 'property' ) || $q->is_tax( array( 'lp_purpose', 'lp_type', 'lp_location' ) ) ) ) {
			return;
		}
		$filters = self::request_filters();
		$args    = self::property_args( array( 'per_page' => (int) lp_option( 'per_page', 12 ) ), $filters );
		foreach ( array( 'meta_query', 'meta_key', 'orderby', 'order', 's', 'posts_per_page' ) as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$q->set( $key, $args[ $key ] );
			}
		}
		if ( isset( $args['tax_query'] ) ) {
			$existing = (array) $q->get( 'tax_query' );
			$q->set( 'tax_query', array_merge( $existing, $args['tax_query'] ) );
		}
		$q->set( 'post_type', 'property' );
	}
}
