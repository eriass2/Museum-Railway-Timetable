<?php
/**
 * Journey normalize: segments
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_normalize_segments_single_service( $service_id, $from_id, $to_id, $dateYmd ) {
	$detail = MRT_get_connection_journey_detail( $service_id, $from_id, $to_id );
	$notice = MRT_get_service_notice( $service_id, $dateYmd );
	return array(
		'segments'         => $detail['stops'],
		'duration_minutes' => $detail['duration_minutes'],
		'notice'           => $notice,
		'is_cancelled'     => MRT_notice_indicates_cancelled( $notice ),
	);
}

function MRT_flatten_wrapped_direct_connection( array $item ) {
	if ( ( $item['connection_type'] ?? '' ) !== 'direct' || empty( $item['legs'][0] ) ) {
		return null;
	}
	$leg = $item['legs'][0];
	$sid = (int) ( $leg['service_id'] ?? 0 );
	if ( $sid <= 0 ) {
		return null;
	}
	$route_id = get_post_meta( $sid, 'mrt_service_route_id', true );
	$dest     = MRT_get_service_destination( $sid );
	return array(
		'service_id'     => $sid,
		'service_name'   => get_the_title( $sid ) ?: ( '#' . $sid ),
		'route_name'     => $route_id ? get_the_title( (int) $route_id ) : '',
		'destination'    => $dest['destination'],
		'direction'      => $dest['direction'],
		'train_type'     => (string) ( $leg['train_type'] ?? '' ),
		'from_departure' => (string) ( $leg['from_departure'] ?? '' ),
		'from_arrival'   => '',
		'to_arrival'     => (string) ( $leg['to_arrival'] ?? '' ),
		'to_departure'   => '',
		'from_sequence'  => 0,
		'to_sequence'    => 0,
	);
}
