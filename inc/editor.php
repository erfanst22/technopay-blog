<?php
/**
 * ویرایشگر بصری «مجله تکنوپی»: ویرایش مستقیم روی خود صفحه با کشیدن و رها کردن (شبیه المنتور، در حد ساده).
 *
 * - فقط برای کاربر دارای دسترسی edit_theme_options و فقط با ?tpb=1 (ویرایش) یا ?tpb=preview (پیش‌نمایش پیش‌نویس).
 * - تغییرات ابتدا در «پیش‌نویس» شخصی همان مدیر ذخیره می‌شود و بازدیدکنندگان چیزی نمی‌بینند؛
 *   با «انتشار» روی سایت اعمال می‌شود (همان گزینهٔ چیدمان و تنظیمات قالب).
 * - ذخیرهٔ پیش‌نویس و انتشار با admin-ajax.php، nonce و بررسی دسترسی؛ ورودی کاملاً پاک‌سازی می‌شود.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

const TECHNOPAY_DRAFT_META = '_technopay_visual_draft';

/**
 * کلید متای پیش‌نویس؛ در چندسایتی هر سایت پیش‌نویس جدا دارد (متای کاربر سراسری است).
 *
 * @return string
 */
function technopay_draft_meta_key() {
	return TECHNOPAY_DRAFT_META . '_' . get_current_blog_id();
}

/* ==========================================================================
   وضعیت ویرایشگر
   ========================================================================== */

/**
 * حالت ویرایشگر برای این درخواست: edit، preview یا رشتهٔ خالی.
 *
 * @return string
 */
function technopay_editor_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	if ( ! did_action( 'init' ) ) {
		return '';
	}
	$state = '';
	if ( is_admin() || wp_doing_ajax() || ! isset( $_GET['tpb'] ) || ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- فقط نمایش؛ تغییری ذخیره نمی‌شود.
		return $state;
	}
	$mode  = sanitize_key( wp_unslash( $_GET['tpb'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$state = 'preview' === $mode ? 'preview' : 'edit';
	return $state;
}

/**
 * آیا ویرایشگر بصری (با رابط ویرایش) فعال است؟
 *
 * @return bool
 */
function technopay_is_editing() {
	return 'edit' === technopay_editor_state();
}

/* ==========================================================================
   پیش‌نویس
   ========================================================================== */

/**
 * پیش‌نویس کاربر فعلی: pages (چیدمان صفحه‌ها) و style (تنظیمات سبک).
 *
 * @param bool $refresh خواندن دوباره از دیتابیس.
 * @return array{pages:array,style:array}
 */
function technopay_draft_get( $refresh = false ) {
	static $draft = null;
	if ( null === $draft || $refresh ) {
		$raw   = get_user_meta( get_current_user_id(), technopay_draft_meta_key(), true );
		$draft = array(
			'pages' => is_array( $raw ) && isset( $raw['pages'] ) && is_array( $raw['pages'] ) ? $raw['pages'] : array(),
			'style' => is_array( $raw ) && isset( $raw['style'] ) && is_array( $raw['style'] ) ? $raw['style'] : array(),
		);
	}
	return $draft;
}

/**
 * چیدمان صفحه‌های پیش‌نویس.
 *
 * @return array
 */
function technopay_draft_pages() {
	$draft = technopay_draft_get();
	return $draft['pages'];
}

/**
 * ذخیرهٔ پیش‌نویس.
 *
 * @param array $pages چیدمان پاک‌سازی‌شدهٔ صفحه‌ها.
 * @param array $style تنظیمات سبک پاک‌سازی‌شده.
 */
function technopay_draft_save( array $pages, array $style ) {
	if ( ! $pages && ! $style ) {
		technopay_draft_clear();
		return;
	}
	update_user_meta(
		get_current_user_id(),
		technopay_draft_meta_key(),
		array(
			'pages' => $pages,
			'style' => $style,
		)
	);
	technopay_draft_get( true );
}

/**
 * حذف پیش‌نویس کاربر.
 *
 * @param int $user_id شناسهٔ کاربر (۰ = کاربر فعلی).
 */
function technopay_draft_clear( $user_id = 0 ) {
	delete_user_meta( $user_id ? $user_id : get_current_user_id(), technopay_draft_meta_key() );
	technopay_draft_get( true );
}

/**
 * آیا پیش‌نویسی منتشرنشده وجود دارد؟
 *
 * @return bool
 */
function technopay_draft_dirty() {
	$draft = technopay_draft_get();
	return (bool) ( $draft['pages'] || $draft['style'] );
}

/**
 * با ذخیره/بازنشانی چیدمان از صفحهٔ لیستی (یا تعویض قالب)، پیش‌نویس همهٔ مدیرها کنار گذاشته می‌شود.
 */
function technopay_draft_clear_all_users() {
	$ids = get_users(
		array(
			'meta_key' => technopay_draft_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'fields'   => 'ID',
			'number'   => 500,
		)
	);
	foreach ( $ids as $user_id ) {
		delete_user_meta( (int) $user_id, technopay_draft_meta_key() ); // کش متا هم درست پاک می‌شود.
	}
	technopay_draft_get( true );
}
add_action( 'switch_theme', 'technopay_draft_clear_all_users' );

/* ==========================================================================
   سبک کلی سایت (اندازهٔ فونت، عرض محتوا، رنگ)
   ========================================================================== */

/**
 * تعریف تنظیمات عددی سبک.
 *
 * @return array<string,array>
 */
function technopay_style_schema() {
	return array(
		'fs_scale'    => array(
			'label'   => __( 'اندازهٔ فونت کل سایت', 'technopay' ),
			'min'     => 80,
			'max'     => 130,
			'step'    => 5,
			'default' => 100,
			'unit'    => '%',
		),
		'fs_article'  => array(
			'label'   => __( 'اندازهٔ متن مقاله (علاوه بر بالا)', 'technopay' ),
			'min'     => 80,
			'max'     => 140,
			'step'    => 5,
			'default' => 100,
			'unit'    => '%',
		),
		'container_w' => array(
			'label'   => __( 'عرض محتوای سایت', 'technopay' ),
			'min'     => 1040,
			'max'     => 1440,
			'step'    => 20,
			'default' => 1240,
			'unit'    => 'px',
		),
	);
}

/**
 * پاک‌سازی یک مقدار سبک (عددی در بازه، رنگ یا حالت متن دکمه).
 *
 * @param string $key   کلید.
 * @param mixed  $value مقدار.
 * @return mixed|null null = کلید نامعتبر.
 */
function technopay_style_sanitize( $key, $value ) {
	$schema = technopay_style_schema();
	if ( isset( $schema[ $key ] ) ) {
		$def = $schema[ $key ];
		return min( $def['max'], max( $def['min'], is_numeric( $value ) ? (int) $value : $def['default'] ) );
	}
	if ( 'brand_color' === $key ) {
		return technopay_sanitize_brand_color( $value );
	}
	if ( 'button_text' === $key ) {
		return 'auto' === $value ? 'auto' : 'white';
	}
	return null;
}

/**
 * مقدار فعلی یک تنظیم سبک (با پیش‌نویس در حالت ویرایش).
 *
 * @param string $key کلید عددی.
 * @return int
 */
function technopay_style_value( $key ) {
	return (int) technopay_style_sanitize( $key, technopay_option( $key ) );
}

/**
 * CSS متغیرهای سبک (فقط مقدارهای غیرپیش‌فرض).
 *
 * @return string
 */
function technopay_style_css() {
	$vars = '';
	$fs   = technopay_style_value( 'fs_scale' );
	$art  = technopay_style_value( 'fs_article' );
	$cw   = technopay_style_value( 'container_w' );
	if ( 100 !== $fs ) {
		$vars .= '--fs-global:' . round( $fs / 100, 3 ) . ';';
	}
	if ( 100 !== $art ) {
		$vars .= '--fs-article:' . round( $art / 100, 3 ) . ';';
	}
	if ( 1240 !== $cw ) {
		$vars .= '--container:' . $cw . 'px;';
	}
	return '' === $vars ? '' : ':root{' . $vars . '}';
}

/**
 * آماده‌سازی درخواست ویرایش/پیش‌نمایش: بدون کش، بدون نوار مدیریت، با تنظیمات سبک پیش‌نویس.
 */
function technopay_editor_boot() {
	if ( ! technopay_editor_state() ) {
		return;
	}
	nocache_headers();
	send_frame_options_header(); // جلوگیری از قرار گرفتن صفحهٔ ویرایش داخل iframe سایت دیگر.
	if ( technopay_is_editing() ) {
		add_filter( 'show_admin_bar', '__return_false' );
	}
	foreach ( technopay_draft_get()['style'] as $key => $value ) {
		$clean = technopay_style_sanitize( $key, $value );
		if ( null !== $clean ) {
			add_filter(
				'theme_mod_' . $key,
				static function () use ( $clean ) {
					return $clean;
				}
			);
		}
	}
}
add_action( 'wp', 'technopay_editor_boot' );

/* ==========================================================================
   آدرس‌های ویرایش
   ========================================================================== */

/**
 * آدرس نمونهٔ هر صفحه برای ویرایش (بدون پارامتر ویرایشگر).
 *
 * @return array<string,array{label:string,url:string,hint:string}>
 */
function technopay_editor_targets() {
	$post = get_posts(
		array(
			'numberposts'         => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	$cats = technopay_get_top_categories( 1 );
	$word = 'تکنوپی';
	if ( $post ) {
		foreach ( preg_split( '/\s+/u', wp_strip_all_tags( get_the_title( $post[0] ) ) ) as $w ) {
			if ( mb_strlen( $w, 'UTF-8' ) >= 4 ) {
				$word = $w;
				break;
			}
		}
	}
	return array(
		'home'    => array(
			'label' => __( 'صفحه اصلی', 'technopay' ),
			'hint'  => __( 'اسلایدر، ردیف‌ها، دسته‌ها، بنر، خبرنامه و ...', 'technopay' ),
			'url'   => home_url( '/' ),
		),
		'single'  => array(
			'label' => __( 'صفحه مقاله', 'technopay' ),
			'hint'  => __( 'عنوان، متن، سایدبار، نویسنده، قبلی/بعدی، دیدگاه‌ها و مرتبط‌ها', 'technopay' ),
			'url'   => $post ? get_permalink( $post[0] ) : '',
		),
		'archive' => array(
			'label' => __( 'آرشیو (دسته، برچسب، نویسنده)', 'technopay' ),
			'hint'  => __( 'لیست مقالات و سایدبار', 'technopay' ),
			'url'   => $cats ? get_category_link( $cats[0] ) : home_url( '/' ),
		),
		'search'  => array(
			'label' => __( 'جستجو', 'technopay' ),
			'hint'  => __( 'نتایج جستجو و سایدبار', 'technopay' ),
			'url'   => get_search_link( $word ),
		),
		'404'     => array(
			'label' => __( 'صفحه ۴۰۴', 'technopay' ),
			'hint'  => __( 'وقتی آدرس پیدا نشود', 'technopay' ),
			'url'   => home_url( '/technopay-404-preview/' ),
		),
	);
}

/**
 * آدرس ویرایشگر برای یک آدرس.
 *
 * @param string $url  آدرس صفحه.
 * @param string $mode edit یا preview.
 * @return string
 */
function technopay_editor_link( $url, $mode = 'edit' ) {
	return add_query_arg( 'tpb', 'preview' === $mode ? 'preview' : '1', $url );
}

/**
 * آدرس صفحهٔ فعلی (برای نوار مدیریت و خروج).
 *
 * @return string
 */
function technopay_editor_current_url() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- با esc_url_raw پاک‌سازی می‌شود.
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	return remove_query_arg( 'tpb', esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $host . $uri ) );
}

/**
 * شناسهٔ صفحهٔ فعلی بر اساس ناحیه‌هایی که چاپ شده‌اند.
 *
 * @return string
 */
function technopay_editor_current_page() {
	$regions = technopay_editor_registry();
	$keys    = array_keys( $regions['regions'] );
	return $keys ? (string) strtok( (string) $keys[0], '.' ) : '';
}

/**
 * ثبت ناحیه‌ها و بلوک‌هایی که در این درخواست چاپ شده‌اند.
 *
 * @param string|null $region_key کلید page.region.
 * @param string|null $uid        شناسهٔ بلوک.
 * @param array|null  $settings   تنظیمات بلوک.
 * @return array{regions:array,blocks:array}
 */
function technopay_editor_registry( $region_key = null, $uid = null, $settings = null ) {
	static $reg = array(
		'regions' => array(),
		'blocks'  => array(),
	);
	if ( null !== $region_key ) {
		$reg['regions'][ $region_key ] = true;
	}
	if ( null !== $uid ) {
		$reg['blocks'][ $uid ] = $settings;
	}
	return $reg;
}

/**
 * ثبت یک بلوک چاپ‌شده و برگرداندن شناسهٔ یکتای آن.
 *
 * @param string $page_id   صفحه.
 * @param string $region_id ناحیه.
 * @param int    $index     جایگاه.
 * @param string $block_id  شناسهٔ بلوک.
 * @param array  $settings  تنظیمات.
 * @return string
 */
function technopay_editor_register_block( $page_id, $region_id, $index, $block_id, $settings ) {
	$key = $page_id . '.' . $region_id;
	$uid = $key . '.' . $index;
	technopay_editor_registry( $key, $uid, $settings );
	unset( $block_id );
	return $uid;
}

/* ==========================================================================
   فایل‌ها و پیکربندی
   ========================================================================== */

/**
 * CSS/JS ویرایشگر (فقط در حالت ویرایش).
 */
function technopay_editor_assets() {
	if ( ! technopay_is_editing() ) {
		return;
	}
	wp_enqueue_style( 'technopay-editor-ui', TECHNOPAY_URI . '/assets/css/editor-ui.css', array( 'technopay-main' ), TECHNOPAY_VERSION );
	wp_enqueue_script( 'technopay-sortable', TECHNOPAY_URI . '/assets/vendor/sortable.min.js', array(), '1.15.7', true );
	wp_enqueue_script( 'technopay-editor-ui', TECHNOPAY_URI . '/assets/js/editor-ui.js', array( 'technopay-sortable' ), TECHNOPAY_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'technopay_editor_assets', 30 );

/**
 * کلاس body در حالت ویرایش.
 *
 * @param string[] $classes کلاس‌ها.
 * @return string[]
 */
function technopay_editor_body_class( $classes ) {
	if ( technopay_is_editing() ) {
		$classes[] = 'tpb-editing';
	}
	return $classes;
}
add_filter( 'body_class', 'technopay_editor_body_class' );

/**
 * پیکربندی ارسالی به جاوااسکریپت ویرایشگر.
 *
 * @return array
 */
function technopay_editor_config() {
	$registry = technopay_layout_registry();
	$reg      = technopay_editor_registry();
	$page     = technopay_editor_current_page();
	$layout   = technopay_layout_get();
	$page_def = isset( $registry['pages'][ $page ] ) ? $registry['pages'][ $page ] : array(
		'label'   => '',
		'regions' => array(),
		'options' => array(),
	);

	$regions = array();
	$allowed = array();
	foreach ( $page_def['regions'] as $region_id => $region ) {
		$regions[ $region_id ] = array(
			'label'    => $region['label'],
			'desc'     => isset( $region['desc'] ) ? $region['desc'] : '',
			'kind'     => isset( $region['kind'] ) ? $region['kind'] : 'wide',
			'blocks'   => array_values( $region['blocks'] ),
			'required' => isset( $region['required'] ) ? array_values( $region['required'] ) : array(),
		);
		$allowed = array_merge( $allowed, $region['blocks'] );
	}
	$blocks = array();
	foreach ( array_unique( $allowed ) as $block_id ) {
		if ( ! isset( $registry['blocks'][ $block_id ] ) ) {
			continue;
		}
		$def                = $registry['blocks'][ $block_id ];
		$blocks[ $block_id ] = array(
			'label'    => $def['label'],
			'desc'     => $def['desc'],
			'kind'     => isset( $def['kind'] ) ? $def['kind'] : 'wide',
			'unique'   => ! empty( $def['unique'] ),
			'settings' => (object) $def['settings'],
		);
	}

	$targets = array();
	foreach ( technopay_editor_targets() as $id => $t ) {
		$targets[] = array(
			'id'    => (string) $id, // کلید «404» در PHP عدد می‌شود.
			'label' => $t['label'],
			'hint'  => $t['hint'],
			'url'   => '' === $t['url'] ? '' : technopay_editor_link( $t['url'] ),
		);
	}

	$categories = array();
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		$categories[] = array(
			'id'   => (int) $cat->term_id,
			'name' => $cat->name,
		);
	}

	$style_values = array();
	foreach ( technopay_style_schema() as $key => $def ) {
		$style_values[ $key ] = technopay_style_value( $key );
	}

	$current = technopay_editor_current_url();
	return array(
		'page'       => $page,
		'pageLabel'  => $page_def['label'],
		'ajax'       => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'tpb' ),
		'targets'    => $targets,
		'regions'    => (object) $regions,
		'blocks'     => (object) $blocks,
		'options'    => (object) ( isset( $layout[ $page ]['options'] ) ? $layout[ $page ]['options'] : array() ),
		'optionDefs' => (object) $page_def['options'],
		'settings'   => (object) $reg['blocks'],
		'style'      => array(
			'schema' => (object) technopay_style_schema(),
			'values' => (object) $style_values,
			'brand'  => technopay_brand_color(),
			'btn'    => technopay_button_text_mode(),
		),
		'categories' => $categories,
		'dirty'      => technopay_draft_dirty(),
		'urls'       => array(
			'exit'    => $current,
			'preview' => technopay_editor_link( $current, 'preview' ),
			'hub'     => admin_url( 'admin.php?page=technopay' ),
			'list'    => admin_url( 'admin.php?page=' . TECHNOPAY_LAYOUT_PAGE ),
			'settings' => admin_url( 'admin.php?page=' . TECHNOPAY_SETTINGS_PAGE ),
		),
	);
}

/**
 * چاپ پیکربندی در فوتر.
 */
function technopay_editor_print_config() {
	if ( ! technopay_is_editing() ) {
		return;
	}
	?>
	<script type="application/json" id="tpb-config"><?php echo wp_json_encode( technopay_editor_config(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON with hex-escaped markup characters. ?></script>
	<?php
}
add_action( 'wp_footer', 'technopay_editor_print_config', 5 );

/**
 * دکمهٔ «ویرایش با ویرایشگر بصری» در نوار مدیریت (روی صفحه‌های قابل ویرایش).
 *
 * @param WP_Admin_Bar $bar نوار مدیریت.
 */
function technopay_editor_admin_bar( $bar ) {
	if ( is_admin() || ! current_user_can( 'edit_theme_options' ) || technopay_editor_state() ) {
		return;
	}
	if ( ! ( is_404() || is_search() || is_singular( 'post' ) || is_archive() || is_home() || is_front_page() ) ) {
		return;
	}
	$bar->add_node(
		array(
			'id'    => 'technopay-visual-editor',
			'title' => '<span class="ab-icon dashicons dashicons-layout" style="font-family:dashicons;margin-top:2px"></span> ' . esc_html__( 'ویرایشگر بصری', 'technopay' ),
			'href'  => technopay_editor_link( technopay_editor_current_url() ),
		)
	);
}
add_action( 'admin_bar_menu', 'technopay_editor_admin_bar', 90 );

/* ==========================================================================
   AJAX: پیش‌نویس، انتشار، رنگ
   ========================================================================== */

/**
 * بررسی nonce و دسترسی.
 */
function technopay_editor_ajax_check() {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) || ! check_ajax_referer( 'tpb', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
}

/**
 * رمزگشایی JSON ورودی با محدودیت حجم.
 *
 * @param string $key کلید در $_POST.
 * @return array
 */
function technopay_editor_ajax_json( $key ) {
	$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce در technopay_editor_ajax_check؛ JSON پاک‌سازی می‌شود.
	if ( ! is_string( $raw ) || '' === $raw || strlen( $raw ) > 400000 ) {
		return array();
	}
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : array();
}

/**
 * ذخیرهٔ پیش‌نویس.
 */
function technopay_editor_ajax_save_draft() {
	technopay_editor_ajax_check();
	$registry = technopay_layout_registry();
	$draft    = technopay_draft_get( true );
	$page     = isset( $_POST['page'] ) ? sanitize_key( wp_unslash( $_POST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- در technopay_editor_ajax_check بررسی شد.

	$data = technopay_editor_ajax_json( 'data' );
	if ( $data && isset( $registry['pages'][ $page ] ) ) {
		// فقط ناحیه‌هایی که کلاینت فرستاده جایگزین می‌شوند؛ ناحیهٔ نمایش‌داده‌نشده (مثلاً سایدبار در حالت «بدون سایدبار») حفظ می‌شود.
		$base = isset( $draft['pages'][ $page ] ) ? $draft['pages'][ $page ] : technopay_layout_get()[ $page ];
		$sent = isset( $data['regions'] ) && is_array( $data['regions'] ) ? $data['regions'] : array();
		$data = array(
			'options' => array_merge( isset( $base['options'] ) ? (array) $base['options'] : array(), isset( $data['options'] ) && is_array( $data['options'] ) ? $data['options'] : array() ),
			'regions' => array_merge( isset( $base['regions'] ) ? (array) $base['regions'] : array(), $sent ),
		);
		$clean = technopay_layout_sanitize( array( 'pages' => array( $page => $data ) ) );
		if ( isset( $clean['pages'][ $page ] ) ) {
			$draft['pages'][ $page ] = $clean['pages'][ $page ];
		}
	}
	foreach ( technopay_editor_ajax_json( 'style' ) as $key => $value ) {
		$clean_value = technopay_style_sanitize( sanitize_key( $key ), $value );
		if ( null !== $clean_value ) {
			$draft['style'][ sanitize_key( $key ) ] = $clean_value;
		}
	}
	technopay_draft_save( $draft['pages'], $draft['style'] );
	wp_send_json_success( array( 'dirty' => technopay_draft_dirty() ) );
}
add_action( 'wp_ajax_tpb_save_draft', 'technopay_editor_ajax_save_draft' );

/**
 * پیش‌نویس کاربر فعلی را روی سایت منتشر می‌کند (چیدمان صفحه‌ها و تنظیمات سبک).
 */
function technopay_editor_publish_draft() {
	$draft = technopay_draft_get( true );
	if ( $draft['pages'] ) {
		technopay_layout_save( technopay_layout_sanitize( array( 'pages' => $draft['pages'] ) ) );
	}
	foreach ( $draft['style'] as $key => $value ) {
		$clean = technopay_style_sanitize( $key, $value );
		if ( null !== $clean ) {
			set_theme_mod( $key, $clean );
		}
	}
	technopay_draft_clear();
}

/**
 * انتشار پیش‌نویس (AJAX).
 */
function technopay_editor_ajax_publish() {
	technopay_editor_ajax_check();
	technopay_editor_publish_draft();
	wp_send_json_success( array( 'dirty' => false ) );
}
add_action( 'wp_ajax_tpb_publish', 'technopay_editor_ajax_publish' );

/**
 * دور انداختن پیش‌نویس.
 */
function technopay_editor_ajax_discard() {
	technopay_editor_ajax_check();
	technopay_draft_clear();
	wp_send_json_success( array( 'dirty' => false ) );
}
add_action( 'wp_ajax_tpb_discard', 'technopay_editor_ajax_discard' );

/**
 * CSS رنگ برند برای پیش‌نمایش زنده.
 */
function technopay_editor_ajax_brand_css() {
	technopay_editor_ajax_check();
	$color = isset( $_POST['color'] ) ? technopay_sanitize_brand_color( wp_unslash( $_POST['color'] ) ) : technopay_default_brand(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- در technopay_editor_ajax_check بررسی و در technopay_sanitize_brand_color پاک‌سازی می‌شود.
	$mode  = isset( $_POST['mode'] ) && 'auto' === $_POST['mode'] ? 'auto' : 'white'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	wp_send_json_success( array( 'css' => technopay_brand_css( $color, $mode, true ) ) );
}
add_action( 'wp_ajax_tpb_brand_css', 'technopay_editor_ajax_brand_css' );
