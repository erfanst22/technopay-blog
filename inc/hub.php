<?php
/**
 * منوی مستقل «مجله تکنوپی» در پیشخوان: صفحهٔ شروع (ورود به ویرایشگر بصری) و چیدمان لیستی.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

const TECHNOPAY_HUB_PAGE = 'technopay';

/**
 * ثبت منوی بالاسطحی و زیرمنوها.
 */
function technopay_hub_menu() {
	add_menu_page(
		__( 'مجله تکنوپی', 'technopay' ),
		__( 'مجله تکنوپی', 'technopay' ),
		'edit_theme_options',
		TECHNOPAY_HUB_PAGE,
		'technopay_hub_page',
		'dashicons-layout',
		58
	);
	add_submenu_page(
		TECHNOPAY_HUB_PAGE,
		__( 'ویرایشگر بصری', 'technopay' ),
		__( 'ویرایشگر بصری', 'technopay' ),
		'edit_theme_options',
		TECHNOPAY_HUB_PAGE,
		'technopay_hub_page'
	);
	add_submenu_page(
		TECHNOPAY_HUB_PAGE,
		__( 'تنظیمات', 'technopay' ),
		__( 'تنظیمات', 'technopay' ),
		'edit_theme_options',
		TECHNOPAY_SETTINGS_PAGE,
		'technopay_settings_page'
	);
	add_submenu_page(
		TECHNOPAY_HUB_PAGE,
		__( 'چیدمان لیستی', 'technopay' ),
		__( 'چیدمان لیستی', 'technopay' ),
		'edit_theme_options',
		TECHNOPAY_LAYOUT_PAGE,
		'technopay_layout_admin_page'
	);
}
add_action( 'admin_menu', 'technopay_hub_menu' );

/**
 * آدرس قدیمی «مجله تکنوپی» به جای جدیدش می‌رود.
 */
function technopay_hub_legacy_redirect() {
	global $pagenow;
	if ( 'themes.php' === $pagenow && isset( $_GET['page'] ) && TECHNOPAY_LAYOUT_PAGE === $_GET['page'] && current_user_can( 'edit_theme_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$args = array();
		foreach ( wp_unslash( $_GET ) as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( is_scalar( $value ) ) {
				$args[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
			}
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
add_action( 'admin_menu', 'technopay_hub_legacy_redirect', 99 ); // پیش از بررسی دسترسی صفحه (که برای آدرس قدیمی ۴۰۳ می‌دهد).

/**
 * فایل‌های صفحهٔ شروع.
 *
 * @param string $hook صفحهٔ فعلی.
 */
function technopay_hub_assets( $hook ) {
	if ( 'toplevel_page_' . TECHNOPAY_HUB_PAGE !== $hook ) { // هوک صفحهٔ بالاسطحی همیشه toplevel_page_{slug} است.
		return;
	}
	wp_enqueue_style( 'technopay-hub', TECHNOPAY_URI . '/assets/css/hub.css', array(), TECHNOPAY_VERSION );
}
add_action( 'admin_enqueue_scripts', 'technopay_hub_assets' );

/**
 * انتشار یا دور انداختن پیش‌نویس از صفحهٔ شروع.
 */
function technopay_hub_handle_draft() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه دسترسی به این بخش را ندارید.', 'technopay' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'technopay_hub_draft' );
	$do = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : '';
	if ( 'publish' === $do ) {
		technopay_editor_publish_draft();
	} elseif ( 'discard' === $do ) {
		technopay_draft_clear();
	}
	wp_safe_redirect( add_query_arg( 'tph-msg', $do, admin_url( 'admin.php?page=' . TECHNOPAY_HUB_PAGE ) ) );
	exit;
}
add_action( 'admin_post_technopay_hub_draft', 'technopay_hub_handle_draft' );

/**
 * صفحهٔ شروع «مجله تکنوپی».
 */
function technopay_hub_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$msg     = isset( $_GET['tph-msg'] ) ? sanitize_key( wp_unslash( $_GET['tph-msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$targets = technopay_editor_targets();
	$dirty   = technopay_draft_dirty();
	?>
	<div class="wrap tph" dir="rtl">
		<h1><?php esc_html_e( 'مجله تکنوپی', 'technopay' ); ?></h1>
		<p class="tph-lead"><?php esc_html_e( 'ظاهر سایت را مثل المنتور مستقیم روی خود صفحه عوض کنید: عنصرها را بکشید و جابه‌جا کنید، اضافه یا حذف کنید، اندازهٔ فونت و رنگ را تغییر دهید و وقتی راضی بودید «انتشار» را بزنید. تا آن موقع بازدیدکنندگان چیزی نمی‌بینند.', 'technopay' ); ?></p>

		<?php if ( 'publish' === $msg ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'پیش‌نویس منتشر شد و روی سایت اعمال شد.', 'technopay' ); ?></p></div>
		<?php elseif ( 'discard' === $msg ) : ?>
			<div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'پیش‌نویس دور انداخته شد.', 'technopay' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $dirty ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="tph-draft">
				<input type="hidden" name="action" value="technopay_hub_draft">
				<?php wp_nonce_field( 'technopay_hub_draft' ); ?>
				<strong><?php esc_html_e( 'تغییرات منتشرنشده دارید.', 'technopay' ); ?></strong>
				<span><?php esc_html_e( 'برای دیدن آن‌ها یکی از صفحه‌ها را در ویرایشگر باز کنید.', 'technopay' ); ?></span>
				<button type="submit" name="do" value="publish" class="button button-primary"><?php esc_html_e( 'انتشار روی سایت', 'technopay' ); ?></button>
				<button type="submit" name="do" value="discard" class="button" onclick="return confirm('<?php echo esc_js( __( 'پیش‌نویس دور انداخته شود؟', 'technopay' ) ); ?>');"><?php esc_html_e( 'دور انداختن', 'technopay' ); ?></button>
			</form>
		<?php endif; ?>

		<h2><?php esc_html_e( 'ویرایش بصری صفحه‌ها', 'technopay' ); ?></h2>
		<div class="tph-grid">
			<?php foreach ( $targets as $id => $target ) : ?>
				<div class="tph-card">
					<h3><?php echo esc_html( $target['label'] ); ?></h3>
					<p><?php echo esc_html( $target['hint'] ); ?></p>
					<?php if ( '' === $target['url'] ) : ?>
						<p class="description"><?php esc_html_e( 'برای ویرایش این صفحه، اول یک مقاله منتشر کنید.', 'technopay' ); ?></p>
					<?php else : ?>
						<p>
							<a class="button button-primary" href="<?php echo esc_url( technopay_editor_link( $target['url'] ) ); ?>"><?php esc_html_e( 'ویرایش بصری', 'technopay' ); ?></a>
							<a class="button" href="<?php echo esc_url( technopay_editor_link( $target['url'], 'preview' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'پیش‌نمایش پیش‌نویس', 'technopay' ); ?></a>
						</p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<h2><?php esc_html_e( 'چه کارهایی می‌شود کرد؟', 'technopay' ); ?></h2>
		<ul class="tph-tips">
			<li><?php esc_html_e( 'هر عنصر را با دستگیرهٔ ⠿ بکشید و جای دیگری رها کنید؛ حتی به ناحیهٔ دیگر (مثلاً «قبلی/بعدی» را زیر «مطالب مرتبط» ببرید).', 'technopay' ); ?></li>
			<li><?php esc_html_e( 'روی هر عنصر کلیک کنید تا تنظیماتش (عنوان، تعداد، ...) در پنل کناری باز شود؛ با «+ افزودن عنصر» یا تب «عناصر» چیز جدید اضافه کنید.', 'technopay' ); ?></li>
			<li><?php esc_html_e( 'تب «سبک سایت»: اندازهٔ فونت کل سایت، اندازهٔ متن مقاله، عرض محتوا، رنگ برند و رنگ متن دکمه‌ها.', 'technopay' ); ?></li>
			<li><?php esc_html_e( 'تب «صفحه‌ها»: بین صفحه اصلی، مقاله، آرشیو، جستجو و ۴۰۴ جابه‌جا شوید و جای سایدبار را عوض کنید.', 'technopay' ); ?></li>
		</ul>

		<h2><?php esc_html_e( 'ابزارهای دیگر', 'technopay' ); ?></h2>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=technopay' ) ); ?>"><?php esc_html_e( 'سفارشی‌سازی (لوگو، منوها، متن‌ها، لینک‌ها)', 'technopay' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . TECHNOPAY_SETTINGS_PAGE ) ); ?>"><?php esc_html_e( 'تنظیمات (تگ منیجر گوگل و کدهای سفارشی)', 'technopay' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . TECHNOPAY_LAYOUT_PAGE ) ); ?>"><?php esc_html_e( 'چیدمان لیستی (بدون ویرایشگر بصری)', 'technopay' ); ?></a>
		</p>
	</div>
	<?php
}
