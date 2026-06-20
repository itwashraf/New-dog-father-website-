<?php
/**
 * Services Manager view.
 *
 * A single, reliable form to create, edit, reorder (by drag is not needed —
 * rows save in the order shown), show/hide and delete services. Avoids the
 * WordPress post editor entirely.
 *
 * @package DogFatherControlCenter
 * @var WP_Post[] $services Existing services.
 * @var array     $icons    Icon slug => label choices.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_saved = isset( $_GET['dfcc_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * Render one service row.
 *
 * @param string  $key   Row key (post ID, or "newN").
 * @param WP_Post $post  Service post or null for the blank "add" row.
 * @param array   $icons Icon choices.
 * @return void
 */
$dfcc_row = static function ( $key, $post, $icons ) {
	$is_new   = ( null === $post );
	$title    = $is_new ? '' : $post->post_title;
	$excerpt  = $is_new ? '' : $post->post_excerpt;
	$price    = $is_new ? '' : DFCC_Services::get_meta( $post->ID, 'price' );
	$suffix   = $is_new ? '' : DFCC_Services::get_meta( $post->ID, 'price_suffix' );
	$icon     = $is_new ? 'dashicons-heart' : DFCC_Services::get_meta( $post->ID, 'icon', 'dashicons-heart' );
	$features = $is_new ? '' : DFCC_Services::get_meta( $post->ID, 'features' );
	$feat_on  = $is_new ? false : ( '1' === (string) DFCC_Services::get_meta( $post->ID, 'highlight' ) );
	$vis_on   = $is_new ? true : ( '0' !== (string) DFCC_Services::get_meta( $post->ID, 'visible', '1' ) );
	$b        = 'service[' . esc_attr( $key ) . ']';
	?>
	<div class="dfcc-svc-card<?php echo $is_new ? ' dfcc-svc-new' : ''; ?>">
		<div class="dfcc-svc-grid">
			<p class="dfcc-svc-title">
				<label><strong><?php esc_html_e( 'Service name', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" name="<?php echo $b; ?>[title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'e.g. Dog Boarding', 'dog-father-control-center' ); ?>" />
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Price', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" name="<?php echo $b; ?>[price]" value="<?php echo esc_attr( $price ); ?>" placeholder="500" />
				<span class="description"><?php esc_html_e( 'Number only. Leave blank for "on request".', 'dog-father-control-center' ); ?></span>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Price note', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" name="<?php echo $b; ?>[price_suffix]" value="<?php echo esc_attr( $suffix ); ?>" placeholder="<?php esc_attr_e( '/ night · meals included', 'dog-father-control-center' ); ?>" />
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Icon', 'dog-father-control-center' ); ?></strong></label>
				<select name="<?php echo $b; ?>[icon]" class="widefat">
					<?php foreach ( $icons as $slug => $lbl ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $icon, $slug ); ?>><?php echo esc_html( $lbl ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="dfcc-svc-wide">
				<label><strong><?php esc_html_e( 'Short description', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" name="<?php echo $b; ?>[excerpt]" value="<?php echo esc_attr( $excerpt ); ?>" />
			</p>
			<p class="dfcc-svc-wide">
				<label><strong><?php esc_html_e( 'Features', 'dog-father-control-center' ); ?></strong></label>
				<textarea class="widefat" rows="4" name="<?php echo $b; ?>[features]" placeholder="<?php esc_attr_e( 'One feature per line', 'dog-father-control-center' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
				<span class="description"><?php esc_html_e( 'One feature per line — each becomes a ticked bullet on the card.', 'dog-father-control-center' ); ?></span>
			</p>
			<div class="dfcc-svc-flags">
				<label title="<?php esc_attr_e( 'Uncheck to hide this service everywhere without deleting it.', 'dog-father-control-center' ); ?>"><input type="checkbox" name="<?php echo $b; ?>[visible]" value="1" <?php checked( $vis_on ); ?> /> <?php esc_html_e( 'Show on website', 'dog-father-control-center' ); ?> <span class="dfcc-svc-hint"><?php esc_html_e( '(visible to visitors)', 'dog-father-control-center' ); ?></span></label>
				<label title="<?php esc_attr_e( 'Adds a “Popular” badge and a highlighted border to make this card stand out.', 'dog-father-control-center' ); ?>"><input type="checkbox" name="<?php echo $b; ?>[highlight]" value="1" <?php checked( $feat_on ); ?> /> <?php esc_html_e( 'Featured', 'dog-father-control-center' ); ?> <span class="dfcc-svc-hint"><?php esc_html_e( '(adds a “Popular” badge)', 'dog-father-control-center' ); ?></span></label>
				<?php if ( ! $is_new ) : ?>
					<label class="dfcc-svc-delete"><input type="checkbox" name="<?php echo $b; ?>[delete]" value="1" /> <?php esc_html_e( 'Delete this service', 'dog-father-control-center' ); ?></label>
				<?php endif; ?>
			</div>
			<p class="dfcc-svc-flagnote description"><?php esc_html_e( '“Show on website” = visible to visitors (untick to hide without deleting). “Featured” = highlights the card with a “Popular” badge.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>
	<?php
};
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-heart"></span> <?php esc_html_e( 'Manage Services', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Edit all your services here, then click Save. This is the easy way — no separate editor screens.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<?php if ( $dfcc_saved ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Services saved and cache cleared.', 'dog-father-control-center' ); ?>
			<a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View services', 'dog-father-control-center' ); ?></a>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="dfcc_save_services" />
		<?php wp_nonce_field( 'dfcc_save_services' ); ?>

		<style>
			.dfcc-svc-card{background:#fff;border:1px solid #e2e2e6;border-left:4px solid var(--dfcc-gold,#FEC208);border-radius:10px;padding:16px 18px;margin:0 0 16px;}
			.dfcc-svc-new{border-left-color:var(--dfcc-orange,#FF2D08);background:#fffdf5;}
			.dfcc-svc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px 18px;}
			.dfcc-svc-grid p{margin:0;}
			.dfcc-svc-grid label{display:block;margin-bottom:4px;}
			.dfcc-svc-title{grid-column:span 4;}
			.dfcc-svc-wide{grid-column:span 4;}
			.dfcc-svc-flags{grid-column:span 4;display:flex;flex-wrap:wrap;gap:18px;align-items:center;padding-top:6px;border-top:1px solid #f0f0f1;margin-top:4px;}
			.dfcc-svc-flags label{display:inline-flex;align-items:center;gap:6px;margin:0;font-weight:600;}
			.dfcc-svc-delete{color:#cf240a;}
			.dfcc-svc-hint{font-weight:400;color:#787c82;font-size:12px;}
			.dfcc-svc-flagnote{margin:6px 0 0;}
			.dfcc-svc-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);padding:14px 0;border-top:1px solid #e2e2e6;margin-top:6px;display:flex;gap:12px;align-items:center;}
			@media(max-width:1100px){.dfcc-svc-grid{grid-template-columns:repeat(2,1fr);}.dfcc-svc-title,.dfcc-svc-wide,.dfcc-svc-flags{grid-column:span 2;}}
		</style>

		<h2><?php esc_html_e( 'Your services', 'dog-father-control-center' ); ?></h2>
		<?php
		if ( empty( $services ) ) {
			echo '<p>' . esc_html__( 'No services yet — add your first one below.', 'dog-father-control-center' ) . '</p>';
		}
		foreach ( $services as $svc ) {
			$dfcc_row( (string) $svc->ID, $svc, $icons );
		}
		?>

		<h2><?php esc_html_e( 'Add a new service', 'dog-father-control-center' ); ?></h2>
		<?php $dfcc_row( 'new1', null, $icons ); ?>

		<div class="dfcc-svc-actions">
			<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save Services', 'dog-father-control-center' ); ?></button>
			<span class="description"><?php esc_html_e( 'Saving also clears your site cache so changes show immediately.', 'dog-father-control-center' ); ?></span>
		</div>
	</form>
</div>
