<?php
/**
 * Reference and stoptime validation.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/stop-time-modes.php';

/**
 * @param array<string, mixed>      $resolved
 * @param array<string, mixed>      $package
 * @param array<string, array<string, bool>>|null $existing_codes
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_references(
	array $resolved,
	array $package,
	?array $existing_codes,
	array &$errors
): void {
	$files     = (array) ( $package['files'] ?? array() );
	$includes  = (array) ( $package['manifest']['includes'] ?? array() );
	$stations  = MRT_csv_merged_code_set( 'stations', $resolved, $existing_codes );
	$routes    = MRT_csv_merged_code_set( 'routes', $resolved, $existing_codes );
	$timetables = MRT_csv_merged_code_set( 'timetables', $resolved, $existing_codes );
	$services  = MRT_csv_merged_code_set( 'services', $resolved, $existing_codes );
	$train     = $resolved['train_slugs'] ?? array();

	if ( in_array( 'routes', $includes, true ) ) {
		MRT_csv_validate_route_station_rows( $files, $stations, $routes, $errors );
	}
	if ( in_array( 'timetables', $includes, true ) ) {
		MRT_csv_validate_timetable_date_rows( $files, $timetables, $errors );
	}
	if ( in_array( 'services', $includes, true ) ) {
		MRT_csv_validate_service_rows( $files, $stations, $routes, $timetables, $train, $errors );
	}
	if ( in_array( 'stoptimes', $includes, true ) ) {
		MRT_csv_validate_stoptime_refs( $files, $stations, $services, $errors );
	}
}

/**
 * @param array<string, mixed> $resolved
 * @param array<string, array<string, bool>>|null $existing_codes
 * @return array<string, bool>
 */
function MRT_csv_merged_code_set( string $type, array $resolved, ?array $existing_codes ): array {
	$key  = rtrim( $type, 's' ) === $type ? $type . 's' : $type;
	$from = array();
	if ( $type === 'stations' ) {
		$from = array_fill_keys( array_keys( (array) ( $resolved['stations'] ?? array() ) ), true );
	}
	if ( $type === 'routes' ) {
		$from = array_fill_keys( array_keys( (array) ( $resolved['routes'] ?? array() ) ), true );
	}
	if ( $type === 'timetables' ) {
		$from = array_fill_keys( array_keys( (array) ( $resolved['timetables'] ?? array() ) ), true );
	}
	if ( $type === 'services' ) {
		$from = array_fill_keys( array_keys( (array) ( $resolved['services'] ?? array() ) ), true );
	}
	$extra = (array) ( $existing_codes[ $type ] ?? array() );
	return $from + $extra;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, bool> $stations
 * @param array<string, bool> $routes
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_route_station_rows( array $files, array $stations, array $routes, array &$errors ): void {
	foreach ( (array) ( $files['route_stations.csv'] ?? array() ) as $row ) {
		$rc = $row['route_code'] ?? '';
		$sc = $row['station_code'] ?? '';
		if ( $rc !== '' && ! isset( $routes[ $rc ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown route_code \"{$rc}\".", $errors );
		}
		if ( $sc !== '' && ! isset( $stations[ $sc ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown station_code \"{$sc}\".", $errors );
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, bool> $timetables
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_timetable_date_rows( array $files, array $timetables, array &$errors ): void {
	foreach ( (array) ( $files['timetable_dates.csv'] ?? array() ) as $row ) {
		$tc = $row['timetable_code'] ?? '';
		if ( $tc !== '' && ! isset( $timetables[ $tc ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown timetable_code \"{$tc}\".", $errors );
		}
		if ( ! MRT_csv_is_valid_date( $row['date'] ?? '' ) ) {
			MRT_csv_add_row_error( $row, 'Invalid date (expected YYYY-MM-DD).', $errors );
		}
	}
}

function MRT_csv_is_valid_date( string $date ): bool {
	if ( $date === '' ) {
		return false;
	}
	$dt = DateTime::createFromFormat( 'Y-m-d', $date );
	return $dt instanceof DateTime && $dt->format( 'Y-m-d' ) === $date;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_service_rows(
	array $files,
	array $stations,
	array $routes,
	array $timetables,
	array $train,
	array &$errors
): void {
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		MRT_csv_check_fk( $row, 'timetable_code', $timetables, $errors );
		MRT_csv_check_fk( $row, 'route_code', $routes, $errors );
		MRT_csv_check_fk( $row, 'end_station_code', $stations, $errors );
	}
	foreach ( (array) ( $files['service_train_types.csv'] ?? array() ) as $row ) {
		$slug = $row['train_type_slug'] ?? '';
		if ( $slug !== '' && $train !== array() && ! isset( $train[ $slug ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown train_type_slug \"{$slug}\".", $errors );
		}
	}
}

/**
 * @param array<string, bool> $set
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_check_fk( array $row, string $field, array $set, array &$errors ): void {
	$val = $row[ $field ] ?? '';
	if ( $val === '' ) {
		MRT_csv_add_row_error( $row, "Missing {$field}.", $errors );
		return;
	}
	if ( ! isset( $set[ $val ] ) ) {
		MRT_csv_add_row_error( $row, "Unknown {$field} \"{$val}\".", $errors );
	}
}

/**
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_stoptimes( array $resolved, array &$errors ): void {
	// Sequence uniqueness checked in stoptime ref pass via validate_stoptime_refs extended below.
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_stoptime_refs( array $files, array $stations, array $services, array &$errors ): void {
	$seen = array();
	foreach ( (array) ( $files['stoptimes.csv'] ?? array() ) as $row ) {
		$svc = $row['service_code'] ?? '';
		$seq = $row['sequence'] ?? '';
		if ( $svc !== '' && ! isset( $services[ $svc ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown service_code \"{$svc}\".", $errors );
		}
		$sc = $row['station_code'] ?? '';
		if ( $sc !== '' && ! isset( $stations[ $sc ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown station_code \"{$sc}\".", $errors );
		}
		$key = $svc . '#' . $seq;
		if ( isset( $seen[ $key ] ) ) {
			MRT_csv_add_row_error( $row, 'Duplicate sequence for service.', $errors );
		}
		$seen[ $key ] = true;
		MRT_csv_validate_time_field( $row, 'arrival_time', $errors );
		MRT_csv_validate_time_field( $row, 'departure_time', $errors );
		foreach ( array( 'ank_pickup_mode', 'ank_dropoff_mode', 'avg_pickup_mode', 'avg_dropoff_mode' ) as $field ) {
			$val = $row[ $field ] ?? '';
			if ( $val === '' ) {
				continue;
			}
			if ( ! MRT_stop_time_mode_is_valid( $val ) ) {
				MRT_csv_add_row_error( $row, "Invalid {$field} (expected none, scheduled, or on_request).", $errors );
			}
		}
	}
}

/**
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_time_field( array $row, string $field, array &$errors ): void {
	$val = $row[ $field ] ?? '';
	if ( $val === '' ) {
		return;
	}
	if ( ! preg_match( '/^\d{2}:\d{2}$/', $val ) ) {
		MRT_csv_add_row_error( $row, "Invalid {$field} (expected HH:MM).", $errors );
	}
}
