<?php
/**
 * Fired on plugin deactivation.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Deactivator.
 */
class DFCC_Deactivator {

	/**
	 * Deactivation routine.
	 *
	 * We intentionally keep all content and settings (CPT data, options) so the
	 * site owner never loses bookings or profiles by toggling the plugin. Only
	 * transient/scheduled state is cleared. Full teardown lives in uninstall.php.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Clear any scheduled cron events owned by the plugin.
		$timestamp = wp_next_scheduled( 'dfcc_daily_maintenance' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'dfcc_daily_maintenance' );
		}

		flush_rewrite_rules();
	}
}
