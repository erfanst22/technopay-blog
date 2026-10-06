<?php
/**
 * صفحه اصلی: دو ستون از دو دسته.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_panels = array_filter(
	array(
		technopay_get_section_category( 'home_panel_1_cat', 2 ),
		technopay_get_section_category( 'home_panel_2_cat', 3 ),
	)
);
if ( ! $technopay_panels ) {
	return;
}
?>
<section class="section container">
	<div class="two-col">
		<?php
		global $post;
		foreach ( $technopay_panels as $technopay_term ) :
			$technopay_posts = get_posts(
				array(
					'posts_per_page'      => 3,
					'cat'                 => $technopay_term->term_id,
					'ignore_sticky_posts' => true,
				)
			);
			if ( ! $technopay_posts ) {
				continue;
			}
			?>
			<div class="panel">
				<?php technopay_section_head( $technopay_term->name, get_category_link( $technopay_term ) ); ?>
				<div class="panel__list">
					<?php
					foreach ( $technopay_posts as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/card-mini' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
