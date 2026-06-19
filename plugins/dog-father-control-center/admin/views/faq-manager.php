<?php
/**
 * FAQ Manager view.
 *
 * A single, friendly form to create, edit, reorder, and delete the frequently
 * asked questions shown on the homepage FAQ accordion. The post title is the
 * question and the content is the answer.
 *
 * @package DogFatherControlCenter
 * @var WP_Post[] $faqs Existing FAQs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dfcc_saved = isset( $_GET['dfcc_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * Render one FAQ row.
 *
 * @param string  $key  Row key (post ID, or "newN").
 * @param WP_Post $post FAQ post or null for the blank "add" row.
 * @return void
 */
$dfcc_faq_row = static function ( $key, $post ) {
	$is_new   = ( null === $post );
	$question = $is_new ? '' : $post->post_title;
	$answer   = $is_new ? '' : $post->post_content;
	$b        = 'faq[' . esc_attr( $key ) . ']';
	?>
	<div class="dfcc-faq-card<?php echo $is_new ? ' dfcc-faq-new' : ''; ?>">
		<p>
			<label><strong><?php esc_html_e( 'Question', 'dog-father-control-center' ); ?></strong></label>
			<input type="text" class="widefat" name="<?php echo $b; ?>[question]" value="<?php echo esc_attr( $question ); ?>" placeholder="<?php esc_attr_e( 'e.g. What vaccinations does my dog need?', 'dog-father-control-center' ); ?>" />
		</p>
		<p>
			<label><strong><?php esc_html_e( 'Answer', 'dog-father-control-center' ); ?></strong></label>
			<textarea class="widefat" rows="3" name="<?php echo $b; ?>[answer]" placeholder="<?php esc_attr_e( 'Write a short, clear answer…', 'dog-father-control-center' ); ?>"><?php echo esc_textarea( $answer ); ?></textarea>
		</p>
		<?php if ( ! $is_new ) : ?>
			<label class="dfcc-faq-delete"><input type="checkbox" name="<?php echo $b; ?>[delete]" value="1" /> <?php esc_html_e( 'Delete this FAQ', 'dog-father-control-center' ); ?></label>
		<?php endif; ?>
	</div>
	<?php
};
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Manage FAQs', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Add the questions your customers ask most. They appear in the FAQ section on your homepage. Edit them all here, then click Save.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<?php if ( $dfcc_saved ) : ?>
		<div class="dfcc-alert"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'FAQs saved and cache cleared.', 'dog-father-control-center' ); ?></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="dfcc_save_faqs" />
		<?php wp_nonce_field( 'dfcc_save_faqs' ); ?>

		<style>
			.dfcc-faq-card{background:#fff;border:1px solid #e2e2e6;border-left:4px solid var(--dfcc-gold,#FEC208);border-radius:10px;padding:16px 18px;margin:0 0 16px;}
			.dfcc-faq-new{border-left-color:var(--dfcc-orange,#FF2D08);background:#fffdf5;}
			.dfcc-faq-card p{margin:0 0 10px;}
			.dfcc-faq-card label{display:block;margin-bottom:4px;}
			.dfcc-faq-delete{color:#cf240a;font-weight:600;display:inline-flex;align-items:center;gap:6px;}
			.dfcc-faq-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);padding:14px 0;border-top:1px solid #e2e2e6;margin-top:6px;display:flex;gap:12px;align-items:center;}
		</style>

		<h2><?php esc_html_e( 'Your FAQs', 'dog-father-control-center' ); ?></h2>
		<?php
		if ( empty( $faqs ) ) {
			echo '<p>' . esc_html__( 'No FAQs yet — add your first one below.', 'dog-father-control-center' ) . '</p>';
		}
		foreach ( $faqs as $dfcc_faq ) {
			$dfcc_faq_row( (string) $dfcc_faq->ID, $dfcc_faq );
		}
		?>

		<h2><?php esc_html_e( 'Add a new FAQ', 'dog-father-control-center' ); ?></h2>
		<?php $dfcc_faq_row( 'new1', null ); ?>

		<div class="dfcc-faq-actions">
			<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save FAQs', 'dog-father-control-center' ); ?></button>
			<span class="description"><?php esc_html_e( 'Saving also clears your site cache so changes show immediately.', 'dog-father-control-center' ); ?></span>
		</div>
	</form>
</div>
