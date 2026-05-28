<?php
/**
 * Branch shuttle timetables (two stations, bus only) — one row per trip.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid-connections.php';

/**
 * Two-station bus routes use a compact table (not one column per service).
 *
 * @param array<string, mixed> $group Route group from MRT_group_services_by_route.
 */
function MRT_timetable_group_is_branch_shuttle( array $group ): bool {
	$stations = $group['stations'] ?? array();
	if ( count( $stations ) !== 2 ) {
		return false;
	}

	$services = $group['services'] ?? array();
	if ( $services === array() ) {
		return false;
	}

	foreach ( $services as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( ! $train_type || $train_type->slug !== 'buss' ) {
			return false;
		}
	}

	return true;
}

/**
 * @param array<string, mixed> $connections From MRT_build_rail_bus_connection_data.
 */
function MRT_branch_trains_label_for_bus( array $service_data, ?array $connections ): string {
	if ( ! $connections ) {
		return '—';
	}

	$number = MRT_connection_service_number( $service_data );
	foreach ( $connections['bus_to_train'] as $row ) {
		if ( (string) $row['bus']['service_number'] === $number ) {
			return MRT_format_connection_service_list( $row['trains'], 'train' );
		}
	}

	return '—';
}

/**
 * @param array<string, mixed> $view From MRT_prepare_timetable_group_view.
 * @param array<string, mixed>|null $connections
 */
function MRT_render_branch_timetable_rows( array $view, ?array $connections ): string {
	$from_station = $view['from_station'];
	$to_station   = $view['to_station'];
	if ( ! $from_station || ! $to_station ) {
		return '';
	}

	$from_id = (int) $from_station->ID;
	$to_id   = (int) $to_station->ID;
	$html    = '';

	foreach ( $view['services_list'] as $idx => $service_data ) {
		$stop_times = $service_data['stop_times'] ?? array();
		$from_stop  = $stop_times[ $from_id ] ?? null;
		$to_stop    = $stop_times[ $to_id ] ?? null;
		$dep        = MRT_format_stop_time_display( MRT_get_from_row_display_stop_time( $from_stop ) );
		$arr        = MRT_format_stop_time_display( MRT_get_to_row_display_stop_time( $to_stop ) );
		$info       = $view['service_info'][ $idx ] ?? array();
		$tur        = (string) ( $info['service_number'] ?? '' );
		$trains     = MRT_branch_trains_label_for_bus( $service_data, $connections );

		$html .= '<tr class="mrt-branch-timetable__row">';
		$html .= '<th scope="row" class="mrt-branch-timetable__tur">' . esc_html( $tur ) . '</th>';
		$html .= '<td class="mrt-branch-timetable__time">' . esc_html( $dep ) . '</td>';
		$html .= '<td class="mrt-branch-timetable__time">' . esc_html( $arr ) . '</td>';
		$html .= '<td class="mrt-branch-timetable__trains">' . esc_html( $trains ) . '</td>';
		$html .= '</tr>';
	}

	return $html;
}

/**
 * Render a branch shuttle group as Tur | Från | Till | Tåg table.
 *
 * @param array<string, mixed> $group Route group.
 * @param string               $dateYmd Date YYYY-MM-DD.
 */
function MRT_render_timetable_group_branch( array $group, string $dateYmd ): string {
	$view = MRT_prepare_timetable_group_view( $group, $dateYmd );

	$route_label      = $view['route_label'];
	$from_station     = $view['from_station'];
	$to_station       = $view['to_station'];
	$group_heading_id = $view['group_heading_id'];

	if ( ! $from_station || ! $to_station ) {
		return '';
	}

	$connections = null;
	if ( ! empty( $group['paired_rail'] ) ) {
		$connections = MRT_build_rail_bus_connection_data( $group['paired_rail'], $group );
	}

	$from_label = sprintf(
		/* translators: %s: station name, may include * for bus stop */
		__( 'Från %s', 'museum-railway-timetable' ),
		MRT_get_station_display_name( $from_station )
	);
	$to_label   = sprintf(
		/* translators: %s: station name, may include * for bus stop */
		__( 'Till %s', 'museum-railway-timetable' ),
		MRT_get_station_display_name( $to_station )
	);

	ob_start();
	?>
	<div class="mrt-timetable-group mrt-timetable-group--branch">
		<div class="mrt-route-header">
			<h3 class="mrt-route-header-main" id="<?php echo esc_attr( $group_heading_id ); ?>">
				<?php echo esc_html( $route_label ); ?>
			</h3>
			<p class="mrt-route-header-branch-note">
				<?php esc_html_e( 'Anslutningsbuss', 'museum-railway-timetable' ); ?>
			</p>
			<div class="mrt-route-header-details">
				<span class="mrt-route-from"><?php echo esc_html( $from_label ); ?></span>
				<span class="mrt-route-separator" aria-hidden="true">→</span>
				<span class="mrt-route-to"><?php echo esc_html( $to_label ); ?></span>
			</div>
		</div>

		<div class="mrt-branch-timetable-wrap" role="group" aria-labelledby="<?php echo esc_attr( $group_heading_id ); ?>">
			<table class="mrt-branch-timetable">
				<thead>
					<tr>
						<th scope="col" class="mrt-branch-timetable__col-tur">
							<?php esc_html_e( 'Tur', 'museum-railway-timetable' ); ?>
						</th>
						<th scope="col" class="mrt-branch-timetable__col-time">
							<?php echo esc_html( $from_label ); ?>
						</th>
						<th scope="col" class="mrt-branch-timetable__col-time">
							<?php echo esc_html( $to_label ); ?>
						</th>
						<th scope="col" class="mrt-branch-timetable__col-trains">
							<?php esc_html_e( 'Anslutande tåg', 'museum-railway-timetable' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php echo MRT_render_branch_timetable_rows( $view, $connections ); ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
