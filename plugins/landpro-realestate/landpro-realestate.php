<?php
/**
 * Plugin Name:       LandPro Real Estate
 * Description:       Quản lý bất động sản: Nhà đất (bán / cho thuê), Dự án, tìm kiếm có bộ lọc, trang chi tiết, widget Elementor, form liên hệ Fluent Forms và dữ liệu demo.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            LandPro
 * Text Domain:       landpro
 * License:           GPL-2.0-or-later
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LP_VERSION', '1.0.0' );
define( 'LP_FILE', __FILE__ );
define( 'LP_DIR', plugin_dir_path( __FILE__ ) );
define( 'LP_URL', plugin_dir_url( __FILE__ ) );

require_once LP_DIR . 'includes/helpers.php';
require_once LP_DIR . 'includes/icons-color.php';
require_once LP_DIR . 'includes/i18n.php';
require_once LP_DIR . 'includes/class-lp-post-types.php';
require_once LP_DIR . 'includes/class-lp-meta-boxes.php';
require_once LP_DIR . 'includes/class-lp-query.php';
require_once LP_DIR . 'includes/class-lp-render.php';
require_once LP_DIR . 'includes/class-lp-shortcodes.php';
require_once LP_DIR . 'includes/class-lp-templates.php';
require_once LP_DIR . 'includes/class-lp-map.php';
require_once LP_DIR . 'includes/class-lp-map-settings.php';
require_once LP_DIR . 'includes/class-lp-cache.php';
require_once LP_DIR . 'includes/class-lp-legal.php';
require_once LP_DIR . 'includes/class-lp-consign.php';
require_once LP_DIR . 'includes/class-lp-settings.php';
require_once LP_DIR . 'includes/class-lp-schema.php';
require_once LP_DIR . 'includes/demo/class-lp-demo.php';
require_once LP_DIR . 'includes/demo/class-lp-demo-i18n.php';

LP_Post_Types::init();
LP_Meta_Boxes::init();
LP_Query::init();
LP_Shortcodes::init();
LP_Templates::init();
LP_Map::init();
LP_Map_Settings::init();
LP_Cache::init();
LP_Legal::init();
LP_Consign::init();
LP_Settings::init();
LP_Schema::init();
LP_Demo::init();

add_action(
	'plugins_loaded',
	function () {
		if ( did_action( 'elementor/loaded' ) ) {
			require_once LP_DIR . 'includes/elementor/class-lp-elementor.php';
			LP_Elementor::init();
		}
	}
);

register_activation_hook(
	__FILE__,
	function () {
		LP_Post_Types::register();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
