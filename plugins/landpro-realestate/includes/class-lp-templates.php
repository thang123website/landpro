<?php
/**
 * Dùng template của plugin cho trang chi tiết / danh sách nhà đất, dự án.
 * Theme có thể ghi đè bằng cách tạo file cùng tên trong thư mục landpro/.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Templates {

	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'get_the_archive_title', array( __CLASS__, 'archive_title' ) );
		add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		add_action( 'wp_footer', array( __CLASS__, 'floating' ), 5 );
	}

	public static function template( $template ) {
		// Bài dựng bằng Elementor thì để Elementor xử lý.
		if ( is_singular() && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor() ) {
			return $template;
		}
		if ( is_singular( 'property' ) ) {
			return lp_locate_template( 'single-property.php' );
		}
		if ( is_singular( 'project' ) ) {
			return lp_locate_template( 'single-project.php' );
		}
		if ( is_post_type_archive( 'property' ) || is_tax( array( 'lp_purpose', 'lp_type', 'lp_location' ) ) ) {
			return lp_locate_template( 'archive-property.php' );
		}
		return $template;
	}

	public static function body_class( $classes ) {
		if ( is_singular( array( 'property', 'project' ) ) || is_post_type_archive( 'property' ) || is_tax( array( 'lp_purpose', 'lp_type', 'lp_location' ) ) ) {
			$classes[] = 'landpro';
		}
		return $classes;
	}

	public static function archive_title( $title ) {
		if ( is_post_type_archive( 'property' ) ) {
			return lp_t( 'Nhà đất' );
		}
		if ( is_tax( array( 'lp_purpose', 'lp_type', 'lp_location' ) ) ) {
			return single_term_title( '', false );
		}
		return $title;
	}

	/**
	 * Breadcrumb dùng chung cho mọi trang con.
	 * Dùng Yoast (có schema BreadcrumbList) nếu bật, ngược lại tự dựng.
	 *
	 * @param string $variant '' (chữ tối) hoặc 'light' (chữ sáng trên banner tối).
	 * @param bool   $echo    In ra hay trả về chuỗi.
	 * @return string
	 */
	public static function breadcrumb( $variant = '', $echo = true ) {
		$class = 'lp-breadcrumb' . ( 'light' === $variant ? ' lp-breadcrumb--light' : '' );
		$html  = '';
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			$html = yoast_breadcrumb( '<nav class="' . esc_attr( $class ) . '" aria-label="Breadcrumb">', '</nav>', false );
		}
		if ( ! $html ) {
			$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( lp_t( 'Trang chủ' ) ) . '</a>' );
			if ( is_singular( 'property' ) ) {
				$purpose = lp_first_term( get_the_ID(), 'lp_purpose' );
				if ( $purpose ) {
					$items[] = '<a href="' . esc_url( get_term_link( $purpose ) ) . '">' . esc_html( $purpose->name ) . '</a>';
				}
			} elseif ( is_singular( 'post' ) && get_option( 'page_for_posts' ) ) {
				$items[] = '<a href="' . esc_url( get_permalink( get_option( 'page_for_posts' ) ) ) . '">' . esc_html( get_the_title( get_option( 'page_for_posts' ) ) ) . '</a>';
			} elseif ( is_page() ) {
				foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $anc ) {
					$items[] = '<a href="' . esc_url( get_permalink( $anc ) ) . '">' . esc_html( get_the_title( $anc ) ) . '</a>';
				}
			}
			$items[] = '<span class="breadcrumb_last" aria-current="page">' . esc_html( self::current_title() ) . '</span>';
			$html    = '<nav class="' . esc_attr( $class ) . '" aria-label="Breadcrumb">' . implode( ' <span class="lp-breadcrumb__sep">›</span> ', $items ) . '</nav>';
		}
		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape.
		}
		return $html;
	}

	/** Tiêu đề của trang hiện tại (dùng cho breadcrumb và dải tiêu đề). */
	public static function current_title() {
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			return get_the_title( get_option( 'page_for_posts' ) );
		}
		if ( is_search() ) {
			/* translators: %s: từ khóa */
			return sprintf( lp_t( 'Kết quả tìm kiếm: %s' ), get_search_query() );
		}
		if ( is_404() ) {
			return lp_t( 'Không tìm thấy trang' );
		}
		if ( is_singular() ) {
			return get_the_title();
		}
		return wp_strip_all_tags( get_the_archive_title() );
	}

	/** Hộp liên hệ (nhân viên tư vấn + nút gọi/Zalo + form). */
	public static function contact_box( $title = 'Liên hệ tư vấn' ) {
		$title = lp_t( $title );
		$phone = lp_option( 'hotline', '' );
		$zalo  = lp_option( 'zalo', $phone );
		$name  = lp_t( lp_option( 'agent_name', get_bloginfo( 'name' ) ) );
		?>
		<div class="lp-contact">
			<p class="lp-contact__title"><?php echo esc_html( $title ); ?></p>
			<p class="lp-contact__agent"><?php echo esc_html( $name ); ?></p>
			<div class="lp-contact__actions">
				<?php if ( $phone ) : ?>
					<a class="lp-btn lp-btn--primary" href="tel:<?php echo esc_attr( lp_phone_digits( $phone ) ); ?>"><?php echo lp_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php if ( $zalo ) : ?>
					<a class="lp-btn lp-btn--outline" href="<?php echo esc_url( 'https://zalo.me/' . lp_phone_digits( $zalo ) ); ?>" target="_blank" rel="noopener nofollow"><?php echo lp_icon( 'zalo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( lp_t( 'Chat Zalo' ) ); ?></a>
				<?php endif; ?>
			</div>
			<div class="lp-contact__form"><?php echo LP_Shortcodes::contact_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<?php
	}

	/** Hộp liên hệ nhanh ở cột phải trang dự án: giá + gọi + Zalo + nút tới form. */
	public static function quick_contact( $price = '' ) {
		$phone = lp_option( 'hotline', '' );
		$zalo  = lp_option( 'zalo', $phone );
		echo '<div class="lp-quick">';
		if ( $price ) {
			echo '<p class="lp-quick__label">' . esc_html( lp_t( 'Giá bán' ) ) . '</p><p class="lp-quick__price">' . esc_html( $price ) . '</p>';
		}
		echo '<div class="lp-contact__actions">';
		if ( $phone ) {
			echo '<a class="lp-btn" href="tel:' . esc_attr( lp_phone_digits( $phone ) ) . '">' . lp_icon( 'phone' ) . esc_html( $phone ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $zalo ) {
			echo '<a class="lp-btn lp-btn--outline" href="' . esc_url( 'https://zalo.me/' . lp_phone_digits( $zalo ) ) . '" target="_blank" rel="noopener nofollow">' . lp_icon( 'zalo' ) . esc_html( lp_t( 'Chat Zalo' ) ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<a class="lp-btn lp-btn--ghost" href="#lien-he">' . esc_html( lp_t( 'Nhận bảng giá & tư vấn' ) ) . '</a>';
		echo '</div></div>';
	}

	/** Nút liên hệ nổi (gọi, Zalo) + nút về đầu trang, hiện trên mọi trang. */
	public static function floating() {
		if ( is_admin() || ( isset( $_GET['elementor-preview'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$phone = lp_option( 'hotline', '' );
		$zalo  = lp_option( 'zalo', $phone );
		echo '<div class="lp-float" role="complementary" aria-label="' . esc_attr( lp_t( 'Liên hệ nhanh' ) ) . '">';
		if ( $zalo ) {
			printf( '<a class="lp-float__zalo" href="%s" target="_blank" rel="noopener nofollow" aria-label="%s"><img src="%s" width="48" height="48" alt=""></a>', esc_url( 'https://zalo.me/' . lp_phone_digits( $zalo ) ), esc_attr( lp_t( 'Chat Zalo' ) ), esc_url( LP_URL . 'assets/img/zalo.svg' ) );
		}
		if ( $phone ) {
			printf( '<a class="lp-float__phone" href="tel:%1$s" aria-label="%2$s"><span class="lp-float__ring">%3$s</span><span class="lp-float__num">%4$s</span></a>', esc_attr( lp_phone_digits( $phone ) ), esc_attr( lp_t( 'Gọi hotline' ) . ' ' . $phone ), lp_icon( 'phone' ), esc_html( $phone ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		printf( '<button type="button" class="lp-totop" aria-label="%s" hidden><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 15 6-6 6 6"/></svg></button>', esc_attr( lp_t( 'Về đầu trang' ) ) );
	}

	/** Thư viện ảnh: ảnh lớn + dải ảnh nhỏ, bấm để phóng to. */
	public static function gallery( $post_id ) {
		$ids = lp_gallery_ids( $post_id );
		if ( ! $ids ) {
			return;
		}
		$title = get_the_title( $post_id );
		$total = count( $ids );
		echo '<div class="lp-gallery" data-lp-gallery>';
		echo '<div class="lp-gallery__grid lp-gallery__grid--' . esc_attr( min( $total, 5 ) ) . '">';
		foreach ( array_slice( $ids, 0, 5 ) as $i => $img ) {
			$full = wp_get_attachment_image_url( $img, 'full' );
			$alt  = get_post_meta( $img, '_wp_attachment_image_alt', true );
			$alt  = $alt ? $alt : sprintf( lp_t( '%1$s - ảnh %2$d' ), $title, $i + 1 );
			echo '<a class="lp-gallery__item" data-elementor-open-lightbox="no" href="' . esc_url( $full ) . '" data-thumb="' . esc_url( wp_get_attachment_image_url( $img, 'thumbnail' ) ) . '" data-index="' . (int) $i . '">';
			echo wp_get_attachment_image(
				$img,
				0 === $i ? 'large' : 'medium_large',
				false,
				array(
					'alt'           => $alt,
					'loading'       => 0 === $i ? 'eager' : 'lazy',
					'fetchpriority' => 0 === $i ? 'high' : 'auto',
					'sizes'         => 0 === $i ? '(max-width: 768px) 100vw, 800px' : '(max-width: 768px) 50vw, 400px',
				)
			);
			if ( 0 === $i && $total > 1 ) { // Nhãn tổng số ảnh ở ảnh lớn.
				echo '<span class="lp-gallery__count"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M4 8h3l1.5-2h7L17 8h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg>' . esc_html( sprintf( lp_t( '%d ảnh' ), $total ) ) . '</span>';
			}
			if ( 4 === $i && $total > 5 ) {
				echo '<span class="lp-gallery__more"><b>+' . (int) ( $total - 5 ) . '</b>' . esc_html( lp_t( 'Xem tất cả ảnh' ) ) . '</span>';
			}
			echo '</a>';
		}
		// Ảnh còn lại: chỉ đưa URL vào lightbox.
		foreach ( array_slice( $ids, 5 ) as $i => $img ) {
			$alt = get_post_meta( $img, '_wp_attachment_image_alt', true );
			echo '<a class="lp-gallery__item lp-gallery__item--hidden" data-elementor-open-lightbox="no" href="' . esc_url( wp_get_attachment_image_url( $img, 'full' ) ) . '" data-thumb="' . esc_url( wp_get_attachment_image_url( $img, 'thumbnail' ) ) . '" data-alt="' . esc_attr( $alt ? $alt : sprintf( lp_t( '%1$s - ảnh %2$d' ), $title, $i + 6 ) ) . '" data-index="' . (int) ( $i + 5 ) . '" aria-hidden="true" tabindex="-1"></a>';
		}
		echo '</div></div>';
	}

	/** Video YouTube nhúng dạng "lite" (chỉ tải iframe khi bấm). */
	public static function video( $url, $title ) {
		$id = lp_youtube_id( $url );
		if ( ! $id ) {
			return;
		}
		printf(
			'<button type="button" class="lp-video" data-lp-youtube="%1$s" aria-label="%5$s"><img src="%3$s" alt="%5$s" loading="lazy" width="480" height="360"><span class="lp-video__play">%4$s</span></button>',
			esc_attr( $id ),
			esc_attr( $title ),
			esc_url( 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg' ),
			lp_icon( 'play' ), // phpcs:ignore WordPress.Security.EscapeOutput
			esc_attr( sprintf( lp_t( 'Phát video %s' ), $title ) )
		);
	}

	/** Bản đồ Google nhúng theo địa chỉ (không cần API key). */
	public static function map( $address, $post_id = 0 ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		if ( ! LP_Map::enabled( 'project' === get_post_type( $post_id ) ? 'project' : 'property' ) ) {
			return; // Tắt bản đồ ở trang chi tiết (Nhà đất → Bản đồ).
		}
		$marker = LP_Map::marker( $post_id );
		if ( $marker ) {
			echo LP_Map::render( array( $marker ), 0, true ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}
		if ( ! $address ) {
			return;
		}
		printf(
			'<div class="lp-map"><iframe title="' . esc_attr( lp_t( 'Bản đồ vị trí' ) ) . '" src="%s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>',
			esc_url( 'https://maps.google.com/maps?q=' . rawurlencode( $address ) . '&z=15&output=embed' )
		);
	}
}

/**
 * Logo trong header/footer (widget Site Logo của Header Footer Builder) gắn alt "default-logo"
 * khi dùng logo của website: thay bằng alt của ảnh logo, nếu trống thì dùng tên website (SEO, trợ năng).
 */
add_filter(
	'elementor/widget/render_content',
	function ( $content, $widget ) {
		if ( false === strpos( $content, 'alt="default-logo"' ) ) {
			return $content;
		}
		$logo = (int) get_theme_mod( 'custom_logo' );
		$alt  = $logo ? trim( (string) get_post_meta( $logo, '_wp_attachment_image_alt', true ) ) : '';
		$alt  = '' !== $alt ? $alt : get_bloginfo( 'name' );
		return str_replace( 'alt="default-logo"', 'alt="' . esc_attr( $alt ) . '"', $content );
	},
	10,
	2
);
