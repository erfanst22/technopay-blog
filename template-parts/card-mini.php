<?php
/**
 * کارت کوچک افقی (لیست‌های فشرده).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="h-card">
	<a href="<?php the_permalink(); ?>" class="thumb" tabindex="-1" aria-hidden="true"><?php technopay_thumbnail( 'technopay-thumb' ); ?></a>
	<div class="h-card__body">
		<h3 class="h-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php technopay_post_meta( array( 'date', 'reading' ) ); ?>
	</div>
</article>
