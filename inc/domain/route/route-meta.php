<?php
/**
 * Route domain: meta
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_get_route_end_stations( $route_id ) {
	$start = get_post_meta( $route_id, 'mrt_route_start_station', true );
	$end   = get_post_meta( $route_id, 'mrt_route_end_station', true );
	return array(
		'start' => $start ? intval( $start ) : 0,
		'end'   => $end ? intval( $end ) : 0,
	);
}

function MRT_get_route_stations( $route_id ) {
	if ( ! $route_id || $route_id <= 0 ) {
		return array();
	}

	$route_stations = get_post_meta( $route_id, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		return array();
	}

	return $route_stations;
}

function MRT_update_route_terminus_station_meta( int $route_id, int $station_id, string $meta_key ): void {
	if ( $station_id > 0 ) {
		update_post_meta( $route_id, $meta_key, $station_id );
		return;
	}
	delete_post_meta( $route_id, $meta_key );
}
