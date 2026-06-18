<?php
/**
 * Security settings view.
 *
 * @package DogFatherControlCenter
 * @var array   $settings  Saved security settings.
 * @var array[] $checklist Status checklist rows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-shield-alt"></span><?php esc_html_e( 'Security', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Harden the basics and review your status at a glance.', 'dog-father-control-center' ); ?></p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Security settings saved.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Hardening', 'dog-father-control-center' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'dfcc_save_security', 'dfcc_security_nonce' ); ?>
				<div class="dfcc-field">
					<label for="disable_xmlrpc">
						<input type="checkbox" id="disable_xmlrpc" name="disable_xmlrpc" value="1" <?php checked( ! empty( $settings['disable_xmlrpc'] ) ); ?> />
						<?php esc_html_e( 'Disable XML-RPC (recommended)', 'dog-father-control-center' ); ?>
					</label>
				</div>
				<div class="dfcc-field">
					<label for="hide_wp_version">
						<input type="checkbox" id="hide_wp_version" name="hide_wp_version" value="1" <?php checked( ! empty( $settings['hide_wp_version'] ) ); ?> />
						<?php esc_html_e( 'Hide WordPress version (remove generator meta)', 'dog-father-control-center' ); ?>
					</label>
				</div>
				<div class="dfcc-field">
					<label for="disable_file_edit">
						<input type="checkbox" id="disable_file_edit" name="disable_file_edit" value="1" <?php checked( ! empty( $settings['disable_file_edit'] ) ); ?> />
						<?php esc_html_e( 'Disable in-dashboard file editing', 'dog-father-control-center' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'For full effect add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.', 'dog-father-control-center' ); ?></p>
				</div>
				<div class="dfcc-field">
					<label for="limit_login">
						<input type="checkbox" id="limit_login" name="limit_login" value="1" <?php checked( ! empty( $settings['limit_login'] ) ); ?> />
						<?php esc_html_e( 'Limit login attempts', 'dog-father-control-center' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Display-only preference — pair with a dedicated login-protection plugin.', 'dog-father-control-center' ); ?></p>
				</div>
				<div class="dfcc-field">
					<label for="strong_rest_auth">
						<input type="checkbox" id="strong_rest_auth" name="strong_rest_auth" value="1" <?php checked( ! empty( $settings['strong_rest_auth'] ) ); ?> />
						<?php esc_html_e( 'Prefer strong REST authentication', 'dog-father-control-center' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Note: control public REST access from the Integrations screen.', 'dog-father-control-center' ); ?></p>
				</div>
				<p><button type="submit" class="dfcc-button"><?php esc_html_e( 'Save Security', 'dog-father-control-center' ); ?></button></p>
			</form>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Status Checklist', 'dog-father-control-center' ); ?></h2>
			<ul class="dfcc-module-list" style="columns:1;">
				<?php foreach ( $checklist as $item ) : ?>
					<li>
						<span class="dashicons dashicons-<?php echo $item['ok'] ? 'yes-alt' : 'warning'; ?>" style="color:<?php echo $item['ok'] ? '#7ad17a' : 'var(--dfcc-orange)'; ?>;"></span>
						<span>
							<strong><?php echo esc_html( $item['label'] ); ?></strong><br />
							<span class="description"><?php echo esc_html( $item['note'] ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
