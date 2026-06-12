<?php
/**
 * Journey search engine: find
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * @return array<int, array<string, mixed>>
 */
function MRT_journey_engine_find(
	int $from_station_id,
	int $to_station_id,
	string $dateYmd,
	int $min_transfer_minutes = 0,
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
 * @return array<int, array<string, mixed>>
 */
function MRT_find_multi_leg_connections( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes = 0, $include_direct = true ) {
	return MRT_journey_engine_find(
		(int) $from_station_id,
		(int) $to_station_id,
		(string) $dateYmd,
		(int) $min_transfer_minutes,
		(bool) $include_direct
	);
}

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
