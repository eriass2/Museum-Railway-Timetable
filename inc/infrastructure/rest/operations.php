<?php
/**
 * REST: operational actions (cancel traffic, etc.).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/admin/cancel-traffic.php';

/**
 * Register operational routes.
 */
function MRT_rest_register_operations_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/operations/cancel-traffic',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_cancel_traffic_handler',
			'permission_callback' => 'MRT_rest_can_edit_operations',
		)
	);
}

/**
 * POST /operations/cancel-traffic — mark all today's (or given date) services as cancelled.
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_cancel_traffic_handler( WP_REST_Request $request ) {
	$body = (array) $request->get_json_params();
	$date = isset( $body['date'] ) ? sanitize_text_field( (string) $body['date'] ) : '';
	if ( $date === '' ) {
		$datetime = MRT_get_current_datetime();
		$date     = gmdate( 'Y-m-d', $datetime['timestamp'] );
	}
	$notice = isset( $body['notice'] ) ? sanitize_textarea_field( (string) $body['notice'] ) : MRT_CANCEL_TRAFFIC_NOTICE;

	$result = MRT_cancel_traffic_for_date( $date, $notice );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}
