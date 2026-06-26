<?php
/**
 * Home: why choose us.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'why' ) ) {
	return;
}

$df_items = array(
	array( 'dashicons-shield', __( 'Safety First', 'dog-father' ), __( 'CCTV-monitored, secure facilities with trained staff present around the clock.', 'dog-father' ) ),
	array( 'dashicons-heart', __( 'Genuine Love', 'dog-father' ), __( 'Small guest-to-carer ratios mean every dog gets real attention and affection.', 'dog-father' ) ),
	array( 'dashicons-clock', __( '24/7 Supervision', 'dog-father' ), __( 'Never alone — overnight carers and on-call veterinary support, always.', 'dog-father' ) ),
	array( 'dashicons-smartphone', __( 'Stay Connected', 'dog-father' ), __( 'Daily photos, videos and updates sent straight to your phone.', 'dog-father' ) ),
);
?>
<section class="df-section df-section--tight df-reveal" style="background:var(--df-surface-1);border-top:1px solid var(--df-border-soft);border-bottom:1px solid var(--df-border-soft);">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php esc_html_e( 'The Difference', 'dog-father' ); ?></span>
			<h2 class="df-section-title"><?php esc_html_e( 'Why Dog Parents Choose Us', 'dog-father' ); ?></h2>
		</div>
		<div class="df-grid df-grid--4">
			<?php foreach ( $df_items as $i ) : ?>
				<div class="df-feature">
					<div class="df-feature__icon"><span class="dashicons <?php echo esc_attr( $i[0] ); ?>"></span></div>
					<h3><?php echo esc_html( $i[1] ); ?></h3>
					<p><?php echo esc_html( $i[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
