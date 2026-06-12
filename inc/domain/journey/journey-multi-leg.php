<?php
/**
 * Multi-leg journey leg builders (shared by engine and API normalization).
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
	$dest  = MRT_journey_service_destination_label( (int) $service_id );
	$dur   = MRT_format_duration_minutes( $dep, $arr );
	return array(
		'service_id'       => (int) $service_id,
		'from_station_id'  => (int) $from_station_id,
		'to_station_id'    => (int) $to_station_id,
		'from_departure'   => $dep,
		'to_arrival'       => $arr,
		'duration_minutes' => $dur,
		'train_type'       => $tt ? $tt->name : '',
		'train_type_slug'  => $tt ? $tt->slug : '',
		'train_type_icon'  => $tt ? MRT_get_train_type_symbol_key( $tt ) : '',
		'service_number'   => $num !== '' && $num !== null ? (string) $num : (string) $service_id,
		'destination'      => $dest,
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
	$sid  = intval( $conn['service_id'] );
	$tt   = MRT_get_service_train_type_for_date( $sid, $dateYmd );
	$num  = get_post_meta( $sid, 'mrt_service_number', true );
	$dest = MRT_journey_service_destination_label( $sid );
	$dep  = $conn['from_departure'] ? (string) $conn['from_departure'] : (string) ( $conn['from_arrival'] ?? '' );
	$arr  = $conn['to_arrival'] ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' );
	return array(
		'service_id'       => $sid,
		'from_station_id'  => (int) $from_station_id,
		'to_station_id'    => (int) $to_station_id,
		'from_departure'   => $dep,
		'to_arrival'       => $arr,
		'duration_minutes' => MRT_format_duration_minutes( $dep, $arr ),
		'train_type'       => $tt ? $tt->name : (string) ( $conn['train_type'] ?? '' ),
		'train_type_slug'  => $tt ? $tt->slug : '',
		'train_type_icon'  => $tt ? MRT_get_train_type_symbol_key( $tt ) : MRT_get_train_type_symbol_key_from_label( (string) ( $conn['train_type'] ?? '' ) ),
		'service_number'   => $num !== '' && $num !== null ? (string) $num : (string) $sid,
		'destination'      => $dest,
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
 * Departure HH:MM for a leg from connection row.
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
