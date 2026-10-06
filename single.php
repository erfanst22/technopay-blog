<?php
/**
 * صفحه نوشته.
 * چیدمان: مسیر راهنما (کادردار)، کارت مقاله در ستون اصلی و سایدبار باریک کنار آن.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$technopay_updated = technopay_modified_date( null, 'numeric' );

	// سایدبار را قبل از چاپ ساخته می‌شود تا اگر خالی بود ستونی برایش رزرو نشود.
	ob_start();
	if ( is_active_sidebar( 'technopay-single' ) ) {
		dynamic_sidebar( 'technopay-single' );
	} else {
		technopay_default_single_sidebar();
	}
	$technopay_sidebar_html = trim( (string) ob_get_clean() );
	?>
	<div class="progress" aria-hidden="true"><span></span></div>

	<main id="main" class="site-main site-main--single container">
		<?php technopay_breadcrumb( 'breadcrumb--box' ); ?>

		<div class="post-layout post-layout--article<?php echo '' === $technopay_sidebar_html ? ' post-layout--solo' : ''; ?>">
			<div class="post-main">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
					<header class="post-card__head">
						<?php the_title( '<h1 class="post-card__title">', '</h1>' ); ?>
						<div class="post-card__meta">
							<span class="meta-chip">
								<?php technopay_icon( 'calendar' ); ?>
								<?php esc_html_e( 'انتشار:', 'technopay' ); ?>
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( technopay_post_date( null, 'numeric' ) ); ?></time>
							</span>
							<?php if ( $technopay_updated ) : ?>
								<span class="meta-chip">
									<?php technopay_icon( 'refresh' ); ?>
									<?php esc_html_e( 'به‌روزرسانی:', 'technopay' ); ?>
									<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php echo esc_html( $technopay_updated ); ?></time>
								</span>
							<?php endif; ?>
							<span class="meta-chip">
								<?php technopay_icon( 'clock' ); ?>
								<?php
								/* translators: %s: minutes */
								echo esc_html( sprintf( __( '%s دقیقه مطالعه', 'technopay' ), technopay_fa_digits( technopay_reading_time() ) ) );
								?>
							</span>
							<span class="meta-chip">
								<?php technopay_icon( 'eye' ); ?>
								<span class="sr-only"><?php esc_html_e( 'بازدید:', 'technopay' ); ?></span>
								<?php echo esc_html( technopay_number( technopay_get_views() ) ); ?>
							</span>
							<a class="meta-chip meta-chip--link" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>">
								<?php technopay_icon( 'user' ); ?>
								<?php the_author(); ?>
							</a>
							<?php if ( technopay_option( 'policy_text' ) && technopay_option( 'policy_url' ) ) : ?>
								<a class="meta-chip meta-chip--link meta-chip--end" href="<?php echo esc_url( technopay_option( 'policy_url' ) ); ?>">
									<?php technopay_icon( 'shield' ); ?>
									<?php echo esc_html( technopay_option( 'policy_text' ) ); ?>
								</a>
							<?php endif; ?>
						</div>
					</header>

					<figure class="post-card__cover">
						<div class="post-card__img">
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
					</div>

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
				</article>

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

			<?php if ( '' !== $technopay_sidebar_html ) : ?>
				<aside class="sidebar sidebar--single" aria-label="<?php esc_attr_e( 'سایدبار نوشته', 'technopay' ); ?>">
					<?php echo $technopay_sidebar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where it is generated. ?>
				</aside>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
