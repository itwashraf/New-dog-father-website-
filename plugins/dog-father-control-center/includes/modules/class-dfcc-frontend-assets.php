<?php
/**
 * Front-end assets module.
 *
 * Owns the public stylesheet and script shared by every front-end shortcode in
 * this plugin (services grid, gallery + lightbox, testimonial slider, booking
 * form). Assets are only enqueued when a shortcode actually needs them: a
 * shortcode calls DFCC_Frontend_Assets::need() during render, and this module
 * flushes the registered handles in wp_footer.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Frontend_Assets.
 */
class DFCC_Frontend_Assets extends DFCC_Module {

	/**
	 * Whether a shortcode has requested the assets this request.
	 *
	 * @var bool
	 */
	private static $needed = false;

	/**
	 * Style handle.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'dfcc-frontend';

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'dfcc-frontend';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'frontend-assets';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Front-end Assets', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		// Enqueue late so shortcodes rendered in the_content have flagged need().
		add_action( 'wp_footer', array( $this, 'maybe_enqueue' ), 1 );
	}

	/**
	 * Flag that the public assets are required for this request.
	 *
	 * Called by shortcode renderers. If we are already past registration the
	 * handles may still be enqueued in the footer.
	 *
	 * @return void
	 */
	public static function need() {
		self::$needed = true;
		// If scripts are already being printed (footer), enqueue immediately.
		if ( did_action( 'wp_enqueue_scripts' ) && function_exists( 'wp_style_is' ) && wp_style_is( self::STYLE_HANDLE, 'registered' ) ) {
			wp_enqueue_style( self::STYLE_HANDLE );
			wp_enqueue_script( self::SCRIPT_HANDLE );
		}
	}

	/**
	 * Register (but do not enqueue) the public assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			self::STYLE_HANDLE,
			DFCC_PLUGIN_URL . 'public/css/frontend.css',
			array(),
			defined( 'DFCC_VERSION' ) ? DFCC_VERSION : '1.0.0'
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			DFCC_PLUGIN_URL . 'public/js/frontend.js',
			array(),
			defined( 'DFCC_VERSION' ) ? DFCC_VERSION : '1.0.0',
			true
		);

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'dfccFrontend',
			array(
				'restUrl' => esc_url_raw( rest_url( 'dfcc/v1/bookings' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'sending' => __( 'Sending…', 'dog-father-control-center' ),
					'success' => __( 'Thank you! Your booking request has been received. We will contact you shortly.', 'dog-father-control-center' ),
					'error'   => __( 'Something went wrong. Please try again or contact us directly.', 'dog-father-control-center' ),
					'required' => __( 'Please fill in all required fields.', 'dog-father-control-center' ),
				),
			)
		);
	}

	/**
	 * Enqueue the assets in the footer if a shortcode requested them.
	 *
	 * @return void
	 */
	public function maybe_enqueue() {
		if ( ! self::$needed ) {
			return;
		}
		wp_enqueue_style( self::STYLE_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Frontend_Assets() );
	}
);
