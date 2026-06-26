<?php
/**
 * Home: call to action.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'cta' ) ) {
	return;
}

$df_title = dfather_home( 'cta_title', __( 'Ready to Give Your Dog the Five-Star Treatment?', 'dog-father' ) );
$df_text  = dfather_home( 'cta_text', __( 'Reserve a luxury suite today and discover why dog parents trust The Dog Father Hotel.', 'dog-father' ) );
$df_label = dfather_home( 'cta_button_label', __( 'Book Now', 'dog-father' ) );
$df_url   = dfather_url( dfather_home( 'cta_button_url', '/book-now/' ), '/book-now/' );
?>
<section class="df-section df-cta df-reveal">
	<div class="df-container">
		<h2><?php echo esc_html( $df_title ); ?></h2>
		<p><?php echo esc_html( $df_text ); ?></p>
		<a class="df-btn df-btn--primary" href="<?php echo esc_url( $df_url ); ?>"><?php echo esc_html( $df_label ); ?></a>
	</div>
</section>
