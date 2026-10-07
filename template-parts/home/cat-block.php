<?php
/**
 * صفحه اصلی: بلوک یک دسته (یک کارت بزرگ + لیست).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_term = technopay_resolve_category( (int) technopay_block_setting( 'category', 0 ), 1 );
if ( ! $technopay_term ) {
	return;
}
$technopay_posts = get_posts(
	array(
		'posts_per_page'      => 4,
		'cat'                 => $technopay_term->term_id,
		'ignore_sticky_posts' => true,
	)
);
if ( ! $technopay_posts ) {
	return;
}
?>
<section class="section container">
	<?php technopay_section_head( $technopay_term->name, get_category_link( $technopay_term ), wp_strip_all_tags( $technopay_term->description ) ); ?>
	<div class="cat-block">
		<?php
		global $post;
		$post = array_shift( $technopay_posts ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		get_template_part( 'template-parts/card-feature', null, array( 'heading' => 'h3' ) );
		?>
		<div class="cat-block__list">
			<?php
			foreach ( $technopay_posts as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				get_template_part( 'template-parts/card-mini' );
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
