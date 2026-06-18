<?php
/**
 * Fired on plugin activation.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Activator.
 */
class DFCC_Activator {

	/**
	 * Activation routine.
	 *
	 * Seeds default options and schedules a rewrite flush so freshly
	 * registered custom post types resolve to pretty permalinks.
	 *
	 * @return void
	 */
	public static function activate() {
		self::seed_default_settings();

		// Custom post types register on 'init', which has not fired yet during
		// activation, so we defer the flush to the next page load.
		update_option( 'dfcc_flush_rewrite', 1 );

		// Stamp the installed version for future migrations.
		if ( ! get_option( 'dfcc_version' ) ) {
			add_option( 'dfcc_version', DFCC_VERSION );
		}
	}

	/**
	 * Default theme / global / integration settings.
	 *
	 * Brand colors are stored as options so they can be changed from the admin
	 * and surfaced to the theme + Elementor global kit.
	 *
	 * @return void
	 */
	private static function seed_default_settings() {
		$defaults = array(
			'dfcc_theme_settings'        => array(
				'color_primary'    => '#FFF10A', // Primary Yellow.
				'color_gold'       => '#FEC208', // Luxury Gold.
				'color_dark_red'   => '#CF240A', // Dark Red.
				'color_orange'     => '#FF2D08', // Accent Orange.
				'color_black'      => '#000000', // Main background.
				'color_white'      => '#FFFFFF',
				'heading_font'     => 'Poppins',
				'body_font'        => 'Inter',
				'border_radius'    => '14',
				'dark_mode_first'  => 1,
			),
			'dfcc_global_settings'       => array(
				'business_name'    => 'The Dog Father Hotel',
				'tagline'          => 'Luxury Boarding & Care for Your Best Friend',
				'phone'            => '',
				'whatsapp'         => '',
				'email'            => '',
				'address'          => '',
				'currency'         => 'SAR',
				'maps_embed'       => '',
				'opening_hours'    => '',
			),
			'dfcc_integration_settings'  => array(),
			'dfcc_seo_settings'          => array(
				'sitewide_title_suffix' => ' | The Dog Father Hotel',
				'enable_schema'         => 1,
				'enable_sitemap'        => 1,
			),
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
