<?php
/**
 * تنظیمات عمومی «مجله تکنوپی»: شناسه/کد تگ منیجر گوگل و کدهای سفارشی (آنالیتیکس، پیکسل و ...).
 *
 * - صفحهٔ «مجله تکنوپی ← تنظیمات»؛ ذخیره با admin-post.php، nonce و دسترسی edit_theme_options.
 * - کد خام (اسکریپت) را فقط کاربری می‌تواند ذخیره کند که دسترسی unfiltered_html دارد (مدیر در سایت تک‌نمونه)؛
 *   شناسهٔ GTM همیشه فقط با الگوی GTM-XXXX پذیرفته می‌شود.
 * - کدها در ویرایشگر بصری، پیش‌نمایش پیش‌نویس و پیش‌نمایش سفارشی‌ساز چاپ نمی‌شوند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

const TECHNOPAY_SETTINGS_PAGE   = 'technopay-settings';
const TECHNOPAY_SETTINGS_OPTION = 'technopay_settings';

/**
 * مقادیر پیش‌فرض.
 *
 * @return array<string,string>
 */
function technopay_settings_defaults() {
	return array(
		'gtm_id'      => '',
		'code_head'   => '',
		'code_body'   => '',
		'code_footer' => '',
	);
}

/**
 * تنظیمات ذخیره‌شده.
 *
 * @return array<string,string>
 */
function technopay_settings_get() {
	$saved = get_option( TECHNOPAY_SETTINGS_OPTION, array() );
	return array_merge( technopay_settings_defaults(), is_array( $saved ) ? array_intersect_key( $saved, technopay_settings_defaults() ) : array() );
}

/**
 * شناسهٔ کانتینر GTM معتبر یا رشتهٔ خالی.
 *
 * @param mixed $value مقدار ورودی.
 * @return string
 */
function technopay_sanitize_gtm_id( $value ) {
	$value = is_string( $value ) ? strtoupper( trim( $value ) ) : '';
	return preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $value ) ? $value : '';
}

/**
 * پاک‌سازی ورودی فرم.
 *
 * @param array $input       مقادیر ارسالی (بدون escape وردپرس).
 * @param array $current     مقادیر فعلی.
 * @param bool  $can_code    آیا کاربر اجازهٔ ذخیرهٔ کد خام دارد؟
 * @return array{values:array,gtm_rejected:bool}
 */
function technopay_settings_sanitize( array $input, array $current, $can_code ) {
	$out          = $current;
	$gtm_raw      = isset( $input['gtm_id'] ) && is_string( $input['gtm_id'] ) ? trim( $input['gtm_id'] ) : '';
	$out['gtm_id'] = technopay_sanitize_gtm_id( $gtm_raw );
	if ( $can_code ) {
		foreach ( array( 'code_head', 'code_body', 'code_footer' ) as $key ) {
			$raw         = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? $input[ $key ] : '';
			$raw         = str_replace( "\0", '', $raw );
			$out[ $key ] = mb_substr( trim( $raw ), 0, 100000, 'UTF-8' );
		}
	}
	return array(
		'values'       => $out,
		'gtm_rejected' => '' !== $gtm_raw && '' === $out['gtm_id'],
	);
}

/* ==========================================================================
   صفحهٔ مدیریت
   ========================================================================== */

/**
 * ذخیرهٔ فرم.
 */
function technopay_settings_handle_save() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه دسترسی به این بخش را ندارید.', 'technopay' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'technopay_settings_save' );
	$input  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce بالا بررسی شد؛ کد خام عمداً و فقط برای کاربر دارای unfiltered_html ذخیره می‌شود.
	$result = technopay_settings_sanitize( is_array( $input ) ? $input : array(), technopay_settings_get(), current_user_can( 'unfiltered_html' ) );
	update_option( TECHNOPAY_SETTINGS_OPTION, $result['values'] );
	wp_safe_redirect(
		add_query_arg(
			'tps-msg',
			$result['gtm_rejected'] ? 'bad-gtm' : 'saved',
			admin_url( 'admin.php?page=' . TECHNOPAY_SETTINGS_PAGE )
		)
	);
	exit;
}
add_action( 'admin_post_technopay_settings_save', 'technopay_settings_handle_save' );

/**
 * صفحهٔ «تنظیمات».
 */
function technopay_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$s        = technopay_settings_get();
	$can_code = current_user_can( 'unfiltered_html' );
	$msg      = isset( $_GET['tps-msg'] ) ? sanitize_key( wp_unslash( $_GET['tps-msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap" dir="rtl">
		<h1><?php esc_html_e( 'تنظیمات مجله تکنوپی', 'technopay' ); ?></h1>

		<?php if ( 'saved' === $msg ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'تنظیمات ذخیره شد.', 'technopay' ); ?></p></div>
		<?php elseif ( 'bad-gtm' === $msg ) : ?>
			<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'شناسهٔ GTM معتبر نبود و ذخیره نشد؛ شکل درست: GTM-ABC1234. بقیهٔ تنظیمات ذخیره شد.', 'technopay' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:860px">
			<input type="hidden" name="action" value="technopay_settings_save">
			<?php wp_nonce_field( 'technopay_settings_save' ); ?>

			<h2><?php esc_html_e( 'تگ منیجر گوگل (GTM)', 'technopay' ); ?></h2>
			<p class="description"><?php esc_html_e( 'ساده‌ترین راه: فقط شناسهٔ کانتینر را بنویسید؛ قالب کد گوگل را خودش در بالای head و بعد از شروع body می‌گذارد.', 'technopay' ); ?></p>
			<p>
				<label for="tps-gtm"><strong><?php esc_html_e( 'شناسهٔ کانتینر', 'technopay' ); ?></strong></label><br>
				<input type="text" id="tps-gtm" name="gtm_id" value="<?php echo esc_attr( $s['gtm_id'] ); ?>" class="regular-text" dir="ltr" placeholder="GTM-XXXXXXX" maxlength="16" autocomplete="off">
			</p>

			<h2><?php esc_html_e( 'کدهای سفارشی', 'technopay' ); ?></h2>
			<p class="description"><?php esc_html_e( 'اگر کد کامل گوگل (یا هر اسکریپت دیگر مثل آنالیتیکس و پیکسل) را دارید، اینجا بچسبانید. اگر شناسهٔ GTM را بالا نوشته‌اید، کد GTM را دوباره اینجا نگذارید.', 'technopay' ); ?></p>
			<?php if ( ! $can_code ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'حساب شما اجازهٔ ذخیرهٔ کد اسکریپت (unfiltered_html) ندارد؛ فقط مدیر کل می‌تواند این بخش را عوض کند.', 'technopay' ); ?></p></div>
			<?php endif; ?>

			<?php
			$fields = array(
				'code_head'   => array(
					__( 'کد داخل <head>', 'technopay' ),
					__( 'مثلاً کد اول تگ منیجر (بخش script) یا gtag.js.', 'technopay' ),
				),
				'code_body'   => array(
					__( 'کد بلافاصله بعد از شروع <body>', 'technopay' ),
					__( 'مثلاً کد دوم تگ منیجر (بخش noscript).', 'technopay' ),
				),
				'code_footer' => array(
					__( 'کد پایین صفحه (قبل از </body>)', 'technopay' ),
					__( 'اسکریپت‌هایی که نباید سرعت بارگذاری را کم کنند (چت، پیکسل و ...).', 'technopay' ),
				),
			);
			foreach ( $fields as $key => $meta ) :
				?>
				<p>
					<label for="tps-<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $meta[0] ); ?></strong></label><br>
					<textarea id="tps-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="7" class="large-text code" dir="ltr" spellcheck="false" <?php disabled( ! $can_code ); ?>><?php echo esc_textarea( $s[ $key ] ); ?></textarea><br>
					<span class="description"><?php echo esc_html( $meta[1] ); ?></span>
				</p>
			<?php endforeach; ?>

			<p class="description"><?php esc_html_e( 'این کدها روی بازدیدکننده‌های سایت اجرا می‌شوند؛ در ویرایشگر بصری، پیش‌نمایش پیش‌نویس و پیش‌نمایش سفارشی‌سازی اجرا نمی‌شوند.', 'technopay' ); ?></p>
			<?php submit_button( __( 'ذخیره تنظیمات', 'technopay' ) ); ?>
		</form>
	</div>
	<?php
}

/* ==========================================================================
   چاپ روی سایت
   ========================================================================== */

/**
 * آیا کدهای ردیابی روی این درخواست چاپ شوند؟
 *
 * @return bool
 */
function technopay_settings_output_allowed() {
	if ( is_admin() || is_customize_preview() || is_feed() ) {
		return false;
	}
	return ! ( function_exists( 'technopay_editor_state' ) && technopay_editor_state() );
}

/**
 * کد داخل head.
 */
function technopay_print_head_code() {
	if ( ! technopay_settings_output_allowed() ) {
		return;
	}
	$s = technopay_settings_get();
	if ( '' !== $s['gtm_id'] && false === strpos( $s['code_head'], 'googletagmanager.com/gtm.js' ) ) {
		printf(
			"<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\nnew Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,'script','dataLayer','%s');</script>\n<!-- End Google Tag Manager -->\n",
			esc_js( $s['gtm_id'] )
		);
	}
	if ( '' !== $s['code_head'] ) {
		echo $s['code_head'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- کد اسکریپت عمداً خام است و فقط کاربر دارای unfiltered_html آن را ذخیره می‌کند.
	}
}
add_action( 'wp_head', 'technopay_print_head_code', 1 );

/**
 * کد بعد از شروع body.
 */
function technopay_print_body_code() {
	if ( ! technopay_settings_output_allowed() ) {
		return;
	}
	$s = technopay_settings_get();
	if ( '' !== $s['gtm_id'] && false === strpos( $s['code_body'], 'googletagmanager.com/ns.html' ) ) {
		printf(
			"<!-- Google Tag Manager (noscript) -->\n<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=%s\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n<!-- End Google Tag Manager (noscript) -->\n",
			esc_attr( $s['gtm_id'] )
		);
	}
	if ( '' !== $s['code_body'] ) {
		echo $s['code_body'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- مانند کد head.
	}
}
add_action( 'wp_body_open', 'technopay_print_body_code', 0 ); // بلافاصله بعد از <body> (پیش از اسپریت آیکون‌ها).

/**
 * کد پایین صفحه.
 */
function technopay_print_footer_code() {
	if ( ! technopay_settings_output_allowed() ) {
		return;
	}
	$s = technopay_settings_get();
	if ( '' !== $s['code_footer'] ) {
		echo $s['code_footer'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- مانند کد head.
	}
}
add_action( 'wp_footer', 'technopay_print_footer_code', 99 );
