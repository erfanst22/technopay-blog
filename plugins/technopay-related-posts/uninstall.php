<?php
/**
 * حذف داده‌های افزونه هنگام پاک‌کردن آن.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'tprp_settings' );

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_tprp_vec_1', '_tprp_items', '_tprp_disabled')" );
