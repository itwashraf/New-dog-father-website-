<?php
/**
 * Home: trust indicators bar.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'trust' ) ) {
	return;
}

$df_fallback = array(
	array( 'dashicons-shield-alt', __( 'Fully Insured', 'dog-father' ), __( 'Licensed & bonded', 'dog-father' ) ),
	array( 'dashicons-heart', __( '24/7 Vet Care', 'dog-father' ), __( 'On-call always', 'dog-father' ) ),
	array( 'dashicons-camera', __( 'Daily Updates', 'dog-father' ), __( 'Photos & reports', 'dog-father' ) ),
	array( 'dashicons-star-filled', __( '4.9★ Rated', 'dog-father' ), __( 'By dog parents', 'dog-father' ) ),
);

$df_items = array();
for ( $df_i = 1; $df_i <= 4; $df_i++ ) {
	$df_title = dfather_home( 'trust' . $df_i . '_title', $df_fallback[ $df_i - 1 ][1] );
	if ( '' === trim( (string) $df_title ) ) {
		continue; // Clear an item's title in the panel to remove it.
	}
	$df_items[] = array(
		'icon'  => dfather_home( 'trust' . $df_i . '_icon', $df_fallback[ $df_i - 1 ][0] ),
		'title' => $df_title,
		'sub'   => dfather_home( 'trust' . $df_i . '_sub', $df_fallback[ $df_i - 1 ][2] ),
	);
}
?>
<section class="df-trust df-reveal">
	<div class="df-container">
		<div class="df-trust__grid">
			<?php foreach ( $df_items as $item ) : ?>
				<div class="df-trust__item">
					<?php echo dfather_icon_html( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<div>
						<strong><?php echo esc_html( $item['title'] ); ?></strong>
						<span><?php echo esc_html( $item['sub'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
