<?php
/**
 * REST: dashboard.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/admin/dashboard-data.php';

/**
 * Register dashboard routes.
 */
function MRT_rest_register_dashboard_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/dashboard',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_get_dashboard',
			'permission_callback' => 'MRT_rest_can_read',
		)
	);
}

/**
 * GET /dashboard
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function MRT_rest_get_dashboard( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( MRT_get_dashboard_payload() );
}
