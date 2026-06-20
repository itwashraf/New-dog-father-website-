<?php
/**
 * Help & Docs view — an in-panel manual for non-technical owners.
 *
 * @package DogFatherControlCenter
 * @var array $brand Provada brand (name, url).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Where do I edit X?" rows: label => array( where, page-url|'' ).
 */
$dfcc_map = array(
	__( 'Logo', 'dog-father-control-center' )                  => array( __( 'Appearance → Customize → Site Identity', 'dog-father-control-center' ), admin_url( 'customize.php' ) ),
	__( 'Menu links (top navigation)', 'dog-father-control-center' ) => array( __( 'Appearance → Menus → "Primary Menu"', 'dog-father-control-center' ), admin_url( 'nav-menus.php' ) ),
	__( 'Phone & WhatsApp icons (header)', 'dog-father-control-center' ) => array( __( 'Global Settings → Contact', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-global' ) ),
	__( 'Hero banner (headline, image, buttons)', 'dog-father-control-center' ) => array( __( 'Homepage → Hero Banner', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-home' ) ),
	__( 'Trust bar & "Why choose us"', 'dog-father-control-center' ) => array( __( 'Homepage → Trust Bar / Why Choose Us', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-home' ) ),
	__( 'Section order & per-section style', 'dog-father-control-center' ) => array( __( 'Homepage → Section Layout', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-home' ) ),
	__( 'Services & prices', 'dog-father-control-center' )     => array( __( 'Manage Services', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-services' ) ),
	__( 'FAQs', 'dog-father-control-center' )                  => array( __( 'Manage FAQs', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-faqs' ) ),
	__( 'Testimonials', 'dog-father-control-center' )          => array( __( 'Testimonials', 'dog-father-control-center' ), admin_url( 'edit.php?post_type=dfcc_testimonial' ) ),
	__( 'Gallery photos', 'dog-father-control-center' )        => array( __( 'Gallery', 'dog-father-control-center' ), admin_url( 'edit.php?post_type=dfcc_gallery' ) ),
	__( 'Colors, fonts & section colors', 'dog-father-control-center' ) => array( __( 'Theme Settings', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-theme' ) ),
	__( 'Layout, announcement bar, custom CSS', 'dog-father-control-center' ) => array( __( 'Theme Settings → Layout', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-theme' ) ),
	__( 'Address, hours, email, map, social links', 'dog-father-control-center' ) => array( __( 'Global Settings', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-global' ) ),
	__( 'Footer credit ("Designed by…")', 'dog-father-control-center' ) => array( __( 'Global Settings → Footer Credit', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-global' ) ),
	__( 'Old page-builder warnings', 'dog-father-control-center' ) => array( __( 'Content Cleanup (appears only when needed)', 'dog-father-control-center' ), admin_url( 'admin.php?page=dfcc-cleanup' ) ),
);

$dfcc_faq = array(
	array( __( 'How do I change the text on the homepage?', 'dog-father-control-center' ), __( 'Go to Dog Father → Homepage. Every section has its own fields. Type your text and click Save.', 'dog-father-control-center' ) ),
	array( __( 'My change isn’t showing on the site.', 'dog-father-control-center' ), __( 'Saving clears the cache automatically. If you still see the old version, refresh with Ctrl/Cmd+Shift+R, or clear any caching plugin/CDN.', 'dog-father-control-center' ) ),
	array( __( 'I see a "block not supported" warning.', 'dog-father-control-center' ), __( 'That is leftover content from the old page builder. Go to Dog Father → Cleanup and click Clean. Every change is backed up so you can undo it.', 'dog-father-control-center' ) ),
	array( __( 'How do I hide a whole section?', 'dog-father-control-center' ), __( 'Dog Father → Homepage → Section Layout. Untick the section, or drag to reorder.', 'dog-father-control-center' ) ),
	array( __( 'How do I change a colour everywhere?', 'dog-father-control-center' ), __( 'Dog Father → Theme Settings → Brand Colors. To target a specific area, use Section Colors below it.', 'dog-father-control-center' ) ),
	array( __( 'How do I start over with sample content?', 'dog-father-control-center' ), __( 'Dog Father → Setup → "Import / reset demo content".', 'dog-father-control-center' ) ),
);
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-book"></span> <?php esc_html_e( 'Help & Docs', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Everything you need to run this website yourself — no developer required.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'How this website works', 'dog-father-control-center' ); ?></h2>
		<ol style="font-size:14px;line-height:1.8;max-width:820px;">
			<li><?php esc_html_e( 'Everything you see on the site is edited from this “Dog Father” menu — nothing is hardcoded.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Text & images for the homepage live under “Homepage”. Your business details live under “Global Settings”.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Colours, fonts and layout live under “Theme Settings” and apply across the whole site instantly.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Lists of things (services, FAQs, testimonials, gallery) each have their own manager screen.', 'dog-father-control-center' ); ?></li>
		</ol>
	</div>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'Where do I edit…?', 'dog-father-control-center' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th style="width:40%;"><?php esc_html_e( 'I want to change…', 'dog-father-control-center' ); ?></th>
					<th><?php esc_html_e( 'Go to', 'dog-father-control-center' ); ?></th>
					<th style="width:90px;"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $dfcc_map as $what => $where ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $what ); ?></strong></td>
						<td><?php echo esc_html( $where[0] ); ?></td>
						<td><?php if ( $where[1] ) : ?><a class="button button-small" href="<?php echo esc_url( $where[1] ); ?>"><?php esc_html_e( 'Open', 'dog-father-control-center' ); ?></a><?php endif; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'Common questions', 'dog-father-control-center' ); ?></h2>
		<?php foreach ( $dfcc_faq as $qa ) : ?>
			<details style="border-bottom:1px solid #ededf0;padding:10px 0;">
				<summary style="cursor:pointer;font-weight:600;"><?php echo esc_html( $qa[0] ); ?></summary>
				<p style="margin:8px 0 0;color:#646970;"><?php echo esc_html( $qa[1] ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>

	<p style="color:#8a8a92;">
		<?php
		printf(
			/* translators: 1: Provada link. */
			esc_html__( 'Built and supported by %s.', 'dog-father-control-center' ),
			'<a href="' . esc_url( $brand['url'] ) . '" target="_blank" rel="noopener"><strong>' . esc_html( $brand['name'] ) . '</strong></a>'
		);
		?>
	</p>
</div>
