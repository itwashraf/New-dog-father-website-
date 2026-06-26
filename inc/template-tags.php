<?php
/**
 * Template tags — small render helpers used across templates.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render star rating markup.
 *
 * @param int $rating 1-5.
 * @return string
 */
function dfather_stars( $rating ) {
	$rating = max( 0, min( 5, (int) $rating ) );
	return str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating );
}

/**
 * Output a service card given a post id (uses Control Center meta).
 *
 * @param int $id Service post id.
 * @return void
 */
function dfather_service_card( $id ) {
	$price    = get_post_meta( $id, '_dfcc_price', true );
	$suffix   = get_post_meta( $id, '_dfcc_price_suffix', true );
	$icon     = get_post_meta( $id, '_dfcc_icon', true );
	$features = get_post_meta( $id, '_dfcc_features', true );
	$featured = get_post_meta( $id, '_dfcc_highlight', true );
	$feats    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $features ) ) );
	?>
	<article class="df-service<?php echo $featured ? ' is-featured' : ''; ?>">
		<?php if ( $featured ) : ?><span class="df-service__tag"><?php esc_html_e( 'Popular', 'dog-father' ); ?></span><?php endif; ?>
		<div class="df-service__icon">
			<?php if ( $icon && 0 === strpos( $icon, 'dashicons' ) ) : ?>
				<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
			<?php elseif ( $icon ) : ?>
				<?php echo esc_html( $icon ); ?>
			<?php else : ?>
				<span class="dashicons dashicons-heart"></span>
			<?php endif; ?>
		</div>
		<h3><?php echo esc_html( get_the_title( $id ) ); ?></h3>
		<?php if ( '' !== $price && null !== $price ) : ?>
			<div class="df-service__price">
				<?php echo function_exists( 'dfcc_money' ) ? dfcc_money( $price ) : esc_html( $price ); ?>
				<?php if ( $suffix ) : ?><span><?php echo esc_html( $suffix ); ?></span><?php endif; ?>
			</div>
		<?php endif; ?>
		<p><?php echo esc_html( get_the_excerpt( $id ) ); ?></p>
		<?php if ( $feats ) : ?>
			<ul class="df-service__features">
				<?php foreach ( array_slice( $feats, 0, 5 ) as $f ) : ?>
					<li><?php echo esc_html( $f ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * Gallery item markup for a gallery post id.
 *
 * @param int $id Gallery post id.
 * @return void
 */
function dfather_gallery_item( $id ) {
	$thumb = get_the_post_thumbnail( $id, 'dfather-card', array( 'loading' => 'lazy' ) );
	?>
	<div class="df-gallery-item">
		<?php if ( $thumb ) : ?>
			<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<div class="df-gallery-item__ph"><span class="dashicons dashicons-camera"></span></div>
		<?php endif; ?>
		<span class="df-gallery-item__label"><?php echo esc_html( get_the_title( $id ) ); ?></span>
	</div>
	<?php
}
