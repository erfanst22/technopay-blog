<?php
/**
 * ایندکس معنایی نوشته‌ها: برای هر نوشته یک بردار وزنی از واژه‌ها در متای نوشته ذخیره می‌شود.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * ساخت، به‌روزرسانی و بارگذاری بردار نوشته‌ها.
 */
final class TPRP_Index {

	/**
	 * کلید متا (شماره نسخه الگوریتم در نام است؛ با تغییر الگوریتم، ایندکس خودکار از نو ساخته می‌شود).
	 */
	const META = '_tprp_vec_1';

	/**
	 * حداکثر تعداد واژه در بردار هر نوشته.
	 */
	const MAX_TERMS = 100;

	/**
	 * اتصال هوک‌ها.
	 */
	public static function init() {
		add_action( 'wp_after_insert_post', array( __CLASS__, 'on_save' ), 20, 2 );
		add_action( 'before_delete_post', array( __CLASS__, 'delete' ) );
	}

	/**
	 * پس از ذخیره نوشته، بردار آن را به‌روز می‌کند.
	 *
	 * @param int     $post_id شناسه.
	 * @param WP_Post $post    نوشته.
	 */
	public static function on_save( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! $post instanceof WP_Post ) {
			return;
		}
		if ( ! in_array( $post->post_type, TPRP_Plugin::post_types(), true ) ) {
			return;
		}
		if ( 'publish' === $post->post_status ) {
			self::update( $post );
		} else {
			self::delete( $post_id );
		}
	}

	/**
	 * حذف بردار نوشته.
	 *
	 * @param int $post_id شناسه.
	 */
	public static function delete( $post_id ) {
		delete_post_meta( $post_id, self::META );
	}

	/**
	 * محتوای نوشته را بدون کامنت‌های بلوک و شورت‌کد تمیز می‌کند.
	 *
	 * @param string $content محتوا.
	 * @return string
	 */
	public static function clean_content( $content ) {
		$content = preg_replace( '/<!--.*?-->/s', ' ', (string) $content );
		$content = strip_shortcodes( $content );
		return $content;
	}

	/**
	 * ساخت بردار وزنی نوشته: عنوان ×۵، دسته/برچسب ×۳، سرتیترها ×۲٫۵، چکیده ×۲، متن ×۱ (+ دوواژه‌ها).
	 *
	 * @param WP_Post|int $post نوشته.
	 * @return array<string,float>
	 */
	public static function build_vector( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return array();
		}

		$weights = array();
		$weights = TPRP_Text::add_frequencies( TPRP_Text::tokens( get_the_title( $post ) ), 5, 3, $weights );

		$names = array();
		foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $taxonomy ) {
			if ( empty( $taxonomy->public ) || 'post_format' === $taxonomy->name ) {
				continue;
			}
			$terms = get_the_terms( $post, $taxonomy->name );
			if ( is_array( $terms ) ) {
				$names = array_merge( $names, wp_list_pluck( $terms, 'name' ) );
			}
		}
		if ( $names ) {
			$weights = TPRP_Text::add_frequencies( TPRP_Text::tokens( implode( ' ', $names ) ), 3, 0, $weights );
		}

		$content = self::clean_content( $post->post_content );
		if ( preg_match_all( '/<h[2-4][^>]*>(.*?)<\/h[2-4]>/is', $content, $headings ) ) {
			$weights = TPRP_Text::add_frequencies( TPRP_Text::tokens( implode( ' ', $headings[1] ) ), 2.5, 1.5, $weights );
		}
		if ( '' !== trim( $post->post_excerpt ) ) {
			$weights = TPRP_Text::add_frequencies( TPRP_Text::tokens( $post->post_excerpt ), 2, 1, $weights );
		}
		$body    = mb_substr( TPRP_Text::normalize( $content ), 0, 8000, 'UTF-8' );
		$weights = TPRP_Text::add_frequencies( TPRP_Text::tokens( $body ), 1, 0.6, $weights );

		arsort( $weights );
		$weights = array_slice( $weights, 0, self::MAX_TERMS, true );
		foreach ( $weights as $term => $weight ) {
			$weights[ $term ] = round( $weight, 2 );
		}
		return $weights;
	}

	/**
	 * ساخت و ذخیره بردار نوشته.
	 *
	 * @param WP_Post|int $post نوشته.
	 * @return array<string,float>
	 */
	public static function update( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return array();
		}
		$vector = self::build_vector( $post );
		update_post_meta( $post->ID, self::META, wp_slash( wp_json_encode( $vector, JSON_UNESCAPED_UNICODE ) ) );
		return $vector;
	}

	/**
	 * شناسه نوشته‌های منتشرشده‌ای که هنوز ایندکس نشده‌اند.
	 *
	 * @param string[] $post_types انواع نوشته.
	 * @param int      $limit      حداکثر تعداد.
	 * @return int[]
	 */
	private static function missing_ids( array $post_types, $limit ) {
		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$sql          = "SELECT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
			WHERE p.post_status = 'publish' AND p.post_password = '' AND p.post_type IN ($placeholders) AND pm.meta_id IS NULL
			ORDER BY p.post_date DESC LIMIT %d";
		$args         = array_merge( array( self::META ), $post_types, array( (int) $limit ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( $sql, $args ) ) );
	}

	/**
	 * ایندکس‌کردن نوشته‌های ایندکس‌نشده (تنبل؛ در هر تحلیل تا سقف مشخص).
	 *
	 * @param string[] $post_types انواع نوشته.
	 * @param int      $limit      حداکثر تعداد در هر بار.
	 * @return int تعداد ایندکس‌شده.
	 */
	public static function index_missing( array $post_types, $limit = 400 ) {
		if ( ! $post_types ) {
			return 0;
		}
		$ids = self::missing_ids( $post_types, $limit );
		if ( $ids ) {
			// آماده‌سازی کش ترم‌ها با یک کوئری.
			_prime_post_caches( $ids, true, false );
		}
		foreach ( $ids as $id ) {
			self::update( $id );
		}
		return count( $ids );
	}

	/**
	 * بارگذاری بردار همه نوشته‌های منتشرشده (به جز یک نوشته و دسته‌های مستثنا).
	 *
	 * @param string[] $post_types   انواع نوشته.
	 * @param int      $exclude_post نوشته مستثنا (نوشته جاری).
	 * @param int[]    $exclude_cats دسته‌های مستثنا.
	 * @return array<int,array{title:string,terms:array<string,float>}>
	 */
	public static function load_all( array $post_types, $exclude_post = 0, array $exclude_cats = array() ) {
		global $wpdb;
		if ( ! $post_types ) {
			return array();
		}

		self::index_missing( $post_types, (int) apply_filters( 'tprp_lazy_index_limit', 400 ) );

		$limit        = (int) apply_filters( 'tprp_max_corpus', 4000 );
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$args         = array_merge( array( self::META ), $post_types );
		$sql          = "SELECT p.ID, p.post_title, pm.meta_value FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
			WHERE p.post_status = 'publish' AND p.post_password = '' AND p.post_type IN ($placeholders)";

		if ( $exclude_post ) {
			$sql   .= ' AND p.ID <> %d';
			$args[] = (int) $exclude_post;
		}
		$exclude_cats = array_filter( array_map( 'intval', $exclude_cats ) );
		if ( $exclude_cats ) {
			$cat_placeholders = implode( ',', array_fill( 0, count( $exclude_cats ), '%d' ) );
			$sql             .= " AND p.ID NOT IN (SELECT tr.object_id FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.term_id IN ($cat_placeholders))";
			$args             = array_merge( $args, $exclude_cats );
		}
		$sql   .= ' ORDER BY p.post_date DESC LIMIT %d';
		$args[] = $limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
		$out  = array();
		foreach ( (array) $rows as $row ) {
			$terms = json_decode( $row->meta_value, true );
			if ( ! is_array( $terms ) || ! $terms ) {
				continue;
			}
			$out[ (int) $row->ID ] = array(
				'title' => $row->post_title,
				'terms' => $terms,
			);
		}
		return $out;
	}

	/**
	 * ساخت مجدد ایندکس به‌صورت دسته‌ای (برای دکمه «بازسازی ایندکس»).
	 *
	 * @param int $offset شروع.
	 * @param int $limit  تعداد در هر دسته.
	 * @return array{done:int,total:int,finished:bool}
	 */
	public static function rebuild_batch( $offset, $limit = 100 ) {
		global $wpdb;
		$post_types = TPRP_Plugin::post_types();
		if ( ! $post_types ) {
			return array(
				'done'     => 0,
				'total'    => 0,
				'finished' => true,
			);
		}
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_password = '' AND post_type IN ($placeholders)", $post_types ) );
		$ids   = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_password = '' AND post_type IN ($placeholders) ORDER BY ID ASC LIMIT %d, %d",
				array_merge( $post_types, array( max( 0, (int) $offset ), max( 1, (int) $limit ) ) )
			)
		);
		// phpcs:enable
		$ids = array_map( 'intval', (array) $ids );
		if ( $ids ) {
			_prime_post_caches( $ids, true, false );
		}
		foreach ( $ids as $id ) {
			self::update( $id );
		}
		$done = min( $total, max( 0, (int) $offset ) + count( $ids ) );
		return array(
			'done'     => $done,
			'total'    => $total,
			'finished' => $done >= $total || ! $ids,
		);
	}

	/**
	 * تعداد نوشته‌های ایندکس‌شده.
	 *
	 * @return int
	 */
	public static function count_indexed() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", self::META ) );
	}
}
