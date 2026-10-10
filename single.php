<?php
/**
 * صفحه نوشته.
 * ناحیه‌ها: card (داخل کارت مقاله)، after (زیر کارت)، sidebar و bottom (تمام‌عرض)؛ همه از
 * «نمایش ← چیدمان صفحات ← صفحه نوشته» با کشیدن و رها کردن قابل چیدن‌اند.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	// سایدبار را قبل از چاپ می‌سازیم تا اگر خالی یا خاموش بود ستونی برایش رزرو نشود.
	$technopay_position = technopay_page_option( 'single', 'sidebar_position' );
	$technopay_sb       = 'none' === $technopay_position ? array( 'html' => '', 'sticky' => false ) : technopay_sidebar_parts( 'single' );
	$technopay_sidebar  = $technopay_sb['html'];
	if ( '' === $technopay_sidebar ) {
		$technopay_position = 'none';
	}
	?>
	<div class="progress" aria-hidden="true"><span></span></div>

	<main id="main" class="site-main site-main--single">
		<div class="container">
			<?php technopay_breadcrumb( 'breadcrumb--box' ); ?>

			<div class="post-layout post-layout--article post-layout--sb-<?php echo esc_attr( $technopay_position ); ?>">
				<div class="post-main">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
						<?php technopay_render_region( 'single', 'card' ); ?>
					</article>
					<?php technopay_render_region( 'single', 'after' ); ?>
				</div>

				<?php if ( '' !== $technopay_sidebar ) : ?>
					<aside class="sidebar sidebar--single<?php echo $technopay_sb['sticky'] ? ' sidebar--has-sticky' : ''; ?>" aria-label="<?php esc_attr_e( 'سایدبار نوشته', 'technopay' ); ?>">
						<?php echo $technopay_sidebar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where it is generated. ?>
					</aside>
				<?php endif; ?>
			</div>
		</div>

		<?php technopay_render_region( 'single', 'bottom' ); ?>
	</main>
	<?php
endwhile;

get_footer();
