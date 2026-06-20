<?php
/**
 * The Dog Father theme — bootstrap.
 *
 * A self-contained luxury theme. No parent theme. Works with zero plugins, and
 * integrates automatically with the Dog Father Control Center plugin and
 * Elementor when they are present.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DFATHER_VERSION', '1.3.2' );
define( 'DFATHER_DIR', get_template_directory() );
define( 'DFATHER_URI', get_template_directory_uri() );

require_once DFATHER_DIR . '/inc/helpers.php';
require_once DFATHER_DIR . '/inc/template-tags.php';

/**
 * Theme setup.
 *
 * @return void
 */
function dfather_setup() {
	load_theme_textdomain( 'dog-father', DFATHER_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'custom-background', array( 'default-color' => '000000' ) );

	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1280;
	}

	add_image_size( 'dfather-card', 720, 540, true );
	add_image_size( 'dfather-wide', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'dog-father' ),
			'footer'  => __( 'Footer Menu', 'dog-father' ),
			'mobile'  => __( 'Mobile Menu', 'dog-father' ),
		)
	);
}
add_action( 'after_setup_theme', 'dfather_setup' );

/**
 * Front-end assets.
 *
 * @return void
 */
function dfather_assets() {
	// Brand fonts.
	wp_enqueue_style(
		'dfather-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	// Dashicons on the front end (used by section icons).
	wp_enqueue_style( 'dashicons' );

	// Main stylesheet.
	wp_enqueue_style( 'dfather-theme', DFATHER_URI . '/assets/css/theme.css', array(), DFATHER_VERSION );

	// Required style.css header file, loaded last so overrides win.
	wp_enqueue_style( 'dfather-style', get_stylesheet_uri(), array( 'dfather-theme' ), DFATHER_VERSION );

	// Scripts.
	wp_enqueue_script( 'dfather-theme', DFATHER_URI . '/assets/js/theme.js', array(), DFATHER_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dfather_assets' );

/**
 * Preconnect to Google Fonts for faster first paint.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Hint type.
 * @return array
 */
function dfather_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'dfather_resource_hints', 10, 2 );

/**
 * Fallback brand CSS variables.
 *
 * The Dog Father Control Center plugin prints the live, editable brand colors
 * on wp_head. When the plugin is inactive we still want the palette available,
 * so we print the defaults here (only if the plugin has not already done so).
 *
 * @return void
 */
function dfather_fallback_brand_vars() {
	if ( function_exists( 'dfcc_brand_color' ) ) {
		return; // Plugin owns the live values.
	}
	echo "<style id=\"dfather-brand-vars\">:root{--dfcc-primary:#FFF10A;--dfcc-gold:#FEC208;--dfcc-dark-red:#CF240A;--dfcc-orange:#FF2D08;--dfcc-black:#000000;--dfcc-white:#FFFFFF;}</style>\n";
}
add_action( 'wp_head', 'dfather_fallback_brand_vars', 1 );

/**
 * Register Elementor Theme Builder locations when Elementor (Pro) is active so
 * power users can override header/footer/etc. Harmless without Elementor.
 *
 * @param object $manager Locations manager.
 * @return void
 */
function dfather_elementor_locations( $manager ) {
	if ( is_object( $manager ) && method_exists( $manager, 'register_all_core_location' ) ) {
		$manager->register_all_core_location();
	}
}
add_action( 'elementor/theme/register_locations', 'dfather_elementor_locations' );

/**
 * Performance: drop emoji scripts.
 *
 * @return void
 */
function dfather_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'dfather_disable_emojis' );

/**
 * Add helpful body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function dfather_body_classes( $classes ) {
	$classes[] = 'dfather';
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'dfather-no-sidebar';
	}

	if ( is_front_page() ) {
		$classes[] = 'df-front';
		if ( '0' !== (string) dfather_style( 'header_transparent', '0' ) && (int) dfather_style( 'header_transparent', 0 ) ) {
			$classes[] = 'df-header-transparent';
		}
	}
	// Sticky header on by default; add a class only when explicitly turned off.
	if ( '0' === (string) dfather_style( 'header_sticky', '1' ) ) {
		$classes[] = 'df-header-static';
	}

	return $classes;
}
add_filter( 'body_class', 'dfather_body_classes' );

/**
 * Footer widget area (optional, used by some Elementor-free layouts).
 *
 * @return void
 */
function dfather_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer Widgets', 'dog-father' ),
			'id'            => 'footer-1',
			'before_widget' => '<div class="df-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="df-footer__heading">',
			'after_title'   => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'dfather_widgets_init' );

/**
 * Excerpt tweaks.
 */
add_filter( 'excerpt_more', static function () { return '…'; } );
add_filter( 'excerpt_length', static function () { return 26; } );
