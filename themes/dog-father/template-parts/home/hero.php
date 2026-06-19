<?php
/**
 * Home: hero banner.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$df_bg_id  = (int) dfather_home( 'hero_bg_id', 0 );
$df_bg_url = $df_bg_id ? wp_get_attachment_image_url( $df_bg_id, 'dfather-wide' ) : '';
// Fall back to a tasteful default banner so the hero never looks empty.
if ( ! $df_bg_url ) {
	$df_bg_url = dfather_default_image( 'hero' );
}

$df_eyebrow   = dfather_home( 'hero_eyebrow', __( 'Welcome to The Dog Father Hotel', 'dog-father' ) );
$df_title     = dfather_home( 'hero_title', __( 'Luxury Boarding & Five-Star Care for Your Best Friend', 'dog-father' ) );
$df_subtitle  = dfather_home( 'hero_subtitle', __( 'Private suites, 24/7 veterinary supervision, and a team that treats your dog like family.', 'dog-father' ) );
$df_p_label   = dfather_home( 'hero_primary_label', __( 'Book a Stay', 'dog-father' ) );
$df_p_url     = dfather_url( dfather_home( 'hero_primary_url', '/book-now/' ), '/book-now/' );
$df_s_label   = dfather_home( 'hero_secondary_label', __( 'Explore Services', 'dog-father' ) );
$df_s_url     = dfather_url( dfather_home( 'hero_secondary_url', '/services/' ), '/services/' );
?>
<section class="df-hero<?php echo $df_bg_url ? '' : ' df-hero--no-image'; ?>">
	<?php if ( $df_bg_url ) : ?>
		<div class="df-hero__bg" style="background-image:url('<?php echo esc_url( $df_bg_url ); ?>');"></div>
	<?php endif; ?>
	<div class="df-container">
		<div class="df-hero__inner df-reveal is-visible">
			<?php if ( $df_eyebrow ) : ?><span class="df-eyebrow"><?php echo esc_html( $df_eyebrow ); ?></span><?php endif; ?>
			<h1><?php echo esc_html( $df_title ); ?></h1>
			<p class="df-hero__subtitle"><?php echo esc_html( $df_subtitle ); ?></p>
			<div class="df-hero__cta">
				<a class="df-btn df-btn--primary" href="<?php echo esc_url( $df_p_url ); ?>"><?php echo esc_html( $df_p_label ); ?></a>
				<a class="df-btn df-btn--ghost" href="<?php echo esc_url( $df_s_url ); ?>"><?php echo esc_html( $df_s_label ); ?></a>
			</div>
		</div>
	</div>
	<span class="df-hero__scroll"><?php esc_html_e( 'Scroll', 'dog-father' ); ?></span>
</section>
