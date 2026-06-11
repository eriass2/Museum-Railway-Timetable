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
require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';

/**
 * Parse and validate trip fields from a REST/CSV body.
 *
 * End station is derived from stop times (A7), not from the request body.
 *
 * @param array<string, mixed> $body Request body.
 * @return array{route_id: int, train_type_id: int, line_code?: string}|WP_Error
 */
function MRT_parse_trip_input( array $body ) {
	$route_id = MRT_parse_trip_route_id_from_body( $body );
	if ( is_wp_error( $route_id ) ) {
		return $route_id;
	}
	if ( $route_id <= 0 ) {
		return new WP_Error( 'route', __( 'Route is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$route = get_post( $route_id );
	if ( ! $route instanceof WP_Post || $route->post_type !== MRT_POST_TYPE_ROUTE ) {
		return new WP_Error( 'route', __( 'Route not found.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}

	$parsed = array(
		'route_id'      => $route_id,
		'train_type_id' => (int) ( $body['train_type_id'] ?? 0 ),
	);
	$line_code = trim( (string) ( $body['line_code'] ?? '' ) );
	if ( $line_code !== '' ) {
		$parsed['line_code'] = $line_code;
	}
	return $parsed;
}

/**
 * @param array<string, mixed> $body
 * @return int|WP_Error
 */
function MRT_parse_trip_route_id_from_body( array $body ) {
	$line_code         = trim( (string) ( $body['line_code'] ?? '' ) );
	$toward_station_id = (int) ( $body['toward_station_id'] ?? 0 );
	if ( $line_code !== '' && $toward_station_id > 0 ) {
		$toward_code = MRT_station_code_for_post_id( $toward_station_id );
		$route_id    = MRT_line_resolve_route_id( $line_code, $toward_code );
		if ( $route_id <= 0 ) {
			return new WP_Error(
				'line',
				__( 'Could not resolve route for the selected line and direction.', 'museum-railway-timetable' ),
				array( 'status' => 400 )
			);
		}
		return $route_id;
	}
	return (int) ( $body['route_id'] ?? 0 );
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
 * @param array{route_id: int, train_type_id: int, line_code?: string} $parsed Parsed trip fields.
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
	$line_code = trim( (string) ( $parsed['line_code'] ?? '' ) );
	if ( $line_code !== '' ) {
		update_post_meta( $service_id, MRT_service_line_code_meta_key(), $line_code );
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
