<?php
/**
 * Default page template.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$df_builder = dfather_is_elementor( get_the_ID() );

	if ( $df_builder ) :
		// Elementor controls the whole page.
		the_content();
	else :
		?>
		<header class="df-page-hero">
			<div class="df-container">
				<h1 class="df-gradient-text"><?php the_title(); ?></h1>
			</div>
		</header>
		<div class="df-page-body">
			<div class="df-container">
				<div class="df-prose">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<div class="df-pagination">',
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
	endif;
endwhile;

get_footer();
