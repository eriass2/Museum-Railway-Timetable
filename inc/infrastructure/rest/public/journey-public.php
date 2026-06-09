<?php
/**
 * REST: public journey search endpoints.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register public journey REST routes.
 */
function MRT_rest_register_journey_public_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/journey/search',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_journey_search_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/journey/calendar',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_journey_calendar_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/journey/connection-detail',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_journey_connection_detail_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_journey_search_handler( WP_REST_Request $request ) {
	$result = MRT_journey_search_response( MRT_rest_request_input( $request ) );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_journey_calendar_handler( WP_REST_Request $request ) {
	$result = MRT_journey_calendar_response( MRT_rest_request_input( $request ) );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_journey_connection_detail_handler( WP_REST_Request $request ) {
	$result = MRT_journey_connection_detail_response( MRT_rest_request_input( $request ) );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}
