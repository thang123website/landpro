<?php
/**
 * SEO: dữ liệu có cấu trúc (schema.org) và mô tả meta mặc định.
 *
 * - Có Yoast: thêm RealEstateListing vào schema graph của Yoast, tự sinh meta description khi bài chưa nhập.
 * - Không có Yoast: tự in JSON-LD và thẻ meta description.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LP_Schema {

	public static function init() {
		add_filter( 'wpseo_schema_graph', array( __CLASS__, 'yoast_graph' ), 10, 2 );
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'yoast_description' ) );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'yoast_description' ) );
		add_action( 'wp_head', array( __CLASS__, 'fallback_head' ), 5 );
	}

	/** Mô tả tự động: "Bán căn hộ 2PN 72 m² tại Quận 7, giá 3,8 tỷ. ..." */
	public static function auto_description( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		$parts = array();
		if ( 'property' === $post->post_type ) {
			$purpose = lp_first_term( $post_id, 'lp_purpose' );
			$type    = lp_first_term( $post_id, 'lp_type' );
			$head    = trim( ( $purpose ? $purpose->name : '' ) . ' ' . ( $type ? mb_strtolower( $type->name ) : 'nhà đất' ) );
			if ( lp_get( 'bedrooms', $post_id ) ) {
				$head .= ' ' . lp_get( 'bedrooms', $post_id ) . 'PN';
			}
			if ( lp_get( 'area', $post_id ) ) {
				$head .= ' ' . lp_number( lp_get( 'area', $post_id ) ) . ' m²';
			}
			if ( lp_location_text( $post_id ) ) {
				$head .= ' tại ' . lp_location_text( $post_id );
			}
			$parts[] = $head . ', giá ' . wp_strip_all_tags( lp_price_html( $post_id ) ) . '.';
		} elseif ( 'project' === $post->post_type ) {
			$parts[] = 'Dự án ' . $post->post_title . ( lp_get( 'developer', $post_id ) ? ' của ' . lp_get( 'developer', $post_id ) : '' ) . ( lp_get( 'address', $post_id ) ? ' tại ' . lp_get( 'address', $post_id ) : '' ) . '.';
			if ( lp_get( 'price_text', $post_id ) ) {
				$parts[] = 'Giá ' . lp_get( 'price_text', $post_id ) . '.';
			}
		}
		$excerpt = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$parts[] = $excerpt;
		$text    = preg_replace( '/\s+/u', ' ', trim( implode( ' ', $parts ) ) );
		return mb_strlen( $text ) > 158 ? rtrim( mb_substr( $text, 0, 155 ) ) . '...' : $text;
	}

	public static function yoast_description( $desc ) {
		if ( $desc || ! is_singular( array( 'property', 'project' ) ) ) {
			return $desc;
		}
		return self::auto_description( get_queried_object_id() );
	}

	/** Schema RealEstateListing cho tin nhà đất. */
	public static function listing_schema( $post_id ) {
		$images = array();
		foreach ( array_slice( lp_gallery_ids( $post_id ), 0, 5 ) as $img ) {
			$images[] = wp_get_attachment_image_url( $img, 'large' );
		}
		$data = array(
			'@type'       => 'RealEstateListing',
			'@id'         => get_permalink( $post_id ) . '#listing',
			'name'        => get_the_title( $post_id ),
			'url'         => get_permalink( $post_id ),
			'description' => self::auto_description( $post_id ),
			'datePosted'  => get_the_date( 'c', $post_id ),
			'image'       => array_values( array_filter( $images ) ),
		);
		$price = (float) lp_get( 'price', $post_id );
		if ( $price > 0 ) {
			$data['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => 'VND',
				'businessFunction' => lp_is_rent( $post_id ) ? 'http://purl.org/goodrelations/v1#LeaseOut' : 'http://purl.org/goodrelations/v1#Sell',
			);
		}
		$address = lp_get( 'address', $post_id );
		if ( $address ) {
			$data['contentLocation'] = array(
				'@type'   => 'Place',
				'address' => array(
					'@type'          => 'PostalAddress',
					'streetAddress'  => $address,
					'addressCountry' => 'VN',
				),
			);
		}
		return $data;
	}

	public static function yoast_graph( $graph, $context ) {
		// Website demo: không khai báo tin rao bán giả lập cho Google.
		if ( is_singular( 'property' ) && ! ( class_exists( 'LP_Legal' ) && LP_Legal::demo() ) ) {
			$listing = self::listing_schema( get_queried_object_id() );
			// Gắn vào WebPage của Yoast.
			$listing['mainEntityOfPage'] = array( '@id' => $context->canonical );
			$graph[]                     = $listing;
		}
		return $graph;
	}

	/** Khi không có plugin SEO: in meta description + JSON-LD. */
	public static function fallback_head() {
		if ( defined( 'WPSEO_VERSION' ) || ! is_singular( array( 'property', 'project' ) ) ) {
			return;
		}
		$id = get_queried_object_id();
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( self::auto_description( $id ) ) );
		if ( is_singular( 'property' ) && ! ( class_exists( 'LP_Legal' ) && LP_Legal::demo() ) ) {
			$data = array_merge( array( '@context' => 'https://schema.org' ), self::listing_schema( $id ) );
			echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	}
}
