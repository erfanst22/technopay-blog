<?php
/**
 * صفحه نوشته.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="progress" aria-hidden="true"><span></span></div>

	<main id="main" class="site-main container">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
			<header class="post-header">
				<?php technopay_breadcrumb(); ?>
				<div class="post-header__badges">
					<?php technopay_category_badge( 'badge' ); ?>
					<?php if ( is_sticky() ) : ?>
						<span class="badge badge--accent"><?php technopay_icon( 'zap', 'icon--sm' ); ?><?php esc_html_e( 'ویژه', 'technopay' ); ?></span>
					<?php endif; ?>
				</div>
				<?php the_title( '<h1>', '</h1>' ); ?>
				<?php if ( has_excerpt() ) : ?>
					<p class="post-header__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="post-header__meta">
					<?php technopay_author_chip( true ); ?>
					<?php technopay_post_meta( array( 'date', 'reading', 'views', 'comments' ) ); ?>
				</div>
			</header>

			<figure class="post-cover-wrap">
				<div class="post-cover">
					<?php
					technopay_thumbnail(
						'technopay-hero',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'alt'           => has_post_thumbnail() ? trim( wp_strip_all_tags( get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ) ) : '',
						)
					);
					?>
				</div>
				<?php if ( has_post_thumbnail() && get_the_post_thumbnail_caption() ) : ?>
					<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
				<?php endif; ?>
			</figure>

			<div class="post-layout">
				<div class="post-main">
					<div class="prose entry-content" data-article>
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="page-links">' . esc_html__( 'صفحه‌ها:', 'technopay' ),
								'after'  => '</nav>',
							)
						);
						?>

						<footer class="post-foot">
							<?php
							$technopay_tags = get_the_tags();
							if ( $technopay_tags ) :
								?>
								<div class="tag-cloud">
									<?php foreach ( $technopay_tags as $technopay_tag ) : ?>
										<a class="tag" href="<?php echo esc_url( get_tag_link( $technopay_tag ) ); ?>" rel="tag"><?php echo esc_html( $technopay_tag->name ); ?></a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php technopay_share_buttons(); ?>
						</footer>
					</div>

					<?php if ( technopay_option( 'show_author_box' ) ) : ?>
						<?php $technopay_author_id = (int) get_the_author_meta( 'ID' ); ?>
						<section class="author-box" aria-label="<?php esc_attr_e( 'درباره نویسنده', 'technopay' ); ?>">
							<?php echo technopay_get_avatar( $technopay_author_id, get_the_author(), 72, 'avatar--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<div>
								<h2><?php the_author(); ?> <small><?php esc_html_e( 'نویسنده', 'technopay' ); ?></small></h2>
								<p>
									<?php
									$technopay_bio = get_the_author_meta( 'description' );
									echo esc_html( $technopay_bio ? $technopay_bio : __( 'نویسنده مجله تکنوپی؛ همراه شما برای خرید هوشمند و مدیریت بهتر مالی.', 'technopay' ) );
									?>
								</p>
								<a class="more-link" href="<?php echo esc_url( get_author_posts_url( $technopay_author_id ) ); ?>">
									<?php
									/* translators: %s: number of posts */
									echo esc_html( sprintf( __( 'مشاهده همه مطالب (%s)', 'technopay' ), technopay_number( count_user_posts( $technopay_author_id, 'post', true ) ) ) );
									?>
									<?php technopay_icon( 'chevron-left' ); ?>
								</a>
							</div>
						</section>
					<?php endif; ?>

					<?php
					$technopay_prev = get_previous_post();
					$technopay_next = get_next_post();
					if ( $technopay_prev || $technopay_next ) :
						?>
						<nav class="post-nav" aria-label="<?php esc_attr_e( 'نوشته‌های قبلی و بعدی', 'technopay' ); ?>">
							<?php if ( $technopay_prev ) : ?>
								<a class="post-nav__prev" href="<?php echo esc_url( get_permalink( $technopay_prev ) ); ?>" rel="prev">
									<small><?php technopay_icon( 'chevron-right', 'icon--sm' ); ?><?php esc_html_e( 'مطلب قبلی', 'technopay' ); ?></small>
									<strong><?php echo esc_html( get_the_title( $technopay_prev ) ); ?></strong>
								</a>
							<?php endif; ?>
							<?php if ( $technopay_next ) : ?>
								<a class="post-nav__next" href="<?php echo esc_url( get_permalink( $technopay_next ) ); ?>" rel="next">
									<small><?php esc_html_e( 'مطلب بعدی', 'technopay' ); ?><?php technopay_icon( 'chevron-left', 'icon--sm' ); ?></small>
									<strong><?php echo esc_html( get_the_title( $technopay_next ) ); ?></strong>
								</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>

				<aside class="sidebar sidebar--sticky" aria-label="<?php esc_attr_e( 'سایدبار نوشته', 'technopay' ); ?>">
					<details class="widget toc" open hidden>
						<summary class="widget__title"><?php technopay_icon( 'list' ); ?><?php esc_html_e( 'فهرست مطالب', 'technopay' ); ?><?php technopay_icon( 'chevron-down', 'toc__chevron' ); ?></summary>
						<ol data-toc></ol>
					</details>
					<?php
					if ( is_active_sidebar( 'technopay-single' ) ) {
						dynamic_sidebar( 'technopay-single' );
					} else {
						technopay_render_promo();
					}
					?>
				</aside>
			</div>
		</article>

		<?php if ( technopay_option( 'show_related' ) ) : ?>
			<?php
			$technopay_related = get_posts(
				array(
					'posts_per_page'      => 3,
					'post__not_in'        => array( get_the_ID() ),
					'category__in'        => wp_get_post_categories( get_the_ID() ),
					'ignore_sticky_posts' => true,
				)
			);
			if ( $technopay_related ) :
				?>
				<section class="related">
					<?php technopay_section_head( __( 'مطالب مرتبط', 'technopay' ) ); ?>
					<div class="posts-grid posts-grid--3">
						<?php
						global $post;
						foreach ( $technopay_related as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $post );
							get_template_part( 'template-parts/card' );
						endforeach;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
