<?php
/**
 * قالب پیش‌فرض (برگه نوشته‌ها و حالت‌های دیگر).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

$technopay_posts_page = (int) get_option( 'page_for_posts' );
$technopay_title      = $technopay_posts_page ? get_the_title( $technopay_posts_page ) : __( 'آخرین مطالب', 'technopay' );
?>
<main id="main" class="site-main container">
	<header class="archive-hero">
		<div>
			<?php technopay_breadcrumb(); ?>
			<h1><?php echo esc_html( $technopay_title ); ?></h1>
			<p><?php bloginfo( 'description' ); ?></p>
		</div>
		<span class="archive-hero__icon bg-1" aria-hidden="true"><?php technopay_icon( 'book' ); ?></span>
	</header>

	<div class="layout section">
		<div>
			<?php if ( have_posts() ) : ?>
				<?php technopay_archive_toolbar(); ?>
				<div class="post-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card-row' );
					endwhile;
					?>
				</div>
				<?php technopay_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content-none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>
<?php
get_footer();
