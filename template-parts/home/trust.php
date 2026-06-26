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

$df_items = array(
	array( 'icon' => 'dashicons-shield-alt', 'title' => __( 'Fully Insured', 'dog-father' ), 'sub' => __( 'Licensed & bonded', 'dog-father' ) ),
	array( 'icon' => 'dashicons-heart', 'title' => __( '24/7 Vet Care', 'dog-father' ), 'sub' => __( 'On-call always', 'dog-father' ) ),
	array( 'icon' => 'dashicons-camera', 'title' => __( 'Daily Updates', 'dog-father' ), 'sub' => __( 'Photos & reports', 'dog-father' ) ),
	array( 'icon' => 'dashicons-star-filled', 'title' => __( '4.9★ Rated', 'dog-father' ), 'sub' => __( 'By dog parents', 'dog-father' ) ),
);
?>
<section class="df-trust df-reveal">
	<div class="df-container">
		<div class="df-trust__grid">
			<?php foreach ( $df_items as $item ) : ?>
				<div class="df-trust__item">
					<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
					<div>
						<strong><?php echo esc_html( $item['title'] ); ?></strong>
						<span><?php echo esc_html( $item['sub'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
