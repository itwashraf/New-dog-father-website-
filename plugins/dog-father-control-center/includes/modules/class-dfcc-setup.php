<?php
/**
 * Setup module — one-click / automatic site setup.
 *
 * When the Dog Father theme is activated this creates all pages, menus and
 * demo content so the site is instantly complete and editable. Everything is
 * idempotent: running it again never creates duplicates.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Setup.
 */
class DFCC_Setup extends DFCC_Module {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'setup';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Setup', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'after_switch_theme', array( $this, 'maybe_auto_setup' ) );
		add_action( 'admin_post_dfcc_run_setup', array( $this, 'handle_run' ) );
		add_action( 'admin_post_dfcc_reset_content', array( $this, 'handle_reset' ) );
		add_action( 'admin_notices', array( $this, 'first_run_notice' ) );
	}

	/**
	 * Register admin page.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-setup',
			'title'    => __( 'Setup', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_page' ),
			'order'    => 5,
		);
		return $pages;
	}

	/**
	 * Auto-run once when the Dog Father theme becomes active.
	 *
	 * @return void
	 */
	public function maybe_auto_setup() {
		if ( 'dog-father' !== get_template() ) {
			return;
		}
		if ( get_option( 'dfcc_setup_done' ) ) {
			return;
		}
		$this->run();
	}

	/**
	 * Handle the manual "Run Setup" button.
	 *
	 * @return void
	 */
	public function handle_run() {
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'dog-father-control-center' ) );
		}
		check_admin_referer( 'dfcc_run_setup' );

		$this->run();

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-setup', 'dfcc_setup' => 'done' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle "Reset to official content" — overwrites homepage/global content and
	 * replaces demo services, testimonials, FAQs and gallery with the official
	 * Dog Father content.
	 *
	 * @return void
	 */
	public function handle_reset() {
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'dog-father-control-center' ) );
		}
		check_admin_referer( 'dfcc_reset_content' );

		$this->reset();

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-setup', 'dfcc_setup' => 'reset' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Force-apply the official content: wipe demo CPT content, overwrite the
	 * homepage + global settings, then re-run the full build.
	 *
	 * @return void
	 */
	public function reset() {
		// Remove auto-generated demo content so it can be reseeded cleanly.
		foreach ( array( 'dfcc_service', 'dfcc_testimonial', 'dfcc_faq', 'dfcc_gallery' ) as $type ) {
			$ids = get_posts(
				array(
					'post_type'        => $type,
					'post_status'      => 'any',
					'numberposts'      => -1,
					'fields'           => 'ids',
					'suppress_filters' => true,
				)
			);
			foreach ( $ids as $pid ) {
				wp_delete_post( $pid, true );
			}
		}

		// Force-overwrite homepage + global content.
		$this->seed_home_defaults( true );
		$this->seed_global( true );

		// Rebuild everything (pages/menus/services/testimonials/faqs/gallery).
		$this->run();
	}

	/**
	 * Seed the business/global settings.
	 *
	 * @param bool $force Overwrite existing core values when true.
	 * @return void
	 */
	private function seed_global( $force = false ) {
		$current = get_option( 'dfcc_global_settings', array() );
		$current = is_array( $current ) ? $current : array();

		$official = array(
			'business_name' => 'The Dog Father Hotel',
			'tagline'       => "Egypt's Trusted Dog Boarding Hotel",
			'phone'         => '+201094622999',
			'whatsapp'      => '+201094622999',
			'email'         => 'egy.dog.hotel@gmail.com',
			'address'       => 'Cairo & Giza, Egypt',
			'currency'      => 'EGP',
			'opening_hours' => "Housekeeping every hour, 6:00 AM – 11:30 PM\nBoarding & care 24/7",
		);

		foreach ( $official as $key => $val ) {
			if ( $force || empty( $current[ $key ] ) ) {
				$current[ $key ] = $val;
			}
		}
		update_option( 'dfcc_global_settings', $current );
	}

	/**
	 * Show a welcome notice until setup has run.
	 *
	 * @return void
	 */
	public function first_run_notice() {
		if ( get_option( 'dfcc_setup_done' ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'dog-father_page_dfcc-setup' === $screen->id ) {
			return; // Don't nag on the setup page itself.
		}
		printf(
			'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s <a href="%3$s" class="button button-primary" style="margin-left:8px;">%4$s</a></p></div>',
			esc_html__( 'The Dog Father Hotel:', 'dog-father-control-center' ),
			esc_html__( 'Finish setup to create your pages, menus and demo content automatically.', 'dog-father-control-center' ),
			esc_url( admin_url( 'admin.php?page=dfcc-setup' ) ),
			esc_html__( 'Run Setup', 'dog-father-control-center' )
		);
	}

	/**
	 * The full setup routine. Idempotent.
	 *
	 * @return void
	 */
	public function run() {
		$pages = $this->create_pages();
		$this->configure_reading( $pages );
		$this->create_menus( $pages );
		$this->seed_global();
		$this->seed_home_defaults();
		$this->seed_services();
		$this->seed_testimonials();
		$this->seed_faqs();
		$this->seed_gallery();

		update_option( 'dfcc_setup_done', 1 );
		update_option( 'dfcc_setup_time', current_time( 'mysql' ) );
		flush_rewrite_rules();
	}

	/**
	 * Create the core pages (if missing). Returns slug => id map.
	 *
	 * @return array
	 */
	private function create_pages() {
		$defs = array(
			'home'              => array( __( 'Home', 'dog-father-control-center' ), '' ),
			'about'             => array( __( 'About Us', 'dog-father-control-center' ), $this->about_content() ),
			'services'          => array( __( 'Services', 'dog-father-control-center' ), '[dfcc_services count="9"]' ),
			'dog-boarding'      => array( __( 'Dog Boarding', 'dog-father-control-center' ), $this->boarding_content() ),
			'long-term-boarding' => array( __( 'Long-Term Boarding', 'dog-father-control-center' ), __( 'Ideal for travel, relocation, business trips and extended vacations. Your dog enjoys the same structured daily routine, feeding, exercise and supervision for as long as needed. Contact us for extended-stay rates.', 'dog-father-control-center' ) ),
			'clinic'            => array( __( 'Clinic', 'dog-father-control-center' ), $this->clinic_content() ),
			'home-visit'        => array( __( 'Home Visit', 'dog-father-control-center' ), $this->home_visit_content() ),
			'pickup'            => array( __( 'Pickup', 'dog-father-control-center' ), $this->pickup_content() ),
			'shop'              => array( __( 'Shop', 'dog-father-control-center' ), __( 'Our pet shop is coming soon — premium food, accessories and care products for your dog. Stay tuned!', 'dog-father-control-center' ) ),
			'gallery'           => array( __( 'Gallery', 'dog-father-control-center' ), '[dfcc_gallery]' ),
			'testimonials'      => array( __( 'Testimonials', 'dog-father-control-center' ), '[dfcc_testimonials count="9"]' ),
			'book-now'          => array( __( 'Book Now', 'dog-father-control-center' ), '[dfcc_booking_form]' ),
			'booking'           => array( __( 'Booking', 'dog-father-control-center' ), '[dfcc_booking_form]' ),
			'contact'           => array( __( 'Contact', 'dog-father-control-center' ), "[dfcc_address]\n[dfcc_phone]\n[dfcc_whatsapp]\n[dfcc_email]\n[dfcc_map]" ),
			'faq'               => array( __( 'FAQ', 'dog-father-control-center' ), '' ),
			'blog'              => array( __( 'Blog', 'dog-father-control-center' ), '' ),
			'privacy-policy'    => array( __( 'Privacy Policy', 'dog-father-control-center' ), __( 'Add your privacy policy here.', 'dog-father-control-center' ) ),
			'terms'             => array( __( 'Terms & Conditions', 'dog-father-control-center' ), __( 'Add your terms and conditions here.', 'dog-father-control-center' ) ),
			'refund-policy'     => array( __( 'Refund Policy', 'dog-father-control-center' ), __( 'Add your refund policy here.', 'dog-father-control-center' ) ),
			'vaccination-policy' => array( __( 'Vaccination Policy', 'dog-father-control-center' ), $this->vaccination_content() ),
		);

		$map = array();
		foreach ( $defs as $slug => $def ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$map[ $slug ] = $existing->ID;
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_title'   => $def[0],
					'post_name'    => $slug,
					'post_content' => $def[1],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				$map[ $slug ] = $id;
			}
		}
		return $map;
	}

	/**
	 * Set the static front page + posts page.
	 *
	 * @param array $pages Slug => id.
	 * @return void
	 */
	private function configure_reading( $pages ) {
		if ( ! empty( $pages['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $pages['home'] );
		}
		if ( ! empty( $pages['blog'] ) ) {
			update_option( 'page_for_posts', (int) $pages['blog'] );
		}
	}

	/**
	 * Create primary + footer menus and assign them to theme locations.
	 *
	 * @param array $pages Slug => id.
	 * @return void
	 */
	private function create_menus( $pages ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();

		$primary_id = $this->build_menu(
			__( 'Primary Menu', 'dog-father-control-center' ),
			array( 'home', 'about', 'dog-boarding', 'clinic', 'home-visit', 'pickup', 'gallery', 'blog', 'contact' ),
			$pages
		);
		if ( $primary_id ) {
			$locations['primary'] = $primary_id;
		}

		$footer_id = $this->build_menu(
			__( 'Footer Menu', 'dog-father-control-center' ),
			array( 'about', 'dog-boarding', 'clinic', 'home-visit', 'gallery', 'faq', 'contact', 'privacy-policy', 'terms' ),
			$pages
		);
		if ( $footer_id ) {
			$locations['footer'] = $footer_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Build a single menu (idempotent) and return its id.
	 *
	 * @param string $name  Menu name.
	 * @param array  $slugs Ordered page slugs.
	 * @param array  $pages Slug => id map.
	 * @return int
	 */
	private function build_menu( $name, $slugs, $pages ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			$menu_id = (int) $menu->term_id;
			// Only populate if empty to avoid duplicates.
			$items = wp_get_nav_menu_items( $menu_id );
			if ( ! empty( $items ) ) {
				return $menu_id;
			}
		} else {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				return 0;
			}
		}

		foreach ( $slugs as $slug ) {
			if ( empty( $pages[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $pages[ $slug ] ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => (int) $pages[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
		return (int) $menu_id;
	}

	/**
	 * Seed home settings defaults (only fills empty values).
	 *
	 * @return void
	 */
	private function seed_home_defaults( $force = false ) {
		if ( ! class_exists( 'DFCC_Home_Settings' ) ) {
			return;
		}
		$module   = new DFCC_Home_Settings();
		$defaults = $module->defaults();
		$current  = get_option( 'dfcc_home_settings', array() );
		$current  = is_array( $current ) ? $current : array();

		foreach ( $defaults as $key => $val ) {
			if ( $force || ! isset( $current[ $key ] ) || '' === $current[ $key ] ) {
				$current[ $key ] = $val;
			}
		}
		// Ensure all sections visible by default.
		foreach ( array_keys( $module->toggles() ) as $tkey ) {
			if ( ! isset( $current[ $tkey ] ) ) {
				$current[ $tkey ] = 1;
			}
		}
		update_option( 'dfcc_home_settings', $current );
	}

	/**
	 * Seed demo services (only if none exist).
	 *
	 * @return void
	 */
	private function seed_services() {
		if ( $this->has_content( 'dfcc_service' ) ) {
			return;
		}
		foreach ( $this->service_data() as $order => $s ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'dfcc_service',
					'post_status'  => 'publish',
					'post_title'   => $s['title'],
					'post_excerpt' => $s['excerpt'],
					'post_content' => $s['excerpt'],
					'menu_order'   => $order,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_dfcc_price', $s['price'] );
				update_post_meta( $id, '_dfcc_price_suffix', $s['suffix'] );
				update_post_meta( $id, '_dfcc_icon', $s['icon'] );
				update_post_meta( $id, '_dfcc_features', implode( "\n", $s['features'] ) );
				update_post_meta( $id, '_dfcc_highlight', $s['featured'] ? 1 : '' );
				update_post_meta( $id, '_dfcc_visible', 1 );
			}
		}
	}

	/**
	 * The real Dog Father service catalogue (all prices in EGP).
	 *
	 * @return array
	 */
	private function service_data() {
		return array(
			array(
				'title'    => __( 'Dog Boarding (Luxury Room)', 'dog-father-control-center' ),
				'excerpt'  => __( 'Private individual room with meals included and round-the-clock care.', 'dog-father-control-center' ),
				'price'    => '500',
				'suffix'   => __( '/ night · meals included', 'dog-father-control-center' ),
				'icon'     => 'dashicons-building',
				'features' => array(
					__( 'Private individual room', 'dog-father-control-center' ),
					__( 'Meals included', 'dog-father-control-center' ),
					__( 'Daily exercise sessions', 'dog-father-control-center' ),
					__( 'Housekeeping every hour (6 AM – 11:30 PM)', 'dog-father-control-center' ),
					__( 'Daily WhatsApp videos & reports', 'dog-father-control-center' ),
				),
				'featured' => 1,
			),
			array(
				'title'    => __( 'Doggy Day Care', 'dog-father-control-center' ),
				'excerpt'  => __( 'Supervised day care for 4–6 hours. Pre-booking required.', 'dog-father-control-center' ),
				'price'    => '500',
				'suffix'   => __( '· 4–6 hours · pre-booking', 'dog-father-control-center' ),
				'icon'     => 'dashicons-pets',
				'features' => array(
					__( '4–6 hours supervised care', 'dog-father-control-center' ),
					__( 'Pre-booking required', 'dog-father-control-center' ),
					__( 'Group & solo play', 'dog-father-control-center' ),
					__( 'Daily WhatsApp updates', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
			array(
				'title'    => __( 'Training Academy', 'dog-father-control-center' ),
				'excerpt'  => __( 'Obedience, behaviour and confidence training tailored to your dog.', 'dog-father-control-center' ),
				'price'    => '5000',
				'suffix'   => __( 'starting from', 'dog-father-control-center' ),
				'icon'     => 'dashicons-awards',
				'features' => array(
					__( 'Obedience & behaviour', 'dog-father-control-center' ),
					__( 'Confidence building', 'dog-father-control-center' ),
					__( 'Tailored programmes', 'dog-father-control-center' ),
					__( 'Daily WhatsApp videos & reports', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
			array(
				'title'    => __( 'Spa & Grooming', 'dog-father-control-center' ),
				'excerpt'  => __( 'Premium baths, styling and pampering to keep your dog looking their best.', 'dog-father-control-center' ),
				'price'    => '400',
				'suffix'   => __( 'starting from', 'dog-father-control-center' ),
				'icon'     => 'dashicons-buddicons-activity',
				'features' => array(
					__( 'Luxury bath', 'dog-father-control-center' ),
					__( 'Styling & trim', 'dog-father-control-center' ),
					__( 'Nail & ear care', 'dog-father-control-center' ),
					__( 'Daily WhatsApp photos', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
			array(
				'title'    => __( 'Private Pool & Swimming', 'dog-father-control-center' ),
				'excerpt'  => __( 'Supervised swimming sessions in our private pool — great fun and exercise.', 'dog-father-control-center' ),
				'price'    => '',
				'suffix'   => '',
				'icon'     => 'dashicons-buddicons-community',
				'features' => array(
					__( 'Private pool', 'dog-father-control-center' ),
					__( 'Supervised swimming', 'dog-father-control-center' ),
					__( 'Confidence in the water', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
			array(
				'title'    => __( 'Pickup & Drop-off', 'dog-father-control-center' ),
				'excerpt'  => __( 'Door-to-door transport via our trusted partner companies across Cairo & Giza.', 'dog-father-control-center' ),
				'price'    => '',
				'suffix'   => __( 'on request', 'dog-father-control-center' ),
				'icon'     => 'dashicons-car',
				'features' => array(
					__( 'Door-to-door service', 'dog-father-control-center' ),
					__( 'Outsourced via multiple trusted companies', 'dog-father-control-center' ),
					__( 'Covers Cairo & Giza', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
			array(
				'title'    => __( 'Veterinary Support', 'dog-father-control-center' ),
				'excerpt'  => __( 'On-call veterinary consultation and emergency support with Dr. Ali.', 'dog-father-control-center' ),
				'price'    => '',
				'suffix'   => __( 'on request', 'dog-father-control-center' ),
				'icon'     => 'dashicons-heart',
				'features' => array(
					__( 'On-call vet (Dr. Ali)', 'dog-father-control-center' ),
					__( 'Vaccinations', 'dog-father-control-center' ),
					__( 'Medication handling', 'dog-father-control-center' ),
					__( 'Emergency support', 'dog-father-control-center' ),
				),
				'featured' => 0,
			),
		);
	}

	/**
	 * Seed demo testimonials.
	 *
	 * @return void
	 */
	private function seed_testimonials() {
		if ( $this->has_content( 'dfcc_testimonial' ) ) {
			return;
		}
		$items = array(
			array( __( 'Sarah M.', 'dog-father-control-center' ), __( 'Bella came home happy, healthy and beautifully groomed. The daily photos gave me total peace of mind while travelling.', 'dog-father-control-center' ) ),
			array( __( 'Ahmed K.', 'dog-father-control-center' ), __( 'The most professional and caring team we have ever trusted with Max. The suites are immaculate and the staff genuinely love the dogs.', 'dog-father-control-center' ) ),
			array( __( 'Lina R.', 'dog-father-control-center' ), __( 'Five stars is not enough. Our anxious rescue actually gets excited when we arrive. That says everything.', 'dog-father-control-center' ) ),
			array( __( 'James P.', 'dog-father-control-center' ), __( 'Outstanding facility and a genuinely loving team. Our golden retriever is always thrilled to stay.', 'dog-father-control-center' ) ),
			array( __( 'Maya H.', 'dog-father-control-center' ), __( 'Spotless suites, attentive carers and brilliant communication. Highly recommended.', 'dog-father-control-center' ) ),
			array( __( 'Omar T.', 'dog-father-control-center' ), __( 'The pickup service is a lifesaver and the daily updates are wonderful. Truly five-star.', 'dog-father-control-center' ) ),
		);
		foreach ( $items as $t ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'dfcc_testimonial',
					'post_status' => 'publish',
					'post_title'  => $t[0],
					'post_content' => $t[1],
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_dfcc_rating', 5 );
				update_post_meta( $id, '_dfcc_author_name', $t[0] );
				update_post_meta( $id, '_dfcc_author_role', __( 'Dog parent', 'dog-father-control-center' ) );
				update_post_meta( $id, '_dfcc_approved', 1 );
			}
		}
	}

	/**
	 * Seed demo FAQs.
	 *
	 * @return void
	 */
	private function seed_faqs() {
		if ( ! post_type_exists( 'dfcc_faq' ) || $this->has_content( 'dfcc_faq' ) ) {
			return;
		}
		$faqs = array(
			array( __( 'What should I bring when boarding my dog?', 'dog-father-control-center' ), __( 'Bring enough of your dog’s regular food for the stay (with feeding instructions), any clearly labelled medications and supplements, a comfort item such as a favourite toy or blanket, up-to-date vaccination records, and your emergency contact details.', 'dog-father-control-center' ) ),
			array( __( 'How do you ensure my dog’s safety and comfort?', 'dog-father-control-center' ), __( 'Clean, secure facilities with regular disinfection, experienced staff trained for all breeds and temperaments, personalised feeding and play routines, veterinary support via our partner Dr. Ali, and 24/7 monitoring with a clear emergency protocol.', 'dog-father-control-center' ) ),
			array( __( 'What happens if my dog gets sick during their stay?', 'dog-father-control-center' ), __( 'Our trained staff act immediately to stabilise your dog, consult our partner veterinarian Dr. Ali, and contact you right away with updates and recommendations. Any required medication is administered accurately by our team.', 'dog-father-control-center' ) ),
			array( __( 'Can my dog receive medication?', 'dog-father-control-center' ), __( 'Yes. We carefully follow owner instructions and maintain scheduled medication routines.', 'dog-father-control-center' ) ),
			array( __( 'How many times are dogs fed?', 'dog-father-control-center' ), __( 'According to your instructions and our structured feeding schedule — typically a morning and an evening meal, with adjustments for age, breed and health needs.', 'dog-father-control-center' ) ),
			array( __( 'Can I visit before booking?', 'dog-father-control-center' ), __( 'Yes. Visits can be arranged by appointment — just message us on WhatsApp.', 'dog-father-control-center' ) ),
			array( __( 'Do you accept puppies?', 'dog-father-control-center' ), __( 'Yes, subject to vaccination status and health requirements.', 'dog-father-control-center' ) ),
			array( __( 'What vaccinations are required?', 'dog-father-control-center' ), __( 'Rabies, DHPP (distemper/parvovirus and more), plus anti-flea and deworming treatment. Please bring records on arrival.', 'dog-father-control-center' ) ),
		);
		$order = 0;
		foreach ( $faqs as $f ) {
			wp_insert_post(
				array(
					'post_type'    => 'dfcc_faq',
					'post_status'  => 'publish',
					'post_title'   => $f[0],
					'post_content' => $f[1],
					'menu_order'   => $order++,
				)
			);
		}
	}

	/**
	 * Seed demo gallery placeholders (titles only — theme shows elegant
	 * gradient placeholders until real photos are added).
	 *
	 * @return void
	 */
	private function seed_gallery() {
		if ( $this->has_content( 'dfcc_gallery' ) ) {
			return;
		}
		$titles = array(
			__( 'Luxury Suite', 'dog-father-control-center' ),
			__( 'Play Garden', 'dog-father-control-center' ),
			__( 'Spa Day', 'dog-father-control-center' ),
			__( 'Morning Walk', 'dog-father-control-center' ),
			__( 'Nap Time', 'dog-father-control-center' ),
			__( 'Training Session', 'dog-father-control-center' ),
			__( 'Gourmet Meals', 'dog-father-control-center' ),
			__( 'Happy Guests', 'dog-father-control-center' ),
		);
		foreach ( $titles as $title ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'dfcc_gallery',
					'post_status' => 'publish',
					'post_title'  => $title,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_dfcc_media_type', 'image' );
			}
		}
	}

	/**
	 * Whether a post type already has any published/draft content.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private function has_content( $post_type ) {
		$existing = get_posts(
			array(
				'post_type'        => $post_type,
				'post_status'      => array( 'publish', 'draft', 'pending' ),
				'numberposts'      => 1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);
		return ! empty( $existing );
	}

	/* ---------------------------------------------------------------------
	 * Page content builders
	 * ------------------------------------------------------------------- */

	/**
	 * About page content.
	 *
	 * @return string
	 */
	private function about_content() {
		return "<h2>Who We Are</h2>\n<p>The Dog Father Hotel was created by dog lovers who understood the need for professional boarding services in Egypt. Our facility was built around one principle: treat every dog like family.</p>\n<p>We understand that leaving your dog behind can be stressful. That's why we provide a structured environment where dogs receive proper care, exercise, feeding, and attention throughout their stay — whether for a weekend or an extended boarding period.</p>\n<h3>Why Choose Us</h3>\n<ul>\n<li><strong>Expert Care:</strong> our trained staff are true dog lovers who treat every guest as their own.</li>\n<li><strong>Safety First:</strong> a secure environment with attentive 24/7 supervision.</li>\n<li><strong>Tailored Services:</strong> boarding, grooming and personalized attention for each dog's needs.</li>\n</ul>";
	}

	/**
	 * Dog Boarding page content.
	 *
	 * @return string
	 */
	private function boarding_content() {
		return "<p>Professional overnight accommodations for dogs of all sizes — 500 EGP per night, including meals.</p>\n<h3>Includes</h3>\n<ul>\n<li>Individual room accommodation</li>\n<li>Daily feeding (meals included)</li>\n<li>Daily exercise sessions</li>\n<li>Housekeeping every hour, 6:00 AM – 11:30 PM</li>\n<li>Health monitoring</li>\n<li>Medication administration</li>\n<li>Supervised care &amp; daily WhatsApp updates</li>\n</ul>\n[dfcc_booking_form]";
	}

	/**
	 * Clinic page content.
	 *
	 * @return string
	 */
	private function clinic_content() {
		return "<p>Our partnered veterinary clinic provides consultations, vaccinations, treatments and emergency support, led by Dr. Ali and the team.</p>\n<p>Veterinary services are handled by our licensed veterinary partners to ensure professional care and legal responsibility.</p>\n<p>To book a clinic appointment, message us on WhatsApp: <a href=\"https://wa.me/201094622999\">+20 109 462 2999</a>.</p>";
	}

	/**
	 * Vet Home Visit page content.
	 *
	 * @return string
	 */
	private function home_visit_content() {
		return "<h2>🏡 Vet Home Visits — Expert Veterinary Care, Right at Your Doorstep</h2>\n<p>No time to visit the clinic? We bring the vet to your home! Whether it's a routine checkup or collecting lab samples, our home-visit service makes it easy to care for your dog in a stress-free environment.</p>\n<h3>🩺 Services Available</h3>\n<ul>\n<li>Routine checkups &amp; consultations</li>\n<li>Vaccinations</li>\n<li>Blood sample collection</li>\n<li>Minor treatments</li>\n<li>Follow-up care after boarding</li>\n<li>Elderly dog care at home</li>\n</ul>\n<p><em>Advanced cases or critical emergencies may still require in-clinic treatment.</em></p>\n<h3>🤝 Powered by Trusted Professionals</h3>\n<p>The Dog Father Hotel manages booking and communication, while medical services are handled directly by our partnered, licensed veterinary team to ensure professional care and legal responsibility.</p>\n<h3>💳 Booking &amp; Fees</h3>\n<ul>\n<li>Home visits available daily (subject to vet availability)</li>\n<li>Charges depend on location and type of service</li>\n<li>Payment via Paymob, cash, or bank transfer</li>\n<li>Advance booking required (at least 24 hours' notice)</li>\n</ul>\n<h3>📍 Areas Covered</h3>\n<p>We currently serve most areas in Cairo and Giza. Please contact us to confirm coverage in your neighborhood.</p>\n<h3>📲 Ready to Book?</h3>\n<p><a href=\"https://wa.me/201094622999\">Message us on WhatsApp</a> or call +20 109 462 2999.</p>\n<h3>⚠️ Important Reminders</h3>\n<ul>\n<li>Please ensure your dog is secured (on leash or in a room) when the vet arrives.</li>\n<li>If your dog is aggressive or scared of strangers, notify us in advance.</li>\n<li>This service is for non-emergency cases only. For urgent situations, please go to the nearest animal hospital.</li>\n</ul>";
	}

	/**
	 * Pickup page content.
	 *
	 * @return string
	 */
	private function pickup_content() {
		return "<h2>Pickup &amp; Drop-off</h2>\n<p>Door-to-door pet transportation so your dog travels in comfort. This service is provided through our trusted outsourced partners (more than one company) covering most areas of Cairo and Giza.</p>\n<p>Charges depend on location and distance. Contact us to arrange a pickup: <a href=\"https://wa.me/201094622999\">+20 109 462 2999</a>.</p>";
	}

	/**
	 * Vaccination policy content.
	 *
	 * @return string
	 */
	private function vaccination_content() {
		return "<h2>Vaccination Policy</h2>\n<p>For the safety of every guest, all dogs must be up to date on the following before boarding:</p>\n<ul>\n<li><strong>Rabies</strong></li>\n<li><strong>DHPP</strong> (distemper, hepatitis, parvovirus, parainfluenza)</li>\n<li><strong>Anti-flea &amp; deworming treatment</strong></li>\n</ul>\n<p>Please bring up-to-date vaccination records on arrival. Puppies are accepted subject to vaccination status and health requirements.</p>";
	}

	/**
	 * Render the setup screen.
	 *
	 * @return void
	 */
	public function render_page() {
		$this->view(
			'setup',
			array(
				'done' => (bool) get_option( 'dfcc_setup_done' ),
				'time' => get_option( 'dfcc_setup_time', '' ),
			)
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Setup() );
	}
);
