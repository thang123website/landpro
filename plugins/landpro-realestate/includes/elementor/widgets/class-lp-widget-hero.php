<?php
/**
 * Widget Elementor: Slider đầu trang toàn màn hình (ảnh hoặc video) + tiêu đề + ô tìm kiếm.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

class LP_Widget_Hero extends LP_Widget_Base {

	public function get_name() {
		return 'lp-hero';
	}

	public function get_title() {
		return 'Slider đầu trang';
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_keywords() {
		return array( 'slider', 'hero', 'banner', 'video', 'landpro' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_slides', array( 'label' => 'Slide' ) );
		$rep = new Repeater();
		$rep->add_control(
			'media_type',
			array(
				'label'   => 'Loại',
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => array(
					'image' => 'Ảnh',
					'video' => 'Video (MP4)',
				),
			)
		);
		$rep->add_control(
			'image',
			array(
				'label'       => 'Ảnh (với video: ảnh hiện trong lúc tải)',
				'description' => 'Kích thước chuẩn: 1920 × 1080 px (16:9), WebP/JPG ≤ 500 KB. Đặt chủ thể ở giữa ảnh vì điện thoại sẽ cắt bớt hai bên; tránh chữ trong ảnh.',
				'type'        => Controls_Manager::MEDIA,
				'label_block' => true,
			)
		);
		$rep->add_control(
			'video',
			array(
				'label'       => 'Video MP4',
				'description' => 'Chọn file MP4 trong thư viện hoặc dán đường dẫn. Video tự phát, tắt tiếng, lặp lại. Nên dưới 10MB.',
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);
		$rep->add_control(
			'video_mobile',
			array(
				'label'       => 'Video MP4 cho điện thoại (tùy chọn, bản nhẹ 720p)',
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);
		$rep->add_control(
			'alt',
			array(
				'label' => 'Mô tả ảnh (alt)',
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'slides',
			array(
				'label'       => 'Danh sách slide',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ media_type === "video" ? "🎬 Video" : "🖼 Ảnh" }}} {{{ alt }}}',
			)
		);
		$this->add_control(
			'autoplay',
			array(
				'label'   => 'Thời gian mỗi slide (giây)',
				'type'    => Controls_Manager::NUMBER,
				'min'     => 3,
				'max'     => 20,
				'default' => 6,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_content', array( 'label' => 'Nội dung' ) );
		$this->add_control(
			'title',
			array(
				'label'       => 'Tiêu đề',
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => 'Mua bán, cho thuê nhà đất',
			)
		);
		$this->add_control(
			'subtitle',
			array(
				'label'   => 'Mô tả',
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => '',
			)
		);
		$this->add_control(
			'search',
			array(
				'label'   => 'Hiện ô tìm kiếm',
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->end_controls_section();

		// Gợi ý dưới thanh tìm kiếm: bấm vào sẽ điền chữ vào ô tìm kiếm.
		$this->start_controls_section(
			'section_suggest',
			array(
				'label'     => 'Gợi ý tìm kiếm',
				'condition' => array( 'search' => 'yes' ),
			)
		);
		$sug = new Repeater();
		$sug->add_control(
			'text',
			array(
				'label'       => 'Từ khóa gợi ý',
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'Căn hộ Quận 7',
			)
		);
		$this->add_control(
			'suggest_items',
			array(
				'label'       => 'Danh sách gợi ý',
				'description' => 'Để trống danh sách để ẩn hàng gợi ý.',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $sug->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array_map(
					function ( $t ) {
						return array( 'text' => $t );
					},
					array( 'Căn hộ Quận 7', 'Nhà phố Thủ Đức', 'Biệt thự ven sông', 'Đất nền Bình Dương', 'Căn hộ Vũng Tàu', 'Văn phòng Cầu Giấy' )
				),
			)
		);
		$this->add_control(
			'suggest_label',
			array(
				'label'   => 'Nhãn đầu hàng',
				'type'    => Controls_Manager::TEXT,
				'default' => 'Gợi ý:',
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
			'overlay',
			array(
				'label'     => 'Độ tối lớp phủ',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 0.9, 'step' => 0.05 ) ),
				'default'   => array( 'size' => 0.5 ),
				'selectors' => array( '{{WRAPPER}} .lp-hero__shade' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => 'Chiều cao',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array( 'vh' => array( 'min' => 50, 'max' => 100 ), 'px' => array( 'min' => 400, 'max' => 1200 ) ),
				'default'    => array( 'unit' => 'vh', 'size' => 100 ),
				'selectors'  => array( '{{WRAPPER}} .lp-hero' => '--lp-hero-h: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$slides = array_values( array_filter( (array) $s['slides'], function ( $sl ) {
			return ! empty( $sl['image']['url'] ) || ! empty( $sl['video']['url'] );
		} ) );
		LP_Render::enqueue();
		$uid = 'lp-hero-' . $this->get_id();
		?>
		<section class="lp-hero" id="<?php echo esc_attr( $uid ); ?>" data-lp-hero data-interval="<?php echo esc_attr( max( 3, (int) $s['autoplay'] ) * 1000 ); ?>" aria-roledescription="carousel" aria-label="<?php echo esc_attr( wp_strip_all_tags( $s['title'] ) ); ?>">
			<div class="lp-hero__slides">
				<?php foreach ( $slides as $i => $sl ) : ?>
					<div class="lp-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( ( $i + 1 ) . ' / ' . count( $slides ) ); ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
						<?php
						$img_id = ! empty( $sl['image']['id'] ) ? (int) $sl['image']['id'] : 0;
						$alt    = $sl['alt'] ? $sl['alt'] : ( $img_id ? get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '' );
						if ( 'video' === $sl['media_type'] && ! empty( $sl['video']['url'] ) ) {
							// Ảnh poster là ảnh thật (LCP, SEO); video chỉ tải sau khi trang tải xong và phủ lên khi đã phát.
							if ( $img_id ) {
								echo wp_get_attachment_image(
									$img_id,
									'full',
									false,
									array(
										'class'         => 0 === $i ? 'lp-hero__media skip-lazy' : 'lp-hero__media',
										'data-no-lazy'  => '1', // Plugin cache không thay lazy-load riêng; ảnh sau vẫn lazy bằng thuộc tính gốc.
										'alt'           => $alt,
										'loading'       => 0 === $i ? 'eager' : 'lazy',
										'fetchpriority' => 0 === $i ? 'high' : 'auto',
										'sizes'         => '100vw',
									)
								);
							}
							printf(
								'<video class="lp-hero__media lp-hero__video" muted loop playsinline preload="none" disablepictureinpicture aria-hidden="true" tabindex="-1" data-src="%1$s" data-src-mobile="%2$s"%3$s></video>',
								esc_url( $sl['video']['url'] ),
								esc_url( ! empty( $sl['video_mobile']['url'] ) ? $sl['video_mobile']['url'] : $sl['video']['url'] ),
								! empty( $sl['image']['url'] ) ? ' poster="' . esc_url( $sl['image']['url'] ) . '"' : ''
							);
						} elseif ( $img_id ) {
							echo wp_get_attachment_image(
								$img_id,
								'full',
								false,
								array(
									'class'         => 0 === $i ? 'lp-hero__media skip-lazy' : 'lp-hero__media',
										'data-no-lazy'  => '1', // Plugin cache không thay lazy-load riêng; ảnh sau vẫn lazy bằng thuộc tính gốc.
									'alt'           => $alt,
									'loading'       => 0 === $i ? 'eager' : 'lazy',
									'fetchpriority' => 0 === $i ? 'high' : 'auto',
									'sizes'         => '100vw',
								)
							);
						} else {
							printf( '<img class="lp-hero__media" src="%s" alt="%s">', esc_url( $sl['image']['url'] ), esc_attr( $alt ) );
						}
						?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="lp-hero__shade" aria-hidden="true"></div>
			<div class="lp-hero__content">
				<div class="lp-container">
					<?php if ( $s['title'] ) : ?>
						<h1 class="lp-hero__title"><?php echo esc_html( lp_t( $s['title'] ) ); ?></h1>
					<?php endif; ?>
					<?php if ( $s['subtitle'] ) : ?>
						<p class="lp-hero__sub"><?php echo esc_html( lp_t( $s['subtitle'] ) ); ?></p>
					<?php endif; ?>
					<?php
					if ( 'yes' === $s['search'] ) {
						echo LP_Render::search_form( // phpcs:ignore WordPress.Security.EscapeOutput
							array(
								'tabs'    => 'yes',
								'fields'  => 'kw,type,location,price',
								'action'  => get_post_type_archive_link( 'property' ),
								'suggest'       => implode( "\n", wp_list_pluck( (array) $s['suggest_items'], 'text' ) ),
								'suggest_label' => isset( $s['suggest_label'] ) ? $s['suggest_label'] : 'Gợi ý:',
							)
						);
					}
					?>
				</div>
			</div>
			<?php if ( count( $slides ) > 1 ) : ?>
				<div class="lp-hero__nav">
					<button type="button" class="lp-hero__arrow" data-dir="-1" aria-label="<?php echo esc_attr( lp_t( 'Slide trước' ) ); ?>"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg></button>
					<div class="lp-hero__dots" role="tablist">
						<?php foreach ( $slides as $i => $sl ) : ?>
							<button type="button" class="lp-hero__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-go="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( 'Slide %d', $i + 1 ) ); ?>"></button>
						<?php endforeach; ?>
					</div>
					<button type="button" class="lp-hero__arrow" data-dir="1" aria-label="<?php echo esc_attr( lp_t( 'Slide sau' ) ); ?>"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg></button>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}
