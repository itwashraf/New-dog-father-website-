<?php
/**
 * Onboarding & Guidance module.
 *
 * Turns the Control Center into a self-explanatory product a non-technical owner
 * (or a buyer of the theme) can run without a developer:
 *
 *   - Getting Started : a guided checklist + first-run wizard.
 *   - Help & Docs     : an in-panel manual ("where do I edit X?").
 *   - About / Branding : the "Built by Provada.net" page (theme author credit).
 *   - License         : a license-key screen (groundwork for selling/updates).
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Onboarding.
 */
class DFCC_Onboarding extends DFCC_Module {

	/**
	 * License option name.
	 */
	const OPT_LICENSE = 'dfcc_license';

	/**
	 * Provada brand constants (admin/theme-author credit — not the public site).
	 */
	const BRAND_NAME = 'Provada';
	const BRAND_URL  = 'https://provada.net';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'onboarding';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Onboarding & Help', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_pages' ) );
		add_action( 'admin_init', array( $this, 'handle_license_save' ) );
	}

	/**
	 * Register the guidance screens.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_pages( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-getting-started',
			'title'    => __( 'Getting Started', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_getting_started' ),
			'order'    => 1,
		);
		$pages[] = array(
			'slug'     => 'dfcc-help',
			'title'    => __( 'How-to / Help & Guide', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_help' ),
			'order'    => 910,
		);
		$pages[] = array(
			'slug'     => 'dfcc-license',
			'title'    => __( 'License', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_license' ),
			'order'    => 920,
		);
		$pages[] = array(
			'slug'     => 'dfcc-about',
			'title'    => __( 'About / Provada', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_about' ),
			'order'    => 930,
		);
		return $pages;
	}

	/* ---------------------------------------------------------------------
	 * Getting Started — checklist of setup tasks
	 * ------------------------------------------------------------------ */

	/**
	 * Build the setup checklist with live completion state.
	 *
	 * @return array[] Each: array( done, title, text, button, url ).
	 */
	public function checklist() {
		$has_logo     = (bool) get_theme_mod( 'custom_logo' );
		$phone        = dfcc_get_setting( 'dfcc_global_settings', 'phone', '' );
		$email        = dfcc_get_setting( 'dfcc_global_settings', 'email', '' );
		$has_contact  = ( '' !== $phone || '' !== $email );
		$brand_set    = '' !== get_option( 'dfcc_theme_settings', '' ) && is_array( get_option( 'dfcc_theme_settings' ) ) && ! empty( get_option( 'dfcc_theme_settings' ) );
		$has_services = $this->count_posts( 'dfcc_service' ) > 0;
		$has_faqs     = $this->count_posts( 'dfcc_faq' ) > 0;
		$setup_done   = (bool) get_option( 'dfcc_setup_done' );

		return array(
			array(
				'done'   => $setup_done,
				'title'  => __( 'Run one-click setup', 'dog-father-control-center' ),
				'text'   => __( 'Create all your pages, menus and starter content automatically.', 'dog-father-control-center' ),
				'button' => __( 'Open Setup', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-setup' ),
			),
			array(
				'done'   => $has_logo,
				'title'  => __( 'Add your logo', 'dog-father-control-center' ),
				'text'   => __( 'Upload your logo so it appears in the header.', 'dog-father-control-center' ),
				'button' => __( 'Set Logo', 'dog-father-control-center' ),
				'url'    => admin_url( 'customize.php?autofocus[control]=custom_logo' ),
			),
			array(
				'done'   => $brand_set,
				'title'  => __( 'Choose your colors & fonts', 'dog-father-control-center' ),
				'text'   => __( 'Set your brand palette and decide which parts of the site each color affects.', 'dog-father-control-center' ),
				'button' => __( 'Theme Settings', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-theme' ),
			),
			array(
				'done'   => $has_contact,
				'title'  => __( 'Enter your business details', 'dog-father-control-center' ),
				'text'   => __( 'Phone, WhatsApp, email, address, hours and social links.', 'dog-father-control-center' ),
				'button' => __( 'Global Settings', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-global' ),
			),
			array(
				'done'   => true,
				'title'  => __( 'Edit your homepage', 'dog-father-control-center' ),
				'text'   => __( 'Change every section, reorder them, and style each one.', 'dog-father-control-center' ),
				'button' => __( 'Edit Homepage', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-home' ),
			),
			array(
				'done'   => $has_services,
				'title'  => __( 'Add your services', 'dog-father-control-center' ),
				'text'   => __( 'Prices, features and icons — shown on the homepage and services page.', 'dog-father-control-center' ),
				'button' => __( 'Manage Services', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-services' ),
			),
			array(
				'done'   => $has_faqs,
				'title'  => __( 'Add your FAQs', 'dog-father-control-center' ),
				'text'   => __( 'Answer the questions your customers ask most.', 'dog-father-control-center' ),
				'button' => __( 'Manage FAQs', 'dog-father-control-center' ),
				'url'    => admin_url( 'admin.php?page=dfcc-faqs' ),
			),
		);
	}

	/**
	 * Count published/draft posts of a type.
	 *
	 * @param string $type Post type.
	 * @return int
	 */
	private function count_posts( $type ) {
		$ids = get_posts(
			array(
				'post_type'        => $type,
				'post_status'      => array( 'publish', 'draft' ),
				'numberposts'      => 1,
				'fields'           => 'ids',
				'suppress_filters' => true,
			)
		);
		return count( $ids );
	}

	/**
	 * Render the Getting Started screen.
	 *
	 * @return void
	 */
	public function render_getting_started() {
		$items = $this->checklist();
		$done  = count( array_filter( wp_list_pluck( $items, 'done' ) ) );
		$total = count( $items );
		$this->view(
			'getting-started',
			array(
				'items'   => $items,
				'done'    => $done,
				'total'   => $total,
				'percent' => $total ? (int) round( $done / $total * 100 ) : 0,
				'brand'   => array( 'name' => self::BRAND_NAME, 'url' => self::BRAND_URL ),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Help & Docs
	 * ------------------------------------------------------------------ */

	/**
	 * Render the in-panel Help & Docs screen.
	 *
	 * @return void
	 */
	public function render_help() {
		$this->view( 'help', array( 'brand' => array( 'name' => self::BRAND_NAME, 'url' => self::BRAND_URL ) ) );
	}

	/* ---------------------------------------------------------------------
	 * License
	 * ------------------------------------------------------------------ */

	/**
	 * Render the License screen.
	 *
	 * @return void
	 */
	public function render_license() {
		$this->view(
			'license',
			array(
				'license' => get_option( self::OPT_LICENSE, array() ),
				'brand'   => array( 'name' => self::BRAND_NAME, 'url' => self::BRAND_URL ),
			)
		);
	}

	/**
	 * Save the license key. (Stores locally; remote validation can be wired in
	 * later by filtering 'dfcc_license_validate'.)
	 *
	 * @return void
	 */
	public function handle_license_save() {
		if ( ! isset( $_POST['dfcc_license_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfcc_license_nonce'] ) ), 'dfcc_save_license' ) ) {
			return;
		}

		$key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

		/**
		 * Filter the validation result for a license key. Return 'active',
		 * 'invalid' or '' (unknown). Defaults to 'active' when a key is present.
		 *
		 * @param string $status Validation status.
		 * @param string $key    Submitted key.
		 */
		$status = apply_filters( 'dfcc_license_validate', ( '' !== $key ? 'active' : '' ), $key );

		update_option(
			self::OPT_LICENSE,
			array(
				'key'       => $key,
				'status'    => $status,
				'activated' => current_time( 'mysql' ),
			)
		);

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-license', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * About / Provada
	 * ------------------------------------------------------------------ */

	/**
	 * Render the About / Provada screen.
	 *
	 * @return void
	 */
	public function render_about() {
		$this->view(
			'about-provada',
			array(
				'brand'   => array( 'name' => self::BRAND_NAME, 'url' => self::BRAND_URL ),
				'version' => defined( 'DFCC_VERSION' ) ? DFCC_VERSION : '',
			)
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Onboarding() );
	}
);
