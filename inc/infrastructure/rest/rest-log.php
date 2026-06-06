<?php
/**
 * REST API error logging (development).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log server-side REST failures for plugin routes (HTTP 500+).
 *
 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Response.
 * @param WP_REST_Server                                   $server  Server.
 * @param WP_REST_Request                                  $request Request.
 * @return mixed
 */
function MRT_rest_log_error_response( $response, $server, $request ) {
	unset( $server );

	if ( ! $response instanceof WP_REST_Response || ! $request instanceof WP_REST_Request ) {
		return $response;
	}

	if ( ! MRT_rest_should_log_response( $request, $response ) ) {
		return $response;
	}

	MRT_log(
		'REST ' . $request->get_method() . ' ' . $request->get_route(),
		array(
			'status'  => $response->get_status(),
			'payload' => $response->get_data(),
		)
	);

	return $response;
}

/**
 * Whether a REST response should be written to the debug log.
 */
function MRT_rest_should_log_response( WP_REST_Request $request, WP_REST_Response $response ): bool {
	$route = $request->get_route();
	if ( ! is_string( $route ) || ! str_starts_with( $route, '/' . MRT_REST_NAMESPACE ) ) {
		return false;
	}

	if ( str_ends_with( $route, '/dev/client-log' ) ) {
		return false;
	}

	return $response->get_status() >= 500;
}

add_filter( 'rest_post_dispatch', 'MRT_rest_log_error_response', 10, 3 );
