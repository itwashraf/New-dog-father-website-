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
</div>
