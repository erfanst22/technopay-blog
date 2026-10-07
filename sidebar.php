<?php
/**
 * سایدبار (برای سازگاری با get_sidebar()): بلوک‌های سایدبار آرشیو از «نمایش ← چیدمان صفحات».
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_sidebar = technopay_region_html( 'archive', 'sidebar', array( 'page' => 'archive' ) );
if ( '' === $technopay_sidebar ) {
	return;
}
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'سایدبار', 'technopay' ); ?>">
	<?php echo $technopay_sidebar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where it is generated. ?>
</aside>
