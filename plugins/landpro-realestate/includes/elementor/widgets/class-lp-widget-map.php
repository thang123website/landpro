<?php
/**
 * Widget Elementor: Bản đồ nhà đất (Leaflet + OpenStreetMap).
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Map extends LP_Widget_Base {

	public function get_name() {
		return 'lp-map';
	}

	public function get_title() {
		return 'Bản đồ nhà đất';
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_keywords() {
		return array( 'bản đồ', 'map', 'vị trí', 'landpro' );
	}

	public function get_style_depends() {
		return array( 'lp-frontend', 'leaflet' );
	}

	public function get_script_depends() {
		return array( 'lp-frontend', 'lp-map' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => 'Dữ liệu' ) );
		$this->add_control(
			'purpose',
			array(
				'label'   => 'Nhu cầu',
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''         => 'Mua và thuê',
					'ban'      => 'Mua bán',
					'cho-thue' => 'Cho thuê',
				),
			)
		);
		$this->add_control(
			'type',
			array(
				'label'       => 'Loại nhà đất',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_choices( 'lp_type' ),
			)
		);
		$this->add_control(
			'location',
			array(
				'label'       => 'Khu vực',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_choices( 'lp_location' ),
			)
		);
		$this->add_control(
			'projects',
			array(
				'label'        => 'Hiện cả dự án',
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'use_filters',
			array(
				'label'        => 'Theo bộ lọc tìm kiếm trên URL',
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'toggle',
			array(
				'label'        => 'Ẩn sẵn, bấm nút "Xem bản đồ" để mở',
				'description'  => 'Máy tính: chia đôi màn hình danh sách / bản đồ. Điện thoại: mở toàn màn hình.',
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'height',
			array(
				'label'       => 'Chiều cao (px)',
				'description' => 'Để trống = theo Nhà đất → Bản đồ.',
				'type'        => Controls_Manager::NUMBER,
				'min'         => 240,
				'max'         => 900,
				'default'     => '',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo LP_Map::shortcode( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'purpose'     => $s['purpose'],
				'type'        => implode( ',', (array) $s['type'] ),
				'location'    => implode( ',', (array) $s['location'] ),
				'projects'    => 'yes' === $s['projects'] ? 'yes' : 'no',
				'use_filters' => 'yes' === $s['use_filters'] ? 'yes' : 'no',
				'height'      => (int) $s['height'],
				'toggle'      => 'yes' === $s['toggle'] ? 'yes' : 'no',
				'context'     => 'listing',
			)
		);
	}
}
