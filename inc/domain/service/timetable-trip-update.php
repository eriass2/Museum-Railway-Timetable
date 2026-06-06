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

require_once MRT_PATH . 'inc/domain/service/timetable-trip-fields.php';

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
	$parsed = MRT_parse_trip_input( $body );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}

	return MRT_persist_trip_fields( $service_id, $parsed, $body );
}
