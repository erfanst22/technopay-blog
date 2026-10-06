<?php
/**
 * توابع کمکی قالب.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * خروجی یک آیکون از اسپرایت SVG.
 *
 * @param string $name  نام آیکون (بدون پیشوند i-).
 * @param string $class کلاس اضافه.
 * @return string
 */
function technopay_get_icon( $name, $class = '' ) {
	return sprintf(
		'<svg class="icon%1$s" aria-hidden="true" focusable="false"><use href="#i-%2$s"></use></svg>',
		$class ? ' ' . esc_attr( $class ) : '',
		esc_attr( $name )
	);
}

/**
 * چاپ آیکون.
 *
 * @param string $name  نام آیکون.
 * @param string $class کلاس اضافه.
 */
function technopay_icon( $name, $class = '' ) {
	echo technopay_get_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
}

/**
 * چاپ اسپرایت آیکون‌ها بلافاصله بعد از <body>.
 */
function technopay_print_icon_sprite() {
	get_template_part( 'template-parts/icons' );
}
add_action( 'wp_body_open', 'technopay_print_icon_sprite', 1 );

/**
 * تبدیل ارقام لاتین به فارسی.
 *
 * @param string|int|float $value مقدار.
 * @return string
 */
function technopay_fa_digits( $value ) {
	return str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
		(string) $value
	);
}

/**
 * قالب‌بندی عدد با جداکننده هزارگان فارسی.
 *
 * @param int|float $number عدد.
 * @return string
 */
function technopay_number( $number ) {
	return technopay_fa_digits( number_format( (float) $number, 0, '.', '٬' ) );
}

/**
 * تبدیل تاریخ میلادی به شمسی (الگوریتم jdf).
 *
 * @param int $gy سال میلادی.
 * @param int $gm ماه میلادی.
 * @param int $gd روز میلادی.
 * @return int[] [سال, ماه, روز]
 */
function technopay_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
	$days %= 12053;
	$jy   += 4 * intdiv( $days, 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy  += intdiv( $days - 1, 365 );
		$days = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + intdiv( $days, 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + intdiv( $days - 186, 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	return array( $jy, $jm, $jd );
}

/**
 * نام ماه‌های شمسی.
 *
 * @return string[]
 */
function technopay_jalali_months() {
	return array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
}

/**
 * آیا تاریخ‌ها باید توسط قالب شمسی شوند؟
 * اگر افزونه پارسی‌دیت فعال باشد، تبدیل را به آن می‌سپاریم.
 *
 * @return bool
 */
function technopay_use_jalali() {
	if ( function_exists( 'parsidate' ) || defined( 'WP_PARSI_VER' ) || defined( 'WPP_VERSION' ) ) {
		return false;
	}
	return (bool) technopay_option( 'jalali_dates' );
}

/**
 * تاریخ خوانا (مثلاً «۱۴ مهر ۱۴۰۵»).
 *
 * @param int $timestamp تایم‌استمپ یونیکس.
 * @return string
 */
function technopay_format_date( $timestamp ) {
	if ( ! technopay_use_jalali() ) {
		return wp_date( get_option( 'date_format' ), $timestamp );
	}
	$parts         = explode( '-', wp_date( 'Y-n-j', $timestamp ) );
	list( $y, $m, $d ) = technopay_gregorian_to_jalali( (int) $parts[0], (int) $parts[1], (int) $parts[2] );
	$months        = technopay_jalali_months();
	return technopay_fa_digits( $d . ' ' . $months[ $m ] . ' ' . $y );
}

/**
 * تاریخ امروز همراه با نام روز هفته (مثلاً «سه‌شنبه ۱۴ مهر ۱۴۰۵»).
 *
 * @return string
 */
function technopay_today_label() {
	if ( ! technopay_use_jalali() ) {
		return wp_date( 'l ' . get_option( 'date_format' ) );
	}
	$weekdays = array( 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه' );
	return $weekdays[ (int) wp_date( 'w' ) ] . ' ' . technopay_format_date( time() );
}

/**
 * سال جاری (شمسی یا میلادی، مطابق تنظیمات).
 *
 * @return int
 */
function technopay_jalali_year() {
	$parts = explode( '-', wp_date( 'Y-n-j' ) );
	if ( ! technopay_use_jalali() && ! function_exists( 'parsidate' ) ) {
		return (int) $parts[0];
	}
	$jalali = technopay_gregorian_to_jalali( (int) $parts[0], (int) $parts[1], (int) $parts[2] );
	return $jalali[0];
}

/**
 * تاریخ انتشار نوشته.
 *
 * @param int|WP_Post|null $post نوشته.
 * @return string
 */
function technopay_post_date( $post = null ) {
	if ( ! technopay_use_jalali() ) {
		return get_the_date( '', $post );
	}
	return technopay_format_date( (int) get_post_timestamp( $post ) );
}

/**
 * کلید متای تعداد بازدید (قابل تغییر برای سازگاری با افزونه‌ها، مثلاً «views» برای WP-PostViews).
 *
 * @return string
 */
function technopay_views_meta_key() {
	return apply_filters( 'technopay_views_meta_key', 'technopay_views' );
}

/**
 * تعداد بازدید نوشته.
 *
 * @param int|null $post_id شناسه نوشته.
 * @return int
 */
function technopay_get_views( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (int) get_post_meta( $post_id, technopay_views_meta_key(), true );
}

/**
 * افزایش شمارنده بازدید در صفحه نوشته (به جز پیش‌نمایش و نویسندگان سایت).
 */
function technopay_track_views() {
	if ( ! is_singular( 'post' ) || is_preview() || current_user_can( 'edit_posts' ) ) {
		return;
	}
	if ( ! apply_filters( 'technopay_track_views', true ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	update_post_meta( $post_id, technopay_views_meta_key(), technopay_get_views( $post_id ) + 1 );
}
add_action( 'template_redirect', 'technopay_track_views' );

/**
 * زمان تقریبی مطالعه به دقیقه.
 *
 * @param int|null $post_id شناسه نوشته.
 * @return int
 */
function technopay_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$words   = preg_split( '/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY );
	$count   = is_array( $words ) ? count( $words ) : 0;
	return max( 1, (int) ceil( $count / 200 ) );
}

/**
 * آدرس کاور پیش‌فرض برای نوشته‌های بدون تصویر شاخص.
 *
 * @param int $post_id شناسه نوشته.
 * @return string
 */
function technopay_fallback_cover_url( $post_id ) {
	$index = ( absint( $post_id ) % 12 ) + 1;
	return TECHNOPAY_URI . '/assets/img/covers/cover-' . sprintf( '%02d', $index ) . '.svg';
}

/**
 * تصویر شاخص یا کاور پیش‌فرض.
 *
 * @param string $size  اندازه تصویر.
 * @param array  $attr  ویژگی‌های اضافه img.
 * @param int    $post_id شناسه نوشته.
 * @return string
 */
function technopay_get_thumbnail( $size = 'technopay-card', $attr = array(), $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$attr    = wp_parse_args( $attr, array( 'alt' => '' ) );

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	$loading = isset( $attr['loading'] ) ? $attr['loading'] : 'lazy';
	return sprintf(
		'<img src="%1$s" alt="%2$s" width="1200" height="750" loading="%3$s" decoding="async"%4$s>',
		esc_url( technopay_fallback_cover_url( $post_id ) ),
		esc_attr( $attr['alt'] ),
		esc_attr( $loading ),
		isset( $attr['fetchpriority'] ) ? ' fetchpriority="' . esc_attr( $attr['fetchpriority'] ) . '"' : ''
	);
}

/**
 * چاپ تصویر شاخص.
 *
 * @param string $size اندازه.
 * @param array  $attr ویژگی‌ها.
 */
function technopay_thumbnail( $size = 'technopay-card', $attr = array() ) {
	echo technopay_get_thumbnail( $size, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * حروف اول نام (برای آواتار حرفی).
 *
 * @param string $name نام.
 * @return string
 */
function technopay_initials( $name ) {
	$parts    = preg_split( '/\s+/u', trim( (string) $name ), -1, PREG_SPLIT_NO_EMPTY );
	$initials = '';
	foreach ( array_slice( (array) $parts, 0, 2 ) as $part ) {
		$initials .= mb_substr( $part, 0, 1 );
	}
	if ( count( (array) $parts ) > 1 ) {
		$initials = implode( ' ', preg_split( '//u', $initials, -1, PREG_SPLIT_NO_EMPTY ) );
	}
	return $initials ? $initials : '؟';
}

/**
 * آواتار: حرفی (بدون درخواست به گراواتار) یا تصویر گراواتار.
 *
 * @param int|string|WP_Comment $id_or_email کاربر/ایمیل/دیدگاه.
 * @param string                $name        نام نمایشی.
 * @param int                   $size        اندازه.
 * @param string                $class       کلاس اضافه.
 * @return string
 */
function technopay_get_avatar( $id_or_email, $name, $size = 30, $class = '' ) {
	if ( ! technopay_option( 'letter_avatars' ) ) {
		$img = get_avatar( $id_or_email, $size * 2, '', $name, array( 'class' => 'avatar ' . $class ) );
		if ( $img ) {
			return $img;
		}
	}
	$variant = ( abs( crc32( (string) $name ) ) % 4 ) + 1;
	return sprintf(
		'<span class="avatar avatar--%1$d %2$s" aria-hidden="true">%3$s</span>',
		$variant,
		esc_attr( $class ),
		esc_html( technopay_initials( $name ) )
	);
}

/**
 * نگهداری شناسه نوشته‌های نمایش‌داده‌شده در صفحه اصلی تا تکراری نشوند.
 *
 * @param int[] $add شناسه‌های جدید.
 * @return int[]
 */
function technopay_shown_ids( $add = array() ) {
	static $ids = array();
	if ( $add ) {
		$ids = array_unique( array_merge( $ids, array_map( 'intval', (array) $add ) ) );
	}
	return $ids;
}

/**
 * نوشته‌های بخش ویژه: ابتدا نوشته‌های سنجاق‌شده، سپس تازه‌ترین‌ها.
 *
 * @param int $count تعداد.
 * @return WP_Post[]
 */
function technopay_get_hero_posts( $count = 5 ) {
	$posts  = array();
	$sticky = array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ) );

	if ( $sticky ) {
		$posts = get_posts(
			array(
				'post__in'            => $sticky,
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'orderby'             => 'date',
			)
		);
	}

	if ( count( $posts ) < $count ) {
		$posts = array_merge(
			$posts,
			get_posts(
				array(
					'posts_per_page'      => $count - count( $posts ),
					'post__not_in'        => wp_list_pluck( $posts, 'ID' ),
					'ignore_sticky_posts' => true,
				)
			)
		);
	}

	return $posts;
}

/**
 * پربازدیدترین نوشته‌ها (در صورت نبود آمار بازدید، بر اساس دیدگاه).
 *
 * @param int   $count   تعداد.
 * @param int[] $exclude نوشته‌های مستثنا.
 * @return WP_Post[]
 */
function technopay_get_popular_posts( $count = 5, $exclude = array() ) {
	$posts = get_posts(
		array(
			'posts_per_page'      => $count,
			'meta_key'            => technopay_views_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'post__not_in'        => $exclude,
			'ignore_sticky_posts' => true,
		)
	);

	if ( count( $posts ) < $count ) {
		$posts = array_merge(
			$posts,
			get_posts(
				array(
					'posts_per_page'      => $count - count( $posts ),
					'orderby'             => array(
						'comment_count' => 'DESC',
						'date'          => 'DESC',
					),
					'post__not_in'        => array_merge( $exclude, wp_list_pluck( $posts, 'ID' ) ),
					'ignore_sticky_posts' => true,
				)
			)
		);
	}

	return $posts;
}

/**
 * دسته‌های پرمطلب (بدون دسته پیش‌فرض «دسته‌بندی نشده» اگر دسته دیگری وجود داشته باشد).
 *
 * @param int $count تعداد.
 * @return WP_Term[]
 */
function technopay_get_top_categories( $count = 6 ) {
	$terms = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => $count + 1,
		)
	);

	$default = (int) get_option( 'default_category' );
	if ( count( $terms ) > 1 ) {
		$terms = array_values(
			array_filter(
				$terms,
				function ( $term ) use ( $default ) {
					return (int) $term->term_id !== $default;
				}
			)
		);
	}

	return array_slice( $terms, 0, $count );
}

/**
 * دسته‌ای که در تنظیمات انتخاب شده یا دسته خودکار بر اساس ترتیب.
 *
 * @param string $option_key کلید تنظیم.
 * @param int    $fallback_index  اندیس در لیست دسته‌های پرمطلب.
 * @return WP_Term|null
 */
function technopay_get_section_category( $option_key, $fallback_index = 0 ) {
	$term_id = absint( technopay_option( $option_key ) );
	if ( $term_id ) {
		$term = get_term( $term_id, 'category' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}
	$top = technopay_get_top_categories( 6 );
	if ( ! $top ) {
		return null;
	}
	return isset( $top[ $fallback_index ] ) ? $top[ $fallback_index ] : $top[0];
}

/**
 * اولین دسته نوشته (یا دسته اصلی Yoast/RankMath در صورت وجود).
 *
 * @param int|null $post_id شناسه نوشته.
 * @return WP_Term|null
 */
function technopay_primary_category( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	if ( ! $primary ) {
		$primary = (int) get_post_meta( $post_id, 'rank_math_primary_category', true );
	}
	if ( $primary ) {
		$term = get_term( $primary, 'category' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	$cats = get_the_category( $post_id );
	return $cats ? $cats[0] : null;
}

/**
 * مرتب‌سازی فعلی آرشیو.
 *
 * @return string
 */
function technopay_current_sort() {
	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'latest'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return in_array( $sort, array( 'latest', 'popular', 'comments', 'oldest' ), true ) ? $sort : 'latest';
}

/**
 * لینک‌های شبکه‌های اجتماعی تنظیم‌شده.
 *
 * @return array[] هر آیتم: [key, url, label, icon]
 */
function technopay_social_links() {
	$networks = array(
		'instagram' => array( __( 'اینستاگرام', 'technopay' ), 'instagram' ),
		'telegram'  => array( __( 'تلگرام', 'technopay' ), 'telegram' ),
		'linkedin'  => array( __( 'لینکدین', 'technopay' ), 'linkedin' ),
		'x'         => array( __( 'ایکس (توییتر)', 'technopay' ), 'x' ),
		'whatsapp'  => array( __( 'واتس‌اپ', 'technopay' ), 'whatsapp' ),
		'youtube'   => array( __( 'یوتیوب / آپارات', 'technopay' ), 'play' ),
	);

	$links = array();
	foreach ( $networks as $key => $data ) {
		$url = technopay_option( 'social_' . $key );
		if ( $url ) {
			$links[] = array(
				'key'   => $key,
				'url'   => $url,
				'label' => $data[0],
				'icon'  => $data[1],
			);
		}
	}
	return $links;
}
