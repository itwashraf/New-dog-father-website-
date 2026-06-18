<?php
/**
 * Booking reports view.
 *
 * Summary statistics, upcoming arrivals, current guests and a CSS bar chart of
 * bookings per month. CSV export is handled in DFCC_Bookings::maybe_export_csv()
 * on admin_init; this view only renders the export button.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$statuses = class_exists( 'DFCC_Bookings' ) ? DFCC_Bookings::statuses() : array();

// Pull every booking once and compute everything in PHP.
$all = new WP_Query(
	array(
		'post_type'      => 'dfcc_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	)
);

$total          = (int) $all->post_count;
$status_counts  = array_fill_keys( array_keys( $statuses ), 0 );
$current_guests = array();
$upcoming       = array();
$month_counts   = array();

$today_ts   = strtotime( current_time( 'Y-m-d' ) );
$week_ts     = $today_ts + ( 7 * DAY_IN_SECONDS );

foreach ( $all->posts as $post ) {
	$status = get_post_meta( $post->ID, '_dfcc_status', true );
	if ( ! $status || ! isset( $status_counts[ $status ] ) ) {
		$status = 'new';
	}
	if ( isset( $status_counts[ $status ] ) ) {
		$status_counts[ $status ]++;
	}

	$arrival   = get_post_meta( $post->ID, '_dfcc_arrival_date', true );
	$dog_name  = get_post_meta( $post->ID, '_dfcc_dog_name', true );
	$owner     = get_post_meta( $post->ID, '_dfcc_owner_name', true );
	$edit_link = get_edit_post_link( $post->ID );
	$title     = $dog_name ? $dog_name : get_the_title( $post );

	// Current guests.
	if ( 'checked_in' === $status ) {
		$current_guests[] = array(
			'title'     => $title,
			'owner'     => $owner,
			'edit_link' => $edit_link,
		);
	}

	// Upcoming arrivals within the next 7 days.
	if ( $arrival ) {
		$arrival_ts = strtotime( $arrival );
		if ( $arrival_ts >= $today_ts && $arrival_ts <= $week_ts && 'cancelled' !== $status ) {
			$upcoming[] = array(
				'title'     => $title,
				'owner'     => $owner,
				'arrival'   => $arrival,
				'sort'      => $arrival_ts,
				'status'    => $status,
				'edit_link' => $edit_link,
			);
		}

		// Per-month tally.
		$mkey = gmdate( 'Y-m', $arrival_ts );
		if ( ! isset( $month_counts[ $mkey ] ) ) {
			$month_counts[ $mkey ] = 0;
		}
		$month_counts[ $mkey ]++;
	}
}

// Sort upcoming by arrival date ascending.
usort(
	$upcoming,
	static function ( $a, $b ) {
		return $a['sort'] <=> $b['sort'];
	}
);

// Sort month chart chronologically and cap to the most recent 12 months present.
ksort( $month_counts );
if ( count( $month_counts ) > 12 ) {
	$month_counts = array_slice( $month_counts, -12, 12, true );
}
$max_month = $month_counts ? max( $month_counts ) : 0;

$export_url = wp_nonce_url(
	add_query_arg(
		array(
			'page'        => 'dfcc-booking-reports',
			'dfcc_export' => 'bookings',
		),
		admin_url( 'admin.php' )
	),
	'dfcc_export_bookings',
	'dfcc_export_nonce'
);
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title">
				<span class="dashicons dashicons-chart-bar"></span>
				<?php esc_html_e( 'Booking Reports', 'dog-father-control-center' ); ?>
			</h1>
		</div>
		<a class="dfcc-button" href="<?php echo esc_url( $export_url ); ?>">
			<span class="dashicons dashicons-download"></span>
			<?php esc_html_e( 'Export CSV', 'dog-father-control-center' ); ?>
		</a>
	</div>

	<div class="dfcc-cards">
		<div class="dfcc-card">
			<span class="dashicons dashicons-calendar-alt"></span>
			<span class="dfcc-card-value"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
			<span class="dfcc-card-label"><?php esc_html_e( 'Total Bookings', 'dog-father-control-center' ); ?></span>
		</div>
		<div class="dfcc-card">
			<span class="dashicons dashicons-pets"></span>
			<span class="dfcc-card-value"><?php echo esc_html( number_format_i18n( count( $current_guests ) ) ); ?></span>
			<span class="dfcc-card-label"><?php esc_html_e( 'Current Guests', 'dog-father-control-center' ); ?></span>
		</div>
		<div class="dfcc-card">
			<span class="dashicons dashicons-clock"></span>
			<span class="dfcc-card-value"><?php echo esc_html( number_format_i18n( count( $upcoming ) ) ); ?></span>
			<span class="dfcc-card-label"><?php esc_html_e( 'Arrivals (next 7 days)', 'dog-father-control-center' ); ?></span>
		</div>
	</div>

	<div class="dfcc-grid">
		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Bookings by Status', 'dog-father-control-center' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<?php foreach ( $statuses as $value => $label ) : ?>
						<tr>
							<td>
								<span class="dfcc-status dfcc-status-<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></span>
							</td>
							<td style="text-align:right;"><?php echo esc_html( number_format_i18n( isset( $status_counts[ $value ] ) ? $status_counts[ $value ] : 0 ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="dfcc-panel">
			<h2><?php esc_html_e( 'Current Guests', 'dog-father-control-center' ); ?></h2>
			<?php if ( empty( $current_guests ) ) : ?>
				<p><?php esc_html_e( 'No dogs are currently checked in.', 'dog-father-control-center' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Dog', 'dog-father-control-center' ); ?></th>
							<th><?php esc_html_e( 'Owner', 'dog-father-control-center' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $current_guests as $guest ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $guest['edit_link'] ); ?>"><?php echo esc_html( $guest['title'] ); ?></a></td>
								<td><?php echo esc_html( $guest['owner'] ? $guest['owner'] : '—' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<div class="dfcc-panel">
		<h2><?php esc_html_e( 'Upcoming Arrivals (next 7 days)', 'dog-father-control-center' ); ?></h2>
		<?php if ( empty( $upcoming ) ) : ?>
			<p><?php esc_html_e( 'No arrivals scheduled in the next 7 days.', 'dog-father-control-center' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Arrival', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Dog', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Owner', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Status', 'dog-father-control-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $upcoming as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row['arrival'] ); ?></td>
							<td><a href="<?php echo esc_url( $row['edit_link'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a></td>
							<td><?php echo esc_html( $row['owner'] ? $row['owner'] : '—' ); ?></td>
							<td>
								<span class="dfcc-status dfcc-status-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( DFCC_Bookings::status_label( $row['status'] ) ); ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="dfcc-panel">
		<h2><?php esc_html_e( 'Bookings per Month', 'dog-father-control-center' ); ?></h2>
		<?php if ( empty( $month_counts ) ) : ?>
			<p><?php esc_html_e( 'No dated bookings yet.', 'dog-father-control-center' ); ?></p>
		<?php else : ?>
			<style>
				.dfcc-barchart { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
				.dfcc-barchart-row { display: flex; align-items: center; gap: 10px; }
				.dfcc-barchart-label { width: 90px; flex: 0 0 90px; color: var(--dfcc-gold, #fec208); font-size: 12px; }
				.dfcc-barchart-track { flex: 1; background: var(--dfcc-panel-2, #1f1f25); border: 1px solid var(--dfcc-border, #2c2c34); border-radius: 3px; }
				.dfcc-barchart-fill { background: var(--dfcc-yellow, #fff10a); height: 18px; border-radius: 3px; min-width: 2px; }
				.dfcc-barchart-value { width: 40px; flex: 0 0 40px; text-align: right; color: #e6e6ea; font-size: 12px; }
			</style>
			<div class="dfcc-barchart">
				<?php
				foreach ( $month_counts as $mkey => $count ) :
					$pct   = $max_month > 0 ? round( ( $count / $max_month ) * 100 ) : 0;
					$label = date_i18n( 'M Y', strtotime( $mkey . '-01' ) );
					?>
					<div class="dfcc-barchart-row">
						<span class="dfcc-barchart-label"><?php echo esc_html( $label ); ?></span>
						<span class="dfcc-barchart-track">
							<span class="dfcc-barchart-fill" style="width: <?php echo esc_attr( $pct ); ?>%;"></span>
						</span>
						<span class="dfcc-barchart-value"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
wp_reset_postdata();
