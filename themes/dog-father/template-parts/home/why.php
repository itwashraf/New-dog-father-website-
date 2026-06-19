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

$df_fallback = array(
	array( 'dashicons-shield', __( 'Safety First', 'dog-father' ), __( 'CCTV-monitored, secure facilities with trained staff present around the clock.', 'dog-father' ) ),
	array( 'dashicons-heart', __( 'Genuine Love', 'dog-father' ), __( 'Small guest-to-carer ratios mean every dog gets real attention and affection.', 'dog-father' ) ),
	array( 'dashicons-clock', __( '24/7 Supervision', 'dog-father' ), __( 'Never alone — overnight carers and on-call veterinary support, always.', 'dog-father' ) ),
	array( 'dashicons-smartphone', __( 'Stay Connected', 'dog-father' ), __( 'Daily photos, videos and updates sent straight to your phone.', 'dog-father' ) ),
);

$df_items = array();
for ( $df_i = 1; $df_i <= 4; $df_i++ ) {
	$df_title = dfather_home( 'why' . $df_i . '_title', $df_fallback[ $df_i - 1 ][1] );
	if ( '' === trim( (string) $df_title ) ) {
		continue; // Clear a reason's title in the panel to remove it.
	}
	$df_items[] = array(
		dfather_home( 'why' . $df_i . '_icon', $df_fallback[ $df_i - 1 ][0] ),
		$df_title,
		dfather_home( 'why' . $df_i . '_text', $df_fallback[ $df_i - 1 ][2] ),
	);
}

$df_eyebrow = dfather_home( 'why_eyebrow', __( 'The Difference', 'dog-father' ) );
$df_title   = dfather_home( 'why_title', __( 'Why Dog Parents Choose Us', 'dog-father' ) );
?>
<section class="df-section df-section--tight df-reveal" style="background:var(--df-surface-1);border-top:1px solid var(--df-border-soft);border-bottom:1px solid var(--df-border-soft);">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php echo esc_html( $df_eyebrow ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-grid df-grid--4">
			<?php foreach ( $df_items as $i ) : ?>
				<div class="df-feature">
					<div class="df-feature__icon"><?php echo dfather_icon_html( $i[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></div>
					<h3><?php echo esc_html( $i[1] ); ?></h3>
					<p><?php echo esc_html( $i[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
