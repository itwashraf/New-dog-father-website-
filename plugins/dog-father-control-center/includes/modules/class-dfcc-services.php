<?php
/**
 * Services module.
 *
 * Adds the pricing / feature meta box to the dfcc_service custom post type
 * (registered in DFCC_Post_Types) and exposes the [dfcc_services] shortcode
 * which renders a premium responsive card grid on the front end.
 *
 * All service meta is stored with the _dfcc_ prefix.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Services.
 */
class DFCC_Services extends DFCC_Module {

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
	const NONCE_ACTION = 'dfcc_save_service';

	/**
	 * Nonce field.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'dfcc_service_nonce';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'services';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Services', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'add_meta_boxes_dfcc_service', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_dfcc_service', array( $this, 'save_meta' ), 10, 2 );

		add_filter( 'manage_dfcc_service_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_dfcc_service_posts_custom_column', array( $this, 'render_column' ), 10, 2 );

		add_shortcode( 'dfcc_services', array( $this, 'shortcode' ) );
	}

	/**
	 * Read a service meta value.
	 *
	 * @param int    $post_id Service id.
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
	 * Register the service meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dfcc-service-details',
			__( 'Service Details', 'dog-father-control-center' ),
			array( $this, 'render_box' ),
			'dfcc_service',
			'normal',
			'high'
		);
	}

	/**
	 * Render the service meta box.
	 *
	 * @param WP_Post $post Current service.
	 * @return void
	 */
	public function render_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$price        = self::get_meta( $post->ID, 'price' );
		$price_suffix = self::get_meta( $post->ID, 'price_suffix' );
		$icon         = self::get_meta( $post->ID, 'icon' );
		$features     = self::get_meta( $post->ID, 'features' );
		$highlight    = self::get_meta( $post->ID, 'highlight' );
		?>
		<div class="dfcc-meta-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
			<p>
				<label for="dfcc-price"><strong><?php esc_html_e( 'Price', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="number" step="0.01" min="0" class="widefat" id="dfcc-price" name="dfcc_price" value="<?php echo esc_attr( $price ); ?>" />
			</p>
			<p>
				<label for="dfcc-price_suffix"><strong><?php esc_html_e( 'Price Suffix', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-price_suffix" name="dfcc_price_suffix" value="<?php echo esc_attr( $price_suffix ); ?>" placeholder="<?php esc_attr_e( '/night', 'dog-father-control-center' ); ?>" />
			</p>
			<p>
				<label for="dfcc-icon"><strong><?php esc_html_e( 'Icon', 'dog-father-control-center' ); ?></strong></label><br />
				<input type="text" class="widefat" id="dfcc-icon" name="dfcc_icon" value="<?php echo esc_attr( $icon ); ?>" placeholder="<?php esc_attr_e( 'dashicons-pets or 🐾', 'dog-father-control-center' ); ?>" />
				<span class="description"><?php esc_html_e( 'A dashicon name (e.g. dashicons-pets) or an emoji.', 'dog-father-control-center' ); ?></span>
			</p>
			<p>
				<label><strong><?php esc_html_e( 'Featured', 'dog-father-control-center' ); ?></strong></label><br />
				<label for="dfcc-highlight">
					<input type="checkbox" id="dfcc-highlight" name="dfcc_highlight" value="1" <?php checked( $highlight, '1' ); ?> />
					<?php esc_html_e( 'Mark this service as featured', 'dog-father-control-center' ); ?>
				</label>
			</p>
		</div>
		<p>
			<label for="dfcc-features"><strong><?php esc_html_e( 'Features', 'dog-father-control-center' ); ?></strong></label><br />
			<textarea class="widefat" rows="5" id="dfcc-features" name="dfcc_features" placeholder="<?php esc_attr_e( 'One feature per line', 'dog-father-control-center' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
			<span class="description"><?php esc_html_e( 'One feature per line. Each line becomes a bullet in the card.', 'dog-father-control-center' ); ?></span>
		</p>
		<?php
	}

	/**
	 * Persist service meta.
	 *
	 * @param int     $post_id Service id.
	 * @param WP_Post $post    Service object.
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

		// Price (numeric).
		$price = isset( $_POST['dfcc_price'] ) ? sanitize_text_field( wp_unslash( $_POST['dfcc_price'] ) ) : '';
		$price = ( '' === $price ) ? '' : (string) floatval( $price );
		update_post_meta( $post_id, self::PREFIX . 'price', $price );

		// Text fields.
		foreach ( array( 'price_suffix', 'icon' ) as $field ) {
			$value = isset( $_POST[ 'dfcc_' . $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_' . $field ] ) ) : '';
			update_post_meta( $post_id, self::PREFIX . $field, $value );
		}

		// Features textarea.
		$features = isset( $_POST['dfcc_features'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dfcc_features'] ) ) : '';
		update_post_meta( $post_id, self::PREFIX . 'features', $features );

		// Highlight checkbox.
		$highlight = isset( $_POST['dfcc_highlight'] ) ? '1' : '';
		update_post_meta( $post_id, self::PREFIX . 'highlight', $highlight );
	}

	/* ---------------------------------------------------------------------
	 * Admin columns
	 * ------------------------------------------------------------------- */

	/**
	 * Add a price column.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['dfcc_price']     = __( 'Price', 'dog-father-control-center' );
				$new['dfcc_featured']  = __( 'Featured', 'dog-father-control-center' );
			}
		}
		return $new;
	}

	/**
	 * Render the custom columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Service id.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		if ( 'dfcc_price' === $column ) {
			$price = self::get_meta( $post_id, 'price' );
			echo '' === $price ? '—' : esc_html( wp_strip_all_tags( dfcc_money( $price ) ) );
		} elseif ( 'dfcc_featured' === $column ) {
			echo self::get_meta( $post_id, 'highlight' ) ? '★' : '—';
		}
	}

	/* ---------------------------------------------------------------------
	 * Shortcode
	 * ------------------------------------------------------------------- */

	/**
	 * Render the [dfcc_services] grid.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'count'    => 6,
				'category' => '',
				'columns'  => 3,
			),
			$atts,
			'dfcc_services'
		);

		$columns = max( 1, min( 4, (int) $atts['columns'] ) );

		$query_args = array(
			'post_type'      => 'dfcc_service',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['count'],
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		);

		if ( '' !== $atts['category'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'dfcc_service_cat',
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['category'] ) ) ),
				),
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		DFCC_Frontend_Assets::need();

		$book_page = get_page_by_path( 'book-now' );
		$book_url  = $book_page ? get_permalink( $book_page ) : '';

		ob_start();
		?>
		<div class="dfcc-services dfcc-grid dfcc-cols-<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$id          = get_the_ID();
				$price       = self::get_meta( $id, 'price' );
				$suffix      = self::get_meta( $id, 'price_suffix' );
				$icon        = self::get_meta( $id, 'icon' );
				$highlight   = self::get_meta( $id, 'highlight' );
				$features    = self::get_meta( $id, 'features' );
				$feature_arr = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $features ) ) );
				?>
				<article class="dfcc-service-card<?php echo $highlight ? ' is-featured' : ''; ?>">
					<?php if ( $highlight ) : ?>
						<span class="dfcc-badge"><?php esc_html_e( 'Featured', 'dog-father-control-center' ); ?></span>
					<?php endif; ?>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="dfcc-service-media">
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'class' => 'dfcc-service-img' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="dfcc-service-body">
						<h3 class="dfcc-service-title">
							<?php echo $this->icon_markup( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within helper. ?>
							<span><?php echo esc_html( get_the_title() ); ?></span>
						</h3>

						<?php if ( '' !== $price ) : ?>
							<p class="dfcc-service-price">
								<span class="dfcc-price-amount"><?php echo esc_html( wp_strip_all_tags( dfcc_money( $price ) ) ); ?></span>
								<?php if ( '' !== $suffix ) : ?>
									<span class="dfcc-price-suffix"><?php echo esc_html( $suffix ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<?php if ( has_excerpt() ) : ?>
							<p class="dfcc-service-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<?php if ( $feature_arr ) : ?>
							<ul class="dfcc-service-features">
								<?php foreach ( $feature_arr as $feature ) : ?>
									<li><?php echo esc_html( $feature ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>

					<div class="dfcc-service-footer">
						<?php if ( $book_url ) : ?>
							<a class="dfcc-btn dfcc-btn-primary" href="<?php echo esc_url( $book_url ); ?>"><?php esc_html_e( 'Book Now', 'dog-father-control-center' ); ?></a>
						<?php else : ?>
							<a class="dfcc-btn dfcc-btn-primary" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php esc_html_e( 'Learn More', 'dog-father-control-center' ); ?></a>
						<?php endif; ?>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Build safe markup for the icon (dashicon span or escaped emoji/text).
	 *
	 * @param string $icon Raw icon value.
	 * @return string
	 */
	private function icon_markup( $icon ) {
		$icon = trim( (string) $icon );
		if ( '' === $icon ) {
			return '';
		}
		if ( 0 === strpos( $icon, 'dashicons-' ) ) {
			return '<span class="dashicons ' . esc_attr( $icon ) . ' dfcc-service-icon" aria-hidden="true"></span>';
		}
		return '<span class="dfcc-service-icon" aria-hidden="true">' . esc_html( $icon ) . '</span>';
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Services() );
	}
);
