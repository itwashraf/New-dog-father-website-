<?php
/**
 * Front page.
 *
 * Behaviour:
 *  - If the front page is built with Elementor (or has manual content), render
 *    that — the owner is in full control via Elementor.
 *  - Otherwise render the theme's ready-made luxury homepage sections, which
 *    pull their copy from Dog Father → Homepage and content from the plugin
 *    CPTs. This is what makes the site look finished the moment it is uploaded.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$df_id      = get_queried_object_id();
$df_builder = $df_id && dfather_is_elementor( $df_id );
$df_manual  = $df_id ? trim( wp_strip_all_tags( get_post_field( 'post_content', $df_id ) ) ) : '';

if ( $df_builder || '' !== $df_manual ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
else :
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/trust' );
	get_template_part( 'template-parts/home/about' );
	get_template_part( 'template-parts/home/services' );
	get_template_part( 'template-parts/home/why' );
	get_template_part( 'template-parts/home/stats' );
	get_template_part( 'template-parts/home/gallery' );
	get_template_part( 'template-parts/home/testimonials' );
	get_template_part( 'template-parts/home/faq' );
	get_template_part( 'template-parts/home/cta' );
	get_template_part( 'template-parts/home/contact' );
endif;

get_footer();
