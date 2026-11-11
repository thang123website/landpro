<?php
/**
 * Lớp nền cho widget LandPro: các control dùng chung (cột, màu card).
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

abstract class LP_Widget_Base extends \Elementor\Widget_Base {

	public function get_categories() {
		return array( 'landpro' );
	}

	public function get_style_depends() {
		return array( 'lp-frontend' );
	}

	public function get_script_depends() {
		return array( 'lp-frontend' );
	}

	/** Danh sách term cho control SELECT2. */
	protected function term_choices( $taxonomy ) {
		return LP_Render::term_options( $taxonomy );
	}

	/** Control số cột theo thiết bị. */
	protected function add_columns_controls() {
		$this->add_control(
			'columns',
			array(
				'label'   => 'Số cột (máy tính)',
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'default' => 4,
			)
		);
		$this->add_control(
			'columns_tablet',
			array(
				'label'   => 'Số cột (máy tính bảng)',
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 4,
				'default' => 3,
			)
		);
		$this->add_control(
			'columns_mobile',
			array(
				'label'   => 'Số cột (điện thoại)',
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 2,
				'default' => 2,
			)
		);
	}

	/** Tab Kiểu: màu và chữ của card. */
	protected function add_card_style_controls() {
		$this->start_controls_section(
			'section_card_style',
			array(
				'label' => 'Card',
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => 'Khoảng cách giữa card',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .lp-grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'card_bg',
			array(
				'label'     => 'Nền card',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-card' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'card_radius',
			array(
				'label'      => 'Bo góc',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'selectors'  => array( '{{WRAPPER}} .lp-card' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => 'Màu tiêu đề',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-card__title a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => 'Chữ tiêu đề',
				'selector' => '{{WRAPPER}} .lp-card__title',
			)
		);
		$this->add_control(
			'price_color',
			array(
				'label'     => 'Màu giá',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-card__price' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'badge_color',
			array(
				'label'     => 'Màu nhãn',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-badge' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/** Chuẩn hóa giá trị switcher Elementor ('yes' / '') sang 'yes' / 'no'. */
	protected function yes_no( $value ) {
		return 'yes' === $value ? 'yes' : 'no';
	}
}
