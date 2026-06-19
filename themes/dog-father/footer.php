<?php
/**
 * Site footer.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$df_name     = dfather_info( 'business_name', get_bloginfo( 'name' ) );
$df_tagline  = dfather_info( 'tagline', get_bloginfo( 'description' ) );
$df_phone    = dfather_info( 'phone', '' );
$df_whatsapp = dfather_info( 'whatsapp', '' );
$df_email    = dfather_info( 'email', '' );
$df_address  = dfather_info( 'address', '' );
?>
</div><!-- #df-content -->

<footer class="df-footer" id="df-footer">
	<div class="df-footer__inner">
		<div class="df-footer__col">
			<h3 class="df-footer__title df-gradient-text"><?php echo esc_html( $df_name ); ?></h3>
			<?php if ( $df_tagline ) : ?><p class="df-footer__tag"><?php echo esc_html( $df_tagline ); ?></p><?php endif; ?>
			<?php
			$df_social = function_exists( 'dfather_social_links' ) ? dfather_social_links() : array();
			if ( $df_whatsapp || $df_social ) :
				?>
				<div class="df-footer__social">
					<?php if ( $df_whatsapp ) : ?>
						<a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $df_whatsapp ) ); ?>" aria-label="WhatsApp" target="_blank" rel="noopener">
							<span class="dashicons dashicons-whatsapp"></span>
						</a>
					<?php endif; ?>
					<?php foreach ( $df_social as $df_link ) : ?>
						<a href="<?php echo esc_url( $df_link['url'] ); ?>" aria-label="<?php echo esc_attr( $df_link['label'] ); ?>" target="_blank" rel="noopener">
							<span class="dashicons <?php echo esc_attr( $df_link['icon'] ); ?>"></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="df-footer__col">
			<h4 class="df-footer__heading"><?php esc_html_e( 'Explore', 'dog-father' ); ?></h4>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'df-footer__menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			}
			?>
		</div>

		<div class="df-footer__col">
			<h4 class="df-footer__heading"><?php esc_html_e( 'Contact', 'dog-father' ); ?></h4>
			<ul>
				<?php if ( $df_phone ) : ?><li><a href="<?php echo esc_attr( dfather_tel( $df_phone ) ); ?>"><?php echo esc_html( $df_phone ); ?></a></li><?php endif; ?>
				<?php if ( $df_email ) : ?><li><a href="mailto:<?php echo esc_attr( $df_email ); ?>"><?php echo esc_html( $df_email ); ?></a></li><?php endif; ?>
				<?php if ( $df_address ) : ?><li><?php echo esc_html( $df_address ); ?></li><?php endif; ?>
			</ul>
		</div>

		<div class="df-footer__col">
			<h4 class="df-footer__heading"><?php esc_html_e( 'Hours', 'dog-father' ); ?></h4>
			<p class="df-footer__tag"><?php echo nl2br( esc_html( dfather_info( 'opening_hours', __( 'Open 24 / 7', 'dog-father' ) ) ) ); ?></p>
		</div>
	</div>

	<div class="df-footer__bottom">
		<p>
			<?php
			printf(
				/* translators: 1: year, 2: business name. */
				esc_html__( '© %1$s %2$s. All rights reserved.', 'dog-father' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( $df_name )
			);
			?>
		</p>
		<?php
		$df_credit_text = dfather_info( 'footer_credit_text', 'Provada' );
		$df_credit_url  = dfather_info( 'footer_credit_url', 'https://provada.net' );
		if ( ! dfather_info( 'hide_footer_credit', '' ) && $df_credit_text ) :
			?>
			<p class="df-credit">
				<?php esc_html_e( 'Designed & developed by', 'dog-father' ); ?>
				<?php if ( $df_credit_url ) : ?>
					<a href="<?php echo esc_url( $df_credit_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $df_credit_text ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $df_credit_text ); ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
</footer>

<?php if ( $df_whatsapp && ( ! function_exists( 'dfather_style' ) || '0' !== (string) dfather_style( 'whatsapp_float', '1' ) ) ) : ?>
	<a class="df-wa-float" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $df_whatsapp ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'dog-father' ); ?>">
		<span class="dashicons dashicons-whatsapp"></span>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
