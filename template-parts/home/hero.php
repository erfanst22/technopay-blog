<?php
/**
 * صفحه اصلی: مطالب ویژه.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_hero = technopay_get_hero_posts( 5 );
if ( ! $technopay_hero ) {
	return;
}
technopay_shown_ids( wp_list_pluck( $technopay_hero, 'ID' ) );
?>
<section class="hero container" aria-label="<?php esc_attr_e( 'مطالب ویژه', 'technopay' ); ?>">
	<div class="hero__grid<?php echo count( $technopay_hero ) < 5 ? ' hero__grid--few' : ''; ?>">
		<?php
		global $post;
		foreach ( $technopay_hero as $technopay_i => $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
			get_template_part(
				'template-parts/card-feature',
				null,
				array(
					'large'   => 0 === $technopay_i,
					'heading' => 0 === $technopay_i ? 'h2' : 'h3',
				)
			);
		endforeach;
		wp_reset_postdata();
		?>
	</div>
</section>
