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
			'home'           => array( __( 'Home', 'dog-father-control-center' ), '' ),
			'about'          => array( __( 'About Us', 'dog-father-control-center' ), __( 'The Dog Father Hotel blends luxury hospitality with expert canine care. Edit this page with Elementor or the block editor.', 'dog-father-control-center' ) ),
			'services'       => array( __( 'Services', 'dog-father-control-center' ), '[dfcc_services]' ),
			'gallery'        => array( __( 'Gallery', 'dog-father-control-center' ), '[dfcc_gallery]' ),
			'testimonials'   => array( __( 'Testimonials', 'dog-father-control-center' ), '[dfcc_testimonials]' ),
			'book-now'       => array( __( 'Book Now', 'dog-father-control-center' ), '[dfcc_booking_form]' ),
			'contact'        => array( __( 'Contact', 'dog-father-control-center' ), "[dfcc_address]\n[dfcc_phone]\n[dfcc_email]\n[dfcc_map]" ),
			'faq'            => array( __( 'FAQ', 'dog-father-control-center' ), '' ),
			'blog'           => array( __( 'Blog', 'dog-father-control-center' ), '' ),
			'privacy-policy' => array( __( 'Privacy Policy', 'dog-father-control-center' ), __( 'Add your privacy policy here.', 'dog-father-control-center' ) ),
			'terms'          => array( __( 'Terms & Conditions', 'dog-father-control-center' ), __( 'Add your terms and conditions here.', 'dog-father-control-center' ) ),
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
			array( 'home', 'about', 'services', 'gallery', 'testimonials', 'contact', 'book-now' ),
			$pages
		);
		if ( $primary_id ) {
			$locations['primary'] = $primary_id;
		}

		$footer_id = $this->build_menu(
			__( 'Footer Menu', 'dog-father-control-center' ),
			array( 'about', 'services', 'gallery', 'faq', 'contact', 'privacy-policy' ),
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
	private function seed_home_defaults() {
		if ( ! class_exists( 'DFCC_Home_Settings' ) ) {
			return;
		}
		$module   = new DFCC_Home_Settings();
		$defaults = $module->defaults();
		$current  = get_option( 'dfcc_home_settings', array() );
		$current  = is_array( $current ) ? $current : array();

		foreach ( $defaults as $key => $val ) {
			if ( ! isset( $current[ $key ] ) || '' === $current[ $key ] ) {
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
		$services = array(
			array( __( 'Luxury Boarding Suite', 'dog-father-control-center' ), __( 'Private climate-controlled suites with plush bedding and daily housekeeping.', 'dog-father-control-center' ), '250', __( '/ night', 'dog-father-control-center' ), 'dashicons-building', array( __( 'Private suite', 'dog-father-control-center' ), __( 'Plush orthopedic bedding', 'dog-father-control-center' ), __( 'Daily housekeeping', 'dog-father-control-center' ) ), 1 ),
			array( __( 'Doggy Day Care', 'dog-father-control-center' ), __( 'Supervised play, socialisation and enrichment in a safe environment.', 'dog-father-control-center' ), '120', __( '/ day', 'dog-father-control-center' ), 'dashicons-pets', array( __( 'Group & solo play', 'dog-father-control-center' ), __( 'Trained supervisors', 'dog-father-control-center' ), __( 'Enrichment activities', 'dog-father-control-center' ) ), 0 ),
			array( __( 'Veterinary Care', 'dog-father-control-center' ), __( 'On-site, on-call veterinary supervision and medication management.', 'dog-father-control-center' ), '', '', 'dashicons-heart', array( __( '24/7 on-call vet', 'dog-father-control-center' ), __( 'Medication handling', 'dog-father-control-center' ), __( 'Health monitoring', 'dog-father-control-center' ) ), 0 ),
			array( __( 'Training Academy', 'dog-father-control-center' ), __( 'Certified trainers offering obedience, behaviour and confidence programmes.', 'dog-father-control-center' ), '300', __( '/ course', 'dog-father-control-center' ), 'dashicons-awards', array( __( 'Certified trainers', 'dog-father-control-center' ), __( 'Custom programmes', 'dog-father-control-center' ), __( 'Progress reports', 'dog-father-control-center' ) ), 0 ),
			array( __( 'Spa & Grooming', 'dog-father-control-center' ), __( 'Premium baths, styling and pampering to keep your dog looking five-star.', 'dog-father-control-center' ), '150', __( '/ session', 'dog-father-control-center' ), 'dashicons-buddicons-activity', array( __( 'Luxury bath', 'dog-father-control-center' ), __( 'Styling & trim', 'dog-father-control-center' ), __( 'Nail & ear care', 'dog-father-control-center' ) ), 0 ),
			array( __( 'Pickup & Drop-off', 'dog-father-control-center' ), __( 'Door-to-door luxury transport so your dog travels in total comfort.', 'dog-father-control-center' ), '80', __( '/ trip', 'dog-father-control-center' ), 'dashicons-car', array( __( 'Climate-controlled', 'dog-father-control-center' ), __( 'GPS tracked', 'dog-father-control-center' ), __( 'Safe & insured', 'dog-father-control-center' ) ), 0 ),
		);

		$order = 0;
		foreach ( $services as $s ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'dfcc_service',
					'post_status'  => 'publish',
					'post_title'   => $s[0],
					'post_excerpt' => $s[1],
					'post_content' => $s[1],
					'menu_order'   => $order++,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_dfcc_price', $s[2] );
				update_post_meta( $id, '_dfcc_price_suffix', $s[3] );
				update_post_meta( $id, '_dfcc_icon', $s[4] );
				update_post_meta( $id, '_dfcc_features', implode( "\n", $s[5] ) );
				if ( $s[6] ) {
					update_post_meta( $id, '_dfcc_highlight', 1 );
				}
			}
		}
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
			array( __( 'What vaccinations does my dog need?', 'dog-father-control-center' ), __( 'All guests must be up to date on core vaccinations. Our team confirms the exact requirements when you book.', 'dog-father-control-center' ) ),
			array( __( 'What should I bring for my dog’s stay?', 'dog-father-control-center' ), __( 'Just their food (if on a special diet), any medication, and a familiar comfort item. We provide everything else.', 'dog-father-control-center' ) ),
			array( __( 'Will I receive updates while my dog stays?', 'dog-father-control-center' ), __( 'Yes — we send daily photos and updates so you always know your best friend is happy and safe.', 'dog-father-control-center' ) ),
			array( __( 'Can you manage medication and special diets?', 'dog-father-control-center' ), __( 'Absolutely. Our trained staff and on-call vet handle medication, special diets and any medical needs.', 'dog-father-control-center' ) ),
			array( __( 'How do I book and what is your cancellation policy?', 'dog-father-control-center' ), __( 'Book online in minutes. Flexible cancellation details are confirmed at the time of booking.', 'dog-father-control-center' ) ),
			array( __( 'How is feeding handled?', 'dog-father-control-center' ), __( 'We follow your dog’s normal feeding schedule and can accommodate any dietary requirements.', 'dog-father-control-center' ) ),
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
