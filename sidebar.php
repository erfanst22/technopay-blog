<?php
/**
 * سایدبار اصلی.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'سایدبار', 'technopay' ); ?>">
	<?php
	if ( is_active_sidebar( 'technopay-main' ) ) {
		dynamic_sidebar( 'technopay-main' );
	} else {
		technopay_default_main_sidebar();
	}
	?>
</aside>
