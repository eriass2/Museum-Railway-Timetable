<?php
/**
 * Timetable trip (service) update helpers.
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
 * Verify service belongs to timetable.
 *
 * @return WP_Post|WP_Error
 */
function MRT_get_timetable_service_post( int $timetable_id, int $service_id ) {
	$post = get_post( $service_id );
	if ( ! $post instanceof WP_Post || $post->post_type !== MRT_POST_TYPE_SERVICE ) {
		return new WP_Error( 'not_found', __( 'Service not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	$owner = (int) get_post_meta( $service_id, 'mrt_service_timetable_id', true );
	if ( $owner !== $timetable_id ) {
		return new WP_Error( 'not_found', __( 'Service not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
	}
	return $post;
}

/**
 * Apply route, destination, train type and service number to an existing service.
 *
 * @param array<string, mixed> $body Request body.
 * @return true|WP_Error
 */
function MRT_apply_timetable_service_update( int $service_id, array $body ) {
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

	if ( array_key_exists( 'service_number', $body ) ) {
		$number = sanitize_text_field( (string) $body['service_number'] );
		if ( $number === '' ) {
			delete_post_meta( $service_id, 'mrt_service_number' );
		} else {
			update_post_meta( $service_id, 'mrt_service_number', $number );
		}
	}

	$auto_title = MRT_build_service_auto_title( $route_id, $end_station_id, $direction );
	wp_update_post(
		array(
			'ID'         => $service_id,
			'post_title' => $auto_title,
		)
	);

	if (
		array_key_exists( 'highlight_label', $body )
		|| array_key_exists( 'highlight_color', $body )
		|| array_key_exists( 'highlight_note', $body )
	) {
		MRT_apply_service_highlight_fields( $service_id, $body );
	}

	return true;
}
