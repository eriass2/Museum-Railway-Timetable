<?php
/**
 * Dashboard: Quick actions
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render quick actions section
 */
function MRT_render_dashboard_quick_actions() {
	?>
	<div class="mrt-section">
		<h2><?php esc_html_e( 'Quick Actions', 'museum-railway-timetable' ); ?></h2>
		<p><?php esc_html_e( 'Create core timetable content or review existing station data.', 'museum-railway-timetable' ); ?></p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_station' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Station', 'museum-railway-timetable' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_route' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Route', 'museum-railway-timetable' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_timetable' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Timetable', 'museum-railway-timetable' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_station&mrt_view=overview' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Stations Overview', 'museum-railway-timetable' ); ?></a>
		</p>
	</div>
	<?php
}
