<?php
/**
 * نمایش باکس‌های تأییدشده لابه‌لای مقاله.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * درج باکس «بیشتر بخوانید» در محتوای نوشته.
 */
final class TPRP_Frontend {

	/**
	 * اگر true باشد فیلتر محتوا کاری نمی‌کند (هنگام تحلیل در ادمین).
	 *
	 * @var bool
	 */
	public static $bypass = false;

	/**
	 * اتصال هوک‌ها.
	 */
	public static function init() {
		// بعد از فهرست مطالب قالب (اولویت ۱۵) و شورت‌کدها اجرا می‌شود.
		add_filter( 'the_content', array( __CLASS__, 'filter_content' ), 25 );
	}

	/**
	 * فیلتر محتوا.
	 *
	 * @param string $content محتوای نهایی.
	 * @return string
	 */
	public static function filter_content( $content ) {
		if ( self::$bypass || is_feed() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post = get_post();
		if ( ! $post || ! in_array( $post->post_type, TPRP_Plugin::post_types(), true ) || TPRP_Store::is_disabled( $post->ID ) ) {
			return $content;
		}
		$items = TPRP_Store::get_items( $post->ID );
		if ( ! $items ) {
			return $content;
		}
		return self::insert( $content, $items, $post );
	}

	/**
	 * درج باکس‌ها در HTML.
	 *
	 * @param string  $html  HTML محتوا.
	 * @param array[] $items آیتم‌های تأییدشده.
	 * @param WP_Post $post  نوشته جاری.
	 * @return string
	 */
	public static function insert( $html, array $items, $post ) {
		$extracted = TPRP_Matcher::extract_paragraphs( $html );
		if ( ! $extracted['paragraphs'] ) {
			return $html;
		}

		$by_key = array();
		foreach ( $extracted['paragraphs'] as $para ) {
			$by_key[ $para['hash'] . ':' . $para['occurrence'] ] = $para;
		}

		$settings   = TPRP_Plugin::settings();
		$insertions = array();
		foreach ( $items as $order => $item ) {
			$key = $item['hash'] . ':' . $item['occurrence'];
			if ( ! isset( $by_key[ $key ] ) || ! TPRP_Store::is_valid_target( $item['target'], $post->ID ) ) {
				continue; // پاراگراف تغییر کرده یا مقصد دیگر معتبر نیست.
			}
			$para = $by_key[ $key ];
			$pos  = $para['end'];

			if ( 'section_end' === $item['placement'] ) {
				$pos = self::section_end( $html, $para, $extracted['h2'] );
			}

			$box = self::box_html( get_post( $item['target'] ), $settings );
			if ( '' === $box ) {
				continue;
			}
			$insertions[] = array( $pos, $order, $box );
		}
		if ( ! $insertions ) {
			return $html;
		}

		usort(
			$insertions,
			function ( $a, $b ) {
				return $a[0] <=> $b[0] ?: $a[1] <=> $b[1];
			}
		);

		$out  = '';
		$last = 0;
		foreach ( $insertions as $ins ) {
			$out .= substr( $html, $last, $ins[0] - $last ) . "\n" . $ins[2] . "\n";
			$last = $ins[0];
		}
		$out .= substr( $html, $last );

		if ( ! wp_style_is( 'tprp-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 'tprp-frontend', TPRP_URL . 'assets/frontend.css', array(), TPRP_VERSION );
		}
		return $out;
	}

	/**
	 * محل انتهای بخشی که پاراگراف در آن است (قبل از H2 بعدی، یا انتهای مقاله).
	 * اگر فهرست مطالب قالب درست قبل از آن H2 باشد، باکس قبل از فهرست قرار می‌گیرد.
	 *
	 * @param string $html HTML.
	 * @param array  $para پاراگراف.
	 * @param int[]  $h2   موقعیت H2ها.
	 * @return int
	 */
	private static function section_end( $html, array $para, array $h2 ) {
		$pos = strlen( $html );
		foreach ( $h2 as $offset ) {
			if ( $offset >= $para['end'] ) {
				$pos = $offset;
				break;
			}
		}
		// فهرست مطالب قالب (details.toc-box) بین پاراگراف و H2.
		$between = substr( $html, $para['end'], $pos - $para['end'] );
		$toc_at  = strpos( $between, '<details class="toc-box"' );
		if ( false !== $toc_at ) {
			$pos = $para['end'] + $toc_at;
		}
		return $pos;
	}

	/**
	 * HTML یک باکس.
	 *
	 * @param WP_Post|null $target نوشته مقصد.
	 * @param array        $settings تنظیمات افزونه.
	 * @return string
	 */
	public static function box_html( $target, array $settings ) {
		if ( ! $target instanceof WP_Post ) {
			return '';
		}
		$url   = get_permalink( $target );
		$title = get_the_title( $target );
		$label = isset( $settings['label'] ) && '' !== $settings['label'] ? $settings['label'] : __( 'بیشتر بخوانید', 'technopay-related-posts' );
		$style = isset( $settings['style'] ) && 'text' === $settings['style'] ? 'text' : 'card';

		if ( 'text' === $style ) {
			$html = sprintf(
				'<aside class="tprp-box tprp-box--text" aria-label="%1$s"><span class="tprp-box__label">%2$s</span> <a class="tprp-box__title" href="%3$s">%4$s</a></aside>',
				esc_attr( $label ),
				esc_html( $label ),
				esc_url( $url ),
				esc_html( $title )
			);
		} else {
			$thumb = get_the_post_thumbnail(
				$target,
				'thumbnail',
				array(
					'alt'      => '',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			/**
			 * تصویر بندانگشتی باکس (قالب می‌تواند برای نوشته‌های بدون تصویر شاخص تصویر پیش‌فرض بدهد).
			 *
			 * @param string  $thumb  HTML تصویر.
			 * @param WP_Post $target نوشته مقصد.
			 */
			$thumb   = (string) apply_filters( 'tprp_thumbnail_html', $thumb, $target );
			$excerpt = wp_trim_words( has_excerpt( $target ) ? $target->post_excerpt : wp_strip_all_tags( strip_shortcodes( $target->post_content ) ), 18, '…' );
			$html    = sprintf(
				'<aside class="tprp-box tprp-box--card%1$s" aria-label="%2$s">%3$s<div class="tprp-box__body"><span class="tprp-box__label">%4$s</span><a class="tprp-box__title" href="%5$s">%6$s</a>%7$s</div></aside>',
				'' === $thumb ? ' tprp-box--no-thumb' : '',
				esc_attr( $label ),
				'' === $thumb ? '' : '<a class="tprp-box__thumb" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . $thumb . '</a>',
				esc_html( $label ),
				esc_url( $url ),
				esc_html( $title ),
				'' === $excerpt ? '' : '<span class="tprp-box__excerpt">' . esc_html( $excerpt ) . '</span>'
			);
		}

		/**
		 * HTML نهایی باکس.
		 *
		 * @param string  $html   HTML.
		 * @param WP_Post $target نوشته مقصد.
		 * @param array   $settings تنظیمات.
		 */
		return (string) apply_filters( 'tprp_box_html', $html, $target, $settings );
	}
}
