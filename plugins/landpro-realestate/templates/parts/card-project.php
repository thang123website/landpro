<?php
/**
 * Card dự án: ảnh dọc tràn khung, chữ trên lớp phủ tối ở đáy.
 * Ghi đè: theme/landpro/parts/card-project.php
 *
 * @var array $args { post_id }
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lp_id       = (int) $args['post_id'];
$lp_status   = lp_get( 'status', $lp_id );
$lp_type     = lp_first_term( $lp_id, 'lp_type' );
$lp_location = lp_location_text( $lp_id );
$lp_price    = lp_get( 'price_text', $lp_id );
$lp_title    = get_the_title( $lp_id );

// Khu vực cấp 1 (tỉnh/thành) để lọc theo tab.
$lp_locs = array();
foreach ( (array) get_the_terms( $lp_id, 'lp_location' ) as $lp_term ) {
	if ( $lp_term && ! is_wp_error( $lp_term ) ) {
		$lp_root   = $lp_term->parent ? get_term( $lp_term->parent, 'lp_location' ) : $lp_term;
		$lp_locs[] = $lp_root->slug;
	}
}
?>
<article class="lp-pcard" data-lp-loc="<?php echo esc_attr( implode( ' ', array_unique( $lp_locs ) ) ); ?>">
	<a class="lp-pcard__link" href="<?php echo esc_url( get_permalink( $lp_id ) ); ?>">
		<?php
		if ( has_post_thumbnail( $lp_id ) ) {
			echo get_the_post_thumbnail(
				$lp_id,
				'full',
				array(
					'class'   => 'lp-pcard__img',
					'loading' => 'lazy',
					'alt'     => $lp_title,
					// Khung dọc phủ ảnh ngang: cần ảnh rộng gấp ~2–3 lần bề ngang card để không bị phóng to (mờ).
					'sizes'   => '(max-width: 600px) 100vw, 1200px',
				)
			);
		}
		?>
		<span class="lp-pcard__badges">
			<?php if ( $lp_type ) : ?>
				<span class="lp-pcard__badge lp-pcard__badge--type"><?php echo esc_html( $lp_type->name ); ?></span>
			<?php endif; ?>
			<?php if ( $lp_status ) : ?>
				<span class="lp-pcard__badge lp-pcard__badge--<?php echo esc_attr( $lp_status ); ?>"><?php echo esc_html( lp_get_label( 'status', $lp_id ) ); ?></span>
			<?php endif; ?>
		</span>
		<span class="lp-pcard__body">
			<span class="lp-pcard__title"><?php echo esc_html( $lp_title ); ?></span>
			<?php if ( $lp_location ) : ?>
				<span class="lp-pcard__location"><?php echo esc_html( $lp_location ); ?></span>
			<?php endif; ?>
			<?php if ( $lp_price ) : ?>
				<span class="lp-pcard__price"><?php echo esc_html( $lp_price ); ?></span>
			<?php endif; ?>
		</span>
	</a>
</article>
