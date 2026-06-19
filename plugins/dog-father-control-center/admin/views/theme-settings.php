<?php
/**
 * Theme Settings admin view.
 *
 * @package DogFatherControlCenter
 * @var array              $settings Stored theme settings.
 * @var string[]           $fonts    Google font choices.
 * @var DFCC_Theme_Settings $module  Controller instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$colors = array(
	'color_primary'  => array(
		'label'   => __( 'Primary Yellow', 'dog-father-control-center' ),
		'default' => '#FFF10A',
	),
	'color_gold'     => array(
		'label'   => __( 'Luxury Gold', 'dog-father-control-center' ),
		'default' => '#FEC208',
	),
	'color_dark_red' => array(
		'label'   => __( 'Dark Red', 'dog-father-control-center' ),
		'default' => '#CF240A',
	),
	'color_orange'   => array(
		'label'   => __( 'Accent Orange', 'dog-father-control-center' ),
		'default' => '#FF2D08',
	),
	'color_black'    => array(
		'label'   => __( 'Black / Background', 'dog-father-control-center' ),
		'default' => '#000000',
	),
	'color_white'    => array(
		'label'   => __( 'White', 'dog-father-control-center' ),
		'default' => '#FFFFFF',
	),
);

$heading_font    = isset( $settings['heading_font'] ) ? $settings['heading_font'] : 'Poppins';
$body_font       = isset( $settings['body_font'] ) ? $settings['body_font'] : 'Inter';
$border_radius   = isset( $settings['border_radius'] ) ? $settings['border_radius'] : '14';
$dark_mode_first = ! empty( $settings['dark_mode_first'] );
?>
<div class="wrap dfcc-wrap">
	<h1 class="dfcc-title">
		<span class="dashicons dashicons-art"></span>
		<?php esc_html_e( 'Theme Settings', 'dog-father-control-center' ); ?>
	</h1>
	<p class="dfcc-subtitle">
		<?php esc_html_e( 'Change the brand colors, fonts and rounding used across the whole site. These values are published as CSS variables that Elementor and the theme read live.', 'dog-father-control-center' ); ?>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'dfcc_theme_settings_group' ); ?>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Brand Colors', 'dog-father-control-center' ); ?></h2>
			<?php foreach ( $colors as $key => $meta ) : ?>
				<?php $value = isset( $settings[ $key ] ) ? $settings[ $key ] : $meta['default']; ?>
				<div class="dfcc-field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['label'] ); ?></label>
					<input
						type="text"
						class="dfcc-color-field"
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[' . $key . ']' ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						data-default-color="<?php echo esc_attr( $meta['default'] ); ?>"
					/>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Section Colors', 'dog-father-control-center' ); ?></h2>
			<p class="description" style="margin:-6px 0 16px;">
				<?php esc_html_e( 'Choose exactly which part of the site each color affects. Leave a field empty to keep the default brand color above.', 'dog-father-control-center' ); ?>
			</p>
			<?php foreach ( DFCC_Theme_Settings::area_colors() as $area_key => $area ) : ?>
				<?php $area_val = isset( $settings[ $area_key ] ) ? $settings[ $area_key ] : ''; ?>
				<div class="dfcc-field">
					<label for="<?php echo esc_attr( $area_key ); ?>">
						<?php echo esc_html( $area['label'] ); ?>
						<span class="description" style="display:block;font-weight:400;"><?php echo esc_html( $area['desc'] ); ?></span>
					</label>
					<input
						type="text"
						class="dfcc-color-field"
						id="<?php echo esc_attr( $area_key ); ?>"
						name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[' . $area_key . ']' ); ?>"
						value="<?php echo esc_attr( $area_val ); ?>"
						data-alpha-enabled="false"
					/>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Typography & Layout', 'dog-father-control-center' ); ?></h2>

			<div class="dfcc-field">
				<label for="heading_font"><?php esc_html_e( 'Heading Font', 'dog-father-control-center' ); ?></label>
				<select id="heading_font" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[heading_font]' ); ?>">
					<?php foreach ( $fonts as $font ) : ?>
						<option value="<?php echo esc_attr( $font ); ?>" <?php selected( $heading_font, $font ); ?>><?php echo esc_html( $font ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="dfcc-field">
				<label for="body_font"><?php esc_html_e( 'Body Font', 'dog-father-control-center' ); ?></label>
				<select id="body_font" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[body_font]' ); ?>">
					<?php foreach ( $fonts as $font ) : ?>
						<option value="<?php echo esc_attr( $font ); ?>" <?php selected( $body_font, $font ); ?>><?php echo esc_html( $font ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="dfcc-field">
				<label for="border_radius"><?php esc_html_e( 'Border Radius (px)', 'dog-father-control-center' ); ?></label>
				<input
					type="number"
					min="0"
					max="80"
					step="1"
					id="border_radius"
					name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[border_radius]' ); ?>"
					value="<?php echo esc_attr( $border_radius ); ?>"
				/>
			</div>

			<div class="dfcc-field">
				<label for="dark_mode_first">
					<input
						type="checkbox"
						id="dark_mode_first"
						name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[dark_mode_first]' ); ?>"
						value="1"
						<?php checked( $dark_mode_first ); ?>
					/>
					<?php esc_html_e( 'Dark mode first (use the black background as the default theme base)', 'dog-father-control-center' ); ?>
				</label>
			</div>
		</div>

		<?php submit_button( __( 'Save Theme Settings', 'dog-father-control-center' ) ); ?>
	</form>
</div>
