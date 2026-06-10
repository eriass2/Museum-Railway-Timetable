<?php
/**
 * Timetable overview bus JSON: bus stop cells
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_timetable_bus_remote_station_label( array $branch_group, int $junction_id ): string {
	$remote_id = MRT_timetable_bus_remote_station_id( $branch_group, $junction_id );
	if ( $remote_id <= 0 ) {
		return '';
	}
	$station = get_post( $remote_id );
	if ( ! $station ) {
		return '';
	}
	return MRT_get_station_display_name( $station );
}

function MRT_timetable_bus_remote_station_id( array $branch_group, int $junction_id ): int {
	foreach ( (array) ( $branch_group['stations'] ?? array() ) as $station_id ) {
		$id = (int) $station_id;
		if ( $id > 0 && $id !== $junction_id ) {
			return $id;
		}
	}
	return 0;
}

function MRT_find_bus_service_in_branch( array $branch_group, string $service_number ): ?array {
	foreach ( (array) ( $branch_group['services'] ?? array() ) as $service_data ) {
		if ( MRT_connection_service_number( $service_data ) === $service_number ) {
			return $service_data;
		}
	}
	return null;
}

function MRT_timetable_bus_time_row_json(
	string $kind,
	string $label,
	array $services,
	array $info,
	array $connection,
	array $branch_group,
	string $stop_role,
	bool $use_departure,
	?array $display_columns = null
): array {
	$cells = array();
	if ( $display_columns === null ) {
		foreach ( $services as $idx => $service_data ) {
			unset( $service_data );
			$train_number = (string) ( $info[ $idx ]['service_number'] ?? '' );
			$cells[]      = MRT_timetable_bus_time_cell_json(
				$train_number,
				$connection,
				$branch_group,
				$stop_role,
				$use_departure
			);
		}
	} else {
		foreach ( $display_columns as $column ) {
			$idx          = (int) $column['primary_idx'];
			$train_number = (string) ( $info[ $idx ]['service_number'] ?? '' );
			$cells[]      = MRT_timetable_bus_time_cell_json(
				$train_number,
				$connection,
				$branch_group,
				$stop_role,
				$use_departure
			);
		}
	}

	return array(
		'kind'  => $kind,
		'label' => $label,
		'cells' => $cells,
	);
}

function MRT_timetable_bus_time_cell_json(
	string $train_number,
	array $connection,
	array $branch_group,
	string $stop_role,
	bool $use_departure
): array {
	$buses = MRT_connection_buses_for_train_number( $connection, $train_number );
	if ( $buses === array() ) {
		return array( 'text' => '—' );
	}

	$bus      = $buses[0];
	$bus_data = MRT_find_bus_service_in_branch( $branch_group, (string) $bus['service_number'] );
	return MRT_timetable_bus_time_cell_from_bus_data(
		$connection,
		$branch_group,
		$bus_data,
		$stop_role,
		$use_departure,
		(string) $bus['service_number']
	);
}

function MRT_timetable_bus_time_cell_from_bus_data(
	array $connection,
	array $branch_group,
	?array $bus_data,
	string $stop_role,
	bool $use_departure,
	string $bus_service_number
): array {
	if ( ! is_array( $bus_data ) ) {
		return array( 'text' => '—' );
	}
	$stop = MRT_timetable_bus_stop_for_role( $connection, $branch_group, $bus_data, $stop_role );
	return array(
		'text'             => MRT_timetable_bus_stop_display_time( $stop, $use_departure ),
		'busServiceNumber' => $bus_service_number,
	);
}

function MRT_timetable_bus_stop_for_role(
	array $connection,
	array $branch_group,
	?array $bus_data,
	string $stop_role
): ?array {
	if ( ! is_array( $bus_data ) ) {
		return null;
	}
	$junction_id = (int) ( $connection['junction_id'] ?? 0 );
	$remote_id   = MRT_timetable_bus_remote_station_id( $branch_group, $junction_id );
	$station_id  = $stop_role === 'junction' ? $junction_id : $remote_id;
	if ( $station_id <= 0 ) {
		return null;
	}
	$stop = $bus_data['stop_times'][ $station_id ] ?? null;
	return is_array( $stop ) ? $stop : null;
}

function MRT_timetable_bus_stop_display_time( $stop, bool $use_departure ): string {
	if ( ! is_array( $stop ) ) {
		return '—';
	}
	if ( $use_departure ) {
		$display = MRT_get_from_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	$display = MRT_get_to_row_display_stop_time( $stop );
	return MRT_format_stop_time_display( $display ?? $stop );
}
