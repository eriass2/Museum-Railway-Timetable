<?php
/**
 * Journey search engine: bfs
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_journey_engine_extend_state(
	array &$queue,
	array &$results,
	array &$seen,
	array $state,
	int $goal_station_id,
	string $dateYmd,
	int $min_transfer_minutes,
	int $max_transfers
): void {
	$edges = MRT_journey_graph_next_legs(
		(int) $state['station'],
		$goal_station_id,
		$dateYmd,
		$state['arrival'],
		(int) $state['last_service_id']
	);
	foreach ( $edges as $edge ) {
		$min      = MRT_journey_min_transfer_between_legs(
			(int) $state['station'],
			(int) $state['last_service_id'],
			(int) $edge['service_id'],
			$min_transfer_minutes
		);
		$earliest = MRT_add_minutes_to_hhmm( $state['arrival'], $min );
		if ( $earliest === null || MRT_compare_hhmm( $edge['departure'], $earliest ) < 0 ) {
			continue;
		}
		MRT_journey_engine_apply_edge(
			$queue,
			$results,
			$seen,
			$state,
			$edge,
			$goal_station_id,
			$dateYmd,
			$max_transfers,
			$min_transfer_minutes
		);
	}
}

function MRT_journey_engine_apply_edge(
	array &$queue,
	array &$results,
	array &$seen,
	array $state,
	array $edge,
	int $goal_station_id,
	string $dateYmd,
	int $max_transfers,
	int $search_min_minutes
): void {
	if ( ! MRT_journey_transfer_wait_is_valid_between_services(
		$state['arrival'],
		$edge['departure'],
		(int) $state['station'],
		(int) $state['last_service_id'],
		(int) $edge['service_id'],
		$search_min_minutes
	) ) {
		return;
	}
	$leg = MRT_journey_engine_leg_from_edge( $edge, (int) $state['station'], $dateYmd );
	$new_legs = array_merge( $state['legs'], array( $leg ) );
	$to_id    = (int) $edge['to_station_id'];
	if ( $to_id === $goal_station_id ) {
		list( $results, $seen ) = MRT_journey_engine_append_result(
			$results,
			$seen,
			MRT_journey_engine_build_result( $new_legs )
		);
		return;
	}
	$transfers = count( $new_legs ) - 1;
	if ( $transfers >= $max_transfers ) {
		return;
	}
	$queue[] = array(
		'station'         => $to_id,
		'arrival'         => $edge['arrival'],
		'legs'            => $new_legs,
		'last_service_id' => (int) $edge['service_id'],
	);
}

function MRT_journey_engine_find_with_transfers(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd,
	int $min_transfer_minutes,
	int $max_transfers,
	bool $emit_single_leg = false
): array {
	if ( $max_transfers <= 0 ) {
		return array();
	}
	/** @var array<int, array<string, mixed>> $results */
	$results = array();
	/** @var array<string, bool> $seen */
	$seen    = array();
	$queue   = array();
	foreach ( MRT_journey_graph_next_legs( $from_station_id, $to_station_id, $dateYmd ) as $edge ) {
		MRT_journey_engine_apply_first_edge(
			$queue,
			$results,
			$seen,
			$edge,
			$from_station_id,
			$to_station_id,
			$dateYmd,
			$max_transfers,
			$emit_single_leg
		);
	}
	while ( $queue !== array() ) {
		$state = array_shift( $queue );
		MRT_journey_engine_extend_state(
			$queue,
			$results,
			$seen,
			$state,
			$to_station_id,
			$dateYmd,
			$min_transfer_minutes,
			$max_transfers
		);
	}
	usort( $results, 'MRT_journey_engine_compare_results' );
	return $results;
}

function MRT_journey_engine_apply_first_edge(
	array &$queue,
	array &$results,
	array &$seen,
	array $edge,
	int $from_station_id,
	int $goal_station_id,
	string $dateYmd,
	int $max_transfers,
	bool $emit_single_leg = false
): void {
	$leg = MRT_journey_engine_leg_from_edge( $edge, $from_station_id, $dateYmd );
	$new_legs = array( $leg );
	$to_id    = (int) $edge['to_station_id'];
	if ( $to_id === $goal_station_id ) {
		if ( $emit_single_leg ) {
			list( $results, $seen ) = MRT_journey_engine_append_result(
				$results,
				$seen,
				MRT_journey_engine_build_result( $new_legs )
			);
		}
		return;
	}
	if ( $max_transfers <= 0 ) {
		return;
	}
	$queue[] = array(
		'station'         => $to_id,
		'arrival'         => $edge['arrival'],
		'legs'            => $new_legs,
		'last_service_id' => (int) $edge['service_id'],
	);
}
