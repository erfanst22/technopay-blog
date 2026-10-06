<?php
/**
 * هدر سایت.
 *
 * @package TechnoPay
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'پرش به محتوای اصلی', 'technopay' ); ?></a>

<?php if ( technopay_option( 'topbar_enabled' ) ) : ?>
	<div class="topbar">
		<div class="container topbar__inner">
			<div class="topbar__date"><?php technopay_icon( 'calendar', 'icon--sm' ); ?><span><?php echo esc_html( technopay_today_label() ); ?></span></div>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'topbar',
					'container'      => 'nav',
					'container_class' => 'topbar__nav',
					'container_aria_label' => __( 'لینک‌های سریع', 'technopay' ),
					'menu_class'     => 'topbar__links',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
			<?php $technopay_socials = technopay_social_links(); ?>
			<?php if ( $technopay_socials ) : ?>
				<div class="topbar__social">
					<?php foreach ( $technopay_socials as $technopay_social ) : ?>
						<a href="<?php echo esc_url( $technopay_social['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $technopay_social['label'] ); ?>"><?php technopay_icon( $technopay_social['icon'], 'icon--sm' ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<header class="site-header">
	<div class="container header__inner">
		<button type="button" class="icon-btn menu-toggle" data-drawer-open aria-label="<?php esc_attr_e( 'باز کردن منو', 'technopay' ); ?>"><?php technopay_icon( 'menu' ); ?></button>

		<?php get_template_part( 'template-parts/logo' ); ?>

		<nav class="nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'technopay' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location'    => 'primary',
					'container'         => false,
					'menu_class'        => 'nav__list',
					'depth'             => 3,
					'fallback_cb'       => 'technopay_menu_fallback',
					'technopay_context' => 'desktop',
				)
			);
			?>
		</nav>

		<div class="header__actions">
			<button type="button" class="icon-btn" data-search-open aria-label="<?php esc_attr_e( 'جستجو', 'technopay' ); ?>"><?php technopay_icon( 'search' ); ?></button>
			<button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="<?php esc_attr_e( 'تغییر حالت روشن و تاریک', 'technopay' ); ?>"><?php technopay_icon( 'sun', 'icon-sun' ); ?><?php technopay_icon( 'moon', 'icon-moon' ); ?></button>
			<?php if ( technopay_option( 'header_cta_url' ) ) : ?>
				<a class="btn btn--primary header__cta" href="<?php echo esc_url( technopay_option( 'header_cta_url' ) ); ?>"><?php technopay_icon( 'card' ); ?><span><?php echo esc_html( technopay_option( 'header_cta_text' ) ); ?></span></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<div class="drawer-backdrop" data-drawer-close></div>
<aside class="drawer" id="mobile-menu" aria-label="<?php esc_attr_e( 'منوی موبایل', 'technopay' ); ?>">
	<div class="drawer__head">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<button type="button" class="icon-btn" data-drawer-close aria-label="<?php esc_attr_e( 'بستن منو', 'technopay' ); ?>"><?php technopay_icon( 'close' ); ?></button>
	</div>
	<div class="drawer__body">
		<?php get_search_form(); ?>
		<?php
		wp_nav_menu(
			array(
				'theme_location'    => 'primary',
				'container'         => 'nav',
				'container_aria_label' => __( 'منوی موبایل', 'technopay' ),
				'menu_class'        => 'drawer-menu',
				'menu_id'           => 'drawer-menu',
				'depth'             => 3,
				'fallback_cb'       => 'technopay_menu_fallback',
				'technopay_context' => 'drawer',
			)
		);
		?>
	</div>
	<div class="drawer__foot">
		<?php if ( technopay_option( 'header_cta_url' ) ) : ?>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( technopay_option( 'header_cta_url' ) ); ?>"><?php technopay_icon( 'card' ); ?><?php echo esc_html( technopay_option( 'header_cta_text' ) ); ?></a>
		<?php endif; ?>
	</div>
</aside>

<div class="search-overlay" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php esc_attr_e( 'جستجو در مجله', 'technopay' ); ?>">
	<div class="search-box">
		<?php get_search_form(); ?>
		<?php
		$technopay_search_tags = get_tags(
			array(
				'orderby' => 'count',
				'order'   => 'DESC',
				'number'  => 6,
			)
		);
		?>
		<?php if ( $technopay_search_tags ) : ?>
			<p class="search-box__hint"><?php esc_html_e( 'جستجوهای پرتکرار:', 'technopay' ); ?></p>
			<div class="search-box__tags">
				<?php foreach ( $technopay_search_tags as $technopay_tag ) : ?>
					<a class="chip" href="<?php echo esc_url( get_tag_link( $technopay_tag ) ); ?>"><?php echo esc_html( $technopay_tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
