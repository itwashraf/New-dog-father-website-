<?php
/**
 * Shared helper functions used across modules.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a value from one of the plugin's settings option groups.
 *
 * @param string $group   Option name, e.g. 'dfcc_theme_settings'.
 * @param string $key     Key within the option array.
 * @param mixed  $default Fallback when the key is missing.
 * @return mixed
 */
function dfcc_get_setting( $group, $key, $default = '' ) {
	$settings = get_option( $group, array() );
	if ( is_array( $settings ) && isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}
	return $default;
}

/**
 * Convenience accessor for a brand color with sane fallbacks.
 *
 * @param string $slug One of: primary, gold, dark_red, orange, black, white.
 * @return string Hex color.
 */
function dfcc_brand_color( $slug ) {
	$fallbacks = array(
		'primary'  => '#FFF10A',
		'gold'     => '#FEC208',
		'dark_red' => '#CF240A',
		'orange'   => '#FF2D08',
		'black'    => '#000000',
		'white'    => '#FFFFFF',
	);
	$key = 'color_' . $slug;
	return dfcc_get_setting( 'dfcc_theme_settings', $key, isset( $fallbacks[ $slug ] ) ? $fallbacks[ $slug ] : '#000000' );
}

/**
 * The capability required to manage the Control Center.
 *
 * @return string
 */
function dfcc_admin_cap() {
	/**
	 * Filter the management capability.
	 *
	 * @param string $cap Capability slug.
	 */
	return apply_filters( 'dfcc_admin_capability', 'manage_options' );
}

/**
 * Top-level admin menu slug. Submenus hang off this.
 *
 * @return string
 */
function dfcc_menu_slug() {
	return 'dfcc-dashboard';
}

/**
 * Format a stored amount with the configured currency.
 *
 * @param float|int|string $amount Raw amount.
 * @return string
 */
function dfcc_money( $amount ) {
	$currency = dfcc_get_setting( 'dfcc_global_settings', 'currency', 'SAR' );
	return esc_html( $currency . ' ' . number_format_i18n( (float) $amount, 2 ) );
}
