<?php
/**
 * CSV package import orchestration.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import a CSV package directory or zip.
 *
 * @param string $path   Package path
 * @param string $mode   merge|override
 * @return array<string, mixed>|WP_Error
 */
function MRT_csv_import_package( string $path, string $mode = 'merge', string $upload_filename = '' ) {
	$package = MRT_csv_load_package( $path, $upload_filename );
	if ( is_wp_error( $package ) ) {
		MRT_log_wp_error( 'MRT_csv_import_package', $package );
		return $package;
	}
	$existing = MRT_csv_existing_codes_from_db();
	$result   = MRT_csv_validate_package( $package, $existing );
	if ( ! $result['valid'] ) {
		MRT_csv_close_package( $package );
		$error = new WP_Error( 'mrt_csv_invalid', 'CSV validation failed.', $result['errors'] );
		MRT_log_wp_error( 'MRT_csv_import_package', $error );
		return $error;
	}
	if ( $mode === 'override' ) {
		MRT_csv_delete_orphans( $package );
	}
	$stats = MRT_csv_run_import( $package );
	MRT_csv_close_package( $package );
	return array_merge( $stats, array( 'mode' => $mode ) );
}

/**
 * @param array<string, mixed> $package
 * @return array<string, int>
 */
function MRT_csv_run_import( array $package ): array {
	$includes = (array) ( $package['manifest']['includes'] ?? array() );
	$files    = (array) ( $package['files'] ?? array() );
	$stats    = array(
		'stations'   => 0,
		'lines'      => 0,
		'routes'     => 0,
		'timetables' => 0,
		'services'   => 0,
	);
	$maps     = array(
		'station' => array(),
		'route' => array(),
		'timetable' => array(),
		'service' => array(),
	);

	if ( in_array( 'stations', $includes, true ) ) {
		$stats['stations'] = MRT_csv_import_stations( $files, $maps );
		MRT_csv_import_station_train_changes( $files, $maps );
	}
	if ( in_array( 'train_types', $includes, true ) ) {
		MRT_csv_import_train_types( $files );
	}
	if ( in_array( 'lines', $includes, true ) ) {
		$stats['lines'] = MRT_csv_import_lines( $files );
	}
	if ( in_array( 'routes', $includes, true ) ) {
		$stats['routes'] = MRT_csv_import_routes( $files, $maps );
	}
	if ( in_array( 'timetables', $includes, true ) ) {
		$stats['timetables'] = MRT_csv_import_timetables( $files, $maps );
	}
	if ( in_array( 'services', $includes, true ) ) {
		$stats['services'] = MRT_csv_import_services( $files, $maps );
	}
	if ( in_array( 'stoptimes', $includes, true ) ) {
		MRT_csv_import_stoptimes( $files, $maps );
	}
	if ( in_array( 'settings', $includes, true ) ) {
		MRT_csv_import_settings( $files );
		MRT_csv_import_ticket_copy_notes( $files );
	}
	if ( in_array( 'brand_tokens', $includes, true ) ) {
		MRT_csv_import_brand_tokens( $files );
	}
	if ( in_array( 'prices', $includes, true ) ) {
		MRT_csv_import_price_schema( $files );
		MRT_csv_import_prices( $files );
	}
	return $stats;
}
