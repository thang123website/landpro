<?php
/**
 * Widget Elementor: Lưới nhà đất.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Properties extends LP_Widget_Base {

	public function get_name() {
		return 'lp-properties';
	}

	public function get_title() {
		return 'Lưới nhà đất';
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_keywords() {
		return array( 'nhà đất', 'bất động sản', 'property', 'bán', 'thuê', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => 'Nguồn dữ liệu' ) );

		$this->add_control(
			'purpose',
			array(
				'label'   => 'Nhu cầu',
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array( '' => 'Tất cả' ) + $this->term_choices( 'lp_purpose' ),
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
			'featured',
			array(
				'label' => 'Chỉ tin nổi bật',
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'   => 'Sắp xếp',
				'type'    => Controls_Manager::SELECT,
				'default' => 'newest',
				'options' => LP_Query::sorts() + array( 'random' => 'Ngẫu nhiên' ),
			)
		);
		$this->add_control(
			'per_page',
			array(
				'label'   => 'Số tin',
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 48,
				'default' => 6,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => 'Bố cục' ) );
		$this->add_columns_controls();
		$this->add_control(
			'pagination',
			array(
				'label' => 'Phân trang',
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'use_filters',
			array(
				'label'       => 'Lọc theo form tìm kiếm trên trang',
				'description' => 'Bật khi trang có widget "Tìm kiếm nhà đất" để kết quả lọc hiển thị ngay tại đây.',
				'type'        => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'show_toolbar',
			array(
				'label' => 'Hiện số kết quả + sắp xếp',
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'empty_text',
			array(
				'label'   => 'Thông báo khi không có tin',
				'type'    => Controls_Manager::TEXT,
				'default' => 'Chưa có tin nhà đất phù hợp.',
			)
		);
		$this->end_controls_section();

		$this->add_card_style_controls();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo LP_Render::properties( // phpcs:ignore WordPress.Security.EscapeOutput -- HTML đã escape trong template.
			array(
				'purpose'        => $s['purpose'],
				'type'           => implode( ',', (array) $s['type'] ),
				'location'       => implode( ',', (array) $s['location'] ),
				'featured'       => $this->yes_no( $s['featured'] ),
				'orderby'        => $s['orderby'],
				'per_page'       => $s['per_page'],
				'columns'        => $s['columns'],
				'columns_tablet' => $s['columns_tablet'],
				'columns_mobile' => $s['columns_mobile'],
				'pagination'     => $this->yes_no( $s['pagination'] ),
				'use_filters'    => $this->yes_no( $s['use_filters'] ),
				'show_toolbar'   => $this->yes_no( $s['show_toolbar'] ),
				'empty_text'     => $s['empty_text'],
			)
		);
	}
}
