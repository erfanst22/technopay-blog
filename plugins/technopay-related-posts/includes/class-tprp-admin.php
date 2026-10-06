<?php
/**
 * بخش مدیریت: صفحه تنظیمات و جعبه «مطالب مرتبط هوشمند» در ویرایشگر نوشته.
 *
 * @package TechnoPay_Related_Posts
 */

defined( 'ABSPATH' ) || exit;

/**
 * رابط مدیریت.
 */
final class TPRP_Admin {

	const PAGE = 'tprp';

	/**
	 * اتصال هوک‌ها.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TPRP_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * لینک «تنظیمات» در لیست افزونه‌ها.
	 *
	 * @param string[] $links لینک‌ها.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'تنظیمات', 'technopay-related-posts' ) . '</a>' );
		return $links;
	}

	/**
	 * منوی تنظیمات.
	 */
	public static function menu() {
		add_options_page(
			__( 'مطالب مرتبط هوشمند', 'technopay-related-posts' ),
			__( 'مطالب مرتبط هوشمند', 'technopay-related-posts' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_settings' )
		);
	}

	/**
	 * ثبت تنظیمات.
	 */
	public static function register_settings() {
		register_setting(
			'tprp_group',
			TPRP_Plugin::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => TPRP_Plugin::defaults(),
			)
		);
	}

	/**
	 * پاکسازی تنظیمات ورودی.
	 *
	 * @param mixed $input ورودی فرم.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = TPRP_Plugin::defaults();
		$input    = is_array( $input ) ? $input : array();

		$types = isset( $input['post_types'] ) ? array_values( array_filter( array_map( 'sanitize_key', (array) $input['post_types'] ), 'post_type_exists' ) ) : array();

		return array(
			'post_types'   => $types ? $types : $defaults['post_types'],
			'min_words'    => min( 80, max( 4, isset( $input['min_words'] ) ? absint( $input['min_words'] ) : $defaults['min_words'] ) ),
			'min_score'    => min( 80, max( 1, isset( $input['min_score'] ) ? absint( $input['min_score'] ) : $defaults['min_score'] ) ),
			'candidates'   => min( 5, max( 1, isset( $input['candidates'] ) ? absint( $input['candidates'] ) : $defaults['candidates'] ) ),
			'style'        => isset( $input['style'] ) && 'text' === $input['style'] ? 'text' : 'card',
			'placement'    => isset( $input['placement'] ) && in_array( $input['placement'], TPRP_Store::placements(), true ) ? $input['placement'] : $defaults['placement'],
			'label'        => isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : $defaults['label'],
			'exclude_cats' => isset( $input['exclude_cats'] ) ? array_values( array_filter( array_map( 'absint', (array) $input['exclude_cats'] ) ) ) : array(),
		);
	}

	/**
	 * صفحه تنظیمات.
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s     = TPRP_Plugin::settings();
		$types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $types['attachment'] );
		$name = TPRP_Plugin::OPTION;
		?>
		<div class="wrap tprp-settings">
			<h1><?php esc_html_e( 'مطالب مرتبط هوشمند', 'technopay-related-posts' ); ?></h1>
			<p class="description"><?php esc_html_e( 'افزونه متن هر نوشته را یک بار می‌خواند و برای هر پاراگراف، مرتبط‌ترین نوشته‌های سایت را پیشنهاد می‌دهد. پیشنهادها فقط پس از تأیید ویراستار (در جعبه «مطالب مرتبط هوشمند» زیر ویرایشگر) در مقاله نمایش داده می‌شوند و متن مقاله تغییر نمی‌کند.', 'technopay-related-posts' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'tprp_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'انواع نوشته', 'technopay-related-posts' ); ?></th>
						<td>
							<?php foreach ( $types as $type ) : ?>
								<label style="display:block;margin-bottom:4px">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[post_types][]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $s['post_types'], true ) ); ?>>
									<?php echo esc_html( $type->labels->name ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-min-words"><?php esc_html_e( 'حداقل کلمات پاراگراف', 'technopay-related-posts' ); ?></label></th>
						<td>
							<input id="tprp-min-words" type="number" min="4" max="80" class="small-text" name="<?php echo esc_attr( $name ); ?>[min_words]" value="<?php echo esc_attr( $s['min_words'] ); ?>">
							<p class="description"><?php esc_html_e( 'پاراگراف‌های کوتاه‌تر از این مقدار تحلیل نمی‌شوند.', 'technopay-related-posts' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-min-score"><?php esc_html_e( 'حداقل درصد تطابق', 'technopay-related-posts' ); ?></label></th>
						<td>
							<input id="tprp-min-score" type="number" min="1" max="80" class="small-text" name="<?php echo esc_attr( $name ); ?>[min_score]" value="<?php echo esc_attr( $s['min_score'] ); ?>"> %
							<p class="description"><?php esc_html_e( 'پیشنهادهای با تطابق کمتر نمایش داده نمی‌شوند. عدد کمتر = پیشنهاد بیشتر (با دقت کمتر).', 'technopay-related-posts' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-candidates"><?php esc_html_e( 'تعداد گزینه برای هر پاراگراف', 'technopay-related-posts' ); ?></label></th>
						<td><input id="tprp-candidates" type="number" min="1" max="5" class="small-text" name="<?php echo esc_attr( $name ); ?>[candidates]" value="<?php echo esc_attr( $s['candidates'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-placement"><?php esc_html_e( 'محل پیش‌فرض درج', 'technopay-related-posts' ); ?></label></th>
						<td>
							<select id="tprp-placement" name="<?php echo esc_attr( $name ); ?>[placement]">
								<option value="section_end" <?php selected( $s['placement'], 'section_end' ); ?>><?php esc_html_e( 'انتهای بخش (قبل از سرتیتر H2 بعدی)', 'technopay-related-posts' ); ?></option>
								<option value="after_paragraph" <?php selected( $s['placement'], 'after_paragraph' ); ?>><?php esc_html_e( 'درست بعد از همان پاراگراف', 'technopay-related-posts' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'برای هر پیشنهاد در ویرایشگر قابل تغییر است. تعداد باکس‌ها محدود نیست؛ هر پاراگراف حداکثر یک باکس دارد.', 'technopay-related-posts' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-style"><?php esc_html_e( 'ظاهر باکس', 'technopay-related-posts' ); ?></label></th>
						<td>
							<select id="tprp-style" name="<?php echo esc_attr( $name ); ?>[style]">
								<option value="card" <?php selected( $s['style'], 'card' ); ?>><?php esc_html_e( 'کارت با تصویر کوچک و چکیده', 'technopay-related-posts' ); ?></option>
								<option value="text" <?php selected( $s['style'], 'text' ); ?>><?php esc_html_e( 'یک خط متنی «بیشتر بخوانید: عنوان»', 'technopay-related-posts' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-label"><?php esc_html_e( 'برچسب باکس', 'technopay-related-posts' ); ?></label></th>
						<td><input id="tprp-label" type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $s['label'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="tprp-exclude"><?php esc_html_e( 'دسته‌های مستثنا', 'technopay-related-posts' ); ?></label></th>
						<td>
							<select id="tprp-exclude" name="<?php echo esc_attr( $name ); ?>[exclude_cats][]" multiple size="5" style="min-width:240px">
								<?php foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( in_array( (int) $cat->term_id, array_map( 'intval', (array) $s['exclude_cats'] ), true ) ); ?>><?php echo esc_html( $cat->name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'نوشته‌های این دسته‌ها هرگز به‌عنوان پیشنهاد معرفی نمی‌شوند.', 'technopay-related-posts' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<h2><?php esc_html_e( 'ایندکس نوشته‌ها', 'technopay-related-posts' ); ?></h2>
			<p>
				<?php
				/* translators: %s: number of indexed posts */
				printf( esc_html__( 'تعداد نوشته‌های ایندکس‌شده: %s', 'technopay-related-posts' ), '<strong>' . esc_html( number_format_i18n( TPRP_Index::count_indexed() ) ) . '</strong>' );
				?>
			</p>
			<p class="description"><?php esc_html_e( 'نوشته‌ها هنگام انتشار/به‌روزرسانی خودکار ایندکس می‌شوند. اگر افزونه را روی سایت دارای نوشته‌های قدیمی نصب کرده‌اید یا تنظیمات را عوض کرده‌اید، یک‌بار «بازسازی ایندکس» را بزنید.', 'technopay-related-posts' ); ?></p>
			<p>
				<button type="button" class="button" data-tprp-reindex><?php esc_html_e( 'بازسازی ایندکس', 'technopay-related-posts' ); ?></button>
				<span id="tprp-reindex-status" role="status" aria-live="polite" style="margin-inline-start:10px"></span>
			</p>
		</div>
		<?php
	}

	/**
	 * ثبت جعبه در ویرایشگر انواع نوشته فعال.
	 */
	public static function add_meta_box() {
		foreach ( TPRP_Plugin::post_types() as $type ) {
			add_meta_box(
				'tprp-box',
				__( 'مطالب مرتبط هوشمند', 'technopay-related-posts' ),
				array( __CLASS__, 'render_meta_box' ),
				$type,
				'normal',
				'default',
				array( '__block_editor_compatible_meta_box' => true )
			);
		}
	}

	/**
	 * محتوای جعبه (رابط با جاوااسکریپت ساخته می‌شود).
	 *
	 * @param WP_Post $post نوشته.
	 */
	public static function render_meta_box( $post ) {
		echo '<div id="tprp-app" data-post-id="' . esc_attr( $post->ID ) . '">';
		echo '<p class="description">' . esc_html__( 'برای دیدن پیشنهادها دکمه «تحلیل متن» را بزنید. (برای فعال شدن این بخش جاوااسکریپت لازم است.)', 'technopay-related-posts' ) . '</p>';
		echo '</div>';
	}

	/**
	 * فایل‌های CSS/JS.
	 *
	 * @param string $hook صفحه جاری.
	 */
	public static function assets( $hook ) {
		$is_settings = 'settings_page_' . self::PAGE === $hook;
		$is_editor   = false;
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			$screen    = get_current_screen();
			$is_editor = $screen && in_array( $screen->post_type, TPRP_Plugin::post_types(), true );
		}
		if ( ! $is_settings && ! $is_editor ) {
			return;
		}

		wp_enqueue_style( 'tprp-admin', TPRP_URL . 'assets/admin.css', array(), TPRP_VERSION );
		wp_enqueue_script( 'tprp-admin', TPRP_URL . 'assets/admin.js', array(), TPRP_VERSION, true );
		wp_localize_script(
			'tprp-admin',
			'TPRP',
			array(
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'endpoints' => array(
					'analyze' => esc_url_raw( rest_url( TPRP_Rest::NS . '/analyze' ) ),
					'approve' => esc_url_raw( rest_url( TPRP_Rest::NS . '/approve' ) ),
					'reindex' => esc_url_raw( rest_url( TPRP_Rest::NS . '/reindex' ) ),
				),
				'i18n'      => array(
					'analyze'       => __( 'تحلیل متن و پیشنهاد مطالب مرتبط', 'technopay-related-posts' ),
					'analyzing'     => __( 'در حال خواندن متن و یافتن مطالب مرتبط…', 'technopay-related-posts' ),
					'selectTop'     => __( 'انتخاب پیشنهاد اصلی هر بخش', 'technopay-related-posts' ),
					'clearAll'      => __( 'برداشتن همه', 'technopay-related-posts' ),
					'save'          => __( 'ذخیره تأییدها', 'technopay-related-posts' ),
					'saving'        => __( 'در حال ذخیره…', 'technopay-related-posts' ),
					'saved'         => __( 'ذخیره شد؛ باکس‌های تأییدشده در مقاله نمایش داده می‌شوند.', 'technopay-related-posts' ),
					'savedNone'     => __( 'ذخیره شد؛ هیچ باکسی در مقاله نمایش داده نمی‌شود.', 'technopay-related-posts' ),
					'preview'       => __( 'مشاهده مقاله', 'technopay-related-posts' ),
					'approve'       => __( 'تأیید و درج این پیشنهاد', 'technopay-related-posts' ),
					'section'       => __( 'بخش', 'technopay-related-posts' ),
					'intro'         => __( 'مقدمه', 'technopay-related-posts' ),
					'none'          => __( 'هیچ‌کدام', 'technopay-related-posts' ),
					'match'         => __( 'تطابق', 'technopay-related-posts' ),
					'keywords'      => __( 'کلیدواژه‌های مشترک:', 'technopay-related-posts' ),
					'placement'     => __( 'محل درج:', 'technopay-related-posts' ),
					'sectionEnd'    => __( 'انتهای همین بخش', 'technopay-related-posts' ),
					'afterPara'     => __( 'درست بعد از این پاراگراف', 'technopay-related-posts' ),
					'disable'       => __( 'نمایش باکس‌های مطالب مرتبط در این نوشته خاموش باشد', 'technopay-related-posts' ),
					'stale'         => __( 'تأییدهایی که پاراگرافشان دیگر در متن نیست (با ذخیره حذف می‌شوند):', 'technopay-related-posts' ),
					'summary'       => __( 'پاراگراف تحلیل‌شده: %1$s — دارای پیشنهاد: %2$s — نوشته‌های ایندکس‌شده: %3$s', 'technopay-related-posts' ),
					'fromEditor'    => __( 'متن فعلی ویرایشگر (ذخیره‌نشده) تحلیل شد.', 'technopay-related-posts' ),
					'fromSaved'     => __( 'آخرین نسخه ذخیره‌شده تحلیل شد.', 'technopay-related-posts' ),
					'noSuggestions' => __( 'برای این مقاله پیشنهادی پیدا نشد. می‌توانید «حداقل درصد تطابق» را در تنظیمات کمتر کنید یا نوشته‌های بیشتری منتشر کنید.', 'technopay-related-posts' ),
					'error'         => __( 'خطا:', 'technopay-related-posts' ),
					'done'          => __( 'انجام شد', 'technopay-related-posts' ),
					'working'       => __( 'در حال ایندکس…', 'technopay-related-posts' ),
					'unpublished'   => __( '(منتشر نشده)', 'technopay-related-posts' ),
				),
			)
		);
	}
}
