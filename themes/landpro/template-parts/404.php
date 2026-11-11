<?php
/**
 * Trang 404.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$landpro_t = function ( $s ) {
	return function_exists( 'lp_t' ) ? lp_t( $s ) : $s;
};
landpro_page_header();
?>
<main id="content" class="site-main lp-404">
	<p><?php echo esc_html( $landpro_t( 'Trang bạn tìm không tồn tại hoặc đã được chuyển sang địa chỉ khác.' ) ); ?></p>
	<p class="lp-404__actions">
		<a class="lp-btn lp-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $landpro_t( 'Về trang chủ' ) ); ?></a>
		<a class="lp-btn lp-btn--outline" href="<?php echo esc_url( home_url( '/nha-dat/' ) ); ?>"><?php echo esc_html( $landpro_t( 'Xem nhà đất' ) ); ?></a>
	</p>
</main>
