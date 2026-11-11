<?php
/**
 * Trang chi tiết nhà đất. Ghi đè: theme/landpro/single-property.php
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
	$lp_purpose = lp_first_term( $lp_id, 'lp_purpose' );
	$lp_type    = lp_first_term( $lp_id, 'lp_type' );
	$lp_address = lp_get( 'address' );
	$lp_area    = lp_get( 'area' );
	$lp_price   = (float) lp_get( 'price' );
	$lp_project = (int) lp_get( 'project_id' );

	$lp_specs = array(
		array( 'area', lp_t( 'Diện tích' ), $lp_area ? lp_number( $lp_area ) . ' m²' : '' ),
		array( 'bed', lp_t( 'Phòng ngủ' ), lp_get( 'bedrooms' ) ),
		array( 'bath', lp_t( 'Phòng tắm / WC' ), lp_get( 'bathrooms' ) ),
		array( 'layers', lp_t( 'Số tầng' ), lp_get( 'floors' ) ),
		array( 'area', lp_t( 'Mặt tiền' ), lp_get( 'frontage' ) ? lp_number( lp_get( 'frontage' ) ) . ' m' : '' ),
		array( 'compass', lp_t( 'Hướng nhà' ), lp_get_label( 'direction' ) ),
		array( 'document', lp_t( 'Pháp lý' ), lp_get_label( 'legal' ) ),
		array( 'sofa', lp_t( 'Nội thất' ), lp_get( 'furniture' ) ),
		array( 'house', lp_t( 'Loại hình' ), $lp_type ? $lp_type->name : '' ),
		array( 'building', lp_t( 'Dự án' ), $lp_project ? '<a href="' . esc_url( get_permalink( $lp_project ) ) . '">' . esc_html( get_the_title( $lp_project ) ) . '</a>' : '' ),
	);
	?>
	<main id="content" class="lp-single lp-single--property">
		<div class="lp-container">
			<?php LP_Templates::breadcrumb(); ?>

			<header class="lp-single__header">
				<div class="lp-single__heading">
					<div class="lp-single__tags">
						<?php if ( $lp_purpose ) : ?>
							<a class="lp-badge lp-badge--<?php echo esc_attr( lp_base_slug( $lp_purpose ) ); ?>" href="<?php echo esc_url( get_term_link( $lp_purpose ) ); ?>"><?php echo esc_html( $lp_purpose->name ); ?></a>
						<?php endif; ?>
						<?php if ( lp_get( 'badge' ) ) : ?>
							<span class="lp-badge lp-badge--hot"><?php echo esc_html( lp_get( 'badge' ) ); ?></span>
						<?php endif; ?>
					</div>
					<h1 class="lp-single__title"><?php the_title(); ?></h1>
					<?php if ( $lp_address || lp_location_text( $lp_id ) ) : ?>
						<p class="lp-single__address"><?php echo lp_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_address ? $lp_address : lp_location_text( $lp_id ) ); ?></p>
					<?php endif; ?>
				</div>
				<div class="lp-single__price-box">
					<p class="lp-single__price"><?php echo lp_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php if ( $lp_price > 0 && $lp_area > 0 && ! lp_is_rent() ) : ?>
						<p class="lp-single__unit">~ <?php echo esc_html( lp_format_money( $lp_price / $lp_area ) ); ?>/m²</p>
					<?php endif; ?>
				</div>
			</header>
			<?php LP_Legal::listing_notice(); ?>

			<?php LP_Templates::gallery( $lp_id ); ?>

			<div class="lp-single__layout">
				<div class="lp-single__main">
					<section class="lp-section" aria-labelledby="lp-specs-title">
						<h2 id="lp-specs-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Thông tin chính' ) ); ?></h2>
						<dl class="lp-specs">
							<?php foreach ( $lp_specs as $lp_spec ) : ?>
								<?php
								if ( '' === (string) $lp_spec[2] ) {
									continue;
								}
								?>
								<div class="lp-specs__item">
									<dt><?php echo lp_color_icon( $lp_spec[0], 30 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_spec[1] ); ?></dt>
									<dd><?php echo wp_kses_post( $lp_spec[2] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</section>

					<?php if ( get_the_content() ) : ?>
						<section class="lp-section" aria-labelledby="lp-desc-title">
							<h2 id="lp-desc-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Mô tả chi tiết' ) ); ?></h2>
							<div class="lp-content"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<?php if ( lp_get( 'video_url' ) ) : ?>
						<section class="lp-section" aria-labelledby="lp-video-title">
							<h2 id="lp-video-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Video' ) ); ?></h2>
							<?php LP_Templates::video( lp_get( 'video_url' ), get_the_title() ); ?>
						</section>
					<?php endif; ?>

					<?php if ( $lp_address ) : ?>
						<section class="lp-section" aria-labelledby="lp-map-title">
							<h2 id="lp-map-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Vị trí' ) ); ?></h2>
							<?php LP_Templates::map( $lp_address ); ?>
						</section>
					<?php endif; ?>
				</div>

				<aside class="lp-single__aside" aria-label="Liên hệ">
					<?php LP_Templates::contact_box( 'Liên hệ xem nhà' ); ?>
				</aside>
			</div>

			<?php
			$lp_related = LP_Render::properties(
				array(
					'purpose'    => $lp_purpose ? $lp_purpose->slug : '',
					'type'       => $lp_type ? $lp_type->slug : '',
					'per_page'   => 4,
					'exclude'    => array( $lp_id ),
					'empty_text' => '',
				)
			);
			if ( false !== strpos( $lp_related, 'lp-card' ) ) :
				?>
				<section class="lp-section lp-related" aria-labelledby="lp-related-title">
					<h2 id="lp-related-title" class="lp-section__title"><?php echo esc_html( lp_t( 'Bất động sản tương tự' ) ); ?></h2>
					<?php echo $lp_related; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
