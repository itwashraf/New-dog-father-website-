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
