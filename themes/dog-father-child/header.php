<?php
/**
 * Site header for the Dog Father Child theme.
 *
 * This template makes the luxury header work on FREE Elementor (no Elementor
 * Pro / Theme Builder required). It renders site-wide on every page that uses
 * a theme template (Hello Elementor's "Default" or "Elementor Full Width" page
 * templates). Pages set to "Elementor Canvas" intentionally hide it.
 *
 * Menus: assign a menu to the "Primary Menu" location under Appearance > Menus.
 *
 * @package DogFatherChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the Book Now URL: the page with slug "book-now" if it exists,
 * otherwise the home page.
 */
$dfchild_book_page = get_page_by_path( 'book-now' );
$dfchild_book_url  = $dfchild_book_page ? get_permalink( $dfchild_book_page ) : home_url( '/' );

/**
 * Pull contact details from the Control Center plugin when available.
 */
$dfchild_phone = function_exists( 'dfcc_get_setting' ) ? dfcc_get_setting( 'dfcc_global_settings', 'phone', '' ) : '';
$dfchild_name  = function_exists( 'dfcc_get_setting' ) ? dfcc_get_setting( 'dfcc_global_settings', 'business_name', '' ) : '';
if ( ! $dfchild_name ) {
	$dfchild_name = get_bloginfo( 'name' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

<a class="dfcc-skip-link screen-reader-text" href="#dfcc-content"><?php esc_html_e( 'Skip to content', 'dog-father-child' ); ?></a>

<header class="dfcc-site-header" id="dfcc-site-header">
	<div class="dfcc-header-inner">

		<div class="dfcc-header-brand">
			<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="dfcc-brand-text" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo esc_html( $dfchild_name ); ?>
				</a>
			<?php endif; ?>
		</div>

		<button class="dfcc-nav-toggle" aria-controls="dfcc-primary-nav" aria-expanded="false">
			<span class="dfcc-nav-toggle-bar"></span>
			<span class="dfcc-nav-toggle-bar"></span>
			<span class="dfcc-nav-toggle-bar"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'dog-father-child' ); ?></span>
		</button>

		<nav class="dfcc-primary-nav" id="dfcc-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'dog-father-child' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'dfcc-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<ul class="dfcc-menu"><li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Set your menu in Appearance → Menus', 'dog-father-child' ) . '</a></li></ul>';
			}
			?>

			<div class="dfcc-header-actions">
				<?php if ( $dfchild_phone ) : ?>
					<a class="dfcc-header-phone" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dfchild_phone ) ); ?>">
						<?php echo esc_html( $dfchild_phone ); ?>
					</a>
				<?php endif; ?>
				<a class="dfcc-btn dfcc-btn-primary" href="<?php echo esc_url( $dfchild_book_url ); ?>">
					<?php esc_html_e( 'Book Now', 'dog-father-child' ); ?>
				</a>
			</div>
		</nav>

	</div>
</header>

<main id="dfcc-content" class="dfcc-site-content">
