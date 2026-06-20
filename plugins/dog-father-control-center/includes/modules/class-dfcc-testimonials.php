<?php
/**
 * Testimonials module.
 *
 * Adds rating / author / approval meta to the dfcc_testimonial custom post type
 * (registered in DFCC_Post_Types) and exposes the [dfcc_testimonials] shortcode
 * which renders approved testimonials as a grid or slider.
 *
 * All testimonial meta is stored with the _dfcc_ prefix.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Testimonials.
 */
class DFCC_Testimonials extends DFCC_Module {

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	const PREFIX = '_dfcc_';

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'dfcc_save_testimonial';

	/**
	 * Nonce field.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'dfcc_testimonial_nonce';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'testimonials';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Testimonials', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'add_meta_boxes_dfcc_testimonial', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_dfcc_testimonial', array( $this, 'save_meta' ), 10, 2 );

		add_filter( 'manage_dfcc_testimonial_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_dfcc_testimonial_posts_custom_column', array( $this, 'render_column' ), 10, 2 );

		add_shortcode( 'dfcc_testimonials', array( $this, 'shortcode' ) );
		add_shortcode( 'dfcc_satisfaction', array( $this, 'satisfaction_shortcode' ) );
	}

	/**
	 * [dfcc_satisfaction] — a customer-satisfaction summary band computed from
	 * approved testimonials (average rating, review count, satisfaction %).
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function satisfaction_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'title' => __( 'Customer Satisfaction', 'dog-father-control-center' ) ), $atts, 'dfcc_satisfaction' );

		$reviews = get_posts(
			array(
				'post_type'        => 'dfcc_testimonial',
				'post_status'      => 'publish',
				'numberposts'      => 500,
				'fields'           => 'ids',
				'suppress_filters' => true,
				'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => self::PREFIX . 'approved',
						'value' => '1',
					),
				),
			)
		);

		$count = count( $reviews );
		if ( 0 === $count ) {
			return '';
		}

		$sum     = 0;
		$happy   = 0;
		foreach ( $reviews as $rid ) {
			$r    = max( 1, min( 5, (int) self::get_meta( $rid, 'rating', 5 ) ) );
			$sum += $r;
			if ( $r >= 4 ) {
				$happy++;
			}
		}
		$avg     = round( $sum / $count, 1 );
		$percent = (int) round( $happy / $count * 100 );

		DFCC_Frontend_Assets::need();

		$stats = array(
			array( number_format_i18n( $avg, 1 ) . ' / 5', __( 'Average rating', 'dog-father-control-center' ) ),
			array( $percent . '%', __( 'Would recommend us', 'dog-father-control-center' ) ),
			array( number_format_i18n( $count ) . '+', __( 'Happy reviews', 'dog-father-control-center' ) ),
		);

		ob_start();
		?>
		<div class="dfcc-satisfaction">
			<?php if ( '' !== $atts['title'] ) : ?>
				<h2 class="dfcc-satisfaction-title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>
			<div class="dfcc-satisfaction-grid">
				<?php foreach ( $stats as $s ) : ?>
					<div class="dfcc-satisfaction-stat">
						<span class="dfcc-satisfaction-num"><?php echo esc_html( $s[0] ); ?></span>
						<span class="dfcc-satisfaction-label"><?php echo esc_html( $s[1] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Read a testimonial meta value.
	 *
	 * @param int    $post_id Testimonial id.
	 * @param string $key     Field key without the prefix.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, $default = '' ) {
		$value = get_post_meta( $post_id, self::PREFIX . $key, true );
		return ( '' === $value || false === $value ) ? $default : $value;
	}

	/* ---------------------------------------------------------------------
	 * Meta box
	 * ------------------------------------------------------------------- */

	/**
	 * Register the testimonial meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dfcc-testimonial-details',
			__( 'Testimonial Details', 'dog-father-control-center' ),
			array( $this, 'render_box' ),
			'dfcc_testimonial',
			'side',
			'high'
		);
	}

	/**
	 * Render the testimonial meta box.
	 *
	 * @param WP_Post $post Current testimonial.
	 * @return void
	 */
	public function render_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$new_post    = ( 'auto-draft' === $post->post_status );
		$rating      = (int) self::get_meta( $post->ID, 'rating', 5 );
		$author_name = self::get_meta( $post->ID, 'author_name' );
		$author_role = self::get_meta( $post->ID, 'author_role' );
		$featured    = self::get_meta( $post->ID, 'featured' );
		$video_url   = self::get_meta( $post->ID, 'video_url' );
		// New testimonials default to approved.
		$approved = $new_post ? '1' : self::get_meta( $post->ID, 'approved' );
		?>
		<p>
			<label for="dfcc-rating"><strong><?php esc_html_e( 'Rating', 'dog-father-control-center' ); ?></strong></label><br />
			<select class="widefat" id="dfcc-rating" name="dfcc_rating">
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>>
						<?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) . ' (' . $i . ')' ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label for="dfcc-author_name"><strong><?php esc_html_e( 'Author Name', 'dog-father-control-center' ); ?></strong></label><br />
			<input type="text" class="widefat" id="dfcc-author_name" name="dfcc_author_name" value="<?php echo esc_attr( $author_name ); ?>" />
		</p>
		<p>
			<label for="dfcc-author_role"><strong><?php esc_html_e( 'Author Role', 'dog-father-control-center' ); ?></strong></label><br />
			<input type="text" class="widefat" id="dfcc-author_role" name="dfcc_author_role" value="<?php echo esc_attr( $author_role ); ?>" placeholder="<?php esc_attr_e( 'Dog parent', 'dog-father-control-center' ); ?>" />
		</p>
		<p>
			<label for="dfcc-video_url"><strong><?php esc_html_e( 'Video URL (optional)', 'dog-father-control-center' ); ?></strong></label><br />
			<input type="url" class="widefat" id="dfcc-video_url" name="dfcc_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://" />
		</p>
		<p>
			<label for="dfcc-featured">
				<input type="checkbox" id="dfcc-featured" name="dfcc_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<?php esc_html_e( 'Featured', 'dog-father-control-center' ); ?>
			</label>
		</p>
		<p>
			<label for="dfcc-approved">
				<input type="checkbox" id="dfcc-approved" name="dfcc_approved" value="1" <?php checked( $approved, '1' ); ?> />
				<?php esc_html_e( 'Approved (visible on site)', 'dog-father-control-center' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Persist testimonial meta.
	 *
	 * @param int     $post_id Testimonial id.
	 * @param WP_Post $post    Testimonial object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Rating (1-5).
		$rating = isset( $_POST['dfcc_rating'] ) ? (int) $_POST['dfcc_rating'] : 5; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cast to int.
		$rating = max( 1, min( 5, $rating ) );
		update_post_meta( $post_id, self::PREFIX . 'rating', $rating );

		// Text fields.
		foreach ( array( 'author_name', 'author_role' ) as $field ) {
			$value = isset( $_POST[ 'dfcc_' . $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_' . $field ] ) ) : '';
			update_post_meta( $post_id, self::PREFIX . $field, $value );
		}

		// Video URL.
		$video_url = isset( $_POST['dfcc_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['dfcc_video_url'] ) ) : '';
		update_post_meta( $post_id, self::PREFIX . 'video_url', $video_url );

		// Checkboxes.
		update_post_meta( $post_id, self::PREFIX . 'featured', isset( $_POST['dfcc_featured'] ) ? '1' : '' );
		update_post_meta( $post_id, self::PREFIX . 'approved', isset( $_POST['dfcc_approved'] ) ? '1' : '' );
	}

	/* ---------------------------------------------------------------------
	 * Admin columns
	 * ------------------------------------------------------------------- */

	/**
	 * Add rating + approved columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['dfcc_rating']   = __( 'Rating', 'dog-father-control-center' );
				$new['dfcc_approved'] = __( 'Approved', 'dog-father-control-center' );
			}
		}
		return $new;
	}

	/**
	 * Render the custom columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Testimonial id.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		if ( 'dfcc_rating' === $column ) {
			$rating = max( 1, min( 5, (int) self::get_meta( $post_id, 'rating', 5 ) ) );
			echo '<span class="dfcc-stars" aria-label="' . esc_attr( $rating . '/5' ) . '">' . esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ) . '</span>';
		} elseif ( 'dfcc_approved' === $column ) {
			if ( self::get_meta( $post_id, 'approved' ) ) {
				echo '<span style="color:#46b450;font-weight:600;">' . esc_html__( 'Approved', 'dog-father-control-center' ) . '</span>';
			} else {
				echo '<span style="color:#dc3232;font-weight:600;">' . esc_html__( 'Pending', 'dog-father-control-center' ) . '</span>';
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Shortcode
	 * ------------------------------------------------------------------- */

	/**
	 * Render the [dfcc_testimonials] block.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'count'    => 6,
				'featured' => 'no',
				'layout'   => 'grid',
			),
			$atts,
			'dfcc_testimonials'
		);

		$layout   = ( 'slider' === strtolower( (string) $atts['layout'] ) ) ? 'slider' : 'grid';
		$featured = in_array( strtolower( (string) $atts['featured'] ), array( 'yes', 'true', '1', 'on' ), true );

		$meta_query = array(
			array(
				'key'   => self::PREFIX . 'approved',
				'value' => '1',
			),
		);
		if ( $featured ) {
			$meta_query[] = array(
				'key'   => self::PREFIX . 'featured',
				'value' => '1',
			);
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_testimonial',
				'post_status'    => 'publish',
				'posts_per_page' => (int) $atts['count'],
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
				'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		DFCC_Frontend_Assets::need();

		ob_start();
		?>
		<div class="dfcc-testimonials dfcc-testimonials-<?php echo esc_attr( $layout ); ?>"<?php echo 'slider' === $layout ? ' data-slider="1"' : ''; ?>>
			<div class="dfcc-testimonials-track">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$id      = get_the_ID();
					$rating  = max( 1, min( 5, (int) self::get_meta( $id, 'rating', 5 ) ) );
					$name    = self::get_meta( $id, 'author_name', get_the_title() );
					$role    = self::get_meta( $id, 'author_role' );
					$avatar  = get_the_post_thumbnail_url( $id, 'thumbnail' );
					$content = get_the_content();
					?>
					<figure class="dfcc-testimonial-card">
						<div class="dfcc-stars" aria-label="<?php echo esc_attr( $rating . ' / 5' ); ?>">
							<?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?>
						</div>
						<blockquote class="dfcc-testimonial-text">
							<?php echo wp_kses_post( wpautop( $content ) ); ?>
						</blockquote>
						<figcaption class="dfcc-testimonial-author">
							<?php if ( $avatar ) : ?>
								<img class="dfcc-testimonial-avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" width="56" height="56" />
							<?php endif; ?>
							<span class="dfcc-testimonial-meta">
								<span class="dfcc-testimonial-name"><?php echo esc_html( $name ); ?></span>
								<?php if ( $role ) : ?>
									<span class="dfcc-testimonial-role"><?php echo esc_html( $role ); ?></span>
								<?php endif; ?>
							</span>
						</figcaption>
					</figure>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php if ( 'slider' === $layout ) : ?>
				<div class="dfcc-slider-nav">
					<button type="button" class="dfcc-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'dog-father-control-center' ); ?>">‹</button>
					<button type="button" class="dfcc-slider-next" aria-label="<?php esc_attr_e( 'Next', 'dog-father-control-center' ); ?>">›</button>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Testimonials() );
	}
);
