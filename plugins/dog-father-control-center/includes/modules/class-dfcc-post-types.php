<?php
/**
 * Post types & taxonomies module.
 *
 * Registers every custom content type the Control Center manages and attaches
 * them to the "Dog Father" admin menu. Field-level behaviour (meta boxes,
 * columns, workflows) lives in the dedicated feature modules.
 *
 * Public contract — other modules rely on these slugs:
 *   CPTs:  dfcc_booking, dfcc_dog, dfcc_service, dfcc_gallery, dfcc_testimonial
 *   Tax:   dfcc_service_cat, dfcc_gallery_cat
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Post_Types.
 */
class DFCC_Post_Types extends DFCC_Module {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'post-types';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Content Types', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$menu = dfcc_menu_slug();

		register_post_type(
			'dfcc_booking',
			$this->args(
				__( 'Bookings', 'dog-father-control-center' ),
				__( 'Booking', 'dog-father-control-center' ),
				array(
					'menu_icon'    => 'dashicons-calendar-alt',
					'show_in_menu' => $menu,
					'public'       => false,
					'show_ui'      => true,
					'supports'     => array( 'title' ),
					'has_archive'  => false,
				)
			)
		);

		register_post_type(
			'dfcc_dog',
			$this->args(
				__( 'Dog Profiles', 'dog-father-control-center' ),
				__( 'Dog Profile', 'dog-father-control-center' ),
				array(
					'menu_icon'    => 'dashicons-pets',
					'show_in_menu' => $menu,
					'public'       => false,
					'show_ui'      => true,
					'supports'     => array( 'title', 'thumbnail' ),
					'has_archive'  => false,
				)
			)
		);

		register_post_type(
			'dfcc_service',
			$this->args(
				__( 'Services', 'dog-father-control-center' ),
				__( 'Service', 'dog-father-control-center' ),
				array(
					'menu_icon'    => 'dashicons-heart',
					'show_in_menu' => $menu,
					'public'       => true,
					'has_archive'  => true,
					'rewrite'      => array( 'slug' => 'dog-services' ),
					'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
					// Classic editor for reliability (the block editor can white-screen
					// on some hosts when combined with custom meta boxes).
					'show_in_rest' => false,
				)
			)
		);

		register_post_type(
			'dfcc_gallery',
			$this->args(
				__( 'Gallery', 'dog-father-control-center' ),
				__( 'Gallery Item', 'dog-father-control-center' ),
				array(
					'menu_icon'    => 'dashicons-format-gallery',
					'show_in_menu' => $menu,
					// Gallery items are content blocks shown via the grid/shortcode,
					// not standalone pages — so no public single/archive (which used
					// to render a blog-style "Archives: Gallery" list) and no block
					// editor confusion. Manage them from Dog Father → Manage Gallery.
					'public'       => false,
					'show_ui'      => true,
					'has_archive'  => false,
					'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
					'show_in_rest' => false,
				)
			)
		);

		register_post_type(
			'dfcc_testimonial',
			$this->args(
				__( 'Testimonials', 'dog-father-control-center' ),
				__( 'Testimonial', 'dog-father-control-center' ),
				array(
					'menu_icon'    => 'dashicons-star-filled',
					'show_in_menu' => $menu,
					'public'       => false,
					'show_ui'      => true,
					'supports'     => array( 'title', 'editor', 'thumbnail' ),
					'show_in_rest' => true,
				)
			)
		);
	}

	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		register_taxonomy(
			'dfcc_service_cat',
			'dfcc_service',
			array(
				'label'             => __( 'Service Categories', 'dog-father-control-center' ),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'service-category' ),
			)
		);

		register_taxonomy(
			'dfcc_gallery_cat',
			'dfcc_gallery',
			array(
				'label'             => __( 'Albums', 'dog-father-control-center' ),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'album' ),
			)
		);
	}

	/**
	 * Build a standard labels + args array for a post type.
	 *
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @param array  $overrides Args to merge over the defaults.
	 * @return array
	 */
	private function args( $plural, $singular, array $overrides ) {
		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			/* translators: %s: singular post type name. */
			'add_new_item'          => sprintf( __( 'Add New %s', 'dog-father-control-center' ), $singular ),
			/* translators: %s: singular post type name. */
			'edit_item'             => sprintf( __( 'Edit %s', 'dog-father-control-center' ), $singular ),
			/* translators: %s: singular post type name. */
			'new_item'              => sprintf( __( 'New %s', 'dog-father-control-center' ), $singular ),
			/* translators: %s: singular post type name. */
			'view_item'             => sprintf( __( 'View %s', 'dog-father-control-center' ), $singular ),
			/* translators: %s: plural post type name. */
			'search_items'          => sprintf( __( 'Search %s', 'dog-father-control-center' ), $plural ),
			/* translators: %s: plural post type name (lowercase). */
			'not_found'             => sprintf( __( 'No %s found', 'dog-father-control-center' ), strtolower( $plural ) ),
			'all_items'             => $plural,
			'menu_name'             => $plural,
		);

		$defaults = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'exclude_from_search' => true,
		);

		return array_merge( $defaults, $overrides );
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Post_Types() );
	}
);
