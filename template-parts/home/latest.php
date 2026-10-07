<?php
/**
 * صفحه اصلی: ردیف «آخرین مطالب» (۶ کارت فشرده داخل جعبه خاکستری).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_count = max( 1, (int) technopay_block_setting( 'count', 6 ) );
$technopay_posts = technopay_get_latest_posts( $technopay_count, technopay_shown_ids() );
// اگر بعد از حذف مطالب ویژه کم ماند، با تازه‌ترین‌ها کامل کن.
if ( count( $technopay_posts ) < $technopay_count ) {
	$technopay_posts = technopay_get_latest_posts( $technopay_count );
}
technopay_shown_ids( wp_list_pluck( $technopay_posts, 'ID' ) );

technopay_post_row(
	array(
		'id'       => 'home-latest-title',
		'title'    => '' !== (string) technopay_block_setting( 'title', '' ) ? technopay_block_setting( 'title' ) : __( 'آخرین مطالب', 'technopay' ),
		'posts'    => $technopay_posts,
		'meta'     => 'date',
		'more_url' => technopay_block_setting( 'more_link', true ) ? technopay_posts_page_url() : '',
	)
);
