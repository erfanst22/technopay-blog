<?php
/**
 * آیکون و رنگ اختصاصی برای دسته‌ها.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

/**
 * آیکون‌های قابل انتخاب برای دسته‌ها.
 *
 * @return string[]
 */
function technopay_category_icons() {
	return array(
		'card'       => __( 'کارت بانکی', 'technopay' ),
		'wallet'     => __( 'کیف پول', 'technopay' ),
		'bag'        => __( 'خرید', 'technopay' ),
		'shield'     => __( 'امنیت', 'technopay' ),
		'coins'      => __( 'طلا و سکه', 'technopay' ),
		'chart'      => __( 'نمودار', 'technopay' ),
		'megaphone'  => __( 'اخبار', 'technopay' ),
		'smartphone' => __( 'موبایل', 'technopay' ),
		'cpu'        => __( 'تکنولوژی', 'technopay' ),
		'home'       => __( 'خانه', 'technopay' ),
		'book'       => __( 'آموزش', 'technopay' ),
		'gift'       => __( 'هدیه و تخفیف', 'technopay' ),
		'percent'    => __( 'درصد', 'technopay' ),
		'folder'     => __( 'پوشه', 'technopay' ),
	);
}

/**
 * آیکون و رنگ یک دسته (با مقدار پیش‌فرض بر اساس شناسه).
 *
 * @param WP_Term|int $term دسته.
 * @return array{icon:string,color:int}
 */
function technopay_term_style( $term ) {
	$term_id = $term instanceof WP_Term ? $term->term_id : (int) $term;
	$icons   = array_keys( technopay_category_icons() );
	$icon    = get_term_meta( $term_id, 'technopay_icon', true );
	$color   = (int) get_term_meta( $term_id, 'technopay_color', true );

	return array(
		'icon'  => $icon && in_array( $icon, $icons, true ) ? $icon : $icons[ $term_id % 7 ],
		'color' => $color >= 1 && $color <= 6 ? $color : ( $term_id % 6 ) + 1,
	);
}

/**
 * فیلدهای فرم افزودن دسته.
 */
function technopay_category_add_fields() {
	?>
	<div class="form-field">
		<label for="technopay_icon"><?php esc_html_e( 'آیکون دسته (قالب تکنوپی)', 'technopay' ); ?></label>
		<?php technopay_category_icon_select( '' ); ?>
	</div>
	<div class="form-field">
		<label for="technopay_color"><?php esc_html_e( 'رنگ دسته', 'technopay' ); ?></label>
		<?php technopay_category_color_select( 0 ); ?>
	</div>
	<?php
	wp_nonce_field( 'technopay_term_meta', 'technopay_term_meta_nonce' );
}
add_action( 'category_add_form_fields', 'technopay_category_add_fields' );

/**
 * فیلدهای فرم ویرایش دسته.
 *
 * @param WP_Term $term دسته.
 */
function technopay_category_edit_fields( $term ) {
	?>
	<tr class="form-field">
		<th scope="row"><label for="technopay_icon"><?php esc_html_e( 'آیکون دسته (قالب تکنوپی)', 'technopay' ); ?></label></th>
		<td><?php technopay_category_icon_select( get_term_meta( $term->term_id, 'technopay_icon', true ) ); ?></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="technopay_color"><?php esc_html_e( 'رنگ دسته', 'technopay' ); ?></label></th>
		<td>
			<?php technopay_category_color_select( (int) get_term_meta( $term->term_id, 'technopay_color', true ) ); ?>
			<?php wp_nonce_field( 'technopay_term_meta', 'technopay_term_meta_nonce' ); ?>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'technopay_category_edit_fields' );

/**
 * لیست انتخاب آیکون.
 *
 * @param string $current مقدار فعلی.
 */
function technopay_category_icon_select( $current ) {
	echo '<select name="technopay_icon" id="technopay_icon"><option value="">' . esc_html__( 'خودکار', 'technopay' ) . '</option>';
	foreach ( technopay_category_icons() as $key => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
	}
	echo '</select>';
}

/**
 * لیست انتخاب رنگ.
 *
 * @param int $current مقدار فعلی.
 */
function technopay_category_color_select( $current ) {
	$colors = array(
		1 => __( 'آبی / بنفش', 'technopay' ),
		2 => __( 'فیروزه‌ای', 'technopay' ),
		3 => __( 'نارنجی / قرمز', 'technopay' ),
		4 => __( 'طلایی', 'technopay' ),
		5 => __( 'سرمه‌ای', 'technopay' ),
		6 => __( 'سبز', 'technopay' ),
	);
	echo '<select name="technopay_color" id="technopay_color"><option value="0">' . esc_html__( 'خودکار', 'technopay' ) . '</option>';
	foreach ( $colors as $key => $label ) {
		printf( '<option value="%1$d"%2$s>%3$s</option>', (int) $key, selected( (int) $current, $key, false ), esc_html( $label ) );
	}
	echo '</select>';
}

/**
 * ذخیره متای دسته.
 *
 * @param int $term_id شناسه دسته.
 */
function technopay_save_category_meta( $term_id ) {
	if ( ! isset( $_POST['technopay_term_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['technopay_term_meta_nonce'] ) ), 'technopay_term_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$icon = isset( $_POST['technopay_icon'] ) ? sanitize_key( wp_unslash( $_POST['technopay_icon'] ) ) : '';
	if ( $icon && array_key_exists( $icon, technopay_category_icons() ) ) {
		update_term_meta( $term_id, 'technopay_icon', $icon );
	} else {
		delete_term_meta( $term_id, 'technopay_icon' );
	}

	$color = isset( $_POST['technopay_color'] ) ? absint( $_POST['technopay_color'] ) : 0;
	if ( $color >= 1 && $color <= 6 ) {
		update_term_meta( $term_id, 'technopay_color', $color );
	} else {
		delete_term_meta( $term_id, 'technopay_color' );
	}
}
add_action( 'created_category', 'technopay_save_category_meta' );
add_action( 'edited_category', 'technopay_save_category_meta' );
