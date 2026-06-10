<?php
/**
 * Timetable overview rail JSON: row assembly
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/overview-column-merge.php';
require_once __DIR__ . '/overview-rail-columns.php';
require_once __DIR__ . '/overview-rail-cells.php';

function MRT_timetable_rail_group_to_json( array $group, string $dateYmd ): array {
	$view             = MRT_prepare_timetable_group_view( $group, $dateYmd );
	$display_columns  = MRT_timetable_build_display_columns( $view );
	$paired_branches  = MRT_timetable_rail_paired_branches( $group );
	$from_label       = $view['from_station'] ? MRT_station_from_label( $view['from_station'] ) : '';
	$to_label         = $view['to_station'] ? MRT_station_to_label( $view['to_station'] ) : '';

	return array(
		'kind'       => 'rail',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => $from_label,
		'toLabel'    => $to_label,
		'columns'    => MRT_timetable_overview_columns_json( $view, $display_columns ),
		'rows'       => MRT_timetable_overview_rail_rows_json( $view, $group, $paired_branches, $display_columns ),
	);
}

/**
 * @param array<int, array<string, mixed>> $paired_branches
 */
function MRT_timetable_overview_rail_rows_json(
	array $view,
	array $rail_group,
	array $paired_branches,
	array $display_columns
): array {
	$station_posts = $view['station_posts'];
	if ( $station_posts === array() ) {
		return array();
	}

	$services       = $view['services_list'];
	$info           = $view['service_info'];
	$grid_direction = MRT_timetable_rail_grid_direction_for_branches( $rail_group, $paired_branches );
	$rows           = array();

	$first = $station_posts[0];
	$rows[] = MRT_timetable_overview_rail_endpoint_row_json(
		'from',
		$first,
		$services,
		$info,
		true,
		false,
		$display_columns,
		$station_posts
	);

	foreach ( array_slice( $station_posts, 1, -1 ) as $station ) {
		foreach ( MRT_timetable_overview_rail_rows_for_station(
			$station,
			$grid_direction,
			$rail_group,
			$paired_branches,
			$services,
			$info,
			$display_columns,
			$station_posts
		) as $row ) {
			$rows[] = $row;
		}
	}

	$last = end( $station_posts );
	$rows[] = MRT_timetable_overview_rail_endpoint_row_json(
		'to',
		$last,
		$services,
		$info,
		false,
		true,
		$display_columns,
		$station_posts
	);

	return array_values( array_filter( $rows ) );
}

/**
 * @param array<int, array<string, mixed>> $paired_branches
 */
function MRT_timetable_rail_grid_direction_for_branches( array $rail_group, array $paired_branches ): string {
	if ( $paired_branches === array() ) {
		return 'outbound';
	}
	$connection = MRT_build_rail_bus_connection_data( $rail_group, $paired_branches[0] );
	return (string) ( $connection['direction'] ?? 'outbound' );
}

function MRT_timetable_overview_rail_endpoint_row_json(
	string $kind,
	WP_Post $station,
	array $services,
	array $info,
	bool $use_from_display,
	bool $use_to_display,
	array $display_columns,
	array $station_posts
): array {
	$label = $kind === 'from' ? MRT_station_from_label( $station ) : MRT_station_to_label( $station );

	return MRT_timetable_row_times_json(
		$kind,
		$label,
		(int) $station->ID,
		$services,
		$info,
		$use_from_display,
		$use_to_display,
		$display_columns,
		$station_posts
	);
}

/**
 * @param array<int, array<string, mixed>> $paired_branches
 */
function MRT_timetable_overview_rail_rows_for_station(
	WP_Post $station,
	string $grid_direction,
	array $rail_group,
	array $paired_branches,
	array $services,
	array $info,
	array $display_columns,
	array $station_posts
): array {
	$station_id = (int) $station->ID;
	$rows       = array();

	if ( $grid_direction === 'inbound' ) {
		$rows = MRT_timetable_append_junction_bus_rows(
			$rows,
			$rail_group,
			$paired_branches,
			$services,
			$info,
			$station_id,
			$display_columns
		);
	}

	if ( MRT_station_row_has_arrival_departure_split( $station_id, $services ) ) {
		$rows[] = MRT_timetable_row_times_json(
			'arrival',
			MRT_station_to_label( $station ),
			$station_id,
			$services,
			$info,
			false,
			true,
			$display_columns,
			$station_posts
		);
		$transfer = MRT_timetable_train_change_row_json( $station, $services, $info, $display_columns );
		if ( $transfer !== null ) {
			$rows[] = $transfer;
		}
		$rows[] = MRT_timetable_row_times_json(
			'departure',
			MRT_station_from_label( $station ),
			$station_id,
			$services,
			$info,
			true,
			false,
			$display_columns,
			$station_posts
		);
		return $rows;
	}

	$rows[] = MRT_timetable_row_times_json(
		'station',
		MRT_get_station_display_name( $station ),
		$station_id,
		$services,
		$info,
		false,
		false,
		$display_columns,
		$station_posts
	);

	if ( $grid_direction !== 'inbound' ) {
		$rows = MRT_timetable_append_junction_bus_rows(
			$rows,
			$rail_group,
			$paired_branches,
			$services,
			$info,
			$station_id,
			$display_columns
		);
	}

	return $rows;
}

/**
 * @param array<int, array<string, mixed>> $paired_branches
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_append_junction_bus_rows(
	array $rows,
	array $rail_group,
	array $paired_branches,
	array $services,
	array $info,
	int $station_id,
	array $display_columns
): array {
	unset( $services );
	foreach ( MRT_timetable_junction_bus_rows_for_station(
		$info,
		$rail_group,
		$paired_branches,
		$station_id,
		$display_columns
	) as $bus_row ) {
		$rows[] = $bus_row;
	}
	return $rows;
}
