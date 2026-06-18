<?php
/**
 * Dog Father Child theme functions.
 *
 * A lightweight, Elementor-first child of Hello Elementor. Everything here is
 * defensive: the theme must work even before Elementor or Elementor Pro is
 * installed, and before the Dog Father Control Center plugin is active.
 *
 * @package DogFatherChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DFCHILD_VERSION' ) ) {
	define( 'DFCHILD_VERSION', '1.0.0' );
}

/**
 * Enqueue parent + child styles and brand fonts.
 *
 * @return void
 */
function dfchild_enqueue_styles() {
	$parent_handle = 'hello-elementor-theme-style';

	// Parent (Hello Elementor) stylesheet. Hello Elementor registers its own
	// styles; we enqueue the parent file directly as a safe dependency anchor.
	wp_enqueue_style(
		'hello-elementor-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		DFCHILD_VERSION
	);

	// Child stylesheet depends on the parent so cascade order is correct.
	wp_enqueue_style(
		'dog-father-child-style',
		get_stylesheet_uri(),
		array( 'hello-elementor-parent-style' ),
		DFCHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'dfchild_enqueue_styles', 20 );

/**
 * Enqueue Google Fonts (Poppins + Inter) with display=swap for performance.
 *
 * If the Control Center plugin later swaps fonts via Theme Settings the CSS
 * vars still resolve; these two families cover the default brand stack.
 *
 * @return void
 */
function dfchild_enqueue_fonts() {
	wp_enqueue_style(
		'dfchild-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'dfchild_enqueue_fonts', 5 );

/**
 * Preconnect to Google Fonts hosts to speed up first paint.
 *
 * @param array  $urls          URLs to print.
 * @param string $relation_type Resource hint type.
 * @return array
 */
function dfchild_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'dfchild_resource_hints', 10, 2 );

/**
 * Theme supports + nav menus.
 *
 * @return void
 */
function dfchild_setup() {
	load_child_theme_textdomain( 'dog-father-child', get_stylesheet_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'dog-father-child' ),
			'footer'  => __( 'Footer Menu', 'dog-father-child' ),
			'mobile'  => __( 'Mobile Menu', 'dog-father-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'dfchild_setup' );

/**
 * Register Elementor Theme Builder location support, gracefully.
 *
 * Hello Elementor already declares header/footer/single/archive locations, but
 * we register them again only when Elementor is active so the child theme is
 * self-sufficient if the parent ever changes.
 *
 * @param object $manager Elementor locations manager.
 * @return void
 */
function dfchild_register_elementor_locations( $manager ) {
	if ( is_object( $manager ) && method_exists( $manager, 'register_all_core_location' ) ) {
		$manager->register_all_core_location();
	}
}
if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
	add_action( 'elementor/theme/register_locations', 'dfchild_register_elementor_locations' );
} else {
	// Defer the check until plugins are loaded so we never fatal if Elementor is absent.
	add_action(
		'after_setup_theme',
		static function () {
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				add_action( 'elementor/theme/register_locations', 'dfchild_register_elementor_locations' );
			}
		},
		20
	);
}

/**
 * Small performance helper: remove the WordPress emoji scripts/styles.
 *
 * @return void
 */
function dfchild_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'dfchild_disable_emojis_tinymce' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'dfchild_disable_emojis' );

/**
 * Drop the emoji TinyMCE plugin.
 *
 * @param array $plugins TinyMCE plugins.
 * @return array
 */
function dfchild_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}
	return array();
}
