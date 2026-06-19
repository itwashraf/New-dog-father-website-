<?php
/**
 * Setup screen view.
 *
 * @package DogFatherControlCenter
 * @var bool   $done Whether setup has run.
 * @var string $time Last run timestamp.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_action   = isset( $_GET['dfcc_setup'] ) ? sanitize_text_field( wp_unslash( $_GET['dfcc_setup'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dfcc_just_ran = ( 'done' === $dfcc_action );
$dfcc_was_reset = ( 'reset' === $dfcc_action );
$dfcc_is_theme = ( 'dog-father' === get_template() );
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-superhero-alt"></span> <?php esc_html_e( 'One-Click Setup', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Build your entire website in one click — pages, menus and demo content. Everything stays fully editable afterward.', 'dog-father-control-center' ); ?></p>
		</div>
		<?php if ( $done ) : ?><span class="dfcc-version"><?php esc_html_e( 'Setup complete', 'dog-father-control-center' ); ?></span><?php endif; ?>
	</div>

	<?php if ( $dfcc_just_ran ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Setup finished! Your site is ready. Visit the homepage to see it live.', 'dog-father-control-center' ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'dog-father-control-center' ); ?></a>
		</div>
	<?php endif; ?>

	<?php if ( $dfcc_was_reset ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Official Dog Father content applied — homepage, services, FAQs and business info are now up to date.', 'dog-father-control-center' ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'dog-father-control-center' ); ?></a>
		</div>
	<?php endif; ?>

	<?php if ( ! $dfcc_is_theme ) : ?>
		<div class="dfcc-alert" style="border-left-color:var(--dfcc-orange);">
			<span class="dashicons dashicons-warning"></span>
			<?php esc_html_e( 'Tip: activate the “Dog Father” theme (Appearance → Themes) for the full luxury design. Setup still works without it.', 'dog-father-control-center' ); ?>
		</div>
	<?php endif; ?>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'What this creates', 'dog-father-control-center' ); ?></h2>
			<ul class="dfcc-module-list">
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Pages: Home, About, Services, Gallery, Testimonials, Book Now, Contact, FAQ, Blog, Privacy, Terms', 'dog-father-control-center' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Primary + Footer navigation menus', 'dog-father-control-center' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Home set as the front page, Blog as the posts page', 'dog-father-control-center' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( '6 demo services with prices & features', 'dog-father-control-center' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( '6 testimonials and 6 FAQs', 'dog-father-control-center' ); ?></li>
				<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Homepage content & demo gallery placeholders', 'dog-father-control-center' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'Safe to run more than once — it never creates duplicates.', 'dog-father-control-center' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px;">
				<input type="hidden" name="action" value="dfcc_run_setup" />
				<?php wp_nonce_field( 'dfcc_run_setup' ); ?>
				<button type="submit" class="button button-primary button-hero">
					<?php echo $done ? esc_html__( 'Re-run Setup', 'dog-father-control-center' ) : esc_html__( 'Run Setup Now', 'dog-father-control-center' ); ?>
				</button>
			</form>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Next steps', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-quick-links">
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-global' ) ); ?>"><?php esc_html_e( 'Add Business Info', 'dog-father-control-center' ); ?></a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-theme' ) ); ?>"><?php esc_html_e( 'Brand Colors', 'dog-father-control-center' ); ?></a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-home' ) ); ?>"><?php esc_html_e( 'Edit Homepage', 'dog-father-control-center' ); ?></a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=dfcc_service' ) ); ?>"><?php esc_html_e( 'Edit Services', 'dog-father-control-center' ); ?></a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-help' ) ); ?>"><?php esc_html_e( 'Help & Docs', 'dog-father-control-center' ); ?></a>
			</div>
			<?php if ( $time ) : ?>
				<p class="description" style="margin-top:18px;">
					<?php
					printf(
						/* translators: %s: date/time. */
						esc_html__( 'Last run: %s', 'dog-father-control-center' ),
						esc_html( $time )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="dfcc-panel" style="border-color:var(--dfcc-orange,#FF2D08);">
		<h2><?php esc_html_e( 'Apply Official Dog Father Content', 'dog-father-control-center' ); ?></h2>
		<p class="description" style="margin-bottom:12px;">
			<?php esc_html_e( 'Load the official content: homepage copy, real services & EGP prices, FAQs, business info (phone, WhatsApp, email) and the extra pages (Clinic, Home Visit, Pickup, Shop).', 'dog-father-control-center' ); ?>
		</p>
		<p class="description" style="margin-bottom:16px;color:var(--dfcc-orange,#FF2D08);">
			<strong><?php esc_html_e( 'Note:', 'dog-father-control-center' ); ?></strong>
			<?php esc_html_e( 'This overwrites the current homepage text and replaces the demo services, testimonials, FAQs and gallery placeholders. Your bookings, dog profiles, pages and menus are not touched.', 'dog-father-control-center' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Apply the official content? This replaces demo services, testimonials, FAQs and homepage text.', 'dog-father-control-center' ) ); ?>');">
			<input type="hidden" name="action" value="dfcc_reset_content" />
			<?php wp_nonce_field( 'dfcc_reset_content' ); ?>
			<button type="submit" class="button button-secondary button-hero"><?php esc_html_e( 'Reset to Official Content', 'dog-father-control-center' ); ?></button>
		</form>
	</div>
</div>
