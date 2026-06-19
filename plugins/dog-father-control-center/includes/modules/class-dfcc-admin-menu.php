<?php
/**
 * Admin menu module.
 *
 * Builds the top-level "Dog Father" admin menu and the Dashboard landing page.
 * Custom post types attach themselves to this menu via show_in_menu. Settings
 * pages register through the 'dfcc_admin_pages' filter so any module can add a
 * screen without editing this file.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Admin_Menu.
 */
class DFCC_Admin_Menu extends DFCC_Module {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'admin-menu';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Admin Menu', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_init', array( $this, 'maybe_flush_rewrite' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer_credit' ) );

		// Clear caches whenever the site's content/brand settings are saved.
		foreach ( array( 'dfcc_home_settings', 'dfcc_global_settings', 'dfcc_theme_settings', 'dfcc_seo_settings' ) as $dfcc_opt ) {
			add_action( 'update_option_' . $dfcc_opt, 'dfcc_purge_caches', 10, 0 );
			add_action( 'add_option_' . $dfcc_opt, 'dfcc_purge_caches', 10, 0 );
		}
	}

	/**
	 * Build the menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		$cap  = dfcc_admin_cap();
		$slug = dfcc_menu_slug();

		add_menu_page(
			__( 'Dog Father Control Center', 'dog-father-control-center' ),
			__( 'Dog Father', 'dog-father-control-center' ),
			$cap,
			$slug,
			array( $this, 'render_dashboard' ),
			'dashicons-pets',
			3
		);

		// Rename the duplicated first submenu item to "Dashboard".
		add_submenu_page(
			$slug,
			__( 'Dashboard', 'dog-father-control-center' ),
			__( 'Dashboard', 'dog-father-control-center' ),
			$cap,
			$slug,
			array( $this, 'render_dashboard' )
		);

		/**
		 * Allow modules to contribute settings / report screens.
		 *
		 * Each entry: array(
		 *   'slug'     => 'dfcc-seo',
		 *   'title'    => 'SEO Center',
		 *   'callback' => callable,
		 *   'order'    => 50, // optional sort weight
		 * )
		 *
		 * @param array $pages Registered admin pages.
		 */
		$pages = apply_filters( 'dfcc_admin_pages', array() );

		usort(
			$pages,
			static function ( $a, $b ) {
				$oa = isset( $a['order'] ) ? (int) $a['order'] : 100;
				$ob = isset( $b['order'] ) ? (int) $b['order'] : 100;
				return $oa <=> $ob;
			}
		);

		foreach ( $pages as $page ) {
			if ( empty( $page['slug'] ) || empty( $page['title'] ) || empty( $page['callback'] ) ) {
				continue;
			}
			add_submenu_page(
				$slug,
				$page['title'],
				$page['title'],
				isset( $page['capability'] ) ? $page['capability'] : $cap,
				$page['slug'],
				$page['callback']
			);
		}
	}

	/**
	 * Render the dashboard landing page.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->view(
			'dashboard',
			array(
				'modules' => dfcc()->modules(),
			)
		);
	}

	/**
	 * Flush rewrite rules once after activation.
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite() {
		if ( get_option( 'dfcc_flush_rewrite' ) ) {
			flush_rewrite_rules();
			delete_option( 'dfcc_flush_rewrite' );
		}
	}

	/**
	 * Enqueue admin styles/scripts on our screens.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function assets( $hook ) {
		// Load globally on our screens (anything with dfcc- in the page query)
		// or on CPT edit screens belonging to us.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_dfcc = ( 0 === strpos( $page, 'dfcc' ) );

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 0 === strpos( (string) $screen->post_type, 'dfcc_' ) ) {
			$is_dfcc = true;
		}

		if ( ! $is_dfcc ) {
			return;
		}

		wp_enqueue_style(
			'dfcc-admin',
			DFCC_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			DFCC_VERSION
		);
		wp_enqueue_script(
			'dfcc-admin',
			DFCC_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			DFCC_VERSION,
			true
		);
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();

		wp_localize_script(
			'dfcc-admin',
			'DFCC',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'restUrl' => esc_url_raw( rest_url( 'dfcc/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Add a body class on our screens for scoping CSS.
	 *
	 * @param string $classes Existing body classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 === strpos( $page, 'dfcc' ) ) {
			$classes .= ' dfcc-admin-screen';
		}
		return $classes;
	}

	/**
	 * Provada credit in the admin footer (Dog Father screens + our CPTs).
	 *
	 * @param string $text Existing footer text.
	 * @return string
	 */
	public function footer_credit( $text ) {
		$page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_dfcc = ( 0 === strpos( $page, 'dfcc' ) );

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 0 === strpos( (string) $screen->post_type, 'dfcc_' ) ) {
			$is_dfcc = true;
		}

		if ( ! $is_dfcc ) {
			return $text;
		}

		return sprintf(
			/* translators: %s: Provada link. */
			esc_html__( 'The Dog Father Control Center — built by %s', 'dog-father-control-center' ),
			'<a href="https://provada.net" target="_blank" rel="noopener"><strong>Provada</strong></a>'
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Admin_Menu() );
	}
);
