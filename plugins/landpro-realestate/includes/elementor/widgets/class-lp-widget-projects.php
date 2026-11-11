<?php
/**
 * Widget Elementor: Lưới dự án.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Projects extends LP_Widget_Base {

	public function get_name() {
		return 'lp-projects';
	}

	public function get_title() {
		return 'Lưới dự án';
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return array( 'dự án', 'project', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_query', array( 'label' => 'Nguồn dữ liệu' ) );
		$statuses = lp_project_statuses();
		$statuses[''] = 'Tất cả';
		$this->add_control(
			'status',
			array(
				'label'   => 'Trạng thái',
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $statuses,
			)
		);
		$this->add_control(
			'type',
			array(
				'label'       => 'Loại hình',
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
				'label' => 'Chỉ dự án nổi bật',
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'per_page',
			array(
				'label'   => 'Số dự án',
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
			'tabs',
			array(
				'label'       => 'Tab lọc theo tỉnh/thành',
				'description' => 'Hiện các tab Hồ Chí Minh, Hà Nội... phía trên lưới.',
				'type'        => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'pagination',
			array(
				'label' => 'Phân trang',
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->end_controls_section();

		$this->add_card_style_controls();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo LP_Render::projects( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'status'         => $s['status'],
				'type'           => implode( ',', (array) $s['type'] ),
				'location'       => implode( ',', (array) $s['location'] ),
				'featured'       => $this->yes_no( $s['featured'] ),
				'per_page'       => $s['per_page'],
				'columns'        => $s['columns'],
				'columns_tablet' => $s['columns_tablet'],
				'columns_mobile' => $s['columns_mobile'],
				'pagination'     => $this->yes_no( $s['pagination'] ),
				'tabs'           => $this->yes_no( $s['tabs'] ),
			)
		);
	}
}
