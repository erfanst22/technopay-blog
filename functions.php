<?php
/**
 * TechnoPay Blog — توابع و تنظیمات قالب.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

define( 'TECHNOPAY_VERSION', '1.3.2' );
define( 'TECHNOPAY_DIR', get_template_directory() );
define( 'TECHNOPAY_URI', get_template_directory_uri() );

require TECHNOPAY_DIR . '/inc/helpers.php';
require TECHNOPAY_DIR . '/inc/template-tags.php';
require TECHNOPAY_DIR . '/inc/menus.php';
require TECHNOPAY_DIR . '/inc/customizer.php';
require TECHNOPAY_DIR . '/inc/widgets.php';
require TECHNOPAY_DIR . '/inc/term-meta.php';
require TECHNOPAY_DIR . '/inc/content-toc.php';
require TECHNOPAY_DIR . '/inc/seo.php';
require TECHNOPAY_DIR . '/inc/layout.php';
require TECHNOPAY_DIR . '/inc/layout-blocks.php';
require TECHNOPAY_DIR . '/inc/layout-admin.php';

/**
 * پشتیبانی‌های قالب، منوها و اندازه تصاویر.
 */
function technopay_setup() {
	load_theme_textdomain( 'technopay', TECHNOPAY_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'آبی تکنوپی', 'technopay' ),
				'slug'  => 'primary',
				'color' => '#2b59ff',
			),
			array(
				'name'  => __( 'بنفش', 'technopay' ),
				'slug'  => 'violet',
				'color' => '#7c3aed',
			),
			array(
				'name'  => __( 'زرد تأکیدی', 'technopay' ),
				'slug'  => 'accent',
				'color' => '#ffb21e',
			),
			array(
				'name'  => __( 'سرمه‌ای', 'technopay' ),
				'slug'  => 'ink',
				'color' => '#0f172a',
			),
			array(
				'name'  => __( 'خاکستری', 'technopay' ),
				'slug'  => 'muted',
				'color' => '#64748b',
			),
			array(
				'name'  => __( 'آبی روشن', 'technopay' ),
				'slug'  => 'primary-50',
				'color' => '#eef2ff',
			),
		)
	);

	add_image_size( 'technopay-hero', 1280, 800, true );
	add_image_size( 'technopay-card', 768, 480, true );
	add_image_size( 'technopay-tile', 640, 360, true );
	add_image_size( 'technopay-thumb', 240, 240, true );

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی', 'technopay' ),
			'topbar'  => __( 'منوی نوار بالای سایت', 'technopay' ),
			'footer'  => __( 'منوی فوتر (لینک‌های مفید)', 'technopay' ),
		)
	);
}
add_action( 'after_setup_theme', 'technopay_setup' );

/**
 * عرض محتوا برای embedها.
 */
function technopay_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'technopay_content_width', 820 );
}
add_action( 'after_setup_theme', 'technopay_content_width', 0 );

/**
 * فایل‌های CSS و JS.
 */
function technopay_assets() {
	wp_enqueue_style( 'technopay-main', TECHNOPAY_URI . '/assets/css/main.css', array(), TECHNOPAY_VERSION );
	wp_add_inline_style( 'technopay-main', ':root{--logo-h:' . technopay_logo_height() . 'px}' );
	wp_enqueue_script(
		'technopay-main',
		TECHNOPAY_URI . '/assets/js/main.js',
		array(),
		TECHNOPAY_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'technopay_assets' );

/**
 * اعمال حالت تاریک/روشن ذخیره‌شده پیش از رندر صفحه (جلوگیری از پرش رنگ).
 */
function technopay_theme_init_script() {
	wp_print_inline_script_tag(
		'try{var t=localStorage.getItem("tp-theme");if(t==="dark"||t==="light")document.documentElement.setAttribute("data-theme",t)}catch(e){}'
	);
}
add_action( 'wp_head', 'technopay_theme_init_script', 0 );

/**
 * پیش‌بارگذاری فونت اصلی.
 */
function technopay_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( TECHNOPAY_URI . '/assets/fonts/vazirmatn/Vazirmatn-FD-Regular.woff2' )
	);
}
add_action( 'wp_head', 'technopay_preload_font', 1 );

/**
 * رنگ نوار مرورگر موبایل.
 */
function technopay_theme_color_meta() {
	echo '<meta name="theme-color" content="#2b59ff">' . "\n";
}
add_action( 'wp_head', 'technopay_theme_color_meta', 2 );

/**
 * طول و انتهای خلاصه نوشته.
 */
add_filter(
	'excerpt_length',
	function () {
		return 28;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '…';
	}
);

/**
 * مرتب‌سازی آرشیوها با پارامتر ?sort=popular|comments|oldest
 *
 * @param WP_Query $query کوئری اصلی.
 */
function technopay_archive_sorting( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! ( $query->is_archive() || $query->is_home() || $query->is_search() ) ) {
		return;
	}

	$sort = technopay_current_sort();

	// با مرتب‌سازی غیر پیش‌فرض، نوشته‌های سنجاق‌شده اول لیست نمی‌آیند.
	if ( 'latest' !== $sort ) {
		$query->set( 'ignore_sticky_posts', true );
	}

	if ( 'popular' === $sort ) {
		$query->set(
			'meta_query',
			array(
				'relation'    => 'OR',
				'views_value' => array(
					'key'     => technopay_views_meta_key(),
					'type'    => 'NUMERIC',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => technopay_views_meta_key(),
					'compare' => 'NOT EXISTS',
				),
			)
		);
		$query->set(
			'orderby',
			array(
				'views_value' => 'DESC',
				'date'        => 'DESC',
			)
		);
	} elseif ( 'comments' === $sort ) {
		$query->set( 'orderby', 'comment_count' );
		$query->set( 'order', 'DESC' );
	} elseif ( 'oldest' === $sort ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'technopay_archive_sorting' );

/**
 * حذف پیشوند «دسته:»، «برچسب:» و ... از عنوان آرشیوها.
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * دسته الگوهای بلوک قالب.
 */
function technopay_pattern_category() {
	register_block_pattern_category( 'technopay', array( 'label' => __( 'تکنوپی', 'technopay' ) ) );
}
add_action( 'init', 'technopay_pattern_category' );

/**
 * هماهنگی با افزونه «مطالب مرتبط هوشمند تکنوپی» (plugins/technopay-related-posts):
 * برای نوشته‌های بدون تصویر شاخص، کاور پیش‌فرض قالب در باکس‌ها و پنل ادمین نمایش داده می‌شود.
 *
 * @param string  $html   HTML تصویر.
 * @param WP_Post $target نوشته مقصد.
 * @return string
 */
function technopay_related_box_thumbnail( $html, $target ) {
	return '' !== $html ? $html : technopay_get_thumbnail( 'technopay-thumb', array( 'alt' => '' ), $target->ID );
}
add_filter( 'tprp_thumbnail_html', 'technopay_related_box_thumbnail', 10, 2 );

/**
 * آدرس تصویر بندانگشتی در پنل ادمین افزونه.
 *
 * @param string  $url  آدرس.
 * @param WP_Post $post نوشته.
 * @return string
 */
function technopay_related_admin_thumbnail( $url, $post ) {
	return '' !== $url ? $url : technopay_fallback_cover_url( $post->ID );
}
add_filter( 'tprp_admin_thumbnail_url', 'technopay_related_admin_thumbnail', 10, 2 );

/**
 * قالب فارسی است؛ حتی اگر زبان سایت فارسی نباشد، جهت صفحه راست‌چین بماند.
 *
 * @param string $output ویژگی‌های تگ html.
 * @return string
 */
function technopay_force_rtl( $output ) {
	if ( ! is_admin() && false === strpos( $output, 'dir=' ) ) {
		$output = 'dir="rtl" ' . $output;
	}
	return $output;
}
add_filter( 'language_attributes', 'technopay_force_rtl' );

/**
 * کلاس‌های اضافه روی body.
 *
 * @param string[] $classes کلاس‌ها.
 * @return string[]
 */
function technopay_body_classes( $classes ) {
	if ( is_singular( 'post' ) ) {
		$classes[] = 'is-article';
	}
	return $classes;
}
add_filter( 'body_class', 'technopay_body_classes' );
