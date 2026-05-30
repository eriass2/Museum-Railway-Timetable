<?php
/**
 * REST: public journey and timetable frontend.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/public-handlers.php';

/**
 * Register public frontend REST routes.
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

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/day',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_timetable_day_handler',
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

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_timetable_day_handler( WP_REST_Request $request ) {
	$date       = sanitize_text_field( (string) $request->get_param( 'date' ) );
	$train_type = sanitize_text_field( (string) $request->get_param( 'train_type' ) );
	if ( $date === '' || ! MRT_validate_date( $date ) ) {
		return new WP_Error(
			'mrt_journey_date',
			__( 'Please select a valid date.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	$data = MRT_get_timetable_day_data( $date, $train_type );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( array( 'overview' => $data ) );
}
