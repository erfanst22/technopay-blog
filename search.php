<?php
/**
 * نتایج جستجو.
 * بخش‌ها از «مجله تکنوپی» قابل چیدن‌اند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php technopay_render_region( 'search', 'main' ); ?>
</main>
<?php
get_footer();
