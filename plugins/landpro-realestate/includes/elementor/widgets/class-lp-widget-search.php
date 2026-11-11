<?php
/**
 * Widget Elementor: Form tìm kiếm nhà đất.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class LP_Widget_Search extends LP_Widget_Base {

	public function get_name() {
		return 'lp-search';
	}

	public function get_title() {
		return 'Tìm kiếm nhà đất';
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_keywords() {
		return array( 'tìm kiếm', 'search', 'bộ lọc', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_form', array( 'label' => 'Form' ) );
		$this->add_control(
			'tabs',
			array(
				'label'       => 'Hiện tab Tất cả / Mua / Thuê',
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => 'Khi bật, kết quả hiển thị ở trang danh sách /nha-dat/.',
			)
		);
		$this->add_control(
			'purpose',
			array(
				'label'     => 'Cố định nhu cầu',
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array( '' => 'Không' ) + $this->term_choices( 'lp_purpose' ),
				'condition' => array( 'tabs!' => 'yes' ),
			)
		);
		$this->add_control(
			'fields',
			array(
				'label'       => 'Trường hiển thị',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => array( 'kw', 'type', 'location', 'price' ),
				'options'     => array(
					'kw'       => 'Từ khóa',
					'type'     => 'Loại nhà đất',
					'location' => 'Khu vực',
					'price'    => 'Mức giá',
					'area'     => 'Diện tích',
					'bed'      => 'Phòng ngủ',
				),
			)
		);
		$this->add_control(
			'action_type',
			array(
				'label'   => 'Hiển thị kết quả tại',
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'    => 'Tự động',
					'current' => 'Ngay trang này (cần widget Lưới nhà đất bật "Lọc theo form")',
					'archive' => 'Trang danh sách /nha-dat/',
					'custom'  => 'Đường dẫn tùy chọn',
				),
			)
		);
		$this->add_control(
			'action_url',
			array(
				'label'     => 'Đường dẫn',
				'type'      => Controls_Manager::URL,
				'condition' => array( 'action_type' => 'custom' ),
			)
		);
		$this->add_control(
			'button_text',
			array(
				'label'   => 'Chữ trên nút',
				'type'    => Controls_Manager::TEXT,
				'default' => 'Tìm kiếm',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => 'Kiểu',
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'form_bg',
			array(
				'label'     => 'Nền form',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-search__fields' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_bg',
			array(
				'label'     => 'Màu nút',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-search__submit' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'tab_color',
			array(
				'label'     => 'Màu chữ tab',
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lp-search__tab span' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		switch ( $s['action_type'] ) {
			case 'current':
				$action = LP_Render::current_url();
				break;
			case 'archive':
				$action = get_post_type_archive_link( 'property' );
				break;
			case 'custom':
				$action = ! empty( $s['action_url']['url'] ) ? $s['action_url']['url'] : '';
				break;
			default:
				$action = '';
		}
		echo LP_Render::search_form( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'action'      => $action,
				'tabs'        => $this->yes_no( $s['tabs'] ),
				'purpose'     => 'yes' === $s['tabs'] ? '' : $s['purpose'],
				'fields'      => implode( ',', (array) $s['fields'] ),
				'button_text' => $s['button_text'],
			)
		);
	}
}
