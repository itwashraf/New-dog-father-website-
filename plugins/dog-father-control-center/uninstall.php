<?php
/**
 * Uninstall routine for Dog Father Control Center.
 *
 * Runs only when the user deletes the plugin from the WordPress admin. It
 * removes plugin options. Content (bookings, dog profiles, services, gallery,
 * testimonials) is intentionally LEFT in place so deleting the plugin never
 * silently destroys business records — those are standard posts the owner can
 * review or export first. Set the DFCC_REMOVE_ALL_DATA constant to true in
 * wp-config.php before deleting if a full wipe is genuinely desired.
 *
 * @package DogFatherControlCenter
 */

// Exit if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$dfcc_options = array(
	'dfcc_version',
	'dfcc_flush_rewrite',
	'dfcc_theme_settings',
	'dfcc_global_settings',
	'dfcc_integration_settings',
	'dfcc_seo_settings',
	'dfcc_notification_settings',
	'dfcc_security_settings',
);

foreach ( $dfcc_options as $dfcc_option ) {
	delete_option( $dfcc_option );
}

// Optional hard wipe of all plugin content.
if ( defined( 'DFCC_REMOVE_ALL_DATA' ) && DFCC_REMOVE_ALL_DATA ) {
	$dfcc_post_types = array( 'dfcc_booking', 'dfcc_dog', 'dfcc_service', 'dfcc_gallery', 'dfcc_testimonial' );

	foreach ( $dfcc_post_types as $dfcc_pt ) {
		$dfcc_posts = get_posts(
			array(
				'post_type'      => $dfcc_pt,
				'post_status'    => 'any',
				'numberposts'    => -1,
				'fields'         => 'ids',
				'suppress_filters' => true,
			)
		);
		foreach ( $dfcc_posts as $dfcc_post_id ) {
			wp_delete_post( $dfcc_post_id, true );
		}
	}
}
