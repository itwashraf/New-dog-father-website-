<?php
/**
 * Archive template (categories, tags, dates, CPT archives).
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
		<h1 class="df-gradient-text"><?php the_archive_title(); ?></h1>
		<?php
		$df_desc = get_the_archive_description();
		if ( $df_desc ) {
			echo '<div class="df-prose" style="margin-top:12px;">' . wp_kses_post( $df_desc ) . '</div>';
		}
		?>
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
			?>
		<?php else : ?>
			<p class="df-prose"><?php esc_html_e( 'Nothing found here.', 'dog-father' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
