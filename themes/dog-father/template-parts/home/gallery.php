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
			<span class="df-eyebrow"><?php echo esc_html( dfather_home( 'gallery_eyebrow', __( 'Gallery', 'dog-father' ) ) ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-gallery-grid">
			<?php foreach ( $df_items as $df_post ) : ?>
				<?php dfather_gallery_item( $df_post->ID ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		$df_btn_label = dfather_home( 'gallery_button_label', __( 'View Full Gallery', 'dog-father' ) );
		if ( '' !== trim( (string) $df_btn_label ) ) :
			?>
			<div style="text-align:center;margin-top:40px;">
				<a class="df-btn df-btn--ghost" href="<?php echo dfather_url( dfather_home( 'gallery_button_url', '/gallery/' ), '/gallery/' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dfather_url returns an escaped URL. ?>"><?php echo esc_html( $df_btn_label ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
