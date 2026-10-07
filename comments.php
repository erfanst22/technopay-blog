<?php
/**
 * دیدگاه‌ها.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}

$technopay_commenter = wp_get_current_commenter();
$technopay_fields    = array(
	'author' => sprintf(
		'<p class="field comment-form-author"><label for="author">%1$s <span aria-hidden="true">*</span></label><input id="author" name="author" type="text" value="%2$s" required autocomplete="name"></p>',
		esc_html__( 'نام', 'technopay' ),
		esc_attr( $technopay_commenter['comment_author'] )
	),
	'email'  => sprintf(
		'<p class="field comment-form-email"><label for="email">%1$s <span aria-hidden="true">*</span></label><input id="email" name="email" type="email" value="%2$s" required autocomplete="email" dir="ltr"></p>',
		esc_html__( 'ایمیل', 'technopay' ),
		esc_attr( $technopay_commenter['comment_author_email'] )
	),
);

if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
	$technopay_fields['cookies'] = sprintf(
		'<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%1$s> <label for="wp-comment-cookies-consent">%2$s</label></p>',
		empty( $technopay_commenter['comment_author_email'] ) ? '' : ' checked',
		esc_html__( 'نام و ایمیل من را برای دیدگاه‌های بعدی در این مرورگر ذخیره کن.', 'technopay' )
	);
}
?>
<section id="comments" class="comments">
	<h4 class="comments__title">
		<?php technopay_icon( 'message' ); ?>
		<?php
		$technopay_count = get_comments_number();
		if ( $technopay_count ) {
			/* translators: %s: number of comments */
			echo esc_html( sprintf( __( '%s دیدگاه', 'technopay' ), technopay_number( $technopay_count ) ) );
		} else {
			esc_html_e( 'دیدگاه‌ها', 'technopay' );
		}
		?>
	</h4>

	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'callback'    => 'technopay_comment',
					'avatar_size' => 40,
				)
			);
			?>
		</ol>
		<?php
		the_comments_pagination(
			array(
				'prev_text' => technopay_get_icon( 'chevron-right' ),
				'next_text' => technopay_get_icon( 'chevron-left' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'امکان ارسال دیدگاه برای این مطلب بسته شده است.', 'technopay' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'fields'               => $technopay_fields,
			'comment_field'        => sprintf(
				'<p class="field comment-form-comment"><label for="comment">%1$s <span aria-hidden="true">*</span></label><textarea id="comment" name="comment" required maxlength="65525" placeholder="%2$s"></textarea></p>',
				esc_html__( 'دیدگاه', 'technopay' ),
				esc_attr__( 'نظر یا سؤال خود را بنویسید…', 'technopay' )
			),
			'title_reply'          => __( 'دیدگاه خود را بنویسید', 'technopay' ),
			/* translators: %s: author name */
			'title_reply_to'       => __( 'پاسخ به %s', 'technopay' ),
			'cancel_reply_link'    => __( 'لغو پاسخ', 'technopay' ),
			'title_reply_before'   => '<h4 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h4>',
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'نشانی ایمیل شما منتشر نخواهد شد. بخش‌های موردنیاز علامت‌گذاری شده‌اند *', 'technopay' ) . '</p>',
			'class_submit'         => 'btn btn--primary',
			'label_submit'         => __( 'ارسال دیدگاه', 'technopay' ),
		)
	);
	?>
</section>
