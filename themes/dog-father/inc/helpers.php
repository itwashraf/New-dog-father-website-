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
