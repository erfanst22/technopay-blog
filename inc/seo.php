<?php
/**
 * سئو: آدرس کنونیکال صفحه‌های آرشیو.
 *
 * همهٔ صفحه‌های شمارهٔ دو به بعد (دسته، برچسب، نویسنده، تاریخ، صفحهٔ نوشته‌ها)
 * و نسخه‌های مرتب‌شده/نمایش‌های آن‌ها (?sort= و ?view=) به صفحهٔ اول همان آرشیو کنونیکال می‌شوند.
 * اگر Yoast SEO، Rank Math و مانند آن فعال باشد، خروجی خود همان افزونه اصلاح می‌شود؛
 * در غیر این صورت قالب خودش تگ کنونیکال را چاپ می‌کند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * آدرس صفحهٔ اول آرشیو فعلی؛ برای صفحه‌هایی که آرشیو نیستند رشتهٔ خالی.
 *
 * @return string
 */
function technopay_archive_first_page_url() {
	if ( is_404() || is_search() || is_singular() ) {
		return '';
	}

	$url = '';

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			$url  = is_wp_error( $link ) ? '' : $link;
		}
	} elseif ( is_author() ) {
		$url = get_author_posts_url( (int) get_queried_object_id() );
	} elseif ( is_day() ) {
		$url = get_day_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ), (int) get_query_var( 'day' ) );
	} elseif ( is_month() ) {
		$url = get_month_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ) );
	} elseif ( is_year() ) {
		$url = get_year_link( (int) get_query_var( 'year' ) );
	} elseif ( is_post_type_archive() ) {
		$object = get_queried_object();
		$url    = $object instanceof WP_Post_Type ? get_post_type_archive_link( $object->name ) : '';
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$url        = ( 'page' === get_option( 'show_on_front' ) && $posts_page ) ? get_permalink( $posts_page ) : home_url( '/' );
	}

	$url = is_string( $url ) ? $url : '';

	/**
	 * اجازه می‌دهد آدرس کنونیکال آرشیو عوض شود ('' یعنی بدون تغییر).
	 *
	 * @param string $url آدرس صفحهٔ اول.
	 */
	return (string) apply_filters( 'technopay_archive_first_page_url', $url );
}

/**
 * آیا یکی از افزونه‌های سئوی شناخته‌شده فعال است؟
 *
 * @return bool
 */
function technopay_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/**
 * چاپ تگ کنونیکال آرشیو (وردپرس برای آرشیوها کنونیکال نمی‌گذارد).
 */
function technopay_print_archive_canonical() {
	if ( technopay_has_seo_plugin() || ! apply_filters( 'technopay_print_canonical', true ) ) {
		return;
	}

	$url = technopay_archive_first_page_url();
	if ( '' !== $url ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'technopay_print_archive_canonical', 10 );

/**
 * اصلاح کنونیکال افزونه‌های سئو برای آرشیوها.
 *
 * @param string $canonical آدرس کنونیکال افزونه.
 * @return string
 */
function technopay_filter_seo_canonical( $canonical ) {
	$url = technopay_archive_first_page_url();
	return '' !== $url ? $url : $canonical;
}
add_filter( 'wpseo_canonical', 'technopay_filter_seo_canonical' );                    // Yoast SEO.
add_filter( 'rank_math/frontend/canonical', 'technopay_filter_seo_canonical' );        // Rank Math.
add_filter( 'aioseo_canonical_url', 'technopay_filter_seo_canonical' );               // All in One SEO.
add_filter( 'seopress_titles_canonical', 'technopay_filter_seo_canonical' );          // SEOPress.
add_filter( 'the_seo_framework_canonical_url', 'technopay_filter_seo_canonical' );    // The SEO Framework.
