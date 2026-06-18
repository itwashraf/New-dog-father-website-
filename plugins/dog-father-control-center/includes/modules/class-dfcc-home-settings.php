<?php
/**
 * Homepage settings module.
 *
 * Lets the owner edit the ready-made homepage the Dog Father theme renders
 * (hero, about, services intro, stats, section visibility, CTA, etc.) with no
 * code. Stored in the 'dfcc_home_settings' option group and read by the theme
 * via dfather_home().
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Home_Settings.
 */
class DFCC_Home_Settings extends DFCC_Module {

	const OPTION = 'dfcc_home_settings';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'home-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Homepage', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register page in the Dog Father menu.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-home',
			'title'    => __( 'Homepage', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_page' ),
			'order'    => 30,
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
			'dfcc_home_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Field definitions: key => type. Drives both sanitization and the view.
	 *
	 * @return array
	 */
	public function fields() {
		return array(
			// Hero.
			'hero_eyebrow'         => 'text',
			'hero_title'           => 'text',
			'hero_subtitle'        => 'textarea',
			'hero_bg_id'           => 'media',
			'hero_primary_label'   => 'text',
			'hero_primary_url'     => 'url',
			'hero_secondary_label' => 'text',
			'hero_secondary_url'   => 'url',
			// About.
			'about_eyebrow'        => 'text',
			'about_title'          => 'text',
			'about_text'           => 'textarea',
			'about_image_id'       => 'media',
			// Services intro.
			'services_eyebrow'     => 'text',
			'services_title'       => 'text',
			// Stats.
			'stat1_number'         => 'text',
			'stat1_label'          => 'text',
			'stat2_number'         => 'text',
			'stat2_label'          => 'text',
			'stat3_number'         => 'text',
			'stat3_label'          => 'text',
			'stat4_number'         => 'text',
			'stat4_label'          => 'text',
			// Titles.
			'gallery_title'        => 'text',
			'testimonials_title'   => 'text',
			'faq_title'            => 'text',
			// CTA.
			'cta_title'            => 'text',
			'cta_text'            => 'textarea',
			'cta_button_label'     => 'text',
			'cta_button_url'       => 'url',
			'contact_title'        => 'text',
		);
	}

	/**
	 * Section visibility toggle keys.
	 *
	 * @return array
	 */
	public function toggles() {
		return array(
			'show_trust'        => __( 'Trust indicators', 'dog-father-control-center' ),
			'show_about'        => __( 'About section', 'dog-father-control-center' ),
			'show_services'     => __( 'Services', 'dog-father-control-center' ),
			'show_why'          => __( 'Why choose us', 'dog-father-control-center' ),
			'show_suites'       => __( 'Suites', 'dog-father-control-center' ),
			'show_gallery'      => __( 'Gallery', 'dog-father-control-center' ),
			'show_testimonials' => __( 'Testimonials', 'dog-father-control-center' ),
			'show_stats'        => __( 'Statistics', 'dog-father-control-center' ),
			'show_faq'          => __( 'FAQ', 'dog-father-control-center' ),
			'show_cta'          => __( 'Call to action', 'dog-father-control-center' ),
			'show_contact'      => __( 'Contact', 'dog-father-control-center' ),
		);
	}

	/**
	 * Default values used when a field is empty.
	 *
	 * @return array
	 */
	public function defaults() {
		return array(
			'hero_eyebrow'         => __( 'Welcome to The Dog Father Hotel', 'dog-father-control-center' ),
			'hero_title'           => __( "Egypt's Trusted Dog Boarding Hotel", 'dog-father-control-center' ),
			'hero_subtitle'        => __( "Professional boarding, daily exercise, medication administration, and loving care for your dog while you're away.", 'dog-father-control-center' ),
			'hero_primary_label'   => __( 'Book a Stay', 'dog-father-control-center' ),
			'hero_primary_url'     => '/book-now/',
			'hero_secondary_label' => __( 'Contact on WhatsApp', 'dog-father-control-center' ),
			'hero_secondary_url'   => 'https://wa.me/201094622999',
			'about_eyebrow'        => __( 'About Us', 'dog-father-control-center' ),
			'about_title'          => __( 'Welcome to The Dog Father Hotel', 'dog-father-control-center' ),
			'about_text'           => __( "A home away from home for your beloved pets. Founded by dog lovers with a commitment to professional care, The Dog Father Hotel is Egypt's premier dog boarding facility. We understand every dog is unique — our experienced handlers go the extra mile with special diets, cozy spaces, daily exercise and round-the-clock supervision, so you can travel knowing your best friend is safe, loved and comfortable.", 'dog-father-control-center' ),
			'services_eyebrow'     => __( 'What We Offer', 'dog-father-control-center' ),
			'services_title'       => __( 'Our Top-Notch Services', 'dog-father-control-center' ),
			'stat1_number'         => '7+',
			'stat1_label'          => __( 'Years of Experience', 'dog-father-control-center' ),
			'stat2_number'         => '5,000+',
			'stat2_label'          => __( 'Wagging Tails Served', 'dog-father-control-center' ),
			'stat3_number'         => '97%',
			'stat3_label'          => __( 'Satisfied Customers', 'dog-father-control-center' ),
			'stat4_number'         => '80%',
			'stat4_label'          => __( 'Loyal Returning Customers', 'dog-father-control-center' ),
			'gallery_title'        => __( 'Life at the Hotel', 'dog-father-control-center' ),
			'testimonials_title'   => __( 'Loved by Dog Parents', 'dog-father-control-center' ),
			'faq_title'            => __( 'Frequently Asked Questions', 'dog-father-control-center' ),
			'cta_title'            => __( "Book Your Dog's Next Vacation With Us", 'dog-father-control-center' ),
			'cta_text'             => __( 'Give your furry friend the ultimate vacation experience at our luxury dog boarding hotel. Join our growing pack of happy customers today.', 'dog-father-control-center' ),
			'cta_button_label'     => __( 'Start Booking', 'dog-father-control-center' ),
			'cta_button_url'       => '/book-now/',
			'contact_title'        => __( "Let's Chat", 'dog-father-control-center' ),
		);
	}

	/**
	 * Sanitize submitted values.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		foreach ( $this->fields() as $key => $type ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}
			switch ( $type ) {
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $input[ $key ] );
					break;
				case 'url':
					$clean[ $key ] = esc_url_raw( trim( $input[ $key ] ) );
					break;
				case 'media':
					$clean[ $key ] = absint( $input[ $key ] );
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		// Checkboxes: present = 1, absent = 0.
		foreach ( array_keys( $this->toggles() ) as $key ) {
			$clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}

		return $clean;
	}

	/**
	 * Render the screen.
	 *
	 * @return void
	 */
	public function render_page() {
		$settings = get_option( self::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();

		$this->view(
			'home-settings',
			array(
				'settings' => $settings,
				'defaults' => $this->defaults(),
				'toggles'  => $this->toggles(),
				'module'   => $this,
			)
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Home_Settings() );
	}
);
