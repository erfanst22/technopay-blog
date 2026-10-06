<?php
/**
 * منوها: کلاس‌های سفارشی و منوی جایگزین.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیا این منو، منوی دسکتاپ هدر است؟
 *
 * @param stdClass|array $args آرگومان‌های wp_nav_menu.
 * @return bool
 */
function technopay_is_desktop_menu( $args ) {
	$args = (object) $args;
	return isset( $args->technopay_context ) && 'desktop' === $args->technopay_context;
}

/**
 * منوی اصلی دو بار (دسکتاپ و موبایل) چاپ می‌شود؛ برای جلوگیری از شناسه‌های تکراری، آیتم‌های منوی موبایل بدون id هستند.
 *
 * @param string   $id   شناسه آیتم.
 * @param WP_Post  $item آیتم منو.
 * @param stdClass $args آرگومان‌ها.
 * @return string
 */
function technopay_menu_item_id( $id, $item, $args ) {
	$args = (object) $args;
	return isset( $args->technopay_context ) && 'drawer' === $args->technopay_context ? '' : $id;
}
add_filter( 'nav_menu_item_id', 'technopay_menu_item_id', 10, 3 );

/**
 * کلاس آیتم‌های سطح اول منوی اصلی.
 *
 * @param string[] $classes کلاس‌ها.
 * @param WP_Post  $item    آیتم منو.
 * @param stdClass $args    آرگومان‌ها.
 * @param int      $depth   عمق.
 * @return string[]
 */
function technopay_menu_item_class( $classes, $item, $args, $depth = 0 ) {
	if ( technopay_is_desktop_menu( $args ) && 0 === $depth ) {
		$classes[] = 'nav__item';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'technopay_menu_item_class', 10, 4 );

/**
 * کلاس لینک‌های سطح اول منوی اصلی.
 *
 * @param array    $atts  ویژگی‌ها.
 * @param WP_Post  $item  آیتم منو.
 * @param stdClass $args  آرگومان‌ها.
 * @param int      $depth عمق.
 * @return array
 */
function technopay_menu_link_atts( $atts, $item, $args, $depth = 0 ) {
	if ( technopay_is_desktop_menu( $args ) && 0 === $depth ) {
		$atts['class'] = trim( ( isset( $atts['class'] ) ? $atts['class'] : '' ) . ' nav__link' );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'technopay_menu_link_atts', 10, 4 );

/**
 * کلاس زیرمنوها (دراپ‌داون).
 *
 * @param string[] $classes کلاس‌ها.
 * @param stdClass $args    آرگومان‌ها.
 * @return string[]
 */
function technopay_submenu_class( $classes, $args ) {
	if ( technopay_is_desktop_menu( $args ) ) {
		$classes[] = 'dropdown';
	}
	return $classes;
}
add_filter( 'nav_menu_submenu_css_class', 'technopay_submenu_class', 10, 2 );

/**
 * فلش برای آیتم‌های دارای زیرمنو و نمایش «توضیحات» آیتم در دراپ‌داون.
 *
 * @param string   $title عنوان.
 * @param WP_Post  $item  آیتم منو.
 * @param stdClass $args  آرگومان‌ها.
 * @param int      $depth عمق.
 * @return string
 */
function technopay_menu_item_title( $title, $item, $args, $depth = 0 ) {
	if ( ! technopay_is_desktop_menu( $args ) ) {
		return $title;
	}
	if ( 0 === $depth && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $title . technopay_get_icon( 'chevron-down' );
	}
	if ( $depth > 0 && ! empty( $item->description ) ) {
		return '<strong>' . $title . '</strong><small>' . esc_html( $item->description ) . '</small>';
	}
	return $title;
}
add_filter( 'nav_menu_item_title', 'technopay_menu_item_title', 10, 4 );

/**
 * منوی جایگزین وقتی هنوز منویی ساخته نشده: خانه + دسته‌های اصلی.
 *
 * @param array $args آرگومان‌های wp_nav_menu.
 */
function technopay_menu_fallback( $args ) {
	$desktop   = technopay_is_desktop_menu( $args );
	$li_class  = $desktop ? 'nav__item' : '';
	$a_class   = $desktop ? 'nav__link' : '';
	$is_front  = is_front_page();
	$items     = array();
	$items[]   = sprintf(
		'<li class="%1$s"><a class="%2$s" href="%3$s"%4$s>%5$s</a></li>',
		esc_attr( trim( $li_class . ( $is_front ? ' current-menu-item' : '' ) ) ),
		esc_attr( $a_class ),
		esc_url( home_url( '/' ) ),
		$is_front ? ' aria-current="page"' : '',
		esc_html__( 'خانه', 'technopay' )
	);

	foreach ( technopay_get_top_categories( 5 ) as $term ) {
		$current = is_category( $term->term_id );
		$items[] = sprintf(
			'<li class="%1$s"><a class="%2$s" href="%3$s"%4$s>%5$s</a></li>',
			esc_attr( trim( $li_class . ( $current ? ' current-menu-item' : '' ) ) ),
			esc_attr( $a_class ),
			esc_url( get_category_link( $term ) ),
			$current ? ' aria-current="page"' : '',
			esc_html( $term->name )
		);
	}

	printf(
		'<ul class="%1$s">%2$s</ul>',
		esc_attr( isset( $args['menu_class'] ) ? $args['menu_class'] : '' ),
		implode( '', $items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * منوی جایگزین فوتر: برگه‌های منتشرشده.
 *
 * @param array $args آرگومان‌ها.
 */
function technopay_footer_menu_fallback( $args ) {
	$pages = get_pages(
		array(
			'number'      => 6,
			'sort_column' => 'menu_order,post_title',
		)
	);
	if ( ! $pages ) {
		return;
	}
	echo '<ul class="' . esc_attr( $args['menu_class'] ) . '">';
	foreach ( $pages as $page ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
	}
	echo '</ul>';
}
