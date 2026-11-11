<?php
/**
 * Đăng ký loại nội dung (Nhà đất, Dự án), phân loại và trường meta.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Post_Types {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
		// Nhà đất / Dự án dùng trình soạn thảo cổ điển: các hộp thông tin hiện rộng, dễ nhập (tin tức vẫn dùng Gutenberg).
		add_filter(
			'use_block_editor_for_post_type',
			function ( $use, $post_type ) {
				return in_array( $post_type, array( 'property', 'project' ), true ) ? false : $use;
			},
			10,
			2
		);
		add_filter( 'manage_property_posts_columns', array( __CLASS__, 'property_columns' ) );
		add_action( 'manage_property_posts_custom_column', array( __CLASS__, 'property_column_content' ), 10, 2 );
		add_filter( 'manage_project_posts_columns', array( __CLASS__, 'project_columns' ) );
		add_action( 'manage_project_posts_custom_column', array( __CLASS__, 'project_column_content' ), 10, 2 );
	}

	public static function register() {
		register_post_type(
			'property',
			array(
				'labels'        => array(
					'name'               => 'Nhà đất',
					'singular_name'      => 'Nhà đất',
					'add_new'            => 'Thêm tin',
					'add_new_item'       => 'Thêm tin nhà đất',
					'edit_item'          => 'Sửa tin nhà đất',
					'all_items'          => 'Tất cả nhà đất',
					'search_items'       => 'Tìm nhà đất',
					'not_found'          => 'Chưa có tin nhà đất nào',
					'menu_name'          => 'Nhà đất',
				),
				'public'        => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-admin-home',
				'menu_position' => 5,
				'has_archive'   => 'nha-dat',
				'rewrite'       => array( 'slug' => 'nha-dat', 'with_front' => false ),
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			)
		);

		register_post_type(
			'project',
			array(
				'labels'        => array(
					'name'          => 'Dự án',
					'singular_name' => 'Dự án',
					'add_new'       => 'Thêm dự án',
					'add_new_item'  => 'Thêm dự án',
					'edit_item'     => 'Sửa dự án',
					'all_items'     => 'Tất cả dự án',
					'search_items'  => 'Tìm dự án',
					'not_found'     => 'Chưa có dự án nào',
					'menu_name'     => 'Dự án',
				),
				'public'        => true,
				'show_in_rest'  => true,
				'menu_icon'     => 'dashicons-building',
				'menu_position' => 6,
				'has_archive'   => false,
				'rewrite'       => array( 'slug' => 'du-an', 'with_front' => false ),
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			)
		);

		register_taxonomy(
			'lp_purpose',
			array( 'property' ),
			array(
				'labels'            => array(
					'name'          => 'Nhu cầu',
					'singular_name' => 'Nhu cầu',
					'menu_name'     => 'Nhu cầu (Bán/Thuê)',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'nhu-cau', 'with_front' => false ),
			)
		);

		register_taxonomy(
			'lp_type',
			array( 'property', 'project' ),
			array(
				'labels'            => array(
					'name'          => 'Loại nhà đất',
					'singular_name' => 'Loại nhà đất',
					'add_new_item'  => 'Thêm loại nhà đất',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'loai-nha-dat', 'with_front' => false ),
			)
		);

		register_taxonomy(
			'lp_location',
			array( 'property', 'project' ),
			array(
				'labels'            => array(
					'name'          => 'Khu vực',
					'singular_name' => 'Khu vực',
					'add_new_item'  => 'Thêm khu vực',
					'parent_item'   => 'Tỉnh / Thành phố',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'khu-vuc', 'with_front' => false, 'hierarchical' => true ),
			)
		);

		foreach ( array( 'property', 'project' ) as $post_type ) {
			foreach ( lp_fields( $post_type ) as $key => $field ) {
				$is_number = in_array( $field['type'], array( 'number', 'post', 'checkbox' ), true );
				register_post_meta(
					$post_type,
					'_lp_' . $key,
					array(
						'type'              => $is_number ? 'number' : 'string',
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => array( 'LP_Meta_Boxes', 'sanitize_value_' . $field['type'] ),
						'auth_callback'     => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		}
	}

	/* ------------------------------------------------------------ Cột admin */

	public static function property_columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			if ( 'title' === $key ) {
				$new['lp_thumb'] = 'Ảnh';
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['lp_price'] = 'Giá';
				$new['lp_area']  = 'Diện tích';
			}
		}
		return $new;
	}

	public static function property_column_content( $col, $post_id ) {
		if ( 'lp_thumb' === $col ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 45 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} elseif ( 'lp_price' === $col ) {
			echo lp_price_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape.
			if ( lp_get( 'featured', $post_id ) ) {
				echo ' <span class="dashicons dashicons-star-filled" title="Nổi bật" style="color:#f0b400"></span>';
			}
		} elseif ( 'lp_area' === $col ) {
			$area = lp_get( 'area', $post_id );
			echo $area ? esc_html( lp_number( $area ) . ' m²' ) : '—';
		}
	}

	public static function project_columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			if ( 'title' === $key ) {
				$new['lp_thumb'] = 'Ảnh';
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['lp_status'] = 'Trạng thái';
				$new['lp_dev']    = 'Chủ đầu tư';
			}
		}
		return $new;
	}

	public static function project_column_content( $col, $post_id ) {
		if ( 'lp_thumb' === $col ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 45 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} elseif ( 'lp_status' === $col ) {
			echo esc_html( lp_get_label( 'status', $post_id ) );
		} elseif ( 'lp_dev' === $col ) {
			echo esc_html( lp_get( 'developer', $post_id ) );
		}
	}
}
