<?php
/**
 * Plugin Name: TechnoPay Smart Related Posts
 * Plugin URI:  https://technopay.ir
 * Description: متن هر نوشته را یک بار می‌خواند و برای هر پاراگراف، مرتبط‌ترین نوشته‌های سایت را پیشنهاد می‌دهد؛ ویراستار تأیید می‌کند و باکس «بیشتر بخوانید» لابه‌لای پاراگراف‌ها (انتهای هر بخش) درج می‌شود.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author:      TechnoPay
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: technopay-related-posts
 * Domain Path: /languages
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

define( 'TPRP_VERSION', '1.0.0' );
define( 'TPRP_FILE', __FILE__ );
define( 'TPRP_DIR', plugin_dir_path( __FILE__ ) );
define( 'TPRP_URL', plugin_dir_url( __FILE__ ) );

require_once TPRP_DIR . 'includes/class-tprp-text.php';
require_once TPRP_DIR . 'includes/class-tprp-index.php';
require_once TPRP_DIR . 'includes/class-tprp-matcher.php';
require_once TPRP_DIR . 'includes/class-tprp-store.php';
require_once TPRP_DIR . 'includes/class-tprp-frontend.php';
require_once TPRP_DIR . 'includes/class-tprp-rest.php';
require_once TPRP_DIR . 'includes/class-tprp-admin.php';

/**
 * کلاس اصلی افزونه: تنظیمات و راه‌اندازی.
 */
final class TPRP_Plugin {

	const OPTION = 'tprp_settings';

	/**
	 * مقادیر پیش‌فرض تنظیمات.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'post_types'   => array( 'post' ),
			'min_words'    => 12,
			'min_score'    => 10,
			'candidates'   => 3,
			'style'        => 'card',
			'placement'    => 'section_end',
			'label'        => 'بیشتر بخوانید',
			'exclude_cats' => array(),
		);
	}

	/**
	 * تنظیمات (ترکیب پیش‌فرض‌ها و مقادیر ذخیره‌شده).
	 *
	 * @return array
	 */
	public static function settings() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	/**
	 * انواع نوشته فعال (فقط انواع عمومی و موجود).
	 *
	 * @return string[]
	 */
	public static function post_types() {
		$types = array_filter(
			(array) self::settings()['post_types'],
			function ( $type ) {
				return post_type_exists( $type ) && is_post_type_viewable( $type );
			}
		);
		return array_values( $types );
	}

	/**
	 * راه‌اندازی.
	 */
	public static function boot() {
		load_plugin_textdomain( 'technopay-related-posts', false, dirname( plugin_basename( TPRP_FILE ) ) . '/languages' );
		TPRP_Index::init();
		TPRP_Frontend::init();
		TPRP_Rest::init();
		if ( is_admin() ) {
			TPRP_Admin::init();
		}
	}
}
add_action( 'plugins_loaded', array( 'TPRP_Plugin', 'boot' ) );
