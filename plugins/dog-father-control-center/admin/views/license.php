<?php
/**
 * License view.
 *
 * @package DogFatherControlCenter
 * @var array $license Stored license data (key, status, activated).
 * @var array $brand   Provada brand (name, url).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_key    = isset( $license['key'] ) ? $license['key'] : '';
$dfcc_status = isset( $license['status'] ) ? $license['status'] : '';
$dfcc_active = ( 'active' === $dfcc_status );
$dfcc_saved  = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-admin-network"></span> <?php esc_html_e( 'License', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Enter the license key you received with this theme to enable support and updates.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<?php if ( $dfcc_saved ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'License saved.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'License Key', 'dog-father-control-center' ); ?></h2>
		<p>
			<?php esc_html_e( 'Status:', 'dog-father-control-center' ); ?>
			<strong style="color:<?php echo $dfcc_active ? '#46b450' : '#cf240a'; ?>;">
				<?php echo esc_html( $dfcc_active ? __( 'Active', 'dog-father-control-center' ) : __( 'Not activated', 'dog-father-control-center' ) ); ?>
			</strong>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-license' ) ); ?>">
			<?php wp_nonce_field( 'dfcc_save_license' ); ?>
			<input type="hidden" name="dfcc_license_nonce" value="<?php echo esc_attr( wp_create_nonce( 'dfcc_save_license' ) ); ?>" />
			<div class="dfcc-field">
				<label for="license_key"><?php esc_html_e( 'Key', 'dog-father-control-center' ); ?></label>
				<input type="text" class="regular-text" id="license_key" name="license_key" value="<?php echo esc_attr( $dfcc_key ); ?>" placeholder="XXXX-XXXX-XXXX-XXXX" />
			</div>
			<?php submit_button( __( 'Save License', 'dog-father-control-center' ) ); ?>
		</form>
		<p class="description">
			<?php
			printf(
				/* translators: 1: Provada link. */
				esc_html__( 'Need a key or having trouble? Contact %s.', 'dog-father-control-center' ),
				'<a href="' . esc_url( $brand['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $brand['name'] ) . '</a>'
			);
			?>
		</p>
	</div>

	<div class="dfcc-panel" style="border-left:4px solid var(--dfcc-gold,#FEC208);">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'For the seller (Provada) — how licensing works', 'dog-father-control-center' ); ?></h2>
		<p><?php esc_html_e( 'This site’s domain is:', 'dog-father-control-center' ); ?> <code><?php echo esc_html( $domain ); ?></code></p>
		<?php if ( $secret_set ) : ?>
			<p><strong style="color:#46b450;"><?php esc_html_e( 'Domain-locked mode is ON.', 'dog-father-control-center' ); ?></strong>
				<?php esc_html_e( 'Each client needs a key generated from their own domain. Generate one below, then send it to them to paste above.', 'dog-father-control-center' ); ?></p>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
				<input type="hidden" name="page" value="dfcc-license" />
				<span>
					<label for="gen_domain"><strong><?php esc_html_e( 'Client domain', 'dog-father-control-center' ); ?></strong></label><br />
					<input type="text" id="gen_domain" name="gen_domain" class="regular-text" value="<?php echo esc_attr( $gen_domain ); ?>" placeholder="clientsite.com" />
				</span>
				<button type="submit" class="button"><?php esc_html_e( 'Generate key', 'dog-father-control-center' ); ?></button>
			</form>
			<?php if ( '' !== $gen_key ) : ?>
				<p style="margin-top:12px;"><?php esc_html_e( 'Key for', 'dog-father-control-center' ); ?> <code><?php echo esc_html( $gen_domain ); ?></code>:
					<code style="font-size:15px;background:#111;color:#FEC208;padding:4px 10px;border-radius:6px;"><?php echo esc_html( $gen_key ); ?></code>
				</p>
			<?php endif; ?>
		<?php else : ?>
			<p><strong><?php esc_html_e( 'Simple mode is ON (default):', 'dog-father-control-center' ); ?></strong>
				<?php esc_html_e( 'any non-empty key activates. Good for your own projects.', 'dog-father-control-center' ); ?></p>
			<p><?php esc_html_e( 'To sell with per-client keys, add this line to wp-config.php (the same secret in every copy you sell), then reload this page:', 'dog-father-control-center' ); ?></p>
			<p><code>define( 'DFCC_LICENSE_SECRET', 'your-long-random-secret' );</code></p>
			<p class="description"><?php esc_html_e( 'A generator will then appear here. Note: offline keys are convenient but can be extracted from the code by a determined buyer — for strong protection, connect a license server via the dfcc_license_validate filter (see Help & Docs).', 'dog-father-control-center' ); ?></p>
		<?php endif; ?>
	</div>
</div>
