<?php
/**
 * کارت فشرده (تصویر، نشان دسته، عنوان تک‌خطی، متا) برای ردیف‌های صفحه اصلی.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_meta = isset( $args['meta'] ) ? $args['meta'] : 'date';
?>
<article class="tile">
	<a href="<?php the_permalink(); ?>" class="tile__thumb" tabindex="-1" aria-hidden="true">
		<?php
		technopay_thumbnail(
			'technopay-tile',
			array(
				'sizes' => '(min-width: 1180px) 184px, (min-width: 700px) 30vw, 46vw',
			)
		);
		?>
	</a>
	<?php technopay_category_badge( 'badge badge--solid tile__badge' ); ?>
	<h3 class="tile__title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
	<div class="tile__meta">
		<?php if ( 'views' === $technopay_meta ) : ?>
			<?php technopay_icon( 'eye' ); ?>
			<span><span class="sr-only"><?php esc_html_e( 'تعداد بازدید:', 'technopay' ); ?></span> <?php echo esc_html( technopay_number( technopay_get_views() ) ); ?></span>
		<?php elseif ( 'comments' === $technopay_meta ) : ?>
			<?php technopay_icon( 'message' ); ?>
			<span><span class="sr-only"><?php esc_html_e( 'تعداد دیدگاه:', 'technopay' ); ?></span> <?php echo esc_html( technopay_number( get_comments_number() ) ); ?></span>
		<?php else : ?>
			<?php technopay_icon( 'calendar' ); ?>
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( technopay_post_date( null, 'numeric' ) ); ?></time>
		<?php endif; ?>
	</div>
</article>
