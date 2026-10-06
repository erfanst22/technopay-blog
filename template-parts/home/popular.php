<?php
/**
 * صفحه اصلی: ردیف «پربازدیدترین مطالب» (۶ کارت فشرده داخل جعبه خاکستری).
 * اگر هنوز آماری نیست و همه این نوشته‌ها قبلاً در بخش‌های بالاتر نشان داده شده‌اند (سایت کم‌مطلب یا تازه)،
 * ردیف نمایش داده نمی‌شود تا همان کارت‌ها تکرار نشوند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_popular = technopay_get_popular_posts( 6 );
if ( ! array_diff( wp_list_pluck( $technopay_popular, 'ID' ), technopay_shown_ids() ) ) {
	return;
}

$technopay_more = technopay_posts_page_url();

technopay_post_row(
	array(
		'id'       => 'home-popular-title',
		'title'    => __( 'پربازدیدترین مطالب', 'technopay' ),
		'posts'    => $technopay_popular,
		'meta'     => 'views',
		'more_url' => $technopay_more ? add_query_arg( 'sort', 'popular', $technopay_more ) : '',
	)
);
