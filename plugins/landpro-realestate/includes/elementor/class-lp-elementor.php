<?php
/**
 * Tích hợp Elementor: nhóm widget "LandPro".
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Elementor {

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'widgets' ) );
		// Nạp CSS của plugin trong trình chỉnh sửa / xem trước Elementor.
		add_action( 'elementor/preview/enqueue_styles', array( 'LP_Render', 'enqueue' ) );
	}

	public static function category( $manager ) {
		$manager->add_category(
			'landpro',
			array(
				'title' => 'LandPro - Bất động sản',
				'icon'  => 'eicon-home',
			)
		);
	}

	public static function widgets( $manager ) {
		require_once __DIR__ . '/widgets/class-lp-widget-base.php';
		require_once __DIR__ . '/widgets/class-lp-widget-properties.php';
		require_once __DIR__ . '/widgets/class-lp-widget-projects.php';
		require_once __DIR__ . '/widgets/class-lp-widget-search.php';
		require_once __DIR__ . '/widgets/class-lp-widget-locations.php';
		$manager->register( new LP_Widget_Properties() );
		$manager->register( new LP_Widget_Projects() );
		$manager->register( new LP_Widget_Search() );
		$manager->register( new LP_Widget_Locations() );
		require_once __DIR__ . '/widgets/class-lp-widget-types.php';
		$manager->register( new LP_Widget_Types() );
		require_once __DIR__ . '/widgets/class-lp-widget-hero.php';
		$manager->register( new LP_Widget_Hero() );
		require_once __DIR__ . '/widgets/class-lp-widget-map.php';
		$manager->register( new LP_Widget_Map() );
	}
}
