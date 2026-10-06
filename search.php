<?php
/**
 * نتایج جستجو.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main container">
	<header class="archive-hero archive-hero--search">
		<div>
			<?php technopay_breadcrumb(); ?>
			<span class="badge"><?php esc_html_e( 'جستجو', 'technopay' ); ?></span>
			<h1>
				<?php
				/* translators: %s: search query */
				printf( esc_html__( 'نتایج جستجو برای «%s»', 'technopay' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</div>
		<span class="archive-hero__icon bg-1" aria-hidden="true"><?php technopay_icon( 'search' ); ?></span>
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
