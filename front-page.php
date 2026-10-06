<?php
/**
 * صفحه اصلی مجله.
 * ترتیب و نمایش بخش‌ها از «سفارشی‌سازی ← تنظیمات قالب تکنوپی ← صفحه اصلی» قابل تغییر است.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

// صفحه‌های بعدی (/page/2) و هر آدرس دارای ?sort= روی صفحه اصلی، به‌صورت لیست ساده نمایش داده می‌شوند.
if ( is_home() && ( is_paged() || technopay_has_explicit_sort() ) ) {
	get_template_part( 'index' );
	return;
}

// اگر صفحه اصلی روی «یک برگه» با محتوای دلخواه تنظیم شده و آن برگه محتوا دارد، همان برگه نمایش داده شود.
if ( 'page' === get_option( 'show_on_front' ) && get_queried_object_id() && '' !== trim( (string) get_post_field( 'post_content', get_queried_object_id() ) ) && ! apply_filters( 'technopay_force_magazine_home', false ) ) {
	get_template_part( 'page' );
	return;
}

get_header();
?>
<main id="main" class="site-main">
	<h1 class="sr-only"><?php bloginfo( 'name' ); ?></h1>
	<?php
	$technopay_sections = array( 'hero', 'trending', 'categories', 'latest', 'popular', 'cta', 'cat_block', 'steps', 'panels', 'newsletter' );
	foreach ( $technopay_sections as $technopay_section ) {
		if ( technopay_option( 'home_' . $technopay_section ) ) {
			get_template_part( 'template-parts/home/' . str_replace( '_', '-', $technopay_section ) );
		}
	}
	?>
</main>
<?php
get_footer();
