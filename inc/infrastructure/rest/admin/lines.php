<?php
/**
 * REST: lines (line registry).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-rest-format.php';

/**
 * Register line routes.
 */
function MRT_rest_register_line_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/lines',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_list_lines_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_list_lines_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( array( 'items' => MRT_rest_format_lines_list() ) );
}
