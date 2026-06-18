<?php
/**
 * SEO Control Center module.
 *
 * Adds a per-page/post SEO meta box, outputs front-end meta tags, Open Graph,
 * Twitter Card and JSON-LD schema, and provides a site-wide SEO settings screen
 * with a dashboard of pages missing meta data.
 *
 * Per-object meta is stored under the '_dfcc_seo_*' keys. Site-wide options live
 * in the 'dfcc_seo_settings' option group (seeded by the activator).
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_SEO.
 */
class DFCC_SEO extends DFCC_Module {

	/**
	 * Site-wide option name.
	 *
	 * @var string
	 */
	const OPTION = 'dfcc_seo_settings';

	/**
	 * Nonce action for the meta box.
	 *
	 * @var string
	 */
	const NONCE = 'dfcc_seo_metabox';

	/**
	 * Post types the meta box appears on.
	 *
	 * @return string[]
	 */
	private function post_types() {
		return array( 'page', 'post', 'dfcc_service' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'seo';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'SEO Center', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		// Admin screen.
		add_filter( 'dfcc_admin_pages', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );

		// Front-end output.
		add_filter( 'pre_get_document_title', array( $this, 'filter_title' ), 20 );
		add_filter( 'document_title_parts', array( $this, 'filter_title_parts' ), 20 );
		add_action( 'wp_head', array( $this, 'print_head_tags' ), 1 );
		add_action( 'wp_head', array( $this, 'print_schema' ), 20 );
	}

	/* --------------------------------------------------------------------- *
	 * Admin screen.
	 * --------------------------------------------------------------------- */

	/**
	 * Add the SEO Center admin page.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_page( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-seo',
			'title'    => __( 'SEO Center', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_page' ),
			'order'    => 50,
		);
		return $pages;
	}

	/**
	 * Register the site-wide setting.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'dfcc_seo_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize site-wide SEO settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$clean['sitewide_title_suffix'] = isset( $input['sitewide_title_suffix'] ) ? sanitize_text_field( $input['sitewide_title_suffix'] ) : '';
		$clean['enable_schema']         = empty( $input['enable_schema'] ) ? 0 : 1;
		$clean['enable_sitemap']        = empty( $input['enable_sitemap'] ) ? 0 : 1;

		return $clean;
	}

	/**
	 * Render the SEO Center screen.
	 *
	 * @return void
	 */
	public function render_page() {
		$settings = get_option( self::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();

		$this->view(
			'seo-center',
			array(
				'settings' => $settings,
				'missing'  => $this->find_missing_meta(),
			)
		);
	}

	/**
	 * Build a report of published pages/posts missing meta title or description.
	 *
	 * @return array[] Each: id, title, type, edit_link, missing_title, missing_desc.
	 */
	private function find_missing_meta() {
		$query = new WP_Query(
			array(
				'post_type'      => array( 'page', 'post', 'dfcc_service' ),
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$rows = array();
		foreach ( $query->posts as $post ) {
			$title = get_post_meta( $post->ID, '_dfcc_seo_meta_title', true );
			$desc  = get_post_meta( $post->ID, '_dfcc_seo_meta_description', true );

			$missing_title = ( '' === trim( (string) $title ) );
			$missing_desc  = ( '' === trim( (string) $desc ) );

			if ( ! $missing_title && ! $missing_desc ) {
				continue;
			}

			$rows[] = array(
				'id'            => $post->ID,
				'title'         => get_the_title( $post ),
				'type'          => $post->post_type,
				'edit_link'     => get_edit_post_link( $post->ID, 'raw' ),
				'missing_title' => $missing_title,
				'missing_desc'  => $missing_desc,
			);
		}

		wp_reset_postdata();
		return $rows;
	}

	/* --------------------------------------------------------------------- *
	 * Meta box.
	 * --------------------------------------------------------------------- */

	/**
	 * Register the SEO meta box on the relevant post types.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		foreach ( $this->post_types() as $type ) {
			add_meta_box(
				'dfcc_seo_metabox',
				__( 'SEO — Dog Father', 'dog-father-control-center' ),
				array( $this, 'render_meta_box' ),
				$type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * The meta fields and their definitions.
	 *
	 * @return array
	 */
	private function meta_fields() {
		return array(
			'meta_title'       => 'text',
			'meta_description' => 'textarea',
			'keywords'         => 'text',
			'og_image'         => 'image',
			'twitter_card'     => 'select_card',
			'canonical_url'    => 'url',
			'robots'           => 'select_robots',
			'schema_type'      => 'select_schema',
		);
	}

	/**
	 * Choices for the twitter card select.
	 *
	 * @return array
	 */
	private function card_choices() {
		return array(
			'summary'             => __( 'Summary', 'dog-father-control-center' ),
			'summary_large_image' => __( 'Summary with large image', 'dog-father-control-center' ),
		);
	}

	/**
	 * Choices for the robots select.
	 *
	 * @return array
	 */
	private function robots_choices() {
		return array(
			'index'   => __( 'Index (default)', 'dog-father-control-center' ),
			'noindex' => __( 'No index', 'dog-father-control-center' ),
		);
	}

	/**
	 * Choices for the schema type select.
	 *
	 * @return array
	 */
	private function schema_choices() {
		return array(
			''              => __( '— Default (WebPage) —', 'dog-father-control-center' ),
			'WebPage'       => 'WebPage',
			'Article'       => 'Article',
			'LocalBusiness' => 'LocalBusiness',
			'Product'       => 'Product',
			'FAQPage'       => 'FAQPage',
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );

		$get = static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, '_dfcc_seo_' . $key, true );
		};

		$og_image_id  = (int) $get( 'og_image' );
		$og_image_url = $og_image_id ? wp_get_attachment_image_url( $og_image_id, 'medium' ) : '';
		?>
		<div class="dfcc-seo-metabox">
			<p>
				<label for="dfcc_seo_meta_title"><strong><?php esc_html_e( 'Meta Title', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" id="dfcc_seo_meta_title" name="dfcc_seo_meta_title" value="<?php echo esc_attr( $get( 'meta_title' ) ); ?>" />
			</p>

			<p>
				<label for="dfcc_seo_meta_description"><strong><?php esc_html_e( 'Meta Description', 'dog-father-control-center' ); ?></strong></label>
				<textarea class="widefat" rows="3" id="dfcc_seo_meta_description" name="dfcc_seo_meta_description"><?php echo esc_textarea( $get( 'meta_description' ) ); ?></textarea>
			</p>

			<p>
				<label for="dfcc_seo_keywords"><strong><?php esc_html_e( 'Keywords (comma separated)', 'dog-father-control-center' ); ?></strong></label>
				<input type="text" class="widefat" id="dfcc_seo_keywords" name="dfcc_seo_keywords" value="<?php echo esc_attr( $get( 'keywords' ) ); ?>" />
			</p>

			<p>
				<strong><?php esc_html_e( 'Social / Open Graph Image', 'dog-father-control-center' ); ?></strong><br />
				<img id="dfcc_seo_og_image_preview" src="<?php echo esc_url( $og_image_url ); ?>" style="max-width:200px;height:auto;<?php echo $og_image_url ? '' : 'display:none;'; ?>" alt="" /><br />
				<input type="hidden" id="dfcc_seo_og_image" name="dfcc_seo_og_image" value="<?php echo esc_attr( $og_image_id ); ?>" />
				<button type="button" class="button dfcc-media-upload" data-target="dfcc_seo_og_image" data-preview="dfcc_seo_og_image_preview"><?php esc_html_e( 'Select Image', 'dog-father-control-center' ); ?></button>
				<button type="button" class="button dfcc-media-clear" data-target="dfcc_seo_og_image" data-preview="dfcc_seo_og_image_preview"><?php esc_html_e( 'Clear', 'dog-father-control-center' ); ?></button>
			</p>

			<p>
				<label for="dfcc_seo_twitter_card"><strong><?php esc_html_e( 'Twitter Card', 'dog-father-control-center' ); ?></strong></label>
				<select id="dfcc_seo_twitter_card" name="dfcc_seo_twitter_card">
					<?php foreach ( $this->card_choices() as $val => $lbl ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $get( 'twitter_card' ), $val ); ?>><?php echo esc_html( $lbl ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="dfcc_seo_canonical_url"><strong><?php esc_html_e( 'Canonical URL', 'dog-father-control-center' ); ?></strong></label>
				<input type="url" class="widefat" id="dfcc_seo_canonical_url" name="dfcc_seo_canonical_url" value="<?php echo esc_attr( $get( 'canonical_url' ) ); ?>" />
			</p>

			<p>
				<label for="dfcc_seo_robots"><strong><?php esc_html_e( 'Search Engine Visibility', 'dog-father-control-center' ); ?></strong></label>
				<select id="dfcc_seo_robots" name="dfcc_seo_robots">
					<?php foreach ( $this->robots_choices() as $val => $lbl ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $get( 'robots' ), $val ); ?>><?php echo esc_html( $lbl ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="dfcc_seo_schema_type"><strong><?php esc_html_e( 'Schema Type', 'dog-father-control-center' ); ?></strong></label>
				<select id="dfcc_seo_schema_type" name="dfcc_seo_schema_type">
					<?php foreach ( $this->schema_choices() as $val => $lbl ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $get( 'schema_type' ), $val ); ?>><?php echo esc_html( $lbl ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * Persist the meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE . '_nonce' ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE . '_nonce' ] ) ), self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! in_array( $post->post_type, $this->post_types(), true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Text fields.
		$text_keys = array( 'meta_title', 'keywords' );
		foreach ( $text_keys as $key ) {
			$value = isset( $_POST[ 'dfcc_seo_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_seo_' . $key ] ) ) : '';
			$this->save_field( $post_id, $key, $value );
		}

		// Description (textarea).
		$desc = isset( $_POST['dfcc_seo_meta_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dfcc_seo_meta_description'] ) ) : '';
		$this->save_field( $post_id, 'meta_description', $desc );

		// Canonical URL.
		$canonical = isset( $_POST['dfcc_seo_canonical_url'] ) ? esc_url_raw( wp_unslash( $_POST['dfcc_seo_canonical_url'] ) ) : '';
		$this->save_field( $post_id, 'canonical_url', $canonical );

		// OG image id.
		$og_image = isset( $_POST['dfcc_seo_og_image'] ) ? absint( $_POST['dfcc_seo_og_image'] ) : 0;
		$this->save_field( $post_id, 'og_image', $og_image ? $og_image : '' );

		// Selects with whitelists.
		$this->save_choice( $post_id, 'twitter_card', $this->card_choices() );
		$this->save_choice( $post_id, 'robots', $this->robots_choices() );
		$this->save_choice( $post_id, 'schema_type', $this->schema_choices() );
	}

	/**
	 * Save (or delete when empty) a single meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Field key (without prefix).
	 * @param mixed  $value   Value.
	 * @return void
	 */
	private function save_field( $post_id, $key, $value ) {
		if ( '' === $value || null === $value ) {
			delete_post_meta( $post_id, '_dfcc_seo_' . $key );
		} else {
			update_post_meta( $post_id, '_dfcc_seo_' . $key, $value );
		}
	}

	/**
	 * Save a select field, validated against allowed choices.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Field key.
	 * @param array  $choices Allowed value => label map.
	 * @return void
	 */
	private function save_choice( $post_id, $key, array $choices ) {
		$raw = isset( $_POST[ 'dfcc_seo_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'dfcc_seo_' . $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked in save_meta() before this runs.
		$value = array_key_exists( $raw, $choices ) ? $raw : '';
		$this->save_field( $post_id, $key, $value );
	}

	/* --------------------------------------------------------------------- *
	 * Front-end output.
	 * --------------------------------------------------------------------- */

	/**
	 * Whether schema output is enabled site-wide.
	 *
	 * @return bool
	 */
	private function schema_enabled() {
		return (int) dfcc_get_setting( self::OPTION, 'enable_schema', 1 ) === 1;
	}

	/**
	 * Read the singular queried object's SEO meta.
	 *
	 * @param string $key Key without prefix.
	 * @return string
	 */
	private function meta( $key ) {
		if ( ! is_singular() ) {
			return '';
		}
		$id = get_queried_object_id();
		if ( ! $id ) {
			return '';
		}
		return (string) get_post_meta( $id, '_dfcc_seo_' . $key, true );
	}

	/**
	 * Override the document title with the custom meta title when set.
	 *
	 * @param string $title Current title.
	 * @return string
	 */
	public function filter_title( $title ) {
		$custom = $this->meta( 'meta_title' );
		if ( '' !== $custom ) {
			$suffix = (string) dfcc_get_setting( self::OPTION, 'sitewide_title_suffix', '' );
			return $custom . $suffix;
		}
		return $title;
	}

	/**
	 * Append the sitewide suffix to the default title parts.
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public function filter_title_parts( $parts ) {
		// When a custom meta title exists, filter_title() already handled it.
		if ( '' !== $this->meta( 'meta_title' ) ) {
			return $parts;
		}
		$suffix = trim( (string) dfcc_get_setting( self::OPTION, 'sitewide_title_suffix', '' ) );
		if ( '' !== $suffix && empty( $parts['site'] ) ) {
			$parts['site'] = ltrim( $suffix, ' |-' );
		}
		return $parts;
	}

	/**
	 * Output meta description, keywords, canonical, robots, OG and Twitter tags.
	 *
	 * @return void
	 */
	public function print_head_tags() {
		$description = $this->resolve_description();
		$keywords    = $this->meta( 'keywords' );
		$robots      = $this->meta( 'robots' );
		$canonical   = $this->resolve_canonical();
		$og_image    = $this->resolve_og_image();
		$card        = $this->meta( 'twitter_card' );
		$card        = $card ? $card : ( $og_image ? 'summary_large_image' : 'summary' );

		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( add_query_arg( array() ) );

		echo "\n<!-- Dog Father SEO -->\n";

		if ( '' !== $description ) {
			printf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( $description ) );
		}
		if ( '' !== $keywords ) {
			printf( "<meta name=\"keywords\" content=\"%s\" />\n", esc_attr( $keywords ) );
		}
		if ( 'noindex' === $robots ) {
			echo "<meta name=\"robots\" content=\"noindex,nofollow\" />\n";
		}
		if ( '' !== $canonical ) {
			printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $canonical ) );
		}

		// Open Graph.
		printf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( $title ) );
		if ( '' !== $description ) {
			printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $description ) );
		}
		printf( "<meta property=\"og:type\" content=\"%s\" />\n", esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) );
		if ( $url ) {
			printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( $url ) );
		}
		printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( dfcc_get_setting( 'dfcc_global_settings', 'business_name', get_bloginfo( 'name' ) ) ) );
		if ( '' !== $og_image ) {
			printf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $og_image ) );
		}

		// Twitter.
		printf( "<meta name=\"twitter:card\" content=\"%s\" />\n", esc_attr( $card ) );
		printf( "<meta name=\"twitter:title\" content=\"%s\" />\n", esc_attr( $title ) );
		if ( '' !== $description ) {
			printf( "<meta name=\"twitter:description\" content=\"%s\" />\n", esc_attr( $description ) );
		}
		if ( '' !== $og_image ) {
			printf( "<meta name=\"twitter:image\" content=\"%s\" />\n", esc_url( $og_image ) );
		}

		echo "<!-- /Dog Father SEO -->\n";
	}

	/**
	 * Resolve the best description for the current view.
	 *
	 * @return string
	 */
	private function resolve_description() {
		$desc = $this->meta( 'meta_description' );
		if ( '' !== $desc ) {
			return $desc;
		}
		if ( is_singular() ) {
			$excerpt = get_the_excerpt( get_queried_object_id() );
			if ( '' !== $excerpt ) {
				return wp_trim_words( wp_strip_all_tags( $excerpt ), 30 );
			}
		}
		if ( is_front_page() || is_home() ) {
			$tagline = dfcc_get_setting( 'dfcc_global_settings', 'tagline', get_bloginfo( 'description' ) );
			return (string) $tagline;
		}
		return '';
	}

	/**
	 * Resolve the canonical URL.
	 *
	 * @return string
	 */
	private function resolve_canonical() {
		$custom = $this->meta( 'canonical_url' );
		if ( '' !== $custom ) {
			return $custom;
		}
		if ( is_singular() ) {
			return (string) get_permalink( get_queried_object_id() );
		}
		return '';
	}

	/**
	 * Resolve the social image URL.
	 *
	 * @return string
	 */
	private function resolve_og_image() {
		$id = (int) $this->meta( 'og_image' );
		if ( $id ) {
			$url = wp_get_attachment_image_url( $id, 'full' );
			if ( $url ) {
				return $url;
			}
		}
		if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
			$url = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
			if ( $url ) {
				return $url;
			}
		}
		return '';
	}

	/* --------------------------------------------------------------------- *
	 * Schema (JSON-LD).
	 * --------------------------------------------------------------------- */

	/**
	 * Output Organization/LocalBusiness schema site-wide plus per-page schema.
	 *
	 * @return void
	 */
	public function print_schema() {
		if ( ! $this->schema_enabled() ) {
			return;
		}

		$graph = array();

		// Site-wide organization / local business.
		$graph[] = $this->organization_schema();

		// Per-page schema.
		$page = $this->page_schema();
		if ( ! empty( $page ) ) {
			$graph[] = $page;
		}

		$data = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		printf(
			"<script type=\"application/ld+json\">%s</script>\n",
			wp_json_encode( $data ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode produces safe JSON for a script context.
		);
	}

	/**
	 * Build the Organization / LocalBusiness node from global settings.
	 *
	 * @return array
	 */
	private function organization_schema() {
		$name    = dfcc_get_setting( 'dfcc_global_settings', 'business_name', get_bloginfo( 'name' ) );
		$phone   = dfcc_get_setting( 'dfcc_global_settings', 'phone', '' );
		$email   = dfcc_get_setting( 'dfcc_global_settings', 'email', '' );
		$address = dfcc_get_setting( 'dfcc_global_settings', 'address', '' );
		$hours   = dfcc_get_setting( 'dfcc_global_settings', 'opening_hours', '' );

		$node = array(
			'@type' => 'LocalBusiness',
			'@id'   => home_url( '/#business' ),
			'name'  => $name,
			'url'   => home_url( '/' ),
		);

		if ( '' !== $phone ) {
			$node['telephone'] = $phone;
		}
		if ( '' !== $email ) {
			$node['email'] = $email;
		}
		if ( '' !== $address ) {
			$node['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => $address,
			);
		}
		if ( '' !== $hours ) {
			$node['openingHours'] = $hours;
		}

		return $node;
	}

	/**
	 * Build the per-page schema node based on the chosen schema type.
	 *
	 * @return array
	 */
	private function page_schema() {
		if ( ! is_singular() ) {
			return array();
		}

		$id   = get_queried_object_id();
		$type = $this->meta( 'schema_type' );
		if ( '' === $type ) {
			$type = 'WebPage';
		}

		$node = array(
			'@type' => $type,
			'@id'   => get_permalink( $id ) . '#' . strtolower( $type ),
			'url'   => get_permalink( $id ),
			'name'  => get_the_title( $id ),
		);

		$desc = $this->resolve_description();
		if ( '' !== $desc ) {
			$node['description'] = $desc;
		}

		$image = $this->resolve_og_image();
		if ( '' !== $image ) {
			$node['image'] = $image;
		}

		if ( 'Article' === $type ) {
			$node['datePublished'] = get_the_date( DATE_W3C, $id );
			$node['dateModified']  = get_the_modified_date( DATE_W3C, $id );
			$node['author']        = array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $id ) ),
			);
		}

		return $node;
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_SEO() );
	}
);
