<?php
/**
 * Trang danh sách / kết quả tìm kiếm nhà đất (/nha-dat/, /nhu-cau/..., /loai-nha-dat/..., /khu-vuc/...).
 * Ghi đè: theme/landpro/archive-property.php
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$lp_filters = LP_Query::request_filters();
$lp_purpose = is_tax( 'lp_purpose' ) ? get_queried_object()->slug : '';
// Bản đồ theo đúng trang đang xem (nhu cầu / loại / khu vực) + bộ lọc trên URL.
$lp_map_atts = array( 'purpose' => $lp_purpose, 'context' => 'archive' );
if ( is_tax( 'lp_type' ) ) {
	$lp_map_atts['type'] = get_queried_object()->slug;
} elseif ( is_tax( 'lp_location' ) ) {
	$lp_map_atts['location'] = get_queried_object()->slug;
}

get_header();
?>
<main id="content" class="lp-archive">
	<div class="lp-container">
		<?php LP_Templates::breadcrumb(); ?>
		<header class="lp-archive__header">
			<h1 class="lp-archive__title"><?php echo esc_html( get_the_archive_title() ); ?></h1>
			<?php if ( term_description() ) : ?>
				<div class="lp-archive__desc"><?php echo wp_kses_post( term_description() ); ?></div>
			<?php endif; ?>
		</header>

		<div class="lp-archive__body" data-lp-split>
		<?php
		echo LP_Render::search_form( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'action'  => LP_Render::current_url(),
				'purpose' => $lp_purpose,
				'tabs'    => $lp_purpose ? 'no' : 'yes',
				'fields'  => 'kw,type,location,price,area,bed',
			)
		);
		echo LP_Map::shortcode( $lp_map_atts ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>

		<div class="lp-listing">
			<?php LP_Render::toolbar( $wp_query->found_posts, $lp_filters ); ?>
			<?php if ( have_posts() ) : ?>
				<div class="lp-grid" style="--lp-cols:4;--lp-cols-tablet:3;--lp-cols-mobile:2">
					<?php
					while ( have_posts() ) :
						the_post();
						lp_template( 'parts/card-property.php', array( 'post_id' => get_the_ID() ) );
					endwhile;
					?>
				</div>
				<?php
				$lp_links = paginate_links(
					array(
						'type'      => 'list',
						'mid_size'  => 1,
						'prev_text' => '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html( lp_t( 'Trang trước' ) ) . '</span>',
						'next_text' => '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html( lp_t( 'Trang sau' ) ) . '</span>',
					)
				);
				if ( $lp_links ) {
					echo '<nav class="lp-pagination" aria-label="' . esc_attr( lp_t( 'Phân trang' ) ) . '">' . wp_kses_post( $lp_links ) . '</nav>';
				}
				?>
			<?php else : ?>
				<p class="lp-empty"><?php echo esc_html( lp_t( 'Không tìm thấy bất động sản phù hợp. Hãy thử bỏ bớt điều kiện lọc.' ) ); ?></p>
			<?php endif; ?>
		</div>
		</div>
	</div>
</main>
<?php
get_footer();
