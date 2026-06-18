<?php
/**
 * Single post template.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<header class="df-page-hero">
		<div class="df-container">
			<div class="df-post-card__meta" style="margin-bottom:14px;">
				<?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?>
			</div>
			<h1 class="df-gradient-text"><?php the_title(); ?></h1>
		</div>
	</header>

	<div class="df-page-body">
		<div class="df-container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div style="max-width:980px;margin:0 auto 40px;border-radius:18px;overflow:hidden;">
					<?php the_post_thumbnail( 'dfather-wide' ); ?>
				</div>
			<?php endif; ?>
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
			<?php
			if ( comments_open() || get_comments_number() ) {
				echo '<div class="df-prose" style="margin-top:40px;">';
				comments_template();
				echo '</div>';
			}
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
