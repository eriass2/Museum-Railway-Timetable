<?php
/**
 * Timetable overview bus JSON: junction rows
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_timetable_station_is_bus_junction( ?array $connection, ?array $branch_group, int $station_id ): bool {
	if ( ! is_array( $connection ) || ! is_array( $branch_group ) ) {
		return false;
	}
	if ( ! MRT_connection_has_any_buses( $connection ) ) {
		return false;
	}
	return (int) ( $connection['junction_id'] ?? 0 ) === $station_id;
}

function MRT_connection_has_any_buses( array $connection ): bool {
	foreach ( (array) ( $connection['train_to_bus'] ?? array() ) as $row ) {
		if ( ! empty( $row['buses'] ) ) {
			return true;
		}
	}
	return false;
}

function MRT_timetable_junction_bus_rows_json(
	array $services,
	array $info,
	array $connection,
	array $branch_group
): array {
	$junction_id    = (int) ( $connection['junction_id'] ?? 0 );
	$junction_label = (string) ( $connection['junction_label'] ?? '' );
	$remote_label   = MRT_timetable_bus_remote_station_label( $branch_group, $junction_id );
	if ( $junction_label === '' || $remote_label === '' ) {
		return array();
	}

	$inbound = (string) ( $connection['direction'] ?? 'outbound' ) === 'inbound';
	if ( $inbound ) {
		return array(
			MRT_timetable_bus_time_row_json(
				'busDeparture',
				MRT_from_place_label( $remote_label ),
				$services,
				$info,
				$connection,
				$branch_group,
				'remote',
				true
			),
			MRT_timetable_bus_time_row_json(
				'busArrival',
				MRT_to_place_label( $junction_label ),
				$services,
				$info,
				$connection,
				$branch_group,
				'junction',
				false
			),
		);
	}

	return array(
		MRT_timetable_bus_time_row_json(
			'busDeparture',
			MRT_from_place_label( $junction_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'junction',
			true
		),
		MRT_timetable_bus_time_row_json(
			'busArrival',
			MRT_to_place_label( $remote_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'remote',
			false
		),
	);
}
