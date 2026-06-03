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
function MRT_csv_open_package( string $path ) {
	$path = rtrim( $path, '/\\' );
	if ( is_dir( $path ) ) {
		return array(
			'dir' => $path,
			'cleanup' => false,
		);
	}
	if ( is_file( $path ) ) {
		return MRT_csv_extract_zip( $path );
	}
	return new WP_Error( 'mrt_csv_path', 'Package path must be a directory or zip file.' );
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
		return new WP_Error( 'mrt_csv_zip', 'manifest.json not found in zip.' );
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
	if ( is_file( $dir . '/manifest.json' ) ) {
		return $dir;
	}
	$children = glob( $dir . '/*', GLOB_ONLYDIR );
	if ( ! is_array( $children ) || count( $children ) !== 1 ) {
		return '';
	}
	$child = $children[0];
	return is_file( $child . '/manifest.json' ) ? $child : '';
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
function MRT_csv_load_package( string $package_path ) {
	$opened = MRT_csv_open_package( $package_path );
	if ( is_wp_error( $opened ) ) {
		return $opened;
	}
	$dir     = $opened['dir'];
	$manifest_path = trailingslashit( $dir ) . 'manifest.json';
	$raw     = file_get_contents( $manifest_path );
	if ( ! is_string( $raw ) ) {
		MRT_csv_maybe_cleanup_package( $opened );
		return new WP_Error( 'mrt_csv_manifest', 'Could not read manifest.json.' );
	}
	$manifest = json_decode( $raw, true );
	if ( ! is_array( $manifest ) ) {
		MRT_csv_maybe_cleanup_package( $opened );
		return new WP_Error( 'mrt_csv_manifest', 'Invalid manifest.json.' );
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
