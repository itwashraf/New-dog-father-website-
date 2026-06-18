<?php
/**
 * Base module class.
 *
 * Every feature of the Control Center lives in a self-contained module that
 * extends this class and drops a file into includes/modules/. Modules are
 * auto-discovered and registered by DFCC_Plugin, so new features can be added
 * without editing any central file.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract module.
 */
abstract class DFCC_Module {

	/**
	 * Unique module id (lowercase, dashes).
	 *
	 * @return string
	 */
	abstract public function id();

	/**
	 * Human readable label.
	 *
	 * @return string
	 */
	abstract public function label();

	/**
	 * Register WordPress hooks for this module.
	 *
	 * Called once, on plugins_loaded.
	 *
	 * @return void
	 */
	abstract public function register();

	/**
	 * Helper: render an admin view file from admin/views.
	 *
	 * @param string $view View filename without extension.
	 * @param array  $args Variables exposed to the view.
	 * @return void
	 */
	protected function view( $view, array $args = array() ) {
		$file = DFCC_PLUGIN_DIR . 'admin/views/' . $view . '.php';
		if ( ! file_exists( $file ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( "Missing view: {$view}" ) );
			return;
		}
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args, EXTR_SKIP );
		include $file;
	}
}
