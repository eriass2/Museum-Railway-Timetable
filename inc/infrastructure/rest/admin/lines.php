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

require_once MRT_PATH . 'inc/domain/line/line-csv.php';
require_once MRT_PATH . 'inc/domain/line/line-rest-format.php';
require_once MRT_PATH . 'inc/domain/line/line-registry-update.php';

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
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/lines/(?P<code>[a-z0-9-]+)',
		array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_update_line_handler',
				'permission_callback' => 'MRT_rest_can_manage',
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

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function MRT_rest_update_line_handler( WP_REST_Request $request ) {
	$code  = sanitize_key( (string) $request->get_param( 'code' ) );
	$body  = (array) $request->get_json_params();
	$title = array_key_exists( 'title', $body ) ? sanitize_text_field( (string) $body['title'] ) : null;
	$station_ids = null;
	if ( array_key_exists( 'station_ids', $body ) && is_array( $body['station_ids'] ) ) {
		$station_ids = array_map( 'intval', $body['station_ids'] );
	}
	if ( $code === '' ) {
		return new WP_Error( 'mrt_invalid_line', __( 'Line code is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	if ( $title === null && $station_ids === null ) {
		return new WP_Error( 'mrt_invalid_line', __( 'Nothing to update.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	if ( $title !== null ) {
		if ( $title === '' ) {
			return new WP_Error( 'mrt_invalid_line', __( 'Line title is required.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
		}
		if ( ! MRT_update_line_registry_title( $code, $title ) ) {
			return new WP_Error( 'mrt_unknown_line', __( 'Line not found.', 'museum-railway-timetable' ), array( 'status' => 404 ) );
		}
	}
	if ( $station_ids !== null ) {
		$result = MRT_update_line_registry_stations( $code, $station_ids );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
	}
	$entry = MRT_line_registry_entry( $code );
	$row   = MRT_rest_format_line_entry( $code, $entry );
	if ( ! is_array( $row ) ) {
		return new WP_Error( 'mrt_line_format', __( 'Could not format line.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	return rest_ensure_response( $row );
}
