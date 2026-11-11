<?php
/**
 * Meta box nhập thông tin Nhà đất / Dự án trong trang sửa bài.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Meta_Boxes {

	/** Gợi ý kích thước ảnh đại diện theo loại nội dung (hiện trong hộp "Ảnh đại diện"). */
	public static function thumbnail_hint( $html, $post_id ) {
		$hints = array(
			'property' => 'Kích thước chuẩn: ảnh ngang 1600 × 1067 px (3:2), tối thiểu 1200 px, WebP/JPG ≤ 400 KB. Dùng cho thẻ tin, ảnh lớn trong trang chi tiết và khi chia sẻ.',
			'project'  => 'Kích thước chuẩn: ảnh ngang 1600 × 1067 px (3:2), WebP/JPG ≤ 400 KB. Thẻ dự án hiển thị ảnh dọc 2:3 nên đặt tòa nhà / chủ thể ở giữa ảnh.',
			'post'     => 'Kích thước chuẩn: 1200 × 675 px (16:9), WebP/JPG ≤ 300 KB. Ảnh cũng làm banner đầu bài và ảnh chia sẻ Facebook / Zalo.',
			'page'     => 'Ảnh chia sẻ mạng xã hội: 1200 × 630 px, ≤ 300 KB.',
		);
		$type = get_post_type( $post_id );
		if ( isset( $hints[ $type ] ) ) {
			$html .= '<p class="description" style="margin-top:8px">' . esc_html( $hints[ $type ] ) . '</p>';
		}
		return $html;
	}

	public static function init() {
		add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'thumbnail_hint' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post_property', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'save_post_project', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function add() {
		add_meta_box( 'lp_property_info', 'Thông tin nhà đất', array( __CLASS__, 'render' ), 'property', 'normal', 'high' );
		add_meta_box( 'lp_project_info', 'Thông tin dự án', array( __CLASS__, 'render' ), 'project', 'normal', 'high' );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'property', 'project' ), true ) || 'post' !== $screen->base ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'lp-admin', LP_URL . 'assets/css/admin.css', array(), LP_VERSION );
		wp_enqueue_script( 'lp-admin', LP_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), LP_VERSION, true );
	}

	public static function render( $post ) {
		wp_nonce_field( 'lp_save_meta', 'lp_meta_nonce' );
		echo '<div class="lp-fields">';
		foreach ( lp_fields( $post->post_type ) as $key => $field ) {
			$name  = 'lp[' . $key . ']';
			$id    = 'lp_' . $key;
			$value = get_post_meta( $post->ID, '_lp_' . $key, true );
			$wide  = in_array( $field['type'], array( 'textarea', 'gallery' ), true ) ? ' lp-field--wide' : '';

			echo '<div class="lp-field lp-field--' . esc_attr( $field['type'] ) . esc_attr( $wide ) . '">';
			if ( 'checkbox' === $field['type'] ) {
				printf(
					'<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
					esc_attr( $name ),
					checked( (bool) $value, true, false ),
					esc_html( $field['label'] )
				);
				echo '</div>';
				continue;
			}

			printf( '<label for="%s">%s</label>', esc_attr( $id ), esc_html( $field['label'] ) );

			switch ( $field['type'] ) {
				case 'select':
					printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
					foreach ( $field['options'] as $opt => $label ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
					}
					echo '</select>';
					break;

				case 'textarea':
					printf( '<textarea id="%s" name="%s" rows="4">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ) );
					break;

				case 'post':
					$items = get_posts(
						array(
							'post_type'      => $field['post_type'],
							'posts_per_page' => 200,
							'orderby'        => 'title',
							'order'          => 'ASC',
						)
					);
					printf( '<select id="%s" name="%s"><option value="">— Không —</option>', esc_attr( $id ), esc_attr( $name ) );
					foreach ( $items as $item ) {
						printf( '<option value="%d" %s>%s</option>', (int) $item->ID, selected( (int) $value, $item->ID, false ), esc_html( $item->post_title ) );
					}
					echo '</select>';
					break;

				case 'gallery':
					$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
					printf( '<input type="hidden" id="%s" name="%s" value="%s" class="lp-gallery-input">', esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
					echo '<ul class="lp-gallery-list">';
					foreach ( $ids as $img ) {
						printf( '<li data-id="%d">%s<button type="button" class="lp-gallery-remove" aria-label="Xóa ảnh">×</button></li>', (int) $img, wp_get_attachment_image( $img, 'thumbnail' ) );
					}
					echo '</ul><button type="button" class="button lp-gallery-add">Thêm ảnh</button> <span class="description">Kéo thả để sắp xếp. Ảnh đại diện luôn hiển thị đầu tiên.<br><strong>Kích thước chuẩn:</strong> ảnh ngang 1600 × 1067 px (3:2) hoặc 1600 × 1200 px (4:3), tối thiểu 1200 px chiều ngang, WebP/JPG ≤ 400 KB/ảnh; nên 5 – 15 ảnh.</span>';
					break;

				default:
					printf(
						'<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s>',
						'number' === $field['type'] ? 'number' : ( 'url' === $field['type'] ? 'url' : 'text' ),
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value ),
						esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ),
						'number' === $field['type'] ? 'min="0" step="' . esc_attr( isset( $field['step'] ) ? $field['step'] : '1' ) . '"' : ''
					);
			}
			if ( ! empty( $field['help'] ) ) {
				echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['lp_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lp_meta_nonce'] ), 'lp_save_meta' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$input = isset( $_POST['lp'] ) && is_array( $_POST['lp'] ) ? wp_unslash( $_POST['lp'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitize từng trường bên dưới.

		foreach ( lp_fields( $post->post_type ) as $key => $field ) {
			$raw   = isset( $input[ $key ] ) ? $input[ $key ] : '';
			$value = call_user_func( array( __CLASS__, 'sanitize_value_' . $field['type'] ), $raw );
			if ( '' === $value || null === $value || 0 === $value && 'checkbox' === $field['type'] ) {
				delete_post_meta( $post_id, '_lp_' . $key );
			} else {
				update_post_meta( $post_id, '_lp_' . $key, $value );
			}
		}
	}

	/* ----------------------------------------------------- Sanitize theo kiểu */

	public static function sanitize_value_text( $v ) {
		return sanitize_text_field( (string) $v );
	}

	public static function sanitize_value_select( $v ) {
		return sanitize_key( (string) $v );
	}

	public static function sanitize_value_textarea( $v ) {
		return sanitize_textarea_field( (string) $v );
	}

	public static function sanitize_value_url( $v ) {
		return esc_url_raw( (string) $v );
	}

	public static function sanitize_value_number( $v ) {
		$v = str_replace( array( ' ', ',' ), array( '', '.' ), (string) $v );
		return is_numeric( $v ) ? $v + 0 : '';
	}

	public static function sanitize_value_post( $v ) {
		$v = absint( $v );
		return $v ? $v : '';
	}

	public static function sanitize_value_checkbox( $v ) {
		return $v ? 1 : 0;
	}

	public static function sanitize_value_gallery( $v ) {
		return implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $v ) ) ) );
	}
}
