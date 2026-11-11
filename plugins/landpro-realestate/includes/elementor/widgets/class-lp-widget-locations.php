<?php
/**
 * Widget Elementor: Lưới khu vực.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Locations extends LP_Widget_Base {

	public function get_name() {
		return 'lp-locations';
	}

	public function get_title() {
		return 'Lưới khu vực';
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_keywords() {
		return array( 'khu vực', 'location', 'tỉnh', 'quận', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => 'Khu vực' ) );
		$this->add_control(
			'locations',
			array(
				'label'       => 'Chọn khu vực',
				'description' => 'Để trống để tự hiển thị các tỉnh/thành có nhiều tin nhất.',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_choices( 'lp_location' ),
			)
		);
		$this->add_control(
			'number',
			array(
				'label'     => 'Số khu vực',
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 6,
				'condition' => array( 'locations' => '' ),
			)
		);
		$this->add_columns_controls();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => 'Kiểu',
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'tile_height',
			array(
				'label'      => 'Chiều cao ô',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 400 ) ),
				'selectors'  => array( '{{WRAPPER}} .lp-location' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'overlay',
			array(
				'label'     => 'Màu phủ',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-location::after' => 'background: linear-gradient(180deg, transparent 30%, {{VALUE}});' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo LP_Render::locations( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'locations'      => implode( ',', (array) $s['locations'] ),
				'number'         => $s['number'],
				'columns'        => $s['columns'],
				'columns_tablet' => $s['columns_tablet'],
				'columns_mobile' => $s['columns_mobile'],
			)
		);
	}
}
