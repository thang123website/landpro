<?php
/**
 * Tương thích plugin cache (WP Rocket, LiteSpeed Cache, W3 Total Cache, WP Super Cache,
 * WP Fastest Cache, SiteGround Optimizer, Breeze, Autoptimize…):
 *  - Tự xóa cache trang khi tin nhà đất / dự án hoặc cài đặt LandPro thay đổi
 *    (trang Mua / Thuê / Dự án dựng bằng Elementor không tự được các plugin cache làm mới).
 *  - Đánh dấu các script cần chạy ngay khi khách bấm để không bị "Delay JS" hoãn.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Cache {

	/** Script phải sẵn sàng ngay (bộ lọc, ô chọn, nút bản đồ, menu…). */
	const CRITICAL_HANDLES = array( 'lp-frontend', 'leaflet', 'lp-map-base', 'lp-map', 'lp-consign' );

	/** Thuộc tính loại trừ khỏi "hoãn JS" của các plugin cache phổ biến. */
	const NO_DELAY_ATTRS = 'nowprocket data-no-defer="1" data-no-optimize="1" data-cfasync="false"';

	private static $queued = false;

	public static function init() {
		add_filter( 'script_loader_tag', array( __CLASS__, 'script_tag' ), 20, 2 );

		// Dữ liệu thay đổi → xóa cache (gom lại, chạy một lần cuối request).
		add_action( 'transition_post_status', array( __CLASS__, 'on_status' ), 10, 3 );
		add_action( 'before_delete_post', array( __CLASS__, 'on_delete' ) );
		foreach ( array( 'lp_map', 'lp_settings' ) as $opt ) {
			add_action( 'update_option_' . $opt, array( __CLASS__, 'queue' ) );
			add_action( 'add_option_' . $opt, array( __CLASS__, 'queue' ) );
		}
		foreach ( array( 'lp_purpose', 'lp_type', 'lp_location' ) as $tax ) {
			add_action( 'created_' . $tax, array( __CLASS__, 'queue' ) );
			add_action( 'edited_' . $tax, array( __CLASS__, 'queue' ) );
			add_action( 'delete_' . $tax, array( __CLASS__, 'queue' ) );
		}
		add_action( 'lp_demo_built', array( __CLASS__, 'purge_all' ) );
	}

	public static function script_tag( $tag, $handle ) {
		if ( in_array( $handle, self::CRITICAL_HANDLES, true ) && false === strpos( $tag, 'nowprocket' ) ) {
			$tag = str_replace( '<script ', '<script ' . self::NO_DELAY_ATTRS . ' ', $tag );
		}
		return $tag;
	}

	public static function on_status( $new, $old, $post ) {
		if ( in_array( $post->post_type, array( 'property', 'project' ), true ) && ( 'publish' === $new || 'publish' === $old ) ) {
			self::queue();
		}
	}

	public static function on_delete( $post_id ) {
		if ( in_array( get_post_type( $post_id ), array( 'property', 'project' ), true ) && 'publish' === get_post_status( $post_id ) ) {
			self::queue();
		}
	}

	public static function queue() {
		if ( ! self::$queued ) {
			self::$queued = true;
			add_action( 'shutdown', array( __CLASS__, 'purge_all' ) );
		}
	}

	/** Xóa toàn bộ cache trang của plugin cache đang dùng (không có plugin nào thì bỏ qua). */
	public static function purge_all() {
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain(); // WP Rocket.
		}
		do_action( 'litespeed_purge_all' ); // LiteSpeed Cache.
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all(); // W3 Total Cache.
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache(); // WP Super Cache.
		}
		if ( function_exists( 'wpfc_clear_all_cache' ) ) {
			wpfc_clear_all_cache( true ); // WP Fastest Cache.
		}
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache(); // SiteGround Optimizer.
		}
		do_action( 'breeze_clear_all_cache' ); // Breeze (Cloudways).
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall(); // Autoptimize.
		}
		do_action( 'lp_cache_purged' );
	}
}
