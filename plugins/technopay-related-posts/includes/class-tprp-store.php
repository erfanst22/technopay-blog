<?php
/**
 * ذخیره‌سازی پیشنهادهای تأییدشده هر نوشته (در متای نوشته).
 *
 * محتوای نوشته هرگز تغییر نمی‌کند؛ باکس‌ها هنگام نمایش و بر اساس همین فهرست در مقاله درج می‌شوند،
 * پس حذف یا ویرایش آن‌ها بدون دست‌زدن به متن مقاله ممکن است.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * فروشگاه تأییدها.
 */
final class TPRP_Store {

	const META_ITEMS    = '_tprp_items';
	const META_DISABLED = '_tprp_disabled';

	/**
	 * محل‌های مجاز درج.
	 *
	 * @return string[]
	 */
	public static function placements() {
		return array( 'section_end', 'after_paragraph' );
	}

	/**
	 * آیتم‌های تأییدشده.
	 * هر آیتم: hash (شناسه پاراگراف)، occurrence، target (شناسه نوشته مقصد)، placement، excerpt.
	 *
	 * @param int $post_id شناسه نوشته.
	 * @return array[]
	 */
	public static function get_items( $post_id ) {
		$raw   = get_post_meta( $post_id, self::META_ITEMS, true );
		$items = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
		if ( ! is_array( $items ) ) {
			return array();
		}
		$clean = array();
		foreach ( $items as $item ) {
			$item = self::sanitize_item( $item );
			if ( $item ) {
				$clean[] = $item;
			}
		}
		return $clean;
	}

	/**
	 * پاکسازی یک آیتم؛ در صورت نامعتبر بودن false.
	 *
	 * @param mixed $item آیتم.
	 * @return array|false
	 */
	public static function sanitize_item( $item ) {
		if ( ! is_array( $item ) || empty( $item['hash'] ) || empty( $item['target'] ) ) {
			return false;
		}
		$hash = strtolower( (string) $item['hash'] );
		if ( ! preg_match( '/^[a-f0-9]{32}$/', $hash ) ) {
			return false;
		}
		$placement = isset( $item['placement'] ) ? (string) $item['placement'] : 'section_end';
		if ( ! in_array( $placement, self::placements(), true ) ) {
			$placement = 'section_end';
		}
		return array(
			'hash'       => $hash,
			'occurrence' => isset( $item['occurrence'] ) ? absint( $item['occurrence'] ) : 0,
			'target'     => absint( $item['target'] ),
			'placement'  => $placement,
			'excerpt'    => isset( $item['excerpt'] ) ? mb_substr( sanitize_text_field( (string) $item['excerpt'] ), 0, 160, 'UTF-8' ) : '',
		);
	}

	/**
	 * ذخیره آیتم‌های تأییدشده (جایگزین فهرست قبلی).
	 *
	 * @param int     $post_id شناسه نوشته.
	 * @param array[] $items   آیتم‌ها.
	 * @return int تعداد ذخیره‌شده.
	 */
	public static function save_items( $post_id, array $items ) {
		$clean = array();
		$used  = array();
		foreach ( $items as $item ) {
			$item = self::sanitize_item( $item );
			if ( ! $item || ! self::is_valid_target( $item['target'], $post_id ) ) {
				continue;
			}
			$key = $item['hash'] . ':' . $item['occurrence'];
			if ( isset( $used[ $key ] ) ) {
				continue; // هر پاراگراف حداکثر یک باکس.
			}
			$used[ $key ] = true;
			$clean[]      = $item;
		}
		if ( $clean ) {
			update_post_meta( $post_id, self::META_ITEMS, wp_slash( wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) ) );
		} else {
			delete_post_meta( $post_id, self::META_ITEMS );
		}
		return count( $clean );
	}

	/**
	 * آیا نوشته مقصد معتبر است؟ (منتشرشده، بدون رمز، عمومی و غیر از خود نوشته)
	 *
	 * @param int $target  شناسه مقصد.
	 * @param int $post_id شناسه نوشته جاری.
	 * @return bool
	 */
	public static function is_valid_target( $target, $post_id ) {
		$target = (int) $target;
		if ( ! $target || $target === (int) $post_id ) {
			return false;
		}
		$post = get_post( $target );
		return $post instanceof WP_Post && 'publish' === $post->post_status && '' === $post->post_password && is_post_type_viewable( $post->post_type );
	}

	/**
	 * آیا نمایش باکس‌ها برای این نوشته خاموش شده است؟
	 *
	 * @param int $post_id شناسه.
	 * @return bool
	 */
	public static function is_disabled( $post_id ) {
		return (bool) get_post_meta( $post_id, self::META_DISABLED, true );
	}

	/**
	 * خاموش/روشن‌کردن نمایش برای یک نوشته.
	 *
	 * @param int  $post_id  شناسه.
	 * @param bool $disabled خاموش؟
	 */
	public static function set_disabled( $post_id, $disabled ) {
		if ( $disabled ) {
			update_post_meta( $post_id, self::META_DISABLED, 1 );
		} else {
			delete_post_meta( $post_id, self::META_DISABLED );
		}
	}
}
