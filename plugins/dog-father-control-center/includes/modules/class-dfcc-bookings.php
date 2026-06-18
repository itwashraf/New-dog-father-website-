<?php
/**
 * Bookings module.
 *
 * Adds the full booking workflow on top of the dfcc_booking custom post type
 * (registered in DFCC_Post_Types). Provides booking detail + status meta boxes,
 * a custom list table, status filtering, a monthly calendar, a reports screen
 * and a CSV export.
 *
 * All booking meta is stored with the _dfcc_ prefix. The status lives in
 * _dfcc_status and uses one of the values returned by self::statuses().
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Bookings.
 */
class DFCC_Bookings extends DFCC_Module {

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	const PREFIX = '_dfcc_';

	/**
	 * Nonce action for the detail meta box.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'dfcc_save_booking';

	/**
	 * Nonce field name for the detail meta box.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'dfcc_booking_nonce';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'bookings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Bookings', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'add_meta_boxes_dfcc_booking', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_dfcc_booking', array( $this, 'save_meta' ), 10, 2 );

		// List table columns.
		add_filter( 'manage_dfcc_booking_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_dfcc_booking_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-dfcc_booking_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_query' ) );

		// Status filter dropdown.
		add_action( 'restrict_manage_posts', array( $this, 'status_filter' ) );

		// Admin pages.
		add_filter( 'dfcc_admin_pages', array( $this, 'admin_pages' ) );

		// CSV export (runs before any output is sent).
		add_action( 'admin_init', array( $this, 'maybe_export_csv' ) );
	}

	/**
	 * Booking status map: value => human label.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'new'         => __( 'New', 'dog-father-control-center' ),
			'pending'     => __( 'Pending', 'dog-father-control-center' ),
			'approved'    => __( 'Approved', 'dog-father-control-center' ),
			'checked_in'  => __( 'Checked In', 'dog-father-control-center' ),
			'checked_out' => __( 'Checked Out', 'dog-father-control-center' ),
			'cancelled'   => __( 'Cancelled', 'dog-father-control-center' ),
		);
	}

	/**
	 * Vaccination status options.
	 *
	 * @return array
	 */
	public static function vaccination_options() {
		return array(
			'up_to_date' => __( 'Up to date', 'dog-father-control-center' ),
			'partial'    => __( 'Partial', 'dog-father-control-center' ),
			'unknown'    => __( 'Unknown', 'dog-father-control-center' ),
		);
	}

	/**
	 * Read a booking meta value.
	 *
	 * @param int    $post_id Booking post id.
	 * @param string $key     Field key without the prefix.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, $default = '' ) {
		$value = get_post_meta( $post_id, self::PREFIX . $key, true );
		return ( '' === $value || false === $value ) ? $default : $value;
	}

	/**
	 * Resolve a stored status to its human label.
	 *
	 * @param string $status Stored status value.
	 * @return string
	 */
	public static function status_label( $status ) {
		$statuses = self::statuses();
		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : ucfirst( (string) $status );
	}

	/* ---------------------------------------------------------------------
	 * Meta boxes
	 * ------------------------------------------------------------------- */

	/**
	 * Register the booking meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dfcc-booking-details',
			__( 'Booking Details', 'dog-father-control-center' ),
			array( $this, 'render_details_box' ),
			'dfcc_booking',
			'normal',
			'high'
		);

		add_meta_box(
			'dfcc-booking-status',
			__( 'Booking Status', 'dog-father-control-center' ),
			array( $this, 'render_status_box' ),
			'dfcc_booking',
			'side',
			'high'
		);
	}

	/**
	 * Render the booking detail fields.
	 *
	 * @param WP_Post $post Current booking.
	 * @return void
	 */
	public function render_details_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$owner_name          = self::get_meta( $post->ID, 'owner_name' );
		$phone               = self::get_meta( $post->ID, 'phone' );
		$whatsapp            = self::get_meta( $post->ID, 'whatsapp' );
		$email               = self::get_meta( $post->ID, 'email' );
		$dog_name            = self::get_meta( $post->ID, 'dog_name' );
		$breed               = self::get_meta( $post->ID, 'breed' );
		$age                 = self::get_meta( $post->ID, 'age' );
		$gender              = self::get_meta( $post->ID, 'gender' );
		$arrival_date        = self::get_meta( $post->ID, 'arrival_date' );
		$departure_date      = self::get_meta( $post->ID, 'departure_date' );
		$special_instructions = self::get_meta( $post->ID, 'special_instructions' );
		$medical_notes       = self::get_meta( $post->ID, 'medical_notes' );
		$vaccination_status  = self::get_meta( $post->ID, 'vaccination_status' );
		$pickup_required     = self::get_meta( $post->ID, 'pickup_required' );
		?>
		<div class="dfcc-meta-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
			<p>
				<label for="dfcc-owner_name"><strong><?php esc_html_e( 'Owner Name', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-owner_name" name="dfcc_owner_name" value="<?php echo esc_attr( $owner_name ); ?>" />
			</p>
			<p>
				<label for="dfcc-email"><strong><?php esc_html_e( 'Email', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="email" class="widefat" id="dfcc-email" name="dfcc_email" value="<?php echo esc_attr( $email ); ?>" />
			</p>
			<p>
				<label for="dfcc-phone"><strong><?php esc_html_e( 'Phone', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-phone" name="dfcc_phone" value="<?php echo esc_attr( $phone ); ?>" />
			</p>
			<p>
				<label for="dfcc-whatsapp"><strong><?php esc_html_e( 'WhatsApp', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-whatsapp" name="dfcc_whatsapp" value="<?php echo esc_attr( $whatsapp ); ?>" />
			</p>
			<p>
				<label for="dfcc-dog_name"><strong><?php esc_html_e( 'Dog Name', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-dog_name" name="dfcc_dog_name" value="<?php echo esc_attr( $dog_name ); ?>" />
			</p>
			<p>
				<label for="dfcc-breed"><strong><?php esc_html_e( 'Breed', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-breed" name="dfcc_breed" value="<?php echo esc_attr( $breed ); ?>" />
			</p>
			<p>
				<label for="dfcc-age"><strong><?php esc_html_e( 'Age', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-age" name="dfcc_age" value="<?php echo esc_attr( $age ); ?>" />
			</p>
			<p>
				<label for="dfcc-gender"><strong><?php esc_html_e( 'Gender', 'dog-father-control-center' ); ?></strong></label><br />
				<select class="widefat" id="dfcc-gender" name="dfcc_gender">
					<option value=""><?php esc_html_e( '— Select —', 'dog-father-control-center' ); ?></option>
					<option value="Male" <?php selected( $gender, 'Male' ); ?>><?php esc_html_e( 'Male', 'dog-father-control-center' ); ?></option>
					<option value="Female" <?php selected( $gender, 'Female' ); ?>><?php esc_html_e( 'Female', 'dog-father-control-center' ); ?></option>
				</select>
			</p>
			<p>
				<label for="dfcc-arrival_date"><strong><?php esc_html_e( 'Arrival Date', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="date" class="widefat" id="dfcc-arrival_date" name="dfcc_arrival_date" value="<?php echo esc_attr( $arrival_date ); ?>" />
			</p>
			<p>
				<label for="dfcc-departure_date"><strong><?php esc_html_e( 'Departure Date', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="date" class="widefat" id="dfcc-departure_date" name="dfcc_departure_date" value="<?php echo esc_attr( $departure_date ); ?>" />
			</p>
			<p>
				<label for="dfcc-vaccination_status"><strong><?php esc_html_e( 'Vaccination Status', 'dog-father-control-center' ); ?></strong></label><br />
				<select class="widefat" id="dfcc-vaccination_status" name="dfcc_vaccination_status">
					<option value=""><?php esc_html_e( '— Select —', 'dog-father-control-center' ); ?></option>
					<?php foreach ( self::vaccination_options() as $value => $vlabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $vaccination_status, $value ); ?>><?php echo esc_html( $vlabel ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Pickup', 'dog-father-control-center' ); ?></strong></label><br />
				<label for="dfcc-pickup_required">
					<input type="checkbox" id="dfcc-pickup_required" name="dfcc_pickup_required" value="1" <?php checked( $pickup_required, '1' ); ?> />
					<?php esc_html_e( 'Pickup required', 'dog-father-control-center' ); ?>
				</label>
			</p>
		</div>
		<p>
			<label for="dfcc-special_instructions"><strong><?php esc_html_e( 'Special Instructions', 'dog-father-control-center' ); ?></strong></label><br />
			<textarea class="widefat" rows="3" id="dfcc-special_instructions" name="dfcc_special_instructions"><?php echo esc_textarea( $special_instructions ); ?></textarea>
		</p>
		<p>
			<label for="dfcc-medical_notes"><strong><?php esc_html_e( 'Medical Notes', 'dog-father-control-center' ); ?></strong></label><br />
			<textarea class="widefat" rows="3" id="dfcc-medical_notes" name="dfcc_medical_notes"><?php echo esc_textarea( $medical_notes ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Render the status meta box.
	 *
	 * @param WP_Post $post Current booking.
	 * @return void
	 */
	public function render_status_box( $post ) {
		$current = self::get_meta( $post->ID, 'status', 'new' );
		?>
		<p>
			<label for="dfcc-status"><strong><?php esc_html_e( 'Status', 'dog-father-control-center' ); ?></strong></label>
		</p>
		<select class="widefat" id="dfcc-status" name="dfcc_status">
			<?php foreach ( self::statuses() as $value => $slabel ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $slabel ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'New bookings start as "New".', 'dog-father-control-center' ); ?></p>
		<?php
	}

	/**
	 * Persist booking meta.
	 *
	 * @param int     $post_id Booking id.
	 * @param WP_Post $post    Booking object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		// Nonce.
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		// Autosave / revision guards.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Capability.
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Plain text fields.
		$text_fields = array( 'owner_name', 'phone', 'whatsapp', 'dog_name', 'breed', 'age' );
		foreach ( $text_fields as $field ) {
			$value = isset( $_POST[ 'dfcc_' . $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_' . $field ] ) ) : '';
			update_post_meta( $post_id, self::PREFIX . $field, $value );
		}

		// Email.
		$email = isset( $_POST['dfcc_email'] ) ? sanitize_email( wp_unslash( $_POST['dfcc_email'] ) ) : '';
		update_post_meta( $post_id, self::PREFIX . 'email', $email );

		// Gender (whitelist).
		$gender     = isset( $_POST['dfcc_gender'] ) ? sanitize_text_field( wp_unslash( $_POST['dfcc_gender'] ) ) : '';
		$gender     = in_array( $gender, array( 'Male', 'Female' ), true ) ? $gender : '';
		update_post_meta( $post_id, self::PREFIX . 'gender', $gender );

		// Dates.
		foreach ( array( 'arrival_date', 'departure_date' ) as $date_field ) {
			$raw  = isset( $_POST[ 'dfcc_' . $date_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_' . $date_field ] ) ) : '';
			$date = $this->sanitize_date( $raw );
			update_post_meta( $post_id, self::PREFIX . $date_field, $date );
		}

		// Textareas.
		foreach ( array( 'special_instructions', 'medical_notes' ) as $area ) {
			$value = isset( $_POST[ 'dfcc_' . $area ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'dfcc_' . $area ] ) ) : '';
			update_post_meta( $post_id, self::PREFIX . $area, $value );
		}

		// Vaccination status (whitelist).
		$vacc = isset( $_POST['dfcc_vaccination_status'] ) ? sanitize_text_field( wp_unslash( $_POST['dfcc_vaccination_status'] ) ) : '';
		$vacc = array_key_exists( $vacc, self::vaccination_options() ) ? $vacc : '';
		update_post_meta( $post_id, self::PREFIX . 'vaccination_status', $vacc );

		// Checkbox.
		$pickup = isset( $_POST['dfcc_pickup_required'] ) ? '1' : '';
		update_post_meta( $post_id, self::PREFIX . 'pickup_required', $pickup );

		// Status (whitelist, default new).
		$status = isset( $_POST['dfcc_status'] ) ? sanitize_text_field( wp_unslash( $_POST['dfcc_status'] ) ) : 'new';
		$status = array_key_exists( $status, self::statuses() ) ? $status : 'new';
		update_post_meta( $post_id, self::PREFIX . 'status', $status );
	}

	/**
	 * Validate a YYYY-MM-DD date string, returning '' when invalid.
	 *
	 * @param string $raw Raw value.
	 * @return string
	 */
	private function sanitize_date( $raw ) {
		if ( '' === $raw ) {
			return '';
		}
		$d = DateTime::createFromFormat( 'Y-m-d', $raw );
		if ( $d && $d->format( 'Y-m-d' ) === $raw ) {
			return $raw;
		}
		return '';
	}

	/* ---------------------------------------------------------------------
	 * List table
	 * ------------------------------------------------------------------- */

	/**
	 * Define the list table columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		if ( isset( $columns['cb'] ) ) {
			$new['cb'] = $columns['cb'];
		}
		$new['title']             = isset( $columns['title'] ) ? $columns['title'] : __( 'Title', 'dog-father-control-center' );
		$new['dfcc_dog_name']     = __( 'Dog Name', 'dog-father-control-center' );
		$new['dfcc_owner']        = __( 'Owner', 'dog-father-control-center' );
		$new['dfcc_arrival']      = __( 'Arrival', 'dog-father-control-center' );
		$new['dfcc_departure']    = __( 'Departure', 'dog-father-control-center' );
		$new['dfcc_status']       = __( 'Status', 'dog-father-control-center' );
		$new['dfcc_phone']        = __( 'Phone', 'dog-father-control-center' );
		$new['date']              = isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'dog-father-control-center' );
		return $new;
	}

	/**
	 * Render a custom column cell.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Booking id.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'dfcc_dog_name':
				echo esc_html( self::get_meta( $post_id, 'dog_name', '—' ) );
				break;
			case 'dfcc_owner':
				echo esc_html( self::get_meta( $post_id, 'owner_name', '—' ) );
				break;
			case 'dfcc_arrival':
				$d = self::get_meta( $post_id, 'arrival_date' );
				echo $d ? esc_html( $d ) : '—';
				break;
			case 'dfcc_departure':
				$d = self::get_meta( $post_id, 'departure_date' );
				echo $d ? esc_html( $d ) : '—';
				break;
			case 'dfcc_status':
				$status = self::get_meta( $post_id, 'status', 'new' );
				printf(
					'<span class="dfcc-status dfcc-status-%1$s">%2$s</span>',
					esc_attr( $status ),
					esc_html( self::status_label( $status ) )
				);
				break;
			case 'dfcc_phone':
				echo esc_html( self::get_meta( $post_id, 'phone', '—' ) );
				break;
		}
	}

	/**
	 * Mark arrival/departure columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['dfcc_arrival']   = 'dfcc_arrival';
		$columns['dfcc_departure'] = 'dfcc_departure';
		return $columns;
	}

	/**
	 * Apply sorting and status filtering to the bookings list query.
	 *
	 * @param WP_Query $query Current query.
	 * @return void
	 */
	public function handle_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'dfcc_booking' !== $query->get( 'post_type' ) ) {
			return;
		}

		// Sorting by date meta.
		$orderby = $query->get( 'orderby' );
		if ( 'dfcc_arrival' === $orderby ) {
			$query->set( 'meta_key', self::PREFIX . 'arrival_date' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'dfcc_departure' === $orderby ) {
			$query->set( 'meta_key', self::PREFIX . 'departure_date' );
			$query->set( 'orderby', 'meta_value' );
		}

		// Status filter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['dfcc_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['dfcc_status_filter'] ) ) : '';
		if ( '' !== $status && array_key_exists( $status, self::statuses() ) ) {
			$meta_query   = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'key'   => self::PREFIX . 'status',
				'value' => $status,
			);
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Output the status filter dropdown above the list table.
	 *
	 * @param string $post_type Current screen post type.
	 * @return void
	 */
	public function status_filter( $post_type ) {
		if ( 'dfcc_booking' !== $post_type ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = isset( $_GET['dfcc_status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['dfcc_status_filter'] ) ) : '';
		?>
		<label class="screen-reader-text" for="dfcc_status_filter"><?php esc_html_e( 'Filter by status', 'dog-father-control-center' ); ?></label>
		<select name="dfcc_status_filter" id="dfcc_status_filter">
			<option value=""><?php esc_html_e( 'All statuses', 'dog-father-control-center' ); ?></option>
			<?php foreach ( self::statuses() as $value => $slabel ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $slabel ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/* ---------------------------------------------------------------------
	 * Admin pages
	 * ------------------------------------------------------------------- */

	/**
	 * Register the calendar and reports screens.
	 *
	 * @param array $pages Existing admin pages.
	 * @return array
	 */
	public function admin_pages( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-booking-calendar',
			'title'    => __( 'Booking Calendar', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_calendar' ),
			'order'    => 21,
		);
		$pages[] = array(
			'slug'     => 'dfcc-booking-reports',
			'title'    => __( 'Booking Reports', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_reports' ),
			'order'    => 22,
		);
		return $pages;
	}

	/**
	 * Render the calendar screen.
	 *
	 * @return void
	 */
	public function render_calendar() {
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$month = isset( $_GET['month'] ) ? sanitize_text_field( wp_unslash( $_GET['month'] ) ) : '';
		$this->view(
			'booking-calendar',
			array(
				'month'    => $month,
				'bookings' => null,
			)
		);
	}

	/**
	 * Render the reports screen.
	 *
	 * @return void
	 */
	public function render_reports() {
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		$this->view( 'booking-reports', array() );
	}

	/* ---------------------------------------------------------------------
	 * CSV export
	 * ------------------------------------------------------------------- */

	/**
	 * Stream a CSV export of all bookings when requested.
	 *
	 * Triggered by ?dfcc_export=bookings with a valid nonce. Runs on admin_init
	 * so headers can be sent before any page output.
	 *
	 * @return void
	 */
	public function maybe_export_csv() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['dfcc_export'] ) || 'bookings' !== $_GET['dfcc_export'] ) {
			return;
		}

		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}

		$nonce = isset( $_GET['dfcc_export_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['dfcc_export_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'dfcc_export_bookings' ) ) {
			return;
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_booking',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		$headers = array(
			'Booking',
			'Owner Name',
			'Phone',
			'WhatsApp',
			'Email',
			'Dog Name',
			'Breed',
			'Age',
			'Gender',
			'Arrival',
			'Departure',
			'Vaccination',
			'Pickup Required',
			'Status',
			'Special Instructions',
			'Medical Notes',
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=bookings-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, $headers );

		foreach ( $query->posts as $post ) {
			$row = array(
				get_the_title( $post ),
				self::get_meta( $post->ID, 'owner_name' ),
				self::get_meta( $post->ID, 'phone' ),
				self::get_meta( $post->ID, 'whatsapp' ),
				self::get_meta( $post->ID, 'email' ),
				self::get_meta( $post->ID, 'dog_name' ),
				self::get_meta( $post->ID, 'breed' ),
				self::get_meta( $post->ID, 'age' ),
				self::get_meta( $post->ID, 'gender' ),
				self::get_meta( $post->ID, 'arrival_date' ),
				self::get_meta( $post->ID, 'departure_date' ),
				self::status_label( self::get_meta( $post->ID, 'vaccination_status' ) ),
				self::get_meta( $post->ID, 'pickup_required' ) ? 'Yes' : 'No',
				self::status_label( self::get_meta( $post->ID, 'status', 'new' ) ),
				self::get_meta( $post->ID, 'special_instructions' ),
				self::get_meta( $post->ID, 'medical_notes' ),
			);
			fputcsv( $out, $row );
		}

		fclose( $out );
		exit;
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Bookings() );
	}
);
