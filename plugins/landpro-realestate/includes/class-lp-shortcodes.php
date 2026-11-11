<?php
/**
 * Shortcode (dùng được trong Elementor qua widget Shortcode hoặc trình soạn thảo):
 *
 *   [lp_properties purpose="ban" type="can-ho" location="quan-7" featured="yes" per_page="6" columns="3" pagination="yes" use_filters="yes" show_toolbar="yes"]
 *   [lp_projects status="dang-ban" per_page="6" columns="3"]
 *   [lp_search tabs="yes" fields="kw,type,location,price" action=""]
 *   [lp_contact_form]  Form Fluent Forms cấu hình trong Nhà đất → Cài đặt
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Shortcodes {

	public static function init() {
		// WP 6.7+ gắn sizes="auto" cho ảnh lazy: trình duyệt chọn ảnh theo bề ngang khung,
		// sai với ảnh phủ khung dọc (card dự án, ô khu vực) → chọn file quá nhỏ, ảnh mờ. Dùng sizes khai báo rõ.
		add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
	}

	public static function register() {
		add_shortcode( 'lp_properties', array( 'LP_Render', 'properties' ) );
		add_shortcode( 'lp_projects', array( 'LP_Render', 'projects' ) );
		add_shortcode( 'lp_search', array( 'LP_Render', 'search_form' ) );
		add_shortcode( 'lp_locations', array( 'LP_Render', 'locations' ) );
		add_shortcode( 'lp_types', array( 'LP_Render', 'types' ) );
		add_shortcode(
			'lp_breadcrumb',
			function ( $atts ) {
				$atts = shortcode_atts( array( 'style' => '' ), $atts );
				return LP_Templates::breadcrumb( $atts['style'], false );
			}
		);
		add_shortcode( 'lp_contact_form', array( __CLASS__, 'contact_form' ) );
	}

	public static function register_assets() {
		wp_register_style( 'lp-frontend', LP_URL . 'assets/css/frontend.css', array(), filemtime( LP_DIR . 'assets/css/frontend.css' ) );
		wp_register_script( 'lp-frontend', LP_URL . 'assets/js/frontend.js', array(), filemtime( LP_DIR . 'assets/js/frontend.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'lp-frontend',
			'lpL10n',
			array(
				'viewer' => lp_t( 'Xem ảnh' ),
				'close'  => lp_t( 'Đóng' ),
				'prev'   => lp_t( 'Ảnh trước' ),
				'next'   => lp_t( 'Ảnh sau' ),
				'zoom'   => lp_t( 'Phóng to' ),
				'play'   => lp_t( 'Trình chiếu' ),
				'pause'  => lp_t( 'Dừng trình chiếu' ),
				'thumbs' => lp_t( 'Ẩn / hiện ảnh nhỏ' ),
				'full'   => lp_t( 'Toàn màn hình' ),
			)
		);
		wp_enqueue_style( 'lp-frontend' ); // Dùng cho header, cờ ngôn ngữ, nút trên mọi trang.
		wp_enqueue_script( 'lp-frontend' ); // Đo chiều cao header (cho các khối dính), nút về đầu trang.
		if ( is_singular( array( 'property', 'project' ) ) || is_post_type_archive( 'property' ) || is_tax( array( 'lp_purpose', 'lp_type', 'lp_location' ) ) ) {
			LP_Render::enqueue();
		}
	}

	/** Form liên hệ Fluent Forms đã chọn trong cài đặt. */
	public static function contact_form( $atts = array() ) {
		$default = lp_option( 'form_id' );
		if ( function_exists( 'pll_current_language' ) && pll_current_language() ) {
			$default = lp_option( 'form_id_' . pll_current_language(), $default );
		}
		$atts    = shortcode_atts( array( 'id' => $default ), $atts );
		$form_id = absint( $atts['id'] );
		if ( ! $form_id || ! shortcode_exists( 'fluentform' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return '<p class="lp-empty">Chưa chọn form liên hệ. Vào <em>Nhà đất → Cài đặt</em> để chọn form Fluent Forms.</p>';
			}
			return '';
		}
		return do_shortcode( '[fluentform id="' . $form_id . '"]' );
	}
}
