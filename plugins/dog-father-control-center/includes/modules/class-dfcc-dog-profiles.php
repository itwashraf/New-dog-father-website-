<?php
/**
 * Dog Profiles module.
 *
 * Adds the rich profile meta box to the dfcc_dog custom post type: breed,
 * health, owner and emergency details, a stay history log and a media-backed
 * document repeater. Also enhances the dog list table with at-a-glance columns.
 *
 * All custom fields are stored as post meta prefixed with _dfcc_. The document
 * repeater stores an array of attachment IDs under _dfcc_documents.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Dog_Profiles.
 */
class DFCC_Dog_Profiles extends DFCC_Module {

	/**
	 * Nonce action for the profile meta box.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'dfcc_save_dog_profile';

	/**
	 * Nonce request field name.
	 *
	 * @var string
	 */
	const NONCE_FIELD = 'dfcc_dog_profile_nonce';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'dog-profiles';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Dog Profiles', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_dfcc_dog', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_filter( 'manage_dfcc_dog_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_dfcc_dog_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	/**
	 * Map of simple text/textarea fields to their input type.
	 *
	 * @return array<string,string> field => 'text'|'textarea'|'email'|'tel'|'number'.
	 */
	private function fields() {
		return array(
			'breed'                => 'text',
			'age'                  => 'text',
			'gender'               => 'text',
			'weight'               => 'text',
			'food_preferences'     => 'textarea',
			'medication'           => 'textarea',
			'vaccination_records'  => 'textarea',
			'medical_notes'        => 'textarea',
			'owner_name'           => 'text',
			'owner_phone'          => 'tel',
			'owner_email'          => 'email',
			'emergency_contact'    => 'text',
			'stay_history'         => 'textarea',
		);
	}

	/**
	 * Register the profile meta box on the dog CPT.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'dfcc_dog_profile',
			__( 'Dog Profile', 'dog-father-control-center' ),
			array( $this, 'render_meta_box' ),
			'dfcc_dog',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue the WordPress media library on the dog edit screen.
	 *
	 * Inline JS for the document repeater is printed inside the meta box so it
	 * is fully self-contained.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'dfcc_dog' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
	}

	/**
	 * Render the profile meta box.
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$labels = array(
			'breed'               => __( 'Breed', 'dog-father-control-center' ),
			'age'                 => __( 'Age', 'dog-father-control-center' ),
			'gender'              => __( 'Gender', 'dog-father-control-center' ),
			'weight'              => __( 'Weight', 'dog-father-control-center' ),
			'food_preferences'    => __( 'Food Preferences', 'dog-father-control-center' ),
			'medication'          => __( 'Medication', 'dog-father-control-center' ),
			'vaccination_records' => __( 'Vaccination Records', 'dog-father-control-center' ),
			'medical_notes'       => __( 'Medical Notes', 'dog-father-control-center' ),
			'owner_name'          => __( 'Owner Name', 'dog-father-control-center' ),
			'owner_phone'         => __( 'Owner Phone', 'dog-father-control-center' ),
			'owner_email'         => __( 'Owner Email', 'dog-father-control-center' ),
			'emergency_contact'   => __( 'Emergency Contact', 'dog-father-control-center' ),
			'stay_history'        => __( 'Stay History', 'dog-father-control-center' ),
		);

		$values = array();
		foreach ( array_keys( $this->fields() ) as $field ) {
			$values[ $field ] = get_post_meta( $post->ID, '_dfcc_' . $field, true );
		}

		echo '<style>
			.dfcc-profile-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-top:8px}
			.dfcc-profile-grid .dfcc-field-full{grid-column:1 / -1}
			.dfcc-profile-grid label{display:block;font-weight:600;margin-bottom:4px}
			.dfcc-profile-grid input[type=text],.dfcc-profile-grid input[type=email],.dfcc-profile-grid input[type=tel],.dfcc-profile-grid textarea{width:100%}
			.dfcc-profile-grid textarea{min-height:80px}
			.dfcc-docs-list{list-style:none;margin:8px 0 0;padding:0}
			.dfcc-docs-list li{display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid #dcdcde;border-radius:6px;margin-bottom:6px;background:#fff}
			.dfcc-docs-list li a{flex:1;text-decoration:none}
			@media (max-width:782px){.dfcc-profile-grid{grid-template-columns:1fr}}
		</style>';

		echo '<div class="dfcc-profile-grid">';
		foreach ( $this->fields() as $field => $type ) {
			$is_full = ( 'textarea' === $type );
			$label   = isset( $labels[ $field ] ) ? $labels[ $field ] : ucwords( str_replace( '_', ' ', $field ) );
			$name    = 'dfcc_' . $field;
			$id      = 'dfcc_field_' . $field;

			printf(
				'<div class="dfcc-field%s">',
				$is_full ? ' dfcc-field-full' : ''
			);
			printf(
				'<label for="%s">%s</label>',
				esc_attr( $id ),
				esc_html( $label )
			);

			if ( 'textarea' === $type ) {
				$readonly = ( 'stay_history' === $field ) ? ' readonly="readonly"' : '';
				printf(
					'<textarea id="%1$s" name="%2$s"%4$s>%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( (string) $values[ $field ] ),
					$readonly // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static literal.
				);
				if ( 'stay_history' === $field ) {
					printf(
						'<p class="description">%s</p>',
						esc_html__( 'Read-only log of past stays.', 'dog-father-control-center' )
					);
				}
			} else {
				printf(
					'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" />',
					esc_attr( $type ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $values[ $field ] )
				);
			}
			echo '</div>';
		}
		echo '</div>';

		$this->render_documents( $post );
	}

	/**
	 * Render the document repeater (media uploader backed).
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	private function render_documents( $post ) {
		$documents = get_post_meta( $post->ID, '_dfcc_documents', true );
		$documents = is_array( $documents ) ? array_map( 'absint', $documents ) : array();

		echo '<hr />';
		printf( '<h3>%s</h3>', esc_html__( 'Documents', 'dog-father-control-center' ) );
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Attach vaccination certificates, contracts or other files.', 'dog-father-control-center' )
		);

		echo '<ul class="dfcc-docs-list" id="dfcc-docs-list">';
		foreach ( $documents as $attachment_id ) {
			$this->render_document_row( $attachment_id );
		}
		echo '</ul>';

		printf(
			'<button type="button" class="button" id="dfcc-add-document">%s</button>',
			esc_html__( 'Add Document', 'dog-father-control-center' )
		);

		// Self-contained inline JS scoped to this meta box.
		?>
		<script type="text/javascript">
		( function () {
			var addBtn = document.getElementById( 'dfcc-add-document' );
			var list   = document.getElementById( 'dfcc-docs-list' );
			if ( ! addBtn || ! list || typeof wp === 'undefined' || ! wp.media ) {
				return;
			}

			function escapeHtml( str ) {
				var div = document.createElement( 'div' );
				div.appendChild( document.createTextNode( str ) );
				return div.innerHTML;
			}

			function addRow( attachment ) {
				var li = document.createElement( 'li' );
				var input = document.createElement( 'input' );
				input.type = 'hidden';
				input.name = 'dfcc_documents[]';
				input.value = attachment.id;

				var link = document.createElement( 'a' );
				link.href = attachment.url;
				link.target = '_blank';
				link.rel = 'noopener noreferrer';
				link.innerHTML = escapeHtml( attachment.filename || attachment.title || ( 'Attachment #' + attachment.id ) );

				var remove = document.createElement( 'button' );
				remove.type = 'button';
				remove.className = 'button-link-delete';
				remove.textContent = <?php echo wp_json_encode( __( 'Remove', 'dog-father-control-center' ) ); ?>;
				remove.addEventListener( 'click', function () {
					li.parentNode.removeChild( li );
				} );

				li.appendChild( input );
				li.appendChild( link );
				li.appendChild( remove );
				list.appendChild( li );
			}

			addBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var frame = wp.media( {
					title: <?php echo wp_json_encode( __( 'Select documents', 'dog-father-control-center' ) ); ?>,
					multiple: true,
					button: { text: <?php echo wp_json_encode( __( 'Use these files', 'dog-father-control-center' ) ); ?> }
				} );
				frame.on( 'select', function () {
					var selection = frame.state().get( 'selection' ).toJSON();
					selection.forEach( addRow );
				} );
				frame.open();
			} );

			// Existing rows render server-side; wire their remove buttons.
			Array.prototype.forEach.call( list.querySelectorAll( '.button-link-delete' ), function ( btn ) {
				btn.addEventListener( 'click', function () {
					var li = btn.closest( 'li' );
					if ( li ) {
						li.parentNode.removeChild( li );
					}
				} );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * Render a single saved document row.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	private function render_document_row( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return;
		}
		$url   = wp_get_attachment_url( $attachment_id );
		$title = get_the_title( $attachment_id );
		if ( ! $url ) {
			return;
		}
		printf(
			'<li><input type="hidden" name="dfcc_documents[]" value="%1$d" /><a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a><button type="button" class="button-link-delete">%4$s</button></li>',
			$attachment_id,
			esc_url( $url ),
			esc_html( $title ? $title : sprintf( '#%d', $attachment_id ) ),
			esc_html__( 'Remove', 'dog-father-control-center' )
		);
	}

	/**
	 * Persist the profile fields.
	 *
	 * @param int     $post_id Post being saved.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		// Bail on autosave / revisions.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Nonce check.
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Simple fields.
		foreach ( $this->fields() as $field => $type ) {
			$key   = 'dfcc_' . $field;
			$meta  = '_dfcc_' . $field;
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

			switch ( $type ) {
				case 'textarea':
					$value = sanitize_textarea_field( $raw );
					break;
				case 'email':
					$value = sanitize_email( $raw );
					break;
				default:
					$value = sanitize_text_field( $raw );
					break;
			}

			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta );
			} else {
				update_post_meta( $post_id, $meta, $value );
			}
		}

		// Documents (array of attachment IDs).
		$documents = array();
		if ( isset( $_POST['dfcc_documents'] ) && is_array( $_POST['dfcc_documents'] ) ) {
			$raw_docs = wp_unslash( $_POST['dfcc_documents'] ); // phpcs:ignore WordPress.Security.ValidatedSanitized -- absint below.
			foreach ( $raw_docs as $doc ) {
				$id = absint( $doc );
				if ( $id ) {
					$documents[] = $id;
				}
			}
		}
		$documents = array_values( array_unique( $documents ) );

		if ( empty( $documents ) ) {
			delete_post_meta( $post_id, '_dfcc_documents' );
		} else {
			update_post_meta( $post_id, '_dfcc_documents', $documents );
		}
	}

	/**
	 * Define the dog list table columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['dfcc_photo'] = __( 'Photo', 'dog-father-control-center' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['dfcc_breed'] = __( 'Breed', 'dog-father-control-center' );
				$new['dfcc_owner'] = __( 'Owner', 'dog-father-control-center' );
				$new['dfcc_phone'] = __( 'Phone', 'dog-father-control-center' );
			}
		}
		return $new;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'dfcc_photo':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 48, 48 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
				} else {
					echo '<span class="dashicons dashicons-pets" aria-hidden="true"></span>';
				}
				break;
			case 'dfcc_breed':
				echo esc_html( (string) get_post_meta( $post_id, '_dfcc_breed', true ) );
				break;
			case 'dfcc_owner':
				echo esc_html( (string) get_post_meta( $post_id, '_dfcc_owner_name', true ) );
				break;
			case 'dfcc_phone':
				echo esc_html( (string) get_post_meta( $post_id, '_dfcc_owner_phone', true ) );
				break;
		}
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Dog_Profiles() );
	}
);
