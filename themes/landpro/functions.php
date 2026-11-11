<?php
/**
 * LandPro child theme.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// Font Be Vietnam Pro tự host (assets/fonts), không dùng Google Fonts.
		wp_enqueue_style( 'landpro-fonts', get_stylesheet_directory_uri() . '/assets/fonts.css', array(), filemtime( get_stylesheet_directory() . '/assets/fonts.css' ) );
		wp_enqueue_style(
			'landpro-child',
			get_stylesheet_uri(),
			array( 'hello-elementor', 'landpro-fonts' ),
			filemtime( get_stylesheet_directory() . '/style.css' )
		);
	},
	20
);

/**
 * Chuyển hướng 301 các đường dẫn của website cũ (Flatsome) sang trang mới,
 * giữ thứ hạng tìm kiếm và tránh lỗi 404 cho link cũ.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_404() ) {
			return;
		}
		$path  = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$exact = array(
			'mua'          => '/mua-nha/',
			'ban-cho-thue' => '/thue-nha/',
			'trang-chu-2'  => '/',
			'chu-dau-tu'   => '/du-an/',
			'nha-can-ho'   => '/nha-dat/',
			'tai-lieu'     => '/tin-tuc/',
			'du-an'        => '/du-an/',
		);
		$first = strtok( $path, '/' );
		if ( isset( $exact[ $path ] ) ) {
			$to = $exact[ $path ];
		} elseif ( in_array( $first, array( 'nha-can-ho', 'tai-lieu', 'du-an', 'chu-dau-tu', 'property_cat', 'khu-vuc-cu' ), true ) ) {
			$to = $exact[ $first ] ?? '/';
		} else {
			return;
		}
		wp_safe_redirect( home_url( $to ), 301 );
		exit;
	}
);

/** Tải trước 2 file font tiếng Việt dùng nhiều nhất để chữ hiện ngay, không bị nháy. */
add_action(
	'wp_head',
	function () {
		foreach ( array( '400', '700' ) as $w ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_stylesheet_directory_uri() . '/assets/fonts/be-vietnam-pro-' . $w . '-vietnamese.woff2' ) );
		}
	},
	2
);

/** Không cho Elementor tải font từ Google Fonts (font đã tự host). */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * Dải tiêu đề dùng chung cho trang con: breadcrumb + H1 (+ mô tả).
 *
 * @param string $title Tiêu đề H1 ('' = tự lấy theo trang hiện tại).
 * @param string $desc  Mô tả ngắn (HTML đã escape).
 */
function landpro_page_header( $title = '', $desc = '' ) {
	if ( '' === $title ) {
		$title = class_exists( 'LP_Templates' ) ? LP_Templates::current_title() : wp_strip_all_tags( get_the_archive_title() );
	}
	$image_id = ( is_singular( 'post' ) && has_post_thumbnail() ) ? get_post_thumbnail_id() : (int) get_theme_mod( 'landpro_banner_image' );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	printf(
		'<header class="lp-page-header"%s><div class="lp-page-header__inner">',
		$image ? ' style="background-image:url(' . esc_url( $image ) . ')"' : ''
	);
	if ( class_exists( 'LP_Templates' ) ) {
		LP_Templates::breadcrumb();
	} elseif ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="lp-breadcrumb" aria-label="Breadcrumb">', '</nav>' );
	}
	echo '<h1 class="lp-page-header__title">' . esc_html( $title ) . '</h1>';
	if ( $desc ) {
		echo '<div class="lp-page-header__desc">' . wp_kses_post( $desc ) . '</div>';
	}
	echo '</div></header>';
}

/** Tắt tiêu đề mặc định của Hello (đã có dải tiêu đề chung). */
add_filter( 'hello_elementor_page_title', '__return_false' );

/** Gợi ý kích thước logo trong Tùy biến. */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$c = $wp_customize->get_control( 'custom_logo' );
		if ( $c ) {
			$c->description = 'Kích thước chuẩn: cao 120 px (rộng tùy logo), PNG / WebP / SVG nền trong suốt, ≤ 50 KB. Logo hiển thị cao khoảng 60 px; chân trang tự đổi sang màu trắng.';
		}
	},
	20
);

/** Tùy biến: ảnh nền banner trang con (Giao diện → Tùy biến → Banner trang con). */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'landpro_banner',
			array(
				'title'    => 'Banner trang con',
				'priority' => 30,
			)
		);
		$wp_customize->add_setting(
			'landpro_banner_image',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'landpro_banner_image',
				array(
					'label'       => 'Ảnh nền banner',
					'description' => 'Dùng cho trang tin tức, chuyên mục, tìm kiếm, 404. Kích thước gợi ý 1920 x 500 px.',
					'section'     => 'landpro_banner',
					'mime_type'   => 'image',
				)
			)
		);
	}
);

/* =================================================================
   Tin tức: sidebar (tìm kiếm, chuyên mục, bài mới) + mục lục bài viết.
   ================================================================= */

/** Sidebar dùng chung cho trang tin tức và bài viết. */
function landpro_blog_sidebar() {
	$t = function ( $s ) {
		return function_exists( 'lp_t' ) ? lp_t( $s ) : $s;
	};
	echo '<aside class="lp-sidebar" aria-label="' . esc_attr( $t( 'Thanh bên' ) ) . '">';

	// Tìm kiếm bài viết.
	echo '<section class="lp-widget"><h2 class="lp-widget__title">' . esc_html( $t( 'Tìm kiếm' ) ) . '</h2>';
	printf(
		'<form class="lp-blog-search" role="search" method="get" action="%1$s"><label class="screen-reader-text" for="lp-blog-s">%2$s</label><input id="lp-blog-s" type="search" name="s" value="%3$s" placeholder="%4$s"><input type="hidden" name="post_type" value="post"><button type="submit" aria-label="%2$s"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4.5-4.5"/></svg></button></form>',
		esc_url( home_url( '/' ) ),
		esc_attr( $t( 'Tìm kiếm' ) ),
		esc_attr( get_search_query() ),
		esc_attr( $t( 'Nhập từ khóa...' ) )
	);
	echo '</section>';

	// Chuyên mục.
	$cats = get_categories( array( 'hide_empty' => true ) );
	if ( $cats ) {
		echo '<section class="lp-widget"><h2 class="lp-widget__title">' . esc_html( $t( 'Chuyên mục' ) ) . '</h2><ul class="lp-cat-list">';
		foreach ( $cats as $c ) {
			printf( '<li><a href="%s"%s><span>%s</span><span class="lp-cat-list__count">%d</span></a></li>', esc_url( get_category_link( $c ) ), is_category( $c->term_id ) ? ' aria-current="page"' : '', esc_html( $c->name ), (int) $c->count );
		}
		echo '</ul></section>';
	}

	// Bài viết mới.
	$recent = get_posts( array( 'numberposts' => 5, 'post__not_in' => is_singular( 'post' ) ? array( get_the_ID() ) : array() ) );
	if ( $recent ) {
		echo '<section class="lp-widget"><h2 class="lp-widget__title">' . esc_html( $t( 'Bài viết mới' ) ) . '</h2><ul class="lp-recent">';
		foreach ( $recent as $r ) {
			echo '<li><a href="' . esc_url( get_permalink( $r ) ) . '">';
			if ( has_post_thumbnail( $r ) ) {
				echo get_the_post_thumbnail( $r, 'thumbnail', array( 'loading' => 'lazy', 'alt' => get_the_title( $r ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '<span><span class="lp-recent__title">' . esc_html( get_the_title( $r ) ) . '</span><time datetime="' . esc_attr( get_the_date( 'c', $r ) ) . '">' . esc_html( get_the_date( '', $r ) ) . '</time></span></a></li>';
		}
		echo '</ul></section>';
	}

	// Liên hệ nhanh.
	$hotline = function_exists( 'lp_option' ) ? lp_option( 'hotline' ) : '';
	if ( $hotline ) {
		printf(
			'<section class="lp-widget lp-widget--cta"><p class="lp-widget__title">%s</p><p>%s</p><a class="lp-btn" href="tel:%s">%s</a></section>',
			esc_html( $t( 'Cần tư vấn bất động sản?' ) ),
			esc_html( $t( 'Chuyên viên LandPro hỗ trợ miễn phí 8:00 - 20:00 mỗi ngày.' ) ),
			esc_attr( preg_replace( '/\D+/', '', $hotline ) ),
			esc_html( $hotline )
		);
	}
	echo '</aside>';
}

/**
 * Mục lục: gắn id cho H2/H3 trong nội dung bài viết và dựng danh sách đánh số 1, 1.1...
 * Trả về [nội dung đã gắn id, HTML mục lục].
 */
function landpro_toc( $content ) {
	$items = array();
	$used  = array();
	$n2    = 0;
	$n3    = 0;
	$content = preg_replace_callback(
		'#<h([23])([^>]*)>(.*?)</h\1>#is',
		function ( $m ) use ( &$items, &$used, &$n2, &$n3 ) {
			$text = trim( wp_strip_all_tags( $m[3] ) );
			$text = preg_replace( '/^(\d+(\.\d+)*[.)]?\s+)/u', '', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) ); // Bỏ số tự đánh trong tiêu đề (đã có số của mục lục).
			if ( '' === $text ) {
				return $m[0];
			}
			if ( preg_match( '/\sid=["\']([^"\']+)/', $m[2], $idm ) ) {
				$id = $idm[1];
				$attrs = $m[2];
			} else {
				$id = sanitize_title( remove_accents( $text ) );
				$id = $id ? $id : 'muc';
				$base = $id;
				$i = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $i++;
				}
				$attrs = $m[2] . ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			if ( '2' === $m[1] ) {
				++$n2;
				$n3 = 0;
				$num = (string) $n2;
			} else {
				++$n3;
				$num = ( $n2 ? $n2 : 1 ) . '.' . $n3;
			}
			$items[] = array( (int) $m[1], $id, $text, $num );
			return '<h' . $m[1] . $attrs . '>' . $m[3] . '</h' . $m[1] . '>';
		},
		$content
	);
	if ( count( $items ) < 2 ) {
		return array( $content, '' );
	}
	$t   = function_exists( 'lp_t' ) ? 'lp_t' : function ( $s ) { return $s; };
	$out = '<nav class="lp-toc" aria-labelledby="lp-toc-title"><div class="lp-toc__head"><p class="lp-toc__title" id="lp-toc-title">' . esc_html( $t( 'Nội dung chính' ) ) . '</p><button type="button" class="lp-toc__toggle" aria-expanded="true" aria-controls="lp-toc-list">' . esc_html( $t( 'Ẩn' ) ) . '</button></div><ol class="lp-toc__list" id="lp-toc-list">';
	foreach ( $items as $it ) {
		$out .= sprintf( '<li class="lp-toc__item lp-toc__item--h%d"><a href="#%s"><span class="lp-toc__num">%s</span><span>%s</span></a></li>', $it[0], esc_attr( $it[1] ), esc_html( $it[3] ), esc_html( $it[2] ) );
	}
	return array( $content, $out . '</ol></nav>' );
}

/* Chữ của sidebar & mục lục: đăng ký với Polylang. */
add_action(
	'init',
	function () {
		if ( function_exists( 'pll_register_string' ) ) {
			foreach ( array( 'Thanh bên', 'Tìm kiếm', 'Nhập từ khóa...', 'Chuyên mục', 'Bài viết mới', 'Cần tư vấn bất động sản?', 'Chuyên viên LandPro hỗ trợ miễn phí 8:00 - 20:00 mỗi ngày.', 'Nội dung chính', 'Ẩn', 'Hiện' ) as $s ) {
				pll_register_string( 'landpro_' . md5( $s ), $s, 'LandPro' );
			}
		}
	}
);

/* JS nhỏ: thu gọn mục lục, đánh dấu mục đang đọc. */
add_action(
	'wp_footer',
	function () {
		if ( ! is_singular( 'post' ) ) {
			return;
		}
		$t = function_exists( 'lp_t' ) ? 'lp_t' : function ( $s ) { return $s; };
		?>
		<script>
		(function(){var toc=document.querySelector('.lp-toc');if(!toc)return;
		var btn=toc.querySelector('.lp-toc__toggle'),list=toc.querySelector('.lp-toc__list'),H=<?php echo wp_json_encode( array( $t( 'Ẩn' ), $t( 'Hiện' ) ) ); ?>;
		btn.addEventListener('click',function(){var open=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',open?'false':'true');list.hidden=open;btn.textContent=open?H[1]:H[0];});
		/* Nút mục lục nổi: bản sao danh sách, xổ lên khi bấm. */
		var fab=document.createElement('div');fab.className='lp-toc-fab';
		fab.innerHTML='<div class="lp-toc-fab__panel" id="lp-toc-fab-panel" role="dialog" aria-label="'+toc.querySelector('.lp-toc__title').textContent+'"><p class="lp-toc-fab__title">'+toc.querySelector('.lp-toc__title').textContent+'</p></div>'+
			'<button type="button" class="lp-toc-fab__btn" aria-expanded="false" aria-controls="lp-toc-fab-panel" title="'+toc.querySelector('.lp-toc__title').textContent+'"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/></svg><span>'+toc.querySelector('.lp-toc__title').textContent+'</span></button>';
		var panel=fab.querySelector('.lp-toc-fab__panel'),fbtn=fab.querySelector('.lp-toc-fab__btn');
		var clone=list.cloneNode(true);clone.removeAttribute('id');clone.hidden=false;panel.appendChild(clone);
		document.body.appendChild(fab);
		function fabSet(open){fab.classList.toggle('is-open',open);fbtn.setAttribute('aria-expanded',open?'true':'false');if(open){var a=clone.querySelector('.is-active a')||clone.querySelector('a');if(a){a.focus({preventScroll:true});}}}
		fbtn.addEventListener('click',function(e){e.stopPropagation();fabSet(!fab.classList.contains('is-open'));});
		clone.addEventListener('click',function(e){if(e.target.closest('a')){fabSet(false);}});
		document.addEventListener('click',function(e){if(!fab.contains(e.target)){fabSet(false);}});
		document.addEventListener('keydown',function(e){if(e.key==='Escape'&&fab.classList.contains('is-open')){fabSet(false);fbtn.focus();}});
		var showFab=function(){var r=toc.getBoundingClientRect();var v=r.bottom<0;fab.classList.toggle('is-visible',v);if(!v){fabSet(false);}};
		window.addEventListener('scroll',showFab,{passive:true});showFab();
		var links=[].slice.call(toc.querySelectorAll('a')),heads=links.map(function(a){return document.getElementById(decodeURIComponent(a.hash.slice(1)));});
		if(!('IntersectionObserver' in window))return;var cur=null;
		var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){var i=heads.indexOf(e.target);if(i>-1){if(cur)cur.classList.remove('is-active');cur=links[i].parentNode;cur.classList.add('is-active');var cl=clone.querySelectorAll('li');cl.forEach(function(li,k){li.classList.toggle('is-active',k===i);});}}});},{rootMargin:'-120px 0px -70% 0px'});
		heads.forEach(function(h){if(h)io.observe(h);});})();
		</script>
		<?php
	}
);

/** Menu mobile dạng bảng trượt: thêm đầu bảng (logo + nút đóng), nền mờ, Esc để đóng. */
add_action(
	'wp_footer',
	function () {
		$logo = wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'medium' );
		?>
		<script>
		(function(){
			var mq = window.matchMedia('(max-width: 1024px)');
			var nav = document.querySelector('.lp-site-header nav.hfe-nav-menu__layout-horizontal');
			var toggle = document.querySelector('.lp-site-header .hfe-nav-menu__toggle');
			if (!nav || !toggle) { return; }
			var closeLabel = <?php echo wp_json_encode( function_exists( 'lp_t' ) ? lp_t( 'Đóng menu' ) : 'Đóng menu' ); ?>;
			var head = document.createElement('div');
			head.className = 'lp-mnav-head';
			head.innerHTML = '<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="140" height="34"></a>' +
				'<button type="button" class="lp-mnav-close" aria-label="' + closeLabel + '"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>';
			nav.insertBefore(head, nav.firstChild);
			nav.setAttribute('aria-label', 'Menu');
			var overlay = document.createElement('div');
			overlay.className = 'lp-mnav-overlay';
			nav.parentNode.insertBefore(overlay, nav); // Cùng lớp với menu (trong header) để không che bảng menu.
			function isOpen(){ return nav.classList.contains('menu-is-active'); }
			function close(){ if (isOpen()) { toggle.click(); } }
			function sync(){
				var open = isOpen() && mq.matches;
				overlay.classList.toggle('is-open', open);
				document.documentElement.classList.toggle('lp-mnav-lock', open);
				if (open) { setTimeout(function(){ var b = head.querySelector('.lp-mnav-close'); if (b) { b.focus(); } }, 320); }
			}
			new MutationObserver(sync).observe(nav, { attributes: true, attributeFilter: ['class'] });
			overlay.addEventListener('click', close);
			head.querySelector('.lp-mnav-close').addEventListener('click', function(){ close(); toggle.focus(); });
			document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && isOpen()) { close(); toggle.focus(); } });
			nav.addEventListener('click', function(e){ var a = e.target.closest('a[href]'); if (a && a.hash && a.pathname === location.pathname) { close(); } });
			mq.addEventListener && mq.addEventListener('change', function(){ if (!mq.matches) { close(); } sync(); });
		})();
		</script>
		<?php
		$t = 'Đóng menu';
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( 'landpro_' . md5( $t ), $t, 'LandPro' );
		}
	},
	20
);

/** Hiệu ứng hiện dần khi cuộn: gắn tự động cho tiêu đề, khối, card trên mọi trang. */
add_action(
	'wp_head',
	function () {
		// Bật sớm để tránh nháy: chỉ khi trình duyệt hỗ trợ và không giảm chuyển động.
		echo "<script>(function(){var d=document.documentElement;if('IntersectionObserver' in window&&!(window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches)&&!/elementor-preview|action=elementor/.test(location.search)){d.classList.add('lp-reveal-on');}})();</script>\n";
	},
	1
);
add_action(
	'wp_footer',
	function () {
		?>
		<script>
		(function(){
			var d=document.documentElement; if(!d.classList.contains('lp-reveal-on')){return;}
			// Nhóm lưới: các phần tử con hiện lần lượt.
			var groups=[
				['.lp-grid','.lp-card, .lp-pcard, .lp-type, .lp-location'],
				['.lp-blog .page-content','.lp-post-card'],
				['.lp-specs','.lp-specs__item'],
				['.lp-amenities, .lp-checklist','li'],
				['.lp-services','.e-con'],
				['.lp-tool-card','.elementor-widget']
			];
			var marked=new Set();
			function mark(el,type,delay){ if(!el||marked.has(el)||el.closest('.lp-hero,#masthead,.lp-site-header,#colophon,footer.site-footer,.lp-float,.lp-toc-fab,.lp-subnav,.lp-mnav-head,.lp-sidebar,.lp-single__aside,.lp-article__toc,.lp-slider__track')){return;} el.setAttribute('data-lp-reveal',type||''); if(delay){el.style.setProperty('--lp-delay',delay+'ms');} marked.add(el); }
			groups.forEach(function(g){ document.querySelectorAll(g[0]).forEach(function(wrap){ var items=[].filter.call(wrap.querySelectorAll(g[1]),function(x){return x.parentElement===wrap||x.parentElement.parentElement===wrap||g[1]==='li';}); items.forEach(function(it,i){ mark(it,'',Math.min(i,8)*80); }); }); });
			// Khối đơn lẻ.
			document.querySelectorAll('.elementor-widget-heading, .elementor-widget-text-editor, .elementor-widget-button, .elementor-widget-icon-list, .elementor-widget-shortcode, .lp-section__title, .lp-section > *:not(.lp-specs):not(.lp-amenities):not(.lp-checklist), .lp-page-header__inner, .lp-single__header, .lp-gallery, .lp-search, .lp-contact, .lp-quick, .lp-toc, .comment-respond, .lp-article .page-content > *, .lp-tabs').forEach(function(el){
				if(el.closest('[data-lp-reveal]')||el.querySelector('[data-lp-reveal]')){return;}
				mark(el, el.matches('.lp-gallery') ? 'zoom' : (el.matches('.lp-page-header__inner') ? 'fade' : ''), 0);
			});
			var io=new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('is-in'); io.unobserve(e.target); } }); },{rootMargin:'0px 0px -8% 0px',threshold:0.08});
			marked.forEach(function(el){ io.observe(el); });
			// Lưới thay đổi (lọc tab dự án): hiện ngay phần tử mới.
			document.addEventListener('click',function(e){ if(e.target.closest('[data-lp-filter]')){ setTimeout(function(){ document.querySelectorAll('[data-lp-reveal]:not(.is-in)').forEach(function(el){ var r=el.getBoundingClientRect(); if(r.top<innerHeight){el.classList.add('is-in');} }); },30); } });
			// An toàn: nếu vì lý do nào đó chưa hiện sau 4s thì hiện hết phần trong màn hình.
			setTimeout(function(){ document.querySelectorAll('[data-lp-reveal]:not(.is-in)').forEach(function(el){ if(el.getBoundingClientRect().top<innerHeight){el.classList.add('is-in');} }); },4000);
		})();
		</script>
		<?php
	},
	30
);
