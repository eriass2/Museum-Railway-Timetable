<?php
/**
 * Stage single CSV uploads as one-file import packages.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed CSV basenames for single-file upload (same names as in a zip package).
 *
 * @return list<string>
 */
function MRT_csv_allowed_upload_filenames(): array {
	return array_values( MRT_csv_entity_files() );
}

/**
 * Whether an upload is a single CSV file (not zip).
 */
function MRT_csv_upload_is_single_csv( string $path, string $upload_filename ): bool {
	$name = MRT_csv_upload_basename( $path, $upload_filename );
	return (bool) preg_match( '/\.csv$/i', $name );
}

/**
 * Resolve upload name to a canonical CSV filename, or null if unknown.
 */
function MRT_csv_resolve_upload_csv_filename( string $upload_filename ): ?string {
	$base = strtolower( MRT_csv_upload_basename( '', $upload_filename ) );
	foreach ( MRT_csv_allowed_upload_filenames() as $file ) {
		if ( strtolower( $file ) === $base ) {
			return $file;
		}
	}
	return null;
}

/**
 * @return array{dir: string, cleanup: bool}|WP_Error
 */
function MRT_csv_stage_single_csv_upload( string $tmp_path, string $upload_filename ) {
	$canonical = MRT_csv_resolve_upload_csv_filename( $upload_filename );
	if ( $canonical === null ) {
		return new WP_Error(
			'mrt_csv_single',
			__( 'Unknown CSV file. Use a standard name from the export package, e.g. stoptimes.csv.', 'museum-railway-timetable' )
		);
	}
	$dir = trailingslashit( sys_get_temp_dir() ) . 'mrt-csv-single-' . wp_generate_password( 12, false );
	if ( ! wp_mkdir_p( $dir ) ) {
		return new WP_Error( 'mrt_csv_single', 'Could not create temp directory.' );
	}
	$dest = $dir . '/' . $canonical;
	if ( ! copy( $tmp_path, $dest ) ) {
		MRT_csv_remove_dir( $dir );
		return new WP_Error( 'mrt_csv_single', 'Could not stage CSV upload.' );
	}
	return array(
		'dir'     => $dir,
		'cleanup' => true,
	);
}

/**
 * Prefer original upload filename over temp path basename.
 */
function MRT_csv_upload_basename( string $path, string $upload_filename ): string {
	$name = $upload_filename !== '' ? $upload_filename : $path;
	$name = str_replace( '\\', '/', $name );
	return basename( $name );
}
