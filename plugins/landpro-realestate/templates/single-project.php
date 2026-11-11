<?php
/**
 * Trang chi tiết dự án: thanh mục lục dính (Tổng quan, Vị trí, Tiện ích, Thiết kế,
 * Sản phẩm, Chính sách, Liên hệ) + các mục tương ứng.
 * Ghi đè: theme/landpro/single-project.php
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$lp_id      = get_the_ID();
	$lp_type    = lp_first_term( $lp_id, 'lp_type' );
	$lp_address = lp_get( 'address' );
	$lp_lines   = function ( $key ) {
		return array_values( array_filter( array_map( 'trim', explode( "\n", (string) lp_get( $key ) ) ) ) );
	};
	$lp_amen    = $lp_lines( 'amenities' );
	$lp_loc     = $lp_lines( 'location_desc' );
	$lp_policy  = $lp_lines( 'policy' );
	$lp_design  = trim( (string) lp_get( 'design' ) );
	$lp_gallery = lp_gallery_ids( $lp_id );

	$lp_specs = array(
		array( 'building', lp_t( 'Chủ đầu tư' ), lp_get( 'developer' ) ),
		array( 'pin', lp_t( 'Vị trí' ), $lp_address ? $lp_address : lp_location_text( $lp_id ) ),
		array( 'house', lp_t( 'Loại hình' ), $lp_type ? $lp_type->name : '' ),
		array( 'area', lp_t( 'Tổng diện tích' ), lp_get( 'area_total' ) ),
		array( 'layers', lp_t( 'Quy mô' ), lp_get( 'scale' ) ),
		array( 'calendar', lp_t( 'Bàn giao' ), lp_get( 'handover' ) ),
		array( 'document', lp_t( 'Pháp lý' ), lp_get( 'legal' ) ),
		array( 'check', lp_t( 'Trạng thái' ), lp_get_label( 'status' ) ),
	);

	$lp_listing = LP_Render::properties(
		array(
			'project'        => $lp_id,
			'per_page'       => 8,
			'columns'        => 3,
			'columns_tablet' => 2,
			'columns_mobile' => 2,
			'empty_text'     => lp_t( 'Hiện chưa có sản phẩm đang giao dịch. Liên hệ để nhận danh sách căn mới nhất.' ),
		)
	);

	// Mục lục: chỉ hiện mục có nội dung (Tổng quan, Sản phẩm, Liên hệ luôn có).
	$lp_nav = array(
		'tong-quan'  => lp_t( 'Tổng quan' ),
		'vi-tri'     => ( $lp_address || $lp_loc ) ? lp_t( 'Vị trí' ) : '',
		'tien-ich'   => $lp_amen ? lp_t( 'Tiện ích' ) : '',
		'thiet-ke'   => ( $lp_design || count( $lp_gallery ) > 1 ) ? lp_t( 'Thiết kế' ) : '',
		'san-pham'   => lp_t( 'Sản phẩm' ),
		'chinh-sach' => $lp_policy ? lp_t( 'Chính sách' ) : '',
		'lien-he'    => lp_t( 'Liên hệ' ),
	);
	$lp_nav = array_filter( $lp_nav );
	$lp_sec = function ( $id, $title ) {
		printf( '<h2 class="lp-section__title" id="%1$s-title">%2$s</h2>', esc_attr( $id ), esc_html( $title ) );
	};
	?>
	<main id="content" class="lp-single lp-single--project">
		<div class="lp-container">
			<?php LP_Templates::breadcrumb(); ?>

			<header class="lp-single__header">
				<div class="lp-single__heading">
					<?php if ( lp_get( 'status' ) ) : ?>
						<div class="lp-single__tags"><span class="lp-badge lp-badge--<?php echo esc_attr( lp_get( 'status' ) ); ?>"><?php echo esc_html( lp_get_label( 'status' ) ); ?></span></div>
					<?php endif; ?>
					<h1 class="lp-single__title"><?php the_title(); ?></h1>
					<?php if ( $lp_address ) : ?>
						<p class="lp-single__address"><?php echo lp_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_address ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( lp_get( 'price_text' ) ) : ?>
					<div class="lp-single__price-box">
						<p class="lp-single__unit"><?php echo esc_html( lp_t( 'Giá bán' ) ); ?></p>
						<p class="lp-single__price"><?php echo esc_html( lp_get( 'price_text' ) ); ?></p>
					</div>
				<?php endif; ?>
			</header>
			<?php LP_Legal::listing_notice(); ?>

			<?php LP_Templates::gallery( $lp_id ); ?>
		</div>

		<nav class="lp-subnav" aria-label="<?php echo esc_attr( lp_t( 'Mục lục dự án' ) ); ?>" data-lp-subnav>
			<div class="lp-container">
				<ul class="lp-subnav__list">
					<?php foreach ( $lp_nav as $lp_key => $lp_label ) : ?>
						<li><a href="#<?php echo esc_attr( $lp_key ); ?>"><?php echo esc_html( $lp_label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</nav>

		<div class="lp-container">
			<div class="lp-single__layout">
				<div class="lp-single__main">

					<section class="lp-section lp-anchor" id="tong-quan" aria-labelledby="tong-quan-title">
						<?php $lp_sec( 'tong-quan', lp_t( 'Tổng quan dự án' ) ); ?>
						<dl class="lp-specs">
							<?php foreach ( $lp_specs as $lp_spec ) : ?>
								<?php
								if ( '' === (string) $lp_spec[2] ) {
									continue;
								}
								?>
								<div class="lp-specs__item">
									<dt><?php echo lp_color_icon( $lp_spec[0], 30 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_spec[1] ); ?></dt>
									<dd><?php echo esc_html( $lp_spec[2] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
						<?php if ( get_the_content() ) : ?>
							<div class="lp-content lp-section__intro"><?php the_content(); ?></div>
						<?php endif; ?>
						<?php if ( lp_get( 'video_url' ) ) : ?>
							<?php LP_Templates::video( lp_get( 'video_url' ), get_the_title() ); ?>
						<?php endif; ?>
					</section>

					<?php if ( isset( $lp_nav['vi-tri'] ) ) : ?>
						<section class="lp-section lp-anchor" id="vi-tri" aria-labelledby="vi-tri-title">
							<?php $lp_sec( 'vi-tri', lp_t( 'Vị trí dự án' ) ); ?>
							<?php
							if ( $lp_address ) {
								array_unshift( $lp_loc, $lp_address ); // Địa chỉ là dòng đầu của danh sách vị trí.
							}
							?>
							<?php if ( $lp_loc ) : ?>
								<ul class="lp-checklist">
									<?php foreach ( $lp_loc as $lp_item ) : ?>
										<li><?php echo lp_color_icon( 'pin-soft', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $lp_item ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php LP_Templates::map( $lp_address ); ?>
						</section>
					<?php endif; ?>

					<?php if ( isset( $lp_nav['tien-ich'] ) ) : ?>
						<section class="lp-section lp-anchor" id="tien-ich" aria-labelledby="tien-ich-title">
							<?php $lp_sec( 'tien-ich', lp_t( 'Tiện ích nổi bật' ) ); ?>
							<ul class="lp-amenities">
								<?php foreach ( $lp_amen as $lp_item ) : ?>
									<li><?php echo lp_color_icon( 'tick', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $lp_item ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<?php if ( isset( $lp_nav['thiet-ke'] ) ) : ?>
						<section class="lp-section lp-anchor" id="thiet-ke" aria-labelledby="thiet-ke-title">
							<?php $lp_sec( 'thiet-ke', lp_t( 'Thiết kế' ) ); ?>
							<?php if ( $lp_design ) : ?>
								<div class="lp-content"><?php echo wp_kses_post( wpautop( $lp_design ) ); ?></div>
							<?php endif; ?>
							<?php if ( count( $lp_gallery ) > 1 ) : ?>
								<div class="lp-design-grid" data-lp-gallery>
									<?php foreach ( array_slice( $lp_gallery, 1, 3 ) as $lp_i => $lp_img ) : ?>
										<a class="lp-gallery__item" data-elementor-open-lightbox="no" href="<?php echo esc_url( wp_get_attachment_image_url( $lp_img, 'full' ) ); ?>" data-thumb="<?php echo esc_url( wp_get_attachment_image_url( $lp_img, 'thumbnail' ) ); ?>">
											<?php echo wp_get_attachment_image( $lp_img, 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<section class="lp-section lp-anchor" id="san-pham" aria-labelledby="san-pham-title">
						<?php $lp_sec( 'san-pham', sprintf( lp_t( 'Sản phẩm đang giao dịch tại %s' ), get_the_title() ) ); ?>
						<?php echo $lp_listing; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</section>

					<?php if ( isset( $lp_nav['chinh-sach'] ) ) : ?>
						<section class="lp-section lp-anchor" id="chinh-sach" aria-labelledby="chinh-sach-title">
							<?php $lp_sec( 'chinh-sach', lp_t( 'Chính sách bán hàng' ) ); ?>
							<ul class="lp-checklist lp-checklist--policy">
								<?php foreach ( $lp_policy as $lp_item ) : ?>
									<li><?php echo lp_color_icon( 'price', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $lp_item ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<section class="lp-section lp-anchor" id="lien-he" aria-labelledby="lien-he-title">
						<?php $lp_sec( 'lien-he', lp_t( 'Liên hệ' ) ); ?>
						<div class="lp-contact lp-contact--inline">
							<?php LP_Templates::contact_box( 'Nhận bảng giá & tư vấn' ); ?>
						</div>
					</section>
				</div>

				<aside class="lp-single__aside" aria-label="<?php echo esc_attr( lp_t( 'Liên hệ nhanh' ) ); ?>">
					<?php LP_Templates::quick_contact( lp_get( 'price_text' ) ); ?>
				</aside>
			</div>

			<?php
			$lp_more = LP_Render::projects(
				array(
					'per_page'   => 8,
					'slider'     => 'auto',
					'exclude'    => array( $lp_id ),
					'empty_text' => '',
				)
			);
			if ( false !== strpos( $lp_more, 'lp-pcard' ) ) :
				?>
				<section class="lp-section lp-related" aria-labelledby="lp-more-title">
					<h2 id="lp-more-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Dự án khác' ) ); ?></h2>
					<?php echo $lp_more; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
