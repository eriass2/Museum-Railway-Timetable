<?php
/**
 * Journey wizard shortcode helpers.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a timetable for the optional printed overview in the wizard.
 *
 * @param array<string,mixed> $atts Parsed shortcode attributes
 * @return int Timetable post ID or 0
 */
function MRT_journey_wizard_resolve_timetable_id( array $atts ): int {
	$timetable_id = isset( $atts['timetable_id'] ) ? intval( $atts['timetable_id'] ) : 0;
	if ( $timetable_id > 0 ) {
		return $timetable_id;
	}

	$timetable_title = isset( $atts['timetable'] ) && is_string( $atts['timetable'] ) ? trim( $atts['timetable'] ) : '';
	if ( $timetable_title === '' ) {
		return 0;
	}

	$timetable_post = MRT_get_post_by_title( $timetable_title, 'mrt_timetable' );
	return $timetable_post ? intval( $timetable_post->ID ) : 0;
}

/**
 * Optional full timetable block for the wizard first step.
 */
function MRT_render_journey_wizard_timetable_drawer( int $timetable_id ): void {
	if ( $timetable_id <= 0 ) {
		return;
	}
	?>
	<details class="mrt-journey-wizard__timetable">
		<summary class="mrt-journey-wizard__timetable-summary">
			<?php esc_html_e( 'Visa tidtabell', 'museum-railway-timetable' ); ?>
		</summary>
		<div class="mrt-journey-wizard__timetable-body">
			<?php echo MRT_render_timetable_overview( $timetable_id ); ?>
		</div>
	</details>
	<?php
}
