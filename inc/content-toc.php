<?php
/**
 * فهرست مطالب خودکار داخل نوشته: از سرتیترهای H2/H3 ساخته و قبل از اولین سرتیتر درج می‌شود.
 *
 * - سرتیترهای داخل بلوک «جزئیات» (سوالات متداول آکاردئونی) یا دارای کلاس no-toc نادیده گرفته می‌شوند.
 * - اگر تعداد سرتیترها کمتر از ۳ باشد (فیلتر technopay_toc_min_headings) فهرستی نمایش داده نمی‌شود.
 * - برای هر نوشته با فیلد دلخواه technopay_hide_toc = 1 غیرفعال می‌شود.
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
	$out   = '<details class="toc-box" open>';
	$out  .= '<summary class="toc-box__head"><span class="toc-box__title">' . $title . '</span><span class="toc-box__toggle" aria-hidden="true"></span></summary>';
	$out  .= '<nav class="toc-box__body" aria-label="' . esc_attr__( 'فهرست مطالب', 'technopay' ) . '"><ol class="toc-box__list" role="list">';

	$num      = 0;
	$open_li  = false;
	$open_sub = false;

	foreach ( $items as $item ) {
		$link = '<a href="#' . esc_attr( $item['id'] ) . '">';

		if ( 3 === $item['level'] && $num > 0 ) {
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
 * درج فهرست مطالب در محتوای نوشته.
 *
 * @param string $content محتوا.
 * @return string
 */
function technopay_content_toc( $content ) {
	if ( is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = (int) get_the_ID();
	if ( ! technopay_option( 'show_toc' ) || get_post_meta( $post_id, 'technopay_hide_toc', true ) || ! apply_filters( 'technopay_toc_enabled', true, $post_id ) ) {
		return $content;
	}
	if ( false !== strpos( $content, 'toc-box' ) ) {
		return $content;
	}

	// محدوده بلوک‌های «جزئیات» که سرتیترهایشان وارد فهرست نمی‌شود.
	$skip = array();
	if ( preg_match_all( '/<details\b.*?<\/details>/is', $content, $details, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $details[0] as $detail ) {
			$skip[] = array( $detail[1], $detail[1] + strlen( $detail[0] ) );
		}
	}

	$items   = array();
	$counter = 0;
	$marker  = '<!--technopay-toc-->';

	$processed = preg_replace_callback(
		'/<h([23])\b([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$items, &$counter, $skip, $marker ) {
			$offset = $m[0][1];
			foreach ( $skip as $range ) {
				if ( $offset >= $range[0] && $offset < $range[1] ) {
					return $m[0][0];
				}
			}

			$level = (int) $m[1][0];
			$attrs = $m[2][0];
			if ( preg_match( '/\bclass=(["\'])[^"\']*\bno-toc\b[^"\']*\1/i', $attrs ) ) {
				return $m[0][0];
			}

			$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $m[3][0] ), ENT_QUOTES, 'UTF-8' ) ) );
			if ( '' === $text ) {
				return $m[0][0];
			}

			++$counter;
			if ( preg_match( '/\bid=(["\'])(.+?)\1/i', $attrs, $id_match ) ) {
				$id   = $id_match[2];
				$html = $m[0][0];
			} else {
				$id   = 'tp-toc-' . $counter;
				$html = '<h' . $level . ' id="' . $id . '"' . $attrs . '>' . $m[3][0] . '</h' . $level . '>';
			}

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
