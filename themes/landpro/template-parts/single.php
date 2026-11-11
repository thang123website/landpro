<?php
/**
 * Bài viết / trang không dựng bằng Elementor: dải tiêu đề chung + nội dung.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( have_posts() ) :
	the_post();
	landpro_page_header( get_the_title() );
	?>
	<main id="content" <?php post_class( 'site-main lp-article' . ( is_singular( 'post' ) ? ' lp-article--post' : '' ) ); ?>>
		<?php if ( is_singular( 'post' ) ) : ?>
			<p class="lp-article__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php
				$landpro_cats = get_the_category_list( ', ' );
				if ( $landpro_cats ) {
					echo ' · ' . wp_kses_post( $landpro_cats );
				}
				?>
			</p>
			<?php // Ảnh đại diện đã làm nền banner phía trên, không lặp lại. ?>
		<?php endif; ?>
		<?php
		$landpro_html = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		list( $landpro_html, $landpro_toc ) = is_singular( 'post' ) ? landpro_toc( $landpro_html ) : array( $landpro_html, '' );
		?>
		<div class="lp-article__body<?php echo $landpro_toc ? ' has-toc' : ''; ?><?php echo is_singular( 'post' ) ? ' has-sidebar' : ''; ?>">
			<?php if ( $landpro_toc ) : ?>
				<div class="lp-article__toc"><?php echo $landpro_toc; // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape trong landpro_toc(). ?></div>
			<?php endif; ?>
			<div class="page-content"><?php echo $landpro_html; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php wp_link_pages(); ?><?php comments_template(); ?></div>
			<?php
			if ( is_singular( 'post' ) ) {
				landpro_blog_sidebar();
			}
			?>
		</div>
	</main>
	<?php
endwhile;
