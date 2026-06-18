<?php
/**
 * Customer Portal module.
 *
 * Provides a premium, dark-themed front-end portal driven by shortcodes so the
 * site owner can drop it onto any Elementor (or block) page:
 *
 *   [dfcc_portal_login]     — login form / greeting.
 *   [dfcc_portal_dashboard] — tabbed dashboard of stays, dogs, invoices, etc.
 *   [dfcc_portal_dogs]      — the customer's dog profiles.
 *
 * Bookings are linked to the logged-in customer by matching their account email
 * against the booking's _dfcc_email meta. Dogs match _dfcc_owner_email.
 *
 * The portal stylesheet is only enqueued on pages that actually contain one of
 * these shortcodes.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Customer_Portal.
 */
class DFCC_Customer_Portal extends DFCC_Module {

	/**
	 * Whether a portal shortcode was rendered on the current request.
	 *
	 * @var bool
	 */
	private $needs_assets = false;

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'customer-portal';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Customer Portal', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_shortcode( 'dfcc_portal_login', array( $this, 'shortcode_login' ) );
		add_shortcode( 'dfcc_portal_dashboard', array( $this, 'shortcode_dashboard' ) );
		add_shortcode( 'dfcc_portal_dogs', array( $this, 'shortcode_dogs' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_in_footer' ) );
	}

	/**
	 * Register (but do not yet print) the portal stylesheet.
	 *
	 * Enqueued conditionally: either here when the post content contains a
	 * portal shortcode, or as a fallback in the footer if a shortcode fired
	 * from a builder template that bypasses has_shortcode().
	 *
	 * @return void
	 */
	public function maybe_enqueue() {
		wp_register_style(
			'dfcc-portal',
			DFCC_PLUGIN_URL . 'public/css/portal.css',
			array(),
			DFCC_VERSION
		);

		$post = get_post();
		if ( $post instanceof WP_Post ) {
			$content = $post->post_content;
			if (
				has_shortcode( $content, 'dfcc_portal_login' )
				|| has_shortcode( $content, 'dfcc_portal_dashboard' )
				|| has_shortcode( $content, 'dfcc_portal_dogs' )
			) {
				wp_enqueue_style( 'dfcc-portal' );
			}
		}
	}

	/**
	 * Fallback enqueue for builder pages: if a shortcode rendered, ensure the
	 * stylesheet is printed in the footer.
	 *
	 * @return void
	 */
	public function enqueue_in_footer() {
		if ( $this->needs_assets && ! wp_style_is( 'dfcc-portal', 'done' ) ) {
			wp_print_styles( 'dfcc-portal' );
		}
	}

	/**
	 * Resolve the current logged-in user's email, or empty string.
	 *
	 * @return string
	 */
	private function current_email() {
		$user = wp_get_current_user();
		return ( $user && $user->exists() ) ? (string) $user->user_email : '';
	}

	/**
	 * Render the "please log in" prompt.
	 *
	 * @return string
	 */
	private function login_prompt() {
		$form = wp_login_form(
			array(
				'echo'     => false,
				'redirect' => esc_url( ( is_ssl() ? 'https://' : 'http://' ) . wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) . wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitized
			)
		);

		return sprintf(
			'<div class="dfcc-portal dfcc-portal--gate"><div class="dfcc-card"><h2 class="dfcc-card__title">%s</h2><p class="dfcc-muted">%s</p>%s</div></div>',
			esc_html__( 'Member Login', 'dog-father-control-center' ),
			esc_html__( 'Please log in to access your portal.', 'dog-father-control-center' ),
			$form // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated form.
		);
	}

	/**
	 * [dfcc_portal_login] shortcode.
	 *
	 * @return string
	 */
	public function shortcode_login() {
		$this->needs_assets = true;
		wp_enqueue_style( 'dfcc-portal' );

		if ( ! is_user_logged_in() ) {
			return $this->login_prompt();
		}

		$user = wp_get_current_user();
		$name = $user->display_name ? $user->display_name : $user->user_login;

		return sprintf(
			'<div class="dfcc-portal"><div class="dfcc-card dfcc-greeting"><div><span class="dfcc-muted">%1$s</span><h2 class="dfcc-card__title">%2$s</h2></div><a class="dfcc-btn dfcc-btn--ghost" href="%3$s">%4$s</a></div></div>',
			esc_html__( 'Welcome back', 'dog-father-control-center' ),
			esc_html( $name ),
			esc_url( wp_logout_url( home_url() ) ),
			esc_html__( 'Log out', 'dog-father-control-center' )
		);
	}

	/**
	 * [dfcc_portal_dashboard] shortcode.
	 *
	 * @return string
	 */
	public function shortcode_dashboard() {
		$this->needs_assets = true;
		wp_enqueue_style( 'dfcc-portal' );

		if ( ! is_user_logged_in() ) {
			return $this->login_prompt();
		}

		$email = $this->current_email();
		$now   = current_time( 'Y-m-d' );

		$bookings = $this->get_bookings( $email );
		$upcoming = array();
		$past     = array();
		foreach ( $bookings as $booking ) {
			$check_in = get_post_meta( $booking->ID, '_dfcc_arrival_date', true );
			if ( $check_in && $check_in >= $now ) {
				$upcoming[] = $booking;
			} else {
				$past[] = $booking;
			}
		}

		$dogs = $this->get_dogs( $email );

		$tabs = array(
			'stays'         => __( 'Upcoming Stays', 'dog-father-control-center' ),
			'past'          => __( 'Past Stays', 'dog-father-control-center' ),
			'dogs'          => __( 'My Dogs', 'dog-father-control-center' ),
			'vaccinations'  => __( 'Vaccination Records', 'dog-father-control-center' ),
			'invoices'      => __( 'Invoices', 'dog-father-control-center' ),
			'photos'        => __( 'Photos', 'dog-father-control-center' ),
			'messages'      => __( 'Messages', 'dog-father-control-center' ),
			'notifications' => __( 'Notifications', 'dog-father-control-center' ),
			'account'       => __( 'Account Settings', 'dog-father-control-center' ),
		);

		ob_start();
		?>
		<div class="dfcc-portal dfcc-dashboard">
			<nav class="dfcc-tabs" role="tablist">
				<?php
				$first = true;
				foreach ( $tabs as $key => $label ) {
					printf(
						'<button type="button" class="dfcc-tab%1$s" data-dfcc-tab="%2$s" aria-selected="%3$s">%4$s</button>',
						$first ? ' is-active' : '',
						esc_attr( $key ),
						$first ? 'true' : 'false',
						esc_html( $label )
					);
					$first = false;
				}
				?>
			</nav>

			<section class="dfcc-panel is-active" data-dfcc-pane="stays" role="tabpanel">
				<?php echo $this->render_stays( $upcoming, __( 'No upcoming stays yet.', 'dog-father-control-center' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="past" role="tabpanel">
				<?php echo $this->render_stays( $past, __( 'No past stays on record.', 'dog-father-control-center' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="dogs" role="tabpanel">
				<?php echo $this->render_dog_cards( $dogs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="vaccinations" role="tabpanel">
				<?php echo $this->render_vaccinations( $dogs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="invoices" role="tabpanel">
				<?php echo $this->render_invoices( $bookings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="photos" role="tabpanel">
				<?php echo $this->render_photos( $dogs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="messages" role="tabpanel">
				<?php echo $this->empty_state( __( 'You have no messages.', 'dog-father-control-center' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="notifications" role="tabpanel">
				<?php echo $this->empty_state( __( 'No notifications right now.', 'dog-father-control-center' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<section class="dfcc-panel" data-dfcc-pane="account" role="tabpanel">
				<?php echo $this->render_account(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>
		</div>
		<script type="text/javascript">
		( function () {
			var root = document.currentScript.previousElementSibling;
			if ( ! root ) { return; }
			var tabs = root.querySelectorAll( '.dfcc-tab' );
			var panes = root.querySelectorAll( '.dfcc-panel' );
			Array.prototype.forEach.call( tabs, function ( tab ) {
				tab.addEventListener( 'click', function () {
					var target = tab.getAttribute( 'data-dfcc-tab' );
					Array.prototype.forEach.call( tabs, function ( t ) {
						var on = ( t === tab );
						t.classList.toggle( 'is-active', on );
						t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
					} );
					Array.prototype.forEach.call( panes, function ( p ) {
						p.classList.toggle( 'is-active', p.getAttribute( 'data-dfcc-pane' ) === target );
					} );
				} );
			} );
		} )();
		</script>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * [dfcc_portal_dogs] shortcode.
	 *
	 * @return string
	 */
	public function shortcode_dogs() {
		$this->needs_assets = true;
		wp_enqueue_style( 'dfcc-portal' );

		if ( ! is_user_logged_in() ) {
			return $this->login_prompt();
		}

		$dogs = $this->get_dogs( $this->current_email() );

		return '<div class="dfcc-portal">' . $this->render_dog_cards( $dogs ) . '</div>';
	}

	/**
	 * Query bookings belonging to an email address.
	 *
	 * @param string $email Customer email.
	 * @return WP_Post[]
	 */
	private function get_bookings( $email ) {
		if ( ! $email ) {
			return array();
		}
		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_booking',
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_dfcc_email',
						'value'   => $email,
						'compare' => '=',
					),
				),
			)
		);
		return $query->posts;
	}

	/**
	 * Query dog profiles belonging to an email address.
	 *
	 * @param string $email Customer email.
	 * @return WP_Post[]
	 */
	private function get_dogs( $email ) {
		if ( ! $email ) {
			return array();
		}
		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_dog',
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_dfcc_owner_email',
						'value'   => $email,
						'compare' => '=',
					),
				),
			)
		);
		return $query->posts;
	}

	/**
	 * Render a simple empty-state card.
	 *
	 * @param string $message Message text.
	 * @return string
	 */
	private function empty_state( $message ) {
		return sprintf(
			'<div class="dfcc-empty"><span class="dfcc-empty__icon" aria-hidden="true">&#9788;</span><p>%s</p></div>',
			esc_html( $message )
		);
	}

	/**
	 * Render a list of stay (booking) cards.
	 *
	 * @param WP_Post[] $bookings Bookings.
	 * @param string    $empty    Empty-state message.
	 * @return string
	 */
	private function render_stays( $bookings, $empty ) {
		if ( empty( $bookings ) ) {
			return $this->empty_state( $empty );
		}

		$out = '<div class="dfcc-grid">';
		foreach ( $bookings as $booking ) {
			$check_in  = get_post_meta( $booking->ID, '_dfcc_arrival_date', true );
			$check_out = get_post_meta( $booking->ID, '_dfcc_departure_date', true );
			$status    = get_post_meta( $booking->ID, '_dfcc_status', true );
			$dog       = get_post_meta( $booking->ID, '_dfcc_dog_name', true );

			$out .= '<article class="dfcc-card dfcc-stay">';
			$out .= sprintf( '<h3 class="dfcc-card__title">%s</h3>', esc_html( get_the_title( $booking ) ) );
			if ( $dog ) {
				$out .= sprintf( '<p class="dfcc-stay__dog">%s</p>', esc_html( $dog ) );
			}
			$out .= '<ul class="dfcc-meta">';
			if ( $check_in ) {
				$out .= sprintf(
					'<li><span>%s</span><strong>%s</strong></li>',
					esc_html__( 'Check-in', 'dog-father-control-center' ),
					esc_html( $this->fmt_date( $check_in ) )
				);
			}
			if ( $check_out ) {
				$out .= sprintf(
					'<li><span>%s</span><strong>%s</strong></li>',
					esc_html__( 'Check-out', 'dog-father-control-center' ),
					esc_html( $this->fmt_date( $check_out ) )
				);
			}
			$out .= '</ul>';
			if ( $status ) {
				$out .= sprintf( '<span class="dfcc-badge">%s</span>', esc_html( ucfirst( $status ) ) );
			}
			$out .= '</article>';
		}
		$out .= '</div>';
		return $out;
	}

	/**
	 * Render dog profile cards.
	 *
	 * @param WP_Post[] $dogs Dogs.
	 * @return string
	 */
	private function render_dog_cards( $dogs ) {
		if ( empty( $dogs ) ) {
			return $this->empty_state( __( 'No dog profiles found for your account.', 'dog-father-control-center' ) );
		}

		$out = '<div class="dfcc-grid">';
		foreach ( $dogs as $dog ) {
			$breed  = get_post_meta( $dog->ID, '_dfcc_breed', true );
			$age    = get_post_meta( $dog->ID, '_dfcc_age', true );
			$gender = get_post_meta( $dog->ID, '_dfcc_gender', true );
			$thumb  = get_the_post_thumbnail( $dog->ID, 'medium', array( 'class' => 'dfcc-dog__photo', 'loading' => 'lazy' ) );

			$out .= '<article class="dfcc-card dfcc-dog">';
			if ( $thumb ) {
				$out .= '<div class="dfcc-dog__media">' . $thumb . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
			}
			$out .= sprintf( '<h3 class="dfcc-card__title">%s</h3>', esc_html( get_the_title( $dog ) ) );
			$out .= '<ul class="dfcc-meta">';
			if ( $breed ) {
				$out .= sprintf( '<li><span>%s</span><strong>%s</strong></li>', esc_html__( 'Breed', 'dog-father-control-center' ), esc_html( $breed ) );
			}
			if ( $age ) {
				$out .= sprintf( '<li><span>%s</span><strong>%s</strong></li>', esc_html__( 'Age', 'dog-father-control-center' ), esc_html( $age ) );
			}
			if ( $gender ) {
				$out .= sprintf( '<li><span>%s</span><strong>%s</strong></li>', esc_html__( 'Gender', 'dog-father-control-center' ), esc_html( $gender ) );
			}
			$out .= '</ul>';
			$out .= '</article>';
		}
		$out .= '</div>';
		return $out;
	}

	/**
	 * Render vaccination records per dog.
	 *
	 * @param WP_Post[] $dogs Dogs.
	 * @return string
	 */
	private function render_vaccinations( $dogs ) {
		$rows = array();
		foreach ( $dogs as $dog ) {
			$records = get_post_meta( $dog->ID, '_dfcc_vaccination_records', true );
			if ( $records ) {
				$rows[] = sprintf(
					'<article class="dfcc-card"><h3 class="dfcc-card__title">%s</h3><p class="dfcc-prewrap">%s</p></article>',
					esc_html( get_the_title( $dog ) ),
					nl2br( esc_html( $records ) )
				);
			}
		}
		if ( empty( $rows ) ) {
			return $this->empty_state( __( 'No vaccination records uploaded yet.', 'dog-father-control-center' ) );
		}
		return '<div class="dfcc-grid">' . implode( '', $rows ) . '</div>';
	}

	/**
	 * Render invoices derived from bookings.
	 *
	 * @param WP_Post[] $bookings Bookings.
	 * @return string
	 */
	private function render_invoices( $bookings ) {
		$rows = array();
		foreach ( $bookings as $booking ) {
			$total = get_post_meta( $booking->ID, '_dfcc_total', true );
			if ( '' === $total || null === $total ) {
				continue;
			}
			$status = get_post_meta( $booking->ID, '_dfcc_payment_status', true );
			$rows[] = sprintf(
				'<tr><td>%s</td><td>%s</td><td class="dfcc-num">%s</td><td><span class="dfcc-badge">%s</span></td></tr>',
				esc_html( get_the_title( $booking ) ),
				esc_html( $this->fmt_date( get_the_date( 'Y-m-d', $booking ) ) ),
				dfcc_money( $total ),
				esc_html( $status ? ucfirst( $status ) : __( 'Pending', 'dog-father-control-center' ) )
			);
		}
		if ( empty( $rows ) ) {
			return $this->empty_state( __( 'No invoices available.', 'dog-father-control-center' ) );
		}
		return sprintf(
			'<div class="dfcc-card"><table class="dfcc-table"><thead><tr><th>%s</th><th>%s</th><th class="dfcc-num">%s</th><th>%s</th></tr></thead><tbody>%s</tbody></table></div>',
			esc_html__( 'Booking', 'dog-father-control-center' ),
			esc_html__( 'Date', 'dog-father-control-center' ),
			esc_html__( 'Amount', 'dog-father-control-center' ),
			esc_html__( 'Status', 'dog-father-control-center' ),
			implode( '', $rows )
		);
	}

	/**
	 * Render the photos gallery from dog featured images.
	 *
	 * @param WP_Post[] $dogs Dogs.
	 * @return string
	 */
	private function render_photos( $dogs ) {
		$tiles = array();
		foreach ( $dogs as $dog ) {
			if ( has_post_thumbnail( $dog->ID ) ) {
				$tiles[] = sprintf(
					'<figure class="dfcc-photo">%s<figcaption>%s</figcaption></figure>',
					get_the_post_thumbnail( $dog->ID, 'medium', array( 'loading' => 'lazy' ) ),
					esc_html( get_the_title( $dog ) )
				);
			}
		}
		if ( empty( $tiles ) ) {
			return $this->empty_state( __( 'No photos shared yet.', 'dog-father-control-center' ) );
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- thumbnails are core markup, captions escaped above.
		return '<div class="dfcc-photo-grid">' . implode( '', $tiles ) . '</div>';
	}

	/**
	 * Render the account settings summary.
	 *
	 * @return string
	 */
	private function render_account() {
		$user = wp_get_current_user();
		return sprintf(
			'<div class="dfcc-card"><ul class="dfcc-meta dfcc-meta--stacked">
				<li><span>%1$s</span><strong>%2$s</strong></li>
				<li><span>%3$s</span><strong>%4$s</strong></li>
				<li><span>%5$s</span><strong>%6$s</strong></li>
			</ul><a class="dfcc-btn dfcc-btn--ghost" href="%7$s">%8$s</a></div>',
			esc_html__( 'Name', 'dog-father-control-center' ),
			esc_html( $user->display_name ),
			esc_html__( 'Username', 'dog-father-control-center' ),
			esc_html( $user->user_login ),
			esc_html__( 'Email', 'dog-father-control-center' ),
			esc_html( $user->user_email ),
			esc_url( get_edit_profile_url( $user->ID ) ),
			esc_html__( 'Edit Profile', 'dog-father-control-center' )
		);
	}

	/**
	 * Format a date string for display.
	 *
	 * @param string $value Raw date (Y-m-d or timestamp-ish).
	 * @return string
	 */
	private function fmt_date( $value ) {
		$ts = strtotime( (string) $value );
		if ( ! $ts ) {
			return (string) $value;
		}
		return date_i18n( get_option( 'date_format' ), $ts );
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Customer_Portal() );
	}
);
