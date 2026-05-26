<?php
/**
 * Multi-leg journey search (one transfer, two services)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Build one leg object for API (segment on one service)
 *
 * @param int    $service_id Service ID
 * @param int    $from_station_id From station
 * @param int    $to_station_id To station
 * @param string $dateYmd Date for train type
 * @return array<string, mixed>|null
 */
function MRT_journey_build_leg_segment( $service_id, $from_station_id, $to_station_id, $dateYmd ) {
	$detail = MRT_get_connection_journey_detail( $service_id, $from_station_id, $to_station_id );
	if ( empty( $detail['stops'] ) ) {
		return null;
	}
	$stops = $detail['stops'];
	$first = $stops[0];
	$last  = $stops[ count( $stops ) - 1 ];
	$dep   = $first['departure_time'] ?: $first['arrival_time'];
	$arr   = $last['arrival_time'] ?: $last['departure_time'];
	$tt    = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
	$num   = get_post_meta( $service_id, 'mrt_service_number', true );
	$dur   = MRT_format_duration_minutes( $dep, $arr );
	return array(
		'service_id'      => (int) $service_id,
		'from_station_id' => (int) $from_station_id,
		'to_station_id'   => (int) $to_station_id,
		'from_departure'  => $dep,
		'to_arrival'      => $arr,
		'duration_minutes' => $dur,
		'train_type'      => $tt ? $tt->name : '',
		'train_type_slug' => $tt ? $tt->slug : '',
		'train_type_icon' => $tt ? MRT_get_train_type_symbol_key( $tt ) : '',
		'service_number'  => $num !== '' && $num !== null ? (string) $num : (string) $service_id,
	);
}

/**
 * Build leg from flat connection row (same endpoints as search)
 *
 * @param array<string, mixed> $conn Connection row
 * @param string               $dateYmd Date
 * @param int                  $from_station_id From
 * @param int                  $to_station_id To
 * @return array<string, mixed>
 */
function MRT_journey_leg_from_connection_row( array $conn, $dateYmd, $from_station_id, $to_station_id ) {
	$sid = intval( $conn['service_id'] );
	$tt  = MRT_get_service_train_type_for_date( $sid, $dateYmd );
	$num = get_post_meta( $sid, 'mrt_service_number', true );
	$dep = $conn['from_departure'] ? (string) $conn['from_departure'] : (string) ( $conn['from_arrival'] ?? '' );
	$arr = $conn['to_arrival'] ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' );
	return array(
		'service_id'      => $sid,
		'from_station_id' => (int) $from_station_id,
		'to_station_id'   => (int) $to_station_id,
		'from_departure'  => $dep,
		'to_arrival'      => $arr,
		'duration_minutes' => MRT_format_duration_minutes( $dep, $arr ),
		'train_type'      => $tt ? $tt->name : (string) ( $conn['train_type'] ?? '' ),
		'train_type_slug' => $tt ? $tt->slug : '',
		'train_type_icon' => $tt ? MRT_get_train_type_symbol_key( $tt ) : MRT_get_train_type_symbol_key_from_label( (string) ( $conn['train_type'] ?? '' ) ),
		'service_number'  => $num !== '' && $num !== null ? (string) $num : (string) $sid,
	);
}

/**
 * Wrap direct connection as multi-leg shape (one leg)
 *
 * @param array<string, mixed> $conn Connection row
 * @param string               $dateYmd Date
 * @param int                  $from_station_id From
 * @param int                  $to_station_id To
 * @return array<string, mixed>
 */
function MRT_journey_wrap_direct_multi( array $conn, $dateYmd, $from_station_id, $to_station_id ) {
	$sid = intval( $conn['service_id'] );
	$leg = MRT_journey_build_leg_segment( $sid, $from_station_id, $to_station_id, $dateYmd );
	if ( $leg === null ) {
		$leg = MRT_journey_leg_from_connection_row( $conn, $dateYmd, $from_station_id, $to_station_id );
	}
	return array(
		'connection_type'     => 'direct',
		'transfer_station_id' => null,
		'legs'                => array( $leg ),
	);
}

/**
 * Departure HH:MM for second leg from connection row.
 *
 * @param array<string, mixed> $connection Connection row
 */
function MRT_journey_connection_departure_hhmm( array $connection ): string {
	$dep = (string) ( $connection['from_departure'] ?? '' );
	if ( $dep !== '' && MRT_validate_time_hhmm( $dep ) ) {
		return $dep;
	}
	$arr = (string) ( $connection['from_arrival'] ?? '' );
	return MRT_validate_time_hhmm( $arr ) ? $arr : '';
}

/**
 * Collect transfer options before filtering and sorting.
 *
 * @return array<int, array{transfer: array<string, mixed>, priority: int, wait: int, departure: string}>
 */
function MRT_journey_collect_transfer_candidates( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes ) {
	$candidates  = array();
	$service_ids = MRT_services_running_on_date( $dateYmd );
	foreach ( $service_ids as $s1 ) {
		MRT_journey_collect_transfers_for_first_leg(
			$candidates,
			(int) $s1,
			$from_station_id,
			$to_station_id,
			$dateYmd,
			$min_transfer_minutes
		);
	}
	return $candidates;
}

/**
 * @param array<int, array<string, mixed>> $candidates
 */
function MRT_journey_collect_transfers_for_first_leg(
	array &$candidates,
	int $s1,
	$from_station_id,
	$to_station_id,
	string $dateYmd,
	int $min_transfer_minutes
): void {
	$ordered  = MRT_get_service_stop_times_ordered( $s1 );
	$from_idx = MRT_journey_find_stop_index( $ordered, $from_station_id );
	if ( $from_idx === null ) {
		return;
	}
	$n = count( $ordered );
	for ( $k = $from_idx + 1; $k < $n; $k++ ) {
		MRT_journey_collect_transfer_at_stop(
			$candidates,
			$s1,
			$ordered[ $k ],
			$from_station_id,
			$to_station_id,
			$dateYmd,
			$min_transfer_minutes
		);
	}
}

/**
 * @param array<int, array<string, mixed>> $candidates
 * @param array<string, mixed>            $stop_row
 */
function MRT_journey_collect_transfer_at_stop(
	array &$candidates,
	int $s1,
	array $stop_row,
	$from_station_id,
	$to_station_id,
	string $dateYmd,
	int $min_transfer_minutes
): void {
	$xfer_id = (int) $stop_row['station_post_id'];
	if ( $xfer_id === (int) $to_station_id ) {
		return;
	}
	$xfer_arr = MRT_stop_effective_arrival( $stop_row );
	if ( $xfer_arr === '' || ! MRT_validate_time_hhmm( $xfer_arr ) ) {
		return;
	}
	$earliest = MRT_add_minutes_to_hhmm( $xfer_arr, $min_transfer_minutes );
	if ( $earliest === null ) {
		return;
	}
	foreach ( MRT_find_connections_departing_not_before( $xfer_id, $to_station_id, $dateYmd, $earliest ) as $c2 ) {
		if ( (int) $c2['service_id'] === $s1 ) {
			continue;
		}
		$dep2 = MRT_journey_connection_departure_hhmm( $c2 );
		if ( ! MRT_journey_transfer_wait_is_valid( $xfer_arr, $dep2 ) ) {
			continue;
		}
		$wait = MRT_journey_transfer_wait_minutes( $xfer_arr, $dep2 );
		if ( $wait === null ) {
			continue;
		}
		$transfer = MRT_journey_transfer_option( $s1, $from_station_id, $xfer_id, $to_station_id, $dateYmd, $c2 );
		if ( $transfer === null ) {
			continue;
		}
		$candidates[] = array(
			'transfer'   => $transfer,
			'priority'   => MRT_journey_transfer_station_priority( $xfer_id ),
			'wait'       => $wait,
			'departure'  => $dep2,
			'dedupe_key' => $s1 . '-' . $xfer_id . '-' . (int) $c2['service_id'] . '-' . $dep2,
		);
	}
}

/**
 * Append transfer options (two legs), sorted by hub priority then wait time.
 *
 * @param array<int, mixed>   $results Out results (by ref)
 * @param array<string, bool> $seen Keys (by ref)
 * @param int                 $from_station_id Origin A
 * @param int                 $to_station_id Destination B
 * @param string              $dateYmd Date
 * @param int                 $min_transfer_minutes Min transfer time
 */
function MRT_journey_append_transfer_options( array &$results, array &$seen, $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes ) {
	$candidates = MRT_journey_collect_transfer_candidates( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes );
	usort( $candidates, 'MRT_journey_compare_transfer_candidates' );
	foreach ( $candidates as $row ) {
		$key = $row['dedupe_key'];
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$results[]    = $row['transfer'];
	}
}

/**
 * Build one transfer option from two service legs.
 *
 * @param array<string, mixed> $connection Second-leg connection row
 * @return array<string, mixed>|null
 */
function MRT_journey_transfer_option( int $first_service_id, $from_station_id, int $transfer_station_id, $to_station_id, string $dateYmd, array $connection ): ?array {
	$leg1 = MRT_journey_build_leg_segment( $first_service_id, $from_station_id, $transfer_station_id, $dateYmd );
	if ( $leg1 === null ) {
		return null;
	}
	$leg2 = MRT_journey_leg_from_connection_row( $connection, $dateYmd, $transfer_station_id, $to_station_id );
	return array(
		'connection_type'     => 'transfer',
		'transfer_station_id' => $transfer_station_id,
		'legs'                => array( $leg1, $leg2 ),
	);
}

/**
 * Direct and optional two-leg connections (same day, one transfer max)
 *
 * @param int    $from_station_id From
 * @param int    $to_station_id To
 * @param string $dateYmd Date
 * @param int    $min_transfer_minutes Minimum minutes between arrival and next departure
 * @param bool   $include_direct Include single-service connections
 * @return array<int, array<string, mixed>>
 */
function MRT_find_multi_leg_connections( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes = 5, $include_direct = true ) {
	$results = array();
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return $results;
	}
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return $results;
	}
	if ( $include_direct ) {
		foreach ( MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) as $conn ) {
			$results[] = MRT_journey_wrap_direct_multi( $conn, $dateYmd, $from_station_id, $to_station_id );
		}
	}
	$seen = array();
	$min  = $min_transfer_minutes > 0 ? (int) $min_transfer_minutes : MRT_journey_min_transfer_minutes();
	MRT_journey_append_transfer_options( $results, $seen, $from_station_id, $to_station_id, $dateYmd, $min );
	return $results;
}
