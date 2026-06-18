<?php
/**
 * Main plugin orchestrator.
 *
 * Auto-discovers and boots every module in includes/modules/.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Plugin singleton.
 */
final class DFCC_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var DFCC_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Registered modules keyed by id.
	 *
	 * @var DFCC_Module[]
	 */
	private $modules = array();

	/**
	 * Get the singleton.
	 *
	 * @return DFCC_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — wires up the boot sequence.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'boot' ), 20 );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'dog-father-control-center', false, dirname( DFCC_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Discover, instantiate and register every module.
	 *
	 * @return void
	 */
	public function boot() {
		$files = glob( DFCC_PLUGIN_DIR . 'includes/modules/class-dfcc-*.php' );
		if ( ! is_array( $files ) ) {
			return;
		}
		sort( $files );

		foreach ( $files as $file ) {
			require_once $file;
		}

		/**
		 * Modules self-register here. Each module file adds an instance via
		 * dfcc()->add_module( new DFCC_Something() ); hooked to this action.
		 *
		 * @param DFCC_Plugin $plugin The plugin instance.
		 */
		do_action( 'dfcc_register_modules', $this );

		foreach ( $this->modules as $module ) {
			$module->register();
		}

		/**
		 * Fires after all modules have been registered.
		 *
		 * @param DFCC_Plugin $plugin The plugin instance.
		 */
		do_action( 'dfcc_loaded', $this );
	}

	/**
	 * Register a module instance.
	 *
	 * @param DFCC_Module $module Module instance.
	 * @return void
	 */
	public function add_module( DFCC_Module $module ) {
		$this->modules[ $module->id() ] = $module;
	}

	/**
	 * Get a module by id.
	 *
	 * @param string $id Module id.
	 * @return DFCC_Module|null
	 */
	public function module( $id ) {
		return isset( $this->modules[ $id ] ) ? $this->modules[ $id ] : null;
	}

	/**
	 * All registered modules.
	 *
	 * @return DFCC_Module[]
	 */
	public function modules() {
		return $this->modules;
	}
}
