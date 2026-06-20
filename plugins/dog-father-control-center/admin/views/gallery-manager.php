<?php
/**
 * Gallery Manager view — add photos with one click, reorder, delete.
 *
 * @package DogFatherControlCenter
 * @var WP_Post[] $items Existing gallery items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_saved = isset( $_GET['dfcc_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * Render one gallery row.
 *
 * @param string  $key  Row key (post ID or "newN").
 * @param WP_Post $post Gallery post or null for the blank add row.
 * @return void
 */
$dfcc_g_row = static function ( $key, $post ) {
	$is_new   = ( null === $post );
	$title    = $is_new ? '' : $post->post_title;
	$image_id = $is_new ? 0 : (int) get_post_thumbnail_id( $post->ID );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	$b        = 'item[' . esc_attr( $key ) . ']';
	$pid      = 'gimg_' . esc_attr( $key );
	?>
	<div class="dfcc-g-card<?php echo $is_new ? ' dfcc-g-new' : ''; ?>">
		<div class="dfcc-g-thumb">
			<img id="<?php echo $pid; ?>_preview" src="<?php echo esc_url( $image ); ?>" style="<?php echo $image ? '' : 'display:none;'; ?>" alt="" />
			<span class="dfcc-g-ph" style="<?php echo $image ? 'display:none;' : ''; ?>"><span class="dashicons dashicons-format-image"></span></span>
		</div>
		<div class="dfcc-g-body">
			<input type="hidden" id="<?php echo $pid; ?>" name="<?php echo $b; ?>[image_id]" value="<?php echo esc_attr( $image_id ); ?>" />
			<label><strong><?php esc_html_e( 'Caption', 'dog-father-control-center' ); ?></strong></label>
			<input type="text" class="widefat" name="<?php echo $b; ?>[title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'e.g. Play Garden', 'dog-father-control-center' ); ?>" />
			<p>
				<button type="button" class="button dfcc-media-upload" data-target="<?php echo $pid; ?>" data-preview="<?php echo $pid; ?>_preview"><?php esc_html_e( 'Choose Photo', 'dog-father-control-center' ); ?></button>
				<button type="button" class="button dfcc-media-clear" data-target="<?php echo $pid; ?>" data-preview="<?php echo $pid; ?>_preview"><?php esc_html_e( 'Remove Photo', 'dog-father-control-center' ); ?></button>
			</p>
			<?php if ( ! $is_new ) : ?>
				<label class="dfcc-g-delete"><input type="checkbox" name="<?php echo $b; ?>[delete]" value="1" /> <?php esc_html_e( 'Delete this item', 'dog-father-control-center' ); ?></label>
			<?php endif; ?>
		</div>
	</div>
	<?php
};
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-format-gallery"></span> <?php esc_html_e( 'Manage Gallery', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Upload your photos here. They appear in the homepage “Life at the Hotel” section and on your Gallery page. Until you add your own, tasteful sample photos are shown.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<?php if ( $dfcc_saved ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Gallery saved and cache cleared.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="dfcc_save_gallery_items" />
		<?php wp_nonce_field( 'dfcc_save_gallery_items' ); ?>

		<style>
			.dfcc-g-card{display:flex;gap:16px;background:#fff;border:1px solid #e2e2e6;border-left:4px solid var(--dfcc-gold,#FEC208);border-radius:10px;padding:14px 16px;margin:0 0 14px;}
			.dfcc-g-new{border-left-color:var(--dfcc-orange,#FF2D08);background:#fffdf5;}
			.dfcc-g-thumb{flex:0 0 120px;width:120px;height:90px;border-radius:8px;overflow:hidden;background:#f0f0f1;display:grid;place-items:center;}
			.dfcc-g-thumb img{width:100%;height:100%;object-fit:cover;}
			.dfcc-g-ph .dashicons{font-size:30px;color:#b5bcc2;}
			.dfcc-g-body{flex:1;}
			.dfcc-g-body label{display:block;margin-bottom:4px;}
			.dfcc-g-delete{color:#cf240a;font-weight:600;display:inline-flex;align-items:center;gap:6px;}
			.dfcc-g-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);padding:14px 0;border-top:1px solid #e2e2e6;margin-top:6px;display:flex;gap:12px;align-items:center;}
		</style>

		<h2><?php esc_html_e( 'Your photos', 'dog-father-control-center' ); ?></h2>
		<?php
		if ( empty( $items ) ) {
			echo '<p>' . esc_html__( 'No photos yet — add your first below.', 'dog-father-control-center' ) . '</p>';
		}
		foreach ( $items as $dfcc_item ) {
			$dfcc_g_row( (string) $dfcc_item->ID, $dfcc_item );
		}
		?>

		<h2><?php esc_html_e( 'Add a new photo', 'dog-father-control-center' ); ?></h2>
		<?php
		$dfcc_g_row( 'new1', null );
		$dfcc_g_row( 'new2', null );
		?>

		<div class="dfcc-g-actions">
			<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save Gallery', 'dog-father-control-center' ); ?></button>
			<span class="description"><?php esc_html_e( 'Tip: add several at once using the blank rows.', 'dog-father-control-center' ); ?></span>
		</div>
	</form>
</div>
