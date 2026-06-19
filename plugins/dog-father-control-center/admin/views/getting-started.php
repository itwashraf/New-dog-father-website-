<?php
/**
 * Getting Started view — guided setup checklist.
 *
 * @package DogFatherControlCenter
 * @var array[] $items   Checklist items.
 * @var int     $done    Completed count.
 * @var int     $total   Total count.
 * @var int     $percent Completion percentage.
 * @var array   $brand   Provada brand (name, url).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap dfcc-wrap">
	<div class="dfcc-header">
		<div>
			<h1 class="dfcc-title"><span class="dashicons dashicons-flag"></span> <?php esc_html_e( 'Getting Started', 'dog-father-control-center' ); ?></h1>
			<p class="dfcc-subtitle"><?php esc_html_e( 'Welcome! Follow these steps and your website will be ready. You can always change everything later.', 'dog-father-control-center' ); ?></p>
		</div>
	</div>

	<style>
		.dfcc-progress{background:#fff;border:1px solid #e2e2e6;border-radius:12px;padding:18px 20px;margin:0 0 18px;}
		.dfcc-progress__bar{height:12px;border-radius:999px;background:#eef0f2;overflow:hidden;margin-top:10px;}
		.dfcc-progress__fill{height:100%;background:linear-gradient(90deg,#FEC208,#FF2D08);transition:width .4s ease;}
		.dfcc-steps{display:grid;gap:12px;}
		.dfcc-step{display:flex;align-items:flex-start;gap:14px;background:#fff;border:1px solid #e2e2e6;border-left:4px solid #dcdce0;border-radius:10px;padding:16px 18px;}
		.dfcc-step.is-done{border-left-color:#46b450;background:#f7fdf8;}
		.dfcc-step__check{flex:0 0 auto;width:26px;height:26px;border-radius:50%;display:grid;place-items:center;background:#eef0f2;color:#8a8a92;}
		.dfcc-step.is-done .dfcc-step__check{background:#46b450;color:#fff;}
		.dfcc-step__body{flex:1;}
		.dfcc-step__body h3{margin:0 0 4px;font-size:15px;}
		.dfcc-step__body p{margin:0;color:#646970;}
		.dfcc-step__action{flex:0 0 auto;}
	</style>

	<div class="dfcc-progress">
		<strong>
			<?php
			/* translators: 1: completed steps, 2: total steps. */
			echo esc_html( sprintf( __( 'Setup progress — %1$d of %2$d done', 'dog-father-control-center' ), $done, $total ) );
			?>
			(<?php echo esc_html( $percent ); ?>%)
		</strong>
		<div class="dfcc-progress__bar"><div class="dfcc-progress__fill" style="width:<?php echo esc_attr( $percent ); ?>%;"></div></div>
	</div>

	<div class="dfcc-steps">
		<?php foreach ( $items as $i => $item ) : ?>
			<div class="dfcc-step<?php echo $item['done'] ? ' is-done' : ''; ?>">
				<span class="dfcc-step__check"><span class="dashicons dashicons-<?php echo $item['done'] ? 'yes' : 'arrow-right-alt2'; ?>"></span></span>
				<div class="dfcc-step__body">
					<h3><?php echo esc_html( ( $i + 1 ) . '. ' . $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['text'] ); ?></p>
				</div>
				<span class="dfcc-step__action">
					<a class="button <?php echo $item['done'] ? '' : 'button-primary'; ?>" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['button'] ); ?></a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="dfcc-panel" style="margin-top:18px;">
		<h2 class="dfcc-section-title"><?php esc_html_e( 'Need a hand?', 'dog-father-control-center' ); ?></h2>
		<p><?php esc_html_e( 'The Help & Docs page explains exactly where to change every part of your website, in plain language.', 'dog-father-control-center' ); ?></p>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-help' ) ); ?>"><?php esc_html_e( 'Open Help & Docs', 'dog-father-control-center' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=dfcc-home' ) ); ?>"><?php esc_html_e( 'Edit Homepage', 'dog-father-control-center' ); ?></a>
	</div>

	<p style="color:#8a8a92;margin-top:16px;">
		<?php
		printf(
			/* translators: 1: Provada link. */
			esc_html__( 'This control center and theme were built by %s.', 'dog-father-control-center' ),
			'<a href="' . esc_url( $brand['url'] ) . '" target="_blank" rel="noopener"><strong>' . esc_html( $brand['name'] ) . '</strong></a>'
		);
		?>
	</p>
</div>
