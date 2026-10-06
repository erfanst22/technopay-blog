<?php
/**
 * آرشیو دسته‌ها، برچسب‌ها، نویسنده‌ها و تاریخ.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

$technopay_object = get_queried_object();
?>
<main id="main" class="site-main container">
	<header class="archive-hero">
		<div>
			<?php technopay_breadcrumb(); ?>
			<?php if ( is_category() ) : ?>
				<span class="badge"><?php esc_html_e( 'دسته‌بندی', 'technopay' ); ?></span>
			<?php elseif ( is_tag() ) : ?>
				<span class="badge"><?php esc_html_e( 'برچسب', 'technopay' ); ?></span>
			<?php elseif ( is_author() ) : ?>
				<span class="badge"><?php esc_html_e( 'نویسنده', 'technopay' ); ?></span>
			<?php endif; ?>

			<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>

			<?php if ( is_author() ) : ?>
				<?php $technopay_bio = get_the_author_meta( 'description', (int) get_query_var( 'author' ) ); ?>
				<?php if ( $technopay_bio ) : ?>
					<p><?php echo esc_html( $technopay_bio ); ?></p>
				<?php endif; ?>
			<?php elseif ( get_the_archive_description() ) : ?>
				<div class="archive-hero__desc"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
			<?php endif; ?>

			<?php
			if ( is_category() ) :
				$technopay_children = get_categories(
					array(
						'parent'     => $technopay_object->term_id,
						'hide_empty' => true,
					)
				);
				if ( $technopay_children ) :
					?>
					<div class="archive-hero__chips">
						<?php foreach ( $technopay_children as $technopay_child ) : ?>
							<a class="chip" href="<?php echo esc_url( get_category_link( $technopay_child ) ); ?>"><?php echo esc_html( $technopay_child->name ); ?></a>
						<?php endforeach; ?>
					</div>
					<?php
				endif;
			endif;
			?>
		</div>

		<?php if ( is_category() || is_tag() ) : ?>
			<?php $technopay_style = technopay_term_style( $technopay_object ); ?>
			<span class="archive-hero__icon bg-<?php echo esc_attr( $technopay_style['color'] ); ?>" aria-hidden="true"><?php technopay_icon( is_tag() ? 'tag' : $technopay_style['icon'] ); ?></span>
		<?php elseif ( is_author() ) : ?>
			<?php echo technopay_get_avatar( (int) get_query_var( 'author' ), get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ), 96, 'avatar--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<span class="archive-hero__icon bg-1" aria-hidden="true"><?php technopay_icon( 'calendar' ); ?></span>
		<?php endif; ?>
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
