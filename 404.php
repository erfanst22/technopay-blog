<?php
/**
 * صفحه ۴۰۴.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main container">
	<section class="error-404">
		<p class="error-404__code" aria-hidden="true">۴۰۴</p>
		<h1><?php esc_html_e( 'صفحه‌ای که دنبالش بودید پیدا نشد!', 'technopay' ); ?></h1>
		<p><?php esc_html_e( 'ممکن است آدرس را اشتباه وارد کرده باشید یا این صفحه حذف شده باشد. از جستجو کمک بگیرید یا به صفحه اصلی برگردید.', 'technopay' ); ?></p>
		<?php get_search_form(); ?>
		<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php technopay_icon( 'home' ); ?><?php esc_html_e( 'بازگشت به صفحه اصلی', 'technopay' ); ?></a>
	</section>

	<?php
	$technopay_latest = get_posts( array( 'posts_per_page' => 3 ) );
	if ( $technopay_latest ) :
		?>
		<section class="section">
			<?php technopay_section_head( __( 'شاید این مطالب برایتان جالب باشد', 'technopay' ) ); ?>
			<div class="posts-grid posts-grid--3">
				<?php
				global $post;
				foreach ( $technopay_latest as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					get_template_part( 'template-parts/card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
