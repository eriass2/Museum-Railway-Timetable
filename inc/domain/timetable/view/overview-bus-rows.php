<?php
/**
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed>|null $connection
 * @param array<string, mixed>|null $branch_group
 */
function MRT_timetable_station_is_bus_junction( ?array $connection, ?array $branch_group, int $station_id ): bool {
	if ( ! is_array( $connection ) || ! is_array( $branch_group ) ) {
		return false;
	}
	if ( ! MRT_connection_has_any_buses( $connection ) ) {
		return false;
	}
	return (int) ( $connection['junction_id'] ?? 0 ) === $station_id;
}

/**
 * @param array<string, mixed> $connection
 */
function MRT_connection_has_any_buses( array $connection ): bool {
	foreach ( (array) ( $connection['train_to_bus'] ?? array() ) as $row ) {
		if ( ! empty( $row['buses'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @return array<int, array<string, mixed>>
 */
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
				sprintf( __( 'Från %s', 'museum-railway-timetable' ), $remote_label ),
				$services,
				$info,
				$connection,
				$branch_group,
				'remote',
				true
			),
			MRT_timetable_bus_time_row_json(
				'busArrival',
				sprintf( __( 'Till %s', 'museum-railway-timetable' ), $junction_label ),
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
			sprintf( __( 'Från %s', 'museum-railway-timetable' ), $junction_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'junction',
			true
		),
		MRT_timetable_bus_time_row_json(
			'busArrival',
			sprintf( __( 'Till %s', 'museum-railway-timetable' ), $remote_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'remote',
			false
		),
	);
}

/**
 * @param array<string, mixed> $branch_group
 */
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

/**
 * @param array<string, mixed> $branch_group
 */
function MRT_timetable_bus_remote_station_id( array $branch_group, int $junction_id ): int {
	foreach ( (array) ( $branch_group['stations'] ?? array() ) as $station_id ) {
		$id = (int) $station_id;
		if ( $id > 0 && $id !== $junction_id ) {
			return $id;
		}
	}
	return 0;
}

/**
 * @param array<string, mixed> $branch_group
 * @return array<string, mixed>|null
 */
function MRT_find_bus_service_in_branch( array $branch_group, string $service_number ): ?array {
	foreach ( (array) ( $branch_group['services'] ?? array() ) as $service_data ) {
		if ( MRT_connection_service_number( $service_data ) === $service_number ) {
			return $service_data;
		}
	}
	return null;
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @return array<string, mixed>
 */
function MRT_timetable_bus_time_row_json(
	string $kind,
	string $label,
	array $services,
	array $info,
	array $connection,
	array $branch_group,
	string $stop_role,
	bool $use_departure
): array {
	$cells = array();
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

	return array(
		'kind'  => $kind,
		'label' => $label,
		'cells' => $cells,
	);
}

/**
 * @param array<string, mixed> $connection
 * @param array<string, mixed> $branch_group
 * @return array<string, mixed>
 */
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

	$bus       = $buses[0];
	$bus_data  = MRT_find_bus_service_in_branch( $branch_group, (string) $bus['service_number'] );
	$stop      = MRT_timetable_bus_stop_for_role( $connection, $branch_group, $bus_data, $stop_role );
	$cell      = array(
		'text'             => MRT_timetable_bus_stop_display_time( $stop, $use_departure ),
		'busServiceNumber' => (string) $bus['service_number'],
	);
	return $cell;
}

/**
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @param array<string, mixed>|null        $bus_data
 * @return array<string, mixed>|null
 */
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

/**
 * @param array<string, mixed>|null $stop
 */
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
