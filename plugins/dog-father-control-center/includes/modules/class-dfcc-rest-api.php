<?php
/**
 * Public REST API module.
 *
 * Exposes read endpoints for services, testimonials and gallery items plus a
 * write endpoint that accepts public booking submissions and stores them as
 * pending dfcc_booking posts (compatible with the DFCC_Bookings workflow:
 * _dfcc_ meta keys and _dfcc_status = 'new').
 *
 * Also provides the [dfcc_booking_form] conversion shortcode which submits to
 * the bookings endpoint via fetch (handled by public/js/frontend.js).
 *
 * Namespace: dfcc/v1
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_REST_API.
 */
class DFCC_REST_API extends DFCC_Module {

	/**
	 * Meta key prefix (shared with DFCC_Bookings).
	 *
	 * @var string
	 */
	const PREFIX = '_dfcc_';

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const NS = 'dfcc/v1';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'rest-api';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Public REST API', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_shortcode( 'dfcc_booking_form', array( $this, 'booking_form_shortcode' ) );
	}

	/**
	 * Whether the public REST endpoints are enabled.
	 *
	 * Honors the dfcc_integration_settings 'rest_enabled' toggle when present,
	 * defaulting to enabled.
	 *
	 * @return bool
	 */
	private function rest_enabled() {
		$settings = get_option( 'dfcc_integration_settings', array() );
		if ( is_array( $settings ) && array_key_exists( 'rest_enabled', $settings ) ) {
			return (bool) $settings['rest_enabled'];
		}
		return true;
	}

	/* ---------------------------------------------------------------------
	 * Routes
	 * ------------------------------------------------------------------- */

	/**
	 * Register all REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::NS,
			'/services',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_services' ),
				'permission_callback' => array( $this, 'public_read_permission' ),
				'args'                => array(
					'count'    => array(
						'default'           => 12,
						'sanitize_callback' => 'absint',
					),
					'category' => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/testimonials',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_testimonials' ),
				'permission_callback' => array( $this, 'public_read_permission' ),
				'args'                => array(
					'count'    => array(
						'default'           => 12,
						'sanitize_callback' => 'absint',
					),
					'featured' => array(
						'default'           => 0,
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/gallery',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_gallery' ),
				'permission_callback' => array( $this, 'public_read_permission' ),
				'args'                => array(
					'count' => array(
						'default'           => 12,
						'sanitize_callback' => 'absint',
					),
					'album' => array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/bookings',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_booking' ),
				'permission_callback' => array( $this, 'booking_permission' ),
			)
		);
	}

	/**
	 * Permission callback for the public read endpoints.
	 *
	 * @return bool|WP_Error
	 */
	public function public_read_permission() {
		if ( ! $this->rest_enabled() ) {
			return new WP_Error( 'dfcc_rest_disabled', __( 'The API is currently disabled.', 'dog-father-control-center' ), array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * Permission callback for booking submissions.
	 *
	 * Public submissions are accepted but must carry a valid wp_rest nonce
	 * (sent by frontend.js via the X-WP-Nonce header). This keeps the endpoint
	 * usable by anonymous visitors while preventing trivial off-site abuse.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function booking_permission( $request ) {
		if ( ! $this->rest_enabled() ) {
			return new WP_Error( 'dfcc_rest_disabled', __( 'Bookings are currently unavailable.', 'dog-father-control-center' ), array( 'status' => 403 ) );
		}

		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( empty( $nonce ) ) {
			$nonce = $request->get_param( '_wpnonce' );
		}

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'dfcc_invalid_nonce',
				__( 'Your session has expired. Please reload the page and try again.', 'dog-father-control-center' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/* ---------------------------------------------------------------------
	 * Read endpoints
	 * ------------------------------------------------------------------- */

	/**
	 * GET /services.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_services( $request ) {
		$args = array(
			'post_type'      => 'dfcc_service',
			'post_status'    => 'publish',
			'posts_per_page' => min( 50, max( 1, (int) $request['count'] ) ),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		);

		if ( '' !== $request['category'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'dfcc_service_cat',
					'field'    => 'slug',
					'terms'    => sanitize_title( $request['category'] ),
				),
			);
		}

		$query = new WP_Query( $args );
		$items = array();

		foreach ( $query->posts as $post ) {
			$features = (string) get_post_meta( $post->ID, self::PREFIX . 'features', true );
			$items[]  = array(
				'id'           => $post->ID,
				'title'        => get_the_title( $post ),
				'permalink'    => get_permalink( $post ),
				'excerpt'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
				'image'        => get_the_post_thumbnail_url( $post, 'medium_large' ),
				'price'        => get_post_meta( $post->ID, self::PREFIX . 'price', true ),
				'price_suffix' => get_post_meta( $post->ID, self::PREFIX . 'price_suffix', true ),
				'icon'         => get_post_meta( $post->ID, self::PREFIX . 'icon', true ),
				'highlight'    => (bool) get_post_meta( $post->ID, self::PREFIX . 'highlight', true ),
				'features'     => array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $features ) ) ) ),
			);
		}

		return rest_ensure_response( $items );
	}

	/**
	 * GET /testimonials.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_testimonials( $request ) {
		$meta_query = array(
			array(
				'key'   => self::PREFIX . 'approved',
				'value' => '1',
			),
		);
		if ( ! empty( $request['featured'] ) ) {
			$meta_query[] = array(
				'key'   => self::PREFIX . 'featured',
				'value' => '1',
			);
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_testimonial',
				'post_status'    => 'publish',
				'posts_per_page' => min( 50, max( 1, (int) $request['count'] ) ),
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		$items = array();
		foreach ( $query->posts as $post ) {
			$items[] = array(
				'id'          => $post->ID,
				'rating'      => max( 1, min( 5, (int) get_post_meta( $post->ID, self::PREFIX . 'rating', true ) ) ),
				'author_name' => get_post_meta( $post->ID, self::PREFIX . 'author_name', true ),
				'author_role' => get_post_meta( $post->ID, self::PREFIX . 'author_role', true ),
				'featured'    => (bool) get_post_meta( $post->ID, self::PREFIX . 'featured', true ),
				'video_url'   => get_post_meta( $post->ID, self::PREFIX . 'video_url', true ),
				'avatar'      => get_the_post_thumbnail_url( $post, 'thumbnail' ),
				'content'     => wp_strip_all_tags( $post->post_content ),
			);
		}

		return rest_ensure_response( $items );
	}

	/**
	 * GET /gallery.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_gallery( $request ) {
		$args = array(
			'post_type'      => 'dfcc_gallery',
			'post_status'    => 'publish',
			'posts_per_page' => min( 100, max( 1, (int) $request['count'] ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		if ( '' !== $request['album'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'dfcc_gallery_cat',
					'field'    => 'slug',
					'terms'    => sanitize_title( $request['album'] ),
				),
			);
		}

		$query = new WP_Query( $args );
		$items = array();

		foreach ( $query->posts as $post ) {
			$terms = wp_get_post_terms( $post->ID, 'dfcc_gallery_cat', array( 'fields' => 'slugs' ) );
			$items[] = array(
				'id'         => $post->ID,
				'title'      => get_the_title( $post ),
				'media_type' => get_post_meta( $post->ID, self::PREFIX . 'media_type', true ) ? get_post_meta( $post->ID, self::PREFIX . 'media_type', true ) : 'image',
				'video_url'  => get_post_meta( $post->ID, self::PREFIX . 'video_url', true ),
				'thumbnail'  => get_the_post_thumbnail_url( $post, 'medium_large' ),
				'full'       => get_the_post_thumbnail_url( $post, 'large' ),
				'albums'     => is_wp_error( $terms ) ? array() : $terms,
			);
		}

		return rest_ensure_response( $items );
	}

	/* ---------------------------------------------------------------------
	 * Booking submission
	 * ------------------------------------------------------------------- */

	/**
	 * POST /bookings — create a pending booking from a public submission.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_booking( $request ) {
		// Required fields.
		$required = array( 'owner_name', 'phone', 'dog_name', 'arrival_date', 'departure_date' );

		$data = array(
			'owner_name'           => sanitize_text_field( (string) $request->get_param( 'owner_name' ) ),
			'phone'                => sanitize_text_field( (string) $request->get_param( 'phone' ) ),
			'whatsapp'             => sanitize_text_field( (string) $request->get_param( 'whatsapp' ) ),
			'email'                => sanitize_email( (string) $request->get_param( 'email' ) ),
			'dog_name'             => sanitize_text_field( (string) $request->get_param( 'dog_name' ) ),
			'breed'                => sanitize_text_field( (string) $request->get_param( 'breed' ) ),
			'age'                  => sanitize_text_field( (string) $request->get_param( 'age' ) ),
			'gender'               => sanitize_text_field( (string) $request->get_param( 'gender' ) ),
			'arrival_date'         => $this->sanitize_date( (string) $request->get_param( 'arrival_date' ) ),
			'departure_date'       => $this->sanitize_date( (string) $request->get_param( 'departure_date' ) ),
			'special_instructions' => sanitize_textarea_field( (string) $request->get_param( 'special_instructions' ) ),
		);

		// Validate required.
		$missing = array();
		foreach ( $required as $field ) {
			if ( '' === $data[ $field ] ) {
				$missing[] = $field;
			}
		}
		if ( $missing ) {
			return new WP_Error(
				'dfcc_missing_fields',
				__( 'Please fill in all required fields.', 'dog-father-control-center' ),
				array(
					'status' => 400,
					'fields' => $missing,
				)
			);
		}

		// Validate email if provided.
		if ( '' !== (string) $request->get_param( 'email' ) && '' === $data['email'] ) {
			return new WP_Error(
				'dfcc_invalid_email',
				__( 'Please provide a valid email address.', 'dog-father-control-center' ),
				array( 'status' => 400 )
			);
		}

		// Normalise gender to the bookings whitelist.
		$data['gender'] = in_array( $data['gender'], array( 'Male', 'Female' ), true ) ? $data['gender'] : '';

		// Build a descriptive title.
		$title = sprintf(
			/* translators: 1: dog name, 2: owner name. */
			__( '%1$s — %2$s', 'dog-father-control-center' ),
			$data['dog_name'],
			$data['owner_name']
		);

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'dfcc_booking',
				'post_status' => 'pending',
				'post_title'  => $title,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error(
				'dfcc_create_failed',
				__( 'Could not save your booking. Please try again.', 'dog-father-control-center' ),
				array( 'status' => 500 )
			);
		}

		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, self::PREFIX . $key, $value );
		}
		// Workflow status (matches DFCC_Bookings).
		update_post_meta( $post_id, self::PREFIX . 'status', 'new' );
		update_post_meta( $post_id, self::PREFIX . 'source', 'website' );

		/**
		 * Fires after a public booking has been created via REST.
		 *
		 * @param int   $post_id New booking id.
		 * @param array $data    Sanitized submission data.
		 */
		do_action( 'dfcc_booking_created', $post_id, $data );

		return rest_ensure_response(
			array(
				'success' => true,
				'id'      => $post_id,
				'message' => __( 'Booking request received.', 'dog-father-control-center' ),
			)
		);
	}

	/**
	 * Validate a YYYY-MM-DD date string, returning '' when invalid.
	 *
	 * @param string $raw Raw value.
	 * @return string
	 */
	private function sanitize_date( $raw ) {
		$raw = sanitize_text_field( $raw );
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
	 * Booking form shortcode
	 * ------------------------------------------------------------------- */

	/**
	 * Render the [dfcc_booking_form] conversion form.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function booking_form_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'title' => __( 'Book Your Stay', 'dog-father-control-center' ),
			),
			$atts,
			'dfcc_booking_form'
		);

		DFCC_Frontend_Assets::need();

		ob_start();
		?>
		<div class="dfcc-booking-form-wrap">
			<form class="dfcc-booking-form" novalidate>
				<?php if ( '' !== $atts['title'] ) : ?>
					<h3 class="dfcc-form-title"><?php echo esc_html( $atts['title'] ); ?></h3>
				<?php endif; ?>

				<div class="dfcc-form-grid">
					<p class="dfcc-field dfcc-field-required">
						<label for="dfcc-bf-owner_name"><?php esc_html_e( 'Your Name', 'dog-father-control-center' ); ?> <span aria-hidden="true">*</span></label>
						<input type="text" id="dfcc-bf-owner_name" name="owner_name" required autocomplete="name" />
					</p>
					<p class="dfcc-field dfcc-field-required">
						<label for="dfcc-bf-phone"><?php esc_html_e( 'Phone', 'dog-father-control-center' ); ?> <span aria-hidden="true">*</span></label>
						<input type="tel" id="dfcc-bf-phone" name="phone" required autocomplete="tel" />
					</p>
					<p class="dfcc-field">
						<label for="dfcc-bf-whatsapp"><?php esc_html_e( 'WhatsApp', 'dog-father-control-center' ); ?></label>
						<input type="tel" id="dfcc-bf-whatsapp" name="whatsapp" />
					</p>
					<p class="dfcc-field">
						<label for="dfcc-bf-email"><?php esc_html_e( 'Email', 'dog-father-control-center' ); ?></label>
						<input type="email" id="dfcc-bf-email" name="email" autocomplete="email" />
					</p>
					<p class="dfcc-field dfcc-field-required">
						<label for="dfcc-bf-dog_name"><?php esc_html_e( 'Dog Name', 'dog-father-control-center' ); ?> <span aria-hidden="true">*</span></label>
						<input type="text" id="dfcc-bf-dog_name" name="dog_name" required />
					</p>
					<p class="dfcc-field">
						<label for="dfcc-bf-breed"><?php esc_html_e( 'Breed', 'dog-father-control-center' ); ?></label>
						<input type="text" id="dfcc-bf-breed" name="breed" />
					</p>
					<p class="dfcc-field">
						<label for="dfcc-bf-age"><?php esc_html_e( 'Age', 'dog-father-control-center' ); ?></label>
						<input type="text" id="dfcc-bf-age" name="age" />
					</p>
					<p class="dfcc-field">
						<label for="dfcc-bf-gender"><?php esc_html_e( 'Gender', 'dog-father-control-center' ); ?></label>
						<select id="dfcc-bf-gender" name="gender">
							<option value=""><?php esc_html_e( '— Select —', 'dog-father-control-center' ); ?></option>
							<option value="Male"><?php esc_html_e( 'Male', 'dog-father-control-center' ); ?></option>
							<option value="Female"><?php esc_html_e( 'Female', 'dog-father-control-center' ); ?></option>
						</select>
					</p>
					<p class="dfcc-field dfcc-field-required">
						<label for="dfcc-bf-arrival_date"><?php esc_html_e( 'Arrival Date', 'dog-father-control-center' ); ?> <span aria-hidden="true">*</span></label>
						<input type="date" id="dfcc-bf-arrival_date" name="arrival_date" required />
					</p>
					<p class="dfcc-field dfcc-field-required">
						<label for="dfcc-bf-departure_date"><?php esc_html_e( 'Departure Date', 'dog-father-control-center' ); ?> <span aria-hidden="true">*</span></label>
						<input type="date" id="dfcc-bf-departure_date" name="departure_date" required />
					</p>
				</div>

				<p class="dfcc-field dfcc-field-full">
					<label for="dfcc-bf-special_instructions"><?php esc_html_e( 'Special Instructions', 'dog-father-control-center' ); ?></label>
					<textarea id="dfcc-bf-special_instructions" name="special_instructions" rows="4"></textarea>
				</p>

				<?php // Simple honeypot for bots. ?>
				<p class="dfcc-hp" aria-hidden="true" style="position:absolute;left:-9999px;">
					<label for="dfcc-bf-website"><?php esc_html_e( 'Leave this field empty', 'dog-father-control-center' ); ?></label>
					<input type="text" id="dfcc-bf-website" name="dfcc_hp_website" tabindex="-1" autocomplete="off" />
				</p>

				<div class="dfcc-form-message" role="status" aria-live="polite"></div>

				<p class="dfcc-form-actions">
					<button type="submit" class="dfcc-btn dfcc-btn-primary dfcc-submit"><?php esc_html_e( 'Request Booking', 'dog-father-control-center' ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_REST_API() );
	}
);
