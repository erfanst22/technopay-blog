<?php
/**
 * Title: باکس هشدار (زرد)
 * Slug: technopay/callout-warning
 * Categories: technopay
 * Keywords: هشدار, توجه, callout, warning
 * Description: یک باکس رنگی برای هشدارها و موارد قابل توجه.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"callout callout--warn"} -->
<div class="wp-block-group callout callout--warn"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'توجه:', 'technopay' ); ?></strong> <?php esc_html_e( 'متن هشدار را اینجا بنویسید.', 'technopay' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
