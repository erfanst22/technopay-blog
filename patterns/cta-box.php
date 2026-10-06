<?php
/**
 * Title: باکس دعوت به دریافت اعتبار
 * Slug: technopay/cta-box
 * Categories: technopay
 * Keywords: cta, اعتبار, تبلیغ, دکمه
 * Description: باکس رنگی تبلیغاتی با دکمه، برای وسط مقاله.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_url = esc_url( technopay_option( 'cta_btn_url' ) );
?>
<!-- wp:group {"className":"tp-cta"} -->
<div class="wp-block-group tp-cta"><!-- wp:paragraph {"className":"tp-cta__title"} -->
<p class="tp-cta__title"><?php esc_html_e( 'می‌خواهید همین حالا اقساطی خرید کنید؟', 'technopay' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'بدون ضامن و کاملاً آنلاین اعتبار خرید بگیرید و از فروشگاه‌های طرف قرارداد تکنوپی خرید کنید.', 'technopay' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $technopay_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'دریافت اعتبار خرید', 'technopay' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
