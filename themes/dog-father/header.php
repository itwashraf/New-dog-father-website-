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
$df_whatsapp = dfather_info( 'whatsapp', '' );
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
					<a class="df-header__icon df-header__icon--phone" href="<?php echo esc_attr( dfather_tel( $df_phone ) ); ?>" aria-label="<?php esc_attr_e( 'Call us', 'dog-father' ); ?>" title="<?php echo esc_attr( $df_phone ); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.62 10.79a15.53 15.53 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24 11.36 11.36 0 0 0 3.57.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.36 11.36 0 0 0 .57 3.57 1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg>
					</a>
				<?php endif; ?>
				<?php if ( $df_whatsapp ) : ?>
					<a class="df-header__icon df-header__icon--whatsapp" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $df_whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'dog-father' ); ?>" title="<?php esc_attr_e( 'Chat on WhatsApp', 'dog-father' ); ?>">
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.04 2a9.9 9.9 0 0 0-8.46 15.02L2 22l5.1-1.34A9.9 9.9 0 1 0 12.04 2Zm0 1.8a8.1 8.1 0 0 1 6.86 12.42l-.2.32.74 2.7-2.77-.73-.31.18a8.1 8.1 0 1 1-4.32-15.1Zm-3.2 3.86c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02 0 1.2.87 2.35.99 2.51.12.16 1.7 2.6 4.13 3.64.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.95-1.2-.72-.64-1.2-1.44-1.34-1.68-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.32-.76-1.8-.18-.42-.37-.42-.54-.43h-.46Z"/></svg>
					</a>
				<?php endif; ?>
				<a class="df-btn df-btn--primary" href="<?php echo esc_url( $df_book_url ); ?>"><?php esc_html_e( 'Book Now', 'dog-father' ); ?></a>
			</div>
		</nav>
	</div>
</header>

<div id="df-content">
