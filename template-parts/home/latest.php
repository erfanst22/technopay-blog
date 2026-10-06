<?php
/**
 * صفحه اصلی: آخرین مطالب با تب دسته‌ها + سایدبار.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_exclude = technopay_shown_ids();
$technopay_tabs    = array(
	'all' => array(
		'label' => __( 'همه', 'technopay' ),
		'posts' => get_posts(
			array(
				'posts_per_page'      => 8,
				'post__not_in'        => $technopay_exclude,
				'ignore_sticky_posts' => true,
			)
		),
		'url'   => '',
	),
);

foreach ( technopay_get_top_categories( 4 ) as $technopay_term ) {
	$technopay_tabs[ 'cat-' . $technopay_term->term_id ] = array(
		'label' => $technopay_term->name,
		'posts' => get_posts(
			array(
				'posts_per_page'      => 4,
				'cat'                 => $technopay_term->term_id,
				'ignore_sticky_posts' => true,
			)
		),
		'url'   => get_category_link( $technopay_term ),
	);
}

if ( ! $technopay_tabs['all']['posts'] ) {
	$technopay_tabs['all']['posts'] = get_posts( array( 'posts_per_page' => 8 ) );
}
technopay_shown_ids( wp_list_pluck( $technopay_tabs['all']['posts'], 'ID' ) );
$technopay_more_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : ( 'posts' === get_option( 'show_on_front' ) ? get_pagenum_link( 2 ) : '' );
?>
<section class="section container">
	<div class="layout">
		<div>
			<div class="section-head">
				<div>
					<h2 class="section-title"><?php esc_html_e( 'آخرین مطالب', 'technopay' ); ?></h2>
					<p class="section-sub"><?php esc_html_e( 'تازه‌ترین مقالات و راهنماهای مجله', 'technopay' ); ?></p>
				</div>
				<?php if ( count( $technopay_tabs ) > 1 ) : ?>
					<div class="tabs" role="tablist" aria-label="<?php esc_attr_e( 'دسته‌بندی مطالب', 'technopay' ); ?>" data-tabs>
						<?php $technopay_first = true; ?>
						<?php foreach ( $technopay_tabs as $technopay_key => $technopay_tab ) : ?>
							<button type="button" class="chip<?php echo $technopay_first ? ' is-active' : ''; ?>" role="tab" id="tab-<?php echo esc_attr( $technopay_key ); ?>" aria-controls="panel-<?php echo esc_attr( $technopay_key ); ?>" aria-selected="<?php echo $technopay_first ? 'true' : 'false'; ?>" tabindex="<?php echo $technopay_first ? '0' : '-1'; ?>"><?php echo esc_html( $technopay_tab['label'] ); ?></button>
							<?php $technopay_first = false; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php
			global $post;
			$technopay_first = true;
			foreach ( $technopay_tabs as $technopay_key => $technopay_tab ) :
				?>
				<div class="tab-panel" role="tabpanel" id="panel-<?php echo esc_attr( $technopay_key ); ?>" aria-labelledby="tab-<?php echo esc_attr( $technopay_key ); ?>"<?php echo $technopay_first ? '' : ' hidden'; ?>>
					<div class="posts-grid">
						<?php
						foreach ( $technopay_tab['posts'] as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $post );
							get_template_part( 'template-parts/card' );
						endforeach;
						wp_reset_postdata();
						?>
					</div>
					<?php
					$technopay_url = 'all' === $technopay_key ? $technopay_more_url : $technopay_tab['url'];
					if ( $technopay_url ) :
						?>
						<div class="load-more">
							<a class="btn btn--ghost" href="<?php echo esc_url( $technopay_url ); ?>"><?php esc_html_e( 'مشاهده مطالب بیشتر', 'technopay' ); ?><?php technopay_icon( 'arrow-left' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
				<?php
				$technopay_first = false;
			endforeach;
			?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</section>
