<?php
/**
 * Widget Elementor: Tìm theo loại hình (icon + tên + số tin).
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Types extends LP_Widget_Base {

	public function get_name() {
		return 'lp-types';
	}

	public function get_title() {
		return 'Tìm theo loại hình';
	}

	public function get_icon() {
		return 'eicon-apps';
	}

	public function get_keywords() {
		return array( 'loại hình', 'căn hộ', 'nhà phố', 'type', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => 'Loại hình' ) );
		$this->add_control(
			'types',
			array(
				'label'       => 'Chọn loại hình',
				'description' => 'Để trống để hiện tất cả loại hình đang có tin.',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_choices( 'lp_type' ),
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
		$this->add_control(
			'tile_bg',
			array(
				'label'     => 'Nền ô',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-type' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'name_color',
			array(
				'label'     => 'Màu tên',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-type__name' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo LP_Render::types( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'types'          => implode( ',', (array) $s['types'] ),
				'columns'        => $s['columns'],
				'columns_tablet' => $s['columns_tablet'],
				'columns_mobile' => $s['columns_mobile'],
			)
		);
	}
}
