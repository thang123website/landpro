<?php
/**
 * Trang tin tức / chuyên mục / thẻ: dải tiêu đề chung + lưới bài viết.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

landpro_page_header( '', get_the_archive_description() );
?>
<main id="content" class="site-main lp-blog lp-with-sidebar">
	<div class="lp-blog__main">
	<?php if ( have_posts() ) : ?>
		<div class="page-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'lp-post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="lp-post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => get_the_title() ) ); ?>
						</a>
					<?php endif; ?>
					<div class="lp-post-card__body">
						<time class="lp-post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<h2 class="lp-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="lp-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '‹',
				'next_text' => '›',
				'class'     => 'lp-blog-pagination',
			)
		);
		?>
	<?php else : ?>
		<p class="lp-empty"><?php echo esc_html( function_exists( 'lp_t' ) ? lp_t( 'Chưa có bài viết.' ) : 'Chưa có bài viết.' ); ?></p>
	<?php endif; ?>
	</div>
	<?php landpro_blog_sidebar(); ?>
</main>
