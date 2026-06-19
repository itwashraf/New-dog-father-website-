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
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Image Badge Text', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[about_badge_text]" value="<?php echo esc_attr( $dfcc_val( 'about_badge_text' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'The small badge over the photo. The big number uses Stat 3.', 'dog-father-control-center' ); ?></p>
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Button Label', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[about_button_label]" value="<?php echo esc_attr( $dfcc_val( 'about_button_label' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Button URL', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[about_button_url]" value="<?php echo esc_attr( $dfcc_val( 'about_button_url' ) ); ?>" />
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Section Titles & Buttons', 'dog-father-control-center' ); ?></h2>
			<?php
			$dfcc_titles = array(
				'services_eyebrow'      => __( 'Services Eyebrow', 'dog-father-control-center' ),
				'services_title'        => __( 'Services Title', 'dog-father-control-center' ),
				'services_button_label' => __( 'Services Button Label', 'dog-father-control-center' ),
				'services_button_url'   => __( 'Services Button URL', 'dog-father-control-center' ),
				'gallery_eyebrow'       => __( 'Gallery Eyebrow', 'dog-father-control-center' ),
				'gallery_title'         => __( 'Gallery Title', 'dog-father-control-center' ),
				'gallery_button_label'  => __( 'Gallery Button Label', 'dog-father-control-center' ),
				'gallery_button_url'    => __( 'Gallery Button URL', 'dog-father-control-center' ),
				'testimonials_eyebrow'  => __( 'Testimonials Eyebrow', 'dog-father-control-center' ),
				'testimonials_title'    => __( 'Testimonials Title', 'dog-father-control-center' ),
				'faq_eyebrow'           => __( 'FAQ Eyebrow', 'dog-father-control-center' ),
				'faq_title'             => __( 'FAQ Title', 'dog-father-control-center' ),
				'contact_eyebrow'       => __( 'Contact Eyebrow', 'dog-father-control-center' ),
				'contact_title'         => __( 'Contact Title', 'dog-father-control-center' ),
				'header_book_label'     => __( 'Header "Book Now" Button Label', 'dog-father-control-center' ),
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
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Trust Bar', 'dog-father-control-center' ); ?></h2>
			<p class="description" style="margin:-6px 0 14px;"><?php esc_html_e( 'The four highlights shown just under the hero. Icon = a Dashicon name (browse names at developer.wordpress.org/resource/dashicons) or an emoji.', 'dog-father-control-center' ); ?></p>
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="dfcc-field" style="display:flex;gap:12px;max-width:760px;">
					<span style="flex:1;">
						<label><?php printf( esc_html__( 'Item %d Icon', 'dog-father-control-center' ), (int) $i ); ?></label>
						<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[trust<?php echo (int) $i; ?>_icon]" value="<?php echo esc_attr( $dfcc_val( 'trust' . $i . '_icon' ) ); ?>" placeholder="dashicons-shield-alt" />
					</span>
					<span style="flex:1;">
						<label><?php printf( esc_html__( 'Item %d Title', 'dog-father-control-center' ), (int) $i ); ?></label>
						<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[trust<?php echo (int) $i; ?>_title]" value="<?php echo esc_attr( $dfcc_val( 'trust' . $i . '_title' ) ); ?>" />
					</span>
					<span style="flex:1;">
						<label><?php printf( esc_html__( 'Item %d Subtitle', 'dog-father-control-center' ), (int) $i ); ?></label>
						<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[trust<?php echo (int) $i; ?>_sub]" value="<?php echo esc_attr( $dfcc_val( 'trust' . $i . '_sub' ) ); ?>" />
					</span>
				</div>
			<?php endfor; ?>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Why Choose Us', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Eyebrow', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[why_eyebrow]" value="<?php echo esc_attr( $dfcc_val( 'why_eyebrow' ) ); ?>" />
			</div>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Title', 'dog-father-control-center' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[why_title]" value="<?php echo esc_attr( $dfcc_val( 'why_title' ) ); ?>" />
			</div>
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<div class="dfcc-field" style="border-top:1px solid #ededf0;padding-top:12px;">
					<div style="display:flex;gap:12px;">
						<span style="flex:1;">
							<label><?php printf( esc_html__( 'Reason %d Icon', 'dog-father-control-center' ), (int) $i ); ?></label>
							<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[why<?php echo (int) $i; ?>_icon]" value="<?php echo esc_attr( $dfcc_val( 'why' . $i . '_icon' ) ); ?>" placeholder="dashicons-shield" />
						</span>
						<span style="flex:2;">
							<label><?php printf( esc_html__( 'Reason %d Title', 'dog-father-control-center' ), (int) $i ); ?></label>
							<input type="text" name="<?php echo esc_attr( $dfcc_name ); ?>[why<?php echo (int) $i; ?>_title]" value="<?php echo esc_attr( $dfcc_val( 'why' . $i . '_title' ) ); ?>" />
						</span>
					</div>
					<label style="margin-top:8px;"><?php printf( esc_html__( 'Reason %d Text', 'dog-father-control-center' ), (int) $i ); ?></label>
					<textarea name="<?php echo esc_attr( $dfcc_name ); ?>[why<?php echo (int) $i; ?>_text]" rows="2"><?php echo esc_textarea( $dfcc_val( 'why' . $i . '_text' ) ); ?></textarea>
				</div>
			<?php endfor; ?>
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
			<?php
			$dfcc_cta = (int) $dfcc_val( 'cta_bg_id' );
			$dfcc_cta_url = $dfcc_cta ? wp_get_attachment_image_url( $dfcc_cta, 'medium' ) : '';
			?>
			<div class="dfcc-field">
				<label><?php esc_html_e( 'Background Image', 'dog-father-control-center' ); ?></label>
				<input type="hidden" id="cta_bg_id" name="<?php echo esc_attr( $dfcc_name ); ?>[cta_bg_id]" value="<?php echo esc_attr( $dfcc_cta ); ?>" />
				<img id="cta_bg_preview" src="<?php echo esc_url( $dfcc_cta_url ); ?>" style="max-width:240px;display:<?php echo $dfcc_cta_url ? 'block' : 'none'; ?>;border-radius:8px;margin-bottom:8px;" alt="" />
				<button type="button" class="button dfcc-media-upload" data-target="cta_bg_id" data-preview="cta_bg_preview"><?php esc_html_e( 'Choose Image', 'dog-father-control-center' ); ?></button>
				<button type="button" class="button dfcc-media-clear" data-target="cta_bg_id" data-preview="cta_bg_preview"><?php esc_html_e( 'Remove', 'dog-father-control-center' ); ?></button>
				<p class="description"><?php esc_html_e( 'Optional. Leave empty to use the default photo.', 'dog-father-control-center' ); ?></p>
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'How Many Items to Show', 'dog-father-control-center' ); ?></h2>
			<div class="dfcc-field" style="display:flex;gap:18px;flex-wrap:wrap;max-width:760px;">
				<?php
				$dfcc_counts = array(
					'count_services'     => __( 'Services', 'dog-father-control-center' ),
					'count_gallery'      => __( 'Gallery photos', 'dog-father-control-center' ),
					'count_testimonials' => __( 'Testimonials', 'dog-father-control-center' ),
					'count_faq'          => __( 'FAQs', 'dog-father-control-center' ),
				);
				foreach ( $dfcc_counts as $ck => $cl ) :
					?>
					<span>
						<label><?php echo esc_html( $cl ); ?></label>
						<input type="number" min="1" max="24" step="1" style="width:90px;" name="<?php echo esc_attr( $dfcc_name ); ?>[<?php echo esc_attr( $ck ); ?>]" value="<?php echo esc_attr( $dfcc_val( $ck ) ); ?>" />
					</span>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="dfcc-panel">
			<h2 class="dfcc-section-title"><?php esc_html_e( 'Section Layout', 'dog-father-control-center' ); ?></h2>
			<p class="description" style="margin:-6px 0 14px;"><?php esc_html_e( 'Drag the handle to reorder homepage sections, tick to show/hide, and optionally give each section its own background color and spacing. (The hero banner is always first.)', 'dog-father-control-center' ); ?></p>

			<style>
				.dfcc-sortable{list-style:none;margin:0;padding:0;}
				.dfcc-sec-row{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #e2e2e6;border-radius:8px;padding:10px 12px;margin:0 0 8px;}
				.dfcc-sec-row .dfcc-drag{cursor:move;color:#a7aaad;font-size:20px;}
				.dfcc-sec-row .dfcc-sec-name{flex:1;font-weight:600;}
				.dfcc-sec-row.dfcc-hidden{opacity:.55;}
				.dfcc-sortable-placeholder{border:2px dashed #FEC208;border-radius:8px;margin:0 0 8px;height:46px;background:#fffdf5;}
			</style>

			<ul class="dfcc-sortable" id="dfcc-section-sort">
				<?php
				$dfcc_spaces = array(
					'normal'   => __( 'Normal spacing', 'dog-father-control-center' ),
					'compact'  => __( 'Compact spacing', 'dog-father-control-center' ),
					'spacious' => __( 'Spacious spacing', 'dog-father-control-center' ),
				);
				$dfcc_sections = $module->sections();
				foreach ( $module->section_order() as $dfcc_slug ) :
					if ( ! isset( $dfcc_sections[ $dfcc_slug ] ) ) {
						continue;
					}
					$dfcc_show_key = 'show_' . $dfcc_slug;
					$dfcc_is_shown = ! isset( $settings[ $dfcc_show_key ] ) || ! empty( $settings[ $dfcc_show_key ] );
					$dfcc_bg       = isset( $settings[ 'sec_' . $dfcc_slug . '_bg' ] ) ? $settings[ 'sec_' . $dfcc_slug . '_bg' ] : '';
					$dfcc_space    = isset( $settings[ 'sec_' . $dfcc_slug . '_space' ] ) ? $settings[ 'sec_' . $dfcc_slug . '_space' ] : 'normal';
					?>
					<li class="dfcc-sec-row<?php echo $dfcc_is_shown ? '' : ' dfcc-hidden'; ?>">
						<span class="dfcc-drag dashicons dashicons-move" aria-hidden="true"></span>
						<input type="hidden" name="<?php echo esc_attr( $dfcc_name ); ?>[home_section_order][]" value="<?php echo esc_attr( $dfcc_slug ); ?>" />
						<label style="display:inline-flex;align-items:center;gap:6px;">
							<input type="checkbox" class="dfcc-sec-toggle" name="<?php echo esc_attr( $dfcc_name ); ?>[<?php echo esc_attr( $dfcc_show_key ); ?>]" value="1" <?php checked( $dfcc_is_shown ); ?> />
						</label>
						<span class="dfcc-sec-name"><?php echo esc_html( $dfcc_sections[ $dfcc_slug ] ); ?></span>
						<select name="<?php echo esc_attr( $dfcc_name ); ?>[sec_<?php echo esc_attr( $dfcc_slug ); ?>_space]">
							<?php foreach ( $dfcc_spaces as $sv => $sl ) : ?>
								<option value="<?php echo esc_attr( $sv ); ?>" <?php selected( $dfcc_space, $sv ); ?>><?php echo esc_html( $sl ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" class="dfcc-color-field" name="<?php echo esc_attr( $dfcc_name ); ?>[sec_<?php echo esc_attr( $dfcc_slug ); ?>_bg]" value="<?php echo esc_attr( $dfcc_bg ); ?>" data-default-color="" placeholder="<?php esc_attr_e( 'Background (optional)', 'dog-father-control-center' ); ?>" />
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php submit_button( __( 'Save Homepage', 'dog-father-control-center' ) ); ?>
	</form>
</div>
