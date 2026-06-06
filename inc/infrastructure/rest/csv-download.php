<?php
/**
 * REST helpers: zip file as base64 download response.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a temp zip path under the system temp directory.
 */
function MRT_rest_temp_zip_path( string $prefix ): string {
	$base = function_exists( 'get_temp_dir' ) ? get_temp_dir() : sys_get_temp_dir();
	return trailingslashit( $base ) . $prefix . wp_generate_password( 8, false ) . '.zip';
}

/**
 * Read a zip file, delete it, and return a REST download payload.
 *
 * @return array<string, string>|WP_Error
 */
function MRT_rest_zip_download_payload( string $zip_path, string $filename ) {
	$raw = file_get_contents( $zip_path );
	@unlink( $zip_path );
	if ( ! is_string( $raw ) ) {
		return new WP_Error(
			'export_failed',
			__( 'Could not read export file.', 'museum-railway-timetable' ),
			array( 'status' => 500 )
		);
	}
	return array(
		'filename'       => $filename,
		'content_base64' => base64_encode( $raw ),
	);
}
