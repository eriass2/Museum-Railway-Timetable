<?php
/**
 * Read CSV rows from a file.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse one CSV file into associative rows.
 *
 * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}|WP_Error
 */
function MRT_csv_read_file( string $path ) {
	if ( ! is_readable( $path ) ) {
		return new WP_Error( 'mrt_csv_missing', sprintf( 'CSV file not readable: %s', $path ) );
	}
	$handle = fopen( $path, 'rb' );
	if ( $handle === false ) {
		return new WP_Error( 'mrt_csv_open', sprintf( 'Could not open: %s', $path ) );
	}
	$headers = fgetcsv( $handle );
	if ( $headers === false ) {
		fclose( $handle );
		return array( 'headers' => array(), 'rows' => array() );
	}
	$headers = MRT_csv_normalize_headers( $headers );
	$rows    = array();
	$line    = 1;
	while ( ( $data = fgetcsv( $handle ) ) !== false ) {
		++$line;
		if ( MRT_csv_row_is_empty( $data ) ) {
			continue;
		}
		$rows[] = MRT_csv_combine_row( $headers, $data, $path, $line );
	}
	fclose( $handle );
	return array( 'headers' => $headers, 'rows' => $rows );
}

/**
 * @param array<int, string> $headers
 * @return array<int, string>
 */
function MRT_csv_normalize_headers( array $headers ): array {
	$out = array();
	foreach ( $headers as $h ) {
		$h = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $h );
		$out[] = trim( (string) $h );
	}
	return $out;
}

/**
 * @param array<int, string|null> $data
 */
function MRT_csv_row_is_empty( array $data ): bool {
	foreach ( $data as $cell ) {
		if ( trim( (string) $cell ) !== '' ) {
			return false;
		}
	}
	return true;
}

/**
 * @param array<int, string>  $headers
 * @param array<int, string>  $data
 * @return array<string, string>
 */
function MRT_csv_combine_row( array $headers, array $data, string $path, int $line ): array {
	$row = array();
	foreach ( $headers as $i => $key ) {
		if ( $key === '' ) {
			continue;
		}
		$row[ $key ] = trim( (string) ( $data[ $i ] ?? '' ) );
	}
	$row['_file'] = basename( $path );
	$row['_line'] = (string) $line;
	return $row;
}
