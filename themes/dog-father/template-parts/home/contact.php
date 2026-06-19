<?php
/**
 * Home: contact + map.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'contact' ) ) {
	return;
}

$df_title    = dfather_home( 'contact_title', __( 'Visit Us', 'dog-father' ) );
$df_phone    = dfather_info( 'phone', '' );
$df_whatsapp = dfather_info( 'whatsapp', '' );
$df_email    = dfather_info( 'email', '' );
$df_address  = dfather_info( 'address', '' );
$df_hours    = dfather_info( 'opening_hours', '' );
$df_map      = dfather_info( 'maps_embed', '' );
?>
<section class="df-section df-reveal" id="contact" style="background:var(--df-surface-1);border-top:1px solid var(--df-border-soft);">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php echo esc_html( dfather_home( 'contact_eyebrow', __( 'Get in Touch', 'dog-father' ) ) ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-contact__grid">
			<div class="df-contact__info">
				<ul>
					<?php if ( $df_address ) : ?>
						<li><span class="dashicons dashicons-location"></span><span><?php echo esc_html( $df_address ); ?></span></li>
					<?php endif; ?>
					<?php if ( $df_phone ) : ?>
						<li><span class="dashicons dashicons-phone"></span><a href="<?php echo esc_attr( dfather_tel( $df_phone ) ); ?>"><?php echo esc_html( $df_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $df_whatsapp ) : ?>
						<li><span class="dashicons dashicons-whatsapp"></span><a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $df_whatsapp ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $df_whatsapp ); ?></a></li>
					<?php endif; ?>
					<?php if ( $df_email ) : ?>
						<li><span class="dashicons dashicons-email"></span><a href="mailto:<?php echo esc_attr( $df_email ); ?>"><?php echo esc_html( $df_email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $df_hours ) : ?>
						<li><span class="dashicons dashicons-clock"></span><span><?php echo nl2br( esc_html( $df_hours ) ); ?></span></li>
					<?php endif; ?>
					<?php if ( ! $df_address && ! $df_phone && ! $df_email ) : ?>
						<li><span class="dashicons dashicons-info"></span><span><?php esc_html_e( 'Add your contact details in Dog Father → Global Settings.', 'dog-father' ); ?></span></li>
					<?php endif; ?>
				</ul>
				<a class="df-btn df-btn--primary" href="<?php echo esc_url( dfather_url( '/book-now/', '/book-now/' ) ); ?>"><?php esc_html_e( 'Book a Stay', 'dog-father' ); ?></a>
			</div>
			<div class="df-contact__map">
				<?php
				if ( $df_map ) {
					// Stored as an iframe/embed; allow safe iframe output.
					echo wp_kses(
						$df_map,
						array(
							'iframe' => array(
								'src'             => true,
								'width'           => true,
								'height'          => true,
								'style'           => true,
								'allowfullscreen' => true,
								'loading'         => true,
								'referrerpolicy'  => true,
								'frameborder'     => true,
								'title'           => true,
							),
						)
					);
				}
				?>
			</div>
		</div>
	</div>
</section>
