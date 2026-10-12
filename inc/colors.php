<?php
/**
 * رنگ برند: از یک رنگ اصلی، همهٔ رنگ‌های لازم قالب (هاور، متن روی رنگ، تیره، حالت تاریک و ...) را
 * با رعایت کنتراست خوانایی (WCAG AA) می‌سازد.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * رنگ برند پیش‌فرض (فیروزه‌ای تکنوپی).
 *
 * @return string
 */
function technopay_default_brand() {
	return '#00b7ce';
}

/**
 * پاک‌سازی رنگ برند: هگز معتبر به‌صورت #rrggbb، وگرنه پیش‌فرض.
 *
 * @param mixed $value مقدار ورودی.
 * @return string
 */
function technopay_sanitize_brand_color( $value ) {
	$hex = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : null;
	if ( ! $hex ) {
		return technopay_default_brand();
	}
	return technopay_rgb_to_hex( technopay_hex_to_rgb( $hex ) );
}

/**
 * رنگ متن روی دکمه‌ها و نشان‌های رنگ برند: white (سفید، پیش‌فرض) یا auto (سفید/سرمه‌ای، هر کدام که خواناتر است).
 *
 * @return string
 */
function technopay_button_text_mode() {
	return 'auto' === technopay_option( 'button_text' ) ? 'auto' : 'white';
}

/**
 * رنگ برند فعلی.
 *
 * @return string
 */
function technopay_brand_color() {
	return technopay_sanitize_brand_color( technopay_option( 'brand_color' ) );
}

/**
 * هگز (#rgb یا #rrggbb) به آرایهٔ [r, g, b].
 *
 * @param string $hex رنگ.
 * @return int[]
 */
function technopay_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/**
 * آرایهٔ [r, g, b] به هگز.
 *
 * @param int[] $rgb رنگ.
 * @return string
 */
function technopay_rgb_to_hex( array $rgb ) {
	return sprintf( '#%02x%02x%02x', max( 0, min( 255, (int) round( $rgb[0] ) ) ), max( 0, min( 255, (int) round( $rgb[1] ) ) ), max( 0, min( 255, (int) round( $rgb[2] ) ) ) );
}

/**
 * [r, g, b] به [h (0-360), s (0-1), l (0-1)].
 *
 * @param int[] $rgb رنگ.
 * @return float[]
 */
function technopay_rgb_to_hsl( array $rgb ) {
	$r   = $rgb[0] / 255;
	$g   = $rgb[1] / 255;
	$b   = $rgb[2] / 255;
	$max = max( $r, $g, $b );
	$min = min( $r, $g, $b );
	$l   = ( $max + $min ) / 2;
	$d   = $max - $min;
	if ( abs( $d ) < 1e-9 ) {
		return array( 0.0, 0.0, $l );
	}
	$den = 1 - abs( 2 * $l - 1 );
	$s   = $den > 1e-9 ? $d / $den : 0.0;
	if ( $max === $r ) {
		$h = fmod( ( $g - $b ) / $d, 6 );
	} elseif ( $max === $g ) {
		$h = ( $b - $r ) / $d + 2;
	} else {
		$h = ( $r - $g ) / $d + 4;
	}
	$h *= 60;
	return array( $h < 0 ? $h + 360 : $h, $s, $l );
}

/**
 * [h, s, l] به [r, g, b].
 *
 * @param float[] $hsl رنگ.
 * @return float[]
 */
function technopay_hsl_to_rgb( array $hsl ) {
	list( $h, $s, $l ) = $hsl;
	$c                 = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x                 = $c * ( 1 - abs( fmod( $h / 60, 2 ) - 1 ) );
	$m                 = $l - $c / 2;
	if ( $h < 60 ) {
		$rgb = array( $c, $x, 0 );
	} elseif ( $h < 120 ) {
		$rgb = array( $x, $c, 0 );
	} elseif ( $h < 180 ) {
		$rgb = array( 0, $c, $x );
	} elseif ( $h < 240 ) {
		$rgb = array( 0, $x, $c );
	} elseif ( $h < 300 ) {
		$rgb = array( $x, 0, $c );
	} else {
		$rgb = array( $c, 0, $x );
	}
	return array( ( $rgb[0] + $m ) * 255, ( $rgb[1] + $m ) * 255, ( $rgb[2] + $m ) * 255 );
}

/**
 * روشنایی نسبی (WCAG).
 *
 * @param int[] $rgb رنگ.
 * @return float
 */
function technopay_luminance( array $rgb ) {
	$lin = array();
	foreach ( $rgb as $v ) {
		$v     = $v / 255;
		$lin[] = $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
}

/**
 * نسبت کنتراست دو رنگ (۱ تا ۲۱).
 *
 * @param int[] $a رنگ اول.
 * @param int[] $b رنگ دوم.
 * @return float
 */
function technopay_contrast( array $a, array $b ) {
	$l1 = technopay_luminance( $a );
	$l2 = technopay_luminance( $b );
	return ( max( $l1, $l2 ) + 0.05 ) / ( min( $l1, $l2 ) + 0.05 );
}

/**
 * ترکیب دو رنگ؛ $t سهم رنگ اول است (۰ تا ۱).
 *
 * @param int[] $a رنگ اول.
 * @param int[] $b رنگ دوم.
 * @param float $t سهم رنگ اول.
 * @return float[]
 */
function technopay_mix( array $a, array $b, $t ) {
	return array(
		$a[0] * $t + $b[0] * ( 1 - $t ),
		$a[1] * $t + $b[1] * ( 1 - $t ),
		$a[2] * $t + $b[2] * ( 1 - $t ),
	);
}

/**
 * تغییر روشنایی (HSL) تا وقتی همهٔ پس‌زمینه‌ها حداقل کنتراست لازم را داشته باشند.
 *
 * @param int[]   $rgb       رنگ شروع.
 * @param int[][] $on        پس‌زمینه‌ها.
 * @param float   $min_ratio حداقل کنتراست.
 * @param int     $dir       -1 تیره‌تر، +1 روشن‌تر.
 * @return float[]
 */
function technopay_shift_until( array $rgb, array $on, $min_ratio, $dir ) {
	$hsl = technopay_rgb_to_hsl( $rgb );
	for ( $i = 0; $i <= 200; $i++ ) {
		$cur = technopay_hsl_to_rgb( $hsl );
		$ok  = true;
		foreach ( $on as $bg ) {
			if ( technopay_contrast( $cur, $bg ) < $min_ratio ) {
				$ok = false;
				break;
			}
		}
		if ( $ok || ( $dir < 0 && $hsl[2] <= 0.02 ) || ( $dir > 0 && $hsl[2] >= 0.98 ) ) {
			return $cur;
		}
		$hsl[2] = max( 0, min( 1, $hsl[2] + $dir * 0.005 ) );
	}
	return technopay_hsl_to_rgb( $hsl );
}

/**
 * رنگ‌های ساخته‌شده از رنگ برند برای حالت روشن و تاریک.
 *
 * @param string $hex       رنگ برند.
 * @param string $text_mode white = متن روی رنگ برند همیشه سفید؛ auto = سفید یا سرمه‌ای (خواناتر).
 * @return array{light:array,dark:array}
 */
function technopay_brand_tokens( $hex, $text_mode = 'white' ) {
	$brand = technopay_hex_to_rgb( technopay_sanitize_brand_color( $hex ) );
	$white = array( 255, 255, 255 );
	$ink   = array( 15, 23, 42 ); // --ink
	$surf  = array( 17, 26, 46 ); // --surface (تاریک)
	$surf2 = array( 15, 23, 41 ); // --surface-2 (تاریک)

	$on_color = static function ( array $bg ) use ( $white, $ink, $text_mode ) {
		if ( 'auto' !== $text_mode ) {
			return $white;
		}
		return technopay_contrast( $white, $bg ) >= technopay_contrast( $ink, $bg ) ? $white : $ink;
	};
	$shift_l  = static function ( array $rgb, $delta ) {
		$h = technopay_rgb_to_hsl( $rgb );
		return technopay_hsl_to_rgb( array( $h[0], $h[1], max( 0.05, min( 0.95, $h[2] + $delta ) ) ) );
	};

	// حالت روشن.
	$p50   = technopay_mix( $brand, $white, 0.10 );
	$p100  = technopay_mix( $brand, $white, 0.20 );
	$ink_l = technopay_shift_until( $brand, array( $white, $p50, $p100 ), 4.6, -1 );
	$deep  = technopay_shift_until( $brand, array( $white ), 6.5, -1 );
	$light = array(
		'primary'       => $brand,
		'primary-600'   => $shift_l( $brand, -0.07 ),
		'primary-50'    => $p50,
		'primary-100'   => $p100,
		'primary-ink'   => $ink_l,
		'primary-deep'  => $deep,
		'primary-deeper' => $shift_l( $deep, -0.11 ),
		'on-primary'    => $on_color( $brand ),
		'primary-solid' => $brand,
	);

	// حالت تاریک: رنگ برند باید روی زمینهٔ تیره دیده شود.
	$brand_d = technopay_contrast( $brand, $surf ) >= 3.0 ? $brand : technopay_shift_until( $brand, array( $surf ), 3.0, 1 );
	$p50_d   = technopay_mix( $brand_d, $surf2, 0.16 );
	$p100_d  = technopay_mix( $brand_d, $surf2, 0.28 );
	$dark    = array(
		'primary'       => $brand_d,
		'primary-600'   => $shift_l( $brand_d, 0.06 ),
		'primary-50'    => $p50_d,
		'primary-100'   => $p100_d,
		'primary-ink'   => technopay_shift_until( $brand_d, array( $surf, $surf2, $p50_d, $p100_d ), 4.6, 1 ),
		'primary-deep'  => $deep,
		'primary-deeper' => $light['primary-deeper'],
		'on-primary'    => $on_color( $brand_d ),
		'primary-solid' => $brand_d,
	);

	$light = array_map( 'technopay_rgb_to_hex', $light );
	$dark  = array_map( 'technopay_rgb_to_hex', $dark );

	// سایهٔ ظریف زیر متن سفید روی رنگ برند برای خوانایی بیشتر؛ برای متن تیره لازم نیست.
	$shadow = '0 1px 1px rgba(0,0,0,.22)';
	$light['on-primary-shadow'] = '#ffffff' === $light['on-primary'] ? $shadow : 'none';
	$dark['on-primary-shadow']  = '#ffffff' === $dark['on-primary'] ? $shadow : 'none';

	return array(
		'light' => $light,
		'dark'  => $dark,
	);
}

/**
 * بلوک متغیرهای CSS برای یک حالت.
 *
 * @param array $tokens نام → هگز.
 * @return string
 */
function technopay_brand_vars( array $tokens ) {
	$out = '';
	foreach ( $tokens as $name => $hex ) {
		$out .= '--' . $name . ':' . $hex . ';';
	}
	return $out;
}

/**
 * CSS رنگ برند؛ اگر رنگ و حالت متن همان پیش‌فرض باشند خالی است (مقدارهای main.css از همان ساخته شده‌اند).
 *
 * @return string
 */
function technopay_brand_css() {
	$brand = technopay_brand_color();
	$mode  = technopay_button_text_mode();
	if ( technopay_default_brand() === $brand && 'white' === $mode ) {
		return '';
	}
	$t = technopay_brand_tokens( $brand, $mode );
	return ':root{' . technopay_brand_vars( $t['light'] ) . '}'
		. ':root[data-theme="dark"]{' . technopay_brand_vars( $t['dark'] ) . '}'
		. '@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){' . technopay_brand_vars( $t['dark'] ) . '}}';
}
