<?php
/**
 * Home: statistics band.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'stats' ) ) {
	return;
}

$df_stats = array(
	array( dfather_home( 'stat1_number', '5,000+' ), dfather_home( 'stat1_label', __( 'Happy Guests', 'dog-father' ) ) ),
	array( dfather_home( 'stat2_number', '24/7' ), dfather_home( 'stat2_label', __( 'Veterinary Care', 'dog-father' ) ) ),
	array( dfather_home( 'stat3_number', '15+' ), dfather_home( 'stat3_label', __( 'Years of Excellence', 'dog-father' ) ) ),
	array( dfather_home( 'stat4_number', '4.9★' ), dfather_home( 'stat4_label', __( 'Average Rating', 'dog-father' ) ) ),
);
?>
<section class="df-section df-section--tight df-stats df-reveal">
	<div class="df-container">
		<div class="df-stats__grid">
			<?php foreach ( $df_stats as $s ) : ?>
				<div class="df-stat">
					<b class="df-gradient-text"><?php echo esc_html( $s[0] ); ?></b>
					<span><?php echo esc_html( $s[1] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
