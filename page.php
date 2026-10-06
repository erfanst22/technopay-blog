<?php
/**
 * برگه‌ها.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="site-main container container--narrow">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="post-header">
				<?php technopay_breadcrumb(); ?>
				<?php the_title( '<h1>', '</h1>' ); ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="post-cover"><?php the_post_thumbnail( 'technopay-hero' ); ?></div>
			<?php endif; ?>

			<div class="prose entry-content page-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="page-links">' . esc_html__( 'صفحه‌ها:', 'technopay' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
	</main>
	<?php
endwhile;

get_footer();
