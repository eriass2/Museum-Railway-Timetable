<?php
/**
 * Load manifest and CSV tables from an opened package directory.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read manifest.json or infer one from CSV files in the directory.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_csv_load_manifest_from_dir( string $dir ) {
	$manifest_path = trailingslashit( $dir ) . 'manifest.json';
	if ( is_file( $manifest_path ) ) {
		return MRT_csv_parse_manifest_file( $manifest_path );
	}
	$manifest = MRT_csv_build_manifest_from_dir( $dir );
	if ( ( $manifest['includes'] ?? array() ) === array() ) {
		return new WP_Error( 'mrt_csv_manifest', 'No CSV files found in package.' );
	}
	return $manifest;
}

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_csv_parse_manifest_file( string $manifest_path ) {
	$raw = file_get_contents( $manifest_path );
	if ( ! is_string( $raw ) ) {
		return new WP_Error( 'mrt_csv_manifest', 'Could not read manifest.json.' );
	}
	$manifest = json_decode( $raw, true );
	if ( ! is_array( $manifest ) ) {
		return new WP_Error( 'mrt_csv_manifest', 'Invalid manifest.json.' );
	}
	return $manifest;
}

/**
 * @param array{dir: string, cleanup: bool} $opened
 * @return array<string, array<int, array<string, mixed>>>|WP_Error
 */
function MRT_csv_read_entity_csv_files( string $dir, array $opened ) {
	$files = array();
	foreach ( MRT_csv_entity_files() as $file ) {
		$full = trailingslashit( $dir ) . $file;
		if ( ! is_file( $full ) ) {
			continue;
		}
		$parsed = MRT_csv_read_file( $full );
		if ( is_wp_error( $parsed ) ) {
			MRT_csv_maybe_cleanup_package( $opened );
			return $parsed;
		}
		$files[ $file ] = $parsed['rows'];
	}
	return $files;
}
