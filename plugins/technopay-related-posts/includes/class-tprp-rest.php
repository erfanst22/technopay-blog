<?php
/**
 * رابط REST افزونه (تحلیل، ذخیره تأییدها، بازسازی ایندکس).
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * مسیرهای /wp-json/tprp/v1/.
 */
final class TPRP_Rest {

	const NS = 'tprp/v1';

	/**
	 * اتصال هوک‌ها.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * ثبت مسیرها.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/analyze',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'analyze' ),
				'permission_callback' => array( __CLASS__, 'can_edit_post' ),
				'args'                => array(
					'post_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'content' => array(
						'type'     => 'string',
						'required' => false,
					),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/approve',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'approve' ),
				'permission_callback' => array( __CLASS__, 'can_edit_post' ),
				'args'                => array(
					'post_id'  => array(
						'type'     => 'integer',
						'required' => true,
					),
					'items'    => array(
						'type'     => 'array',
						'required' => false,
						'default'  => array(),
					),
					'disabled' => array(
						'type'     => 'boolean',
						'required' => false,
						'default'  => false,
					),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/reindex',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reindex' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'offset' => array(
						'type'    => 'integer',
						'default' => 0,
					),
				),
			)
		);
	}

	/**
	 * دسترسی: ویرایش همین نوشته.
	 *
	 * @param WP_REST_Request $request درخواست.
	 * @return bool
	 */
	public static function can_edit_post( $request ) {
		$post_id = (int) $request->get_param( 'post_id' );
		return $post_id > 0 && current_user_can( 'edit_post', $post_id );
	}

	/**
	 * اطلاعات نمایشی یک نوشته مقصد.
	 *
	 * @param int $id شناسه.
	 * @return array|null
	 */
	private static function target_info( $id ) {
		$post = get_post( $id );
		if ( ! $post instanceof WP_Post ) {
			return null;
		}
		$thumb = (string) get_the_post_thumbnail_url( $post, 'thumbnail' );
		/**
		 * آدرس تصویر بندانگشتی در پنل ادمین (برای قالب‌هایی که تصویر پیش‌فرض دارند).
		 *
		 * @param string  $thumb آدرس.
		 * @param WP_Post $post  نوشته.
		 */
		$thumb = (string) apply_filters( 'tprp_admin_thumbnail_url', $thumb, $post );
		return array(
			'id'    => (int) $post->ID,
			'title' => get_the_title( $post ),
			'url'   => get_permalink( $post ),
			'thumb' => $thumb,
			'valid' => 'publish' === $post->post_status,
		);
	}

	/**
	 * تحلیل متن مقاله و پیشنهاد مطالب مرتبط برای هر پاراگراف.
	 *
	 * @param WP_REST_Request $request درخواست.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function analyze( $request ) {
		$post_id = (int) $request->get_param( 'post_id' );
		$post    = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'tprp_not_found', __( 'نوشته پیدا نشد.', 'technopay-related-posts' ), array( 'status' => 404 ) );
		}

		$posted  = $request->get_param( 'content' );
		$source  = 'saved';
		$content = $post->post_content;
		if ( is_string( $posted ) && '' !== trim( $posted ) ) {
			$content = $posted;
			$source  = 'editor';
		}

		// رندر نهایی محتوا (بلوک‌ها، شورت‌کدها) بدون دخالت فیلتر درج باکس خودمان.
		$previous_post  = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		TPRP_Frontend::$bypass = true;
		try {
			$html = apply_filters( 'the_content', $content );
		} finally {
			TPRP_Frontend::$bypass = false;
			if ( $previous_post ) {
				$GLOBALS['post'] = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $previous_post );
			} else {
				wp_reset_postdata();
			}
		}

		$settings = TPRP_Plugin::settings();
		$analysis = TPRP_Matcher::analyze(
			$post_id,
			$html,
			array(
				'min_words'    => $settings['min_words'],
				'min_score'    => $settings['min_score'],
				'candidates'   => $settings['candidates'],
				'post_types'   => TPRP_Plugin::post_types(),
				'exclude_cats' => $settings['exclude_cats'],
			)
		);

		// تأییدهای قبلی را روی پاراگراف‌ها بنشان؛ آنچه پیدا نشد «منسوخ» است.
		$approved = array();
		foreach ( TPRP_Store::get_items( $post_id ) as $item ) {
			$approved[ $item['hash'] . ':' . $item['occurrence'] ] = $item;
		}
		$infos      = array();
		$paragraphs = array();
		foreach ( $analysis['paragraphs'] as $para ) {
			$key   = $para['hash'] . ':' . $para['occurrence'];
			$cands = array();
			foreach ( $para['candidates'] as $cand ) {
				$info = self::target_info( $cand['id'] );
				if ( $info ) {
					$cands[] = array_merge(
						$info,
						array(
							'score'    => $cand['score'],
							'keywords' => $cand['keywords'],
						)
					);
				}
			}
			$entry = array(
				'index'       => $para['index'],
				'hash'        => $para['hash'],
				'occurrence'  => $para['occurrence'],
				'section'     => $para['section'],
				'excerpt'     => $para['excerpt'],
				'candidates'  => $cands,
				'recommended' => ( $para['recommended'] >= 0 && isset( $cands[ $para['recommended'] ] ) ) ? $para['recommended'] : -1,
				'approved'    => null,
			);
			if ( isset( $approved[ $key ] ) ) {
				$info = self::target_info( $approved[ $key ]['target'] );
				if ( $info ) {
					$entry['approved'] = array(
						'target'    => $approved[ $key ]['target'],
						'placement' => $approved[ $key ]['placement'],
						'info'      => $info,
					);
				}
				unset( $approved[ $key ] );
			}
			// پاراگرافی که نه پیشنهادی دارد و نه تأیید قبلی، در خروجی نمی‌آید.
			if ( $entry['candidates'] || $entry['approved'] ) {
				$paragraphs[] = $entry;
			}
		}
		$stale = array();
		foreach ( $approved as $item ) {
			$stale[] = array_merge( $item, array( 'info' => self::target_info( $item['target'] ) ) );
		}

		return rest_ensure_response(
			array(
				'post_id'    => $post_id,
				'source'     => $source,
				'corpus'     => $analysis['corpus'],
				'total'      => count( $analysis['paragraphs'] ),
				'paragraphs' => $paragraphs,
				'stale'      => $stale,
				'disabled'   => TPRP_Store::is_disabled( $post_id ),
				'placement'  => $settings['placement'],
				'preview'    => get_preview_post_link( $post_id ),
			)
		);
	}

	/**
	 * ذخیره تأییدها.
	 *
	 * @param WP_REST_Request $request درخواست.
	 * @return WP_REST_Response
	 */
	public static function approve( $request ) {
		$post_id = (int) $request->get_param( 'post_id' );
		$items   = $request->get_param( 'items' );
		$saved   = TPRP_Store::save_items( $post_id, is_array( $items ) ? $items : array() );
		TPRP_Store::set_disabled( $post_id, (bool) $request->get_param( 'disabled' ) );

		return rest_ensure_response(
			array(
				'saved'    => $saved,
				'disabled' => TPRP_Store::is_disabled( $post_id ),
				'preview'  => get_preview_post_link( $post_id ),
				'permalink' => get_permalink( $post_id ),
			)
		);
	}

	/**
	 * بازسازی دسته‌ای ایندکس.
	 *
	 * @param WP_REST_Request $request درخواست.
	 * @return WP_REST_Response
	 */
	public static function reindex( $request ) {
		return rest_ensure_response( TPRP_Index::rebuild_batch( (int) $request->get_param( 'offset' ), 100 ) );
	}
}
