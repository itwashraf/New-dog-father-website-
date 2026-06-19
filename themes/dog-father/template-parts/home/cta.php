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

$df_bg_id  = (int) dfather_home( 'cta_bg_id', 0 );
$df_bg_url = $df_bg_id ? wp_get_attachment_image_url( $df_bg_id, 'dfather-wide' ) : '';
if ( ! $df_bg_url ) {
	$df_bg_url = dfather_default_image( 'cta' );
}
?>
<section class="df-section df-cta df-reveal<?php echo $df_bg_url ? ' df-cta--image' : ''; ?>"<?php echo $df_bg_url ? ' style="background-image:url(\'' . esc_url( $df_bg_url ) . '\');"' : ''; ?>>
	<div class="df-container">
		<h2><?php echo esc_html( $df_title ); ?></h2>
		<p><?php echo esc_html( $df_text ); ?></p>
		<a class="df-btn df-btn--primary" href="<?php echo esc_url( $df_url ); ?>"><?php echo esc_html( $df_label ); ?></a>
	</div>
</section>
