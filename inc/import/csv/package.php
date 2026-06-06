<?php
/**
 * Open a CSV package (directory or zip).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array{dir: string, cleanup: bool}|WP_Error
 */
function MRT_csv_open_package( string $path, string $upload_filename = '' ) {
	$path = rtrim( $path, '/\\' );
	if ( is_dir( $path ) ) {
		return array(
			'dir'     => $path,
			'cleanup' => false,
		);
	}
	if ( ! is_file( $path ) ) {
		return new WP_Error( 'mrt_csv_path', 'Package path must be a directory, zip, or CSV file.' );
	}
	if ( MRT_csv_upload_is_single_csv( $path, $upload_filename ) ) {
		return MRT_csv_stage_single_csv_upload( $path, $upload_filename );
	}
	return MRT_csv_extract_zip( $path );
}

/**
 * @return array{dir: string, cleanup: bool}|WP_Error
 */
function MRT_csv_extract_zip( string $zip_path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'mrt_csv_zip', 'ZipArchive is not available.' );
	}
	$zip = new ZipArchive();
	if ( $zip->open( $zip_path ) !== true ) {
		return new WP_Error( 'mrt_csv_zip', 'Could not open zip archive.' );
	}
	$dir = trailingslashit( sys_get_temp_dir() ) . 'mrt-csv-' . wp_generate_password( 12, false );
	if ( ! wp_mkdir_p( $dir ) ) {
		$zip->close();
		return new WP_Error( 'mrt_csv_zip', 'Could not create temp directory.' );
	}
	$zip->extractTo( $dir );
	$zip->close();
	$root = MRT_csv_find_package_root( $dir );
	if ( $root === '' ) {
		MRT_csv_remove_dir( $dir );
		return new WP_Error( 'mrt_csv_zip', 'No CSV package found in zip (manifest.json or *.csv expected).' );
	}
	return array(
		'dir' => $root,
		'cleanup' => true,
	);
}

/**
 * Locate directory containing manifest.json (zip may have one subfolder).
 */
function MRT_csv_find_package_root( string $dir ): string {
	$dir = rtrim( $dir, '/\\' );
	if ( MRT_csv_dir_has_package_markers( $dir ) ) {
		return $dir;
	}
	$children = glob( $dir . '/*', GLOB_ONLYDIR );
	if ( ! is_array( $children ) || count( $children ) !== 1 ) {
		return '';
	}
	$child = $children[0];
	return MRT_csv_dir_has_package_markers( $child ) ? $child : '';
}

/**
 * Recursively delete a directory.
 */
function MRT_csv_remove_dir( string $dir ): void {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$items = scandir( $dir );
	if ( ! is_array( $items ) ) {
		return;
	}
	foreach ( $items as $item ) {
		if ( $item === '.' || $item === '..' ) {
			continue;
		}
		$path = $dir . DIRECTORY_SEPARATOR . $item;
		if ( is_dir( $path ) ) {
			MRT_csv_remove_dir( $path );
		} else {
			wp_delete_file( $path );
		}
	}
	rmdir( $dir );
}

/**
 * Load manifest and CSV tables from a package directory.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_csv_load_package( string $package_path, string $upload_filename = '' ) {
	$opened = MRT_csv_open_package( $package_path, $upload_filename );
	if ( is_wp_error( $opened ) ) {
		return $opened;
	}
	$dir           = $opened['dir'];
	$manifest_path = trailingslashit( $dir ) . 'manifest.json';
	if ( is_file( $manifest_path ) ) {
		$raw = file_get_contents( $manifest_path );
		if ( ! is_string( $raw ) ) {
			MRT_csv_maybe_cleanup_package( $opened );
			return new WP_Error( 'mrt_csv_manifest', 'Could not read manifest.json.' );
		}
		$manifest = json_decode( $raw, true );
		if ( ! is_array( $manifest ) ) {
			MRT_csv_maybe_cleanup_package( $opened );
			return new WP_Error( 'mrt_csv_manifest', 'Invalid manifest.json.' );
		}
	} else {
		$manifest = MRT_csv_build_manifest_from_dir( $dir );
		if ( ( $manifest['includes'] ?? array() ) === array() ) {
			MRT_csv_maybe_cleanup_package( $opened );
			return new WP_Error( 'mrt_csv_manifest', 'No CSV files found in package.' );
		}
	}
	$package = array(
		'dir'      => $dir,
		'cleanup'  => $opened['cleanup'],
		'manifest' => $manifest,
		'files'    => array(),
	);
	$file_map = MRT_csv_entity_files();
	foreach ( $file_map as $file ) {
		$full = trailingslashit( $dir ) . $file;
		if ( ! is_file( $full ) ) {
			continue;
		}
		$parsed = MRT_csv_read_file( $full );
		if ( is_wp_error( $parsed ) ) {
			MRT_csv_maybe_cleanup_package( $opened );
			return $parsed;
		}
		$package['files'][ $file ] = $parsed['rows'];
	}
	return $package;
}

/**
 * @param array{dir: string, cleanup: bool} $opened
 */
function MRT_csv_maybe_cleanup_package( array $opened ): void {
	if ( ! empty( $opened['cleanup'] ) ) {
		MRT_csv_remove_dir( $opened['dir'] );
	}
}

/**
 * Release temp extraction directory.
 *
 * @param array<string, mixed> $package
 */
function MRT_csv_close_package( array $package ): void {
	if ( empty( $package['cleanup'] ) || empty( $package['dir'] ) ) {
		return;
	}
	MRT_csv_remove_dir( (string) $package['dir'] );
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
