<?php
/**
 * لوگو: لوگوی آپلودشده در «سفارشی‌سازی ← هویت سایت» یا لوگوی متنی پیش‌فرض.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

if ( has_custom_logo() ) {
	the_custom_logo();
	return;
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" rel="home">
	<span class="logo__mark" aria-hidden="true"><?php technopay_icon( 'logo' ); ?></span>
	<span class="logo__text">
		<span class="logo__name"><?php bloginfo( 'name' ); ?></span>
		<?php if ( technopay_option( 'logo_tagline' ) ) : ?>
			<span class="logo__tag"><?php echo esc_html( technopay_option( 'logo_tagline' ) ); ?></span>
		<?php endif; ?>
	</span>
</a>
