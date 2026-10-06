<?php
/**
 * فهرست مطالب خودکار داخل نوشته: از سرتیترهای H2/H3 ساخته و قبل از اولین سرتیتر درج می‌شود.
 *
 * - سرتیترهای داخل بلوک «جزئیات» (سوالات متداول آکاردئونی، حتی تودرتو)، باکس‌های callout/CTA و
 *   عناصر دارای کلاس no-toc نادیده گرفته می‌شوند.
 * - اگر تعداد سرتیترها کمتر از ۳ باشد (فیلتر technopay_toc_min_headings) فهرستی نمایش داده نمی‌شود.
 * - برای هر نوشته با فیلد دلخواه technopay_hide_toc = 1 غیرفعال می‌شود.
 * - فقط روی محتوای اصلی نوشته اجرا می‌شود (نه خلاصه‌ها، نه محتوای نوشته‌های دیگر).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * ساخت HTML فهرست مطالب.
 *
 * @param array[] $items هر آیتم: level (2|3)، text، id.
 * @return string
 */
function technopay_build_toc( $items ) {
	$title = esc_html__( 'فهرست مطالب', 'technopay' );
	$base  = min( wp_list_pluck( $items, 'level' ) ); // سطح بالاترین سرتیتر؛ فقط عمیق‌تر از آن زیرمجموعه می‌شود.
	$out   = '<details class="toc-box" open>';
	$out  .= '<summary class="toc-box__head"><span class="toc-box__title">' . $title . '</span><span class="toc-box__toggle" aria-hidden="true"></span></summary>';
	$out  .= '<nav class="toc-box__body" aria-label="' . esc_attr__( 'فهرست مطالب', 'technopay' ) . '"><ol class="toc-box__list" role="list">';

	$num      = 0;
	$open_li  = false;
	$open_sub = false;

	foreach ( $items as $item ) {
		$link = '<a href="#' . esc_attr( $item['id'] ) . '">';

		if ( $item['level'] > $base && $num > 0 ) {
			if ( ! $open_sub ) {
				$out     .= '<ol class="toc-box__sub" role="list">';
				$open_sub = true;
			}
			$out .= '<li>' . $link . '<span class="toc-box__text">' . esc_html( $item['text'] ) . '</span></a></li>';
			continue;
		}

		if ( $open_sub ) {
			$out     .= '</ol>';
			$open_sub = false;
		}
		if ( $open_li ) {
			$out .= '</li>';
		}
		++$num;
		$out    .= '<li>' . $link . '<span class="toc-box__num" aria-hidden="true">' . esc_html( technopay_fa_digits( $num ) ) . '</span><span class="toc-box__text">' . esc_html( $item['text'] ) . '</span></a>';
		$open_li = true;
	}

	if ( $open_sub ) {
		$out .= '</ol>';
	}
	if ( $open_li ) {
		$out .= '</li>';
	}

	return $out . '</ol></nav></details>';
}

/**
 * محدوده‌های HTML که سرتیترهایشان وارد فهرست نمی‌شود: هر <details> (تودرتو هم)، و عناصر دارای
 * کلاس tp-cta، callout، no-toc، post-foot یا wp-block-details.
 *
 * @param string $html HTML محتوا.
 * @return array[] هر مورد [شروع، پایان].
 */
function technopay_toc_skip_ranges( $html ) {
	$ranges = array();
	if ( ! preg_match_all( '/<(\/?)([a-zA-Z][a-zA-Z0-9]*)\b([^>]*?)(\/?)>/', $html, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
		return $ranges;
	}

	static $void = array(
		'br'     => 1,
		'hr'     => 1,
		'img'    => 1,
		'input'  => 1,
		'meta'   => 1,
		'link'   => 1,
		'source' => 1,
		'track'  => 1,
		'wbr'    => 1,
		'col'    => 1,
		'area'   => 1,
		'embed'  => 1,
		'param'  => 1,
	);

	$stack = array();
	foreach ( $tags as $tag ) {
		$closing = '/' === $tag[1][0];
		$name    = strtolower( $tag[2][0] );
		if ( isset( $void[ $name ] ) || '/' === $tag[4][0] ) {
			continue;
		}
		$start = $tag[0][1];

		if ( ! $closing ) {
			$skip    = 'details' === $name || (bool) preg_match( '/(?<![\w-])class\s*=\s*(["\'])[^"\']*(?<![\w-])(?:tp-cta|callout|no-toc|post-foot|wp-block-details)(?![\w-])[^"\']*\1/i', $tag[3][0] );
			$stack[] = array( $name, $skip ? $start : null );
			continue;
		}

		for ( $i = count( $stack ) - 1; $i >= 0; $i-- ) {
			if ( $stack[ $i ][0] === $name ) {
				$frame = $stack[ $i ];
				array_splice( $stack, $i );
				if ( null !== $frame[1] ) {
					$ranges[] = array( $frame[1], $start + strlen( $tag[0][0] ) );
				}
				break;
			}
		}
	}
	return $ranges;
}

/**
 * درج فهرست مطالب در محتوای نوشته.
 *
 * @param string $content محتوا.
 * @return string
 */
function technopay_content_toc( $content ) {
	if ( is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}

	$post_id = (int) get_the_ID();
	// فقط محتوای خود نوشته اصلی (نه نوشته‌های دیگری که ابزارک‌ها داخل حلقه می‌سازند).
	if ( $post_id !== (int) get_queried_object_id() ) {
		return $content;
	}
	if ( ! technopay_option( 'show_toc' ) || get_post_meta( $post_id, 'technopay_hide_toc', true ) || ! apply_filters( 'technopay_toc_enabled', true, $post_id ) ) {
		return $content;
	}
	if ( false !== strpos( $content, 'class="toc-box"' ) ) {
		return $content;
	}

	$skip    = technopay_toc_skip_ranges( $content );
	$items   = array();
	$counter = 0;
	$marker  = '<!--technopay-toc-->';
	$used    = array();

	$processed = preg_replace_callback(
		'/<h([23])\b([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$items, &$counter, &$used, $skip, $marker ) {
			$offset = $m[0][1];
			foreach ( $skip as $range ) {
				if ( $offset >= $range[0] && $offset < $range[1] ) {
					return $m[0][0];
				}
			}

			$level = (int) $m[1][0];
			$attrs = $m[2][0];

			$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $m[3][0] ), ENT_QUOTES, 'UTF-8' ) ) );
			if ( '' === $text ) {
				return $m[0][0];
			}

			++$counter;
			// شناسه موجود (غیرخالی و غیرتکراری) حفظ می‌شود؛ وگرنه شناسه جدید می‌سازیم.
			if ( preg_match( '/\sid=(["\'])([^"\']+)\1/i', $attrs, $id_match ) && ! isset( $used[ $id_match[2] ] ) ) {
				$id   = $id_match[2];
				$html = $m[0][0];
			} else {
				$id    = 'tp-toc-' . $counter;
				$attrs = preg_replace( '/\sid=(["\'])[^"\']*\1/i', '', $attrs );
				$html  = '<h' . $level . ' id="' . $id . '"' . $attrs . '>' . $m[3][0] . '</h' . $level . '>';
			}
			$used[ $id ] = true;

			$items[] = array(
				'level' => $level,
				'text'  => $text,
				'id'    => $id,
			);

			return ( 1 === $counter ? $marker : '' ) . $html;
		},
		$content,
		-1,
		$replaced,
		PREG_OFFSET_CAPTURE
	);

	if ( null === $processed || count( $items ) < (int) apply_filters( 'technopay_toc_min_headings', 3 ) ) {
		return $content;
	}

	return str_replace( $marker, technopay_build_toc( $items ), $processed );
}
add_filter( 'the_content', 'technopay_content_toc', 15 );
