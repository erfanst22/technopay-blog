<?php
/**
 * کارت تصویری با متن روی تصویر (بخش ویژه).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_large = ! empty( $args['large'] );
$technopay_tag   = tag_escape( isset( $args['heading'] ) ? $args['heading'] : 'h3' );
$technopay_img   = $technopay_large ? array(
	'loading'       => 'eager',
	'fetchpriority' => 'high',
) : array();
?>
<article class="feature<?php echo $technopay_large ? ' feature--lg' : ''; ?>">
	<?php technopay_thumbnail( $technopay_large ? 'technopay-hero' : 'technopay-card', $technopay_img ); ?>
	<div class="feature__body">
		<?php technopay_category_badge( 'badge badge--glass' ); ?>
		<<?php echo $technopay_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="feature__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $technopay_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( $technopay_large ) : ?>
			<p class="feature__excerpt"><?php echo esc_html( technopay_excerpt( 30 ) ); ?></p>
			<?php technopay_post_meta( array( 'date', 'reading', 'views' ) ); ?>
		<?php else : ?>
			<?php technopay_post_meta( array( 'date' ) ); ?>
		<?php endif; ?>
	</div>
</article>
