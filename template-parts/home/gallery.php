<?php
/**
 * Home: gallery preview.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'gallery' ) ) {
	return;
}

$df_title = dfather_home( 'gallery_title', __( 'Life at the Hotel', 'dog-father' ) );
$df_items = get_posts(
	array(
		'post_type'      => 'dfcc_gallery',
		'posts_per_page' => 8,
		'post_status'    => 'publish',
	)
);

// If there is no gallery content at all, skip rather than show empties.
if ( ! $df_items ) {
	return;
}
?>
<section class="df-section df-reveal" id="gallery">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php esc_html_e( 'Gallery', 'dog-father' ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-gallery-grid">
			<?php foreach ( $df_items as $df_post ) : ?>
				<?php dfather_gallery_item( $df_post->ID ); ?>
			<?php endforeach; ?>
		</div>
		<div style="text-align:center;margin-top:40px;">
			<a class="df-btn df-btn--ghost" href="<?php echo esc_url( dfather_url( '/gallery/', '/gallery/' ) ); ?>"><?php esc_html_e( 'View Full Gallery', 'dog-father' ); ?></a>
		</div>
	</div>
</section>
