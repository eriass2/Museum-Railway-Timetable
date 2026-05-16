<?php
/**
 * Dashboard: Quick start guide
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render quick start guide (collapsible)
 */
function MRT_render_dashboard_guide() {
	?>
	<div class="mrt-section mrt-bg-info">
		<h2 class="mrt-cursor-pointer" onclick="jQuery(this).next().slideToggle();">
			<?php esc_html_e( '📖 Quick Start Guide', 'museum-railway-timetable' ); ?>
			<span class="mrt-text-xs mrt-text-muted">(<?php esc_html_e( 'Click to expand', 'museum-railway-timetable' ); ?>)</span>
		</h2>
		<div class="mrt-guide-content mrt-mt-1">
			<p><strong><?php esc_html_e( 'Recommended workflow:', 'museum-railway-timetable' ); ?></strong></p>
			<ol class="mrt-ml-lg mrt-leading-relaxed">
				<li><strong><?php esc_html_e( 'Stations', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'Create all stations where trains stop', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Routes', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'Create routes and add stations in order (use ↑ ↓ buttons to reorder)', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Train Types', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'Optional but recommended: create terms such as Ångtåg, Dieseltåg, Rälsbuss (used for icons and filters)', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Timetables', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'Create timetables and add dates (YYYY-MM-DD) when they apply', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Add Trips', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'On the timetable edit screen, open "Trips (Services)". Select Route, Train Type, and Destination (end station), then click "Add Trip".', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Set Train Number', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'When editing a trip (Service), enter the train number (e.g., 71, 91, 73) in the "Train Number" field. This will be displayed in timetables instead of the service ID.', 'museum-railway-timetable' ); ?></li>
				<li><strong><?php esc_html_e( 'Configure Stop Times', 'museum-railway-timetable' ); ?></strong> - <?php esc_html_e( 'Click "Edit" on any trip to set arrival/departure times per station. Restrict pickup/dropoff with P/A; leave both times empty while the train still stops to show X; use | when the train does not stop.', 'museum-railway-timetable' ); ?></li>
			</ol>
			<p class="mrt-alert mrt-alert-warning mrt-mt-1">
				<strong><?php esc_html_e( '💡 Tip:', 'museum-railway-timetable' ); ?></strong> <?php esc_html_e( 'Trip titles are generated from the route and service data—you normally do not type a trip name yourself.', 'museum-railway-timetable' ); ?>
			</p>
		</div>
	</div>
	<?php
}
