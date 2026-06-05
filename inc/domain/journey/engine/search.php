<?php
/**
 * Journey search engine (BFS, configurable max transfers).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/engine/constraints.php';
require_once MRT_PATH . 'inc/domain/journey/engine/graph.php';

/**
 * Build one leg payload from a graph edge.
 *
 * @param array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>} $edge
 * @return array<string, mixed>
 */
function MRT_journey_engine_leg_from_edge(
	array $edge,
	int $from_station_id,
	string $dateYmd
): array {
	$leg = MRT_journey_build_leg_segment(
		(int) $edge['service_id'],
		$from_station_id,
		(int) $edge['to_station_id'],
		$dateYmd
	);
	if ( $leg !== null ) {
		return $leg;
	}
	return MRT_journey_leg_from_connection_row(
		$edge['connection'],
		$dateYmd,
		$from_station_id,
		(int) $edge['to_station_id']
	);
}

/**
 * Wrap legs as API multi-leg bundle.
 *
 * @param array<int, array<string, mixed>> $legs
 * @return array<string, mixed>
 */
function MRT_journey_engine_build_result( array $legs ): array {
	if ( count( $legs ) <= 1 ) {
		return array(
			'connection_type'     => 'direct',
			'transfer_station_id' => null,
			'legs'                => $legs,
		);
	}
	return array(
		'connection_type'     => 'transfer',
		'transfer_station_id' => (int) ( $legs[0]['to_station_id'] ?? 0 ),
		'legs'                => $legs,
	);
}

/**
 * Stable dedupe key for a journey path.
 *
 * @param array<int, array<string, mixed>> $legs
 */
function MRT_journey_engine_dedupe_key( array $legs ): string {
	$parts = array();
	foreach ( $legs as $leg ) {
		$parts[] = (int) ( $leg['service_id'] ?? 0 ) . ':' . (string) ( $leg['from_departure'] ?? '' );
	}
	return implode( '|', $parts );
}

/**
 * Compare two journey results for sort order.
 *
 * @param array<string, mixed> $a
 * @param array<string, mixed> $b
 */
function MRT_journey_engine_compare_results( array $a, array $b ): int {
	$legs_a = $a['legs'] ?? array();
	$legs_b = $b['legs'] ?? array();
	$dep_a  = (string) ( $legs_a[0]['from_departure'] ?? '' );
	$dep_b  = (string) ( $legs_b[0]['from_departure'] ?? '' );
	if ( $dep_a !== $dep_b ) {
		return strcmp( $dep_a, $dep_b );
	}
	$count_cmp = count( $legs_a ) <=> count( $legs_b );
	if ( $count_cmp !== 0 ) {
		return $count_cmp;
	}
	$hub_a = (int) ( $a['transfer_station_id'] ?? 0 );
	$hub_b = (int) ( $b['transfer_station_id'] ?? 0 );
	if ( $hub_a > 0 && $hub_b > 0 ) {
		$prio = MRT_journey_transfer_station_priority( $hub_a ) <=> MRT_journey_transfer_station_priority( $hub_b );
		if ( $prio !== 0 ) {
			return $prio;
		}
	}
	return strcmp( MRT_journey_engine_dedupe_key( $legs_a ), MRT_journey_engine_dedupe_key( $legs_b ) );
}

/**
 * Collect direct connections with direction filter.
 *
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_engine_find_direct(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd
): array {
	$results = array();
	foreach ( MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) as $conn ) {
		$sid = (int) ( $conn['service_id'] ?? 0 );
		if ( ! MRT_journey_constraint_leg_direction( $sid, $from_station_id, $to_station_id, $to_station_id ) ) {
			continue;
		}
		$results[] = MRT_journey_wrap_direct_multi( $conn, $dateYmd, $from_station_id, $to_station_id );
	}
	return $results;
}

/**
 * Append a journey result when not already seen.
 *
 * @param array<int, array<string, mixed>> $results
 * @param array<string, bool>              $seen
 * @return array{0: array<int, array<string, mixed>>, 1: array<string, bool>}
 */
function MRT_journey_engine_append_result( array $results, array $seen, array $result ): array {
	$legs = $result['legs'] ?? array();
	if ( ! is_array( $legs ) || $legs === array() ) {
		return array( $results, $seen );
	}
	$key = MRT_journey_engine_dedupe_key( $legs );
	if ( isset( $seen[ $key ] ) ) {
		return array( $results, $seen );
	}
	$seen[ $key ]       = true;
	$results[]          = $result;
	return array( $results, $seen );
}

/**
 * Try extending a partial path with one more leg.
 *
 * @param array<int, array<string, mixed>> $queue
 * @param array<int, array<string, mixed>> $results
 * @param-out array<int, array<string, mixed>> $results
 * @param array<string, bool>              $seen
 * @param-out array<string, bool>          $seen
 * @param array{station: int, arrival: string, legs: array<int, array<string, mixed>>, last_service_id: int} $state
 */
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
	unset( $min_transfer_minutes );
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
			(int) $edge['service_id']
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
			$max_transfers
		);
	}
}

/**
 * Apply one graph edge to BFS state.
 *
 * @param array<int, array<string, mixed>> $queue
 * @param array<int, array<string, mixed>> $results
 * @param-out array<int, array<string, mixed>> $results
 * @param array<string, bool>              $seen
 * @param-out array<string, bool>          $seen
 * @param array{station: int, arrival: string, legs: array<int, array<string, mixed>>, last_service_id: int} $state
 * @param array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>} $edge
 */
function MRT_journey_engine_apply_edge(
	array &$queue,
	array &$results,
	array &$seen,
	array $state,
	array $edge,
	int $goal_station_id,
	string $dateYmd,
	int $max_transfers
): void {
	if ( ! MRT_journey_transfer_wait_is_valid_between_services(
		$state['arrival'],
		$edge['departure'],
		(int) $state['station'],
		(int) $state['last_service_id'],
		(int) $edge['service_id']
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

/**
 * BFS transfer search up to max_transfers.
 *
 * @return array<int, array<string, mixed>>
 */
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

/**
 * Seed BFS from the first leg out of origin.
 *
 * @param array<int, array<string, mixed>> $queue
 * @param array<int, array<string, mixed>> $results
 * @param-out array<int, array<string, mixed>> $results
 * @param array<string, bool>              $seen
 * @param-out array<string, bool>          $seen
 * @param array{service_id: int, to_station_id: int, departure: string, arrival: string, connection: array<string, mixed>} $edge
 */
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

/**
 * Full journey search (direct + transfers up to max).
 *
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_engine_find(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd,
	int $min_transfer_minutes = 3,
	bool $include_direct = true,
	?int $max_transfers = null
): array {
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return array();
	}
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return array();
	}
	$max   = $max_transfers ?? MRT_journey_engine_max_transfers();
	$min   = $min_transfer_minutes > 0 ? (int) $min_transfer_minutes : MRT_journey_min_transfer_minutes();
	/** @var array<int, array<string, mixed>> $out */
	$out   = array();
	/** @var array<string, bool> $seen */
	$seen  = array();
	if ( $include_direct ) {
		foreach ( MRT_journey_engine_find_direct( $from_station_id, $to_station_id, $dateYmd ) as $direct ) {
			list( $out, $seen ) = MRT_journey_engine_append_result( $out, $seen, $direct );
		}
	}
	foreach ( MRT_journey_engine_find_with_transfers( $from_station_id, $to_station_id, $dateYmd, $min, $max, false ) as $transfer ) {
		list( $out, $seen ) = MRT_journey_engine_append_result( $out, $seen, $transfer );
	}
	return $out;
}

/**
 * Direct and multi-leg connections (same day, up to configured max transfers).
 *
 * @param int    $from_station_id From
 * @param int    $to_station_id To
 * @param string $dateYmd Date
 * @param int    $min_transfer_minutes Minimum minutes between arrival and next departure
 * @param bool   $include_direct Include single-service connections
 * @return array<int, array<string, mixed>>
 */
function MRT_find_multi_leg_connections( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes = 3, $include_direct = true ) {
	return MRT_journey_engine_find(
		(int) $from_station_id,
		(int) $to_station_id,
		(string) $dateYmd,
		(int) $min_transfer_minutes,
		(bool) $include_direct
	);
}

/**
 * Whether any connection exists (progressive depth for calendar).
 */
function MRT_journey_engine_has_connection(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd,
	int $min_transfer_minutes
): bool {
	if ( MRT_journey_engine_find_direct( $from_station_id, $to_station_id, $dateYmd ) !== array() ) {
		return true;
	}
	$max = MRT_journey_engine_max_transfers();
	for ( $depth = 1; $depth <= $max; $depth++ ) {
		$hits = MRT_journey_engine_find_with_transfers(
			$from_station_id,
			$to_station_id,
			$dateYmd,
			$min_transfer_minutes,
			$depth
		);
		if ( $hits !== array() ) {
			return true;
		}
	}
	return false;
}
