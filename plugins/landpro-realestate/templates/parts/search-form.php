<?php
/**
 * Form tìm kiếm nhà đất. Ghi đè: theme/landpro/parts/search-form.php
 *
 * @var array $args { action, purpose, tabs, fields[], layout, button_text, values[] }
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lp_values  = $args['values'];
$lp_purpose = ! empty( $lp_values['lp_purpose'] ) ? $lp_values['lp_purpose'] : $args['purpose'];
$lp_uid     = wp_unique_id( 'lp-search-' );
$lp_val     = function ( $key ) use ( $lp_values ) {
	return isset( $lp_values[ $key ] ) ? $lp_values[ $key ] : '';
};
$lp_select  = function ( $name, $label, $options, $placeholder ) use ( $lp_uid, $lp_val ) {
	printf( '<div class="lp-search__field lp-search__field--%s">', esc_attr( $name ) );
	printf( '<label class="screen-reader-text" for="%1$s-%2$s">%3$s</label>', esc_attr( $lp_uid ), esc_attr( $name ), esc_html( $label ) );
	printf( '<select id="%1$s-%2$s" name="lp_%2$s"><option value="">%3$s</option>', esc_attr( $lp_uid ), esc_attr( $name ), esc_html( $placeholder ) );
	foreach ( $options as $value => $text ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $lp_val( 'lp_' . $name ), (string) $value, false ), esc_html( $text ) );
	}
	echo '</select></div>';
};
?>
<form class="lp-search lp-search--<?php echo esc_attr( $args['layout'] ); ?>" method="get" action="<?php echo esc_url( $args['action'] ); ?>" role="search"<?php echo ! empty( $args['live'] ) ? ' data-lp-live' : ''; ?>>
	<div class="lp-search__panel">
	<?php if ( 'yes' === $args['tabs'] ) : ?>
		<div class="lp-search__tabs" role="radiogroup" aria-label="<?php echo esc_attr( lp_t( 'Nhu cầu' ) ); ?>">
			<?php
			$lp_tabs = array(
				''         => lp_t( 'Tất cả' ),
				lp_lang_slug( 'ban', 'lp_purpose' )      => lp_t( 'Mua nhà' ),
				lp_lang_slug( 'cho-thue', 'lp_purpose' ) => lp_t( 'Thuê nhà' ),
			);
			foreach ( $lp_tabs as $lp_slug => $lp_label ) :
				?>
				<label class="lp-search__tab">
					<input type="radio" name="lp_purpose" value="<?php echo esc_attr( $lp_slug ); ?>" <?php checked( $lp_purpose, $lp_slug ); ?>>
					<span><?php echo esc_html( $lp_label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	<?php elseif ( $lp_purpose ) : ?>
		<input type="hidden" name="lp_purpose" value="<?php echo esc_attr( $lp_purpose ); ?>">
	<?php endif; ?>

	<div class="lp-search__fields">
		<?php
		foreach ( $args['fields'] as $lp_field ) {
			switch ( $lp_field ) {
				case 'kw':
					printf(
						'<div class="lp-search__field lp-search__field--kw"><label class="screen-reader-text" for="%1$s-kw">%3$s</label><input id="%1$s-kw" type="search" name="lp_kw" value="%2$s" placeholder="%4$s"></div>',
						esc_attr( $lp_uid ),
						esc_attr( $lp_val( 'lp_kw' ) ),
						esc_html( lp_t( 'Từ khóa' ) ),
						esc_attr( lp_t( 'Nhập địa điểm, dự án, tên đường...' ) )
					);
					break;
				case 'type':
					$lp_select( 'type', lp_t( 'Loại nhà đất' ), LP_Render::term_options( 'lp_type' ), lp_t( 'Loại nhà đất' ) );
					break;
				case 'location':
					$lp_select( 'location', lp_t( 'Khu vực' ), LP_Render::term_options( 'lp_location' ), lp_t( 'Khu vực' ) );
					break;
				case 'price':
					$lp_select( 'price', lp_t( 'Mức giá' ), LP_Query::price_ranges( $lp_purpose ), lp_t( 'Mức giá' ) );
					break;
				case 'area':
					$lp_select( 'area', lp_t( 'Diện tích' ), LP_Query::area_ranges(), lp_t( 'Diện tích' ) );
					break;
				case 'bed':
					$lp_select(
						'bed',
						lp_t( 'Phòng ngủ' ),
						array(
							'1' => lp_t( '1+ phòng ngủ' ),
							'2' => lp_t( '2+ phòng ngủ' ),
							'3' => lp_t( '3+ phòng ngủ' ),
							'4' => lp_t( '4+ phòng ngủ' ),
						),
						lp_t( 'Phòng ngủ' )
					);
					break;
			}
		}
		?>
		<button type="submit" class="lp-btn lp-search__submit"><?php echo lp_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( lp_t( $args['button_text'] ) ); ?></span></button>
	</div>
	</div>
	<?php if ( ! empty( $args['suggest'] ) ) : ?>
		<div class="lp-search__suggest">
			<span class="lp-search__suggest-label"><?php echo esc_html( lp_t( $args['suggest_label'] ) ); ?></span>
			<?php foreach ( $args['suggest'] as $lp_s ) : ?>
				<button type="button" class="lp-search__chip" data-lp-suggest="<?php echo esc_attr( lp_t( $lp_s ) ); ?>"><?php echo esc_html( lp_t( $lp_s ) ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
	if ( ! empty( $args['live'] ) ) :
		$lp_active = array_diff_key( array_filter( $lp_values ), array( 'lp_purpose' => 1, 'lp_sort' => 1, 'lp_page' => 1 ) );
		?>
		<div class="lp-search__active" data-lp-active<?php echo $lp_active ? '' : ' hidden'; ?>>
			<span class="lp-search__active-label"><?php echo esc_html( lp_t( 'Đang lọc:' ) ); ?></span>
			<span class="lp-search__chips" data-lp-chips></span>
			<a class="lp-search__reset" href="<?php echo esc_url( strtok( $args['action'], '?' ) ); ?>" data-lp-reset data-remove-label="<?php echo esc_attr( lp_t( 'Bỏ lọc' ) ); ?>"><?php echo esc_html( lp_t( 'Xóa lọc' ) ); ?></a>
		</div>
	<?php endif; ?>
</form>
