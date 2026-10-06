<?php
/**
 * آیتم کوچک لیست سایدبار: عنوان + تاریخ و تصویر بندانگشتی (سمت چپ، مانند طرح مرجع).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="mini">
	<div class="mini__body">
		<h3 class="mini__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<span class="mini__meta"><?php technopay_icon( 'calendar' ); ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( technopay_post_date( null, 'numeric' ) ); ?></time></span>
	</div>
	<a href="<?php the_permalink(); ?>" class="mini__thumb" tabindex="-1" aria-hidden="true"><?php technopay_thumbnail( 'technopay-thumb' ); ?></a>
</article>
