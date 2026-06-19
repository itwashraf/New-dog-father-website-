<?php
/**
 * Global Settings module.
 *
 * Stores the business identity / contact information in the
 * 'dfcc_global_settings' option group and exposes it to Elementor pages through
 * a set of simple shortcodes so the same details never have to be hardcoded.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Global_Settings.
 */
class DFCC_Global_Settings extends DFCC_Module {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION = 'dfcc_global_settings';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'global-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Global Settings', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Add the admin page.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-global',
			'title'    => __( 'Global Settings', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_page' ),
			'order'    => 90,
		);
		return $pages;
	}

	/**
	 * Register the setting with sanitization.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'dfcc_global_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize the submitted global settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$existing = get_option( self::OPTION, array() );
		$clean    = is_array( $existing ) ? $existing : array();

		if ( isset( $input['business_name'] ) ) {
			$clean['business_name'] = sanitize_text_field( $input['business_name'] );
		}
		if ( isset( $input['tagline'] ) ) {
			$clean['tagline'] = sanitize_text_field( $input['tagline'] );
		}
		if ( isset( $input['phone'] ) ) {
			$clean['phone'] = sanitize_text_field( $input['phone'] );
		}
		if ( isset( $input['whatsapp'] ) ) {
			$clean['whatsapp'] = sanitize_text_field( $input['whatsapp'] );
		}
		if ( isset( $input['email'] ) ) {
			$clean['email'] = sanitize_email( $input['email'] );
		}
		if ( isset( $input['address'] ) ) {
			$clean['address'] = sanitize_textarea_field( $input['address'] );
		}
		if ( isset( $input['currency'] ) ) {
			$clean['currency'] = sanitize_text_field( $input['currency'] );
		}
		if ( isset( $input['opening_hours'] ) ) {
			$clean['opening_hours'] = sanitize_textarea_field( $input['opening_hours'] );
		}
		if ( isset( $input['maps_embed'] ) ) {
			$clean['maps_embed'] = $this->sanitize_iframe( $input['maps_embed'] );
		}

		// Social profiles (full URLs).
		foreach ( array_keys( self::social_networks() ) as $network ) {
			if ( isset( $input[ $network ] ) ) {
				$clean[ $network ] = esc_url_raw( trim( $input[ $network ] ) );
			}
		}

		// Footer credit.
		if ( isset( $input['footer_credit_text'] ) ) {
			$clean['footer_credit_text'] = sanitize_text_field( $input['footer_credit_text'] );
		}
		if ( isset( $input['footer_credit_url'] ) ) {
			$clean['footer_credit_url'] = esc_url_raw( trim( $input['footer_credit_url'] ) );
		}
		$clean['hide_footer_credit'] = empty( $input['hide_footer_credit'] ) ? 0 : 1;

		return $clean;
	}

	/**
	 * Supported social networks: key => array( label, dashicon ).
	 *
	 * @return array
	 */
	public static function social_networks() {
		return array(
			'facebook'  => array( 'label' => __( 'Facebook', 'dog-father-control-center' ), 'icon' => 'dashicons-facebook' ),
			'instagram' => array( 'label' => __( 'Instagram', 'dog-father-control-center' ), 'icon' => 'dashicons-instagram' ),
			'tiktok'    => array( 'label' => __( 'TikTok', 'dog-father-control-center' ), 'icon' => 'dashicons-video-alt3' ),
			'youtube'   => array( 'label' => __( 'YouTube', 'dog-father-control-center' ), 'icon' => 'dashicons-youtube' ),
			'twitter'   => array( 'label' => __( 'X / Twitter', 'dog-father-control-center' ), 'icon' => 'dashicons-twitter' ),
		);
	}

	/**
	 * Allow only a safe Google Maps iframe in the maps embed field.
	 *
	 * @param string $html Raw HTML.
	 * @return string
	 */
	private function sanitize_iframe( $html ) {
		$allowed = array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'style'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'loading'         => true,
				'referrerpolicy'  => true,
				'title'           => true,
				'aria-label'      => true,
			),
		);
		return wp_kses( (string) $html, $allowed );
	}

	/**
	 * Render the admin screen.
	 *
	 * @return void
	 */
	public function render_page() {
		$settings = get_option( self::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();

		$get = static function ( $key ) use ( $settings ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : '';
		};
		?>
		<div class="wrap dfcc-wrap">
			<h1 class="dfcc-title">
				<span class="dashicons dashicons-store"></span>
				<?php esc_html_e( 'Global Settings', 'dog-father-control-center' ); ?>
			</h1>
			<p class="dfcc-subtitle">
				<?php esc_html_e( 'Your business details. Drop these anywhere in Elementor with shortcodes such as [dfcc_phone], [dfcc_whatsapp], [dfcc_email], [dfcc_address], [dfcc_business_name], [dfcc_map] and [dfcc_hours].', 'dog-father-control-center' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'dfcc_global_settings_group' ); ?>

				<div class="dfcc-panel">
					<h2 class="dfcc-section-title"><?php esc_html_e( 'Business Identity', 'dog-father-control-center' ); ?></h2>

					<div class="dfcc-field">
						<label for="business_name"><?php esc_html_e( 'Business Name', 'dog-father-control-center' ); ?></label>
						<input type="text" class="regular-text" id="business_name" name="<?php echo esc_attr( self::OPTION . '[business_name]' ); ?>" value="<?php echo esc_attr( $get( 'business_name' ) ); ?>" />
					</div>

					<div class="dfcc-field">
						<label for="tagline"><?php esc_html_e( 'Tagline', 'dog-father-control-center' ); ?></label>
						<input type="text" class="regular-text" id="tagline" name="<?php echo esc_attr( self::OPTION . '[tagline]' ); ?>" value="<?php echo esc_attr( $get( 'tagline' ) ); ?>" />
					</div>

					<div class="dfcc-field">
						<label for="currency"><?php esc_html_e( 'Currency Code', 'dog-father-control-center' ); ?></label>
						<input type="text" class="small-text" id="currency" name="<?php echo esc_attr( self::OPTION . '[currency]' ); ?>" value="<?php echo esc_attr( $get( 'currency' ) ); ?>" />
					</div>
				</div>

				<div class="dfcc-panel">
					<h2 class="dfcc-section-title"><?php esc_html_e( 'Contact', 'dog-father-control-center' ); ?></h2>

					<div class="dfcc-field">
						<label for="phone"><?php esc_html_e( 'Phone', 'dog-father-control-center' ); ?></label>
						<input type="text" class="regular-text" id="phone" name="<?php echo esc_attr( self::OPTION . '[phone]' ); ?>" value="<?php echo esc_attr( $get( 'phone' ) ); ?>" />
					</div>

					<div class="dfcc-field">
						<label for="whatsapp"><?php esc_html_e( 'WhatsApp Number', 'dog-father-control-center' ); ?></label>
						<input type="text" class="regular-text" id="whatsapp" name="<?php echo esc_attr( self::OPTION . '[whatsapp]' ); ?>" value="<?php echo esc_attr( $get( 'whatsapp' ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Digits only, including country code (e.g. 9665XXXXXXXX).', 'dog-father-control-center' ); ?></p>
					</div>

					<div class="dfcc-field">
						<label for="email"><?php esc_html_e( 'Email', 'dog-father-control-center' ); ?></label>
						<input type="email" class="regular-text" id="email" name="<?php echo esc_attr( self::OPTION . '[email]' ); ?>" value="<?php echo esc_attr( $get( 'email' ) ); ?>" />
					</div>

					<div class="dfcc-field">
						<label for="address"><?php esc_html_e( 'Address', 'dog-father-control-center' ); ?></label>
						<textarea id="address" rows="3" class="large-text" name="<?php echo esc_attr( self::OPTION . '[address]' ); ?>"><?php echo esc_textarea( $get( 'address' ) ); ?></textarea>
					</div>

					<div class="dfcc-field">
						<label for="opening_hours"><?php esc_html_e( 'Opening Hours', 'dog-father-control-center' ); ?></label>
						<textarea id="opening_hours" rows="4" class="large-text" name="<?php echo esc_attr( self::OPTION . '[opening_hours]' ); ?>"><?php echo esc_textarea( $get( 'opening_hours' ) ); ?></textarea>
					</div>
				</div>

				<div class="dfcc-panel">
					<h2 class="dfcc-section-title"><?php esc_html_e( 'Social Media', 'dog-father-control-center' ); ?></h2>
					<p class="description" style="margin-bottom:14px;"><?php esc_html_e( 'Paste the full link to each profile (e.g. https://instagram.com/yourpage). Empty ones are hidden. Icons appear in the footer.', 'dog-father-control-center' ); ?></p>
					<?php foreach ( self::social_networks() as $network => $meta ) : ?>
						<div class="dfcc-field">
							<label for="<?php echo esc_attr( $network ); ?>"><?php echo esc_html( $meta['label'] ); ?></label>
							<input type="url" class="regular-text" id="<?php echo esc_attr( $network ); ?>" name="<?php echo esc_attr( self::OPTION . '[' . $network . ']' ); ?>" value="<?php echo esc_attr( $get( $network ) ); ?>" placeholder="https://" />
						</div>
					<?php endforeach; ?>
				</div>

				<div class="dfcc-panel">
					<h2 class="dfcc-section-title"><?php esc_html_e( 'Footer Credit', 'dog-father-control-center' ); ?></h2>
					<div class="dfcc-field">
						<label for="footer_credit_text"><?php esc_html_e( 'Credit Text', 'dog-father-control-center' ); ?></label>
						<input type="text" class="regular-text" id="footer_credit_text" name="<?php echo esc_attr( self::OPTION . '[footer_credit_text]' ); ?>" value="<?php echo esc_attr( $get( 'footer_credit_text' ) ); ?>" placeholder="Provada" />
					</div>
					<div class="dfcc-field">
						<label for="footer_credit_url"><?php esc_html_e( 'Credit Link', 'dog-father-control-center' ); ?></label>
						<input type="url" class="regular-text" id="footer_credit_url" name="<?php echo esc_attr( self::OPTION . '[footer_credit_url]' ); ?>" value="<?php echo esc_attr( $get( 'footer_credit_url' ) ); ?>" placeholder="https://provada.net" />
					</div>
					<div class="dfcc-field">
						<label for="hide_footer_credit">
							<input type="checkbox" id="hide_footer_credit" name="<?php echo esc_attr( self::OPTION . '[hide_footer_credit]' ); ?>" value="1" <?php checked( ! empty( $settings['hide_footer_credit'] ) ); ?> />
							<?php esc_html_e( 'Hide the "Designed & developed by" credit line', 'dog-father-control-center' ); ?>
						</label>
					</div>
				</div>

				<div class="dfcc-panel">
					<h2 class="dfcc-section-title"><?php esc_html_e( 'Map', 'dog-father-control-center' ); ?></h2>
					<div class="dfcc-field">
						<label for="maps_embed"><?php esc_html_e( 'Google Maps Embed (iframe)', 'dog-father-control-center' ); ?></label>
						<textarea id="maps_embed" rows="4" class="large-text code" name="<?php echo esc_attr( self::OPTION . '[maps_embed]' ); ?>"><?php echo esc_textarea( $get( 'maps_embed' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Paste the <iframe> embed code from Google Maps. Only iframe markup is kept. Output with [dfcc_map].', 'dog-father-control-center' ); ?></p>
					</div>
				</div>

				<?php submit_button( __( 'Save Global Settings', 'dog-father-control-center' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the public shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'dfcc_phone', array( $this, 'sc_phone' ) );
		add_shortcode( 'dfcc_whatsapp', array( $this, 'sc_whatsapp' ) );
		add_shortcode( 'dfcc_email', array( $this, 'sc_email' ) );
		add_shortcode( 'dfcc_address', array( $this, 'sc_address' ) );
		add_shortcode( 'dfcc_business_name', array( $this, 'sc_business_name' ) );
		add_shortcode( 'dfcc_map', array( $this, 'sc_map' ) );
		add_shortcode( 'dfcc_hours', array( $this, 'sc_hours' ) );
	}

	/**
	 * Read a global setting value.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	private function get( $key ) {
		return (string) dfcc_get_setting( self::OPTION, $key, '' );
	}

	/**
	 * [dfcc_phone] — clickable tel: link.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function sc_phone( $atts ) {
		$atts  = shortcode_atts( array( 'label' => '' ), $atts, 'dfcc_phone' );
		$phone = $this->get( 'phone' );
		if ( '' === $phone ) {
			return '';
		}
		$label = '' !== $atts['label'] ? $atts['label'] : $phone;
		$tel   = preg_replace( '/[^0-9+]/', '', $phone );
		return sprintf(
			'<a class="dfcc-phone" href="tel:%s">%s</a>',
			esc_attr( $tel ),
			esc_html( $label )
		);
	}

	/**
	 * [dfcc_whatsapp] — wa.me link.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function sc_whatsapp( $atts ) {
		$atts = shortcode_atts(
			array(
				'label' => __( 'Chat on WhatsApp', 'dog-father-control-center' ),
				'text'  => '',
			),
			$atts,
			'dfcc_whatsapp'
		);
		$number = preg_replace( '/[^0-9]/', '', $this->get( 'whatsapp' ) );
		if ( '' === $number ) {
			return '';
		}
		$url = 'https://wa.me/' . $number;
		if ( '' !== $atts['text'] ) {
			$url = add_query_arg( 'text', rawurlencode( $atts['text'] ), $url );
		}
		return sprintf(
			'<a class="dfcc-whatsapp" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $url ),
			esc_html( $atts['label'] )
		);
	}

	/**
	 * [dfcc_email] — mailto link.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function sc_email( $atts ) {
		$atts  = shortcode_atts( array( 'label' => '' ), $atts, 'dfcc_email' );
		$email = $this->get( 'email' );
		if ( '' === $email || ! is_email( $email ) ) {
			return '';
		}
		$label = '' !== $atts['label'] ? $atts['label'] : $email;
		return sprintf(
			'<a class="dfcc-email" href="%s">%s</a>',
			esc_url( 'mailto:' . $email ),
			esc_html( $label )
		);
	}

	/**
	 * [dfcc_address] — address text.
	 *
	 * @return string
	 */
	public function sc_address() {
		$address = $this->get( 'address' );
		if ( '' === $address ) {
			return '';
		}
		return '<span class="dfcc-address">' . nl2br( esc_html( $address ) ) . '</span>';
	}

	/**
	 * [dfcc_business_name] — business name text.
	 *
	 * @return string
	 */
	public function sc_business_name() {
		return esc_html( $this->get( 'business_name' ) );
	}

	/**
	 * [dfcc_map] — the stored Google Maps iframe.
	 *
	 * @return string
	 */
	public function sc_map() {
		$embed = $this->get( 'maps_embed' );
		if ( '' === $embed ) {
			return '';
		}
		// Already sanitized on save; re-run kses for defence in depth.
		return '<div class="dfcc-map">' . $this->sanitize_iframe( $embed ) . '</div>';
	}

	/**
	 * [dfcc_hours] — opening hours text.
	 *
	 * @return string
	 */
	public function sc_hours() {
		$hours = $this->get( 'opening_hours' );
		if ( '' === $hours ) {
			return '';
		}
		return '<div class="dfcc-hours">' . nl2br( esc_html( $hours ) ) . '</div>';
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Global_Settings() );
	}
);
