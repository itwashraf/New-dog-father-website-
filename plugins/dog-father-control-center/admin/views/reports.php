<?php
/**
 * Reports dashboard view.
 *
 * @package DogFatherControlCenter
 * @var object $booking_counts wp_count_posts() result for bookings.
 * @var array  $by_status      Booking counts keyed by workflow status.
 * @var int    $dogs           Published dog profiles.
 * @var int    $services       Published services.
 * @var int    $testimonials   Published testimonials.
 * @var int    $occupancy      Currently checked-in count.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_bookings = 0;
if ( is_object( $booking_counts ) ) {
	foreach ( get_object_vars( $booking_counts ) as $n ) {
		$total_bookings += (int) $n;
	}
}

$status_labels = array(
	'new'         => __( 'New', 'dog-father-control-center' ),
	'pending'     => __( 'Pending', 'dog-father-control-center' ),
	'approved'    => __( 'Approved', 'dog-father-control-center' ),
	'checked_in'  => __( 'Checked In', 'dog-father-control-center' ),
	'checked_out' => __( 'Checked Out', 'dog-father-control-center' ),
	'cancelled'   => __( 'Cancelled', 'dog-father-control-center' ),
);

$cards = array(
	array( 'label' => __( 'Total Bookings', 'dog-father-control-center' ), 'value' => $total_bookings, 'icon' => 'calendar-alt' ),
	array( 'label' => __( 'Currently Checked In', 'dog-father-control-center' ), 'value' => (int) $occupancy, 'icon' => 'admin-home' ),
	array( 'label' => __( 'Dog Profiles', 'dog-father-control-center' ), 'value' => (int) $dogs, 'icon' => 'pets' ),
	array( 'label' => __( 'Services', 'dog-father-control-center' ), 'value' => (int) $services, 'icon' => 'heart' ),
	array( 'label' => __( 'Testimonials', 'dog-father-control-center' ), 'value' => (int) $testimonials, 'icon' => 'star-filled' ),
);

$export_nonce = wp_create_nonce( 'dfcc_backup_export' );
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-chart-bar"></span><?php esc_html_e( 'Reports', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'A live snapshot of bookings, occupancy and content.', 'dog-father-control-center' ); ?></p>
		</div>
		<span class="dfcc-version"><?php echo esc_html( 'v' . DFCC_VERSION ); ?></span>
	</div>

	<div class="dfcc-cards">
		<?php foreach ( $cards as $card ) : ?>
			<div class="dfcc-card">
				<span class="dashicons dashicons-<?php echo esc_attr( $card['icon'] ); ?>"></span>
				<span class="dfcc-card-value"><?php echo esc_html( number_format_i18n( $card['value'] ) ); ?></span>
				<span class="dfcc-card-label"><?php echo esc_html( $card['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Bookings by Status', 'dog-father-control-center' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Status', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Count', 'dog-father-control-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $by_status as $slug => $count ) : ?>
						<tr>
							<td><span class="dfcc-status dfcc-status-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( isset( $status_labels[ $slug ] ) ? $status_labels[ $slug ] : $slug ); ?></span></td>
							<td><?php echo esc_html( number_format_i18n( (int) $count ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Occupancy Snapshot', 'dog-father-control-center' ); ?></h2>
			<p class="dfcc-card-value"><?php echo esc_html( number_format_i18n( (int) $occupancy ) ); ?></p>
			<p class="dfcc-card-label"><?php esc_html_e( 'dogs currently in the hotel', 'dog-father-control-center' ); ?></p>
			<hr />
			<h2><?php esc_html_e( 'Export', 'dog-father-control-center' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Download a full JSON backup including content, or export bookings/services from their list screens.', 'dog-father-control-center' ); ?></p>
			<div class="dfcc-quick-links">
				<a class="dfcc-button" href="<?php echo esc_url( add_query_arg( array( 'dfcc_backup_export' => 1, '_wpnonce' => $export_nonce ), admin_url( 'admin.php?page=dfcc-backup' ) ) ); ?>">
					<?php esc_html_e( 'Download Full Backup (JSON)', 'dog-father-control-center' ); ?>
				</a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=dfcc_booking' ) ); ?>"><?php esc_html_e( 'Open Bookings', 'dog-father-control-center' ); ?></a>
				<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=dfcc_service' ) ); ?>"><?php esc_html_e( 'Open Services', 'dog-father-control-center' ); ?></a>
			</div>
		</div>
	</div>
</div>
