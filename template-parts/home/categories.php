<?php
/**
 * صفحه اصلی: کاشی‌های دسته‌بندی.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_terms = technopay_get_top_categories( max( 2, (int) technopay_block_setting( 'count', 6 ) ) );
if ( count( $technopay_terms ) < 2 ) {
	return;
}
?>
<section class="section container" aria-labelledby="home-cats-title">
	<h2 class="sr-only" id="home-cats-title"><?php esc_html_e( 'دسته‌بندی‌ها', 'technopay' ); ?></h2>
	<div class="cat-strip">
		<?php foreach ( $technopay_terms as $technopay_term ) : ?>
			<?php $technopay_style = technopay_term_style( $technopay_term ); ?>
			<a class="cat-tile" href="<?php echo esc_url( get_category_link( $technopay_term ) ); ?>">
				<span class="cat-tile__icon bg-<?php echo esc_attr( $technopay_style['color'] ); ?>"><?php technopay_icon( $technopay_style['icon'] ); ?></span>
				<strong><?php echo esc_html( $technopay_term->name ); ?></strong>
				<small>
					<?php
					/* translators: %s: number of posts */
					echo esc_html( sprintf( __( '%s مقاله', 'technopay' ), technopay_number( $technopay_term->count ) ) );
					?>
				</small>
			</a>
		<?php endforeach; ?>
	</div>
</section>
