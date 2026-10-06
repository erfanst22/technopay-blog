<?php
/**
 * استخراج پاراگراف‌ها از HTML مقاله و پیدا کردن مرتبط‌ترین نوشته برای هر پاراگراف.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * تطبیق پاراگراف‌ها با نوشته‌ها (TF-IDF + شباهت کسینوسی).
 */
final class TPRP_Matcher {

	/**
	 * آیا این تگ یک کانتینر است که پاراگراف‌های داخلش نباید تحلیل/هدف درج شوند؟
	 *
	 * @param string $name  نام تگ.
	 * @param string $attrs ویژگی‌ها.
	 * @return bool
	 */
	private static function is_excluded_container( $name, $attrs ) {
		static $names = array(
			'details'    => 1,
			'blockquote' => 1,
			'figure'     => 1,
			'figcaption' => 1,
			'aside'      => 1,
			'nav'        => 1,
			'ul'         => 1,
			'ol'         => 1,
			'table'      => 1,
			'pre'        => 1,
			'form'       => 1,
			'button'     => 1,
			'script'     => 1,
			'style'      => 1,
			'header'     => 1,
			'footer'     => 1,
		);
		if ( isset( $names[ $name ] ) ) {
			return true;
		}
		if ( ( 'div' === $name || 'section' === $name ) && '' !== $attrs ) {
			return (bool) preg_match( '/class\s*=\s*["\'][^"\']*\b(callout|tp-cta|tprp-box|wp-block-quote|wp-block-pullquote|wp-block-table|wp-block-details|wp-block-embed|wp-block-buttons|wp-block-media-text)\b/i', $attrs );
		}
		return false;
	}

	/**
	 * استخراج پاراگراف‌های سطح بالا و موقعیت سرتیترهای H2 از HTML.
	 *
	 * @param string $html HTML نهایی محتوا.
	 * @return array{paragraphs:array<int,array>,h2:int[]}
	 */
	public static function extract_paragraphs( $html ) {
		$html       = (string) $html;
		$paragraphs = array();
		$h2         = array();

		if ( ! preg_match_all( '/<(\/?)([a-zA-Z][a-zA-Z0-9]*)\b([^>]*?)(\/?)>/', $html, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
			return array(
				'paragraphs' => array(),
				'h2'         => array(),
			);
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

		$stack   = array();
		$current = null;
		$seen    = array();

		foreach ( $tags as $tag ) {
			$closing = '/' === $tag[1][0];
			$name    = strtolower( $tag[2][0] );
			$attrs   = $tag[3][0];
			$start   = $tag[0][1];
			$end     = $start + strlen( $tag[0][0] );

			if ( isset( $void[ $name ] ) || '/' === $tag[4][0] ) {
				continue;
			}

			if ( ! $closing ) {
				$parent_excluded = $stack ? $stack[ count( $stack ) - 1 ][1] : false;
				$excluded        = $parent_excluded || self::is_excluded_container( $name, $attrs );
				$stack[]         = array( $name, $excluded );
				if ( 'h2' === $name && ! $excluded ) {
					$h2[] = $start;
				}
				if ( 'p' === $name && ! $excluded ) {
					$current = array(
						'start' => $start,
						'inner' => $end,
					);
				}
				continue;
			}

			// تگ بسته: تا اولین تگ هم‌نام از پشته برمی‌داریم.
			$found = false;
			for ( $i = count( $stack ) - 1; $i >= 0; $i-- ) {
				if ( $stack[ $i ][0] === $name ) {
					array_splice( $stack, $i );
					$found = true;
					break;
				}
			}
			if ( $found && 'p' === $name && $current ) {
				$inner = substr( $html, $current['inner'], $start - $current['inner'] );
				$text  = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $inner ), ENT_QUOTES, 'UTF-8' ) ) );
				if ( '' !== $text ) {
					$hash                = TPRP_Text::hash( $inner );
					$occurrence          = isset( $seen[ $hash ] ) ? $seen[ $hash ] : 0;
					$seen[ $hash ]       = $occurrence + 1;
					$paragraphs[]        = array(
						'index'      => count( $paragraphs ),
						'hash'       => $hash,
						'occurrence' => $occurrence,
						'start'      => $current['start'],
						'end'        => $end,
						'section'    => count( $h2 ),
						'text'       => $text,
						'words'      => TPRP_Text::word_count( $text ),
					);
				}
				$current = null;
			}
		}

		return array(
			'paragraphs' => $paragraphs,
			'h2'         => $h2,
		);
	}

	/**
	 * پیدا کردن مرتبط‌ترین نوشته‌ها برای هر پاراگراف مقاله.
	 *
	 * @param int    $post_id شناسه نوشته جاری.
	 * @param string $html    HTML نهایی محتوا.
	 * @param array  $args    min_words, min_score (درصد)، candidates، post_types، exclude_cats.
	 * @return array{paragraphs:array,corpus:int}
	 */
	public static function analyze( $post_id, $html, array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'min_words'    => 12,
				'min_score'    => 10,
				'candidates'   => 3,
				'post_types'   => array( 'post' ),
				'exclude_cats' => array(),
			)
		);

		$extracted = self::extract_paragraphs( $html );
		$corpus    = TPRP_Index::load_all( $args['post_types'], (int) $post_id, $args['exclude_cats'] );
		$total     = count( $corpus );

		// فهرست معکوس و بسامد سندی (df).
		$postings = array();
		foreach ( $corpus as $id => $row ) {
			foreach ( $row['terms'] as $term => $weight ) {
				$postings[ $term ][ $id ] = (float) $weight;
			}
		}
		$idf = array();
		foreach ( $postings as $term => $docs ) {
			$idf[ $term ] = log( 1 + $total / ( 1 + count( $docs ) ) );
		}
		// نُرم هر سند.
		$norms = array();
		foreach ( $corpus as $id => $row ) {
			$sum = 0.0;
			foreach ( $row['terms'] as $term => $weight ) {
				$w    = ( 1 + log( max( 1.0, (float) $weight ) ) ) * $idf[ $term ];
				$sum += $w * $w;
			}
			$norms[ $id ] = sqrt( $sum );
		}
		// بردارهای خام دیگر لازم نیستند؛ حافظه را آزاد کن (در سایت‌های بزرگ چند ده مگابایت است).
		unset( $corpus );

		$threshold = max( 0, (float) $args['min_score'] ) / 100;
		$result    = array();

		foreach ( $extracted['paragraphs'] as $para ) {
			$candidates = array();
			if ( $para['words'] >= (int) $args['min_words'] && $total ) {
				$candidates = self::match_paragraph( $para['text'], $postings, $idf, $norms, $threshold, (int) $args['candidates'] );
			}
			$result[] = array(
				'index'      => $para['index'],
				'hash'       => $para['hash'],
				'occurrence' => $para['occurrence'],
				'section'    => $para['section'],
				'words'      => $para['words'],
				'excerpt'    => TPRP_Text::excerpt( $para['text'], 150 ),
				'candidates' => $candidates,
			);
		}

		return array(
			'paragraphs' => self::mark_recommended( $result ),
			'corpus'     => $total,
		);
	}

	/**
	 * شباهت یک پاراگراف با همه نوشته‌ها.
	 *
	 * @param string                           $text      متن پاراگراف.
	 * @param array<string,array<int,float>>   $postings  فهرست معکوس.
	 * @param array<string,float>              $idf       IDF هر واژه.
	 * @param array<int,float>                 $norms     نُرم اسناد.
	 * @param float                            $threshold حداقل شباهت (۰ تا ۱).
	 * @param int                              $limit     حداکثر تعداد نتیجه.
	 * @return array[] هر مورد: id, score (درصد)، keywords.
	 */
	private static function match_paragraph( $text, array $postings, array $idf, array $norms, $threshold, $limit ) {
		$tf = TPRP_Text::add_frequencies( TPRP_Text::tokens( $text ), 1, 1 );
		if ( ! $tf ) {
			return array();
		}

		$query = array();
		$qnorm = 0.0;
		foreach ( $tf as $term => $count ) {
			if ( ! isset( $idf[ $term ] ) ) {
				// واژه‌ای که در هیچ نوشته‌ای نیست روی نُرم پرسش اثر ندارد (فقط شباهت نسبی مهم است).
				continue;
			}
			$w             = ( 1 + log( $count ) ) * $idf[ $term ];
			$query[ $term ] = $w;
			$qnorm         += $w * $w;
		}
		$qnorm = sqrt( $qnorm );
		if ( $qnorm <= 0 ) {
			return array();
		}

		$dots = array();
		foreach ( $query as $term => $qw ) {
			foreach ( $postings[ $term ] as $id => $weight ) {
				$dw           = ( 1 + log( max( 1.0, $weight ) ) ) * $idf[ $term ];
				$dots[ $id ]  = ( isset( $dots[ $id ] ) ? $dots[ $id ] : 0.0 ) + $qw * $dw;
			}
		}

		$scores = array();
		foreach ( $dots as $id => $dot ) {
			if ( empty( $norms[ $id ] ) ) {
				continue;
			}
			$score = $dot / ( $qnorm * $norms[ $id ] );
			if ( $score >= $threshold ) {
				$scores[ $id ] = $score;
			}
		}
		if ( ! $scores ) {
			return array();
		}
		arsort( $scores );
		$scores = array_slice( $scores, 0, max( 1, $limit ), true );

		$out = array();
		foreach ( $scores as $id => $score ) {
			// واژه‌های مشترک با بیشترین سهم.
			$shared = array();
			foreach ( $query as $term => $qw ) {
				if ( isset( $postings[ $term ][ $id ] ) ) {
					$shared[ $term ] = $qw * ( 1 + log( max( 1.0, $postings[ $term ][ $id ] ) ) ) * $idf[ $term ];
				}
			}
			arsort( $shared );
			$out[] = array(
				'id'       => (int) $id,
				'score'    => (int) round( min( 1, $score ) * 100 ),
				'keywords' => array_slice( array_keys( $shared ), 0, 4 ),
			);
		}
		return $out;
	}

	/**
	 * برای هر بخش (بین دو H2) بهترین پاراگراف را «پیشنهاد اصلی» علامت می‌زند؛
	 * هر نوشته در کل مقاله حداکثر یک بار پیشنهاد اصلی می‌شود.
	 *
	 * @param array[] $paragraphs پاراگراف‌ها با candidates.
	 * @return array[]
	 */
	private static function mark_recommended( array $paragraphs ) {
		$pairs = array();
		foreach ( $paragraphs as $i => $para ) {
			foreach ( $para['candidates'] as $j => $cand ) {
				$pairs[] = array( $cand['score'], $i, $j );
			}
		}
		usort(
			$pairs,
			function ( $a, $b ) {
				return $b[0] <=> $a[0] ?: $a[1] <=> $b[1];
			}
		);

		$section_done = array();
		$post_done    = array();
		$para_done    = array();
		foreach ( $paragraphs as $i => $para ) {
			$paragraphs[ $i ]['recommended'] = -1;
		}
		foreach ( $pairs as $pair ) {
			list( , $i, $j ) = $pair;
			$section         = $paragraphs[ $i ]['section'];
			$target          = $paragraphs[ $i ]['candidates'][ $j ]['id'];
			if ( isset( $section_done[ $section ] ) || isset( $post_done[ $target ] ) || isset( $para_done[ $i ] ) ) {
				continue;
			}
			$paragraphs[ $i ]['recommended'] = $j;
			$section_done[ $section ]        = true;
			$post_done[ $target ]            = true;
			$para_done[ $i ]                 = true;
		}
		return $paragraphs;
	}
}
