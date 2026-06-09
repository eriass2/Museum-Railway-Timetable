<?php
/**
 * User-facing CSV import error formatting.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format validation errors for admin display.
 *
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_format_validation_errors( array $errors, int $limit = 6 ): string {
	if ( $errors === array() ) {
		return __( 'CSV validation failed.', 'museum-railway-timetable' );
	}
	$lines = array();
	foreach ( array_slice( $errors, 0, $limit ) as $error ) {
		$lines[] = MRT_csv_format_validation_error_line( $error );
	}
	if ( count( $errors ) > $limit ) {
		/* translators: %d: number of additional validation errors */
		$lines[] = sprintf( __( '… och %d fler fel.', 'museum-railway-timetable' ), count( $errors ) - $limit );
	}
	return implode( "\n", $lines );
}

/**
 * @param array{file?: string, line?: int, message?: string} $error
 */
function MRT_csv_format_validation_error_line( array $error ): string {
	$file = (string) ( $error['file'] ?? 'unknown' );
	$line = (int) ( $error['line'] ?? 0 );
	$msg  = (string) ( $error['message'] ?? '' );
	if ( $line > 0 ) {
		/* translators: 1: CSV filename, 2: line number, 3: error message */
		return sprintf( __( '%1$s rad %2$d: %3$s', 'museum-railway-timetable' ), $file, $line, $msg );
	}
	/* translators: 1: file or manifest name, 2: error message */
	return sprintf( __( '%1$s: %2$s', 'museum-railway-timetable' ), $file, $msg );
}

/**
 * Map import WP_Error to a REST-friendly message.
 */
function MRT_csv_import_error_message( WP_Error $result ): string {
	$message = $result->get_error_message();
	if ( $result->get_error_code() !== 'mrt_csv_invalid' ) {
		return $message;
	}
	$data = $result->get_error_data();
	return is_array( $data ) ? MRT_csv_format_validation_errors( $data ) : $message;
}
