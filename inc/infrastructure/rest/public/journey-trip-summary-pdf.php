<?php
/**
 * REST: trip summary PDF download for journey wizard.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register trip summary PDF REST route.
 */
function MRT_rest_register_journey_trip_summary_pdf_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/journey/trip-summary/pdf',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_journey_trip_summary_pdf_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function MRT_rest_journey_trip_summary_pdf_handler( WP_REST_Request $request ) {
	$parsed = MRT_trip_summary_parse_rest_input( MRT_rest_request_input( $request ) );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}

	$payload = MRT_trip_summary_pdf_download_payload( $parsed );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}

	return rest_ensure_response( $payload );
}
