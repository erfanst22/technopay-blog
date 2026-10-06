<?php
/**
 * کارت نوشته (نمایش شبکه‌ای).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_tag = tag_escape( isset( $args['heading'] ) ? $args['heading'] : 'h3' );
?>
<article <?php post_class( 'card' ); ?>>
	<div class="card__media">
		<a href="<?php the_permalink(); ?>" class="thumb" tabindex="-1" aria-hidden="true"><?php technopay_thumbnail( 'technopay-card' ); ?></a>
		<?php technopay_category_badge(); ?>
	</div>
	<div class="card__body">
		<?php technopay_post_meta( array( 'date', 'reading' ) ); ?>
		<<?php echo $technopay_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $technopay_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<p class="card__excerpt"><?php echo esc_html( technopay_excerpt( 26 ) ); ?></p>
		<div class="card__foot">
			<?php technopay_author_chip(); ?>
			<?php technopay_post_meta( array( 'views' ) ); ?>
		</div>
	</div>
</article>
