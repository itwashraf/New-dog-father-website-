<?php
/**
 * Home: FAQ accordion.
 *
 * @package DogFather
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! dfather_show( 'faq' ) ) {
	return;
}

$df_title = dfather_home( 'faq_title', __( 'Frequently Asked Questions', 'dog-father' ) );

$df_items = get_posts(
	array(
		'post_type'      => 'dfcc_faq',
		'posts_per_page' => 6,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);

$df_defaults = array(
	array( __( 'What vaccinations does my dog need?', 'dog-father' ), __( 'All guests must be up to date on core vaccinations. Our team will confirm requirements when you book.', 'dog-father' ) ),
	array( __( 'Will I receive updates while my dog stays?', 'dog-father' ), __( 'Yes — we send daily photos and updates so you always know your best friend is happy.', 'dog-father' ) ),
	array( __( 'Can you manage medication and special diets?', 'dog-father' ), __( 'Absolutely. Our trained staff and on-call vet handle medication, special diets and medical needs.', 'dog-father' ) ),
	array( __( 'How do I book and what is your cancellation policy?', 'dog-father' ), __( 'Book online in minutes. Flexible cancellation details are confirmed at the time of booking.', 'dog-father' ) ),
);
?>
<section class="df-section df-reveal" id="faq">
	<div class="df-container">
		<div class="df-section-head">
			<span class="df-eyebrow"><?php echo esc_html( dfather_home( 'faq_eyebrow', __( 'Questions', 'dog-father' ) ) ); ?></span>
			<h2 class="df-section-title"><?php echo esc_html( $df_title ); ?></h2>
		</div>
		<div class="df-faq">
			<?php
			if ( $df_items ) {
				foreach ( $df_items as $df_post ) {
					$answer = wp_strip_all_tags( get_the_content( null, false, $df_post ) );
					?>
					<div class="df-faq__item">
						<button class="df-faq__q" type="button"><?php echo esc_html( get_the_title( $df_post->ID ) ); ?></button>
						<div class="df-faq__a"><p><?php echo esc_html( $answer ); ?></p></div>
					</div>
					<?php
				}
			} else {
				foreach ( $df_defaults as $d ) :
					?>
					<div class="df-faq__item">
						<button class="df-faq__q" type="button"><?php echo esc_html( $d[0] ); ?></button>
						<div class="df-faq__a"><p><?php echo esc_html( $d[1] ); ?></p></div>
					</div>
					<?php
				endforeach;
			}
			?>
		</div>
	</div>
</section>
