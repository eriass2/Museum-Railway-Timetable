<?php
/**
 * Resolve and validate stable codes in a CSV package.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<int, array{file: string, line: int, message: string}> $errors
 * @return array<string, mixed>
 */
function MRT_csv_resolve_package_codes( array $package, array &$errors ): array {
	$files    = $package['files'] ?? array();
	$includes = (array) ( $package['manifest']['includes'] ?? array() );
	$resolved = array(
		'stations'    => array(),
		'lines'       => array(),
		'routes'      => array(),
		'timetables'  => array(),
		'services'    => array(),
		'train_slugs' => array(),
	);
	MRT_csv_validate_required_files( $includes, $files, $errors );
	MRT_csv_check_columns( $files, $errors );
	if ( in_array( 'stations', $includes, true ) ) {
		MRT_csv_resolve_stations( $files, $resolved, $errors );
	}
	if ( in_array( 'train_types', $includes, true ) ) {
		MRT_csv_resolve_train_types( $files, $resolved, $errors );
	}
	if ( in_array( 'lines', $includes, true ) ) {
		MRT_csv_resolve_lines( $files, $resolved, $errors );
	}
	if ( in_array( 'routes', $includes, true ) ) {
		MRT_csv_resolve_routes( $files, $resolved, $errors );
	}
	if ( in_array( 'timetables', $includes, true ) ) {
		MRT_csv_resolve_timetables( $files, $resolved, $errors );
	}
	if ( in_array( 'services', $includes, true ) ) {
		MRT_csv_resolve_services( $files, $resolved, $errors );
	}
	return $resolved;
}

/**
 * @param array<int, string> $includes
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_required_files( array $includes, array $files, array &$errors ): void {
	$need = array(
		'stations'   => array( 'stations.csv' ),
		'lines'      => array( 'lines.csv', 'line_stations.csv', 'branch_junctions.csv' ),
		'routes'     => array( 'routes.csv', 'route_stations.csv' ),
		'timetables' => array( 'timetables.csv', 'timetable_dates.csv' ),
		'services'   => array( 'services.csv', 'service_train_types.csv' ),
		'stoptimes'  => array( 'stoptimes.csv' ),
	);
	foreach ( $need as $type => $csv_files ) {
		if ( ! in_array( $type, $includes, true ) ) {
			continue;
		}
		foreach ( $csv_files as $csv ) {
			if ( empty( $files[ $csv ] ) ) {
				$errors[] = MRT_csv_error( $csv, 0, 'Required file missing for includes.' );
			}
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_check_columns( array $files, array &$errors ): void {
	$required = MRT_csv_required_columns();
	foreach ( $files as $name => $rows ) {
		if ( ! isset( $required[ $name ] ) || $rows === array() ) {
			continue;
		}
		$first = $rows[0];
		foreach ( $required[ $name ] as $col ) {
			if ( ! array_key_exists( $col, $first ) ) {
				$errors[] = MRT_csv_error( $name, 1, "Missing column: {$col}" );
			}
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_stations( array $files, array &$resolved, array &$errors ): void {
	$slug_to_name = array();
	foreach ( (array) ( $files['stations.csv'] ?? array() ) as $row ) {
		if ( trim( $row['name'] ?? '' ) === '' ) {
			MRT_csv_add_row_error( $row, 'Station name is required.', $errors );
			continue;
		}
		$code = trim( $row['station_code'] ?? '' );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $row['name'] );
		}
		MRT_csv_register_code( 'station', $code, $row['name'], $slug_to_name, $row, $errors );
		$resolved['stations'][ $code ] = $row;
	}
}

/**
 * @param array<string, string> $slug_to_name
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_register_code(
	string $kind,
	string $code,
	string $label,
	array &$slug_to_name,
	array $row,
	array &$errors
): void {
	if ( $code === '' ) {
		MRT_csv_add_row_error( $row, "Could not derive {$kind} code.", $errors );
		return;
	}
	if ( isset( $slug_to_name[ $code ] ) && $slug_to_name[ $code ] !== $label ) {
		MRT_csv_add_row_error( $row, "Code \"{$code}\" conflicts with \"{$slug_to_name[$code]}\".", $errors );
		return;
	}
	$slug_to_name[ $code ] = $label;
}
