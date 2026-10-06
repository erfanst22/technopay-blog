<?php
/**
 * پیام «مطلبی یافت نشد».
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="empty-state">
	<span class="empty-state__icon"><?php technopay_icon( 'search' ); ?></span>
	<h2><?php esc_html_e( 'مطلبی پیدا نشد!', 'technopay' ); ?></h2>
	<p>
		<?php
		if ( is_search() ) {
			esc_html_e( 'برای عبارت جستجوشده نتیجه‌ای پیدا نکردیم. لطفاً با کلمات دیگری دوباره جستجو کنید.', 'technopay' );
		} else {
			esc_html_e( 'هنوز مطلبی در این بخش منتشر نشده است.', 'technopay' );
		}
		?>
	</p>
	<?php get_search_form(); ?>
</div>
