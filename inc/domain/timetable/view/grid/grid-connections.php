<?php
/**
 * Rail ↔ branch bus connection matching and panels (separate timetables).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/station/station-timetable-meta.php';
require_once __DIR__ . '/grid-junction-match.php';
require_once __DIR__ . '/grid-connection-rows.php';

/**
 * @param array<string, mixed> $rail_group
 * @return array<int, array<string, mixed>>
 */
function MRT_rail_services_from_group( array $rail_group ): array {
	$services = array();
	foreach ( (array) ( $rail_group['services'] ?? array() ) as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( $train_type && $train_type->slug === 'buss' ) {
			continue;
		}
		$services[] = $service_data;
	}
	return $services;
}

/**
 * @param array<string, mixed> $branch_group
 * @return array<int, array<string, mixed>>
 */
function MRT_bus_services_from_group( array $branch_group ): array {
	$services = array();
	foreach ( (array) ( $branch_group['services'] ?? array() ) as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( $train_type && $train_type->slug === 'buss' ) {
			$services[] = $service_data;
		}
	}
	return $services;
}

/**
 * @param array<string, mixed> $rail_group
 */
function MRT_timetable_rail_grid_direction( array $rail_group ): string {
	$stations = (array) ( $rail_group['stations'] ?? array() );
	if ( $stations === array() ) {
		return 'outbound';
	}
	$first_id = (int) $stations[0];
	if ( MRT_station_is_inbound_grid_origin( $first_id, MRT_rail_group_route_id( $rail_group ) ) ) {
		return 'inbound';
	}
	return 'outbound';
}

/**
 * @param array<string, mixed> $rail_group
 * @param array<string, mixed> $branch_group
 */
function MRT_timetable_branch_junction_station_id( array $rail_group, array $branch_group ): int {
	$rail_ids   = array_map( 'intval', (array) ( $rail_group['stations'] ?? array() ) );
	$branch_ids = array_map( 'intval', (array) ( $branch_group['stations'] ?? array() ) );
	$shared     = array_values( array_intersect( $rail_ids, $branch_ids ) );
	if ( $shared === array() ) {
		return 0;
	}
	foreach ( $shared as $station_id ) {
		if ( get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1' ) {
			return (int) $station_id;
		}
	}
	return (int) $shared[0];
}

/**
 * @param array<string, mixed>|null $train_stop
 */
function MRT_train_connection_anchor_time( ?array $train_stop ): string {
	if ( ! $train_stop ) {
		return '';
	}
	$arrival   = MRT_stop_effective_arrival( $train_stop );
	$departure = MRT_stop_effective_departure( $train_stop );
	if ( $arrival !== '' && $departure !== '' ) {
		return $arrival;
	}
	return $arrival !== '' ? $arrival : $departure;
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_connection_service_number( array $service_data ): string {
	$service = $service_data['service'];
	$number  = (string) get_post_meta( $service->ID, 'mrt_service_number', true );
	return $number !== '' ? $number : (string) $service->ID;
}

/**
 * @param array<string, mixed> $bus_data
 */
function MRT_get_bus_connection_destination_label( array $bus_data ): string {
	$service = $bus_data['service'];
	$dest    = MRT_get_service_destination( $service->ID );
	if ( ! empty( $dest['end_station_id'] ) ) {
		$station = get_post( (int) $dest['end_station_id'] );
		if ( $station instanceof WP_Post ) {
			return MRT_get_station_display_name( $station );
		}
	}

	$ordered = MRT_get_service_stop_times_ordered( $service->ID );
	if ( $ordered === array() ) {
		return '';
	}
	$last_row = end( $ordered );
	$station  = get_post( (int) ( $last_row['station_post_id'] ?? 0 ) );
	if ( $station instanceof WP_Post ) {
		return MRT_get_station_display_name( $station );
	}

	return '';
}

/**
 * @param array<string, mixed> $bus_data
 * @return array{service_number: string, time_display: string, destination: string}
 */
function MRT_build_bus_link_entry( array $bus_data, array $stop ): array {
	return array(
		'service_number' => MRT_connection_service_number( $bus_data ),
		'time_display'   => MRT_format_stop_time_display( $stop ),
		'destination'    => MRT_get_bus_connection_destination_label( $bus_data ),
	);
}

/**
 * @param array<string, mixed> $train_data
 * @return array{service_number: string, time_display: string}
 */
function MRT_build_train_link_entry( array $train_data, array $stop ): array {
	return array(
		'service_number' => MRT_connection_service_number( $train_data ),
		'time_display'   => MRT_format_stop_time_display( $stop ),
	);
}

/**
 * @return array{
 *   junction_id: int,
 *   junction_label: string,
 *   direction: string,
 *   train_to_bus: array<int, array{train: array{service_number: string, time_display: string}, buses: array<int, array{service_number: string, time_display: string, destination: string}>}>,
 *   bus_to_train: array<int, array{bus: array{service_number: string, time_display: string, destination: string}, trains: array<int, array{service_number: string, time_display: string}>}>
 * }
 */
function MRT_build_rail_bus_connection_data( array $rail_group, array $branch_group ): array {
	$junction_id = MRT_timetable_branch_junction_station_id( $rail_group, $branch_group );
	$direction   = MRT_timetable_rail_grid_direction( $rail_group );
	$junction    = $junction_id ? get_post( $junction_id ) : null;
	$label       = $junction instanceof WP_Post ? MRT_get_station_display_name( $junction ) : '';

	$rail_services = MRT_rail_services_from_group( $rail_group );
	$bus_services  = MRT_bus_services_from_group( $branch_group );

	return array(
		'junction_id'    => $junction_id,
		'junction_label' => $label,
		'direction'      => $direction,
		'train_to_bus'   => MRT_build_train_to_bus_connection_rows( $rail_services, $bus_services, $junction_id, $direction ),
		'bus_to_train'   => MRT_build_bus_to_train_connection_rows( $bus_services, $rail_services, $branch_group, $junction_id, $direction ),
	);
}

/**
 * @param array<string, mixed> $connection_data
 * @return array<int, array{service_number: string, time_display: string, destination: string}>
 */
function MRT_connection_buses_for_train_number( array $connection_data, string $train_number ): array {
	foreach ( $connection_data['train_to_bus'] as $row ) {
		if ( (string) $row['train']['service_number'] === $train_number ) {
			return $row['buses'];
		}
	}
	return array();
}

/**
 * Train numbers to try when resolving bus links for a merged overview column.
 *
 * @param array<int, array<string, mixed>> $info
 * @param array{primary_idx: int, continuation_idx: int|null, split_station_id: int} $column
 * @return array<int, string>
 */
function MRT_connection_train_numbers_for_column( array $info, array $column ): array {
	$numbers = array();
	$primary = (string) ( $info[ (int) $column['primary_idx'] ]['service_number'] ?? '' );
	if ( $primary !== '' ) {
		$numbers[] = $primary;
	}
	$continuation = $column['continuation_idx'] ?? null;
	if ( $continuation !== null ) {
		$cont = (string) ( $info[ (int) $continuation ]['service_number'] ?? '' );
		if ( $cont !== '' && ! in_array( $cont, $numbers, true ) ) {
			$numbers[] = $cont;
		}
	}
	return $numbers;
}

/**
 * @param array<string, mixed> $connection_data
 * @param array<int, array<string, mixed>> $info
 * @param array{primary_idx: int, continuation_idx: int|null, split_station_id: int}|null $column
 * @return array{
 *   buses: array<int, array{service_number: string, time_display: string, destination: string}>,
 *   train_number: string
 * }
 */
function MRT_connection_buses_for_column(
	array $connection_data,
	array $info,
	?array $column,
	int $fallback_idx = 0
): array {
	$train_numbers = array();
	if ( is_array( $column ) ) {
		$train_numbers = MRT_connection_train_numbers_for_column( $info, $column );
	} else {
		$train_numbers[] = (string) ( $info[ $fallback_idx ]['service_number'] ?? '' );
	}
	foreach ( $train_numbers as $train_number ) {
		if ( $train_number === '' ) {
			continue;
		}
		$buses = MRT_connection_buses_for_train_number( $connection_data, $train_number );
		if ( $buses !== array() ) {
			return array(
				'buses'        => $buses,
				'train_number' => $train_number,
			);
		}
	}
	return array(
		'buses'        => array(),
		'train_number' => '',
	);
}

/**
 * @param array{service_number: string, time_display: string, destination: string} $bus
 */
function MRT_bus_transfer_detail_line( array $bus ): string {
	$parts = array();
	if ( $bus['time_display'] !== '' && $bus['time_display'] !== '—' ) {
		$parts[] = $bus['time_display'];
	}
	if ( ! empty( $bus['destination'] ) ) {
		$parts[] = '→ ' . $bus['destination'];
	}
	return implode( ' ', $parts );
}
