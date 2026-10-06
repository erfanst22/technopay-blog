<?php
/**
 * فوتر سایت.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_socials = technopay_social_links();
?>
<footer class="site-footer">
	<div class="container footer__grid">
		<div class="footer__about">
			<?php get_template_part( 'template-parts/logo' ); ?>
			<?php if ( technopay_option( 'footer_about' ) ) : ?>
				<p><?php echo esc_html( technopay_option( 'footer_about' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $technopay_socials ) : ?>
				<div class="socials">
					<?php foreach ( $technopay_socials as $technopay_social ) : ?>
						<a href="<?php echo esc_url( $technopay_social['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $technopay_social['label'] ); ?>"><?php technopay_icon( $technopay_social['icon'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( technopay_option( 'app_android_url' ) || technopay_option( 'app_ios_url' ) ) : ?>
				<div class="app-badges">
					<?php if ( technopay_option( 'app_android_url' ) ) : ?>
						<a class="app-badge" href="<?php echo esc_url( technopay_option( 'app_android_url' ) ); ?>"><?php technopay_icon( 'download' ); ?><span><?php esc_html_e( 'دانلود اپلیکیشن', 'technopay' ); ?><strong><?php esc_html_e( 'نسخه اندروید', 'technopay' ); ?></strong></span></a>
					<?php endif; ?>
					<?php if ( technopay_option( 'app_ios_url' ) ) : ?>
						<a class="app-badge" href="<?php echo esc_url( technopay_option( 'app_ios_url' ) ); ?>"><?php technopay_icon( 'smartphone' ); ?><span><?php esc_html_e( 'نسخه وب‌اپ', 'technopay' ); ?><strong><?php esc_html_e( 'iOS و وب', 'technopay' ); ?></strong></span></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div>
			<h2 class="footer__title"><?php esc_html_e( 'دسته‌بندی‌ها', 'technopay' ); ?></h2>
			<ul class="footer__links">
				<?php foreach ( technopay_get_top_categories( 6 ) as $technopay_term ) : ?>
					<li><a href="<?php echo esc_url( get_category_link( $technopay_term ) ); ?>"><?php echo esc_html( $technopay_term->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2 class="footer__title"><?php esc_html_e( 'لینک‌های مفید', 'technopay' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer__links',
					'depth'          => 1,
					'fallback_cb'    => 'technopay_footer_menu_fallback',
				)
			);
			?>
		</div>

		<div>
			<h2 class="footer__title"><?php esc_html_e( 'ارتباط با ما', 'technopay' ); ?></h2>
			<ul class="contact-list">
				<?php if ( technopay_option( 'contact_phone' ) ) : ?>
					<li><?php technopay_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', technopay_option( 'contact_phone' ) ) ); ?>" dir="ltr"><?php echo esc_html( technopay_option( 'contact_phone' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( technopay_option( 'contact_email' ) ) : ?>
					<li><?php technopay_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( antispambot( technopay_option( 'contact_email' ) ) ); ?>"><?php echo esc_html( antispambot( technopay_option( 'contact_email' ) ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( technopay_option( 'contact_address' ) ) : ?>
					<li><?php technopay_icon( 'map-pin' ); ?><span><?php echo esc_html( technopay_option( 'contact_address' ) ); ?></span></li>
				<?php endif; ?>
			</ul>
			<?php if ( technopay_option( 'footer_trust_html' ) ) : ?>
				<div class="trust"><?php echo technopay_sanitize_trust_html( technopay_option( 'footer_trust_html' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>
	</div>

	<div class="container footer__bottom">
		<p>
			<?php
			if ( technopay_option( 'footer_copyright' ) ) {
				echo esc_html( technopay_option( 'footer_copyright' ) );
			} else {
				/* translators: 1: year, 2: site name */
				printf( esc_html__( '© %1$s تمامی حقوق برای %2$s محفوظ است.', 'technopay' ), esc_html( technopay_fa_digits( technopay_jalali_year() ) ), esc_html( get_bloginfo( 'name' ) ) );
			}
			?>
		</p>
		<p><?php esc_html_e( 'استفاده از مطالب مجله با ذکر منبع مجاز است.', 'technopay' ); ?></p>
	</div>
</footer>

<button type="button" class="to-top" aria-label="<?php esc_attr_e( 'بازگشت به بالای صفحه', 'technopay' ); ?>"><?php technopay_icon( 'arrow-up' ); ?></button>

<?php wp_footer(); ?>
</body>
</html>
