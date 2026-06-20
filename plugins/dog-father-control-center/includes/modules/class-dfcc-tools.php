<?php
/**
 * Tools module — lightweight operational screens.
 *
 * Bundles several owner-facing utility pages into one module:
 *   - Reports        : live counts and an occupancy snapshot from the CPTs.
 *   - Notifications  : admin email recipients + per-event toggles.
 *   - Backup Center  : export / import of all plugin option groups as JSON.
 *   - Security       : hardening toggles (XML-RPC, version meta) + a checklist.
 *   - Users          : read-only overview of WP users with booking counts.
 *
 * Each screen registers through the 'dfcc_admin_pages' filter and shares the
 * dark admin styling. Heavy lifting (user management, post editing) is left to
 * WordPress core — these are dashboards and switches, not replacements.
 *
 * @package DogFatherControlCenter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DFCC_Tools.
 */
class DFCC_Tools extends DFCC_Module {

	/**
	 * Notification settings option name.
	 */
	const OPT_NOTIFY = 'dfcc_notification_settings';

	/**
	 * Security settings option name.
	 */
	const OPT_SECURITY = 'dfcc_security_settings';

	/**
	 * Option groups included in backup export / import.
	 *
	 * @var string[]
	 */
	private $backup_options = array(
		'dfcc_theme_settings',
		'dfcc_global_settings',
		'dfcc_integration_settings',
		'dfcc_seo_settings',
		'dfcc_notification_settings',
		'dfcc_security_settings',
	);

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'tools';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Tools', 'dog-father-control-center' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'dfcc_admin_pages', array( $this, 'register_pages' ) );
		add_action( 'admin_init', array( $this, 'handle_notifications_save' ) );
		add_action( 'admin_init', array( $this, 'handle_security_save' ) );
		add_action( 'admin_init', array( $this, 'handle_backup_export' ) );
		add_action( 'admin_init', array( $this, 'handle_backup_import' ) );

		// Kubio leftover cleanup actions.
		add_action( 'admin_post_dfcc_kubio_clean', array( $this, 'handle_kubio_clean' ) );
		add_action( 'admin_post_dfcc_kubio_restore', array( $this, 'handle_kubio_restore' ) );

		// Apply security hardening based on saved toggles.
		add_action( 'init', array( $this, 'apply_security' ) );
	}

	/**
	 * Meta key that stores a one-time backup of post content before cleanup,
	 * so the operation is reversible.
	 */
	const KUBIO_BACKUP_META = '_dfcc_pre_cleanup_content';

	/**
	 * Register all Tools admin pages.
	 *
	 * @param array $pages Existing pages.
	 * @return array
	 */
	public function register_pages( $pages ) {
		$pages[] = array(
			'slug'     => 'dfcc-reports',
			'title'    => __( 'Reports', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_reports' ),
			'order'    => 40,
		);
		$pages[] = array(
			'slug'     => 'dfcc-notifications',
			'title'    => __( 'Notifications', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_notifications' ),
			'order'    => 100,
		);
		$pages[] = array(
			'slug'     => 'dfcc-backup',
			'title'    => __( 'Backup Center', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_backup' ),
			'order'    => 900,
		);
		// Only surface "Content Cleanup" when there is actually leftover
		// page-builder markup to fix — so a fresh site never shows a tab the
		// owner can't make sense of.
		if ( $this->has_builder_leftovers() ) {
			$pages[] = array(
				'slug'     => 'dfcc-cleanup',
				'title'    => __( 'Content Cleanup', 'dog-father-control-center' ),
				'callback' => array( $this, 'render_cleanup' ),
				'order'    => 130,
			);
		}
		$pages[] = array(
			'slug'     => 'dfcc-security',
			'title'    => __( 'Security', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_security' ),
			'order'    => 120,
		);
		$pages[] = array(
			'slug'     => 'dfcc-users',
			'title'    => __( 'Users', 'dog-father-control-center' ),
			'callback' => array( $this, 'render_users' ),
			'order'    => 130,
		);
		return $pages;
	}

	/* ---------------------------------------------------------------------
	 * Reports
	 * ------------------------------------------------------------------ */

	/**
	 * Render the Reports dashboard.
	 *
	 * @return void
	 */
	public function render_reports() {
		$bookings = wp_count_posts( 'dfcc_booking' );

		// Booking status breakdown (meta-driven workflow status).
		$statuses = array( 'new', 'pending', 'approved', 'checked_in', 'checked_out', 'cancelled' );
		$by_status = array();
		foreach ( $statuses as $status ) {
			$by_status[ $status ] = $this->count_bookings_by_status( $status );
		}

		$this->view(
			'reports',
			array(
				'booking_counts' => $bookings,
				'by_status'      => $by_status,
				'dogs'           => $this->count_published( 'dfcc_dog' ),
				'services'       => $this->count_published( 'dfcc_service' ),
				'testimonials'   => $this->count_published( 'dfcc_testimonial' ),
				'occupancy'      => $by_status['checked_in'],
			)
		);
	}

	/**
	 * Count bookings carrying a given workflow status meta value.
	 *
	 * @param string $status Status slug.
	 * @return int
	 */
	private function count_bookings_by_status( $status ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_booking',
				'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
				'meta_key'       => '_dfcc_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $status, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'no_found_rows'  => false,
			)
		);
		return (int) $query->found_posts;
	}

	/**
	 * Count published posts of a type.
	 *
	 * @param string $post_type Post type.
	 * @return int
	 */
	private function count_published( $post_type ) {
		$counts = wp_count_posts( $post_type );
		return isset( $counts->publish ) ? (int) $counts->publish : 0;
	}

	/* ---------------------------------------------------------------------
	 * Notifications
	 * ------------------------------------------------------------------ */

	/**
	 * Render the Notifications settings screen.
	 *
	 * @return void
	 */
	public function render_notifications() {
		$this->view(
			'notifications',
			array(
				'settings' => get_option( self::OPT_NOTIFY, array() ),
			)
		);
	}

	/**
	 * Save notification settings.
	 *
	 * @return void
	 */
	public function handle_notifications_save() {
		if ( ! isset( $_POST['dfcc_notifications_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfcc_notifications_nonce'] ) ), 'dfcc_save_notifications' ) ) {
			return;
		}

		$recipients_raw = isset( $_POST['recipients'] ) ? (string) wp_unslash( $_POST['recipients'] ) : '';
		$parts          = preg_split( '/[\s,;]+/', $recipients_raw );
		$emails         = array();
		foreach ( (array) $parts as $maybe ) {
			$maybe = sanitize_email( $maybe );
			if ( $maybe && is_email( $maybe ) ) {
				$emails[] = $maybe;
			}
		}

		$out = array(
			'recipients'        => implode( ', ', array_unique( $emails ) ),
			'on_new_booking'    => ! empty( $_POST['on_new_booking'] ) ? 1 : 0,
			'on_status_change'  => ! empty( $_POST['on_status_change'] ) ? 1 : 0,
		);

		update_option( self::OPT_NOTIFY, $out );

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-notifications', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Send an administrative notification email to the configured recipients.
	 *
	 * Falls back to the site admin email when no recipients are configured.
	 * Usable by other modules, e.g. DFCC_Tools::notify_admin( $subject, $body ).
	 *
	 * @param string $subject Email subject.
	 * @param string $body    Email body (plain text or HTML).
	 * @return bool Whether the mail was accepted for delivery.
	 */
	public static function notify_admin( $subject, $body ) {
		$settings   = get_option( self::OPT_NOTIFY, array() );
		$recipients = isset( $settings['recipients'] ) ? (string) $settings['recipients'] : '';

		$to = array();
		foreach ( preg_split( '/[\s,;]+/', $recipients ) as $email ) {
			$email = sanitize_email( $email );
			if ( $email && is_email( $email ) ) {
				$to[] = $email;
			}
		}
		if ( empty( $to ) ) {
			$to[] = get_option( 'admin_email' );
		}

		$subject = wp_strip_all_tags( (string) $subject );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return (bool) wp_mail( $to, $subject, wpautop( wp_kses_post( $body ) ), $headers );
	}

	/* ---------------------------------------------------------------------
	 * Backup Center
	 * ------------------------------------------------------------------ */

	/**
	 * Render the Backup Center screen.
	 *
	 * @return void
	 */
	public function render_backup() {
		$this->view(
			'backup',
			array(
				'option_groups' => $this->backup_options,
			)
		);
	}

	/**
	 * Stream a JSON export of all plugin option groups (and a CPT bonus block).
	 *
	 * @return void
	 */
	public function handle_backup_export() {
		if ( ! isset( $_GET['dfcc_backup_export'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'dfcc_backup_export' );

		$payload = array(
			'plugin'      => 'dog-father-control-center',
			'version'     => defined( 'DFCC_VERSION' ) ? DFCC_VERSION : '',
			'exported_at' => gmdate( 'c' ),
			'options'     => array(),
			'content'     => array(),
		);

		foreach ( $this->backup_options as $name ) {
			$payload['options'][ $name ] = get_option( $name, array() );
		}

		// Bonus: lightweight CPT content snapshot.
		$payload['content'] = $this->export_cpt_content();

		$filename = 'dfcc-backup-' . gmdate( 'Y-m-d-His' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	/**
	 * Build a portable snapshot of the plugin's CPT content.
	 *
	 * @return array
	 */
	private function export_cpt_content() {
		$types  = array( 'dfcc_service', 'dfcc_testimonial', 'dfcc_dog', 'dfcc_gallery' );
		$result = array();

		foreach ( $types as $type ) {
			$posts = get_posts(
				array(
					'post_type'        => $type,
					'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
					'numberposts'      => 500,
					'suppress_filters' => false,
				)
			);
			$rows = array();
			foreach ( $posts as $post ) {
				$rows[] = array(
					'title'   => $post->post_title,
					'content' => $post->post_content,
					'excerpt' => $post->post_excerpt,
					'status'  => $post->post_status,
					'meta'    => get_post_meta( $post->ID ),
				);
			}
			$result[ $type ] = $rows;
		}

		return $result;
	}

	/**
	 * Restore option groups from an uploaded JSON backup.
	 *
	 * @return void
	 */
	public function handle_backup_import() {
		if ( ! isset( $_POST['dfcc_backup_import_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfcc_backup_import_nonce'] ) ), 'dfcc_backup_import' ) ) {
			return;
		}

		$notice = 'error';

		if ( ! empty( $_FILES['dfcc_backup_file']['tmp_name'] ) && is_uploaded_file( $_FILES['dfcc_backup_file']['tmp_name'] ) ) {
			$raw  = file_get_contents( $_FILES['dfcc_backup_file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$data = json_decode( (string) $raw, true );

			if ( is_array( $data ) && isset( $data['options'] ) && is_array( $data['options'] ) ) {
				foreach ( $data['options'] as $name => $value ) {
					// Only restore option groups we own.
					if ( in_array( $name, $this->backup_options, true ) ) {
						update_option( $name, $value );
					}
				}
				$notice = 'imported';
			}
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-backup', 'dfcc_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Cleanup — remove leftover Kubio block markup
	 * ------------------------------------------------------------------ */

	/**
	 * Fast check: is there any leftover page-builder markup anywhere? Used to
	 * decide whether to show the Content Cleanup tab at all.
	 *
	 * @return bool
	 */
	private function has_builder_leftovers() {
		global $wpdb;
		$found = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_status NOT IN ('trash','auto-draft','inherit')
			   AND post_content LIKE '%wp:kubio%'
			 LIMIT 1"
		);
		return ! empty( $found );
	}

	/**
	 * Find all posts/pages whose content still contains Kubio block markup.
	 *
	 * @return WP_Post[]
	 */
	private function scan_kubio_posts() {
		global $wpdb;

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_status NOT IN ('trash','auto-draft','inherit')
			   AND post_content LIKE '%wp:kubio%'
			 ORDER BY post_type ASC, post_title ASC
			 LIMIT 500"
		);

		if ( empty( $ids ) ) {
			return array();
		}

		return array_filter( array_map( 'get_post', array_map( 'intval', $ids ) ) );
	}

	/**
	 * Count Kubio blocks in a chunk of content.
	 *
	 * @param string $content Post content.
	 * @return int
	 */
	private function count_kubio_blocks( $content ) {
		return preg_match_all( '/<!--\s*wp:kubio\//', (string) $content );
	}

	/**
	 * Strip Kubio block delimiters from content, keeping any inner HTML so text
	 * and images survive (mirrors WordPress' "Keep as HTML" recovery). When a
	 * block has no real inner HTML the result is empty — which lets the theme's
	 * built-in homepage take over.
	 *
	 * @param string $content Original content.
	 * @return string Cleaned content.
	 */
	private function strip_kubio( $content ) {
		// Remove opening Kubio block comments (with or without JSON attributes).
		$content = preg_replace( '/<!--\s*wp:kubio\/[^>]*?-->/s', '', (string) $content );
		// Remove closing Kubio block comments.
		$content = preg_replace( '/<!--\s*\/wp:kubio\/[^>]*?-->/s', '', (string) $content );
		// Collapse the blank lines left behind.
		$content = preg_replace( "/(\r?\n){3,}/", "\n\n", (string) $content );
		return trim( (string) $content );
	}

	/**
	 * Render the Cleanup screen.
	 *
	 * @return void
	 */
	public function render_cleanup() {
		$this->view(
			'cleanup',
			array(
				'posts'        => $this->scan_kubio_posts(),
				'backup_meta'  => self::KUBIO_BACKUP_META,
				'count_blocks' => array( $this, 'count_kubio_blocks' ),
			)
		);
	}

	/**
	 * Handle the "clean Kubio markup" action for one post or all of them.
	 *
	 * @return void
	 */
	public function handle_kubio_clean() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'dog-father-control-center' ) );
		}
		check_admin_referer( 'dfcc_kubio_clean' );

		$target  = isset( $_POST['target'] ) ? sanitize_text_field( wp_unslash( $_POST['target'] ) ) : '';
		$cleaned = 0;

		if ( 'all' === $target ) {
			$posts = $this->scan_kubio_posts();
		} else {
			$post  = get_post( (int) $target );
			$posts = $post ? array( $post ) : array();
		}

		foreach ( $posts as $post ) {
			$original = (string) $post->post_content;
			if ( false === strpos( $original, 'wp:kubio' ) ) {
				continue;
			}

			// Back up the original once so the change can be undone.
			if ( '' === (string) get_post_meta( $post->ID, self::KUBIO_BACKUP_META, true ) ) {
				update_post_meta( $post->ID, self::KUBIO_BACKUP_META, wp_slash( $original ) );
			}

			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => wp_slash( $this->strip_kubio( $original ) ),
				)
			);
			$cleaned++;
		}

		if ( function_exists( 'dfcc_purge_caches' ) ) {
			dfcc_purge_caches();
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-cleanup', 'dfcc_cleaned' => $cleaned ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Restore the pre-cleanup content for one post (or all backed-up posts).
	 *
	 * @return void
	 */
	public function handle_kubio_restore() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'dog-father-control-center' ) );
		}
		check_admin_referer( 'dfcc_kubio_restore' );

		$target   = isset( $_POST['target'] ) ? sanitize_text_field( wp_unslash( $_POST['target'] ) ) : '';
		$restored = 0;

		if ( 'all' === $target ) {
			$ids = get_posts(
				array(
					'post_type'   => 'any',
					'post_status' => 'any',
					'fields'      => 'ids',
					'numberposts' => 500,
					'meta_key'    => self::KUBIO_BACKUP_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				)
			);
		} else {
			$ids = array( (int) $target );
		}

		foreach ( $ids as $id ) {
			$backup = (string) get_post_meta( $id, self::KUBIO_BACKUP_META, true );
			if ( '' === $backup ) {
				continue;
			}
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => wp_slash( $backup ),
				)
			);
			delete_post_meta( $id, self::KUBIO_BACKUP_META );
			$restored++;
		}

		if ( function_exists( 'dfcc_purge_caches' ) ) {
			dfcc_purge_caches();
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-cleanup', 'dfcc_restored' => $restored ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Security
	 * ------------------------------------------------------------------ */

	/**
	 * Render the Security screen.
	 *
	 * @return void
	 */
	public function render_security() {
		$this->view(
			'security',
			array(
				'settings'  => get_option( self::OPT_SECURITY, array() ),
				'checklist' => $this->security_checklist(),
			)
		);
	}

	/**
	 * Save security settings.
	 *
	 * @return void
	 */
	public function handle_security_save() {
		if ( ! isset( $_POST['dfcc_security_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( dfcc_admin_cap() ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfcc_security_nonce'] ) ), 'dfcc_save_security' ) ) {
			return;
		}

		$out = array(
			'disable_xmlrpc'   => ! empty( $_POST['disable_xmlrpc'] ) ? 1 : 0,
			'hide_wp_version'  => ! empty( $_POST['hide_wp_version'] ) ? 1 : 0,
			'disable_file_edit' => ! empty( $_POST['disable_file_edit'] ) ? 1 : 0,
			'limit_login'      => ! empty( $_POST['limit_login'] ) ? 1 : 0,
			'strong_rest_auth' => ! empty( $_POST['strong_rest_auth'] ) ? 1 : 0,
		);

		update_option( self::OPT_SECURITY, $out );

		wp_safe_redirect( add_query_arg( array( 'page' => 'dfcc-security', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Apply enabled security hardening measures.
	 *
	 * @return void
	 */
	public function apply_security() {
		$settings = get_option( self::OPT_SECURITY, array() );
		if ( ! is_array( $settings ) ) {
			return;
		}

		if ( ! empty( $settings['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( ! empty( $settings['hide_wp_version'] ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}
	}

	/**
	 * Build a simple security status checklist.
	 *
	 * @return array[] Each: array( 'label', 'ok' (bool), 'note' ).
	 */
	private function security_checklist() {
		$admin_user = get_user_by( 'login', 'admin' );

		return array(
			array(
				'label' => __( 'Site served over HTTPS (SSL)', 'dog-father-control-center' ),
				'ok'    => is_ssl(),
				'note'  => is_ssl() ? __( 'Active', 'dog-father-control-center' ) : __( 'Not detected on this request', 'dog-father-control-center' ),
			),
			array(
				'label' => __( 'Debug mode disabled', 'dog-father-control-center' ),
				'ok'    => ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
				'note'  => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? __( 'WP_DEBUG is ON', 'dog-father-control-center' ) : __( 'Off', 'dog-father-control-center' ),
			),
			array(
				'label' => __( 'No default "admin" username', 'dog-father-control-center' ),
				'ok'    => empty( $admin_user ),
				'note'  => empty( $admin_user ) ? __( 'Good', 'dog-father-control-center' ) : __( 'A user named "admin" exists — rename it', 'dog-father-control-center' ),
			),
			array(
				'label' => __( 'File editing disabled in dashboard', 'dog-father-control-center' ),
				'ok'    => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT,
				'note'  => ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ? __( 'Disabled', 'dog-father-control-center' ) : __( 'Add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php', 'dog-father-control-center' ),
			),
		);
	}

	/* ---------------------------------------------------------------------
	 * Users
	 * ------------------------------------------------------------------ */

	/**
	 * Render the read-only Users overview.
	 *
	 * @return void
	 */
	public function render_users() {
		$users = get_users(
			array(
				'number'  => 200,
				'orderby' => 'registered',
				'order'   => 'DESC',
			)
		);

		$rows = array();
		foreach ( $users as $user ) {
			$rows[] = array(
				'id'         => $user->ID,
				'name'       => $user->display_name,
				'login'      => $user->user_login,
				'email'      => $user->user_email,
				'roles'      => $user->roles,
				'registered' => $user->user_registered,
				'bookings'   => $this->count_bookings_for_email( $user->user_email ),
			);
		}

		$this->view( 'users', array( 'rows' => $rows ) );
	}

	/**
	 * Count bookings linked to a customer email address.
	 *
	 * Bookings are expected to store the customer email in the _dfcc_email meta.
	 *
	 * @param string $email Email address.
	 * @return int
	 */
	private function count_bookings_for_email( $email ) {
		if ( empty( $email ) ) {
			return 0;
		}
		$query = new WP_Query(
			array(
				'post_type'      => 'dfcc_booking',
				'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
				'meta_key'       => '_dfcc_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'no_found_rows'  => false,
			)
		);
		return (int) $query->found_posts;
	}
}

add_action(
	'dfcc_register_modules',
	static function ( $plugin ) {
		$plugin->add_module( new DFCC_Tools() );
	}
);
