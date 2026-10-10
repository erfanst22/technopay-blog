<?php
/**
 * موتور چیدمان صفحات: رجیستری صفحه‌ها/ناحیه‌ها/بلوک‌ها، مقادیر پیش‌فرض، پاک‌سازی و رندر.
 *
 * هر صفحه (صفحه اصلی، نوشته، آرشیو، جستجو، ۴۰۴) از چند «ناحیه» ساخته شده و هر ناحیه فهرستی مرتب از
 * «بلوک»هاست. مدیر سایت از «نمایش ← چیدمان صفحات» با کشیدن و رها کردن بلوک‌ها را مرتب، فعال/غیرفعال و
 * تنظیم می‌کند. چیدمان در گزینه technopay_layout ذخیره می‌شود؛ فقط بلوک‌های فعال و به ترتیب نمایش.
 *
 * توسعه‌دهنده‌ها می‌توانند با فیلتر technopay_layout_registry صفحه/ناحیه/بلوک جدید اضافه کنند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

const TECHNOPAY_LAYOUT_OPTION = 'technopay_layout';

/**
 * شناسه بلوک‌های «بخش تمام‌عرض» که در ناحیه اصلی هر صفحه قابل استفاده‌اند.
 *
 * @return string[]
 */
function technopay_layout_sections() {
	return array( 'trending', 'categories', 'latest', 'popular', 'cta', 'cat_block', 'steps', 'panels', 'newsletter' );
}

/**
 * شناسه بلوک‌های سایدبار.
 *
 * @return string[]
 */
function technopay_layout_widgets() {
	return array( 'w_popular', 'w_popular_mini', 'w_latest_mini', 'w_related_mini', 'w_calculator', 'w_categories', 'w_promo', 'w_tags', 'w_search' );
}

/**
 * رجیستری صفحه‌ها و بلوک‌ها.
 *
 * @return array{pages:array,blocks:array}
 */
function technopay_layout_registry() {
	static $registry = null;
	if ( null !== $registry ) {
		return $registry;
	}

	$text  = function ( $label, $default ) {
		return array(
			'type'    => 'text',
			'label'   => $label,
			'default' => $default,
		);
	};
	$num   = function ( $label, $default, $min, $max ) {
		return array(
			'type'    => 'number',
			'label'   => $label,
			'default' => $default,
			'min'     => $min,
			'max'     => $max,
		);
	};
	$tog   = function ( $label, $default = true ) {
		return array(
			'type'    => 'toggle',
			'label'   => $label,
			'default' => $default,
		);
	};
	$cat   = function ( $label ) {
		return array(
			'type'    => 'category',
			'label'   => $label,
			'default' => 0,
		);
	};
	$block = function ( $label, $desc, $callback, $settings = array() ) {
		return array(
			'label'    => $label,
			'desc'     => $desc,
			'callback' => $callback,
			'settings' => $settings,
		);
	};

	$blocks = array(
		// بخش‌های تمام‌عرض.
		'hero'              => $block( 'مطالب ویژه', 'یک کارت بزرگ و چهار کارت کوچک؛ نوشته‌های سنجاق‌شده در اولویت‌اند.', 'technopay_block_hero' ),
		'trending'          => $block( 'نوار داغ‌ترین‌ها', 'برچسب‌های پرکاربرد در یک نوار افقی.', 'technopay_block_trending', array( 'count' => $num( 'تعداد برچسب', 10, 4, 20 ) ) ),
		'categories'        => $block( 'کاشی دسته‌بندی‌ها', 'آیکون و تعداد مقالات هر دسته.', 'technopay_block_categories', array( 'count' => $num( 'تعداد دسته', 6, 2, 12 ) ) ),
		'latest'            => $block(
			'ردیف آخرین مطالب',
			'جعبه خاکستری با کارت‌های فشرده.',
			'technopay_block_latest',
			array(
				'title'     => $text( 'عنوان', 'آخرین مطالب' ),
				'count'     => $num( 'تعداد کارت', 6, 3, 12 ),
				'more_link' => $tog( 'لینک «مشاهده همه»' ),
			)
		),
		'popular'           => $block(
			'ردیف پربازدیدترین مطالب',
			'جعبه خاکستری با کارت‌های فشرده (بر اساس بازدید).',
			'technopay_block_popular',
			array(
				'title'     => $text( 'عنوان', 'پربازدیدترین مطالب' ),
				'count'     => $num( 'تعداد کارت', 6, 3, 12 ),
				'more_link' => $tog( 'لینک «مشاهده همه»' ),
			)
		),
		'cta'               => $block( 'بنر دریافت اعتبار', 'متن و دکمه‌ها از «سفارشی‌سازی ← بنر تبلیغاتی» تنظیم می‌شود.', 'technopay_block_cta' ),
		'cat_block'         => $block( 'بلوک یک دسته', 'یک کارت بزرگ و سه کارت کوچک از یک دسته.', 'technopay_block_cat_block', array( 'category' => $cat( 'دسته' ) ) ),
		'steps'             => $block( 'مراحل دریافت اعتبار', 'چهار قدم؛ متن‌ها از «سفارشی‌سازی ← مراحل دریافت اعتبار».', 'technopay_block_steps' ),
		'panels'            => $block(
			'دو ستون دسته‌ها',
			'دو لیست کنار هم از دو دسته.',
			'technopay_block_panels',
			array(
				'category_1' => $cat( 'دسته ستون اول' ),
				'category_2' => $cat( 'دسته ستون دوم' ),
			)
		),
		'newsletter'        => $block( 'خبرنامه', 'فقط وقتی فرم/شورت‌کد تنظیم شده باشد به بازدیدکننده نمایش داده می‌شود.', 'technopay_block_newsletter' ),

		// آرشیو و جستجو.
		'archive_hero'      => $block(
			'سربرگ آرشیو',
			'عنوان، توضیح، زیردسته‌ها و آیکون دسته/برچسب/نویسنده.',
			'technopay_block_archive_hero',
			array(
				'show_breadcrumb'   => $tog( 'نمایش مسیر راهنما' ),
				'show_description'  => $tog( 'نمایش توضیحات' ),
				'show_children'     => $tog( 'نمایش زیردسته‌ها' ),
				'show_icon'         => $tog( 'نمایش آیکون' ),
			)
		),
		'search_hero'       => $block(
			'سربرگ جستجو',
			'عنوان «نتایج جستجو» و فرم جستجو.',
			'technopay_block_search_hero',
			array(
				'show_breadcrumb' => $tog( 'نمایش مسیر راهنما' ),
				'show_form'       => $tog( 'نمایش فرم جستجو' ),
				'show_icon'       => $tog( 'نمایش آیکون' ),
			)
		),
		'results'           => $block(
			'لیست نوشته‌ها',
			'نوارابزار مرتب‌سازی، لیست یا شبکه، صفحه‌بندی و سایدبار.',
			'technopay_block_results',
			array(
				'view'             => array(
					'type'    => 'select',
					'label'   => 'نمایش پیش‌فرض',
					'default' => 'list',
					'options' => array(
						'list' => 'لیستی',
						'grid' => 'شبکه‌ای (دو ستون)',
					),
				),
				'show_toolbar'     => $tog( 'نمایش نوارابزار (تعداد و مرتب‌سازی)' ),
				'show_sort'        => $tog( 'دکمه‌های مرتب‌سازی' ),
				'show_view_switch' => $tog( 'دکمه تغییر لیست/شبکه' ),
				'show_suggestions' => $tog( 'پیشنهاد مطالب وقتی نتیجه‌ای نیست' ),
			)
		),

		// صفحه نوشته.
		's_header'          => $block(
			'عنوان و اطلاعات نوشته',
			'عنوان اصلی (H1) و ردیف تاریخ، زمان مطالعه، بازدید و نویسنده.',
			'technopay_block_s_header',
			array(
				'show_updated' => $tog( 'تاریخ به‌روزرسانی' ),
				'show_reading' => $tog( 'زمان مطالعه' ),
				'show_views'   => $tog( 'تعداد بازدید' ),
				'show_author'  => $tog( 'نام نویسنده' ),
			)
		),
		's_cover'           => $block( 'تصویر شاخص', 'تصویر بزرگ بالای متن (یا کاور پیش‌فرض قالب).', 'technopay_block_s_cover' ),
		's_content'         => $block( 'متن مقاله', 'محتوای نوشته؛ فهرست مطالب خودکار هم داخل آن است.', 'technopay_block_s_content' ),
		's_footer'          => $block(
			'برچسب‌ها و اشتراک‌گذاری',
			'برچسب‌های نوشته و دکمه‌های اشتراک‌گذاری.',
			'technopay_block_s_footer',
			array(
				'show_tags'  => $tog( 'برچسب‌ها' ),
				'show_share' => $tog( 'دکمه‌های اشتراک‌گذاری' ),
			)
		),
		's_author'          => $block( 'باکس نویسنده', 'عکس، نام و توضیح کوتاه نویسنده.', 'technopay_block_s_author' ),
		's_nav'             => $block( 'نوشته قبلی و بعدی', 'دو کارت لینک در پایین مقاله.', 'technopay_block_s_nav' ),
		's_comments'        => $block( 'دیدگاه‌ها', 'لیست دیدگاه‌ها و فرم ارسال (اگر برای نوشته باز باشد).', 'technopay_block_s_comments' ),
		's_related_grid'    => $block(
			'مطالب مرتبط (کارت‌ها)',
			'کارت‌های بزرگ از نوشته‌های هم‌دسته/هم‌برچسب.',
			'technopay_block_s_related_grid',
			array(
				'title' => $text( 'عنوان', 'مطالب مرتبط' ),
				'count' => $num( 'تعداد کارت', 3, 2, 6 ),
			)
		),

		// صفحه ۴۰۴.
		'e_message'         => $block( 'پیام ۴۰۴', 'عدد ۴۰۴، توضیح و دکمه بازگشت.', 'technopay_block_e_message', array( 'show_search' => $tog( 'نمایش فرم جستجو' ) ) ),
		'e_suggested'       => $block(
			'مطالب پیشنهادی',
			'چند کارت از تازه‌ترین نوشته‌ها.',
			'technopay_block_e_suggested',
			array(
				'title' => $text( 'عنوان', 'شاید این مطالب برایتان جالب باشد' ),
				'count' => $num( 'تعداد کارت', 3, 3, 9 ),
			)
		),

		// ابزارک‌های سایدبار.
		'w_popular'         => $block(
			'لیست شماره‌دار پربازدیدترین‌ها',
			'پنج مطلب پربازدید با شماره.',
			'technopay_block_w_popular',
			array(
				'title' => $text( 'عنوان', 'پربازدیدترین‌ها' ),
				'count' => $num( 'تعداد', 5, 3, 10 ),
			)
		),
		'w_popular_mini'    => $block(
			'پربازدیدترین مقالات (با تصویر)',
			'لیست کوچک با تصویر بندانگشتی و تاریخ.',
			'technopay_block_w_popular_mini',
			array(
				'title' => $text( 'عنوان', 'پربازدیدترین مقالات' ),
				'count' => $num( 'تعداد', 5, 3, 10 ),
			)
		),
		'w_latest_mini'     => $block(
			'جدیدترین مقالات (با تصویر)',
			'لیست کوچک با تصویر بندانگشتی و تاریخ.',
			'technopay_block_w_latest_mini',
			array(
				'title' => $text( 'عنوان', 'جدیدترین مقالات' ),
				'count' => $num( 'تعداد', 5, 3, 10 ),
			)
		),
		'w_related_mini'    => $block(
			'مقالات مرتبط (با تصویر)',
			'فقط در صفحه نوشته نمایش داده می‌شود.',
			'technopay_block_w_related_mini',
			array(
				'title' => $text( 'عنوان', 'مقالات مرتبط' ),
				'count' => $num( 'تعداد', 5, 3, 10 ),
			)
		),
		'w_calculator'      => $block( 'محاسبه‌گر اقساط', 'نرخ و بازه مبلغ از «سفارشی‌سازی ← محاسبه‌گر اقساط».', 'technopay_block_w_calculator', array( 'title' => $text( 'عنوان', 'محاسبه‌گر اقساط' ) ) ),
		'w_categories'      => $block(
			'دسته‌بندی‌ها',
			'لیست دسته‌ها با تعداد مطالب.',
			'technopay_block_w_categories',
			array(
				'title' => $text( 'عنوان', 'دسته‌بندی‌ها' ),
				'count' => $num( 'تعداد', 8, 3, 20 ),
			)
		),
		'w_promo'           => $block( 'بنر دریافت اعتبار (سایدبار)', 'جعبه رنگی با دکمه؛ متن از «سفارشی‌سازی ← بنر تبلیغاتی».', 'technopay_block_w_promo' ),
		'w_tags'            => $block(
			'برچسب‌های پرکاربرد',
			'ابر برچسب.',
			'technopay_block_w_tags',
			array(
				'title' => $text( 'عنوان', 'برچسب‌های پرکاربرد' ),
				'count' => $num( 'تعداد', 14, 5, 40 ),
			)
		),
		'w_search'          => $block( 'جستجو', 'فرم جستجو در سایدبار.', 'technopay_block_w_search', array( 'title' => $text( 'عنوان', 'جستجو در مجله' ) ) ),
		'w_widgets_main'    => $block( 'ابزارک‌های وردپرس (آرشیو و جستجو)', 'هر آنچه در «نمایش ← ابزارک‌ها ← سایدبار اصلی» گذاشته‌اید.', 'technopay_block_w_widgets_main' ),
		'w_widgets_single'  => $block( 'ابزارک‌های وردپرس (نوشته)', 'هر آنچه در «نمایش ← ابزارک‌ها ← سایدبار نوشته» گذاشته‌اید.', 'technopay_block_w_widgets_single' ),
	);

	$sections     = technopay_layout_sections();
	$widgets      = technopay_layout_widgets();
	$sidebar_opts = array(
		'sidebar_position' => array(
			'type'    => 'select',
			'label'   => 'جای سایدبار',
			'default' => 'left',
			'options' => array(
				'left'  => 'سمت چپ',
				'right' => 'سمت راست',
				'none'  => 'بدون سایدبار',
			),
		),
	);

	// ناحیهٔ چسبان: زیر ابزارک‌های سایدبار می‌آید و با اسکرول صفحه زیر هدر می‌ماند (فقط دسکتاپ).
	$sidebar_sticky = array(
		'label'  => 'سایدبار — چسبان',
		'desc'   => 'این ابزارک‌ها زیر ابزارک‌های بالا قرار می‌گیرند و هنگام اسکرول کنار صفحه می‌مانند (فقط دسکتاپ). برای چسباندن کل سایدبار، همه را اینجا بگذارید.',
		'blocks' => array_merge( $widgets, array( 'w_widgets_single', 'w_widgets_main' ) ),
	);

	$pages = array(
		'home'    => array(
			'label'   => 'صفحه اصلی',
			'regions' => array(
				'main' => array(
					'label'  => 'بخش‌های صفحه اصلی',
					'desc'   => 'بخش‌ها از بالا به پایین به همین ترتیب نمایش داده می‌شوند.',
					'blocks' => array_merge( array( 'hero' ), $sections ),
				),
			),
			'options' => array(),
		),
		'single'  => array(
			'label'   => 'صفحه نوشته',
			'regions' => array(
				'card'    => array(
					'label'    => 'داخل کارت مقاله',
					'desc'     => 'عنوان و متن لازم‌اند؛ بقیه را می‌توانید جابه‌جا یا غیرفعال کنید.',
					'blocks'   => array( 's_header', 's_cover', 's_content', 's_footer' ),
					'required' => array( 's_header', 's_content' ),
				),
				'after'   => array(
					'label'  => 'زیر کارت مقاله',
					'desc'   => 'در همان ستون مقاله و زیر آن.',
					'blocks' => array( 's_author', 's_nav', 's_comments' ),
				),
				'sidebar' => array(
					'label'  => 'سایدبار',
					'desc'   => 'ابزارک‌های کنار مقاله؛ محاسبه‌گر اقساط، دسته‌ها، بنر و ... را از ستون «غیرفعال» به ستون «فعال» بکشید.',
					'blocks' => array_merge( $widgets, array( 'w_widgets_single', 'w_widgets_main' ) ),
				),
				'sidebar_sticky' => $sidebar_sticky,
				'bottom'  => array(
					'label'  => 'پایین صفحه (تمام‌عرض)',
					'desc'   => 'بعد از مقاله و سایدبار.',
					'blocks' => array_merge( array( 's_related_grid' ), $sections ),
				),
			),
			'options' => $sidebar_opts,
		),
		'archive' => array(
			'label'   => 'آرشیو (دسته، برچسب، نویسنده، تاریخ)',
			'regions' => array(
				'main'    => array(
					'label'    => 'بخش‌های صفحه',
					'desc'     => 'سربرگ، لیست نوشته‌ها و هر بخش دلخواه دیگر.',
					'blocks'   => array_merge( array( 'archive_hero', 'results' ), $sections ),
					'required' => array( 'results' ),
				),
				'sidebar' => array(
					'label'  => 'سایدبار',
					'desc'   => 'ابزارک‌های کنار لیست نوشته‌ها؛ محاسبه‌گر اقساط، دسته‌ها، بنر و ... را از ستون «غیرفعال» به ستون «فعال» بکشید.',
					'blocks' => array_merge( $widgets, array( 'w_widgets_main', 'w_widgets_single' ) ),
				),
				'sidebar_sticky' => $sidebar_sticky,
			),
			'options' => $sidebar_opts,
		),
		'search'  => array(
			'label'   => 'جستجو',
			'regions' => array(
				'main'    => array(
					'label'    => 'بخش‌های صفحه',
					'desc'     => 'سربرگ جستجو، نتایج و هر بخش دلخواه دیگر.',
					'blocks'   => array_merge( array( 'search_hero', 'results' ), $sections ),
					'required' => array( 'results' ),
				),
				'sidebar' => array(
					'label'  => 'سایدبار',
					'desc'   => 'ابزارک‌های کنار نتایج؛ محاسبه‌گر اقساط، دسته‌ها، بنر و ... را از ستون «غیرفعال» به ستون «فعال» بکشید.',
					'blocks' => array_merge( $widgets, array( 'w_widgets_main', 'w_widgets_single' ) ),
				),
				'sidebar_sticky' => $sidebar_sticky,
			),
			'options' => $sidebar_opts,
		),
		'404'     => array(
			'label'   => 'صفحه ۴۰۴',
			'regions' => array(
				'main' => array(
					'label'  => 'بخش‌های صفحه',
					'desc'   => 'پیام خطا، مطالب پیشنهادی و هر بخش دلخواه دیگر.',
					'blocks' => array_merge( array( 'e_message', 'e_suggested' ), $sections ),
				),
			),
			'options' => array(),
		),
	);

	/**
	 * رجیستری چیدمان؛ برای افزودن صفحه، ناحیه یا بلوک جدید.
	 *
	 * @param array $registry آرایه pages و blocks.
	 */
	$registry = apply_filters(
		'technopay_layout_registry',
		array(
			'pages'  => $pages,
			'blocks' => $blocks,
		)
	);
	return $registry;
}

/**
 * مقادیر پیش‌فرض تنظیمات یک بلوک.
 *
 * @param string $block_id شناسه بلوک.
 * @return array
 */
function technopay_block_defaults( $block_id ) {
	$registry = technopay_layout_registry();
	$out      = array();
	if ( isset( $registry['blocks'][ $block_id ]['settings'] ) ) {
		foreach ( $registry['blocks'][ $block_id ]['settings'] as $key => $field ) {
			$out[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}
	}
	return $out;
}

/**
 * یک مقدار تنظیم را بر اساس نوع فیلد پاک‌سازی می‌کند.
 *
 * @param array $field تعریف فیلد.
 * @param mixed $value مقدار ورودی.
 * @return mixed
 */
function technopay_layout_sanitize_value( $field, $value ) {
	$default = isset( $field['default'] ) ? $field['default'] : '';
	switch ( isset( $field['type'] ) ? $field['type'] : 'text' ) {
		case 'toggle':
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		case 'number':
			$min = isset( $field['min'] ) ? (int) $field['min'] : 0;
			$max = isset( $field['max'] ) ? (int) $field['max'] : PHP_INT_MAX;
			return min( $max, max( $min, is_numeric( $value ) ? (int) $value : (int) $default ) );
		case 'select':
			$options = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
			return in_array( (string) $value, array_map( 'strval', $options ), true ) ? (string) $value : $default;
		case 'category':
			$id = is_numeric( $value ) ? (int) $value : 0; // منفی و نامعتبر = خودکار (absint عدد منفی را مثبت می‌کرد).
			return ( $id > 0 && term_exists( $id, 'category' ) ) ? $id : 0;
		default:
			return mb_substr( sanitize_text_field( is_scalar( $value ) ? (string) $value : '' ), 0, 80, 'UTF-8' );
	}
}

/**
 * پاک‌سازی و نرمال‌سازی داده یک صفحه (گزینه‌ها و ناحیه‌ها).
 *
 * @param string $page_id شناسه صفحه.
 * @param mixed  $data    داده ورودی: options و regions.
 * @param array  $default داده پیش‌فرض (برای بلوک‌های الزامی و گزینه‌های خالی).
 * @return array
 */
function technopay_layout_normalize_page( $page_id, $data, $default ) {
	$registry = technopay_layout_registry();
	$page     = $registry['pages'][ $page_id ];
	$data     = is_array( $data ) ? $data : array();
	$out      = array(
		'options' => array(),
		'regions' => array(),
	);

	foreach ( $page['options'] as $key => $field ) {
		$raw                  = isset( $data['options'][ $key ] ) ? $data['options'][ $key ] : ( isset( $default['options'][ $key ] ) ? $default['options'][ $key ] : $field['default'] );
		$out['options'][ $key ] = technopay_layout_sanitize_value( $field, $raw );
	}

	foreach ( $page['regions'] as $region_id => $region ) {
		$items = isset( $data['regions'][ $region_id ] ) && is_array( $data['regions'][ $region_id ] ) ? $data['regions'][ $region_id ] : array();
		$list  = array();
		$seen  = array();
		foreach ( $items as $item ) {
			$id = is_array( $item ) && isset( $item['id'] ) ? (string) $item['id'] : '';
			if ( '' === $id || isset( $seen[ $id ] ) || ! in_array( $id, $region['blocks'], true ) || ! isset( $registry['blocks'][ $id ] ) ) {
				continue;
			}
			$seen[ $id ] = true;
			$settings    = array();
			$incoming    = isset( $item['settings'] ) && is_array( $item['settings'] ) ? $item['settings'] : array();
			foreach ( $registry['blocks'][ $id ]['settings'] as $key => $field ) {
				$settings[ $key ] = technopay_layout_sanitize_value( $field, array_key_exists( $key, $incoming ) ? $incoming[ $key ] : $field['default'] );
			}
			$list[] = array(
				'id'       => $id,
				'settings' => $settings,
			);
		}

		// بلوک‌های الزامی حذف‌شدنی نیستند: اگر نبودند، در جای پیش‌فرضشان اضافه می‌شوند.
		$required = isset( $region['required'] ) ? $region['required'] : array();
		foreach ( $required as $req ) {
			if ( isset( $seen[ $req ] ) ) {
				continue;
			}
			$settings = technopay_block_defaults( $req );
			$index    = 0;
			if ( isset( $default['regions'][ $region_id ] ) ) {
				foreach ( $default['regions'][ $region_id ] as $i => $d ) {
					if ( $d['id'] === $req ) {
						$index = $i;
						break;
					}
				}
			}
			array_splice(
				$list,
				min( $index, count( $list ) ),
				0,
				array(
					array(
						'id'       => $req,
						'settings' => $settings,
					),
				)
			);
		}
		$out['regions'][ $region_id ] = $list;
	}
	return $out;
}

/**
 * ساخت لیست پیش‌فرض بلوک‌ها (با شناسه‌ها یا آرایه‌ای از id/settings).
 *
 * @param array $ids فهرست شناسه یا آرایه‌های [id, settings].
 * @return array
 */
function technopay_layout_list( array $ids ) {
	$out = array();
	foreach ( $ids as $item ) {
		$id    = is_array( $item ) ? $item['id'] : $item;
		$extra = is_array( $item ) && isset( $item['settings'] ) ? $item['settings'] : array();
		$out[] = array(
			'id'       => $id,
			'settings' => array_merge( technopay_block_defaults( $id ), $extra ),
		);
	}
	return $out;
}

/**
 * چیدمان پیش‌فرض همه صفحه‌ها. مقادیر قدیمیِ «سفارشی‌سازی» (روشن/خاموش‌بودن بخش‌های صفحه اصلی، دسته‌ها،
 * باکس نویسنده، مطالب مرتبط، ابزارک‌های وردپرس) برای سایت‌های موجود حفظ می‌شود.
 *
 * @return array<string,array>
 */
function technopay_layout_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}

	$home      = array();
	$home_flag = array(
		'hero'       => 'home_hero',
		'trending'   => 'home_trending',
		'categories' => 'home_categories',
		'latest'     => 'home_latest',
		'popular'    => 'home_popular',
		'cta'        => 'home_cta',
		'cat_block'  => 'home_cat_block',
		'steps'      => 'home_steps',
		'panels'     => 'home_panels',
		'newsletter' => 'home_newsletter',
	);
	foreach ( $home_flag as $id => $mod ) {
		if ( ! technopay_option( $mod ) ) {
			continue;
		}
		$extra = array();
		if ( 'cat_block' === $id ) {
			$extra = array( 'category' => absint( technopay_option( 'home_cat_block_cat' ) ) );
		} elseif ( 'panels' === $id ) {
			$extra = array(
				'category_1' => absint( technopay_option( 'home_panel_1_cat' ) ),
				'category_2' => absint( technopay_option( 'home_panel_2_cat' ) ),
			);
		}
		$home[] = array(
			'id'       => $id,
			'settings' => $extra,
		);
	}

	$archive_sidebar = is_active_sidebar( 'technopay-main' )
		? array( 'w_widgets_main' )
		: array( 'w_popular', 'w_calculator', 'w_categories', 'w_promo', 'w_tags' );

	$single_sidebar = array();
	if ( is_active_sidebar( 'technopay-single' ) ) {
		$single_sidebar = array( 'w_widgets_single' );
	} else {
		$single_sidebar = array( 'w_popular_mini', 'w_latest_mini' );
		if ( technopay_option( 'show_related' ) ) {
			$single_sidebar[] = 'w_related_mini';
		}
	}

	$after = array();
	if ( technopay_option( 'show_author_box' ) ) {
		$after[] = 's_author';
	}
	$after[] = 's_nav';
	$after[] = 's_comments';

	$sidebar_opt = array( 'sidebar_position' => 'left' );

	$defaults = array(
		'home'    => array(
			'options' => array(),
			'regions' => array( 'main' => technopay_layout_list( $home ) ),
		),
		'single'  => array(
			'options' => $sidebar_opt,
			'regions' => array(
				'card'    => technopay_layout_list( array( 's_header', 's_cover', 's_content', 's_footer' ) ),
				'after'   => technopay_layout_list( $after ),
				'sidebar' => technopay_layout_list( $single_sidebar ),
				'sidebar_sticky' => array(),
				'bottom'  => array(),
			),
		),
		'archive' => array(
			'options' => $sidebar_opt,
			'regions' => array(
				'main'    => technopay_layout_list( array( 'archive_hero', 'results' ) ),
				'sidebar' => technopay_layout_list( $archive_sidebar ),
				'sidebar_sticky' => array(),
			),
		),
		'search'  => array(
			'options' => $sidebar_opt,
			'regions' => array(
				'main'    => technopay_layout_list( array( 'search_hero', 'results' ) ),
				'sidebar' => technopay_layout_list( $archive_sidebar ),
				'sidebar_sticky' => array(),
			),
		),
		'404'     => array(
			'options' => array(),
			'regions' => array( 'main' => technopay_layout_list( array( 'e_message', 'e_suggested' ) ) ),
		),
	);

	/**
	 * چیدمان پیش‌فرض.
	 *
	 * @param array $defaults چیدمان پیش‌فرض همه صفحه‌ها.
	 */
	$defaults = apply_filters( 'technopay_layout_defaults', $defaults );
	return $defaults;
}

/**
 * چیدمان فعلی همه صفحه‌ها (ذخیره‌شده، روی پیش‌فرض‌ها).
 *
 * @param bool $refresh نادیده گرفتن کش درون‌درخواستی.
 * @return array<string,array>
 */
function technopay_layout_get( $refresh = false ) {
	static $cache = null;
	if ( null !== $cache && ! $refresh ) {
		return $cache;
	}
	$saved    = get_option( TECHNOPAY_LAYOUT_OPTION, array() );
	$saved    = is_array( $saved ) && isset( $saved['pages'] ) && is_array( $saved['pages'] ) ? $saved['pages'] : array();
	$defaults = technopay_layout_defaults();
	$registry = technopay_layout_registry();
	$cache    = array();
	foreach ( $registry['pages'] as $page_id => $page ) {
		$default = isset( $defaults[ $page_id ] ) ? $defaults[ $page_id ] : array(
			'options' => array(),
			'regions' => array(),
		);
		$cache[ $page_id ] = technopay_layout_normalize_page( $page_id, isset( $saved[ $page_id ] ) ? $saved[ $page_id ] : $default, $default );
	}
	return $cache;
}

/**
 * پاک‌سازی ورودی کامل ذخیره/ایمپورت: فقط صفحه‌هایی که در ورودی هستند بازنویسی می‌شوند.
 *
 * @param mixed $input ورودی (آرایه با کلید pages).
 * @return array
 */
function technopay_layout_sanitize( $input ) {
	$registry = technopay_layout_registry();
	$defaults = technopay_layout_defaults();
	$pages_in = is_array( $input ) && isset( $input['pages'] ) && is_array( $input['pages'] ) ? $input['pages'] : array();
	$out      = array(
		'version' => 1,
		'pages'   => array(),
	);
	foreach ( $registry['pages'] as $page_id => $page ) {
		if ( ! isset( $pages_in[ $page_id ] ) ) {
			continue;
		}
		$default                  = isset( $defaults[ $page_id ] ) ? $defaults[ $page_id ] : array();
		$out['pages'][ $page_id ] = technopay_layout_normalize_page( $page_id, $pages_in[ $page_id ], $default );
	}
	return $out;
}

/**
 * ذخیره چیدمان: صفحه‌های ذخیره‌نشده در ورودی، همان مقدار قبلی را حفظ می‌کنند.
 *
 * @param array $sanitized خروجی technopay_layout_sanitize().
 */
function technopay_layout_save( array $sanitized ) {
	$current = get_option( TECHNOPAY_LAYOUT_OPTION, array() );
	$pages   = is_array( $current ) && isset( $current['pages'] ) && is_array( $current['pages'] ) ? $current['pages'] : array();
	foreach ( $sanitized['pages'] as $page_id => $data ) {
		$pages[ $page_id ] = $data;
	}
	update_option(
		TECHNOPAY_LAYOUT_OPTION,
		array(
			'version' => 1,
			'pages'   => $pages,
		)
	);
	technopay_layout_get( true );
}

/**
 * بازگرداندن یک صفحه به حالت پیش‌فرض.
 *
 * @param string $page_id شناسه صفحه.
 */
function technopay_layout_reset_page( $page_id ) {
	$current = get_option( TECHNOPAY_LAYOUT_OPTION, array() );
	if ( is_array( $current ) && isset( $current['pages'][ $page_id ] ) ) {
		unset( $current['pages'][ $page_id ] );
		update_option( TECHNOPAY_LAYOUT_OPTION, $current );
		technopay_layout_get( true );
	}
}

/**
 * یک گزینه صفحه (مثلاً جای سایدبار).
 *
 * @param string $page_id شناسه صفحه.
 * @param string $key     کلید گزینه.
 * @return mixed
 */
function technopay_page_option( $page_id, $key ) {
	$layout = technopay_layout_get();
	if ( isset( $layout[ $page_id ]['options'][ $key ] ) ) {
		return $layout[ $page_id ]['options'][ $key ];
	}
	$registry = technopay_layout_registry();
	return isset( $registry['pages'][ $page_id ]['options'][ $key ]['default'] ) ? $registry['pages'][ $page_id ]['options'][ $key ]['default'] : '';
}

/**
 * بلوک‌های فعال یک ناحیه به ترتیب نمایش.
 *
 * @param string $page_id   شناسه صفحه.
 * @param string $region_id شناسه ناحیه.
 * @return array[] هر مورد: id، settings.
 */
function technopay_region_blocks( $page_id, $region_id ) {
	$layout = technopay_layout_get();
	return isset( $layout[ $page_id ]['regions'][ $region_id ] ) ? $layout[ $page_id ]['regions'][ $region_id ] : array();
}

/**
 * پشته تنظیمات بلوکی که در حال رندر است.
 *
 * @param array|null $push تنظیمات برای افزودن.
 * @param bool       $pop  حذف آخرین مورد.
 * @return array تنظیمات بلوک جاری.
 */
function technopay_block_stack( $push = null, $pop = false ) {
	static $stack = array();
	if ( null !== $push ) {
		$stack[] = $push;
	}
	if ( $pop ) {
		array_pop( $stack );
	}
	return $stack ? $stack[ count( $stack ) - 1 ] : array();
}

/**
 * مقدار یک تنظیم برای بلوکی که الان رندر می‌شود (یا مقدار پیش‌فرض داده‌شده).
 *
 * @param string $key     کلید.
 * @param mixed  $default مقدار جایگزین.
 * @return mixed
 */
function technopay_block_setting( $key, $default = null ) {
	$current = technopay_block_stack();
	return array_key_exists( $key, $current ) ? $current[ $key ] : $default;
}

/**
 * رندر همه بلوک‌های فعال یک ناحیه.
 *
 * @param string $page_id   شناسه صفحه.
 * @param string $region_id شناسه ناحیه.
 * @param array  $context   اطلاعات اضافه برای بلوک‌ها.
 */
function technopay_render_region( $page_id, $region_id, $context = array() ) {
	$registry = technopay_layout_registry();
	$context  = array_merge(
		array(
			'page'   => $page_id,
			'region' => $region_id,
		),
		(array) $context
	);
	foreach ( technopay_region_blocks( $page_id, $region_id ) as $item ) {
		if ( ! isset( $registry['blocks'][ $item['id'] ] ) || ! is_callable( $registry['blocks'][ $item['id'] ]['callback'] ) ) {
			continue;
		}
		$settings = array_merge( technopay_block_defaults( $item['id'] ), $item['settings'] );
		technopay_block_stack( $settings );
		call_user_func( $registry['blocks'][ $item['id'] ]['callback'], $settings, $context );
		technopay_block_stack( null, true );
	}
}

/**
 * خروجی رندر یک ناحیه به‌صورت رشته (برای تشخیص ناحیه خالی).
 *
 * @param string $page_id   شناسه صفحه.
 * @param string $region_id شناسه ناحیه.
 * @param array  $context   اطلاعات اضافه.
 * @return string
 */
function technopay_region_html( $page_id, $region_id, $context = array() ) {
	ob_start();
	technopay_render_region( $page_id, $region_id, $context );
	return trim( (string) ob_get_clean() );
}

/**
 * محتوای سایدبار یک صفحه: ناحیهٔ عادی + ناحیهٔ چسبان (در یک پوشش که با اسکرول همراه می‌آید).
 *
 * @param string $page_id شناسه صفحه.
 * @param array  $context اطلاعات اضافه.
 * @return array{html:string,sticky:bool} خروجی HTML (خالی = سایدبار نشان داده نشود) و وجود بخش چسبان.
 */
function technopay_sidebar_parts( $page_id, $context = array() ) {
	$html   = technopay_region_html( $page_id, 'sidebar', $context );
	$sticky = technopay_region_html( $page_id, 'sidebar_sticky', $context );
	if ( '' !== $sticky ) {
		$html .= '<div class="sidebar__sticky" data-sticky-sidebar>' . $sticky . '</div>';
	}
	return array(
		'html'   => $html,
		'sticky' => '' !== $sticky,
	);
}

/**
 * کلاس چیدمان دوستونه بر اساس جای سایدبار.
 *
 * @param string $prefix  layout یا post-layout.
 * @param string $position left | right | none.
 * @return string
 */
function technopay_layout_class( $prefix, $position ) {
	return $prefix . ' ' . $prefix . '--sb-' . sanitize_html_class( $position );
}
