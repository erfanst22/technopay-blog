<?php
/**
 * صفحه اصلی: خبرنامه.
 * فرم با شورت‌کد افزونه خبرنامه یا آدرس Action تنظیم می‌شود؛ تا تنظیم نشده فقط مدیر سایت یک راهنما می‌بیند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_shortcode = technopay_option( 'newsletter_shortcode' );
$technopay_action    = technopay_option( 'newsletter_action' );

if ( ! $technopay_shortcode && ! $technopay_action && ! current_user_can( 'edit_theme_options' ) ) {
	return;
}
?>
<section class="section container">
	<div class="newsletter">
		<span class="newsletter__icon"><?php technopay_icon( 'mail' ); ?></span>
		<div>
			<h2><?php echo esc_html( technopay_option( 'newsletter_title' ) ); ?></h2>
			<p><?php echo esc_html( technopay_option( 'newsletter_text' ) ); ?></p>
		</div>
		<div class="newsletter__form">
			<?php if ( $technopay_shortcode ) : ?>
				<?php echo do_shortcode( $technopay_shortcode ); ?>
			<?php elseif ( $technopay_action ) : ?>
				<form class="subscribe" method="post" action="<?php echo esc_url( $technopay_action ); ?>" target="_blank">
					<label class="sr-only" for="newsletter-email"><?php esc_html_e( 'ایمیل', 'technopay' ); ?></label>
					<input id="newsletter-email" type="email" name="email" required placeholder="<?php esc_attr_e( 'ایمیل خود را وارد کنید', 'technopay' ); ?>">
					<button type="submit" class="btn btn--primary"><?php esc_html_e( 'عضویت', 'technopay' ); ?></button>
				</form>
			<?php else : ?>
				<p class="admin-note"><?php esc_html_e( 'این پیام فقط برای مدیر سایت نمایش داده می‌شود: برای فعال‌سازی خبرنامه، در «نمایش ← سفارشی‌سازی ← تنظیمات قالب تکنوپی ← خبرنامه» شورت‌کد فرم یا آدرس ارسال فرم را وارد کنید.', 'technopay' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
