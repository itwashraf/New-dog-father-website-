<?php
/**
 * Home: testimonials.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'testimonials' ) ) {
	return;
}

$df_title = dfather_home( 'testimonials_title', __( 'Loved by Dog Parents', 'dog-father' ) );

$df_items = get_posts(
	array(
		'post_type'      => 'dfcc_testimonial',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array(
				'key'     => '_dfcc_approved',
				'value'   => '1',
				'compare' => '=',
			),
			array(
				'key'     => '_dfcc_approved',
				'compare' => 'NOT EXISTS',
			),
		),
	)
);

$df_defaults = array(
	array( 5, __( 'Bella came home happy, healthy and beautifully groomed. The daily photos gave me total peace of mind while travelling.', 'dog-father' ), __( 'Sarah M.', 'dog-father' ) ),
	array( 5, __( 'The most professional and caring team we have ever trusted with Max. The suites are immaculate and the staff genuinely love the dogs.', 'dog-father' ), __( 'Ahmed K.', 'dog-father' ) ),
	array( 5, __( 'Five stars is not enough. Our anxious rescue actually gets excited when we arrive. That says everything.', 'dog-father' ), __( 'Lina R.', 'dog-father' ) ),
);
?>
<section class="df-section df-reveal" style="background:var(--df-surface-1);border-top:1px solid var(--df-border-soft);border-bottom:1px solid var(--df-border-soft);">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php esc_html_e( 'Testimonials', 'dog-father' ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-grid df-grid--3">
			<?php
			if ( $df_items ) {
				foreach ( $df_items as $df_post ) {
					$rating = (int) get_post_meta( $df_post->ID, '_dfcc_rating', true );
					$author = get_post_meta( $df_post->ID, '_dfcc_author_name', true );
					$role   = get_post_meta( $df_post->ID, '_dfcc_author_role', true );
					$author = $author ? $author : get_the_title( $df_post->ID );
					?>
					<article class="df-testimonial">
						<div class="df-testimonial__stars"><?php echo esc_html( dfather_stars( $rating ? $rating : 5 ) ); ?></div>
						<p><?php echo esc_html( wp_strip_all_tags( get_the_content( null, false, $df_post ) ) ); ?></p>
						<div class="df-testimonial__by">
							<span class="df-testimonial__avatar"><?php echo esc_html( strtoupper( substr( $author, 0, 1 ) ) ); ?></span>
							<div>
								<strong><?php echo esc_html( $author ); ?></strong>
								<span><?php echo esc_html( $role ? $role : __( 'Dog parent', 'dog-father' ) ); ?></span>
							</div>
						</div>
					</article>
					<?php
				}
			} else {
				foreach ( $df_defaults as $d ) :
					?>
					<article class="df-testimonial">
						<div class="df-testimonial__stars"><?php echo esc_html( dfather_stars( $d[0] ) ); ?></div>
						<p><?php echo esc_html( $d[1] ); ?></p>
						<div class="df-testimonial__by">
							<span class="df-testimonial__avatar"><?php echo esc_html( strtoupper( substr( $d[2], 0, 1 ) ) ); ?></span>
							<div>
								<strong><?php echo esc_html( $d[2] ); ?></strong>
								<span><?php esc_html_e( 'Dog parent', 'dog-father' ); ?></span>
							</div>
						</div>
					</article>
					<?php
				endforeach;
			}
			?>
		</div>
	</div>
</section>
