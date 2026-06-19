<?php
/**
 * 404 template.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="df-404">
	<div class="df-container">
		<h1 class="df-gradient-text">404</h1>
		<h2><?php echo esc_html( dfather_info( 'error_404_title', __( 'This page went walkies.', 'dog-father' ) ) ); ?></h2>
		<p class="df-prose" style="margin:0 auto 28px;"><?php echo esc_html( dfather_info( 'error_404_text', __( 'The page you are looking for could not be found. Let’s get you back home.', 'dog-father' ) ) ); ?></p>
		<a class="df-btn df-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( dfather_info( 'error_404_button', __( 'Back to Home', 'dog-father' ) ) ); ?></a>
	</div>
</section>
<?php
get_footer();
