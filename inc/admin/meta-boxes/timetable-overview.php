<?php
/**
 * Timetable overview meta box
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render timetable overview preview box (lazy-loaded iframe).
 *
 * @param WP_Post $post Current post object (Timetable)
 */
function MRT_render_timetable_overview_box( $post ) {
	$timetable_id = (int) $post->ID;
	$preview_url  = function_exists( 'MRT_timetable_public_page_url' )
		? MRT_timetable_public_page_url( $timetable_id )
		: '';
	?>
	<div class="mrt-box mrt-timetable-overview-preview">
		<p class="description">
			<?php esc_html_e( 'Preview how the timetable looks on the public site. Loaded on demand to keep this screen fast.', 'museum-railway-timetable' ); ?>
		</p>
		<?php if ( $preview_url === '' ) : ?>
			<p class="description mrt-text-error">
				<?php esc_html_e( 'No public page linked yet. Run timetable page sync from the dashboard or dev reset.', 'museum-railway-timetable' ); ?>
			</p>
		<?php else : ?>
			<p class="mrt-mb-sm">
				<button type="button" class="button" id="mrt-load-timetable-preview" data-preview-url="<?php echo esc_url( $preview_url ); ?>">
					<?php esc_html_e( 'Show preview', 'museum-railway-timetable' ); ?>
				</button>
				<a href="<?php echo esc_url( $preview_url ); ?>" class="button button-link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Open public page', 'museum-railway-timetable' ); ?>
				</a>
			</p>
			<div id="mrt-timetable-preview-frame-wrap" class="mrt-hidden" hidden>
				<iframe
					id="mrt-timetable-preview-frame"
					class="mrt-timetable-preview-frame"
					title="<?php esc_attr_e( 'Timetable preview', 'museum-railway-timetable' ); ?>"
					loading="lazy"
					src="about:blank"
				></iframe>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
