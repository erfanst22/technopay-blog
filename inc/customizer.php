<?php
/**
 * تنظیمات قالب در «نمایش ← سفارشی‌سازی».
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * مقادیر پیش‌فرض تنظیمات.
 *
 * @return array
 */
function technopay_defaults() {
	return array(
		// هدر.
		'topbar_enabled'       => true,
		'header_cta_text'      => 'دریافت اعتبار',
		'header_cta_url'       => 'https://technopay.ir',
		'logo_tagline'         => 'مجله خرید هوشمند',
		'logo_dark'            => 0,
		'logo_height'          => 40,
		'logo_invert_dark'     => false,

		// شبکه‌های اجتماعی.
		'social_instagram'     => '',
		'social_telegram'      => '',
		'social_linkedin'      => '',
		'social_x'             => '',
		'social_whatsapp'      => '',
		'social_youtube'       => '',

		// تماس و فوتر.
		'contact_phone'        => '',
		'contact_email'        => '',
		'contact_address'      => '',
		'app_android_url'      => '',
		'app_ios_url'          => '',
		'footer_about'         => 'مجله تکنوپی؛ راهنمای شما برای خرید هوشمند، مدیریت مالی و استفاده بهتر از اعتبار خرید اقساطی. با ما همیشه آگاهانه‌تر خرید کنید.',
		'footer_trust_html'    => '',
		'footer_copyright'     => '',

		// صفحه اصلی.
		'home_hero'            => true,
		'home_trending'        => true,
		'home_categories'      => true,
		'home_latest'          => true,
		'home_popular'         => true,
		'home_cta'             => true,
		'home_cat_block'       => true,
		'home_steps'           => true,
		'home_panels'          => true,
		'home_newsletter'      => true,
		'home_cat_block_cat'   => 0,
		'home_panel_1_cat'     => 0,
		'home_panel_2_cat'     => 0,

		// بنر تبلیغاتی (CTA).
		'cta_eyebrow'          => 'خرید اقساطی با تکنوپی',
		'cta_title'            => 'همین حالا بخر، قسطی پرداخت کن!',
		'cta_text'             => 'بدون ضامن و کاملاً آنلاین اعتبار خرید بگیر و از فروشگاه‌های طرف قرارداد تکنوپی، کالای دیجیتال، لوازم خانگی و طلا را اقساطی بخر.',
		'cta_btn_text'         => 'دریافت اعتبار خرید',
		'cta_btn_url'          => 'https://technopay.ir',
		'cta_btn2_text'        => 'آشنایی با شرایط',
		'cta_btn2_url'         => '',
		'cta_stat_1_value'     => 'تا ۳۰۰ میلیون',
		'cta_stat_1_label'     => 'تومان سقف اعتبار',
		'cta_stat_2_value'     => '۲۴ ماه',
		'cta_stat_2_label'     => 'حداکثر زمان بازپرداخت',
		'cta_stat_3_value'     => 'بدون ضامن',
		'cta_stat_3_label'     => 'فقط با چک صیادی',
		'cta_stat_4_value'     => '۱۰۰٪ آنلاین',
		'cta_stat_4_label'     => 'از ثبت‌نام تا خرید',

		// مراحل.
		'steps_title'          => 'خرید اقساطی با تکنوپی در ۴ قدم',
		'steps_subtitle'       => 'از ثبت‌نام تا خرید، همه مراحل آنلاین است',
		'step_1_title'         => 'ثبت‌نام و احراز هویت',
		'step_1_text'          => 'با شماره موبایل و کد ملی خود در چند دقیقه ثبت‌نام کنید.',
		'step_2_title'         => 'اعتبارسنجی آنلاین',
		'step_2_text'          => 'رتبه اعتباری شما بررسی و سقف اعتبارتان مشخص می‌شود.',
		'step_3_title'         => 'ثبت چک صیادی',
		'step_3_text'          => 'چک صیادی را در سامانه ثبت و برای ما ارسال کنید.',
		'step_4_title'         => 'خرید از فروشگاه‌ها',
		'step_4_text'          => 'اعتبارتان فعال شد! از فروشگاه‌های طرف قرارداد خرید کنید.',

		// محاسبه‌گر اقساط.
		'calc_rate'            => 23,
		'calc_min'             => 10,
		'calc_max'             => 300,
		'calc_default'         => 50,
		'calc_btn_url'         => 'https://technopay.ir',
		'calc_note'            => 'محاسبه تقریبی با نرخ نمونه است؛ مبلغ دقیق اقساط پس از اعتبارسنجی مشخص می‌شود.',

		// خبرنامه.
		'newsletter_title'     => 'عضویت در خبرنامه مجله تکنوپی',
		'newsletter_text'      => 'هر هفته تازه‌ترین مقالات، راهنمای خرید و تخفیف‌های ویژه را در ایمیل خود دریافت کنید.',
		'newsletter_action'    => '',
		'newsletter_shortcode' => '',

		// عمومی.
		'jalali_dates'         => true,
		'letter_avatars'       => true,
		'show_author_box'      => true,
		'show_related'         => true,
		'show_toc'             => true,
		'policy_text'          => '',
		'policy_url'           => '',
	);
}

/**
 * گرفتن مقدار یک تنظیم.
 *
 * @param string $key کلید.
 * @return mixed
 */
function technopay_option( $key ) {
	$defaults = technopay_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * پاکسازی مقدار چک‌باکس.
 *
 * @param mixed $value مقدار.
 * @return bool
 */
function technopay_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * ارتفاع لوگوی هدر (پیکسل) بین ۲۴ تا ۷۲.
 *
 * @param mixed $value مقدار.
 * @return int
 */
function technopay_sanitize_logo_height( $value ) {
	return min( 72, max( 24, (int) $value ) );
}

/**
 * ارتفاع لوگوی هدر از تنظیمات.
 *
 * @return int
 */
function technopay_logo_height() {
	return technopay_sanitize_logo_height( technopay_option( 'logo_height' ) );
}

/**
 * HTML مجاز برای نمادهای اعتماد (اینماد، ساماندهی و ...).
 *
 * @param string $value مقدار.
 * @return string
 */
function technopay_sanitize_trust_html( $value ) {
	$allowed = array(
		'a'   => array(
			'href'           => true,
			'target'         => true,
			'rel'            => true,
			'referrerpolicy' => true,
			'title'          => true,
			'class'          => true,
		),
		'img' => array(
			'src'            => true,
			'alt'            => true,
			'width'          => true,
			'height'         => true,
			'style'          => true,
			'code'           => true,
			'id'             => true,
			'class'          => true,
			'referrerpolicy' => true,
			'loading'        => true,
		),
		'span' => array( 'class' => true ),
		'div'  => array( 'class' => true ),
	);
	return wp_kses( $value, $allowed );
}

/**
 * ثبت پنل، بخش‌ها و کنترل‌ها.
 *
 * @param WP_Customize_Manager $wp_customize مدیر سفارشی‌سازی.
 */
function technopay_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'technopay',
		array(
			'title'    => __( 'تنظیمات قالب تکنوپی', 'technopay' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'technopay_header'     => __( 'هدر', 'technopay' ),
		'technopay_cta'        => __( 'بنر تبلیغاتی', 'technopay' ),
		'technopay_steps'      => __( 'مراحل دریافت اعتبار', 'technopay' ),
		'technopay_calc'       => __( 'محاسبه‌گر اقساط', 'technopay' ),
		'technopay_newsletter' => __( 'خبرنامه', 'technopay' ),
		'technopay_social'     => __( 'شبکه‌های اجتماعی', 'technopay' ),
		'technopay_footer'     => __( 'فوتر و اطلاعات تماس', 'technopay' ),
		'technopay_general'    => __( 'عمومی', 'technopay' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section(
			$id,
			array(
				'title' => $title,
				'panel' => 'technopay',
			)
		);
	}

	$fields = array(
		// هدر.
		array( 'topbar_enabled', 'technopay_header', 'checkbox', __( 'نمایش نوار بالای هدر', 'technopay' ) ),
		array( 'logo_tagline', 'technopay_header', 'text', __( 'متن زیر لوگو', 'technopay' ) ),
		array( 'header_cta_text', 'technopay_header', 'text', __( 'متن دکمه هدر', 'technopay' ) ),
		array( 'header_cta_url', 'technopay_header', 'url', __( 'لینک دکمه هدر', 'technopay' ) ),

		// CTA.
		array( 'cta_eyebrow', 'technopay_cta', 'text', __( 'برچسب بالای عنوان', 'technopay' ) ),
		array( 'cta_title', 'technopay_cta', 'text', __( 'عنوان', 'technopay' ) ),
		array( 'cta_text', 'technopay_cta', 'textarea', __( 'متن', 'technopay' ) ),
		array( 'cta_btn_text', 'technopay_cta', 'text', __( 'متن دکمه اصلی', 'technopay' ) ),
		array( 'cta_btn_url', 'technopay_cta', 'url', __( 'لینک دکمه اصلی', 'technopay' ) ),
		array( 'cta_btn2_text', 'technopay_cta', 'text', __( 'متن دکمه دوم', 'technopay' ) ),
		array( 'cta_btn2_url', 'technopay_cta', 'url', __( 'لینک دکمه دوم (خالی = مخفی)', 'technopay' ) ),
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: number */
		$fields[] = array( "cta_stat_{$i}_value", 'technopay_cta', 'text', sprintf( __( 'آمار %d — مقدار', 'technopay' ), $i ) );
		/* translators: %d: number */
		$fields[] = array( "cta_stat_{$i}_label", 'technopay_cta', 'text', sprintf( __( 'آمار %d — توضیح', 'technopay' ), $i ) );
	}

	$fields[] = array( 'steps_title', 'technopay_steps', 'text', __( 'عنوان', 'technopay' ) );
	$fields[] = array( 'steps_subtitle', 'technopay_steps', 'text', __( 'زیرعنوان', 'technopay' ) );
	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: step number */
		$fields[] = array( "step_{$i}_title", 'technopay_steps', 'text', sprintf( __( 'قدم %d — عنوان', 'technopay' ), $i ) );
		/* translators: %d: step number */
		$fields[] = array( "step_{$i}_text", 'technopay_steps', 'textarea', sprintf( __( 'قدم %d — توضیح', 'technopay' ), $i ) );
	}

	$fields = array_merge(
		$fields,
		array(
			// محاسبه‌گر.
			array( 'calc_rate', 'technopay_calc', 'number', __( 'نرخ سود سالانه (درصد)', 'technopay' ) ),
			array( 'calc_min', 'technopay_calc', 'number', __( 'حداقل مبلغ (میلیون تومان)', 'technopay' ) ),
			array( 'calc_max', 'technopay_calc', 'number', __( 'حداکثر مبلغ (میلیون تومان)', 'technopay' ) ),
			array( 'calc_default', 'technopay_calc', 'number', __( 'مبلغ پیش‌فرض (میلیون تومان)', 'technopay' ) ),
			array( 'calc_btn_url', 'technopay_calc', 'url', __( 'لینک دکمه درخواست اعتبار', 'technopay' ) ),
			array( 'calc_note', 'technopay_calc', 'textarea', __( 'توضیح زیر محاسبه‌گر', 'technopay' ) ),

			// خبرنامه.
			array( 'newsletter_title', 'technopay_newsletter', 'text', __( 'عنوان', 'technopay' ) ),
			array( 'newsletter_text', 'technopay_newsletter', 'textarea', __( 'متن', 'technopay' ) ),
			array( 'newsletter_action', 'technopay_newsletter', 'url', __( 'آدرس ارسال فرم (Action) — مثلاً فرم Mailchimp/MailerLite', 'technopay' ) ),
			array( 'newsletter_shortcode', 'technopay_newsletter', 'text', __( 'یا شورت‌کد فرم افزونه خبرنامه (اولویت دارد)', 'technopay' ) ),

			// شبکه‌های اجتماعی.
			array( 'social_instagram', 'technopay_social', 'url', __( 'اینستاگرام', 'technopay' ) ),
			array( 'social_telegram', 'technopay_social', 'url', __( 'تلگرام', 'technopay' ) ),
			array( 'social_linkedin', 'technopay_social', 'url', __( 'لینکدین', 'technopay' ) ),
			array( 'social_x', 'technopay_social', 'url', __( 'ایکس (توییتر)', 'technopay' ) ),
			array( 'social_whatsapp', 'technopay_social', 'url', __( 'واتس‌اپ', 'technopay' ) ),
			array( 'social_youtube', 'technopay_social', 'url', __( 'یوتیوب / آپارات', 'technopay' ) ),

			// فوتر.
			array( 'footer_about', 'technopay_footer', 'textarea', __( 'متن درباره ما', 'technopay' ) ),
			array( 'contact_phone', 'technopay_footer', 'text', __( 'تلفن', 'technopay' ) ),
			array( 'contact_email', 'technopay_footer', 'email', __( 'ایمیل', 'technopay' ) ),
			array( 'contact_address', 'technopay_footer', 'textarea', __( 'آدرس', 'technopay' ) ),
			array( 'app_android_url', 'technopay_footer', 'url', __( 'لینک دانلود اپ اندروید', 'technopay' ) ),
			array( 'app_ios_url', 'technopay_footer', 'url', __( 'لینک وب‌اپ / iOS', 'technopay' ) ),
			array( 'footer_trust_html', 'technopay_footer', 'trust', __( 'کد نمادهای اعتماد (اینماد، ساماندهی و ...)', 'technopay' ) ),
			array( 'footer_copyright', 'technopay_footer', 'text', __( 'متن کپی‌رایت (خالی = پیش‌فرض)', 'technopay' ) ),

			// عمومی.
			array( 'jalali_dates', 'technopay_general', 'checkbox', __( 'نمایش تاریخ شمسی (اگر افزونه پارسی‌دیت فعال باشد، از آن استفاده می‌شود)', 'technopay' ) ),
			array( 'letter_avatars', 'technopay_general', 'checkbox', __( 'آواتار حرفی به جای گراواتار (سریع‌تر در ایران)', 'technopay' ) ),
			array( 'show_toc', 'technopay_general', 'checkbox', __( 'فهرست مطالب خودکار داخل نوشته‌ها (حداقل ۳ سرتیتر)', 'technopay' ) ),
			array( 'policy_text', 'technopay_general', 'text', __( 'متن لینک زیر عنوان نوشته (مثلاً «سیاست انتشار مطالب»)', 'technopay' ) ),
			array( 'policy_url', 'technopay_general', 'url', __( 'آدرس لینک بالا (خالی = مخفی)', 'technopay' ) ),
		)
	);

	$defaults = technopay_defaults();
	foreach ( $fields as $field ) {
		list( $key, $section, $type, $label ) = $field;

		switch ( $type ) {
			case 'checkbox':
				$sanitize = 'technopay_sanitize_checkbox';
				break;
			case 'url':
				$sanitize = 'esc_url_raw';
				break;
			case 'email':
				$sanitize = 'sanitize_email';
				break;
			case 'number':
			case 'select':
				$sanitize = 'absint';
				break;
			case 'textarea':
				$sanitize = 'sanitize_textarea_field';
				break;
			case 'trust':
				$sanitize = 'technopay_sanitize_trust_html';
				break;
			default:
				$sanitize = 'sanitize_text_field';
		}

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $sanitize,
			)
		);

		$control = array(
			'label'   => $label,
			'section' => $section,
			'type'    => 'trust' === $type ? 'textarea' : $type,
		);
		if ( 'select' === $type && isset( $field[4] ) ) {
			$control['choices'] = $field[4];
		}
		if ( 'number' === $type ) {
			$control['input_attrs'] = array(
				'min'  => 0,
				'step' => 1,
			);
		}
		$wp_customize->add_control( $key, $control );
	}

	// لوگو: خود لوگو را وردپرس در «هویت سایت» می‌گیرد؛ اینجا نسخهٔ حالت تاریک و اندازه را اضافه می‌کنیم.
	$wp_customize->add_setting(
		'logo_dark',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'logo_dark',
			array(
				'label'       => __( 'لوگوی حالت تاریک (اختیاری)', 'technopay' ),
				'description' => __( 'نسخه‌ای از لوگو که روی زمینهٔ تیره خوانا باشد (مثلاً سفید). پیشنهاد: PNG یا WebP با پس‌زمینهٔ شفاف.', 'technopay' ),
				'section'     => 'title_tagline',
				'mime_type'   => 'image',
				'priority'    => 9,
			)
		)
	);
	$wp_customize->add_setting(
		'logo_invert_dark',
		array(
			'default'           => false,
			'sanitize_callback' => 'technopay_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'logo_invert_dark',
		array(
			'label'       => __( 'اگر لوگوی حالت تاریک ندارید: لوگو در حالت تاریک سفید شود', 'technopay' ),
			'description' => __( 'مناسب لوگوی تک‌رنگ؛ برای لوگوی رنگی بهتر است نسخهٔ جداگانه بالا را بگذارید.', 'technopay' ),
			'section'     => 'title_tagline',
			'type'        => 'checkbox',
			'priority'    => 9,
		)
	);
	$wp_customize->add_setting(
		'logo_height',
		array(
			'default'           => 40,
			'sanitize_callback' => 'technopay_sanitize_logo_height',
		)
	);
	$wp_customize->add_control(
		'logo_height',
		array(
			'label'       => __( 'ارتفاع لوگو در هدر (پیکسل)', 'technopay' ),
			'section'     => 'title_tagline',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 24,
				'max'  => 72,
				'step' => 2,
			),
			'priority'    => 9,
		)
	);
}
add_action( 'customize_register', 'technopay_customize_register' );
