<?php
/**
 * تگ‌های قالب (بخش‌های تکرارشونده HTML).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * متای نوشته: تاریخ، زمان مطالعه، بازدید، دیدگاه.
 *
 * @param string[] $items آیتم‌ها: date, reading, views, comments.
 * @param string   $class کلاس ظرف.
 */
function technopay_post_meta( $items = array( 'date', 'reading' ), $class = 'meta' ) {
	$out = array();
	foreach ( $items as $item ) {
		switch ( $item ) {
			case 'date':
				$out[] = sprintf(
					'<span>%1$s<time datetime="%2$s">%3$s</time></span>',
					technopay_get_icon( 'calendar' ),
					esc_attr( get_the_date( DATE_W3C ) ),
					esc_html( technopay_post_date() )
				);
				break;
			case 'reading':
				$out[] = sprintf(
					'<span>%1$s%2$s</span>',
					technopay_get_icon( 'clock' ),
					/* translators: %s: minutes */
					esc_html( sprintf( __( '%s دقیقه مطالعه', 'technopay' ), technopay_fa_digits( technopay_reading_time() ) ) )
				);
				break;
			case 'views':
				$out[] = sprintf(
					'<span title="%3$s">%1$s%2$s</span>',
					technopay_get_icon( 'eye' ),
					esc_html( technopay_number( technopay_get_views() ) ),
					esc_attr__( 'بازدید', 'technopay' )
				);
				break;
			case 'comments':
				$out[] = sprintf(
					'<span title="%3$s">%1$s%2$s</span>',
					technopay_get_icon( 'message' ),
					esc_html( technopay_number( get_comments_number() ) ),
					esc_attr__( 'دیدگاه', 'technopay' )
				);
				break;
		}
	}
	if ( $out ) {
		printf( '<div class="%1$s">%2$s</div>', esc_attr( $class ), implode( '', $out ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * برچسب دسته نوشته.
 *
 * @param string $class کلاس.
 */
function technopay_category_badge( $class = 'badge badge--solid' ) {
	$term = technopay_primary_category();
	if ( ! $term ) {
		return;
	}
	printf(
		'<a class="%1$s" href="%2$s">%3$s</a>',
		esc_attr( $class ),
		esc_url( get_category_link( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * نام و آواتار نویسنده.
 *
 * @param bool $with_role نمایش عنوان «نویسنده».
 */
function technopay_author_chip( $with_role = false ) {
	$author_id = (int) get_the_author_meta( 'ID' );
	$name      = get_the_author();
	printf(
		'<a class="author" href="%1$s">%2$s<span>%3$s%4$s</span></a>',
		esc_url( get_author_posts_url( $author_id ) ),
		technopay_get_avatar( $author_id, $name, $with_role ? 44 : 30 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $name ),
		$with_role ? '<small>' . esc_html__( 'نویسنده', 'technopay' ) . '</small>' : ''
	);
}

/**
 * خلاصه نوشته با تعداد کلمات مشخص.
 *
 * @param int $words تعداد کلمات.
 * @return string
 */
function technopay_excerpt( $words = 28 ) {
	$text = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags( strip_shortcodes( get_the_content() ) );
	return wp_trim_words( $text, $words, '…' );
}

/**
 * مسیر راهنما (Breadcrumb). در صورت وجود از Yoast یا RankMath استفاده می‌شود.
 *
 * @param string $extra_class کلاس اضافه (مثلاً breadcrumb--box برای نمایش کادردار).
 */
function technopay_breadcrumb( $extra_class = '' ) {
	$class = trim( 'breadcrumb ' . $extra_class );

	if ( function_exists( 'yoast_breadcrumb' ) && class_exists( 'WPSEO_Options' ) && WPSEO_Options::get( 'breadcrumbs-enable' ) ) {
		yoast_breadcrumb( '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'مسیر صفحه', 'technopay' ) . '">', '</nav>' );
		return;
	}
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'مسیر صفحه', 'technopay' ) . '">';
		rank_math_the_breadcrumbs();
		echo '</nav>';
		return;
	}

	$sep   = technopay_get_icon( 'chevron-left' );
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'خانه', 'technopay' ) . '</a>' );

	if ( is_singular( 'post' ) ) {
		$term = technopay_primary_category();
		if ( $term ) {
			foreach ( array_reverse( get_ancestors( $term->term_id, 'category' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'category' );
				$items[]  = '<a href="' . esc_url( get_category_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
			}
			$items[] = '<a href="' . esc_url( get_category_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
		}
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( is_category() ) {
			foreach ( array_reverse( get_ancestors( $term->term_id, 'category' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'category' );
				$items[]  = '<a href="' . esc_url( get_category_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
			}
		}
		$items[] = '<span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'جستجو', 'technopay' ) . '</span>';
	} elseif ( is_author() ) {
		$items[] = '<span aria-current="page">' . esc_html( get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) ) ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_404() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'صفحه پیدا نشد', 'technopay' ) . '</span>';
	} elseif ( is_home() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'مقالات', 'technopay' ) . '</span>';
	}

	printf(
		'<nav class="%1$s" aria-label="%2$s">%3$s</nav>',
		esc_attr( $class ),
		esc_attr__( 'مسیر صفحه', 'technopay' ),
		implode( $sep, $items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * صفحه‌بندی آرشیوها.
 */
function technopay_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => technopay_get_icon( 'chevron-right' ) . '<span class="sr-only">' . esc_html__( 'صفحه قبل', 'technopay' ) . '</span>',
			'next_text' => technopay_get_icon( 'chevron-left' ) . '<span class="sr-only">' . esc_html__( 'صفحه بعد', 'technopay' ) . '</span>',
		)
	);
	if ( ! $links ) {
		return;
	}
	printf(
		'<nav class="pagination" aria-label="%1$s">%2$s</nav>',
		esc_attr__( 'صفحه‌بندی', 'technopay' ),
		implode( '', array_map( 'technopay_fa_digits_in_text', $links ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * تبدیل ارقام داخل متن (نه داخل تگ‌ها) به فارسی.
 *
 * @param string $html HTML.
 * @return string
 */
function technopay_fa_digits_in_text( $html ) {
	return preg_replace_callback(
		'/>([^<]*)</u',
		function ( $m ) {
			return '>' . technopay_fa_digits( $m[1] ) . '<';
		},
		$html
	);
}

/**
 * دکمه‌های اشتراک‌گذاری.
 */
function technopay_share_buttons() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$items = array(
		array( 'telegram', __( 'اشتراک در تلگرام', 'technopay' ), 'https://t.me/share/url?url=' . $url . '&text=' . $title ),
		array( 'whatsapp', __( 'اشتراک در واتس‌اپ', 'technopay' ), 'https://wa.me/?text=' . $title . '%20' . $url ),
		array( 'x', __( 'اشتراک در ایکس', 'technopay' ), 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		array( 'linkedin', __( 'اشتراک در لینکدین', 'technopay' ), 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ),
	);
	echo '<div class="share"><span>' . esc_html__( 'اشتراک‌گذاری:', 'technopay' ) . '</span>';
	foreach ( $items as $item ) {
		printf(
			'<a class="icon-btn" href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s</a>',
			esc_url( $item[2] ),
			esc_attr( $item[1] ),
			technopay_get_icon( $item[0] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
	printf(
		'<button type="button" class="icon-btn" data-copy-link="%1$s" aria-label="%2$s">%3$s</button>',
		esc_url( wp_get_shortlink() ? wp_get_shortlink() : get_permalink() ),
		esc_attr__( 'کپی لینک', 'technopay' ),
		technopay_get_icon( 'link' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
	echo '</div>';
}

/**
 * نوار ابزار آرشیو: تعداد مطالب، مرتب‌سازی و نوع نمایش.
 */
function technopay_archive_toolbar() {
	global $wp_query;
	$current = technopay_current_sort();
	$options = array(
		'latest'   => __( 'جدیدترین', 'technopay' ),
		'popular'  => __( 'پربازدیدترین', 'technopay' ),
		'comments' => __( 'پربحث‌ترین', 'technopay' ),
	);
	?>
	<div class="toolbar">
		<p class="toolbar__count">
			<?php
			/* translators: %s: number of posts */
			printf( esc_html__( '%s مطلب', 'technopay' ), '<strong>' . esc_html( technopay_number( $wp_query->found_posts ) ) . '</strong>' );
			?>
		</p>
		<div class="tabs" aria-label="<?php esc_attr_e( 'مرتب‌سازی', 'technopay' ); ?>">
			<?php foreach ( $options as $key => $label ) : ?>
				<a class="chip<?php echo $key === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( ( 'latest' === $key && ! ( is_home() && is_front_page() ) ) ? remove_query_arg( 'sort', get_pagenum_link( 1 ) ) : add_query_arg( 'sort', $key, get_pagenum_link( 1 ) ) ); ?>"<?php echo $key === $current ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
		<div class="view-switch">
			<button type="button" class="icon-btn is-active" data-view="list" aria-pressed="true" aria-label="<?php esc_attr_e( 'نمایش لیستی', 'technopay' ); ?>"><?php technopay_icon( 'list' ); ?></button>
			<button type="button" class="icon-btn" data-view="grid" aria-pressed="false" aria-label="<?php esc_attr_e( 'نمایش شبکه‌ای', 'technopay' ); ?>"><?php technopay_icon( 'grid' ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * بخش «عنوان سکشن» با لینک «مشاهده همه».
 *
 * @param string $title    عنوان.
 * @param string $more_url لینک.
 * @param string $subtitle زیرعنوان.
 * @param string $tag      تگ عنوان.
 */
function technopay_section_head( $title, $more_url = '', $subtitle = '', $tag = 'h2' ) {
	$tag = tag_escape( $tag );
	echo '<div class="section-head"><div>';
	printf( '<%1$s class="section-title">%2$s</%1$s>', $tag, esc_html( $title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $subtitle ) {
		printf( '<p class="section-sub">%s</p>', esc_html( $subtitle ) );
	}
	echo '</div>';
	if ( $more_url ) {
		printf(
			'<a class="more-link" href="%1$s">%2$s%3$s</a>',
			esc_url( $more_url ),
			esc_html__( 'مشاهده همه', 'technopay' ),
			technopay_get_icon( 'chevron-left' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
	echo '</div>';
}

/**
 * ردیف کارت‌های فشرده داخل جعبه خاکستری (آخرین مطالب، پربازدیدترین‌ها و ...).
 *
 * @param array $args {
 *     @type string    $id       شناسه یکتا برای aria-labelledby.
 *     @type string    $title    عنوان بخش.
 *     @type WP_Post[] $posts    نوشته‌ها.
 *     @type string    $meta     نوع متای پایین کارت: date | views | comments.
 *     @type string    $more_url لینک «مشاهده همه» (خالی = مخفی).
 * }
 */
function technopay_post_row( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'       => wp_unique_id( 'rail-' ),
			'title'    => '',
			'posts'    => array(),
			'meta'     => 'date',
			'more_url' => '',
		)
	);
	if ( ! $args['posts'] ) {
		return;
	}
	global $post;
	technopay_prime_thumbnails( $args['posts'] );
	?>
	<section class="section section--rail container" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>">
		<div class="rail">
			<div class="rail__head">
				<h2 class="pill-title" id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['title'] ); ?></h2>
				<?php if ( $args['more_url'] ) : ?>
					<a class="more-link" href="<?php echo esc_url( $args['more_url'] ); ?>" aria-describedby="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'مشاهده همه', 'technopay' ); ?><?php technopay_icon( 'chevron-left' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="rail__grid">
				<?php
				foreach ( $args['posts'] as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					get_template_part( 'template-parts/card-compact', null, array( 'meta' => $args['meta'] ) );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * ویجت لیستی کوچک (تصویر بندانگشتی + عنوان + تاریخ) برای سایدبار نوشته.
 *
 * @param string    $title عنوان.
 * @param WP_Post[] $posts نوشته‌ها.
 */
function technopay_mini_list( $title, $posts ) {
	if ( ! $posts ) {
		return;
	}
	global $post;
	technopay_prime_thumbnails( $posts );
	?>
	<section class="widget widget--mini">
		<h2 class="widget__title"><?php echo esc_html( $title ); ?></h2>
		<div class="mini-list">
			<?php
			foreach ( $posts as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				get_template_part( 'template-parts/mini' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/**
 * قالب نمایش هر دیدگاه.
 *
 * @param WP_Comment $comment دیدگاه.
 * @param array      $args    آرگومان‌ها.
 * @param int        $depth   عمق.
 */
function technopay_comment( $comment, $args, $depth ) {
	$name     = get_comment_author( $comment );
	$is_staff = $comment->user_id && user_can( (int) $comment->user_id, 'edit_posts' );
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( '', $comment ); ?>>
		<article class="comment-card">
			<?php echo technopay_get_avatar( $comment, $name, 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="comment__body">
				<div class="comment__head">
					<strong><?php echo esc_html( $name ); ?></strong>
					<?php if ( $is_staff ) : ?>
						<span class="badge"><?php esc_html_e( 'تیم تکنوپی', 'technopay' ); ?></span>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C, $comment ) ); ?>"><?php echo esc_html( technopay_format_date( (int) get_comment_date( 'U', $comment ) ) ); ?></time>
				</div>
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<p class="comment__pending"><?php esc_html_e( 'دیدگاه شما پس از تأیید نمایش داده می‌شود.', 'technopay' ); ?></p>
				<?php endif; ?>
				<div class="comment__text"><?php comment_text(); ?></div>
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
							'reply_text' => technopay_get_icon( 'reply', 'icon--sm' ) . esc_html__( 'پاسخ', 'technopay' ),
							'before'     => '<div class="comment__reply">',
							'after'      => '</div>',
						)
					)
				);
				?>
			</div>
		</article>
	<?php
	// تگ </li> توسط Walker بسته می‌شود.
}
