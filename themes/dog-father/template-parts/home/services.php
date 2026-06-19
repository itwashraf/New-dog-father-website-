<?php
/**
 * Home: services section.
 *
 * Pulls live services from the Control Center; falls back to a curated default
 * set so the homepage looks complete before any content is added.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'services' ) ) {
	return;
}

$df_eyebrow = dfather_home( 'services_eyebrow', __( 'What We Offer', 'dog-father' ) );
$df_title   = dfather_home( 'services_title', __( 'Premium Services', 'dog-father' ) );

$df_service_args = array(
	'post_type'      => 'dfcc_service',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
);
if ( class_exists( 'DFCC_Services' ) ) {
	$df_service_args['meta_query'] = DFCC_Services::visible_meta_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
}
$df_services = get_posts( $df_service_args );

$df_defaults = array(
	array( 'dashicons-building', __( 'Luxury Boarding Suite', 'dog-father' ), __( 'Private climate-controlled suites with plush bedding and daily housekeeping.', 'dog-father' ) ),
	array( 'dashicons-pets', __( 'Doggy Day Care', 'dog-father' ), __( 'Supervised play, socialisation and enrichment in a safe, spotless environment.', 'dog-father' ) ),
	array( 'dashicons-heart', __( 'Veterinary Care', 'dog-father' ), __( 'On-site, on-call veterinary supervision and medication management 24/7.', 'dog-father' ) ),
	array( 'dashicons-awards', __( 'Training Academy', 'dog-father' ), __( 'Certified trainers offering obedience, behaviour and confidence programmes.', 'dog-father' ) ),
	array( 'dashicons-buddicons-activity', __( 'Spa & Grooming', 'dog-father' ), __( 'Premium baths, styling and pampering to keep your dog looking five-star.', 'dog-father' ) ),
	array( 'dashicons-car', __( 'Pickup & Drop-off', 'dog-father' ), __( 'Door-to-door luxury transport so your dog travels in total comfort.', 'dog-father' ) ),
);
?>
<section class="df-section df-reveal" id="services">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php echo esc_html( $df_eyebrow ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-grid df-grid--3">
			<?php
			if ( $df_services ) {
				foreach ( $df_services as $df_post ) {
					dfather_service_card( $df_post->ID );
				}
			} else {
				foreach ( $df_defaults as $d ) :
					?>
					<article class="df-service">
						<div class="df-service__icon"><span class="dashicons <?php echo esc_attr( $d[0] ); ?>"></span></div>
						<h3><?php echo esc_html( $d[1] ); ?></h3>
						<p><?php echo esc_html( $d[2] ); ?></p>
					</article>
					<?php
				endforeach;
			}
			?>
		</div>
		<div style="text-align:center;margin-top:48px;">
			<a class="df-btn df-btn--ghost" href="<?php echo dfather_url( dfather_home( 'services_button_url', '/services/' ), '/services/' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dfather_url returns an escaped URL. ?>"><?php echo esc_html( dfather_home( 'services_button_label', __( 'View All Services', 'dog-father' ) ) ); ?></a>
		</div>
	</div>
</section>
