<?php
/**
 * Booking calendar view.
 *
 * Monthly grid showing bookings positioned by arrival -> departure date.
 * Supports ?month=YYYY-MM with prev/next navigation. Pure PHP/HTML/CSS.
 *
 * @package DogFatherControlCenter
 * @var string $month Requested month as YYYY-MM (may be empty).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Resolve the requested month, falling back to the current month.
$requested = isset( $month ) ? (string) $month : '';
if ( ! preg_match( '/^\d{4}-\d{2}$/', $requested ) ) {
	$requested = current_time( 'Y-m' );
}

$year      = (int) substr( $requested, 0, 4 );
$month_num = (int) substr( $requested, 5, 2 );
if ( $month_num < 1 || $month_num > 12 ) {
	$month_num = (int) current_time( 'm' );
	$year      = (int) current_time( 'Y' );
}

$first_day      = sprintf( '%04d-%02d-01', $year, $month_num );
$first_ts       = strtotime( $first_day );
$days_in_month  = (int) gmdate( 't', $first_ts );
$last_day       = sprintf( '%04d-%02d-%02d', $year, $month_num, $days_in_month );
$start_weekday  = (int) gmdate( 'w', $first_ts ); // 0 (Sun) - 6 (Sat).
$month_label    = date_i18n( 'F Y', $first_ts );

// Prev / next month strings.
$prev = gmdate( 'Y-m', strtotime( $first_day . ' -1 month' ) );
$next = gmdate( 'Y-m', strtotime( $first_day . ' +1 month' ) );

$base_url = admin_url( 'admin.php?page=dfcc-booking-calendar' );

// Fetch bookings overlapping this month: arrival <= last_day AND departure >= first_day.
$query = new WP_Query(
	array(
		'post_type'      => 'dfcc_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => '_dfcc_arrival_date',
				'value'   => $last_day,
				'compare' => '<=',
				'type'    => 'DATE',
			),
			array(
				'key'     => '_dfcc_departure_date',
				'value'   => $first_day,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	)
);

// Index bookings by each day they cover within this month.
$by_day = array();
foreach ( $query->posts as $post ) {
	$arrival   = get_post_meta( $post->ID, '_dfcc_arrival_date', true );
	$departure = get_post_meta( $post->ID, '_dfcc_departure_date', true );
	if ( ! $arrival ) {
		continue;
	}
	if ( ! $departure ) {
		$departure = $arrival;
	}

	$cursor   = max( strtotime( $arrival ), $first_ts );
	$end_ts   = min( strtotime( $departure ), strtotime( $last_day ) );
	$dog_name = get_post_meta( $post->ID, '_dfcc_dog_name', true );
	$status   = get_post_meta( $post->ID, '_dfcc_status', true );
	if ( ! $status ) {
		$status = 'new';
	}
	$status_label = class_exists( 'DFCC_Bookings' ) ? DFCC_Bookings::status_label( $status ) : ucfirst( $status );
	$title        = $dog_name ? $dog_name : get_the_title( $post );
	$edit_link    = get_edit_post_link( $post->ID );

	for ( $ts = $cursor; $ts <= $end_ts; $ts += DAY_IN_SECONDS ) {
		$key = (int) gmdate( 'j', $ts );
		if ( ! isset( $by_day[ $key ] ) ) {
			$by_day[ $key ] = array();
		}
		$by_day[ $key ][] = array(
			'title'        => $title,
			'status'       => $status,
			'status_label' => $status_label,
			'edit_link'    => $edit_link,
		);
	}
}

// Weekday header labels (start on Sunday to match gmdate('w')).
$weekdays = array(
	__( 'Sun', 'dog-father-control-center' ),
	__( 'Mon', 'dog-father-control-center' ),
	__( 'Tue', 'dog-father-control-center' ),
	__( 'Wed', 'dog-father-control-center' ),
	__( 'Thu', 'dog-father-control-center' ),
	__( 'Fri', 'dog-father-control-center' ),
	__( 'Sat', 'dog-father-control-center' ),
);

$today_key = ( current_time( 'Y-m' ) === sprintf( '%04d-%02d', $year, $month_num ) ) ? (int) current_time( 'j' ) : 0;
?>
<style>
.dfcc-calendar { margin-top: 20px; }
.dfcc-calendar .dfcc-cal-nav {
	display: flex;
	align-items: center;
	gap: 16px;
	margin-bottom: 16px;
}
.dfcc-calendar .dfcc-cal-nav h2 { margin: 0; color: var(--dfcc-gold, #fec208); }
.dfcc-calendar .dfcc-cal-nav a {
	display: inline-block;
	padding: 6px 12px;
	background: var(--dfcc-panel-2, #1f1f25);
	border: 1px solid var(--dfcc-border, #2c2c34);
	border-radius: 4px;
	color: var(--dfcc-yellow, #fff10a);
	text-decoration: none;
}
.dfcc-calendar .dfcc-cal-nav a:hover { border-color: var(--dfcc-gold, #fec208); }
.dfcc-calendar table {
	width: 100%;
	border-collapse: collapse;
	table-layout: fixed;
	background: var(--dfcc-panel, #16161a);
	border: 1px solid var(--dfcc-border, #2c2c34);
}
.dfcc-calendar th {
	padding: 8px;
	text-align: left;
	border: 1px solid var(--dfcc-border, #2c2c34);
	background: var(--dfcc-panel-2, #1f1f25);
	color: var(--dfcc-gold, #fec208);
	font-size: 12px;
	text-transform: uppercase;
}
.dfcc-calendar td {
	vertical-align: top;
	height: 96px;
	padding: 6px;
	border: 1px solid var(--dfcc-border, #2c2c34);
}
.dfcc-calendar td.dfcc-empty { background: rgba(0,0,0,0.2); }
.dfcc-calendar .dfcc-daynum { font-size: 12px; color: #9a9aa2; margin-bottom: 4px; }
.dfcc-calendar td.dfcc-today { outline: 2px solid var(--dfcc-yellow, #fff10a); outline-offset: -2px; }
.dfcc-calendar td.dfcc-today .dfcc-daynum { color: var(--dfcc-yellow, #fff10a); font-weight: 700; }
.dfcc-calendar .dfcc-cal-event {
	display: block;
	margin-bottom: 4px;
	padding: 3px 5px;
	border-radius: 3px;
	background: var(--dfcc-panel-2, #1f1f25);
	border: 1px solid var(--dfcc-border, #2c2c34);
	color: #e6e6ea;
	text-decoration: none;
	font-size: 12px;
	line-height: 1.3;
}
.dfcc-calendar .dfcc-cal-event:hover { border-color: var(--dfcc-gold, #fec208); }
.dfcc-calendar .dfcc-cal-event .dfcc-cal-dog { display: block; font-weight: 600; }
.dfcc-calendar .dfcc-cal-event .dfcc-status { margin-top: 2px; }
</style>

<div class="wrap dfcc-wrap dfcc-calendar">
	<div class="dfcc-cal-nav">
		<h2><?php echo esc_html( $month_label ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'month', $prev, $base_url ) ); ?>">&laquo; <?php esc_html_e( 'Previous', 'dog-father-control-center' ); ?></a>
		<a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Today', 'dog-father-control-center' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'month', $next, $base_url ) ); ?>"><?php esc_html_e( 'Next', 'dog-father-control-center' ); ?> &raquo;</a>
	</div>

	<table>
		<thead>
			<tr>
				<?php foreach ( $weekdays as $wd ) : ?>
					<th><?php echo esc_html( $wd ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$day      = 1;
			$cell     = 0;
			$total    = $start_weekday + $days_in_month;
			$rows     = (int) ceil( $total / 7 );
			for ( $r = 0; $r < $rows; $r++ ) :
				?>
				<tr>
					<?php for ( $c = 0; $c < 7; $c++ ) : ?>
						<?php if ( $cell < $start_weekday || $day > $days_in_month ) : ?>
							<td class="dfcc-empty"></td>
						<?php else : ?>
							<td class="<?php echo ( $today_key === $day ) ? 'dfcc-today' : ''; ?>">
								<div class="dfcc-daynum"><?php echo esc_html( $day ); ?></div>
								<?php
								if ( ! empty( $by_day[ $day ] ) ) :
									foreach ( $by_day[ $day ] as $event ) :
										?>
										<a class="dfcc-cal-event" href="<?php echo esc_url( $event['edit_link'] ); ?>">
											<span class="dfcc-cal-dog"><?php echo esc_html( $event['title'] ); ?></span>
											<span class="dfcc-status dfcc-status-<?php echo esc_attr( $event['status'] ); ?>"><?php echo esc_html( $event['status_label'] ); ?></span>
										</a>
										<?php
									endforeach;
								endif;
								$day++;
								?>
							</td>
						<?php endif; ?>
						<?php $cell++; ?>
					<?php endfor; ?>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
</div>
<?php
wp_reset_postdata();
