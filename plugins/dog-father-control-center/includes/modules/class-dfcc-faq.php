<?php
/**
 * FAQ module.
 *
 * Registers the dfcc_faq custom post type, giving the owner a simple FAQ
 * manager inside the "Dog Father" admin menu. The post title is the question
 * and the editor content is the answer — no extra meta boxes are needed.
 *
 * Public contract — the theme relies on this slug:
 *   CPT: dfcc_faq
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_FAQ.
 */
class DFCC_FAQ extends DFCC_Module {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'faq';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'FAQs', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Friendly all-in-one manager screen in the Dog Father menu.
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_post_dfcc_save_faqs', array( $this, 'handle_manager_save' ) );
	}

	/**
	 * Register the "Manage FAQs" control-center page.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-faqs',
			'title'    => __( 'Manage FAQs', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_manager' ),
			'order'    => 40,
		);
		return $pages;
	}

	/**
	 * Fetch all FAQs in display order.
	 *
	 * @return WP_Post[]
	 */
	private function all_faqs() {
		return get_posts(
			array(
				'post_type'        => 'dfcc_faq',
				'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => true,
			)
		);
	}

	/**
	 * Render the FAQ manager screen.
	 *
	 * @return void
	 */
	public function render_manager() {
		$this->view(
			'faq-manager',
			array(
				'faqs' => $this->all_faqs(),
			)
		);
	}

	/**
	 * Handle the FAQ manager save (create / update / delete in one submit).
	 *
	 * @return void
	 */
	public function handle_manager_save() {
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'dog-father-control-center' ) );
		}
		check_admin_referer( 'dfcc_save_faqs' );

		$rows = isset( $_POST['faq'] ) && is_array( $_POST['faq'] ) ? wp_unslash( $_POST['faq'] ) : array(); // phpcs:ignore WordPress.Security.ValidationSanitization.MissingUnslash, WordPress.Security.ValidationSanitization.InputNotSanitized

		$order = 0;
		foreach ( $rows as $key => $row ) {
			$question = isset( $row['question'] ) ? sanitize_text_field( $row['question'] ) : '';
			$answer   = isset( $row['answer'] ) ? sanitize_textarea_field( $row['answer'] ) : '';

			$is_new = ( 0 === strpos( (string) $key, 'new' ) );

			// New blank rows only create when a question is provided.
			if ( $is_new && '' === $question ) {
				continue;
			}

			// Delete existing rows flagged for removal.
			if ( ! $is_new && ! empty( $row['delete'] ) ) {
				wp_trash_post( (int) $key );
				continue;
			}

			if ( '' === $question ) {
				continue;
			}

			$postarr = array(
				'post_type'    => 'dfcc_faq',
				'post_status'  => 'publish',
				'post_title'   => $question,
				'post_content' => $answer,
				'menu_order'   => $order,
			);

			if ( $is_new ) {
				wp_insert_post( $postarr );
			} else {
				$postarr['ID'] = (int) $key;
				wp_update_post( $postarr );
			}
			$order++;
		}

		if ( function_exists( 'dfcc_purge_caches' ) ) {
			dfcc_purge_caches();
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-faqs', 'dfcc_saved' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Register the dfcc_faq custom post type.
	 *
	 * Title = question, editor = answer.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'          => __( 'FAQs', 'dog-father-control-center' ),
			'singular_name' => __( 'FAQ', 'dog-father-control-center' ),
			'add_new_item'  => __( 'Add New FAQ', 'dog-father-control-center' ),
			'edit_item'     => __( 'Edit FAQ', 'dog-father-control-center' ),
			'new_item'      => __( 'New FAQ', 'dog-father-control-center' ),
			'view_item'     => __( 'View FAQ', 'dog-father-control-center' ),
			'search_items'  => __( 'Search FAQs', 'dog-father-control-center' ),
			'not_found'     => __( 'No FAQs found', 'dog-father-control-center' ),
			'all_items'     => __( 'FAQs', 'dog-father-control-center' ),
			'menu_name'     => __( 'FAQs', 'dog-father-control-center' ),
		);

		register_post_type(
			'dfcc_faq',
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => dfcc_menu_slug(),
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-editor-help',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'supports'            => array( 'title', 'editor', 'page-attributes' ),
			)
		);
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_FAQ() );
	}
);
