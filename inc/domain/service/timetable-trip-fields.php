<?php
/**
 * Shared trip (service) field parsing and persistence.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/timetable-trip-create.php';
require_once MRT_PATH . 'inc/domain/service/highlight.php';

/**
 * Parse and validate trip fields from a REST/CSV body.
 *
 * @param array<string, mixed> $body Request body.
 * @return array{route_id: int, train_type_id: int, end_station_id: int, direction: string}|WP_Error
 */
function MRT_parse_trip_input( array $body ) {
	$route_id = (int) ( $body['route_id'] ?? 0 );
	if ( $route_id <= 0 ) {
		return new WP_Error( 'route', __( 'Route is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$route = get_post( $route_id );
	if ( ! $route instanceof WP_Post || $route->post_type !== MRT_POST_TYPE_ROUTE ) {
		return new WP_Error( 'route', __( 'Route not found.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}

	$train_type_id  = (int) ( $body['train_type_id'] ?? 0 );
	$end_station_id = (int) ( $body['end_station_id'] ?? 0 );
	$direction      = '';
	if ( $end_station_id > 0 ) {
		$direction = MRT_calculate_direction_from_end_station( $route_id, $end_station_id );
	}

	return array(
		'route_id'       => $route_id,
		'train_type_id'  => $train_type_id,
		'end_station_id' => $end_station_id,
		'direction'      => $direction,
	);
}

/**
 * Persist route, destination, train type, title and optional highlight meta.
 *
 * @param array{route_id: int, train_type_id: int, end_station_id: int, direction: string} $parsed Parsed trip fields.
 * @param array<string, mixed>                                                          $body   Original request body.
 * @return true
 */
function MRT_persist_trip_fields( int $service_id, array $parsed, array $body ): bool {
	$route_id       = (int) $parsed['route_id'];
	$train_type_id  = (int) $parsed['train_type_id'];
	$end_station_id = (int) $parsed['end_station_id'];
	$direction      = (string) $parsed['direction'];

	update_post_meta( $service_id, 'mrt_service_route_id', $route_id );
	if ( $end_station_id > 0 ) {
		update_post_meta( $service_id, 'mrt_service_end_station_id', $end_station_id );
		if ( $direction !== '' ) {
			update_post_meta( $service_id, 'mrt_direction', $direction );
		} else {
			delete_post_meta( $service_id, 'mrt_direction' );
		}
	} else {
		delete_post_meta( $service_id, 'mrt_service_end_station_id' );
		delete_post_meta( $service_id, 'mrt_direction' );
	}

	if ( $train_type_id > 0 ) {
		wp_set_object_terms( $service_id, array( $train_type_id ), MRT_TAXONOMY_TRAIN_TYPE );
	} else {
		wp_set_object_terms( $service_id, array(), MRT_TAXONOMY_TRAIN_TYPE );
	}

	wp_update_post(
		array(
			'ID'         => $service_id,
			'post_title' => MRT_build_service_auto_title( $route_id, $end_station_id, $direction ),
		)
	);

	MRT_apply_service_number_and_highlight_meta( $service_id, $body );

	return true;
}
