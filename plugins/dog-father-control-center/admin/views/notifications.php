<?php
/**
 * Notifications settings view.
 *
 * @package DogFatherControlCenter
 * @var array $settings Saved notification settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recipients = isset( $settings['recipients'] ) ? $settings['recipients'] : '';
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-email-alt"></span><?php esc_html_e( 'Notifications', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Decide who is emailed and when.', 'dog-father-control-center' ); ?></p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Notification settings saved.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'dfcc_save_notifications', 'dfcc_notifications_nonce' ); ?>
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Recipients', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label for="recipients"><?php esc_html_e( 'Admin Email Recipients', 'dog-father-control-center' ); ?></label>
				<textarea id="recipients" name="recipients" rows="3" placeholder="owner@example.com, manager@example.com"><?php echo esc_textarea( $recipients ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Separate addresses with commas. Leave empty to use the site admin email.', 'dog-father-control-center' ); ?></p>
			</div>

			<h2><?php esc_html_e( 'Events', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label for="on_new_booking">
					<input type="checkbox" id="on_new_booking" name="on_new_booking" value="1" <?php checked( ! empty( $settings['on_new_booking'] ) ); ?> />
					<?php esc_html_e( 'Email when a new booking is received', 'dog-father-control-center' ); ?>
				</label>
			</div>
			<div class="dfcc-field">
				<label for="on_status_change">
					<input type="checkbox" id="on_status_change" name="on_status_change" value="1" <?php checked( ! empty( $settings['on_status_change'] ) ); ?> />
					<?php esc_html_e( 'Email when a booking status changes', 'dog-father-control-center' ); ?>
				</label>
			</div>
		</div>
		<p><button type="submit" class="dfcc-button"><?php esc_html_e( 'Save Notifications', 'dog-father-control-center' ); ?></button></p>
	</form>
</div>
