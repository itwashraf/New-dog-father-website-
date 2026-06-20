<?php
/**
 * Theme helpers — safe accessors that work with or without the
 * Dog Father Control Center plugin.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read an editable homepage setting (managed in Dog Father → Homepage).
 *
 * @param string $key     Setting key.
 * @param string $default Fallback used when the plugin/value is absent.
 * @return mixed
 */
function dfather_home( $key, $default = '' ) {
	if ( function_exists( 'dfcc_get_setting' ) ) {
		return dfcc_get_setting( 'dfcc_home_settings', $key, $default );
	}
	return $default;
}

/**
 * Read a theme/layout setting (managed in Dog Father → Theme Settings).
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback used when the plugin/value is absent.
 * @return mixed
 */
function dfather_style( $key, $default = '' ) {
	if ( function_exists( 'dfcc_get_setting' ) ) {
		return dfcc_get_setting( 'dfcc_theme_settings', $key, $default );
	}
	return $default;
}

/**
 * Read a global business setting (managed in Dog Father → Global Settings).
 *
 * @param string $key     Setting key.
 * @param string $default Fallback.
 * @return mixed
 */
function dfather_info( $key, $default = '' ) {
	if ( function_exists( 'dfcc_get_setting' ) ) {
		return dfcc_get_setting( 'dfcc_global_settings', $key, $default );
	}
	return $default;
}

/**
 * A tasteful default photo URL for a homepage slot, used only when the owner
 * has not uploaded their own image yet — so the site looks finished out of the
 * box. Royalty-free Unsplash photography; override any of these with the
 * `dfather_default_images` filter or, better, by uploading your own image in
 * Dog Father → Homepage.
 *
 * @param string $slot One of: hero, about, cta.
 * @return string Image URL (empty string if unknown slot).
 */
function dfather_default_image( $slot ) {
	$images = apply_filters(
		'dfather_default_images',
		array(
			// Golden retriever close-up — warm, premium hero banner.
			'hero'  => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=1600&q=80',
			// Happy dog being held — friendly "about us" feel.
			'about' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&w=1200&q=80',
			// Dog running on grass — energetic call-to-action backdrop.
			'cta'   => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=1600&q=80',
		)
	);
	return isset( $images[ $slot ] ) ? $images[ $slot ] : '';
}

/**
 * A rotating set of tasteful default gallery photos, used when a gallery item
 * has no uploaded image yet. Override with the `dfather_default_gallery_images`
 * filter, or upload your own in Dog Father → Manage Gallery (uploads win).
 *
 * @param int $index Zero-based position in the grid.
 * @return string Image URL.
 */
function dfather_default_gallery_image( $index ) {
	$images = apply_filters(
		'dfather_default_gallery_images',
		array(
			'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1518717758536-85ae29035b6d?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1535930891776-0c2dfb7fda1a?auto=format&fit=crop&w=800&q=80',
			'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=800&q=80',
		)
	);
	if ( empty( $images ) ) {
		return '';
	}
	return $images[ $index % count( $images ) ];
}

/**
 * Configured social profile links (only those with a URL set in
 * Dog Father → Global Settings → Social Media).
 *
 * @return array[] Each: array( 'url', 'label', 'icon' (dashicon class) ).
 */
function dfather_social_links() {
	$networks = array(
		'facebook'  => array( 'label' => 'Facebook', 'icon' => 'dashicons-facebook' ),
		'instagram' => array( 'label' => 'Instagram', 'icon' => 'dashicons-instagram' ),
		'tiktok'    => array( 'label' => 'TikTok', 'icon' => 'dashicons-video-alt3' ),
		'youtube'   => array( 'label' => 'YouTube', 'icon' => 'dashicons-youtube' ),
		'twitter'   => array( 'label' => 'X / Twitter', 'icon' => 'dashicons-twitter' ),
	);

	$links = array();
	foreach ( $networks as $key => $meta ) {
		$url = dfather_info( $key, '' );
		if ( $url ) {
			$links[] = array(
				'url'   => $url,
				'label' => $meta['label'],
				'icon'  => $meta['icon'],
			);
		}
	}
	return $links;
}

/**
 * The canonical list of reorderable homepage section slugs. Each maps to a
 * file in template-parts/home/{slug}.php and a show_{slug} visibility toggle.
 *
 * @return string[]
 */
function dfather_home_sections() {
	return array( 'trust', 'about', 'services', 'why', 'stats', 'gallery', 'testimonials', 'faq', 'cta', 'contact' );
}

/**
 * The homepage section order chosen by the owner (Dog Father → Homepage →
 * Section Layout), validated against the known sections with any missing ones
 * appended. Falls back to the natural order.
 *
 * @return string[]
 */
function dfather_section_order() {
	$known = dfather_home_sections();
	$saved = dfather_home( 'home_section_order', '' );
	if ( ! is_array( $saved ) || empty( $saved ) ) {
		return $known;
	}
	$order = array_values( array_intersect( $saved, $known ) );
	foreach ( $known as $slug ) {
		if ( ! in_array( $slug, $order, true ) ) {
			$order[] = $slug;
		}
	}
	return $order;
}

/**
 * Build the per-section style overrides (background color + spacing) chosen in
 * the Section Layout manager, as a CSS string scoped to each .df-slot wrapper.
 *
 * @return string
 */
function dfather_section_inline_styles() {
	$space = array(
		'compact'  => '64px',
		'spacious' => '140px',
	);
	$css = '';
	foreach ( dfather_home_sections() as $slug ) {
		$rules = '';
		$bg    = (string) dfather_home( 'sec_' . $slug . '_bg', '' );
		if ( $bg && preg_match( '/^#[0-9a-fA-F]{3,8}$/', $bg ) ) {
			$rules .= 'background:' . $bg . ' !important;';
		}
		$sp = (string) dfather_home( 'sec_' . $slug . '_space', 'normal' );
		if ( isset( $space[ $sp ] ) ) {
			$rules .= 'padding-top:' . $space[ $sp ] . ' !important;padding-bottom:' . $space[ $sp ] . ' !important;';
		}
		if ( '' !== $rules ) {
			$css .= '.df-slot--' . $slug . ' > section{' . $rules . '}';
		}
	}
	return $css;
}

/**
 * Resolve how many items a homepage list should show (min 1).
 *
 * @param string $key     Setting key (e.g. count_services).
 * @param int    $default Fallback count.
 * @return int
 */
function dfather_count( $key, $default ) {
	$n = (int) dfather_home( $key, $default );
	return $n >= 1 ? $n : (int) $default;
}

/**
 * Whether a homepage section should render. Defaults to visible.
 *
 * @param string $section Section key without the show_ prefix (e.g. 'about').
 * @return bool
 */
function dfather_show( $section ) {
	$val = dfather_home( 'show_' . $section, '1' );
	return '0' !== (string) $val && false !== $val;
}

/**
 * Is this post/page built with Elementor? Used so Elementor content overrides
 * the theme's default sections seamlessly.
 *
 * @param int $post_id Post id.
 * @return bool
 */
function dfather_is_elementor( $post_id ) {
	if ( ! $post_id ) {
		return false;
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
		$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( $doc && method_exists( $doc, 'is_built_with_elementor' ) ) {
			return (bool) $doc->is_built_with_elementor();
		}
	}
	return 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

/**
 * Resolve a URL that may be stored as a path ("/book-now/") or full URL.
 *
 * @param string $url      Stored value.
 * @param string $fallback Fallback path.
 * @return string
 */
function dfather_url( $url, $fallback = '/' ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		$url = $fallback;
	}
	if ( preg_match( '#^https?://#i', $url ) ) {
		return esc_url( $url );
	}
	return esc_url( home_url( '/' . ltrim( $url, '/' ) ) );
}

/**
 * Telephone href, digits only.
 *
 * @param string $number Phone number.
 * @return string
 */
function dfather_tel( $number ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $number );
}

/**
 * Render a section opening with optional toggle + reveal animation.
 *
 * @param array $args id, class, toggle.
 * @return bool True if the section should render (caller closes it).
 */
function dfather_section_open( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'     => '',
			'class'  => '',
			'toggle' => '',
		)
	);
	if ( $args['toggle'] && ! dfather_show( $args['toggle'] ) ) {
		return false;
	}
	printf(
		'<section class="df-section %1$s df-reveal"%2$s>',
		esc_attr( $args['class'] ),
		$args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''
	);
	return true;
}
