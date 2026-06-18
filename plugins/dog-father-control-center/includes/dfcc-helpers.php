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
 * Purge the most common caching layers after the plugin changes content.
 *
 * Keeps the public site from showing a stale version after editing services,
 * the homepage, or running the official-content reset. Safely no-ops when a
 * given cache plugin or host feature is not present.
 *
 * @return void
 */
function dfcc_purge_caches() {
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	// LiteSpeed Cache.
	do_action( 'litespeed_purge_all' );

	// WP Rocket.
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}

	// W3 Total Cache.
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	}

	// WP Super Cache.
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}

	// WP Fastest Cache.
	if ( isset( $GLOBALS['wp_fastest_cache'] ) && is_object( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
		$GLOBALS['wp_fastest_cache']->deleteCache( true );
	}

	// SG Optimizer, Cache Enabler, Autoptimize, Breeze, Hummingbird.
	do_action( 'sg_cachepress_purge_cache' );
	do_action( 'cache_enabler_clear_complete_cache' );
	do_action( 'autoptimize_action_cachepurged' );
	do_action( 'breeze_clear_all_cache' );
	do_action( 'wphb_clear_page_cache' );

	/**
	 * Allow integrations to flush their own cache.
	 */
	do_action( 'dfcc_purge_caches' );
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
	$currency = dfcc_get_setting( 'dfcc_global_settings', 'currency', 'EGP' );
	$amount   = (float) $amount;
	// Whole numbers render without trailing decimals (e.g. "500 EGP"); otherwise keep 2dp.
	$decimals = ( floor( $amount ) === $amount ) ? 0 : 2;
	return esc_html( number_format_i18n( $amount, $decimals ) . ' ' . $currency );
}
