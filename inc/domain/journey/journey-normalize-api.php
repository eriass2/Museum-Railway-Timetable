<?php
/**
 * Journey normalize: api
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_normalize_multi_leg_for_api( array $item, $dateYmd ) {
	$legs     = $item['legs'];
	$duration = MRT_normalize_total_duration_from_legs( $legs );
	$notices  = array();
	$cancelled = false;
	foreach ( $legs as $leg ) {
		$nid = isset( $leg['service_id'] ) ? (int) $leg['service_id'] : 0;
		if ( $nid <= 0 ) {
			continue;
		}
		$n = MRT_get_service_notice( $nid, $dateYmd );
		if ( MRT_notice_indicates_cancelled( $n ) ) {
			$cancelled = true;
		}
		if ( $n !== '' ) {
			$notices[] = $n;
		}
	}
	$last      = count( $legs ) - 1;
	$dep_first = (string) ( $legs[0]['from_departure'] ?? '' );
	$arr_last  = (string) ( $legs[ $last ]['to_arrival'] ?? '' );
	$transfer_wait = null;
	if ( count( $legs ) > 1 ) {
		$transfer_wait = MRT_journey_transfer_wait_minutes(
			(string) ( $legs[0]['to_arrival'] ?? '' ),
			(string) ( $legs[1]['from_departure'] ?? '' )
		);
	}
	return array(
		'connection_type'     => $item['connection_type'] ?? 'transfer',
		'transfer_station_id' => $item['transfer_station_id'] ?? null,
		'transfer_wait_minutes' => $transfer_wait,
		'legs'                => $legs,
		'duration_minutes'    => $duration,
		'segments'            => array(),
		'notice'              => implode( "\n", array_unique( $notices ) ),
		'is_cancelled'        => $cancelled,
		'service_id'          => isset( $legs[0]['service_id'] ) ? (int) $legs[0]['service_id'] : 0,
		'departure'           => $dep_first,
		'arrival'             => $arr_last,
		'from_departure'      => $dep_first,
		'to_arrival'          => $arr_last,
		'service_name'        => MRT_journey_multi_leg_service_label( $item, $legs ),
		'train_type'          => MRT_journey_multi_leg_train_type_label( $legs ),
	);
}

function MRT_normalize_connection_for_api( $item, $dateYmd, $from_station_id, $to_station_id ) {
	$flat = MRT_flatten_wrapped_direct_connection( $item );
	if ( $flat !== null ) {
		$item = $flat;
	}
	if ( isset( $item['legs'] ) && is_array( $item['legs'] ) && count( $item['legs'] ) > 1 ) {
		return MRT_normalize_multi_leg_for_api( $item, $dateYmd );
	}
	$conn  = $item;
	$sid   = intval( $conn['service_id'] ?? 0 );
	$dep   = MRT_connection_row_departure_at_from( $conn );
	$arr   = ! empty( $conn['to_arrival'] ) ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' );
	$tt    = $sid > 0 ? MRT_get_service_train_type_for_date( $sid, $dateYmd ) : null;
	$num   = $sid > 0 ? get_post_meta( $sid, 'mrt_service_number', true ) : '';
	$extra = MRT_normalize_segments_single_service( $sid, $from_station_id, $to_station_id, $dateYmd );
	$dur   = $extra['duration_minutes'];
	if ( $dur === null ) {
		$dur = MRT_format_duration_minutes( $dep, $arr );
	}
	$leg   = $sid > 0 ? MRT_journey_build_leg_segment( $sid, $from_station_id, $to_station_id, $dateYmd ) : null;
	$legs  = $leg !== null ? array( $leg ) : array();
	return array(
		'connection_type'     => 'direct',
		'transfer_station_id' => null,
		'legs'                => $legs,
		'service_id'          => $sid,
		'departure'           => $dep,
		'arrival'             => $arr,
		'from_departure'      => $dep,
		'to_arrival'          => $arr,
		'duration_minutes'    => $dur,
		'train_type'          => $tt ? $tt->name : (string) ( $conn['train_type'] ?? '' ),
		'train_type_slug'     => $tt ? $tt->slug : '',
		'train_type_icon'     => $tt
			? MRT_get_train_type_symbol_key( $tt )
			: MRT_get_train_type_symbol_key_from_label( (string) ( $conn['train_type'] ?? '' ) ),
		'service_name'        => (string) ( $conn['service_name'] ?? '' ),
		'service_number'      => $num !== '' && $num !== null ? (string) $num : ( $sid > 0 ? (string) $sid : '' ),
		'route_name'          => (string) ( $conn['route_name'] ?? '' ),
		'destination'         => $leg !== null
			? (string) ( $leg['destination'] ?? '' )
			: ( $sid > 0
				? MRT_journey_service_destination_label( $sid )
				: MRT_journey_leg_destination_label( $to_station_id ) ),
		'direction'           => (string) ( $conn['direction'] ?? '' ),
		'segments'            => $extra['segments'],
		'notice'              => $extra['notice'],
		'is_cancelled'        => ! empty( $extra['is_cancelled'] ),
	);
}
