<?php
/**
 * Users overview view (read-only).
 *
 * @package DogFatherControlCenter
 * @var array[] $rows User rows with role, email, registered date and booking count.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$can_edit = current_user_can( 'edit_users' );
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-groups"></span><?php esc_html_e( 'Users', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'A read-only overview. Edit users in the native WordPress screen.', 'dog-father-control-center' ); ?></p>
		</div>
		<a class="dfcc-button" href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php esc_html_e( 'Manage Users', 'dog-father-control-center' ); ?></a>
	</div>

	<div class="dfcc-panel">
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'dog-father-control-center' ); ?></th>
					<th><?php esc_html_e( 'Email', 'dog-father-control-center' ); ?></th>
					<th><?php esc_html_e( 'Role', 'dog-father-control-center' ); ?></th>
					<th><?php esc_html_e( 'Registered', 'dog-father-control-center' ); ?></th>
					<th><?php esc_html_e( 'Bookings', 'dog-father-control-center' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'No users found.', 'dog-father-control-center' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td>
								<?php if ( $can_edit ) : ?>
									<a href="<?php echo esc_url( get_edit_user_link( $row['id'] ) ); ?>"><?php echo esc_html( $row['name'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $row['name'] ); ?>
								<?php endif; ?>
								<br /><span class="description"><?php echo esc_html( $row['login'] ); ?></span>
							</td>
							<td><a href="<?php echo esc_url( 'mailto:' . $row['email'] ); ?>"><?php echo esc_html( $row['email'] ); ?></a></td>
							<td><?php echo esc_html( ! empty( $row['roles'] ) ? implode( ', ', $row['roles'] ) : '—' ); ?></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $row['registered'] ) ); ?></td>
							<td><?php echo esc_html( number_format_i18n( (int) $row['bookings'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
