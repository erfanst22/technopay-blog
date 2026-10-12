<?php
/**
 * کنترل‌های اختصاصی سفارشی‌سازی (فقط وقتی کلاس‌های سفارشی‌ساز بارگذاری شده باشند).
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'TechnoPay_Note_Control' ) ) {
	/**
	 * یادداشت توضیحی (بدون مقدار) داخل پنل سفارشی‌سازی.
	 */
	class TechnoPay_Note_Control extends WP_Customize_Control {
		/**
		 * نوع کنترل.
		 *
		 * @var string
		 */
		public $type = 'technopay_note';

		/**
		 * خروجی کنترل: فقط عنوان و توضیح.
		 */
		protected function render_content() {
			if ( $this->label ) {
				echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
			}
			if ( $this->description ) {
				echo '<span class="description customize-control-description">' . wp_kses_post( $this->description ) . '</span>';
			}
		}
	}
}
