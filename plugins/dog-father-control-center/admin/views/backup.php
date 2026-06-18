<?php
/**
 * Backup Center view.
 *
 * @package DogFatherControlCenter
 * @var string[] $option_groups Option group names included in the backup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$export_nonce = wp_create_nonce( 'dfcc_backup_export' );
$notice       = isset( $_GET['dfcc_notice'] ) ? sanitize_key( wp_unslash( $_GET['dfcc_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-database-export"></span><?php esc_html_e( 'Backup Center', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Export and restore all Control Center settings and content.', 'dog-father-control-center' ); ?></p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<?php if ( 'imported' === $notice ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Backup imported successfully.', 'dog-father-control-center' ); ?></div>
	<?php elseif ( 'error' === $notice ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Could not import — the file was not a valid backup.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Export', 'dog-father-control-center' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Downloads a single JSON file containing the option groups below plus a snapshot of your content.', 'dog-father-control-center' ); ?></p>
			<ul class="dfcc-module-list">
				<?php foreach ( $option_groups as $group ) : ?>
					<li><span class="dashicons dashicons-yes-alt"></span><?php echo esc_html( $group ); ?></li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a class="dfcc-button" href="<?php echo esc_url( add_query_arg( array( 'dfcc_backup_export' => 1, '_wpnonce' => $export_nonce ), admin_url( 'admin.php?page=dfcc-backup' ) ) ); ?>">
					<?php esc_html_e( 'Download Backup (JSON)', 'dog-father-control-center' ); ?>
				</a>
			</p>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Import', 'dog-father-control-center' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Upload a previously exported JSON file. Existing settings in the matched option groups will be overwritten.', 'dog-father-control-center' ); ?></p>
			<form method="post" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'dfcc_backup_import', 'dfcc_backup_import_nonce' ); ?>
				<div class="dfcc-field">
					<label for="dfcc_backup_file"><?php esc_html_e( 'Backup File', 'dog-father-control-center' ); ?></label>
					<input type="file" id="dfcc_backup_file" name="dfcc_backup_file" accept="application/json,.json" required />
				</div>
				<p><button type="submit" class="dfcc-button"><?php esc_html_e( 'Import Backup', 'dog-father-control-center' ); ?></button></p>
			</form>
		</div>
	</div>
</div>
