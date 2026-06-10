<?php
/**
 * REST: admin traffic notice messages.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/traffic-notices/public-notices.php';

/**
 * Register admin traffic notices routes.
 */
function MRT_rest_register_traffic_notices_admin_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/traffic-notices/messages',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_traffic_notices_messages_get_handler',
				'permission_callback' => 'MRT_rest_can_edit_operations',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_traffic_notices_messages_put_handler',
				'permission_callback' => 'MRT_rest_can_edit_operations',
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_traffic_notices_messages_get_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response(
		array(
			'messages' => MRT_public_notices_get_all(),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_traffic_notices_messages_put_handler( WP_REST_Request $request ) {
	$body     = MRT_rest_request_input( $request );
	$messages = isset( $body['messages'] ) && is_array( $body['messages'] ) ? $body['messages'] : null;
	if ( $messages === null ) {
		return new WP_Error( 'mrt_notice_payload', __( 'Ogiltig data.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$saved = MRT_public_notices_save_all( $messages );
	if ( is_wp_error( $saved ) ) {
		return $saved;
	}
	return rest_ensure_response(
		array(
			'saved'    => true,
			'messages' => $saved,
		)
	);
}
