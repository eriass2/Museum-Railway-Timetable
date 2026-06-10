<?php
/**
 * REST: journey wizard feedback.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/feedback/wizard-feedback.php';

/**
 * Register wizard feedback REST routes.
 */
function MRT_rest_register_wizard_feedback_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/wizard/feedback',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_wizard_feedback_create_handler',
			'permission_callback' => 'MRT_rest_can_read_public',
		)
	);
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/feedback',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_feedback_list_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/feedback/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'MRT_rest_feedback_update_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_wizard_feedback_create_handler( WP_REST_Request $request ) {
	$input = MRT_rest_request_input( $request );
	if ( ! empty( $input['website'] ) ) {
		return rest_ensure_response( array( 'saved' => true ) );
	}
	$limited = MRT_rest_wizard_feedback_rate_limited( $request );
	if ( is_wp_error( $limited ) ) {
		return $limited;
	}
	$result = MRT_feedback_create( $input );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response(
		array(
			'saved' => true,
			'id'    => (int) $result['id'],
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function MRT_rest_wizard_feedback_rate_limited( WP_REST_Request $request ) {
	$ip    = MRT_rest_feedback_client_ip( $request );
	$key   = 'mrt_feedback_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return new WP_Error( 'mrt_feedback_rate_limited', __( 'För många rapporter. Försök igen om en stund.', 'museum-railway-timetable' ), array( 'status' => 429 ) );
	}
	set_transient( $key, $count + 1, 60 );
	return true;
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_feedback_client_ip( WP_REST_Request $request ): string {
	$forwarded = (string) $request->get_header( 'X-Forwarded-For' );
	if ( $forwarded !== '' ) {
		return trim( explode( ',', $forwarded )[0] );
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_feedback_list_handler( WP_REST_Request $request ) {
	$limit = (int) $request->get_param( 'limit' );
	return rest_ensure_response(
		array(
			'items' => MRT_feedback_list( $limit > 0 ? $limit : 50 ),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_feedback_update_handler( WP_REST_Request $request ) {
	$input  = MRT_rest_request_input( $request );
	$result = MRT_feedback_update_status(
		(int) $request->get_param( 'id' ),
		(string) ( $input['status'] ?? '' )
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}
