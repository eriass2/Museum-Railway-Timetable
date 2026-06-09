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

require_once __DIR__ . '/overview-rail-columns.php';
require_once __DIR__ . '/overview-rail-cells.php';

function MRT_timetable_rail_group_to_json( array $group, string $dateYmd ): array {
	$view = MRT_prepare_timetable_group_view( $group, $dateYmd );

	$connection = null;
	if ( ! empty( $group['paired_branch'] ) ) {
		$connection = MRT_build_rail_bus_connection_data( $group, $group['paired_branch'] );
	}

	$from_label = $view['from_station'] ? MRT_station_from_label( $view['from_station'] ) : '';
	$to_label   = $view['to_station'] ? MRT_station_to_label( $view['to_station'] ) : '';

	return array(
		'kind'       => 'rail',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => $from_label,
		'toLabel'    => $to_label,
		'columns'    => MRT_timetable_overview_columns_json( $view ),
		'rows'       => MRT_timetable_overview_rail_rows_json(
			$view,
			$connection,
			! empty( $group['paired_branch'] ) ? $group['paired_branch'] : null
		),
	);
}

function MRT_timetable_overview_rail_rows_json( array $view, ?array $connection, ?array $branch_group = null ): array {
	$station_posts = $view['station_posts'];
	if ( $station_posts === array() ) {
		return array();
	}

	$services       = $view['services_list'];
	$info           = $view['service_info'];
	$grid_direction = is_array( $connection ) ? (string) ( $connection['direction'] ?? 'outbound' ) : 'outbound';
	$rows           = array();

	$first = $station_posts[0];
	$rows[] = MRT_timetable_overview_rail_endpoint_row_json( 'from', $first, $services, $info, true, false );

	foreach ( array_slice( $station_posts, 1, -1 ) as $station ) {
		foreach ( MRT_timetable_overview_rail_rows_for_station(
			$station,
			$grid_direction,
			$connection,
			$branch_group,
			$services,
			$info
		) as $row ) {
			$rows[] = $row;
		}
	}

	$last = end( $station_posts );
	$rows[] = MRT_timetable_overview_rail_endpoint_row_json( 'to', $last, $services, $info, false, true );

	return array_values( array_filter( $rows ) );
}

function MRT_timetable_overview_rail_endpoint_row_json(
	string $kind,
	WP_Post $station,
	array $services,
	array $info,
	bool $use_from_display,
	bool $use_to_display
): array {
	$label = $kind === 'from' ? MRT_station_from_label( $station ) : MRT_station_to_label( $station );

	return MRT_timetable_row_times_json(
		$kind,
		$label,
		(int) $station->ID,
		$services,
		$info,
		$use_from_display,
		$use_to_display
	);
}

function MRT_timetable_overview_rail_rows_for_station(
	WP_Post $station,
	string $grid_direction,
	?array $connection,
	?array $branch_group,
	array $services,
	array $info
): array {
	$station_id  = (int) $station->ID;
	$at_junction = MRT_timetable_station_is_bus_junction( $connection, $branch_group, $station_id );
	$rows        = array();

	if ( $at_junction && $grid_direction === 'inbound' ) {
		foreach ( MRT_timetable_junction_bus_rows_json( $services, $info, $connection, $branch_group ) as $bus_row ) {
			$rows[] = $bus_row;
		}
	}

	if ( MRT_station_row_has_arrival_departure_split( $station_id, $services ) ) {
		$rows[] = MRT_timetable_row_times_json(
			'arrival',
			MRT_station_to_label( $station ),
			$station_id,
			$services,
			$info,
			false,
			true
		);
		$transfer = MRT_timetable_train_change_row_json( $station, $services, $info );
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
			false
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
		false
	);

	if ( $at_junction && $grid_direction !== 'inbound' ) {
		foreach ( MRT_timetable_junction_bus_rows_json( $services, $info, $connection, $branch_group ) as $bus_row ) {
			$rows[] = $bus_row;
		}
	}

	return $rows;
}
