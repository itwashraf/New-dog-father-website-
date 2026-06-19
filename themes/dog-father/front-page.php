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
	// Hero is always first and pinned.
	get_template_part( 'template-parts/home/hero' );

	// Per-section background/spacing overrides chosen in Section Layout.
	$df_inline = dfather_section_inline_styles();
	if ( '' !== $df_inline ) {
		printf( '<style id="df-section-styles">%s</style>', $df_inline ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated from sanitized hex / whitelisted values.
	}

	// Render the remaining sections in the owner's chosen order. Each part
	// self-guards on its show_{slug} toggle; we also skip hidden ones here so
	// no empty wrapper is emitted.
	foreach ( dfather_section_order() as $df_slug ) {
		if ( ! dfather_show( $df_slug ) ) {
			continue;
		}
		echo '<div class="df-slot df-slot--' . esc_attr( $df_slug ) . '">';
		get_template_part( 'template-parts/home/' . $df_slug );
		echo '</div>';
	}
endif;

get_footer();
