<?php
/**
 * Theme Settings module.
 *
 * Provides an admin screen to edit brand colors, fonts and layout tokens and,
 * crucially, surfaces those brand colors to the public site as CSS custom
 * properties so the whole theme (and Elementor) can consume them. Changing a
 * color here changes it everywhere.
 *
 * Settings live in the 'dfcc_theme_settings' option group (seeded by the
 * activator).
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Theme_Settings.
 */
class DFCC_Theme_Settings extends DFCC_Module {

	/**
	 * Option group / option name.
	 *
	 * @var string
	 */
	const OPTION = 'dfcc_theme_settings';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'theme-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Theme Settings', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Surface brand colors to the public site and the editor.
		add_action( 'wp_head', array( $this, 'print_css_variables' ), 5 );
		add_action( 'enqueue_block_assets', array( $this, 'maybe_print_editor_variables' ) );
	}

	/**
	 * Add our admin page to the Dog Father menu.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-theme',
			'title'    => __( 'Theme Settings', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_page' ),
			'order'    => 80,
		);
		return $pages;
	}

	/**
	 * Register the setting + sanitization callback (Settings API).
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'dfcc_theme_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize the submitted theme settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$existing = get_option( self::OPTION, array() );
		$existing = is_array( $existing ) ? $existing : array();

		$color_keys = array(
			'color_primary',
			'color_gold',
			'color_dark_red',
			'color_orange',
			'color_black',
			'color_white',
		);

		$clean = $existing;

		foreach ( $color_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$color = sanitize_hex_color( $input[ $key ] );
				if ( $color ) {
					$clean[ $key ] = $color;
				}
			}
		}

		// Section / area colors. These may be intentionally left blank to fall
		// back to the brand palette, so an empty submission clears the value.
		foreach ( array_keys( self::area_colors() ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$raw = trim( (string) $input[ $key ] );
				if ( '' === $raw ) {
					$clean[ $key ] = '';
				} else {
					$color = sanitize_hex_color( $raw );
					if ( $color ) {
						$clean[ $key ] = $color;
					}
				}
			}
		}

		if ( isset( $input['heading_font'] ) ) {
			$clean['heading_font'] = sanitize_text_field( $input['heading_font'] );
		}
		if ( isset( $input['body_font'] ) ) {
			$clean['body_font'] = sanitize_text_field( $input['body_font'] );
		}
		if ( isset( $input['border_radius'] ) ) {
			$clean['border_radius'] = (string) absint( $input['border_radius'] );
		}

		$clean['dark_mode_first'] = empty( $input['dark_mode_first'] ) ? 0 : 1;

		return $clean;
	}

	/**
	 * Section / area colors: the "which part does this color affect" controls.
	 * Maps a setting key to the CSS custom property the theme consumes and a
	 * human label. When a value is empty the theme keeps its brand default.
	 *
	 * @return array key => array( 'var' => css var, 'label' => string, 'desc' => string ).
	 */
	public static function area_colors() {
		return array(
			'header_bg'   => array(
				'var'   => '--df-header-bg',
				'label' => __( 'Header background', 'dog-father-control-center' ),
				'desc'  => __( 'The top menu bar background.', 'dog-father-control-center' ),
			),
			'header_text' => array(
				'var'   => '--df-header-text',
				'label' => __( 'Header text & menu links', 'dog-father-control-center' ),
				'desc'  => __( 'Logo text and navigation links.', 'dog-father-control-center' ),
			),
			'accent'      => array(
				'var'   => '--df-accent',
				'label' => __( 'Accents (eyebrows, icons, links)', 'dog-father-control-center' ),
				'desc'  => __( 'Small highlight text and section icons.', 'dog-father-control-center' ),
			),
			'btn_bg'      => array(
				'var'   => '--df-btn-bg',
				'label' => __( 'Buttons background', 'dog-father-control-center' ),
				'desc'  => __( 'Primary “Book Now” style buttons.', 'dog-father-control-center' ),
			),
			'btn_text'    => array(
				'var'   => '--df-btn-text',
				'label' => __( 'Buttons text', 'dog-father-control-center' ),
				'desc'  => __( 'The label color inside primary buttons.', 'dog-father-control-center' ),
			),
			'footer_bg'   => array(
				'var'   => '--df-footer-bg',
				'label' => __( 'Footer background', 'dog-father-control-center' ),
				'desc'  => __( 'The bottom site footer background.', 'dog-father-control-center' ),
			),
		);
	}

	/**
	 * List of common Google fonts offered in the selects.
	 *
	 * @return string[]
	 */
	public function font_choices() {
		return array(
			'Poppins',
			'Inter',
			'Montserrat',
			'Roboto',
			'Open Sans',
			'Lato',
			'Raleway',
			'Playfair Display',
			'Oswald',
			'Nunito',
			'Merriweather',
			'Work Sans',
			'Cairo',
			'Tajawal',
		);
	}

	/**
	 * Render the admin screen via the view.
	 *
	 * @return void
	 */
	public function render_page() {
		$settings = get_option( self::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();

		$this->view(
			'theme-settings',
			array(
				'settings' => $settings,
				'fonts'    => $this->font_choices(),
				'module'   => $this,
			)
		);
	}

	/**
	 * Print the live brand colors as CSS custom properties.
	 *
	 * This is what lets the theme + Elementor consume the configured palette.
	 *
	 * @return void
	 */
	public function print_css_variables() {
		$primary  = dfcc_brand_color( 'primary' );
		$gold     = dfcc_brand_color( 'gold' );
		$dark_red = dfcc_brand_color( 'dark_red' );
		$orange   = dfcc_brand_color( 'orange' );
		$black    = dfcc_brand_color( 'black' );
		$white    = dfcc_brand_color( 'white' );
		$radius   = absint( dfcc_get_setting( self::OPTION, 'border_radius', '14' ) );

		$heading_font = dfcc_get_setting( self::OPTION, 'heading_font', 'Poppins' );
		$body_font    = dfcc_get_setting( self::OPTION, 'body_font', 'Inter' );

		$css  = ':root{';
		$css .= '--dfcc-primary:' . $primary . ';';
		$css .= '--dfcc-gold:' . $gold . ';';
		$css .= '--dfcc-dark-red:' . $dark_red . ';';
		$css .= '--dfcc-orange:' . $orange . ';';
		$css .= '--dfcc-black:' . $black . ';';
		$css .= '--dfcc-white:' . $white . ';';
		$css .= '--dfcc-radius:' . $radius . 'px;';
		$css .= '--dfcc-heading-font:' . $this->font_stack( $heading_font ) . ';';
		$css .= '--dfcc-body-font:' . $this->font_stack( $body_font ) . ';';

		// Section / area color overrides — only emitted when the owner set one,
		// so the theme's brand defaults stay in effect otherwise.
		foreach ( self::area_colors() as $key => $meta ) {
			$value = dfcc_get_setting( self::OPTION, $key, '' );
			$value = sanitize_hex_color( (string) $value );
			if ( $value ) {
				$css .= $meta['var'] . ':' . $value . ';';
			}
		}

		$css .= '}';

		printf( "<style id=\"dfcc-brand-vars\">%s</style>\n", $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values sanitized above (hex colors / int / font name) and CSS cannot be escaped with esc_html.
	}

	/**
	 * Also surface the variables inside the block editor iframe.
	 *
	 * @return void
	 */
	public function maybe_print_editor_variables() {
		if ( is_admin() ) {
			$this->print_css_variables();
		}
	}

	/**
	 * Build a safe CSS font-family stack from a font name.
	 *
	 * @param string $font Font name.
	 * @return string
	 */
	private function font_stack( $font ) {
		$font = trim( preg_replace( '/[^A-Za-z0-9 \-]/', '', (string) $font ) );
		if ( '' === $font ) {
			$font = 'sans-serif';
		}
		return '"' . $font . '", sans-serif';
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Theme_Settings() );
	}
);
