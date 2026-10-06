<?php
/**
 * صفحه اصلی: ردیف «پربازدیدترین مطالب» (۶ کارت فشرده داخل جعبه خاکستری).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_more = technopay_posts_page_url();

technopay_post_row(
	array(
		'id'       => 'home-popular-title',
		'title'    => __( 'پربازدیدترین مطالب', 'technopay' ),
		'posts'    => technopay_get_popular_posts( 6 ),
		'meta'     => 'views',
		'more_url' => $technopay_more ? add_query_arg( 'sort', 'popular', $technopay_more ) : '',
	)
);
