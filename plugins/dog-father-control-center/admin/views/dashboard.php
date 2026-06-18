<?php
/**
 * Control Center dashboard view.
 *
 * @package DogFatherControlCenter
 * @var DFCC_Module[] $modules Registered modules (passed from the controller).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$counts = array(
	'bookings'     => wp_count_posts( 'dfcc_booking' ),
	'dogs'         => wp_count_posts( 'dfcc_dog' ),
	'services'     => wp_count_posts( 'dfcc_service' ),
	'testimonials' => wp_count_posts( 'dfcc_testimonial' ),
);

$pending = isset( $counts['bookings']->pending ) ? (int) $counts['bookings']->pending : 0;

$cards = array(
	array(
		'label' => __( 'Total Bookings', 'dog-father-control-center' ),
		'value' => isset( $counts['bookings']->publish ) ? (int) $counts['bookings']->publish + $pending : 0,
		'icon'  => 'calendar-alt',
		'link'  => 'edit.php?post_type=dfcc_booking',
	),
	array(
		'label' => __( 'Dog Profiles', 'dog-father-control-center' ),
		'value' => isset( $counts['dogs']->publish ) ? (int) $counts['dogs']->publish : 0,
		'icon'  => 'pets',
		'link'  => 'edit.php?post_type=dfcc_dog',
	),
	array(
		'label' => __( 'Services', 'dog-father-control-center' ),
		'value' => isset( $counts['services']->publish ) ? (int) $counts['services']->publish : 0,
		'icon'  => 'heart',
		'link'  => 'edit.php?post_type=dfcc_service',
	),
	array(
		'label' => __( 'Testimonials', 'dog-father-control-center' ),
		'value' => isset( $counts['testimonials']->publish ) ? (int) $counts['testimonials']->publish : 0,
		'icon'  => 'star-filled',
		'link'  => 'edit.php?post_type=dfcc_testimonial',
	),
);

$quick_links = array(
	array(
		'label' => __( 'New Booking', 'dog-father-control-center' ),
		'link'  => 'post-new.php?post_type=dfcc_booking',
	),
	array(
		'label' => __( 'Add Dog Profile', 'dog-father-control-center' ),
		'link'  => 'post-new.php?post_type=dfcc_dog',
	),
	array(
		'label' => __( 'Theme & Brand Colors', 'dog-father-control-center' ),
		'link'  => 'admin.php?page=dfcc-theme',
	),
	array(
		'label' => __( 'Integrations', 'dog-father-control-center' ),
		'link'  => 'admin.php?page=dfcc-integrations',
	),
	array(
		'label' => __( 'SEO Center', 'dog-father-control-center' ),
		'link'  => 'admin.php?page=dfcc-seo',
	),
	array(
		'label' => __( 'Edit Pages with Elementor', 'dog-father-control-center' ),
		'link'  => 'edit.php?post_type=page',
	),
);
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title">
				<span class="dashicons dashicons-pets"></span>
				<?php esc_html_e( 'Dog Father Control Center', 'dog-father-control-center' ); ?>
			</h1>
			<p class="dfcc-subtitle">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: business name. */
						__( 'Manage everything for %s. Design your pages in Elementor — run your business here.', 'dog-father-control-center' ),
						dfcc_get_setting( 'dfcc_global_settings', 'business_name', 'The Dog Father Hotel' )
					)
				);
				?>
			</p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<?php if ( $pending > 0 ) : ?>
		<div class="dfcc-alert">
			<span class="dashicons dashicons-bell"></span>
			<?php
			printf(
				/* translators: %d: number of pending bookings. */
				esc_html( _n( 'You have %d booking awaiting review.', 'You have %d bookings awaiting review.', $pending, 'dog-father-control-center' ) ),
				(int) $pending
			);
			?>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=dfcc_booking&post_status=pending' ) ); ?>"><?php esc_html_e( 'Review now', 'dog-father-control-center' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="dfcc-cards">
		<?php foreach ( $cards as $card ) : ?>
			<a class="dfcc-card" href="<?php echo esc_url( admin_url( $card['link'] ) ); ?>">
				<span class="dashicons dashicons-<?php echo esc_attr( $card['icon'] ); ?>"></span>
				<span class="dfcc-card-value"><?php echo esc_html( number_format_i18n( $card['value'] ) ); ?></span>
				<span class="dfcc-card-label"><?php echo esc_html( $card['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Quick Actions', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-quick-links">
				<?php foreach ( $quick_links as $link ) : ?>
					<a class="dfcc-button" href="<?php echo esc_url( admin_url( $link['link'] ) ); ?>">
						<?php echo esc_html( $link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Loaded Modules', 'dog-father-control-center' ); ?></h2>
			<ul class="dfcc-module-list">
				<?php foreach ( $modules as $module ) : ?>
					<li>
						<span class="dashicons dashicons-yes-alt"></span>
						<?php echo esc_html( $module->label() ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="dfcc-panel dfcc-help">
		<h2><?php esc_html_e( 'How this site works', 'dog-father-control-center' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Design and edit all pages visually with Elementor — nothing is hardcoded.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Use the Theme Settings screen to change brand colors and fonts everywhere at once.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Manage bookings, dog profiles, services, gallery and testimonials from this menu.', 'dog-father-control-center' ); ?></li>
			<li><?php esc_html_e( 'Connect payments, analytics and messaging from the Integrations screen when you are ready.', 'dog-father-control-center' ); ?></li>
		</ol>
	</div>
</div>
