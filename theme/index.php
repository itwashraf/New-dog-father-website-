<?php
/**
 * Main fallback template — blog index / archives.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$df_title = __( 'Journal', 'dog-father' );
if ( is_home() && ! is_front_page() ) {
	$df_title = get_the_title( get_option( 'page_for_posts' ) );
}
?>
<header class="df-page-hero">
	<div class="df-container">
		<h1 class="df-gradient-text"><?php echo esc_html( $df_title ); ?></h1>
	</div>
</header>

<div class="df-page-body">
	<div class="df-container">
		<?php if ( have_posts() ) : ?>
			<div class="df-grid df-grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content' );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'class'     => 'df-pagination',
					'mid_size'  => 1,
					'prev_text' => __( '← Prev', 'dog-father' ),
					'next_text' => __( 'Next →', 'dog-father' ),
				)
			);
			?>
		<?php else : ?>
			<p class="df-prose"><?php esc_html_e( 'Nothing here yet. Check back soon.', 'dog-father' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
