<?php
/**
 * Calendar day states for journey search (public / API)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Arrival HH:MM at journey destination from a raw multi-leg bundle.
 *
 * @param array<string, mixed> $item Row from MRT_find_multi_leg_connections.
 */
function MRT_journey_raw_item_arrival_hhmm( array $item ): string {
	$legs = $item['legs'] ?? array();
	if ( ! is_array( $legs ) || $legs === array() ) {
		return '';
	}
	$last = $legs[ count( $legs ) - 1 ];
	if ( ! is_array( $last ) ) {
		return '';
	}
	$candidates = array(
		(string) ( $last['to_arrival'] ?? '' ),
		(string) ( $last['to_departure'] ?? '' ),
	);
	foreach ( $candidates as $time ) {
		if ( $time !== '' && MRT_validate_time_hhmm( $time ) ) {
			return $time;
		}
	}
	return '';
}

/**
 * Whether any return departs on/after earliest allowed turnaround.
 *
 * @param array<int, array<string, mixed>> $return_raw Cached return search rows.
 */
function MRT_journey_calendar_has_return_after_arrival(
	array $return_raw,
	string $dateYmd,
	int $return_origin_id,
	int $return_dest_id,
	string $earliest_hhmm
): bool {
	$normalized = MRT_return_journey_normalized_after_turnaround(
		$return_raw,
		$dateYmd,
		$return_origin_id,
		$return_dest_id,
		$earliest_hhmm
	);
	return $normalized !== array();
}

/**
 * Progressive round-trip check — one return search per day, no full outbound list.
 *
 * @param array<string, array<int, array<string, mixed>>> $return_raw_cache Request-scope cache.
 */
function MRT_journey_calendar_has_round_trip_fast(
	int $from_station_id,
	int $to_station_id,
	string $ymd,
	array &$return_raw_cache
): bool {
	$min_xfer   = MRT_journey_min_transfer_minutes();
	$return_key = (string) $to_station_id . '|' . (string) $from_station_id . '|' . $ymd;
	if ( ! isset( $return_raw_cache[ $return_key ] ) ) {
		$return_raw_cache[ $return_key ] = MRT_find_multi_leg_connections(
			$to_station_id,
			$from_station_id,
			$ymd,
			$min_xfer,
			true
		);
	}
	$return_raw = $return_raw_cache[ $return_key ];
	if ( $return_raw === array() ) {
		return false;
	}
	$outbound_raw = MRT_find_multi_leg_connections(
		$from_station_id,
		$to_station_id,
		$ymd,
		$min_xfer,
		true
	);
	if ( $outbound_raw === array() ) {
		return false;
	}
	$turnaround = MRT_journey_min_transfer_minutes();
	foreach ( $outbound_raw as $out_item ) {
		if ( ! is_array( $out_item ) ) {
			continue;
		}
		$arrival = MRT_journey_raw_item_arrival_hhmm( $out_item );
		if ( $arrival === '' ) {
			continue;
		}
		$earliest = MRT_add_minutes_to_hhmm( $arrival, $turnaround );
		if ( $earliest === null ) {
			continue;
		}
		if ( MRT_journey_calendar_has_return_after_arrival(
			$return_raw,
			$ymd,
			$to_station_id,
			$from_station_id,
			$earliest
		) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether at least one outbound connection has a valid return on the same day.
 *
 * @param int    $from_station_id Outbound origin
 * @param int    $to_station_id   Outbound destination
 * @param string $ymd             Date YYYY-MM-DD
 */
function MRT_journey_calendar_has_round_trip( int $from_station_id, int $to_station_id, string $ymd ): bool {
	$cache = array();
	return MRT_journey_calendar_has_round_trip_fast( $from_station_id, $to_station_id, $ymd, $cache );
}

/**
 * Status for one day: no traffic, traffic but no connection, or ok
 *
 * @param int                      $from_station_id From station
 * @param int                      $to_station_id To station
 * @param string                   $ymd Date YYYY-MM-DD
 * @param array<string, list<int>> $services_cache Ref-filled cache date => service ids
 * @param string                   $trip_type single|return
 * @return string none|traffic_no_match|ok
 */
function MRT_journey_calendar_day_status(
	$from_station_id,
	$to_station_id,
	$ymd,
	array &$services_cache,
	string $trip_type = 'single',
	array &$return_raw_cache = array()
) {
	if ( ! isset( $services_cache[ $ymd ] ) ) {
		$run                    = MRT_services_running_on_date( $ymd );
		$services_cache[ $ymd ] = array_values( array_map( static fn ( $id ): int => (int) $id, $run ) );
	}
	if ( empty( $services_cache[ $ymd ] ) ) {
		return 'none';
	}
	if ( $trip_type === 'return' ) {
		return MRT_journey_calendar_has_round_trip_fast(
			(int) $from_station_id,
			(int) $to_station_id,
			$ymd,
			$return_raw_cache
		)
			? 'ok'
			: 'traffic_no_match';
	}
	$min_xfer = MRT_journey_min_transfer_minutes();
	if ( MRT_journey_engine_has_connection( $from_station_id, $to_station_id, $ymd, $min_xfer ) ) {
		return 'ok';
	}
	return 'traffic_no_match';
}

/**
 * One calendar cell: journey status + dominant timetable type (when traffic runs).
 *
 * @return array{status: string, type: string}
 */
function MRT_journey_calendar_day_entry(
	int $from_station_id,
	int $to_station_id,
	string $ymd,
	array &$services_cache,
	string $trip_type = 'single',
	array &$return_raw_cache = array()
): array {
	$day_params = array(
		'from'      => (string) $from_station_id,
		'to'        => (string) $to_station_id,
		'ymd'       => $ymd,
		'trip_type' => $trip_type === 'return' ? 'return' : 'single',
	);
	$cached_day = MRT_journey_cache_get( 'calendar.day', $day_params );
	if ( is_array( $cached_day ) && isset( $cached_day['status'], $cached_day['type'] ) ) {
		return array(
			'status' => (string) $cached_day['status'],
			'type'   => (string) $cached_day['type'],
		);
	}

	$status = MRT_journey_calendar_day_status(
		$from_station_id,
		$to_station_id,
		$ymd,
		$services_cache,
		$trip_type,
		$return_raw_cache
	);
	$type   = $status === 'none' ? '' : MRT_dominant_timetable_type_for_date( $ymd );
	$entry  = array(
		'status' => $status,
		'type'   => $type,
	);
	MRT_journey_cache_set( 'calendar.day', $day_params, $entry );
	return $entry;
}

/**
 * Per-day status for a calendar month (YYYY-MM-DD => status + type)
 *
 * @param int $from_station_id From station
 * @param int $to_station_id To station
 * @param int $year Year
 * @param int $month Month 1-12
 * @return array<string, array{status: string, type: string}>
 */
function MRT_build_journey_calendar_month( $from_station_id, $to_station_id, $year, $month, string $trip_type = 'single' ) {
	$out = array();
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return $out;
	}
	$year  = (int) $year;
	$month = (int) $month;
	if ( $year < 1970 || $year > 2100 || $month < 1 || $month > 12 ) {
		return $out;
	}
	$trip_type = ( $trip_type === 'return' ) ? 'return' : 'single';
	$days           = (int) gmdate( 't', gmmktime( 0, 0, 0, $month, 1, $year ) );
	$services_cache = array();
	$return_raw_cache = array();
	for ( $d = 1; $d <= $days; $d++ ) {
		$ymd = sprintf( '%04d-%02d-%02d', $year, $month, $d );
		if ( ! MRT_validate_date( $ymd ) ) {
			continue;
		}
		$out[ $ymd ] = MRT_journey_calendar_day_entry(
			(int) $from_station_id,
			(int) $to_station_id,
			$ymd,
			$services_cache,
			$trip_type,
			$return_raw_cache
		);
	}
	return $out;
}

/**
 * Per-day status for a calendar month (YYYY-MM-DD => status + type)
 *
 * @param int $from_station_id From station
 * @param int $to_station_id To station
 * @param int $year Year
 * @param int $month Month 1-12
 * @return array<string, array{status: string, type: string}>
 */
function MRT_get_journey_calendar_month( $from_station_id, $to_station_id, $year, $month, string $trip_type = 'single' ) {
	$from_station_id = (int) $from_station_id;
	$to_station_id   = (int) $to_station_id;
	$year            = (int) $year;
	$month           = (int) $month;
	$trip_type       = ( $trip_type === 'return' ) ? 'return' : 'single';

	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return array();
	}
	if ( $year < 1970 || $year > 2100 || $month < 1 || $month > 12 ) {
		return array();
	}

	$cache_key = MRT_journey_calendar_month_cache_key(
		$from_station_id,
		$to_station_id,
		$year,
		$month,
		$trip_type
	);
	$cached = MRT_journey_calendar_month_cache_get( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$started_at = microtime( true );
	$built      = MRT_build_journey_calendar_month(
		$from_station_id,
		$to_station_id,
		$year,
		$month,
		$trip_type
	);
	MRT_journey_calendar_month_cache_set( $cache_key, $built );
	MRT_journey_calendar_maybe_log_slow_build(
		$started_at,
		$from_station_id,
		$to_station_id,
		$year,
		$month,
		$trip_type
	);

	return $built;
}

/**
 * Log slow uncached calendar month builds in development (Fas 4 perf baseline).
 *
 * @param float  $started_at microtime(true) before build.
 */
function MRT_journey_calendar_maybe_log_slow_build(
	float $started_at,
	int $from_station_id,
	int $to_station_id,
	int $year,
	int $month,
	string $trip_type
): void {
	if ( ! MRT_is_development_mode() ) {
		return;
	}

	$elapsed_ms = ( microtime( true ) - $started_at ) * 1000;
	$threshold  = (int) apply_filters( 'mrt_journey_calendar_slow_ms', 500 );
	if ( $elapsed_ms < $threshold ) {
		return;
	}

	MRT_log(
		sprintf( 'Slow journey calendar month build: %.0f ms', $elapsed_ms ),
		array(
			'from_station_id' => $from_station_id,
			'to_station_id'   => $to_station_id,
			'year'            => $year,
			'month'           => $month,
			'trip_type'       => $trip_type,
		),
		'warn'
	);
}
