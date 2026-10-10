<?php
/**
 * بلوک‌های چیدمان: تابع رندر هر بخش از صفحه‌ها (در رجیستری inc/layout.php معرفی شده‌اند).
 * هر تابع تنظیمات خودش (merge‌شده با پیش‌فرض‌ها) و context را می‌گیرد.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   بخش‌های تمام‌عرض (قابل استفاده در همه صفحه‌ها)
   ========================================================================== */

/**
 * مطالب ویژه.
 */
function technopay_block_hero() {
	get_template_part( 'template-parts/home/hero' );
}

/**
 * نوار داغ‌ترین‌ها.
 */
function technopay_block_trending() {
	get_template_part( 'template-parts/home/trending' );
}

/**
 * کاشی دسته‌ها.
 */
function technopay_block_categories() {
	get_template_part( 'template-parts/home/categories' );
}

/**
 * ردیف آخرین مطالب.
 */
function technopay_block_latest() {
	get_template_part( 'template-parts/home/latest' );
}

/**
 * ردیف پربازدیدترین‌ها.
 */
function technopay_block_popular() {
	get_template_part( 'template-parts/home/popular' );
}

/**
 * بنر دریافت اعتبار.
 */
function technopay_block_cta() {
	get_template_part( 'template-parts/home/cta' );
}

/**
 * بلوک یک دسته.
 */
function technopay_block_cat_block() {
	get_template_part( 'template-parts/home/cat-block' );
}

/**
 * مراحل دریافت اعتبار.
 */
function technopay_block_steps() {
	get_template_part( 'template-parts/home/steps' );
}

/**
 * دو ستون دسته‌ها.
 */
function technopay_block_panels() {
	get_template_part( 'template-parts/home/panels' );
}

/**
 * خبرنامه.
 */
function technopay_block_newsletter() {
	get_template_part( 'template-parts/home/newsletter' );
}

/* ==========================================================================
   آرشیو و جستجو
   ========================================================================== */

/**
 * سربرگ آرشیو (دسته، برچسب، نویسنده، تاریخ، صفحه نوشته‌ها).
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_archive_hero( $s ) {
	$object  = get_queried_object();
	$is_blog = is_home();
	$title   = $is_blog && ! is_category() && ! is_tag() && ! is_author() && ! is_date()
		? ( (int) get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'آخرین مطالب', 'technopay' ) )
		: wp_strip_all_tags( get_the_archive_title() );
	?>
	<div class="container">
		<header class="archive-hero">
			<div>
				<?php
				if ( $s['show_breadcrumb'] ) {
					technopay_breadcrumb();
				}
				?>
				<?php if ( is_category() ) : ?>
					<span class="badge"><?php esc_html_e( 'دسته‌بندی', 'technopay' ); ?></span>
				<?php elseif ( is_tag() ) : ?>
					<span class="badge"><?php esc_html_e( 'برچسب', 'technopay' ); ?></span>
				<?php elseif ( is_author() ) : ?>
					<span class="badge"><?php esc_html_e( 'نویسنده', 'technopay' ); ?></span>
				<?php endif; ?>

				<h1><?php echo esc_html( $title ); ?></h1>

				<?php if ( $s['show_description'] ) : ?>
					<?php if ( is_author() ) : ?>
						<?php $bio = get_the_author_meta( 'description', (int) get_query_var( 'author' ) ); ?>
						<?php if ( $bio ) : ?>
							<p><?php echo esc_html( $bio ); ?></p>
						<?php endif; ?>
					<?php elseif ( $is_blog && ! is_category() && ! is_tag() && ! is_date() ) : ?>
						<?php if ( get_bloginfo( 'description' ) ) : ?>
							<p><?php bloginfo( 'description' ); ?></p>
						<?php endif; ?>
					<?php elseif ( get_the_archive_description() ) : ?>
						<div class="archive-hero__desc"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
					<?php endif; ?>
				<?php endif; ?>

				<?php
				if ( $s['show_children'] && is_category() ) :
					$children = get_categories(
						array(
							'parent'     => $object->term_id,
							'hide_empty' => true,
						)
					);
					if ( $children ) :
						?>
						<div class="archive-hero__chips">
							<?php foreach ( $children as $child ) : ?>
								<a class="chip" href="<?php echo esc_url( get_category_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a>
							<?php endforeach; ?>
						</div>
						<?php
					endif;
				endif;
				?>
			</div>

			<?php if ( $s['show_icon'] ) : ?>
				<?php if ( is_category() || is_tag() ) : ?>
					<?php $style = technopay_term_style( $object ); ?>
					<span class="archive-hero__icon bg-<?php echo esc_attr( $style['color'] ); ?>" aria-hidden="true"><?php technopay_icon( is_tag() ? 'tag' : $style['icon'] ); ?></span>
				<?php elseif ( is_author() ) : ?>
					<?php echo technopay_get_avatar( (int) get_query_var( 'author' ), get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ), 96, 'avatar--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span class="archive-hero__icon bg-1" aria-hidden="true"><?php technopay_icon( $is_blog ? 'book' : 'calendar' ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</header>
	</div>
	<?php
}

/**
 * سربرگ نتایج جستجو.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_search_hero( $s ) {
	?>
	<div class="container">
		<header class="archive-hero archive-hero--search">
			<div>
				<?php
				if ( $s['show_breadcrumb'] ) {
					technopay_breadcrumb();
				}
				?>
				<span class="badge"><?php esc_html_e( 'جستجو', 'technopay' ); ?></span>
				<h1>
					<?php
					/* translators: %s: search query */
					printf( esc_html__( 'نتایج جستجو برای «%s»', 'technopay' ), esc_html( get_search_query() ) );
					?>
				</h1>
				<?php
				if ( $s['show_form'] ) {
					get_search_form();
				}
				?>
			</div>
			<?php if ( $s['show_icon'] ) : ?>
				<span class="archive-hero__icon bg-1" aria-hidden="true"><?php technopay_icon( 'search' ); ?></span>
			<?php endif; ?>
		</header>
	</div>
	<?php
}

/**
 * لیست نوشته‌ها با نوارابزار، صفحه‌بندی و سایدبار.
 *
 * @param array $s       تنظیمات بلوک.
 * @param array $context page و region.
 */
function technopay_block_results( $s, $context ) {
	$page     = isset( $context['page'] ) && 'search' === $context['page'] ? 'search' : 'archive';
	$position = technopay_page_option( $page, 'sidebar_position' );
	$parts    = 'none' === $position ? array( 'html' => '', 'sticky' => false ) : technopay_sidebar_parts( $page, $context );
	$sidebar  = $parts['html'];
	if ( '' === $sidebar ) {
		$position = 'none';
	}
	?>
	<section class="section container">
		<div class="<?php echo esc_attr( technopay_layout_class( 'layout', $position ) ); ?>">
			<div class="layout__main">
				<?php if ( have_posts() ) : ?>
					<?php
					if ( $s['show_toolbar'] ) {
						technopay_archive_toolbar(
							array(
								'show_sort'        => $s['show_sort'],
								'show_view_switch' => $s['show_view_switch'],
								'view'             => $s['view'],
							)
						);
					}
					?>
					<div class="post-list<?php echo 'grid' === $s['view'] ? ' is-grid' : ''; ?>">
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
					<?php
					if ( $s['show_suggestions'] ) {
						echo '<div class="empty-suggestions">';
						technopay_mini_list( __( 'مطالب پیشنهادی', 'technopay' ), technopay_get_popular_posts( 5 ) );
						echo '</div>';
					}
					?>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $sidebar ) : ?>
				<aside class="sidebar<?php echo $parts['sticky'] ? ' sidebar--has-sticky' : ''; ?>" aria-label="<?php esc_attr_e( 'سایدبار', 'technopay' ); ?>">
					<?php echo $sidebar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where it is generated. ?>
				</aside>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/* ==========================================================================
   صفحه نوشته
   ========================================================================== */

/**
 * عنوان (H1) و ردیف اطلاعات.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_s_header( $s ) {
	$updated = $s['show_updated'] ? technopay_modified_date( null, 'numeric' ) : '';
	?>
	<header class="post-card__head">
		<?php the_title( '<h1 class="post-card__title">', '</h1>' ); ?>
		<div class="post-card__meta">
			<span class="meta-chip">
				<?php technopay_icon( 'calendar' ); ?>
				<?php esc_html_e( 'انتشار:', 'technopay' ); ?>
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( technopay_post_date( null, 'numeric' ) ); ?></time>
			</span>
			<?php if ( $updated ) : ?>
				<span class="meta-chip">
					<?php technopay_icon( 'refresh' ); ?>
					<?php esc_html_e( 'به‌روزرسانی:', 'technopay' ); ?>
					<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php echo esc_html( $updated ); ?></time>
				</span>
			<?php endif; ?>
			<?php if ( $s['show_reading'] ) : ?>
				<span class="meta-chip">
					<?php technopay_icon( 'clock' ); ?>
					<?php
					/* translators: %s: minutes */
					echo esc_html( sprintf( __( '%s دقیقه مطالعه', 'technopay' ), technopay_fa_digits( technopay_reading_time() ) ) );
					?>
				</span>
			<?php endif; ?>
			<?php if ( $s['show_views'] ) : ?>
				<span class="meta-chip">
					<?php technopay_icon( 'eye' ); ?>
					<span class="sr-only"><?php esc_html_e( 'بازدید:', 'technopay' ); ?></span>
					<?php echo esc_html( technopay_number( technopay_get_views() ) ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $s['show_author'] ) : ?>
				<a class="meta-chip meta-chip--link" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>">
					<?php technopay_icon( 'user' ); ?>
					<?php the_author(); ?>
				</a>
			<?php endif; ?>
			<?php if ( technopay_option( 'policy_text' ) && technopay_option( 'policy_url' ) ) : ?>
				<a class="meta-chip meta-chip--link" href="<?php echo esc_url( technopay_option( 'policy_url' ) ); ?>">
					<?php technopay_icon( 'shield' ); ?>
					<?php echo esc_html( technopay_option( 'policy_text' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * تصویر شاخص.
 */
function technopay_block_s_cover() {
	?>
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
	<?php
}

/**
 * متن مقاله.
 */
function technopay_block_s_content() {
	?>
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
	<?php
}

/**
 * برچسب‌ها و اشتراک‌گذاری.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_s_footer( $s ) {
	$tags = $s['show_tags'] ? get_the_tags() : false;
	if ( ! $tags && ! $s['show_share'] ) {
		return;
	}
	?>
	<footer class="post-foot">
		<?php if ( $tags ) : ?>
			<div class="tag-cloud">
				<?php foreach ( $tags as $tag ) : ?>
					<a class="tag" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" rel="tag"><?php echo esc_html( $tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
		if ( $s['show_share'] ) {
			technopay_share_buttons();
		}
		?>
	</footer>
	<?php
}

/**
 * باکس نویسنده.
 */
function technopay_block_s_author() {
	$author_id = (int) get_the_author_meta( 'ID' );
	?>
	<section class="author-box" aria-label="<?php esc_attr_e( 'درباره نویسنده', 'technopay' ); ?>">
		<?php echo technopay_get_avatar( $author_id, get_the_author(), 72, 'avatar--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div>
			<h4><?php the_author(); ?> <small><?php esc_html_e( 'نویسنده', 'technopay' ); ?></small></h4>
			<p>
				<?php
				$bio = get_the_author_meta( 'description' );
				echo esc_html( $bio ? $bio : __( 'نویسنده مجله تکنوپی؛ همراه شما برای خرید هوشمند و مدیریت بهتر مالی.', 'technopay' ) );
				?>
			</p>
		</div>
	</section>
	<?php
}

/**
 * نوشته قبلی و بعدی.
 */
function technopay_block_s_nav() {
	$prev = get_previous_post();
	$next = get_next_post();
	if ( ! $prev && ! $next ) {
		return;
	}
	?>
	<nav class="post-nav" aria-label="<?php esc_attr_e( 'نوشته‌های قبلی و بعدی', 'technopay' ); ?>">
		<?php if ( $prev ) : ?>
			<a class="post-nav__prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev">
				<small><?php technopay_icon( 'chevron-right', 'icon--sm' ); ?><?php esc_html_e( 'مطلب قبلی', 'technopay' ); ?></small>
				<strong><?php echo esc_html( get_the_title( $prev ) ); ?></strong>
			</a>
		<?php endif; ?>
		<?php if ( $next ) : ?>
			<a class="post-nav__next" href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next">
				<small><?php esc_html_e( 'مطلب بعدی', 'technopay' ); ?><?php technopay_icon( 'chevron-left', 'icon--sm' ); ?></small>
				<strong><?php echo esc_html( get_the_title( $next ) ); ?></strong>
			</a>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * دیدگاه‌ها.
 */
function technopay_block_s_comments() {
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
}

/**
 * مطالب مرتبط به‌صورت کارت‌های بزرگ.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_s_related_grid( $s ) {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$posts = technopay_get_related_posts( get_the_ID(), (int) $s['count'] );
	if ( ! $posts ) {
		return;
	}
	technopay_prime_thumbnails( $posts );
	global $post;
	?>
	<section class="section container related">
		<?php technopay_section_head( '' !== $s['title'] ? $s['title'] : __( 'مطالب مرتبط', 'technopay' ) ); ?>
		<div class="posts-grid posts-grid--3">
			<?php
			foreach ( $posts as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				get_template_part( 'template-parts/card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/* ==========================================================================
   صفحه ۴۰۴
   ========================================================================== */

/**
 * پیام ۴۰۴.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_e_message( $s ) {
	?>
	<section class="section container">
		<div class="error-404">
			<p class="error-404__code" aria-hidden="true">۴۰۴</p>
			<h1><?php esc_html_e( 'صفحه‌ای که دنبالش بودید پیدا نشد!', 'technopay' ); ?></h1>
			<p><?php esc_html_e( 'ممکن است آدرس را اشتباه وارد کرده باشید یا این صفحه حذف شده باشد. از جستجو کمک بگیرید یا به صفحه اصلی برگردید.', 'technopay' ); ?></p>
			<?php
			if ( $s['show_search'] ) {
				get_search_form();
			}
			?>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php technopay_icon( 'home' ); ?><?php esc_html_e( 'بازگشت به صفحه اصلی', 'technopay' ); ?></a>
		</div>
	</section>
	<?php
}

/**
 * مطالب پیشنهادی در صفحه ۴۰۴.
 *
 * @param array $s تنظیمات بلوک.
 */
function technopay_block_e_suggested( $s ) {
	$posts = technopay_get_latest_posts( (int) $s['count'] );
	if ( ! $posts ) {
		return;
	}
	technopay_prime_thumbnails( $posts );
	global $post;
	?>
	<section class="section container">
		<?php technopay_section_head( '' !== $s['title'] ? $s['title'] : __( 'شاید این مطالب برایتان جالب باشد', 'technopay' ) ); ?>
		<div class="posts-grid posts-grid--3">
			<?php
			foreach ( $posts as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				get_template_part( 'template-parts/card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/* ==========================================================================
   ابزارک‌های سایدبار
   ========================================================================== */

/**
 * شناسه نوشته‌هایی که در لیست‌های کوچک سایدبار نشان داده شده‌اند (تا «مرتبط» تکرارشان نکند).
 *
 * @param int[] $add شناسه‌های جدید.
 * @return int[]
 */
function technopay_sidebar_shown_ids( $add = array() ) {
	static $ids = array();
	if ( $add ) {
		$ids = array_values( array_unique( array_merge( $ids, array_map( 'intval', (array) $add ) ) ) );
	}
	return $ids;
}

/**
 * یک ابزارک سایدبار با عنوان و آیکون.
 *
 * @param string   $title    عنوان.
 * @param string   $icon     نام آیکون.
 * @param callable $callback تولید محتوا.
 */
function technopay_widget_box( $title, $icon, $callback ) {
	echo '<section class="widget"><h2 class="widget__title">';
	technopay_icon( $icon );
	echo esc_html( $title ) . '</h2>';
	call_user_func( $callback );
	echo '</section>';
}

/**
 * لیست شماره‌دار پربازدیدترین‌ها.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_popular( $s ) {
	technopay_widget_box(
		$s['title'],
		'flame',
		function () use ( $s ) {
			technopay_render_popular_list( (int) $s['count'] );
		}
	);
}

/**
 * پربازدیدترین (کوچک، با تصویر).
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_popular_mini( $s ) {
	$exclude = is_singular() ? array( get_the_ID() ) : array();
	$posts   = technopay_get_popular_posts( (int) $s['count'], $exclude );
	technopay_sidebar_shown_ids( wp_list_pluck( $posts, 'ID' ) );
	technopay_mini_list( $s['title'], $posts );
}

/**
 * جدیدترین (کوچک، با تصویر).
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_latest_mini( $s ) {
	$exclude = is_singular() ? array( get_the_ID() ) : array();
	$posts   = technopay_get_latest_posts( (int) $s['count'], $exclude );
	technopay_sidebar_shown_ids( wp_list_pluck( $posts, 'ID' ) );
	technopay_mini_list( $s['title'], $posts );
}

/**
 * مقالات مرتبط (کوچک، با تصویر): فقط در صفحه نوشته.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_related_mini( $s ) {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	technopay_mini_list( $s['title'], technopay_get_related_posts( get_the_ID(), (int) $s['count'], technopay_sidebar_shown_ids() ) );
}

/**
 * محاسبه‌گر اقساط.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_calculator( $s ) {
	technopay_widget_box( $s['title'], 'calculator', 'technopay_render_calculator' );
}

/**
 * لیست دسته‌ها.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_categories( $s ) {
	technopay_widget_box(
		$s['title'],
		'folder',
		function () use ( $s ) {
			technopay_render_category_list( (int) $s['count'] );
		}
	);
}

/**
 * بنر دریافت اعتبار.
 */
function technopay_block_w_promo() {
	technopay_render_promo();
}

/**
 * ابر برچسب‌ها.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_tags( $s ) {
	if ( ! get_tags( array( 'number' => 1 ) ) ) {
		return;
	}
	technopay_widget_box(
		$s['title'],
		'tag',
		function () use ( $s ) {
			technopay_render_tag_cloud( (int) $s['count'] );
		}
	);
}

/**
 * فرم جستجو.
 *
 * @param array $s تنظیمات.
 */
function technopay_block_w_search( $s ) {
	technopay_widget_box( $s['title'], 'search', 'get_search_form' );
}

/**
 * ابزارک‌های وردپرس: سایدبار اصلی.
 */
function technopay_block_w_widgets_main() {
	if ( is_active_sidebar( 'technopay-main' ) ) {
		dynamic_sidebar( 'technopay-main' );
	}
}

/**
 * ابزارک‌های وردپرس: سایدبار نوشته.
 */
function technopay_block_w_widgets_single() {
	if ( is_active_sidebar( 'technopay-single' ) ) {
		dynamic_sidebar( 'technopay-single' );
	}
}
