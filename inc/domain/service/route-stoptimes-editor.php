<?php
/**
 * Route stop times editor row builders.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, mixed>>
 */
function MRT_map_existing_stoptimes_by_station( int $service_id ): array {
	if ( $service_id <= 0 ) {
		return array();
	}
	global $wpdb;
	$stoptimes_table    = $wpdb->prefix . 'mrt_stoptimes';
	$stoptimes          = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $stoptimes_table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
			$service_id
		),
		ARRAY_A
	);
	$existing_stoptimes = array();
	foreach ( $stoptimes as $st ) {
		$existing_stoptimes[ $st['station_post_id'] ] = $st;
	}
	return $existing_stoptimes;
}

/**
 * @param array<int>                       $route_stations Route station IDs.
 * @param array<int, array<string, mixed>> $existing_stoptimes Existing rows keyed by station.
 * @return array<int, array<string, mixed>>
 */
function MRT_build_stoptimes_station_rows( array $route_stations, array $existing_stoptimes ): array {
	if ( $route_stations === array() ) {
		return array();
	}
	$station_posts = get_posts(
		array(
			'post_type'      => 'mrt_station',
			'post__in'       => $route_stations,
			'posts_per_page' => -1,
			'orderby'        => 'post__in',
			'fields'         => 'all',
		)
	);

	$stations = array();
	foreach ( $station_posts as $index => $station ) {
		$st         = $existing_stoptimes[ $station->ID ] ?? null;
		$stops_here = $st !== null;
		$sequence   = $st ? $st['stop_sequence'] : ( $index + 1 );

		$stations[] = array(
			'id'              => $station->ID,
			'name'            => $station->post_title,
			'sequence'        => $sequence,
			'stops_here'      => $stops_here,
			'arrival_time'    => $st ? $st['arrival_time'] : '',
			'departure_time'  => $st ? $st['departure_time'] : '',
			'pickup_allowed'  => $st ? ! empty( $st['pickup_allowed'] ) : true,
			'dropoff_allowed' => $st ? ! empty( $st['dropoff_allowed'] ) : true,
		);
	}
	return $stations;
}
