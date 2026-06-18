<?php
/**
 * Homepage settings view.
 *
 * @package DogFatherControlCenter
 * @var array $settings Saved values.
 * @var array $defaults Default values.
 * @var array $toggles  Section toggle keys => labels.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper to fetch a value with default fallback.
 *
 * @param string $key Key.
 * @return string
 */
$dfcc_val = static function ( $key ) use ( $settings, $defaults ) {
	if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
};

$dfcc_name = 'dfcc_home_settings';
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e( 'Homepage', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Edit the content of your ready-made homepage. (If you build the Home page in Elementor, your Elementor design takes over automatically.)', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<form method="post" action="options.php">
		<?php settings_fields( 'dfcc_home_settings_group' ); ?>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Hero Banner', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Eyebrow', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_eyebrow]" value="<?php echo esc_attr( $dfcc_val( 'hero_eyebrow' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Headline', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_title]" value="<?php echo esc_attr( $dfcc_val( 'hero_title' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Subtitle', 'dog-father-control-center' ); ?></label>
				<textarea name="<?php echo esc_attr( $dfcc_name ); ?>[hero_subtitle]" rows="2"><?php echo esc_textarea( $dfcc_val( 'hero_subtitle' ) ); ?></textarea>
			</div>
			<?php
			$dfcc_bg = (int) $dfcc_val( 'hero_bg_id' );
			$dfcc_bg_url = $dfcc_bg ? wp_get_attachment_image_url( $dfcc_bg, 'medium' ) : '';
			?>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Background Image', 'dog-father-control-center' ); ?></label>
				<input type="hidden" id="hero_bg_id" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_bg_id]" value="<?php echo esc_attr( $dfcc_bg ); ?>" />
				<img id="hero_bg_preview" src="<?php echo esc_url( $dfcc_bg_url ); ?>" style="max-width:240px;display:<?php echo $dfcc_bg_url ? 'block' : 'none'; ?>;border-radius:8px;margin-bottom:8px;" alt="" />
				<button type="button" class="button dfcc-media-upload" data-target="hero_bg_id" data-preview="hero_bg_preview"><?php esc_html_e( 'Choose Image', 'dog-father-control-center' ); ?></button>
				<button type="button" class="button dfcc-media-clear" data-target="hero_bg_id" data-preview="hero_bg_preview"><?php esc_html_e( 'Remove', 'dog-father-control-center' ); ?></button>
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Primary Button Label', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_primary_label]" value="<?php echo esc_attr( $dfcc_val( 'hero_primary_label' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Primary Button URL', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_primary_url]" value="<?php echo esc_attr( $dfcc_val( 'hero_primary_url' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Secondary Button Label', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_secondary_label]" value="<?php echo esc_attr( $dfcc_val( 'hero_secondary_label' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Secondary Button URL', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[hero_secondary_url]" value="<?php echo esc_attr( $dfcc_val( 'hero_secondary_url' ) ); ?>" />
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'About Section', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Eyebrow', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[about_eyebrow]" value="<?php echo esc_attr( $dfcc_val( 'about_eyebrow' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Title', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[about_title]" value="<?php echo esc_attr( $dfcc_val( 'about_title' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Text', 'dog-father-control-center' ); ?></label>
				<textarea name="<?php echo esc_attr( $dfcc_name ); ?>[about_text]" rows="4"><?php echo esc_textarea( $dfcc_val( 'about_text' ) ); ?></textarea>
			</div>
			<?php
			$dfcc_ab = (int) $dfcc_val( 'about_image_id' );
			$dfcc_ab_url = $dfcc_ab ? wp_get_attachment_image_url( $dfcc_ab, 'medium' ) : '';
			?>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'About Image', 'dog-father-control-center' ); ?></label>
				<input type="hidden" id="about_image_id" name="<?php echo esc_attr( $dfcc_name ); ?>[about_image_id]" value="<?php echo esc_attr( $dfcc_ab ); ?>" />
				<img id="about_image_preview" src="<?php echo esc_url( $dfcc_ab_url ); ?>" style="max-width:240px;display:<?php echo $dfcc_ab_url ? 'block' : 'none'; ?>;border-radius:8px;margin-bottom:8px;" alt="" />
				<button type="button" class="button dfcc-media-upload" data-target="about_image_id" data-preview="about_image_preview"><?php esc_html_e( 'Choose Image', 'dog-father-control-center' ); ?></button>
				<button type="button" class="button dfcc-media-clear" data-target="about_image_id" data-preview="about_image_preview"><?php esc_html_e( 'Remove', 'dog-father-control-center' ); ?></button>
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Section Titles', 'dog-father-control-center' ); ?></h2>
			<?php
			$dfcc_titles = array(
				'services_eyebrow'   => __( 'Services Eyebrow', 'dog-father-control-center' ),
				'services_title'     => __( 'Services Title', 'dog-father-control-center' ),
				'gallery_title'      => __( 'Gallery Title', 'dog-father-control-center' ),
				'testimonials_title' => __( 'Testimonials Title', 'dog-father-control-center' ),
				'faq_title'          => __( 'FAQ Title', 'dog-father-control-center' ),
				'contact_title'      => __( 'Contact Title', 'dog-father-control-center' ),
			);
			foreach ( $dfcc_titles as $k => $lbl ) :
				?>
				<div class="dfcc-field">
					<label><?php echo esc_html( $lbl ); ?></label>
					<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $dfcc_val( $k ) ); ?>" />
				</div>
			<?php endforeach; ?>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Statistics', 'dog-father-control-center' ); ?></h2>
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="dfcc-field" style="display:flex;gap:16px;max-width:560px;">
					<span style="flex:1;">
						<label><?php printf( esc_html__( 'Stat %d Number', 'dog-father-control-center' ), (int) $i ); ?></label>
						<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[stat<?php echo (int) $i; ?>_number]" value="<?php echo esc_attr( $dfcc_val( 'stat' . $i . '_number' ) ); ?>" />
					</span>
					<span style="flex:2;">
						<label><?php printf( esc_html__( 'Stat %d Label', 'dog-father-control-center' ), (int) $i ); ?></label>
						<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[stat<?php echo (int) $i; ?>_label]" value="<?php echo esc_attr( $dfcc_val( 'stat' . $i . '_label' ) ); ?>" />
					</span>
				</div>
			<?php endfor; ?>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Call to Action', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Title', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[cta_title]" value="<?php echo esc_attr( $dfcc_val( 'cta_title' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Text', 'dog-father-control-center' ); ?></label>
				<textarea name="<?php echo esc_attr( $dfcc_name ); ?>[cta_text]" rows="2"><?php echo esc_textarea( $dfcc_val( 'cta_text' ) ); ?></textarea>
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Button Label', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[cta_button_label]" value="<?php echo esc_attr( $dfcc_val( 'cta_button_label' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Button URL', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[cta_button_url]" value="<?php echo esc_attr( $dfcc_val( 'cta_button_url' ) ); ?>" />
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Show / Hide Sections', 'dog-father-control-center' ); ?></h2>
			<p class="description" style="margin-bottom:14px;"><?php esc_html_e( 'Untick a section to hide it from the homepage.', 'dog-father-control-center' ); ?></p>
			<div style="columns:2;">
				<?php foreach ( $toggles as $key => $label ) : ?>
					<?php $checked = ! isset( $settings[ $key ] ) || ! empty( $settings[ $key ] ); ?>
					<label style="display:block;margin-bottom:10px;color:#fff;">
						<input type="checkbox" name="<?php echo esc_attr( $dfcc_name ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $checked ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<?php submit_button( __( 'Save Homepage', 'dog-father-control-center' ) ); ?>
	</form>
</div>
