<?php
/**
 * صفحه مدیریت «نمایش ← چیدمان صفحات»: چیدن بخش‌های هر صفحه با کشیدن و رها کردن.
 *
 * رابط با جاوااسکریپت (assets/js/layout-admin.js + SortableJS) ساخته می‌شود و با یک فرم معمولی
 * (admin-post.php، nonce و دسترسی edit_theme_options) ذخیره می‌شود؛ ورودی سمت سرور کاملاً پاک‌سازی می‌شود.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

const TECHNOPAY_LAYOUT_PAGE = 'technopay-layout';

/**
 * افزودن صفحه به منوی «نمایش».
 */
function technopay_layout_admin_menu() {
	add_theme_page(
		__( 'چیدمان صفحات', 'technopay' ),
		__( 'چیدمان صفحات', 'technopay' ),
		'edit_theme_options',
		TECHNOPAY_LAYOUT_PAGE,
		'technopay_layout_admin_page'
	);
}
add_action( 'admin_menu', 'technopay_layout_admin_menu' );

/**
 * فایل‌های CSS/JS (فقط در همین صفحه).
 *
 * @param string $hook صفحه جاری.
 */
function technopay_layout_admin_assets( $hook ) {
	if ( 'appearance_page_' . TECHNOPAY_LAYOUT_PAGE !== $hook ) {
		return;
	}
	wp_enqueue_style( 'technopay-layout-admin', TECHNOPAY_URI . '/assets/css/layout-admin.css', array(), TECHNOPAY_VERSION );
	wp_enqueue_script( 'technopay-sortable', TECHNOPAY_URI . '/assets/vendor/sortable.min.js', array(), '1.15.7', true );
	wp_enqueue_script( 'technopay-layout-admin', TECHNOPAY_URI . '/assets/js/layout-admin.js', array( 'technopay-sortable' ), TECHNOPAY_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'technopay_layout_admin_assets' );

/**
 * آدرس آدرس پیش‌نمایش هر صفحه.
 *
 * @return array<string,string>
 */
function technopay_layout_preview_urls() {
	$post = get_posts(
		array(
			'numberposts'         => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	$cats = technopay_get_top_categories( 1 );
	return array(
		'home'    => home_url( '/' ),
		'single'  => $post ? get_permalink( $post[0] ) : home_url( '/' ),
		'archive' => $cats ? get_category_link( $cats[0] ) : home_url( '/' ),
		'search'  => get_search_link( 'تکنوپی' ),
		'404'     => home_url( '/technopay-404-preview/' ),
	);
}

/**
 * پیکربندی ارسالی به جاوااسکریپت.
 *
 * @return array
 */
function technopay_layout_admin_config() {
	$registry = technopay_layout_registry();
	$previews = technopay_layout_preview_urls();

	$pages = array();
	foreach ( $registry['pages'] as $page_id => $page ) {
		$regions = array();
		foreach ( $page['regions'] as $region_id => $region ) {
			$regions[ $region_id ] = array(
				'label'    => $region['label'],
				'desc'     => isset( $region['desc'] ) ? $region['desc'] : '',
				'blocks'   => array_values( $region['blocks'] ),
				'required' => isset( $region['required'] ) ? array_values( $region['required'] ) : array(),
			);
		}
		$pages[ $page_id ] = array(
			'label'   => $page['label'],
			'preview' => isset( $previews[ $page_id ] ) ? $previews[ $page_id ] : home_url( '/' ),
			'options' => (object) $page['options'],
			'regions' => $regions,
		);
	}

	$blocks = array();
	foreach ( $registry['blocks'] as $id => $block ) {
		$blocks[ $id ] = array(
			'label'    => $block['label'],
			'desc'     => $block['desc'],
			'settings' => (object) $block['settings'],
		);
	}

	$categories = array();
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$categories[] = array(
			'id'   => (int) $cat->term_id,
			'name' => $cat->name,
		);
	}

	$state = technopay_layout_get();
	foreach ( $state as $page_id => $data ) {
		$state[ $page_id ]['options'] = (object) $data['options'];
		foreach ( $data['regions'] as $region_id => $list ) {
			foreach ( $list as $i => $item ) {
				$state[ $page_id ]['regions'][ $region_id ][ $i ]['settings'] = (object) $item['settings'];
			}
		}
	}

	// حالت پیش‌فرض (برای دکمه «بازگردانی به پیش‌فرض» در همان مرورگر، بدون رفت‌وبرگشت).
	$defaults = technopay_layout_defaults();
	foreach ( $defaults as $page_id => $data ) {
		$defaults[ $page_id ]['options'] = (object) $data['options'];
		foreach ( $data['regions'] as $region_id => $list ) {
			foreach ( $list as $i => $item ) {
				$defaults[ $page_id ]['regions'][ $region_id ][ $i ]['settings'] = (object) $item['settings'];
			}
		}
	}

	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'home'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	return array(
		'order'      => array_map( 'strval', array_keys( $pages ) ), // ترتیب تب‌ها (جاوااسکریپت کلیدهای عددی مثل 404 را همیشه اول می‌گذارد).
		'pages'      => $pages,
		'blocks'     => $blocks,
		'state'      => $state,
		'defaults'   => $defaults,
		'categories' => $categories,
		'tab'        => isset( $pages[ $tab ] ) ? $tab : 'home',
		'i18n'       => array(
			'active'      => __( 'فعال؛ به ترتیب نمایش', 'technopay' ),
			'pool'        => __( 'غیرفعال؛ برای افزودن بکشید', 'technopay' ),
			'emptyActive' => __( 'هیچ بخشی فعال نیست؛ از ستون کناری بکشید و اینجا رها کنید.', 'technopay' ),
			'emptyPool'   => __( 'همه بخش‌ها فعال‌اند.', 'technopay' ),
			'required'    => __( 'لازم است', 'technopay' ),
			'settings'    => __( 'تنظیمات', 'technopay' ),
			'moveUp'      => __( 'انتقال به بالا', 'technopay' ),
			'moveDown'    => __( 'انتقال به پایین', 'technopay' ),
			'add'         => __( 'افزودن', 'technopay' ),
			'remove'      => __( 'حذف از صفحه', 'technopay' ),
			'drag'        => __( 'برای جابه‌جایی بکشید', 'technopay' ),
			'auto'        => __( '— خودکار —', 'technopay' ),
			'preview'     => __( 'پیش‌نمایش در تب جدید', 'technopay' ),
			'reset'       => __( 'بازگردانی این صفحه به پیش‌فرض', 'technopay' ),
			'resetAsk'    => __( 'چیدمان این صفحه به حالت پیش‌فرض برگردد؟ تغییرات ذخیره‌نشده از بین می‌رود.', 'technopay' ),
			'pageOptions' => __( 'تنظیمات صفحه', 'technopay' ),
			'unsaved'     => __( 'تغییرات ذخیره‌نشده دارید.', 'technopay' ),
			'moved'       => __( 'جابه‌جا شد', 'technopay' ),
			'added'       => __( 'به صفحه اضافه شد', 'technopay' ),
			'removed'     => __( 'از صفحه حذف شد', 'technopay' ),
			'on'          => __( 'روشن', 'technopay' ),
		),
	);
}

/**
 * پیام‌های بعد از ذخیره/بازنشانی/ایمپورت.
 *
 * @return string HTML اعلان یا رشته خالی.
 */
function technopay_layout_admin_notice() {
	$msg = isset( $_GET['tpl-msg'] ) ? sanitize_key( wp_unslash( $_GET['tpl-msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$map = array(
		'saved'        => array( 'success', __( 'چیدمان ذخیره شد و روی سایت اعمال شد.', 'technopay' ) ),
		'reset'        => array( 'success', __( 'چیدمان این صفحه به حالت پیش‌فرض برگشت.', 'technopay' ) ),
		'imported'     => array( 'success', __( 'چیدمان وارد شد و روی سایت اعمال شد.', 'technopay' ) ),
		'import-error' => array( 'error', __( 'متن واردشده یک چیدمان معتبر نبود؛ چیزی تغییر نکرد.', 'technopay' ) ),
	);
	if ( ! isset( $map[ $msg ] ) ) {
		return '';
	}
	return '<div class="notice notice-' . esc_attr( $map[ $msg ][0] ) . ' is-dismissible"><p>' . esc_html( $map[ $msg ][1] ) . '</p></div>';
}

/**
 * خروجی صفحه مدیریت.
 */
function technopay_layout_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$config = technopay_layout_admin_config();
	$export = wp_json_encode(
		array(
			'version' => 1,
			'pages'   => technopay_layout_get(),
		),
		JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);
	?>
	<div class="wrap tpl-wrap" dir="rtl">
		<h1><?php esc_html_e( 'چیدمان صفحات', 'technopay' ); ?></h1>
		<p class="description tpl-lead"><?php esc_html_e( 'بخش‌های هر صفحه را با کشیدن و رها کردن مرتب کنید: از ستون «غیرفعال» به ستون «فعال» بکشید تا نمایش داده شود و برعکس تا پنهان شود. با دکمهٔ «تنظیمات» هر بخش، عنوان و تعداد و چند گزینهٔ دیگر را عوض کنید. تغییرات پس از «ذخیره» روی سایت اعمال می‌شود.', 'technopay' ); ?></p>

		<?php echo technopay_layout_admin_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="tpl-form">
			<input type="hidden" name="action" value="technopay_layout_save">
			<input type="hidden" name="tab" id="tpl-tab" value="<?php echo esc_attr( $config['tab'] ); ?>">
			<input type="hidden" name="layout" id="tpl-layout" value="">
			<?php wp_nonce_field( 'technopay_layout_save' ); ?>

			<div id="tpl-app" class="tpl-app">
				<noscript><p><?php esc_html_e( 'برای استفاده از چیدمان کشیدن‌ورها کردن، جاوااسکریپت لازم است.', 'technopay' ); ?></p></noscript>
			</div>

			<div class="tpl-savebar">
				<button type="submit" class="button button-primary button-large" id="tpl-save"><?php esc_html_e( 'ذخیره تغییرات', 'technopay' ); ?></button>
				<span class="tpl-dirty" id="tpl-dirty" role="status" aria-live="polite"></span>
			</div>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="tpl-reset-form">
			<input type="hidden" name="action" value="technopay_layout_reset">
			<input type="hidden" name="page" id="tpl-reset-page" value="">
			<?php wp_nonce_field( 'technopay_layout_reset' ); ?>
		</form>

		<details class="tpl-backup">
			<summary><?php esc_html_e( 'پشتیبان‌گیری و انتقال چیدمان', 'technopay' ); ?></summary>
			<p class="description"><?php esc_html_e( 'برای برداشتن نسخه پشتیبان، این متن را ذخیره کنید؛ برای انتقال به سایت دیگر یا بازگردانی، متن را در کادر پایین بچسبانید.', 'technopay' ); ?></p>
			<label for="tpl-export"><?php esc_html_e( 'چیدمان فعلی', 'technopay' ); ?></label>
			<textarea id="tpl-export" class="large-text code" rows="8" readonly dir="ltr"><?php echo esc_textarea( (string) $export ); ?></textarea>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="technopay_layout_import">
				<?php wp_nonce_field( 'technopay_layout_import' ); ?>
				<label for="tpl-import"><?php esc_html_e( 'وارد کردن چیدمان', 'technopay' ); ?></label>
				<textarea id="tpl-import" name="layout" class="large-text code" rows="6" dir="ltr" placeholder='{"version":1,"pages":{ ... }}'></textarea>
				<p><button type="submit" class="button"><?php esc_html_e( 'وارد کردن', 'technopay' ); ?></button></p>
			</form>
		</details>

		<script type="application/json" id="tpl-config"><?php echo wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON with hex-escaped markup characters. ?></script>
	</div>
	<?php
}

/**
 * آدرس بازگشت به صفحه مدیریت.
 *
 * @param string $msg  کد پیام.
 * @param string $tab  تب فعال.
 * @return string
 */
function technopay_layout_admin_url( $msg, $tab = 'home' ) {
	return add_query_arg(
		array(
			'page'    => TECHNOPAY_LAYOUT_PAGE,
			'tab'     => sanitize_key( $tab ),
			'tpl-msg' => $msg,
		),
		admin_url( 'themes.php' )
	);
}

/**
 * دسترسی و nonce مشترک هر سه عملیات.
 *
 * @param string $action نام nonce.
 */
function technopay_layout_check_request( $action ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه دسترسی به این بخش را ندارید.', 'technopay' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( $action );
}

/**
 * رمزگشایی ایمن JSON ورودی فرم.
 *
 * @param string $raw متن JSON.
 * @return array|null
 */
function technopay_layout_decode( $raw ) {
	$raw = (string) $raw;
	if ( '' === trim( $raw ) || strlen( $raw ) > 400000 ) {
		return null;
	}
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : null;
}

/**
 * ذخیره چیدمان.
 */
function technopay_layout_handle_save() {
	technopay_layout_check_request( 'technopay_layout_save' );
	$tab  = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'home';
	$data = technopay_layout_decode( isset( $_POST['layout'] ) ? wp_unslash( $_POST['layout'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON sanitized by technopay_layout_sanitize().
	if ( null === $data ) {
		wp_safe_redirect( technopay_layout_admin_url( 'import-error', $tab ) );
		exit;
	}
	technopay_layout_save( technopay_layout_sanitize( $data ) );
	wp_safe_redirect( technopay_layout_admin_url( 'saved', $tab ) );
	exit;
}
add_action( 'admin_post_technopay_layout_save', 'technopay_layout_handle_save' );

/**
 * بازگرداندن یک صفحه به پیش‌فرض.
 */
function technopay_layout_handle_reset() {
	technopay_layout_check_request( 'technopay_layout_reset' );
	$page     = isset( $_POST['page'] ) ? sanitize_key( wp_unslash( $_POST['page'] ) ) : '';
	$registry = technopay_layout_registry();
	if ( isset( $registry['pages'][ $page ] ) ) {
		technopay_layout_reset_page( $page );
	}
	wp_safe_redirect( technopay_layout_admin_url( 'reset', $page ) );
	exit;
}
add_action( 'admin_post_technopay_layout_reset', 'technopay_layout_handle_reset' );

/**
 * وارد کردن چیدمان از متن JSON (جایگزین کل چیدمان).
 */
function technopay_layout_handle_import() {
	technopay_layout_check_request( 'technopay_layout_import' );
	$data = technopay_layout_decode( isset( $_POST['layout'] ) ? wp_unslash( $_POST['layout'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON sanitized by technopay_layout_sanitize().
	if ( null === $data ) {
		wp_safe_redirect( technopay_layout_admin_url( 'import-error' ) );
		exit;
	}
	$clean = technopay_layout_sanitize( $data );
	if ( empty( $clean['pages'] ) ) {
		wp_safe_redirect( technopay_layout_admin_url( 'import-error' ) );
		exit;
	}
	delete_option( TECHNOPAY_LAYOUT_OPTION );
	technopay_layout_save( $clean );
	wp_safe_redirect( technopay_layout_admin_url( 'imported' ) );
	exit;
}
add_action( 'admin_post_technopay_layout_import', 'technopay_layout_handle_import' );
