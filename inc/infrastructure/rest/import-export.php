<?php
/**
 * REST: CSV import and export.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/import/csv/loader.php';

/**
 * Register import/export routes.
 */
function MRT_rest_register_import_export_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/import/csv',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_import_csv_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/export/csv',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_export_csv_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/data/clear',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_clear_plugin_data_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);
}

/**
 * Delete all plugin-owned timetable data (stations, routes, services, settings, prices).
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_clear_plugin_data_handler( WP_REST_Request $request ) {
	unset( $request );
	if ( ! function_exists( 'MRT_clear_all_plugin_data' ) ) {
		return new WP_Error(
			'unavailable',
			__( 'Clear data is not available.', 'museum-railway-timetable' ),
			array( 'status' => 500 )
		);
	}
	MRT_clear_all_plugin_data();
	return rest_ensure_response( array( 'cleared' => true ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_import_csv_handler( WP_REST_Request $request ) {
	$files = $request->get_file_params();
	if ( empty( $files['file']['tmp_name'] ) || ! file_exists( $files['file']['tmp_name'] ) ) {
		return new WP_Error( 'no_file', __( 'No file uploaded.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	$mode = $request->get_param( 'mode' ) === 'override' ? 'override' : 'merge';
	$result = MRT_csv_import_package( (string) $files['file']['tmp_name'], $mode );
	if ( is_wp_error( $result ) ) {
		return new WP_Error(
			'import_failed',
			$result->get_error_message(),
			array(
				'status' => 400,
				'data'   => $result->get_error_data(),
			)
		);
	}
	if ( function_exists( 'MRT_sync_timetable_public_pages' ) ) {
		MRT_sync_timetable_public_pages();
	}
	return rest_ensure_response(
		array(
			'imported' => true,
			'stats'    => is_array( $result ) ? $result : array(),
			'mode'     => $mode,
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_export_csv_handler( WP_REST_Request $request ) {
	$tmpdir   = trailingslashit( get_temp_dir() ) . 'mrt-export-' . wp_generate_password( 8, false );
	$options  = array(
		'include_prices'   => rest_sanitize_boolean( $request->get_param( 'include_prices' ) ?? true ),
		'include_settings' => rest_sanitize_boolean( $request->get_param( 'include_settings' ) ?? true ),
	);
	$zip_path = $tmpdir . '.zip';
	$result   = MRT_csv_export_zip( $zip_path, $options );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$raw = file_get_contents( $zip_path );
	if ( ! is_string( $raw ) ) {
		return new WP_Error( 'export_failed', __( 'Could not read export file.', 'museum-railway-timetable' ), array( 'status' => 500 ) );
	}
	@unlink( $zip_path );
	$filename = 'mrt-timetable-' . gmdate( 'Y-m-d' ) . '.zip';
	return rest_ensure_response(
		array(
			'filename'       => $filename,
			'content_base64' => base64_encode( $raw ),
		)
	);
}
