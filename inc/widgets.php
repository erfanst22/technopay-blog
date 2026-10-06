<?php
/**
 * سایدبارها و ابزارک‌های اختصاصی قالب.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت سایدبارها و ابزارک‌ها.
 */
function technopay_widgets_init() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'name'        => __( 'سایدبار اصلی', 'technopay' ),
				'id'          => 'technopay-main',
				'description' => __( 'در صفحه اصلی، آرشیوها و جستجو نمایش داده می‌شود. اگر خالی باشد، ابزارک‌های پیش‌فرض قالب نمایش داده می‌شوند.', 'technopay' ),
			)
		)
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'name'        => __( 'سایدبار نوشته', 'technopay' ),
				'id'          => 'technopay-single',
				'description' => __( 'ستون کنار مقاله در صفحه نوشته‌ها. اگر خالی باشد، لیست‌های پربازدیدترین، جدیدترین و مقالات مرتبط نمایش داده می‌شود.', 'technopay' ),
			)
		)
	);

	register_widget( 'TechnoPay_Popular_Widget' );
	register_widget( 'TechnoPay_Calculator_Widget' );
	register_widget( 'TechnoPay_Promo_Widget' );
}
add_action( 'widgets_init', 'technopay_widgets_init' );

/**
 * محتوای ابزارک پربازدیدترین‌ها.
 *
 * @param int $count تعداد.
 */
function technopay_render_popular_list( $count = 5 ) {
	$posts = technopay_get_popular_posts( $count );
	if ( ! $posts ) {
		return;
	}
	global $post;
	echo '<ol class="popular">';
	foreach ( $posts as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		echo '<li><div>';
		printf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
		technopay_post_meta( array( 'views', 'date' ) );
		echo '</div></li>';
	}
	echo '</ol>';
	wp_reset_postdata();
}

/**
 * محتوای ابزارک محاسبه‌گر اقساط.
 */
function technopay_render_calculator() {
	static $instance = 0;
	$instance++;

	$min     = max( 1, absint( technopay_option( 'calc_min' ) ) );
	$max     = max( $min + 1, absint( technopay_option( 'calc_max' ) ) );
	$default = min( $max, max( $min, absint( technopay_option( 'calc_default' ) ) ) );
	$rate    = (float) technopay_option( 'calc_rate' );
	$id      = 'tp-calc-' . $instance;
	?>
	<div class="calc" data-calc data-rate="<?php echo esc_attr( $rate ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'مبلغ خرید', 'technopay' ); ?> <output data-calc-amount for="<?php echo esc_attr( $id ); ?>"></output></label>
		<input id="<?php echo esc_attr( $id ); ?>" type="range" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="5" value="<?php echo esc_attr( $default ); ?>">
		<div class="calc__months" role="group" aria-label="<?php esc_attr_e( 'مدت بازپرداخت', 'technopay' ); ?>">
			<?php foreach ( array( 6, 12, 18, 24 ) as $months ) : ?>
				<button type="button" data-months="<?php echo esc_attr( $months ); ?>" class="<?php echo 12 === $months ? 'is-active' : ''; ?>" aria-pressed="<?php echo 12 === $months ? 'true' : 'false'; ?>">
					<?php
					/* translators: %s: number of months */
					echo esc_html( sprintf( __( '%s ماه', 'technopay' ), technopay_fa_digits( $months ) ) );
					?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="calc__result" aria-live="polite">
			<span><?php esc_html_e( 'قسط ماهانه تقریبی', 'technopay' ); ?></span>
			<strong data-calc-result>—</strong>
		</div>
		<?php if ( technopay_option( 'calc_btn_url' ) ) : ?>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( technopay_option( 'calc_btn_url' ) ); ?>"><?php technopay_icon( 'card' ); ?><?php esc_html_e( 'درخواست اعتبار', 'technopay' ); ?></a>
		<?php endif; ?>
		<?php if ( technopay_option( 'calc_note' ) ) : ?>
			<p class="calc__note"><?php echo esc_html( technopay_option( 'calc_note' ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * باکس تبلیغاتی دریافت اعتبار.
 *
 * @param array $args عنوان، متن، مبلغ، دکمه.
 */
function technopay_render_promo( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'    => __( 'اعتبار خرید تکنوپی', 'technopay' ),
			'amount'   => technopay_option( 'cta_stat_1_value' ) . ' ' . technopay_option( 'cta_stat_1_label' ),
			'text'     => __( 'بدون ضامن و کاملاً آنلاین؛ همین امروز اعتبار بگیرید و اقساطی خرید کنید.', 'technopay' ),
			'btn_text' => technopay_option( 'cta_btn_text' ),
			'btn_url'  => technopay_option( 'cta_btn_url' ),
		)
	);
	?>
	<section class="widget promo">
		<span class="promo__card" aria-hidden="true"></span>
		<h2 class="widget__title"><?php technopay_icon( 'zap' ); ?><?php echo esc_html( $args['title'] ); ?></h2>
		<?php if ( $args['amount'] ) : ?>
			<p class="promo__amount"><?php echo esc_html( $args['amount'] ); ?></p>
		<?php endif; ?>
		<p><?php echo esc_html( $args['text'] ); ?></p>
		<?php if ( $args['btn_url'] ) : ?>
			<a class="btn btn--white btn--block" href="<?php echo esc_url( $args['btn_url'] ); ?>"><?php echo esc_html( $args['btn_text'] ); ?><?php technopay_icon( 'arrow-left' ); ?></a>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * لیست دسته‌ها با تعداد مطالب.
 */
function technopay_render_category_list() {
	$terms = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => 8,
		)
	);
	if ( ! $terms ) {
		return;
	}
	echo '<ul class="cat-list">';
	foreach ( $terms as $term ) {
		printf(
			'<li><a href="%1$s">%2$s<span>%3$s</span></a></li>',
			esc_url( get_category_link( $term ) ),
			esc_html( $term->name ),
			esc_html( technopay_number( $term->count ) )
		);
	}
	echo '</ul>';
}

/**
 * برچسب‌های پرکاربرد.
 */
function technopay_render_tag_cloud() {
	$tags = get_tags(
		array(
			'orderby' => 'count',
			'order'   => 'DESC',
			'number'  => 14,
		)
	);
	if ( ! $tags ) {
		return;
	}
	echo '<div class="tag-cloud">';
	foreach ( $tags as $tag ) {
		printf( '<a class="tag" href="%1$s">%2$s</a>', esc_url( get_tag_link( $tag ) ), esc_html( $tag->name ) );
	}
	echo '</div>';
}

/**
 * ابزارک‌های پیش‌فرض سایدبار اصلی (وقتی سایدبار خالی است).
 */
function technopay_default_main_sidebar() {
	?>
	<section class="widget">
		<h2 class="widget__title"><?php technopay_icon( 'flame' ); ?><?php esc_html_e( 'پربازدیدترین‌ها', 'technopay' ); ?></h2>
		<?php technopay_render_popular_list( 5 ); ?>
	</section>
	<section class="widget">
		<h2 class="widget__title"><?php technopay_icon( 'calculator' ); ?><?php esc_html_e( 'محاسبه‌گر اقساط', 'technopay' ); ?></h2>
		<?php technopay_render_calculator(); ?>
	</section>
	<section class="widget">
		<h2 class="widget__title"><?php technopay_icon( 'folder' ); ?><?php esc_html_e( 'دسته‌بندی‌ها', 'technopay' ); ?></h2>
		<?php technopay_render_category_list(); ?>
	</section>
	<?php technopay_render_promo(); ?>
	<?php if ( get_tags( array( 'number' => 1 ) ) ) : ?>
		<section class="widget">
			<h2 class="widget__title"><?php technopay_icon( 'tag' ); ?><?php esc_html_e( 'برچسب‌های پرکاربرد', 'technopay' ); ?></h2>
			<?php technopay_render_tag_cloud(); ?>
		</section>
	<?php endif; ?>
	<?php
}

/**
 * محتوای پیش‌فرض سایدبار نوشته (وقتی سایدبار «سایدبار نوشته» خالی است):
 * پربازدیدترین، جدیدترین و مقالات مرتبط.
 */
function technopay_default_single_sidebar() {
	$post_id = (int) get_the_ID();
	technopay_mini_list( __( 'پربازدیدترین مقالات', 'technopay' ), technopay_get_popular_posts( 5, array( $post_id ) ) );
	technopay_mini_list( __( 'جدیدترین مقالات', 'technopay' ), technopay_get_latest_posts( 5, array( $post_id ) ) );
	if ( technopay_option( 'show_related' ) ) {
		technopay_mini_list( __( 'مقالات مرتبط', 'technopay' ), technopay_get_related_posts( $post_id, 5 ) );
	}
}

/**
 * ابزارک: پربازدیدترین مطالب.
 */
class TechnoPay_Popular_Widget extends WP_Widget {

	/**
	 * سازنده.
	 */
	public function __construct() {
		parent::__construct(
			'technopay_popular',
			__( 'تکنوپی: پربازدیدترین‌ها', 'technopay' ),
			array( 'description' => __( 'لیست شماره‌دار پربازدیدترین مطالب.', 'technopay' ) )
		);
	}

	/**
	 * نمایش.
	 *
	 * @param array $args     آرگومان‌های سایدبار.
	 * @param array $instance تنظیمات.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'پربازدیدترین‌ها', 'technopay' );
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . technopay_get_icon( 'flame' ) . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		technopay_render_popular_list( $count );
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * فرم تنظیمات.
	 *
	 * @param array $instance تنظیمات.
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'پربازدیدترین‌ها', 'technopay' );
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'عنوان:', 'technopay' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'تعداد:', 'technopay' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="10" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
		return '';
	}

	/**
	 * ذخیره.
	 *
	 * @param array $new_instance جدید.
	 * @param array $old_instance قدیم.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'count' => min( 10, max( 1, absint( $new_instance['count'] ) ) ),
		);
	}
}

/**
 * ابزارک: محاسبه‌گر اقساط.
 */
class TechnoPay_Calculator_Widget extends WP_Widget {

	/**
	 * سازنده.
	 */
	public function __construct() {
		parent::__construct(
			'technopay_calculator',
			__( 'تکنوپی: محاسبه‌گر اقساط', 'technopay' ),
			array( 'description' => __( 'محاسبه تقریبی قسط ماهانه. نرخ و بازه مبلغ از «سفارشی‌سازی» تنظیم می‌شود.', 'technopay' ) )
		);
	}

	/**
	 * نمایش.
	 *
	 * @param array $args     آرگومان‌ها.
	 * @param array $instance تنظیمات.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'محاسبه‌گر اقساط', 'technopay' );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . technopay_get_icon( 'calculator' ) . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		technopay_render_calculator();
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * فرم تنظیمات.
	 *
	 * @param array $instance تنظیمات.
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'محاسبه‌گر اقساط', 'technopay' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'عنوان:', 'technopay' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
		return '';
	}

	/**
	 * ذخیره.
	 *
	 * @param array $new_instance جدید.
	 * @param array $old_instance قدیم.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array( 'title' => sanitize_text_field( $new_instance['title'] ) );
	}
}

/**
 * ابزارک: باکس تبلیغاتی دریافت اعتبار.
 */
class TechnoPay_Promo_Widget extends WP_Widget {

	/**
	 * سازنده.
	 */
	public function __construct() {
		parent::__construct(
			'technopay_promo',
			__( 'تکنوپی: بنر دریافت اعتبار', 'technopay' ),
			array( 'description' => __( 'باکس رنگی تبلیغاتی با دکمه.', 'technopay' ) )
		);
	}

	/**
	 * فیلدهای ابزارک.
	 *
	 * @return array
	 */
	private function fields() {
		return array(
			'title'    => __( 'عنوان', 'technopay' ),
			'amount'   => __( 'متن درشت (مثلاً سقف اعتبار)', 'technopay' ),
			'text'     => __( 'توضیح', 'technopay' ),
			'btn_text' => __( 'متن دکمه', 'technopay' ),
			'btn_url'  => __( 'لینک دکمه', 'technopay' ),
		);
	}

	/**
	 * نمایش.
	 *
	 * @param array $args     آرگومان‌ها.
	 * @param array $instance تنظیمات.
	 */
	public function widget( $args, $instance ) {
		technopay_render_promo( array_filter( (array) $instance ) );
	}

	/**
	 * فرم تنظیمات.
	 *
	 * @param array $instance تنظیمات.
	 * @return string
	 */
	public function form( $instance ) {
		foreach ( $this->fields() as $key => $label ) {
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
			printf(
				'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s"></p>',
				esc_attr( $this->get_field_id( $key ) ),
				esc_html( $label ),
				esc_attr( $this->get_field_name( $key ) ),
				esc_attr( $value )
			);
		}
		echo '<p class="description">' . esc_html__( 'فیلدهای خالی از تنظیمات «بنر تبلیغاتی» در سفارشی‌سازی پر می‌شوند.', 'technopay' ) . '</p>';
		return '';
	}

	/**
	 * ذخیره.
	 *
	 * @param array $new_instance جدید.
	 * @param array $old_instance قدیم.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$clean = array();
		foreach ( array_keys( $this->fields() ) as $key ) {
			$value         = isset( $new_instance[ $key ] ) ? $new_instance[ $key ] : '';
			$clean[ $key ] = 'btn_url' === $key ? esc_url_raw( $value ) : sanitize_text_field( $value );
		}
		return $clean;
	}
}
