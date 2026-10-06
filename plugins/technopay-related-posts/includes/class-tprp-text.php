<?php
/**
 * پردازش متن فارسی: نرمال‌سازی، توکن‌سازی، ریشه‌یابی سبک و بسامد واژه‌ها.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * کلاس کمکی متن (همه متدها استاتیک و بدون وابستگی به دیتابیس‌اند).
 */
final class TPRP_Text {

	/**
	 * کلمات توقف.
	 *
	 * @var array|null
	 */
	private static $stop = null;

	/**
	 * متن (یا HTML) را نرمال می‌کند: حذف تگ‌ها، یکسان‌سازی ی/ک عربی، حذف اعراب و کشیده،
	 * ارقام لاتین، تبدیل نیم‌فاصله به فاصله و حروف کوچک.
	 *
	 * @param string $text متن یا HTML.
	 * @return string
	 */
	public static function normalize( $text ) {
		$text = (string) $text;
		// پایان بلوک‌ها فاصله بگذارند تا کلمه‌ها به هم نچسبند.
		$text = preg_replace( '/<(?:br|\/p|\/div|\/h[1-6]|\/li|\/tr|\/td|\/th|\/blockquote)\b[^>]*>/i', ' ', $text );
		$text = html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' );
		$text = strtr(
			$text,
			array(
				'ي' => 'ی',
				'ى' => 'ی',
				'ك' => 'ک',
				'ۀ' => 'ه',
				'ة' => 'ه',
				'أ' => 'ا',
				'إ' => 'ا',
				'ٱ' => 'ا',
				'٠' => '0',
				'١' => '1',
				'٢' => '2',
				'٣' => '3',
				'٤' => '4',
				'٥' => '5',
				'٦' => '6',
				'٧' => '7',
				'٨' => '8',
				'٩' => '9',
				'۰' => '0',
				'۱' => '1',
				'۲' => '2',
				'۳' => '3',
				'۴' => '4',
				'۵' => '5',
				'۶' => '6',
				'۷' => '7',
				'۸' => '8',
				'۹' => '9',
			)
		);
		// اعراب، کشیده و نشانه‌های جهت‌دهی.
		$text = preg_replace( '/[\x{064B}-\x{065F}\x{0670}\x{0640}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $text );
		// نیم‌فاصله (ZWNJ) و فاصله‌های نشکن.
		$text = str_replace( array( "\xE2\x80\x8C", "\xC2\xA0" ), ' ', $text );
		$text = self::lower( $text );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	/**
	 * حروف کوچک (با پشتیبانی از نبودن mbstring).
	 *
	 * @param string $text متن.
	 * @return string
	 */
	public static function lower( $text ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
	}

	/**
	 * هش پایدار یک پاراگراف (برای شناسایی آن در مقاله).
	 *
	 * @param string $text متن یا HTML پاراگراف.
	 * @return string
	 */
	public static function hash( $text ) {
		return md5( self::normalize( $text ) );
	}

	/**
	 * شمارش کلمات.
	 *
	 * @param string $text متن.
	 * @return int
	 */
	public static function word_count( $text ) {
		$norm = self::normalize( $text );
		if ( '' === $norm ) {
			return 0;
		}
		$parts = preg_split( '/[^\p{L}\p{N}]+/u', $norm, -1, PREG_SPLIT_NO_EMPTY );
		return is_array( $parts ) ? count( $parts ) : 0;
	}

	/**
	 * ریشه‌یابی سبک فارسی (حذف جمع و صفت تفضیلی و «ی» پایانی). برای کلمات غیرفارسی دست نمی‌زند.
	 *
	 * @param string $word کلمه (نرمال‌شده).
	 * @return string
	 */
	public static function stem( $word ) {
		if ( ! preg_match( '/\p{Arabic}/u', $word ) ) {
			return $word;
		}
		$len = mb_strlen( $word, 'UTF-8' );
		foreach ( array( 'ترین', 'های', 'ها', 'تر', 'ات', 'ان', 'ین' ) as $suffix ) {
			$sl = mb_strlen( $suffix, 'UTF-8' );
			if ( $len - $sl >= 3 && mb_substr( $word, -$sl, null, 'UTF-8' ) === $suffix ) {
				$word = mb_substr( $word, 0, $len - $sl, 'UTF-8' );
				$len -= $sl;
				break;
			}
		}
		// «طلایی» → «طلا» (پسوند یی بعد از مصوت)، «بانکی» → «بانک».
		if ( $len > 4 && 'یی' === mb_substr( $word, -2, null, 'UTF-8' ) ) {
			return mb_substr( $word, 0, $len - 2, 'UTF-8' );
		}
		if ( $len > 3 && 'ی' === mb_substr( $word, -1, null, 'UTF-8' ) ) {
			$word = mb_substr( $word, 0, $len - 1, 'UTF-8' );
		}
		return $word;
	}

	/**
	 * کلمات توقف.
	 *
	 * @return array
	 */
	public static function stopwords() {
		if ( null === self::$stop ) {
			$stop       = include TPRP_DIR . 'includes/stopwords-fa.php';
			self::$stop = apply_filters( 'tprp_stopwords', is_array( $stop ) ? $stop : array() );
		}
		return self::$stop;
	}

	/**
	 * توکن‌ها (ریشه‌یابی‌شده، بدون کلمات توقف و اعداد کوتاه).
	 *
	 * @param string $text متن یا HTML.
	 * @return string[]
	 */
	public static function tokens( $text ) {
		$norm = self::normalize( $text );
		if ( '' === $norm ) {
			return array();
		}
		$parts = preg_split( '/[^\p{L}\p{N}]+/u', $norm, -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $parts ) ) {
			return array();
		}
		$stop = self::stopwords();
		$out  = array();
		foreach ( $parts as $word ) {
			if ( isset( $stop[ $word ] ) ) {
				continue;
			}
			if ( ctype_digit( $word ) && strlen( $word ) < 4 ) {
				continue;
			}
			$word = self::stem( $word );
			if ( mb_strlen( $word, 'UTF-8' ) < 2 || isset( $stop[ $word ] ) ) {
				continue;
			}
			$out[] = $word;
		}
		return $out;
	}

	/**
	 * بسامد واژه‌ها (تک‌واژه و دوواژه‌های مجاور).
	 *
	 * @param string[] $tokens     توکن‌ها.
	 * @param float    $weight     وزن تک‌واژه‌ها.
	 * @param float    $bigram_w   وزن دوواژه‌ها (۰ = بدون دوواژه).
	 * @param array    $into       آرایه‌ای که نتیجه به آن اضافه می‌شود.
	 * @return array<string,float>
	 */
	public static function add_frequencies( array $tokens, $weight, $bigram_w, array $into = array() ) {
		$prev = null;
		foreach ( $tokens as $token ) {
			$into[ $token ] = ( isset( $into[ $token ] ) ? $into[ $token ] : 0 ) + $weight;
			if ( $bigram_w > 0 && null !== $prev ) {
				$bigram          = $prev . ' ' . $token;
				$into[ $bigram ] = ( isset( $into[ $bigram ] ) ? $into[ $bigram ] : 0 ) + $bigram_w;
			}
			$prev = $token;
		}
		return $into;
	}

	/**
	 * خلاصه‌ای کوتاه از متن برای نمایش.
	 *
	 * @param string $text   متن یا HTML.
	 * @param int    $length حداکثر تعداد حروف.
	 * @return string
	 */
	public static function excerpt( $text, $length = 140 ) {
		$plain = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' ) ) );
		if ( mb_strlen( $plain, 'UTF-8' ) <= $length ) {
			return $plain;
		}
		return rtrim( mb_substr( $plain, 0, $length, 'UTF-8' ) ) . '…';
	}
}
