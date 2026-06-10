<?php
/**
 * Route domain: labels
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_get_route_label_from_end_station( $route_id, $end_station_id ) {
	$end_station_post = get_post( $end_station_id );
	if ( ! $end_station_post ) {
		return '';
	}

	$end_stations     = MRT_get_route_end_stations( $route_id );
	$start_station_id = $end_stations['start'];
	$start_station    = $start_station_id ? get_post( $start_station_id ) : null;

	if ( $start_station ) {
		return MRT_route_from_to_label( $start_station->post_title, $end_station_post->post_title );
	}

	return sprintf( __( 'Route to %s', 'museum-railway-timetable' ), $end_station_post->post_title );
}

function MRT_get_route_label_from_direction( $route_id, $direction, $station_posts = array() ) {
	if ( $direction !== 'dit' && $direction !== 'från' ) {
		return '';
	}

	// Fetch station posts if not provided
	if ( empty( $station_posts ) ) {
		$route_stations = MRT_get_route_stations( $route_id );
		if ( ! empty( $route_stations ) ) {
			$station_posts = get_posts(
				array(
					'post_type'      => 'mrt_station',
					'post__in'       => $route_stations,
					'posts_per_page' => -1,
					'orderby'        => 'post__in',
					'fields'         => 'all',
				)
			);
		}
	}

	if ( empty( $station_posts ) ) {
		return '';
	}

	$first_station = $station_posts[0];
	$last_station  = end( $station_posts );

	if ( $direction === 'dit' ) {
		return MRT_route_from_to_label( $first_station->post_title, $last_station->post_title );
	}

	return MRT_route_from_to_label( $last_station->post_title, $first_station->post_title );
}

function MRT_get_route_label( $route, $direction, $services_list = array(), $station_posts = array() ) {
	if ( ! $route ) {
		return '';
	}

	$route_id    = $route->ID;
	$route_label = $route->post_title;
	$label       = MRT_get_route_label_from_services_end_station( $route_id, $services_list );
	if ( $label !== '' ) {
		return $label;
	}

	$direction_label = MRT_get_route_label_from_direction( $route_id, $direction, $station_posts );
	return $direction_label !== '' ? $direction_label : $route_label;
}

function MRT_get_route_label_from_services_end_station( $route_id, array $services_list ): string {
	$end_station_ids = array();
	foreach ( $services_list as $service_data ) {
		$service = MRT_route_label_service_object( $service_data );
		if ( ! $service ) {
			continue;
		}
		$end_station_id = get_post_meta( $service->ID, 'mrt_service_end_station_id', true );
		if ( $end_station_id ) {
			$end_station_ids[] = $end_station_id;
		}
	}
	return MRT_get_route_label_from_unique_end_station( $route_id, $end_station_ids );
}

function MRT_route_label_service_object( $service_data ) {
	if ( is_array( $service_data ) && isset( $service_data['service'] ) && is_object( $service_data['service'] ) ) {
		return $service_data['service'];
	}
	return is_object( $service_data ) ? $service_data : null;
}

function MRT_get_route_label_from_unique_end_station( $route_id, array $end_station_ids ): string {
	if ( empty( $end_station_ids ) ) {
		return '';
	}
	$unique_end_stations = array_unique( $end_station_ids );
	if ( count( $unique_end_stations ) !== 1 ) {
		return '';
	}
	return MRT_get_route_label_from_end_station( $route_id, reset( $unique_end_stations ) );
}
