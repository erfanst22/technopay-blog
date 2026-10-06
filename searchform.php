<?php
/**
 * فرم جستجو.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

$technopay_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php technopay_icon( 'search' ); ?>
	<label class="sr-only" for="<?php echo esc_attr( $technopay_search_id ); ?>"><?php esc_html_e( 'عبارت جستجو', 'technopay' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $technopay_search_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجو در مقالات مجله…', 'technopay' ); ?>" autocomplete="off">
	<button type="submit" class="btn btn--primary"><?php esc_html_e( 'جستجو', 'technopay' ); ?></button>
</form>
