<?php
/**
 * Title: باکس نکته (سبز)
 * Slug: technopay/callout-tip
 * Categories: technopay
 * Keywords: نکته, callout, tip
 * Description: یک باکس رنگی برای نکات مهم داخل مقاله.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"callout callout--tip"} -->
<div class="wp-block-group callout callout--tip"><!-- wp:paragraph -->
<p><strong><?php esc_html_e( 'نکته:', 'technopay' ); ?></strong> <?php esc_html_e( 'متن نکته را اینجا بنویسید.', 'technopay' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
