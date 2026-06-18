<?php
/**
 * SEO Center admin view.
 *
 * @package DogFatherControlCenter
 * @var array   $settings Stored SEO settings.
 * @var array[] $missing  Pages/posts missing meta data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_suffix   = isset( $settings['sitewide_title_suffix'] ) ? $settings['sitewide_title_suffix'] : '';
$enable_schema  = ! empty( $settings['enable_schema'] );
$enable_sitemap = ! empty( $settings['enable_sitemap'] );
?>
<div class="wrap dfcc-wrap">
	<h1 class="dfcc-title">
		<span class="dashicons dashicons-search"></span>
		<?php esc_html_e( 'SEO Center', 'dog-father-control-center' ); ?>
	</h1>
	<p class="dfcc-subtitle">
		<?php esc_html_e( 'Site-wide search engine settings, plus a health check of pages that still need meta data.', 'dog-father-control-center' ); ?>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'dfcc_seo_settings_group' ); ?>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Site-wide Settings', 'dog-father-control-center' ); ?></h2>

			<div class="dfcc-field">
				<label for="sitewide_title_suffix"><?php esc_html_e( 'Title Suffix', 'dog-father-control-center' ); ?></label>
				<input type="text" class="regular-text" id="sitewide_title_suffix" name="dfcc_seo_settings[sitewide_title_suffix]" value="<?php echo esc_attr( $title_suffix ); ?>" />
				<p class="description"><?php esc_html_e( 'Appended to page titles, e.g. " | The Dog Father Hotel".', 'dog-father-control-center' ); ?></p>
			</div>

			<div class="dfcc-field">
				<label for="enable_schema">
					<input type="checkbox" id="enable_schema" name="dfcc_seo_settings[enable_schema]" value="1" <?php checked( $enable_schema ); ?> />
					<?php esc_html_e( 'Output structured data (JSON-LD schema)', 'dog-father-control-center' ); ?>
				</label>
			</div>

			<div class="dfcc-field">
				<label for="enable_sitemap">
					<input type="checkbox" id="enable_sitemap" name="dfcc_seo_settings[enable_sitemap]" value="1" <?php checked( $enable_sitemap ); ?> />
					<?php esc_html_e( 'Enable XML sitemap', 'dog-father-control-center' ); ?>
				</label>
			</div>
		</div>

		<?php submit_button( __( 'Save SEO Settings', 'dog-father-control-center' ) ); ?>
	</form>

	<div class="dfcc-panel">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'Pages Missing SEO Data', 'dog-father-control-center' ); ?></h2>

		<?php if ( empty( $missing ) ) : ?>
			<p>
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'Great — every published page and post has a meta title and description.', 'dog-father-control-center' ); ?>
			</p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Type', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Missing Meta Title', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Missing Meta Description', 'dog-father-control-center' ); ?></th>
						<th><?php esc_html_e( 'Action', 'dog-father-control-center' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $missing as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row['title'] ); ?></td>
							<td><code><?php echo esc_html( $row['type'] ); ?></code></td>
							<td><?php echo $row['missing_title'] ? '<span class="dashicons dashicons-warning"></span>' : '<span class="dashicons dashicons-yes"></span>'; ?></td>
							<td><?php echo $row['missing_desc'] ? '<span class="dashicons dashicons-warning"></span>' : '<span class="dashicons dashicons-yes"></span>'; ?></td>
							<td>
								<?php if ( $row['edit_link'] ) : ?>
									<a class="button button-small" href="<?php echo esc_url( $row['edit_link'] ); ?>"><?php esc_html_e( 'Edit', 'dog-father-control-center' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
