<?php
/**
 * Gallery module.
 *
 * Adds media meta to the dfcc_gallery custom post type (registered in
 * DFCC_Post_Types) and exposes the [dfcc_gallery] shortcode which renders a
 * responsive lazy-loaded grid with optional album filters and a lightbox.
 *
 * All gallery meta is stored with the _dfcc_ prefix.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Gallery.
 */
class DFCC_Gallery extends DFCC_Module {

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
	const NONCE_ACTION = 'dfcc_save_gallery';

	/**
	 * Nonce field.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'dfcc_gallery_nonce';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'gallery';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Gallery', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'add_meta_boxes_dfcc_gallery', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_dfcc_gallery', array( $this, 'save_meta' ), 10, 2 );

		add_shortcode( 'dfcc_gallery', array( $this, 'shortcode' ) );
	}

	/**
	 * Read a gallery meta value.
	 *
	 * @param int    $post_id Gallery id.
	 * @param string $key     Field key without the prefix.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, $default = '' ) {
		$value = get_post_meta( $post_id, self::PREFIX . $key, true );
		return ( '' === $value || false === $value ) ? $default : $value;
	}

	/**
	 * Allowed media types.
	 *
	 * @return array
	 */
	public static function media_types() {
		return array(
			'image' => __( 'Image', 'dog-father-control-center' ),
			'video' => __( 'Video', 'dog-father-control-center' ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Meta box
	 * ------------------------------------------------------------------- */

	/**
	 * Register the gallery meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dfcc-gallery-details',
			__( 'Media Details', 'dog-father-control-center' ),
			array( $this, 'render_box' ),
			'dfcc_gallery',
			'side',
			'high'
		);
	}

	/**
	 * Render the gallery meta box.
	 *
	 * @param WP_Post $post Current gallery item.
	 * @return void
	 */
	public function render_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$media_type = self::get_meta( $post->ID, 'media_type', 'image' );
		$video_url  = self::get_meta( $post->ID, 'video_url' );
		?>
		<p>
			<label for="dfcc-media_type"><strong><?php esc_html_e( 'Media Type', 'dog-father-control-center' ); ?></strong></label><br />
			<select class="widefat" id="dfcc-media_type" name="dfcc_media_type">
				<?php foreach ( self::media_types() as $value => $tlabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $media_type, $value ); ?>><?php echo esc_html( $tlabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="dfcc-video_url"><strong><?php esc_html_e( 'Video URL', 'dog-father-control-center' ); ?></strong></label><br />
			<input type="url" class="widefat" id="dfcc-video_url" name="dfcc_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://" />
			<span class="description"><?php esc_html_e( 'YouTube, Vimeo or direct .mp4 URL. Used when media type is Video.', 'dog-father-control-center' ); ?></span>
		</p>
		<p class="description"><?php esc_html_e( 'Set the Featured Image for the thumbnail (and the image itself for image items).', 'dog-father-control-center' ); ?></p>
		<?php
	}

	/**
	 * Persist gallery meta.
	 *
	 * @param int     $post_id Gallery id.
	 * @param WP_Post $post    Gallery object.
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

		$media_type = isset( $_POST['dfcc_media_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dfcc_media_type'] ) ) : 'image';
		$media_type = array_key_exists( $media_type, self::media_types() ) ? $media_type : 'image';
		update_post_meta( $post_id, self::PREFIX . 'media_type', $media_type );

		$video_url = isset( $_POST['dfcc_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['dfcc_video_url'] ) ) : '';
		update_post_meta( $post_id, self::PREFIX . 'video_url', $video_url );
	}

	/* ---------------------------------------------------------------------
	 * Shortcode
	 * ------------------------------------------------------------------- */

	/**
	 * Render the [dfcc_gallery] grid.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'count'    => 12,
				'album'    => '',
				'columns'  => 3,
				'lightbox' => 'yes',
				'filters'  => 'yes',
			),
			$atts,
			'dfcc_gallery'
		);

		$columns  = max( 1, min( 5, (int) $atts['columns'] ) );
		$lightbox = $this->is_yes( $atts['lightbox'] );
		$filters  = $this->is_yes( $atts['filters'] );

		$query_args = array(
			'post_type'      => 'dfcc_gallery',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['count'],
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		if ( '' !== $atts['album'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'dfcc_gallery_cat',
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['album'] ) ) ),
				),
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		DFCC_Frontend_Assets::need();

		// Collect albums for the filter bar.
		$terms = array();
		if ( $filters ) {
			$found = get_terms(
				array(
					'taxonomy'   => 'dfcc_gallery_cat',
					'hide_empty' => true,
				)
			);
			if ( ! is_wp_error( $found ) ) {
				$terms = $found;
			}
		}

		ob_start();
		?>
		<div class="dfcc-gallery-wrap" data-lightbox="<?php echo $lightbox ? '1' : '0'; ?>">
			<?php if ( $filters && $terms ) : ?>
				<div class="dfcc-filters" role="tablist">
					<button type="button" class="dfcc-filter is-active" data-filter="*"><?php esc_html_e( 'All', 'dog-father-control-center' ); ?></button>
					<?php foreach ( $terms as $term ) : ?>
						<button type="button" class="dfcc-filter" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="dfcc-gallery dfcc-masonry dfcc-cols-<?php echo esc_attr( $columns ); ?>">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$id         = get_the_ID();
					$media_type = self::get_meta( $id, 'media_type', 'image' );
					$video_url  = self::get_meta( $id, 'video_url' );
					$title      = get_the_title();
					$item_terms = wp_get_post_terms( $id, 'dfcc_gallery_cat', array( 'fields' => 'slugs' ) );
					$item_terms = is_wp_error( $item_terms ) ? array() : $item_terms;
					$slug_attr  = esc_attr( implode( ' ', $item_terms ) );
					$thumb      = get_the_post_thumbnail_url( $id, 'large' );
					$is_video   = ( 'video' === $media_type && '' !== $video_url );
					?>
					<figure class="dfcc-gallery-item<?php echo $is_video ? ' is-video' : ''; ?>" data-terms="<?php echo $slug_attr; ?>">
						<?php
						if ( $lightbox ) {
							$data = $is_video
								? 'data-type="video" data-src="' . esc_url( $video_url ) . '"'
								: 'data-type="image" data-src="' . esc_url( $thumb ? $thumb : '' ) . '"';
							echo '<button type="button" class="dfcc-gallery-trigger" ' . $data . ' aria-label="' . esc_attr( $title ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pieces escaped above.
						}

						if ( has_post_thumbnail( $id ) ) {
							echo get_the_post_thumbnail( $id, 'large', array( 'loading' => 'lazy', 'class' => 'dfcc-gallery-img', 'alt' => esc_attr( $title ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core returns safe markup.
						} else {
							echo '<span class="dfcc-gallery-noimg" aria-hidden="true"></span>';
						}

						if ( $is_video ) {
							echo '<span class="dfcc-play-badge" aria-hidden="true">▶</span>';
						}

						if ( $title ) {
							echo '<figcaption class="dfcc-gallery-caption">' . esc_html( $title ) . '</figcaption>';
						}

						if ( $lightbox ) {
							echo '</button>';
						}
						?>
					</figure>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Normalise a yes/no attribute.
	 *
	 * @param string $value Raw value.
	 * @return bool
	 */
	private function is_yes( $value ) {
		return in_array( strtolower( (string) $value ), array( 'yes', 'true', '1', 'on' ), true );
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Gallery() );
	}
);
