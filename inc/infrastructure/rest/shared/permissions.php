<?php
/**
 * REST API permission callbacks.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only admin REST (dashboard, lists, preview).
 */
function MRT_rest_can_read(): bool {
	return current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
}

/**
 * Full timetable data management (stations, routes, timetables, stop times bulk).
 */
function MRT_rest_can_manage(): bool {
	return current_user_can( 'manage_options' );
}

/**
 * Deviations and limited departure edits (edit_posts role).
 */
function MRT_rest_can_edit_operations(): bool {
	return MRT_rest_can_manage() || current_user_can( 'edit_posts' );
}

/**
 * Verify wp_rest nonce from a REST request.
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_verify_public_nonce( WP_REST_Request $request ): bool {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( is_array( $nonce ) ) {
		$nonce = isset( $nonce[0] ) ? (string) $nonce[0] : '';
	}
	if ( ! is_string( $nonce ) || $nonce === '' ) {
		$nonce = (string) $request->get_param( '_wpnonce' );
	}
	if ( $nonce === '' ) {
		return false;
	}
	return wp_verify_nonce( $nonce, 'wp_rest' ) !== false;
}

/**
 * Public frontend REST (wizard, overview) with nonce, or admin read.
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_can_read_public( WP_REST_Request $request ): bool {
	if ( MRT_rest_can_read() ) {
		return true;
	}
	return MRT_rest_verify_public_nonce( $request );
}

/**
 * Extract merged JSON/query params from a REST request.
 *
 * @param WP_REST_Request $request Request.
 * @return array<string, mixed>
 */
function MRT_rest_request_input( WP_REST_Request $request ): array {
	$json = $request->get_json_params();
	if ( is_array( $json ) && $json !== array() ) {
		return $json;
	}
	$params = $request->get_params();
	return is_array( $params ) ? $params : array();
}
