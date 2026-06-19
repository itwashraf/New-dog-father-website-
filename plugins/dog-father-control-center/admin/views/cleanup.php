<?php
/**
 * Cleanup view — remove leftover Kubio block markup after the Kubio plugin has
 * been deactivated.
 *
 * @package DogFatherControlCenter
 * @var WP_Post[] $posts        Posts that still contain Kubio markup.
 * @var string    $backup_meta  Meta key used for the reversible backup.
 * @var callable  $count_blocks Counts Kubio blocks in a content string.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_cleaned  = isset( $_GET['dfcc_cleaned'] ) ? (int) $_GET['dfcc_cleaned'] : -1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dfcc_restored = isset( $_GET['dfcc_restored'] ) ? (int) $_GET['dfcc_restored'] : -1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dfcc_action   = esc_url( admin_url( 'admin-post.php' ) );
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-editor-removeformatting"></span> <?php esc_html_e( 'Cleanup — Leftover Kubio Blocks', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'You removed the Kubio page builder, but some pages still contain its blocks — that is what causes the "block not supported" warnings and stops you editing or saving. Clean them here.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<?php if ( $dfcc_cleaned >= 0 ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span>
			<?php
			/* translators: %d: number of items cleaned. */
			echo esc_html( sprintf( _n( 'Cleaned %d item. A backup was kept so you can undo.', 'Cleaned %d items. A backup was kept so you can undo.', $dfcc_cleaned, 'dog-father-control-center' ), $dfcc_cleaned ) );
			?>
		</div>
	<?php endif; ?>
	<?php if ( $dfcc_restored >= 0 ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-undo"></span>
			<?php
			/* translators: %d: number of items restored. */
			echo esc_html( sprintf( _n( 'Restored %d item from backup.', 'Restored %d items from backup.', $dfcc_restored, 'dog-father-control-center' ), $dfcc_restored ) );
			?>
		</div>
	<?php endif; ?>

	<div class="dfcc-panel" style="border-left:4px solid #ffb900;">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'How this works', 'dog-father-control-center' ); ?></h2>
		<p>
			<?php esc_html_e( 'Cleaning removes the Kubio block wrappers and keeps any real text/images as plain HTML — exactly like the editor\'s "Keep as HTML" option, but in one click. If a page was 100% Kubio with no plain content, it becomes empty, which lets The Dog Father theme show its own built-in design for that page (recommended for the Home page).', 'dog-father-control-center' ); ?>
		</p>
		<p><strong><?php esc_html_e( 'Every change is backed up', 'dog-father-control-center' ); ?></strong> — <?php esc_html_e( 'you can undo any item with Restore below.', 'dog-father-control-center' ); ?></p>
	</div>

	<?php if ( empty( $posts ) ) : ?>
		<div class="dfcc-panel">
			<p style="font-size:15px;"><span class="dashicons dashicons-yes-alt" style="color:#46b450;"></span> <?php esc_html_e( 'No leftover Kubio blocks were found. You are all clean!', 'dog-father-control-center' ); ?></p>
		</div>
	<?php else : ?>
		<div class="dfcc-panel">
			<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
				<h2 class="dfcc-section-title" style="margin:0;">
					<?php
					/* translators: %d: number of affected pages. */
					echo esc_html( sprintf( _n( '%d page still uses Kubio', '%d pages still use Kubio', count( $posts ), 'dog-father-control-center' ), count( $posts ) ) );
					?>
				</h2>
				<form method="post" action="<?php echo $dfcc_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Clean Kubio blocks from ALL listed pages? A backup is kept so you can undo.', 'dog-father-control-center' ) ); ?>');">
					<input type="hidden" name="action" value="dfcc_kubio_clean" />
					<input type="hidden" name="target" value="all" />
					<?php wp_nonce_field( 'dfcc_kubio_clean' ); ?>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Clean All', 'dog-father-control-center' ); ?></button>
				</form>
			</div>

			<table class="widefat striped" style="margin-top:16px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Page / Post', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Type', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Kubio blocks', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'dog-father-control-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $posts as $dfcc_post ) : ?>
						<?php
						$dfcc_count   = (int) call_user_func( $count_blocks, $dfcc_post->post_content );
						$dfcc_has_bak = '' !== (string) get_post_meta( $dfcc_post->ID, $backup_meta, true );
						$dfcc_type    = get_post_type_object( $dfcc_post->post_type );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $dfcc_post->post_title ? $dfcc_post->post_title : __( '(no title)', 'dog-father-control-center' ) ); ?></strong>
								<div>
									<a href="<?php echo esc_url( get_edit_post_link( $dfcc_post->ID ) ); ?>"><?php esc_html_e( 'Edit', 'dog-father-control-center' ); ?></a> ·
									<a href="<?php echo esc_url( get_permalink( $dfcc_post->ID ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'dog-father-control-center' ); ?></a>
								</div>
							</td>
							<td><?php echo esc_html( $dfcc_type ? $dfcc_type->labels->singular_name : $dfcc_post->post_type ); ?></td>
							<td><?php echo esc_html( $dfcc_count ); ?></td>
							<td>
								<form method="post" action="<?php echo $dfcc_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Clean Kubio blocks from this page?', 'dog-father-control-center' ) ); ?>');">
									<input type="hidden" name="action" value="dfcc_kubio_clean" />
									<input type="hidden" name="target" value="<?php echo esc_attr( $dfcc_post->ID ); ?>" />
									<?php wp_nonce_field( 'dfcc_kubio_clean' ); ?>
									<button type="submit" class="button button-small button-primary"><?php esc_html_e( 'Clean', 'dog-father-control-center' ); ?></button>
								</form>
								<?php if ( $dfcc_has_bak ) : ?>
									<form method="post" action="<?php echo $dfcc_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" style="display:inline;">
										<input type="hidden" name="action" value="dfcc_kubio_restore" />
										<input type="hidden" name="target" value="<?php echo esc_attr( $dfcc_post->ID ); ?>" />
										<?php wp_nonce_field( 'dfcc_kubio_restore' ); ?>
										<button type="submit" class="button button-small"><?php esc_html_e( 'Restore', 'dog-father-control-center' ); ?></button>
									</form>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'Undo everything', 'dog-father-control-center' ); ?></h2>
		<p><?php esc_html_e( 'Restore the original content of every page that was cleaned.', 'dog-father-control-center' ); ?></p>
		<form method="post" action="<?php echo $dfcc_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Restore original content for ALL previously cleaned pages?', 'dog-father-control-center' ) ); ?>');">
			<input type="hidden" name="action" value="dfcc_kubio_restore" />
			<input type="hidden" name="target" value="all" />
			<?php wp_nonce_field( 'dfcc_kubio_restore' ); ?>
			<button type="submit" class="button"><?php esc_html_e( 'Restore All from Backup', 'dog-father-control-center' ); ?></button>
		</form>
	</div>
</div>
