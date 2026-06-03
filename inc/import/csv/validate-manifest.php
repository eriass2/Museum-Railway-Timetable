<?php
/**
 * Validate a loaded CSV package.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed>      $package
 * @param array<string, array<string, bool>>|null $existing_codes DB codes for partial import
 * @return array{valid: bool, errors: array<int, array{file: string, line: int, message: string}>}
 */
function MRT_csv_validate_package( array $package, ?array $existing_codes = null ): array {
	$errors = array();
	MRT_csv_validate_manifest( $package, $errors );
	if ( $errors !== array() ) {
		return array(
			'valid' => false,
			'errors' => $errors,
		);
	}
	$resolved = MRT_csv_resolve_package_codes( $package, $errors );
	if ( $errors !== array() ) {
		return array(
			'valid' => false,
			'errors' => $errors,
		);
	}
	MRT_csv_validate_references( $resolved, $package, $existing_codes, $errors );
	MRT_csv_validate_stoptimes( $resolved, $errors );
	return array(
		'valid' => $errors === array(),
		'errors' => $errors,
	);
}

/**
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_manifest( array $package, array &$errors ): void {
	$manifest = $package['manifest'] ?? array();
	$version  = (string) ( $manifest['format_version'] ?? '' );
	if ( $version !== MRT_csv_format_version() ) {
		$errors[] = MRT_csv_error( 'manifest.json', 0, 'Unsupported format_version.' );
	}
	$includes = $manifest['includes'] ?? null;
	if ( ! is_array( $includes ) || $includes === array() ) {
		$errors[] = MRT_csv_error( 'manifest.json', 0, 'includes must be a non-empty array.' );
	}
}

/**
 * @return array{file: string, line: int, message: string}
 */
function MRT_csv_error( string $file, int $line, string $message ): array {
	return array(
		'file'    => $file,
		'line'    => $line,
		'message' => $message,
	);
}

/**
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_add_row_error( array $row, string $message, array &$errors ): void {
	$errors[] = MRT_csv_error(
		(string) ( $row['_file'] ?? 'unknown' ),
		(int) ( $row['_line'] ?? 0 ),
		$message
	);
}
