<?php
/**
 * Home: about section.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'about' ) ) {
	return;
}

$df_img_id  = (int) dfather_home( 'about_image_id', 0 );
$df_img_url = $df_img_id ? wp_get_attachment_image_url( $df_img_id, 'dfather-wide' ) : '';
// Fall back to a default photo so the About media panel is never empty.
if ( ! $df_img_url ) {
	$df_img_url = dfather_default_image( 'about' );
}
$df_eyebrow = dfather_home( 'about_eyebrow', __( 'About The Dog Father', 'dog-father' ) );
$df_title   = dfather_home( 'about_title', __( 'A Five-Star Home Away From Home', 'dog-father' ) );
$df_text    = dfather_home( 'about_text', __( 'At The Dog Father Hotel we blend luxury hospitality with expert canine care. Every guest enjoys a private suite, daily enrichment, gourmet meals, and round-the-clock veterinary supervision — so you can travel knowing your best friend is in the safest, most loving hands.', 'dog-father' ) );
?>
<section class="df-section df-about df-reveal">
	<div class="df-container">
		<div class="df-about__grid">
			<div class="df-about__media">
				<?php if ( $df_img_url ) : ?>
					<img src="<?php echo esc_url( $df_img_url ); ?>" alt="<?php echo esc_attr( $df_title ); ?>" loading="lazy" />
				<?php endif; ?>
				<div class="df-about__badge"><b><?php echo esc_html( dfather_home( 'stat3_number', '15+' ) ); ?></b><?php esc_html_e( 'Years of Excellence', 'dog-father' ); ?></div>
			</div>
			<div>
				<span class="df-eyebrow"><?php echo esc_html( $df_eyebrow ); ?></span>
				<h2><?php echo esc_html( $df_title ); ?></h2>
				<p><?php echo esc_html( $df_text ); ?></p>
				<a class="df-btn df-btn--primary" href="<?php echo esc_url( dfather_url( '/about/', '/about/' ) ); ?>"><?php esc_html_e( 'Our Story', 'dog-father' ); ?></a>
			</div>
		</div>
	</div>
</section>
