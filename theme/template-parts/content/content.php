<?php
/**
 * Blog post card (used in archives/index).
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'df-post-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="df-post-card__thumb">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'dfather-card', array( 'loading' => 'lazy' ) );
		}
		?>
	</a>
	<div class="df-post-card__body">
		<div class="df-post-card__meta">
			<?php echo esc_html( get_the_date() ); ?>
			<?php
			$df_cats = get_the_category_list( ', ' );
			if ( $df_cats ) {
				echo ' · ' . wp_kses_post( $df_cats );
			}
			?>
		</div>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<a href="<?php the_permalink(); ?>" class="df-btn df-btn--ghost" style="padding:9px 18px;font-size:.9rem;"><?php esc_html_e( 'Read More', 'dog-father' ); ?></a>
	</div>
</article>
