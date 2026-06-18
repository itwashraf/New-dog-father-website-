<?php
/**
 * Integrations Center settings view.
 *
 * @package DogFatherControlCenter
 * @var array           $settings Saved integration settings.
 * @var DFCC_Integrations $module Owning module (for value/mask helpers).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a labelled text/secret field.
 *
 * @param DFCC_Integrations $module   Module helper.
 * @param array             $settings Settings.
 * @param string            $key      Field key.
 * @param string            $label    Label.
 * @param string            $desc     Description.
 * @param string            $type     Input type.
 * @return void
 */
function dfcc_integrations_field( $module, $settings, $key, $label, $desc = '', $type = 'text' ) {
	$value     = $module->field_value( $settings, $key );
	$is_secret = $module->is_secret( $key );
	$hint      = $is_secret ? $module->masked_hint( $settings, $key ) : '';
	?>
	<div class="dfcc-field">
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			id="<?php echo esc_attr( $key ); ?>"
			name="<?php echo esc_attr( $key ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			autocomplete="off"
			<?php echo $is_secret ? 'spellcheck="false"' : ''; ?>
		/>
		<?php if ( '' !== $hint ) : ?>
			<p class="description"><?php echo esc_html( sprintf( /* translators: %s: masked secret hint. */ __( 'Stored secret: %s — leave the mask to keep it, clear it to remove.', 'dog-father-control-center' ), $hint ) ); ?></p>
		<?php elseif ( '' !== $desc ) : ?>
			<p class="description"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render an on/off toggle field.
 *
 * @param array  $settings Settings.
 * @param string $key      Field key.
 * @param string $label    Label.
 * @return void
 */
function dfcc_integrations_toggle( $settings, $key, $label ) {
	$on = ! empty( $settings[ $key ] );
	?>
	<div class="dfcc-field">
		<label for="<?php echo esc_attr( $key ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $on ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
	</div>
	<?php
}

$enc_value      = isset( $settings['smtp_encryption'] ) ? $settings['smtp_encryption'] : 'tls';
$sms_provider   = isset( $settings['sms_provider'] ) ? $settings['sms_provider'] : 'none';
$webhook_urls   = isset( $settings['webhook_urls'] ) ? $settings['webhook_urls'] : '';
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title">
				<span class="dashicons dashicons-admin-plugins"></span>
				<?php esc_html_e( 'Integrations Center', 'dog-father-control-center' ); ?>
			</h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Connect payments, analytics, maps and messaging. Secret keys are masked once saved.', 'dog-father-control-center' ); ?></p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Integration settings saved.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'dfcc_save_integrations', 'dfcc_integrations_nonce' ); ?>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Payments', 'dog-father-control-center' ); ?></h2>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'Stripe', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'stripe_enabled', __( 'Enable Stripe', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'stripe_publishable', __( 'Publishable Key', 'dog-father-control-center' ), 'pk_live_…' );
			dfcc_integrations_field( $module, $settings, 'stripe_secret', __( 'Secret Key', 'dog-father-control-center' ), '', 'password' );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'PayMob', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'paymob_enabled', __( 'Enable PayMob', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'paymob_api_key', __( 'API Key', 'dog-father-control-center' ), '', 'password' );
			dfcc_integrations_field( $module, $settings, 'paymob_integration_id', __( 'Integration ID', 'dog-father-control-center' ) );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'PayPal', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'paypal_enabled', __( 'Enable PayPal', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'paypal_client_id', __( 'Client ID', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'paypal_secret', __( 'Secret', 'dog-father-control-center' ), '', 'password' );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'HyperPay', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'hyperpay_enabled', __( 'Enable HyperPay', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'hyperpay_entity_id', __( 'Entity ID', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'hyperpay_access_token', __( 'Access Token', 'dog-father-control-center' ), '', 'password' );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'Moyasar', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'moyasar_enabled', __( 'Enable Moyasar', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'moyasar_publishable', __( 'Publishable Key', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'moyasar_secret', __( 'Secret Key', 'dog-father-control-center' ), '', 'password' );
			?>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Analytics & Pixels', 'dog-father-control-center' ); ?></h2>
			<?php
			dfcc_integrations_field( $module, $settings, 'ga4_id', __( 'Google Analytics 4 Measurement ID', 'dog-father-control-center' ), 'G-XXXXXXXXXX' );
			dfcc_integrations_field( $module, $settings, 'gtm_id', __( 'Google Tag Manager Container ID', 'dog-father-control-center' ), 'GTM-XXXXXXX' );
			dfcc_integrations_field( $module, $settings, 'gsc_code', __( 'Google Search Console Verification Code', 'dog-father-control-center' ), __( 'The content value of the verification meta tag.', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'clarity_id', __( 'Microsoft Clarity Project ID', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'meta_pixel_id', __( 'Meta (Facebook) Pixel ID', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'tiktok_pixel_id', __( 'TikTok Pixel ID', 'dog-father-control-center' ) );
			?>
			<p class="description"><?php esc_html_e( 'Tracking tags are not output for logged-in administrators, so your own visits stay out of the data.', 'dog-father-control-center' ); ?></p>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Maps', 'dog-father-control-center' ); ?></h2>
			<?php
			dfcc_integrations_field( $module, $settings, 'maps_api_key', __( 'Google Maps API Key', 'dog-father-control-center' ), __( 'Stored for map widgets. Not auto-loaded site-wide.', 'dog-father-control-center' ) );
			?>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Messaging', 'dog-father-control-center' ); ?></h2>
			<?php
			dfcc_integrations_field( $module, $settings, 'whatsapp_number', __( 'WhatsApp Business Number', 'dog-father-control-center' ), __( 'International format, e.g. 9665XXXXXXXX.', 'dog-father-control-center' ) );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'Outgoing Email (SMTP)', 'dog-father-control-center' ); ?></h3>
			<?php
			dfcc_integrations_toggle( $settings, 'smtp_enabled', __( 'Send WordPress email through this SMTP server', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'smtp_host', __( 'SMTP Host', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'smtp_port', __( 'SMTP Port', 'dog-father-control-center' ), '587 (TLS) / 465 (SSL)', 'number' );
			dfcc_integrations_field( $module, $settings, 'smtp_user', __( 'SMTP Username', 'dog-father-control-center' ) );
			dfcc_integrations_field( $module, $settings, 'smtp_pass', __( 'SMTP Password', 'dog-father-control-center' ), '', 'password' );
			?>
			<div class="dfcc-field">
				<label for="smtp_encryption"><?php esc_html_e( 'Encryption', 'dog-father-control-center' ); ?></label>
				<select id="smtp_encryption" name="smtp_encryption">
					<option value="tls" <?php selected( $enc_value, 'tls' ); ?>>TLS</option>
					<option value="ssl" <?php selected( $enc_value, 'ssl' ); ?>>SSL</option>
					<option value="none" <?php selected( $enc_value, 'none' ); ?>><?php esc_html_e( 'None', 'dog-father-control-center' ); ?></option>
				</select>
			</div>
			<?php
			dfcc_integrations_field( $module, $settings, 'smtp_from_email', __( 'From Email', 'dog-father-control-center' ), '', 'email' );
			dfcc_integrations_field( $module, $settings, 'smtp_from_name', __( 'From Name', 'dog-father-control-center' ) );
			?>

			<h3 class="dfcc-section-title"><?php esc_html_e( 'SMS Provider', 'dog-father-control-center' ); ?></h3>
			<div class="dfcc-field">
				<label for="sms_provider"><?php esc_html_e( 'Provider', 'dog-father-control-center' ); ?></label>
				<select id="sms_provider" name="sms_provider">
					<option value="none" <?php selected( $sms_provider, 'none' ); ?>><?php esc_html_e( 'None', 'dog-father-control-center' ); ?></option>
					<option value="twilio" <?php selected( $sms_provider, 'twilio' ); ?>>Twilio</option>
					<option value="unifonic" <?php selected( $sms_provider, 'unifonic' ); ?>>Unifonic</option>
				</select>
			</div>
			<?php
			dfcc_integrations_field( $module, $settings, 'sms_api_key', __( 'SMS API Key', 'dog-father-control-center' ), '', 'password' );
			dfcc_integrations_field( $module, $settings, 'sms_sender_id', __( 'Sender ID', 'dog-father-control-center' ) );
			?>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Developer', 'dog-father-control-center' ); ?></h2>
			<?php dfcc_integrations_toggle( $settings, 'rest_enabled', __( 'Allow public (unauthenticated) REST API access', 'dog-father-control-center' ) ); ?>
			<div class="dfcc-field">
				<label for="webhook_urls"><?php esc_html_e( 'Webhook URLs (one per line)', 'dog-father-control-center' ); ?></label>
				<textarea id="webhook_urls" name="webhook_urls" rows="4"><?php echo esc_textarea( $webhook_urls ); ?></textarea>
			</div>
		</div>

		<p>
			<button type="submit" class="dfcc-button"><?php esc_html_e( 'Save Integrations', 'dog-father-control-center' ); ?></button>
		</p>
	</form>
</div>
