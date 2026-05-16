<?php
/**
 * Dashboard: Statistics cards
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render dashboard statistics cards
 *
 * @param array $stats Stats array with keys: stations_count, routes_count, timetables_count, services_count, train_types_count
 */
function MRT_render_dashboard_stats( $stats ) {
	?>
	<div class="mrt-grid mrt-grid-auto mrt-my-lg">
		<?php MRT_render_dashboard_stat_card( $stats['stations_count'], __( 'Stations', 'museum-railway-timetable' ), admin_url( 'edit.php?post_type=mrt_station' ) ); ?>
		<?php MRT_render_dashboard_stat_card( $stats['routes_count'], __( 'Routes', 'museum-railway-timetable' ), admin_url( 'edit.php?post_type=mrt_route' ) ); ?>
		<?php MRT_render_dashboard_stat_card( $stats['timetables_count'], __( 'Timetables', 'museum-railway-timetable' ), admin_url( 'edit.php?post_type=mrt_timetable' ) ); ?>
		<?php MRT_render_dashboard_services_stat_card( $stats['services_count'] ); ?>
		<?php MRT_render_dashboard_stat_card( $stats['train_types_count'], __( 'Train Types', 'museum-railway-timetable' ), admin_url( 'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service' ) ); ?>
	</div>
	<?php
}

/**
 * Render one linked dashboard statistic card.
 *
 * @param int|string $count Statistic count
 * @param string     $label Label
 * @param string     $url Admin URL
 */
function MRT_render_dashboard_stat_card( $count, string $label, string $url ): void {
	?>
	<div class="mrt-card mrt-card--center">
		<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( (string) $count ); ?></div>
		<div class="mrt-text-muted mrt-mt-sm">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
		</div>
	</div>
	<?php
}

/**
 * Render services statistic card.
 *
 * @param int|string $count Service count
 */
function MRT_render_dashboard_services_stat_card( $count ): void {
	?>
	<div class="mrt-card mrt-card--center">
		<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( (string) $count ); ?></div>
		<div class="mrt-text-muted mrt-mt-sm">
			<?php esc_html_e( 'Trips (Services)', 'museum-railway-timetable' ); ?>
			<span class="mrt-block mrt-text-small mrt-mt-xs mrt-opacity-85">
				<?php esc_html_e( 'Managed via Timetables', 'museum-railway-timetable' ); ?>
			</span>
		</div>
	</div>
	<?php
}
