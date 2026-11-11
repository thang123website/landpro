<?php
/**
 * Card nhà đất trong lưới. Ghi đè: theme/landpro/parts/card-property.php
 *
 * @var array $args { post_id }
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lp_id       = (int) $args['post_id'];
$lp_link     = get_permalink( $lp_id );
$lp_purpose  = lp_first_term( $lp_id, 'lp_purpose' );
$lp_type     = lp_first_term( $lp_id, 'lp_type' );
$lp_area     = lp_get( 'area', $lp_id );
$lp_bed      = lp_get( 'bedrooms', $lp_id );
$lp_bath     = lp_get( 'bathrooms', $lp_id );
$lp_badge    = lp_get( 'badge', $lp_id );
$lp_location = lp_location_text( $lp_id );
$lp_title    = get_the_title( $lp_id );
?>
<article class="lp-card lp-card--property">
	<a class="lp-card__media" href="<?php echo esc_url( $lp_link ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail( $lp_id ) ) {
			echo get_the_post_thumbnail(
				$lp_id,
				'medium_large',
				array(
					'loading' => 'lazy',
					'alt'     => $lp_title,
					'sizes'   => '(max-width: 600px) 100vw, (max-width: 1024px) 50vw, 400px',
				)
			);
		} else {
			echo '<span class="lp-card__noimg">' . lp_icon( 'building' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
		<?php if ( $lp_purpose ) : ?>
			<span class="lp-badge lp-badge--<?php echo esc_attr( lp_base_slug( $lp_purpose ) ); ?>"><?php echo esc_html( $lp_purpose->name ); ?></span>
		<?php endif; ?>
		<?php if ( $lp_badge ) : ?>
			<span class="lp-badge lp-badge--hot"><?php echo esc_html( $lp_badge ); ?></span>
		<?php endif; ?>
	</a>
	<div class="lp-card__body">
		<?php if ( $lp_type ) : ?>
			<span class="lp-card__type"><?php echo esc_html( $lp_type->name ); ?></span>
		<?php endif; ?>
		<h3 class="lp-card__title"><a href="<?php echo esc_url( $lp_link ); ?>"><?php echo esc_html( $lp_title ); ?></a></h3>
		<p class="lp-card__price"><?php echo lp_price_html( $lp_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape. ?></p>
		<ul class="lp-card__meta">
			<?php if ( $lp_area ) : ?>
				<li title="<?php echo esc_attr( lp_t( 'Diện tích' ) ); ?>"><?php echo lp_icon( 'area' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( lp_number( $lp_area ) ); ?> m²</li>
			<?php endif; ?>
			<?php if ( $lp_bed ) : ?>
				<li title="<?php echo esc_attr( lp_t( 'Phòng ngủ' ) ); ?>"><?php echo lp_icon( 'bed' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_bed . ' ' . lp_t( 'PN' ) ); ?></li>
			<?php endif; ?>
			<?php if ( $lp_bath ) : ?>
				<li title="<?php echo esc_attr( lp_t( 'Phòng tắm' ) ); ?>"><?php echo lp_icon( 'bath' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_bath . ' ' . lp_t( 'WC' ) ); ?></li>
			<?php endif; ?>
		</ul>
		<?php if ( $lp_location ) : ?>
			<p class="lp-card__location"><?php echo lp_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_location ); ?></p>
		<?php endif; ?>
	</div>
</article>
