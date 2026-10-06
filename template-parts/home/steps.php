<?php
/**
 * صفحه اصلی: مراحل دریافت اعتبار.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section container">
	<?php technopay_section_head( technopay_option( 'steps_title' ), '', technopay_option( 'steps_subtitle' ) ); ?>
	<ol class="guides">
		<?php for ( $technopay_i = 1; $technopay_i <= 4; $technopay_i++ ) : ?>
			<?php if ( technopay_option( "step_{$technopay_i}_title" ) ) : ?>
				<li class="guide">
					<span class="guide__num" aria-hidden="true"><?php echo esc_html( technopay_fa_digits( sprintf( '%02d', $technopay_i ) ) ); ?></span>
					<h3><?php echo esc_html( technopay_option( "step_{$technopay_i}_title" ) ); ?></h3>
					<p><?php echo esc_html( technopay_option( "step_{$technopay_i}_text" ) ); ?></p>
				</li>
			<?php endif; ?>
		<?php endfor; ?>
	</ol>
</section>
