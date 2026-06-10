<?php
/**
 * Write CSV package files.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Write rows to a CSV file (UTF-8 with BOM for Excel).
 *
 * @param array<int, string>               $headers
 * @param array<int, array<string, mixed>> $rows
 */
function MRT_csv_write_file( string $path, array $headers, array $rows ): bool {
	$dir = dirname( $path );
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return false;
	}
	$handle = fopen( $path, 'wb' );
	if ( $handle === false ) {
		return false;
	}
	fwrite( $handle, "\xEF\xBB\xBF" );
	fputcsv( $handle, $headers );
	foreach ( $rows as $row ) {
		$line = array();
		foreach ( $headers as $h ) {
			$line[] = (string) ( $row[ $h ] ?? '' );
		}
		fputcsv( $handle, $line );
	}
	fclose( $handle );
	return true;
}

/**
 * Write manifest.json for a package directory.
 *
 * @param array<string, mixed> $manifest
 */
function MRT_csv_write_manifest( string $dir, array $manifest ): bool {
	if ( ! wp_mkdir_p( $dir ) ) {
		return false;
	}
	$json = wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! is_string( $json ) ) {
		return false;
	}
	return false !== file_put_contents( trailingslashit( $dir ) . 'manifest.json', $json . "\n" );
}

/**
 * Create a zip archive from a package directory.
 */
function MRT_csv_zip_directory( string $source_dir, string $zip_path ): bool {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return false;
	}
	$zip = new ZipArchive();
	if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
		return false;
	}
	$source_dir = trailingslashit( $source_dir );
	$iterator   = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $source_dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $iterator as $file ) {
		if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
			continue;
		}
		$relative = substr( $file->getPathname(), strlen( $source_dir ) );
		$zip->addFile( $file->getPathname(), str_replace( '\\', '/', $relative ) );
	}
	$zip->close();
	return true;
}
