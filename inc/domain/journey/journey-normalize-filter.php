<?php
/**
 * Journey normalize: filter
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_journey_filter_wizard_connections( array $connections ): array {
	if ( $connections === array() ) {
		return $connections;
	}
	$directs     = array();
	$transfers   = array();
	$direct_deps = array();
	foreach ( $connections as $conn ) {
		if ( (string) ( $conn['connection_type'] ?? '' ) === 'direct' ) {
			$directs[] = $conn;
			$dep       = MRT_journey_normalized_departure_hhmm( $conn );
			if ( $dep !== '' ) {
				$direct_deps[ $dep ] = true;
			}
			continue;
		}
		$transfers[] = $conn;
	}
	$kept = MRT_journey_filter_transfer_connections( $transfers, $direct_deps, $directs );
	$out  = array_merge( $directs, $kept );
	return (array) apply_filters( 'mrt_journey_wizard_connections', $out, $connections );
}

function MRT_journey_filter_transfer_connections( array $transfers, array $direct_deps, array $directs ): array {
	$earliest_direct = MRT_journey_earliest_departure_hhmm( $directs );
	$kept            = array();
	$best_by_dep     = array();
	foreach ( $transfers as $conn ) {
		$dep = MRT_journey_normalized_departure_hhmm( $conn );
		if ( $dep !== '' && isset( $direct_deps[ $dep ] ) ) {
			continue;
		}
		$arr = MRT_journey_normalized_arrival_hhmm( $conn );
		if ( $dep === '' || $arr === '' ) {
			$kept[] = $conn;
			continue;
		}
		if ( ! isset( $best_by_dep[ $dep ] ) ) {
			$best_by_dep[ $dep ] = count( $kept );
			$kept[]              = $conn;
			continue;
		}
		$idx     = $best_by_dep[ $dep ];
		$current = $kept[ $idx ];
		if ( MRT_compare_hhmm( $arr, MRT_journey_normalized_arrival_hhmm( $current ) ) < 0 ) {
			$kept[ $idx ] = $conn;
		}
	}
	return array_values(
		array_filter(
			$kept,
			static function ( array $conn ) use ( $directs, $earliest_direct ): bool {
				return ! MRT_journey_transfer_dominated_by_direct( $conn, $directs, $earliest_direct );
			}
		)
	);
}

function MRT_journey_earliest_departure_hhmm( array $directs ): string {
	$earliest = '';
	foreach ( $directs as $direct ) {
		$dep = MRT_journey_normalized_departure_hhmm( $direct );
		if ( $dep === '' ) {
			continue;
		}
		if ( $earliest === '' || MRT_compare_hhmm( $dep, $earliest ) < 0 ) {
			$earliest = $dep;
		}
	}
	return $earliest;
}

function MRT_journey_transfer_dominated_by_direct(
	array $transfer,
	array $directs,
	string $earliest_direct_dep
): bool {
	$t_dep = MRT_journey_normalized_departure_hhmm( $transfer );
	$t_arr = MRT_journey_normalized_arrival_hhmm( $transfer );
	if ( $t_dep === '' || $t_arr === '' ) {
		return false;
	}
	if ( $earliest_direct_dep !== '' && MRT_compare_hhmm( $t_dep, $earliest_direct_dep ) < 0 ) {
		return false;
	}
	foreach ( $directs as $direct ) {
		$d_dep = MRT_journey_normalized_departure_hhmm( $direct );
		$d_arr = MRT_journey_normalized_arrival_hhmm( $direct );
		if ( $d_dep === '' || $d_arr === '' ) {
			continue;
		}
		if ( MRT_compare_hhmm( $d_arr, $t_arr ) > 0 ) {
			continue;
		}
		if ( MRT_compare_hhmm( $d_dep, $t_dep ) <= 0 ) {
			continue;
		}
		return true;
	}
	return false;
}

function MRT_journey_find_normalized_connections( int $from_station_id, int $to_station_id, string $dateYmd ): array {
	$min_xfer   = MRT_journey_min_transfer_minutes();
	$raw_multi  = MRT_find_multi_leg_connections(
		$from_station_id,
		$to_station_id,
		$dateYmd,
		$min_xfer,
		true
	);
	$normalized = array();
	foreach ( $raw_multi as $item ) {
		$normalized[] = MRT_normalize_connection_for_api(
			$item,
			$dateYmd,
			$from_station_id,
			$to_station_id
		);
	}

	$normalized = MRT_journey_filter_wizard_connections( $normalized );

	return MRT_journey_sort_outbound_connections(
		$normalized,
		$from_station_id,
		$to_station_id,
		$dateYmd
	);
}
