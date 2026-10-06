<?php
/**
 * کارت افقی نوشته (آرشیو و جستجو).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'post-row' ); ?>>
	<div class="card__media">
		<a href="<?php the_permalink(); ?>" class="thumb" tabindex="-1" aria-hidden="true"><?php technopay_thumbnail( 'technopay-card' ); ?></a>
		<?php technopay_category_badge(); ?>
	</div>
	<div class="post-row__body">
		<?php technopay_post_meta( array( 'date', 'reading' ) ); ?>
		<h2 class="post-row__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-row__excerpt"><?php echo esc_html( technopay_excerpt( 34 ) ); ?></p>
		<div class="card__foot">
			<?php technopay_author_chip(); ?>
			<?php technopay_post_meta( array( 'views', 'comments' ) ); ?>
		</div>
	</div>
</article>
