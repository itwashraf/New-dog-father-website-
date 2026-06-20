<?php
/**
 * Plugin Name:       Dog Father Control Center
 * Plugin URI:        https://thedogfatherhotel.com
 * Description:        The business control center for The Dog Father Hotel — bookings, dog profiles, services, gallery, testimonials, SEO, integrations, one-click setup and more. Built by Provada (provada.net).
 * Version:           1.3.5
 * Release:            TDF_CP_M5
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            The Dog Father Hotel
 * Author URI:        https://thedogfatherhotel.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dog-father-control-center
 * Domain Path:       /languages
 *
 * @package DogFatherControlCenter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin constants.
 */
define( 'DFCC_VERSION', '1.3.5' );
define( 'DFCC_RELEASE', 'TDF_CP_M5' );
define( 'DFCC_PLUGIN_FILE', __FILE__ );
define( 'DFCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DFCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DFCC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Core includes (load order matters for the base classes only).
 */
require_once DFCC_PLUGIN_DIR . 'includes/dfcc-helpers.php';
require_once DFCC_PLUGIN_DIR . 'includes/class-dfcc-module.php';
require_once DFCC_PLUGIN_DIR . 'includes/class-dfcc-plugin.php';
require_once DFCC_PLUGIN_DIR . 'includes/class-dfcc-activator.php';
require_once DFCC_PLUGIN_DIR . 'includes/class-dfcc-deactivator.php';

/**
 * Activation / deactivation hooks.
 */
register_activation_hook( __FILE__, array( 'DFCC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'DFCC_Deactivator', 'deactivate' ) );

/**
 * Main accessor.
 *
 * @return DFCC_Plugin
 */
function dfcc() {
	return DFCC_Plugin::instance();
}

// Boot the plugin.
dfcc();
