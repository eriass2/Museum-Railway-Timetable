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
require_once MRT_PATH . 'inc/domain/route/route-direction.php';

/**
 * Parse and validate trip fields from a REST/CSV body.
 *
 * End station is derived from stop times (A7), not from the request body.
 *
 * @param array<string, mixed> $body Request body.
 * @return array{route_id: int, train_type_id: int}|WP_Error
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

	return array(
		'route_id'      => $route_id,
		'train_type_id' => (int) ( $body['train_type_id'] ?? 0 ),
	);
}

/**
 * Read stored end station and direction for title updates.
 *
 * @return array{end_station_id: int, direction: string}
 */
function MRT_service_stored_destination_meta( int $service_id, int $route_id ): array {
	$end_station_id = (int) get_post_meta( $service_id, 'mrt_service_end_station_id', true );
	$direction      = '';
	if ( $end_station_id > 0 ) {
		$direction = MRT_calculate_direction_from_end_station( $route_id, $end_station_id );
	} else {
		$direction = (string) get_post_meta( $service_id, 'mrt_direction', true );
	}
	return array(
		'end_station_id' => $end_station_id,
		'direction'      => $direction,
	);
}

/**
 * Persist route, train type, title and optional highlight meta.
 *
 * @param array{route_id: int, train_type_id: int} $parsed Parsed trip fields.
 * @param array<string, mixed>                     $body   Original request body.
 * @return true
 */
function MRT_persist_trip_fields( int $service_id, array $parsed, array $body ): bool {
	$route_id      = (int) $parsed['route_id'];
	$train_type_id = (int) $parsed['train_type_id'];
	$prev_route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );

	update_post_meta( $service_id, 'mrt_service_route_id', $route_id );
	if ( $route_id !== $prev_route_id ) {
		delete_post_meta( $service_id, 'mrt_service_end_station_id' );
		delete_post_meta( $service_id, 'mrt_direction' );
	}

	$destination = MRT_service_stored_destination_meta( $service_id, $route_id );

	if ( $train_type_id > 0 ) {
		wp_set_object_terms( $service_id, array( $train_type_id ), MRT_TAXONOMY_TRAIN_TYPE );
	} else {
		wp_set_object_terms( $service_id, array(), MRT_TAXONOMY_TRAIN_TYPE );
	}

	wp_update_post(
		array(
			'ID'         => $service_id,
			'post_title' => MRT_build_service_auto_title(
				$route_id,
				$destination['end_station_id'],
				$destination['direction']
			),
		)
	);

	MRT_apply_service_number_and_highlight_meta( $service_id, $body );

	return true;
}
