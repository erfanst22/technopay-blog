<?php
/**
 * صفحه اصلی: نوار موضوعات داغ (برچسب‌های پرکاربرد).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_tags = get_tags(
	array(
		'orderby' => 'count',
		'order'   => 'DESC',
		'number'  => max( 1, (int) technopay_block_setting( 'count', 10 ) ),
	)
);
if ( ! $technopay_tags ) {
	return;
}
?>
<div class="container">
	<div class="trending">
		<span class="trending__label"><?php technopay_icon( 'flame' ); ?><?php esc_html_e( 'داغ‌ترین‌ها', 'technopay' ); ?></span>
		<div class="trending__list">
			<?php foreach ( $technopay_tags as $technopay_tag ) : ?>
				<a href="<?php echo esc_url( get_tag_link( $technopay_tag ) ); ?>">#<?php echo esc_html( $technopay_tag->name ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
