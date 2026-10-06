<?php
/**
 * صفحه اصلی: بنر تبلیغاتی دریافت اعتبار.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section container">
	<div class="cta">
		<div>
			<?php if ( technopay_option( 'cta_eyebrow' ) ) : ?>
				<span class="badge badge--glass"><?php technopay_icon( 'zap', 'icon--sm' ); ?><?php echo esc_html( technopay_option( 'cta_eyebrow' ) ); ?></span>
			<?php endif; ?>
			<h2><?php echo esc_html( technopay_option( 'cta_title' ) ); ?></h2>
			<p><?php echo esc_html( technopay_option( 'cta_text' ) ); ?></p>
			<div class="cta__actions">
				<?php if ( technopay_option( 'cta_btn_url' ) ) : ?>
					<a class="btn btn--white" href="<?php echo esc_url( technopay_option( 'cta_btn_url' ) ); ?>"><?php technopay_icon( 'card' ); ?><?php echo esc_html( technopay_option( 'cta_btn_text' ) ); ?></a>
				<?php endif; ?>
				<?php if ( technopay_option( 'cta_btn2_url' ) ) : ?>
					<a class="btn btn--outline-white" href="<?php echo esc_url( technopay_option( 'cta_btn2_url' ) ); ?>"><?php echo esc_html( technopay_option( 'cta_btn2_text' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="cta__stats">
			<?php for ( $technopay_i = 1; $technopay_i <= 4; $technopay_i++ ) : ?>
				<?php if ( technopay_option( "cta_stat_{$technopay_i}_value" ) ) : ?>
					<div class="stat">
						<strong><?php echo esc_html( technopay_option( "cta_stat_{$technopay_i}_value" ) ); ?></strong>
						<span><?php echo esc_html( technopay_option( "cta_stat_{$technopay_i}_label" ) ); ?></span>
					</div>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	</div>
</section>
