<?php
/**
 * آیتم کوچک لیست سایدبار: تصویر بندانگشتی (سمت راست، مانند طرح مرجع) + عنوان + تاریخ.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_has_title = '' !== trim( wp_strip_all_tags( get_the_title() ) );
?>
<article class="mini">
	<a href="<?php the_permalink(); ?>" class="mini__thumb" tabindex="-1" aria-hidden="true"><?php technopay_thumbnail( 'technopay-thumb' ); ?></a>
	<div class="mini__body">
		<h3 class="mini__title"><a href="<?php the_permalink(); ?>"><?php echo $technopay_has_title ? get_the_title() : esc_html__( '(بدون عنوان)', 'technopay' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_the_title() output is filtered for display. ?></a></h3>
		<span class="mini__meta"><?php technopay_icon( 'calendar' ); ?><span><span class="sr-only"><?php esc_html_e( 'تاریخ انتشار:', 'technopay' ); ?></span> <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( technopay_post_date( null, 'numeric' ) ); ?></time></span></span>
	</div>
</article>
