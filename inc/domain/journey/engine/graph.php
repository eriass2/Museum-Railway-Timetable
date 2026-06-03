<?php
/**
 * Day graph: possible next legs from a station on a given date.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One traversable leg edge for journey search.
 *
 * @return array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>}|null
 */
function MRT_journey_graph_edge_from_stops(
	int $service_id,
	array $ordered,
	int $from_idx,
	int $to_idx,
	int $goal_station_id
): ?array {
	$from_row = $ordered[ $from_idx ];
	$to_row   = $ordered[ $to_idx ];
	$from_id  = (int) ( $from_row['station_post_id'] ?? 0 );
	$to_id    = (int) ( $to_row['station_post_id'] ?? 0 );
	if ( $from_id <= 0 || $to_id <= 0 ) {
		return null;
	}
	if ( ! MRT_journey_constraint_stop_permissions( $ordered, $from_idx, $to_idx ) ) {
		return null;
	}
	if ( ! MRT_journey_constraint_edge_allowed( $service_id, $from_id, $to_id, $goal_station_id ) ) {
		return null;
	}
	$dep = MRT_connection_row_departure_at_from(
		array(
			'from_departure' => (string) ( $from_row['departure_time'] ?? '' ),
			'from_arrival'   => (string) ( $from_row['arrival_time'] ?? '' ),
		)
	);
	$arr = (string) ( $to_row['arrival_time'] ?? '' );
	if ( $arr === '' ) {
		$arr = (string) ( $to_row['departure_time'] ?? '' );
	}
	if ( $dep === '' || ! MRT_validate_time_hhmm( $dep ) || ! MRT_validate_time_hhmm( $arr ) ) {
		return null;
	}
	return array(
		'service_id'     => $service_id,
		'to_station_id'  => $to_id,
		'departure'      => $dep,
		'arrival'        => $arr,
		'connection'     => array(
			'service_id'     => $service_id,
			'from_departure' => $dep,
			'from_arrival'   => (string) ( $from_row['arrival_time'] ?? '' ),
			'to_arrival'     => $arr,
			'to_departure'   => (string) ( $to_row['departure_time'] ?? '' ),
		),
	);
}

/**
 * Collect edges for one service departing from a station.
 *
 * @param array<int, array<string, mixed>> $edges
 */
function MRT_journey_graph_collect_service_edges(
	array &$edges,
	int $service_id,
	int $from_station_id,
	int $goal_station_id,
	string $earliest_departure_hhmm
): void {
	$ordered  = MRT_get_service_stop_times_ordered( $service_id );
	$from_idx = MRT_journey_find_stop_index( $ordered, $from_station_id );
	if ( $from_idx === null ) {
		return;
	}
	$count = count( $ordered );
	for ( $to_idx = $from_idx + 1; $to_idx < $count; $to_idx++ ) {
		$edge = MRT_journey_graph_edge_from_stops( $service_id, $ordered, $from_idx, $to_idx, $goal_station_id );
		if ( $edge === null ) {
			continue;
		}
		if ( $earliest_departure_hhmm !== '' && MRT_compare_hhmm( $edge['departure'], $earliest_departure_hhmm ) < 0 ) {
			continue;
		}
		$edges[] = $edge;
	}
}

/**
 * All valid next legs from a station (optionally after a minimum departure time).
 *
 * @return array<int, array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>}>
 */
function MRT_journey_graph_next_legs(
	int $from_station_id,
	int $goal_station_id,
	string $dateYmd,
	string $earliest_departure_hhmm = '',
	int $exclude_service_id = 0
): array {
	$edges = array();
	foreach ( MRT_services_running_on_date( $dateYmd ) as $service_id ) {
		$service_id = (int) $service_id;
		if ( $exclude_service_id > 0 && $service_id === $exclude_service_id ) {
			continue;
		}
		MRT_journey_graph_collect_service_edges(
			$edges,
			$service_id,
			$from_station_id,
			$goal_station_id,
			$earliest_departure_hhmm
		);
	}
	return $edges;
}
