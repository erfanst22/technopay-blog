<?php
/**
 * قالب پیش‌فرض (برگه نوشته‌ها و حالت‌های دیگر).
 * بخش‌ها از «نمایش ← چیدمان صفحات» قابل چیدن‌اند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php technopay_render_region( 'archive', 'main' ); ?>
</main>
<?php
get_footer();
