<?php
/**
 * About / Provada view — theme author credit.
 *
 * @package DogFatherControlCenter
 * @var array  $brand   Provada brand (name, url).
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-superhero"></span> <?php esc_html_e( 'About this theme', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'The Dog Father theme & Control Center.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<div class="dfcc-panel" style="text-align:center;padding:40px 24px;">
		<div style="font-family:Poppins,system-ui,sans-serif;font-weight:800;font-size:2rem;letter-spacing:-.02em;">
			<?php esc_html_e( 'Built by', 'dog-father-control-center' ); ?>
			<a href="<?php echo esc_url( $brand['url'] ); ?>" target="_blank" rel="noopener" style="text-decoration:none;background:linear-gradient(120deg,#FEC208,#FF2D08);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;"><?php echo esc_html( $brand['name'] ); ?></a>
		</div>
		<p style="max-width:620px;margin:14px auto 0;color:#646970;font-size:15px;">
			<?php esc_html_e( 'This complete website — the theme and this control center — was designed and developed by Provada. We build fast, beautiful, fully editable WordPress sites that owners can run themselves.', 'dog-father-control-center' ); ?>
		</p>
		<p style="margin-top:20px;">
			<a class="button button-primary button-hero" href="<?php echo esc_url( $brand['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit provada.net', 'dog-father-control-center' ); ?></a>
		</p>
	</div>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'What you get', 'dog-father-control-center' ); ?></h2>
			<ul style="line-height:1.9;">
				<li><?php esc_html_e( 'A self-contained theme — no page builder or paid plugins required.', 'dog-father-control-center' ); ?></li>
				<li><?php esc_html_e( 'Every part of the site editable from one dashboard.', 'dog-father-control-center' ); ?></li>
				<li><?php esc_html_e( 'Brand colors, fonts and layout controlled from Theme Settings.', 'dog-father-control-center' ); ?></li>
				<li><?php esc_html_e( 'Built-in bookings, services, FAQs, testimonials, gallery and SEO.', 'dog-father-control-center' ); ?></li>
			</ul>
		</div>
		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Details', 'dog-father-control-center' ); ?></h2>
			<table class="widefat striped">
				<tr><td><?php esc_html_e( 'Product', 'dog-father-control-center' ); ?></td><td><strong><?php esc_html_e( 'The Dog Father — Theme & Control Center', 'dog-father-control-center' ); ?></strong></td></tr>
				<tr><td><?php esc_html_e( 'Version', 'dog-father-control-center' ); ?></td><td><?php echo esc_html( $version ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Author', 'dog-father-control-center' ); ?></td><td><a href="<?php echo esc_url( $brand['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $brand['name'] ); ?></a></td></tr>
				<tr><td><?php esc_html_e( 'Help', 'dog-father-control-center' ); ?></td><td><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-help' ) ); ?>"><?php esc_html_e( 'Help & Docs', 'dog-father-control-center' ); ?></a></td></tr>
			</table>
		</div>
	</div>
</div>
