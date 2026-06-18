<?php
/**
 * Site footer for the Dog Father Child theme.
 *
 * Renders a branded, multi-column footer site-wide on FREE Elementor (no Pro
 * needed). Uses the Control Center plugin shortcodes for live contact details
 * when the plugin is active, and degrades gracefully when it is not.
 *
 * Menus: assign a menu to the "Footer Menu" location under Appearance > Menus.
 *
 * @package DogFatherChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfchild_has_plugin = function_exists( 'dfcc_get_setting' );
$dfchild_name       = $dfchild_has_plugin ? dfcc_get_setting( 'dfcc_global_settings', 'business_name', '' ) : '';
if ( ! $dfchild_name ) {
	$dfchild_name = get_bloginfo( 'name' );
}
$dfchild_tagline = $dfchild_has_plugin ? dfcc_get_setting( 'dfcc_global_settings', 'tagline', '' ) : get_bloginfo( 'description' );
?>
</main><!-- #dfcc-content -->

<footer class="dfcc-site-footer" id="dfcc-site-footer">
	<div class="dfcc-footer-inner">

		<div class="dfcc-footer-col dfcc-footer-about">
			<h3 class="dfcc-footer-title"><?php echo esc_html( $dfchild_name ); ?></h3>
			<?php if ( $dfchild_tagline ) : ?>
				<p class="dfcc-footer-tagline"><?php echo esc_html( $dfchild_tagline ); ?></p>
			<?php endif; ?>
		</div>

		<div class="dfcc-footer-col dfcc-footer-links">
			<h4 class="dfcc-footer-heading"><?php esc_html_e( 'Explore', 'dog-father-child' ); ?></h4>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'dfcc-footer-menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			}
			?>
		</div>

		<div class="dfcc-footer-col dfcc-footer-contact">
			<h4 class="dfcc-footer-heading"><?php esc_html_e( 'Contact', 'dog-father-child' ); ?></h4>
			<?php if ( $dfchild_has_plugin ) : ?>
				<ul class="dfcc-footer-contact-list">
					<li><?php echo do_shortcode( '[dfcc_phone]' ); ?></li>
					<li><?php echo do_shortcode( '[dfcc_whatsapp]' ); ?></li>
					<li><?php echo do_shortcode( '[dfcc_email]' ); ?></li>
					<li><?php echo do_shortcode( '[dfcc_address]' ); ?></li>
				</ul>
				<div class="dfcc-footer-hours"><?php echo do_shortcode( '[dfcc_hours]' ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'Activate the Dog Father Control Center plugin to show contact details here.', 'dog-father-child' ); ?></p>
			<?php endif; ?>
		</div>

	</div>

	<div class="dfcc-footer-bottom">
		<p>
			<?php
			printf(
				/* translators: 1: year, 2: business name. */
				esc_html__( '© %1$s %2$s. All rights reserved.', 'dog-father-child' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( $dfchild_name )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>

<script>
/* Mobile navigation toggle — vanilla, no dependencies. */
( function () {
	var toggle = document.querySelector( '.dfcc-nav-toggle' );
	var nav    = document.getElementById( 'dfcc-primary-nav' );
	if ( ! toggle || ! nav ) { return; }
	toggle.addEventListener( 'click', function () {
		var open = document.body.classList.toggle( 'dfcc-nav-open' );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );
}() );
</script>
</body>
</html>
