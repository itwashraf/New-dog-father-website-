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

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Layout & Extras', 'dog-father-control-center' ); ?></h2>
			<input type="hidden" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[_layout_submitted]' ); ?>" value="1" />

			<?php
			$dfcc_cw   = isset( $settings['container_width'] ) ? $settings['container_width'] : '1280';
			$dfcc_sticky = ! isset( $settings['header_sticky'] ) || ! empty( $settings['header_sticky'] );
			$dfcc_trans  = ! empty( $settings['header_transparent'] );
			$dfcc_wa     = ! isset( $settings['whatsapp_float'] ) || ! empty( $settings['whatsapp_float'] );
			$dfcc_ann_on = ! empty( $settings['announcement_enabled'] );
			$dfcc_ann_t  = isset( $settings['announcement_text'] ) ? $settings['announcement_text'] : '';
			$dfcc_ann_l  = isset( $settings['announcement_link'] ) ? $settings['announcement_link'] : '';
			$dfcc_ann_bg = isset( $settings['announcement_bg'] ) ? $settings['announcement_bg'] : '';
			$dfcc_ann_c  = isset( $settings['announcement_color'] ) ? $settings['announcement_color'] : '';
			$dfcc_css    = isset( $settings['custom_css'] ) ? $settings['custom_css'] : '';
			?>

			<div class="dfcc-field">
				<label for="container_width"><?php esc_html_e( 'Content Width (px)', 'dog-father-control-center' ); ?></label>
				<input type="number" min="800" max="1920" step="10" id="container_width" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[container_width]' ); ?>" value="<?php echo esc_attr( $dfcc_cw ); ?>" />
				<p class="description"><?php esc_html_e( 'How wide the page content is (800–1920). Default 1280.', 'dog-father-control-center' ); ?></p>
			</div>

			<div class="dfcc-field">
				<label><input type="checkbox" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[header_sticky]' ); ?>" value="1" <?php checked( $dfcc_sticky ); ?> /> <?php esc_html_e( 'Sticky header (stays at the top when scrolling)', 'dog-father-control-center' ); ?></label>
			</div>
			<div class="dfcc-field">
				<label><input type="checkbox" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[header_transparent]' ); ?>" value="1" <?php checked( $dfcc_trans ); ?> /> <?php esc_html_e( 'Transparent header over the hero (becomes solid on scroll)', 'dog-father-control-center' ); ?></label>
			</div>
			<div class="dfcc-field">
				<label><input type="checkbox" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[whatsapp_float]' ); ?>" value="1" <?php checked( $dfcc_wa ); ?> /> <?php esc_html_e( 'Show the floating WhatsApp button', 'dog-father-control-center' ); ?></label>
			</div>

			<hr />
			<h3 style="margin:6px 0;"><?php esc_html_e( 'Announcement Bar', 'dog-father-control-center' ); ?></h3>
			<div class="dfcc-field">
				<label><input type="checkbox" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[announcement_enabled]' ); ?>" value="1" <?php checked( $dfcc_ann_on ); ?> /> <?php esc_html_e( 'Show a thin bar above the header', 'dog-father-control-center' ); ?></label>
			</div>
			<div class="dfcc-field">
				<label for="announcement_text"><?php esc_html_e( 'Bar Text', 'dog-father-control-center' ); ?></label>
				<input type="text" class="regular-text" id="announcement_text" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[announcement_text]' ); ?>" value="<?php echo esc_attr( $dfcc_ann_t ); ?>" placeholder="<?php esc_attr_e( '🎉 Now taking holiday bookings — reserve early!', 'dog-father-control-center' ); ?>" />
			</div>
			<div class="dfcc-field">
				<label for="announcement_link"><?php esc_html_e( 'Bar Link (optional)', 'dog-father-control-center' ); ?></label>
				<input type="url" class="regular-text" id="announcement_link" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[announcement_link]' ); ?>" value="<?php echo esc_attr( $dfcc_ann_l ); ?>" placeholder="https://" />
			</div>
			<div class="dfcc-field">
				<label for="announcement_bg"><?php esc_html_e( 'Bar Background', 'dog-father-control-center' ); ?></label>
				<input type="text" class="dfcc-color-field" id="announcement_bg" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[announcement_bg]' ); ?>" value="<?php echo esc_attr( $dfcc_ann_bg ); ?>" data-default-color="#FEC208" />
			</div>
			<div class="dfcc-field">
				<label for="announcement_color"><?php esc_html_e( 'Bar Text Color', 'dog-father-control-center' ); ?></label>
				<input type="text" class="dfcc-color-field" id="announcement_color" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[announcement_color]' ); ?>" value="<?php echo esc_attr( $dfcc_ann_c ); ?>" data-default-color="#000000" />
			</div>

			<hr />
			<div class="dfcc-field">
				<label for="custom_css"><?php esc_html_e( 'Custom CSS (advanced)', 'dog-father-control-center' ); ?></label>
				<textarea id="custom_css" rows="8" class="large-text code" name="<?php echo esc_attr( DFCC_Theme_Settings::OPTION . '[custom_css]' ); ?>" placeholder=".df-hero h1 { letter-spacing: -0.02em; }"><?php echo esc_textarea( $dfcc_css ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Optional. Added to every page after the theme styles, so it always wins.', 'dog-father-control-center' ); ?></p>
			</div>
		</div>

		<?php submit_button( __( 'Save Theme Settings', 'dog-father-control-center' ) ); ?>
	</form>
</div>
