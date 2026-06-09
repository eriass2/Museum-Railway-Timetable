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
require_once __DIR__ . '/csv-download.php';

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
		'/export/csv/template',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_export_csv_template_handler',
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
	$mode        = $request->get_param( 'mode' ) === 'override' ? 'override' : 'merge';
	$upload_name = isset( $files['file']['name'] ) ? (string) $files['file']['name'] : '';
	$result      = MRT_csv_import_package( (string) $files['file']['tmp_name'], $mode, $upload_name );
	if ( is_wp_error( $result ) ) {
		return new WP_Error(
			'import_failed',
			MRT_csv_import_error_message( $result ),
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
function MRT_rest_export_csv_template_handler( WP_REST_Request $request ) {
	unset( $request );
	$zip_path = MRT_rest_temp_zip_path( 'mrt-template-' );
	$result   = MRT_csv_export_template_zip( $zip_path );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$payload = MRT_rest_zip_download_payload( $zip_path, 'mrt-import-template.zip' );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}
	return rest_ensure_response( $payload );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_export_csv_handler( WP_REST_Request $request ) {
	$options = array(
		'include_prices'   => rest_sanitize_boolean( $request->get_param( 'include_prices' ) ?? true ),
		'include_settings' => rest_sanitize_boolean( $request->get_param( 'include_settings' ) ?? true ),
	);
	$zip_path = MRT_rest_temp_zip_path( 'mrt-export-' );
	$result   = MRT_csv_export_zip( $zip_path, $options );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$filename = 'mrt-timetable-' . gmdate( 'Y-m-d' ) . '.zip';
	$payload  = MRT_rest_zip_download_payload( $zip_path, $filename );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}
	return rest_ensure_response( $payload );
}
