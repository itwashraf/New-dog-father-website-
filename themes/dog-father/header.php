<?php
/**
 * Site header.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$df_book_url = dfather_url( dfather_home( 'hero_primary_url', '/book-now/' ), '/book-now/' );
$df_phone    = dfather_info( 'phone', '' );
$df_name     = dfather_info( 'business_name', get_bloginfo( 'name' ) );
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

<a class="df-skip screen-reader-text" href="#df-content"><?php esc_html_e( 'Skip to content', 'dog-father' ); ?></a>

<header class="df-header" id="df-header">
	<div class="df-header__inner">
		<div class="df-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="df-brand__text" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php
					// Style the last word in brand accent if multi-word.
					$df_words = explode( ' ', (string) $df_name );
					if ( count( $df_words ) > 1 ) {
						$df_last = array_pop( $df_words );
						echo esc_html( implode( ' ', $df_words ) ) . ' <span>' . esc_html( $df_last ) . '</span>';
					} else {
						echo esc_html( $df_name );
					}
					?>
				</a>
			<?php endif; ?>
		</div>

		<button class="df-burger" aria-controls="df-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'dog-father' ); ?>">
			<span></span><span></span><span></span>
		</button>

		<nav class="df-nav" id="df-nav" aria-label="<?php esc_attr_e( 'Primary', 'dog-father' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'df-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<ul class="df-menu">';
				echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'dog-father' ) . '</a></li>';
				echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Set up your menu', 'dog-father' ) . '</a></li>';
				echo '</ul>';
			}
			?>
			<div class="df-header__actions">
				<?php if ( $df_phone ) : ?>
					<a class="df-header__phone" href="<?php echo esc_attr( dfather_tel( $df_phone ) ); ?>"><?php echo esc_html( $df_phone ); ?></a>
				<?php endif; ?>
				<a class="df-btn df-btn--primary" href="<?php echo esc_url( $df_book_url ); ?>"><?php esc_html_e( 'Book Now', 'dog-father' ); ?></a>
			</div>
		</nav>
	</div>
</header>

<div id="df-content">
