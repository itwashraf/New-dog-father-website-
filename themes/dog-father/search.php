<?php
/**
 * Search results template.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<header class="df-page-hero">
	<div class="df-container">
		<h1 class="df-gradient-text">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Results for “%s”', 'dog-father' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
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
					'prev_text' => __( '← Prev', 'dog-father' ),
					'next_text' => __( 'Next →', 'dog-father' ),
				)
			);
		else :
			?>
			<div class="df-prose" style="text-align:center;">
				<p><?php esc_html_e( 'No results found. Try another search.', 'dog-father' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
