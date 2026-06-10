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
	$manifest = MRT_csv_load_manifest_from_dir( $opened['dir'] );
	if ( is_wp_error( $manifest ) ) {
		MRT_csv_maybe_cleanup_package( $opened );
		return $manifest;
	}
	$files = MRT_csv_read_entity_csv_files( $opened['dir'], $opened );
	if ( is_wp_error( $files ) ) {
		return $files;
	}
	return array(
		'dir'      => $opened['dir'],
		'cleanup'  => $opened['cleanup'],
		'manifest' => $manifest,
		'files'    => $files,
	);
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
