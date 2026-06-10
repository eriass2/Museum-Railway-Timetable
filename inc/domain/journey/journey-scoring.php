<?php
/**
 * Journey connection scoring and sort order for wizard search results.
 *
 * TODO (produkt): Väg in avvikelser/störningar i poäng när regler är beslutade — se kommentar i docs/REBUILD_PRODUCT_DECISIONS.md (journey scoring).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default score weights (higher total score = better connection).
 *
 * @return array<string, int>
 */
function MRT_journey_score_default_weights(): array {
	return array(
		'return_after_outbound'        => 3,
		'return_travel'                => 1,
		'direct_bonus'                 => 40,
		'unnecessary_transfer_penalty' => 100,
	);
}

/**
 * Score weights with filter hook for tuning.
 *
 * @return array<string, int>
 */
function MRT_journey_score_weights(): array {
	$weights = MRT_journey_score_default_weights();
	return (array) apply_filters( 'mrt_journey_score_weights', $weights );
}

/**
 * Departure HH:MM from a normalized connection row.
 *
 * @param array<string, mixed> $connection Normalized API connection
 */
function MRT_journey_normalized_departure_hhmm( array $connection ): string {
	$candidates = array(
		(string) ( $connection['from_departure'] ?? '' ),
		(string) ( $connection['departure'] ?? '' ),
		(string) ( $connection['from_arrival'] ?? '' ),
	);
	foreach ( $candidates as $time ) {
		if ( $time !== '' && MRT_validate_time_hhmm( $time ) ) {
			return $time;
		}
	}
	return '';
}

/**
 * Arrival HH:MM from a normalized connection row.
 *
 * @param array<string, mixed> $connection Normalized API connection
 */
function MRT_journey_normalized_arrival_hhmm( array $connection ): string {
	$candidates = array(
		(string) ( $connection['to_arrival'] ?? '' ),
		(string) ( $connection['arrival'] ?? '' ),
		(string) ( $connection['to_departure'] ?? '' ),
	);
	foreach ( $candidates as $time ) {
		if ( $time !== '' && MRT_validate_time_hhmm( $time ) ) {
			return $time;
		}
	}
	return '';
}

/**
 * Door-to-door minutes for a normalized connection.
 *
 * @param array<string, mixed> $connection Normalized API connection
 */
function MRT_journey_normalized_door_to_door_minutes( array $connection ): ?int {
	$dep = MRT_journey_normalized_departure_hhmm( $connection );
	$arr = MRT_journey_normalized_arrival_hhmm( $connection );
	if ( $dep === '' || $arr === '' ) {
		return null;
	}
	return MRT_format_duration_minutes( $dep, $arr );
}

/**
 * Whether a normalized connection is a direct trip.
 *
 * @param array<string, mixed> $connection Normalized API connection
 */
function MRT_journey_normalized_is_direct( array $connection ): bool {
	if ( (string) ( $connection['connection_type'] ?? '' ) === 'direct' ) {
		return true;
	}
	$legs = $connection['legs'] ?? array();
	return ! is_array( $legs ) || count( $legs ) <= 1;
}

/**
 * Whether any direct connection exists for a station pair on a date.
 */
function MRT_journey_search_has_direct_connections(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd
): bool {
	return MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) !== array();
}

/**
 * Quality adjustment for direct vs unnecessary transfer.
 *
 * @param array<string, mixed> $connection Normalized API connection
 */
function MRT_journey_score_quality_adjustment( array $connection, bool $has_direct ): int {
	$weights = MRT_journey_score_weights();
	if ( MRT_journey_normalized_is_direct( $connection ) ) {
		return (int) $weights['direct_bonus'];
	}
	if ( $has_direct ) {
		return - (int) $weights['unnecessary_transfer_penalty'];
	}
	return 0;
}

/**
 * Return score: prefer departing soon after outbound arrival + turnaround.
 *
 * @param array<string, mixed> $connection Normalized API connection
 * @param string               $earliest_departure_hhmm First allowed return departure
 * @return int|null Null when times cannot be calculated
 */
function MRT_journey_score_return_connection(
	array $connection,
	bool $has_direct,
	string $earliest_departure_hhmm
): ?int {
	if ( ! MRT_validate_time_hhmm( $earliest_departure_hhmm ) ) {
		return null;
	}
	$departure = MRT_journey_normalized_departure_hhmm( $connection );
	$travel    = MRT_journey_normalized_door_to_door_minutes( $connection );
	if ( $departure === '' || $travel === null ) {
		return null;
	}
	$after_earliest = MRT_format_duration_minutes( $earliest_departure_hhmm, $departure );
	if ( $after_earliest === null ) {
		return null;
	}
	$weights = MRT_journey_score_weights();
	$score   = -( $after_earliest * (int) $weights['return_after_outbound'] );
	$score  -= $travel * (int) $weights['return_travel'];
	$score  += MRT_journey_score_quality_adjustment( $connection, $has_direct );
	return $score;
}

/**
 * Compare two outbound connections: earlier departure first.
 *
 * @param array<string, mixed> $a Normalized API connection
 * @param array<string, mixed> $b Normalized API connection
 */
function MRT_journey_compare_outbound_connections( array $a, array $b ): int {
	return MRT_journey_compare_departure_tie_break( $a, $b );
}

/**
 * Compare two return connections for descending score sort.
 *
 * @param array<string, mixed> $a Normalized API connection
 * @param array<string, mixed> $b Normalized API connection
 */
function MRT_journey_compare_return_connections(
	array $a,
	array $b,
	bool $has_direct,
	string $earliest_departure_hhmm
): int {
	$score_a = MRT_journey_score_return_connection( $a, $has_direct, $earliest_departure_hhmm );
	$score_b = MRT_journey_score_return_connection( $b, $has_direct, $earliest_departure_hhmm );
	$cmp     = MRT_journey_compare_nullable_scores( $score_a, $score_b );
	if ( $cmp !== 0 ) {
		return $cmp;
	}
	return MRT_journey_compare_departure_tie_break( $a, $b );
}

/**
 * Higher score first; null scores sink to the bottom.
 */
function MRT_journey_compare_nullable_scores( ?int $score_a, ?int $score_b ): int {
	if ( $score_a === null && $score_b === null ) {
		return 0;
	}
	if ( $score_a === null ) {
		return 1;
	}
	if ( $score_b === null ) {
		return -1;
	}
	if ( $score_a !== $score_b ) {
		return $score_b <=> $score_a;
	}
	return 0;
}

/**
 * Earlier departure wins when scores are equal.
 *
 * @param array<string, mixed> $a Normalized API connection
 * @param array<string, mixed> $b Normalized API connection
 */
function MRT_journey_compare_departure_tie_break( array $a, array $b ): int {
	$dep_a = MRT_journey_normalized_departure_hhmm( $a );
	$dep_b = MRT_journey_normalized_departure_hhmm( $b );
	if ( $dep_a === '' && $dep_b === '' ) {
		return 0;
	}
	if ( $dep_a === '' ) {
		return 1;
	}
	if ( $dep_b === '' ) {
		return -1;
	}
	return MRT_compare_hhmm( $dep_a, $dep_b );
}

/**
 * Sort outbound connections by departure time (earliest first).
 *
 * Search already filters invalid transfers and wait times; scoring is not used for order.
 *
 * @param array<int, array<string, mixed>> $connections Normalized API connections
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_sort_outbound_connections(
	array $connections,
	int $from_station_id,
	int $to_station_id,
	string $dateYmd
): array {
	if ( $connections === array() ) {
		return $connections;
	}
	unset( $from_station_id, $to_station_id, $dateYmd );
	usort(
		$connections,
		static function ( array $a, array $b ): int {
			return MRT_journey_compare_outbound_connections( $a, $b );
		}
	);
	return $connections;
}

/**
 * Sort return connections by score (best first).
 *
 * @param array<int, array<string, mixed>> $connections Normalized API connections
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_sort_return_connections(
	array $connections,
	int $from_station_id,
	int $to_station_id,
	string $dateYmd,
	string $earliest_departure_hhmm
): array {
	if ( $connections === array() ) {
		return $connections;
	}
	$has_direct = MRT_journey_search_has_direct_connections( $from_station_id, $to_station_id, $dateYmd );
	usort(
		$connections,
		static function ( array $a, array $b ) use ( $has_direct, $earliest_departure_hhmm ): int {
			return MRT_journey_compare_return_connections( $a, $b, $has_direct, $earliest_departure_hhmm );
		}
	);
	return $connections;
}
