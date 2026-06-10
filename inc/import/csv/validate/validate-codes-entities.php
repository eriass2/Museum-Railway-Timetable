<?php
/**
 * Continue code resolution for routes, timetables, services.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_train_types( array $files, array &$resolved, array &$errors ): void {
	foreach ( (array) ( $files['train_types.csv'] ?? array() ) as $row ) {
		$slug = trim( $row['slug'] ?? '' );
		if ( $slug === '' ) {
			MRT_csv_add_row_error( $row, 'Train type slug is required.', $errors );
			continue;
		}
		$resolved['train_slugs'][ $slug ] = true;
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_routes( array $files, array &$resolved, array &$errors ): void {
	$slug_to_name = array();
	foreach ( (array) ( $files['routes.csv'] ?? array() ) as $row ) {
		if ( trim( $row['title'] ?? '' ) === '' ) {
			MRT_csv_add_row_error( $row, 'Route title is required.', $errors );
			continue;
		}
		$code = trim( $row['route_code'] ?? '' );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $row['title'] );
		}
		MRT_csv_register_code( 'route', $code, $row['title'], $slug_to_name, $row, $errors );
		$resolved['routes'][ $code ] = $row;
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_timetables( array $files, array &$resolved, array &$errors ): void {
	$slug_to_name = array();
	foreach ( (array) ( $files['timetables.csv'] ?? array() ) as $row ) {
		if ( trim( $row['title'] ?? '' ) === '' ) {
			MRT_csv_add_row_error( $row, 'Timetable title is required.', $errors );
			continue;
		}
		$code = trim( $row['timetable_code'] ?? '' );
		if ( $code === '' ) {
			$code = MRT_csv_slugify( $row['title'] );
		}
		MRT_csv_register_code( 'timetable', $code, $row['title'], $slug_to_name, $row, $errors );
		$resolved['timetables'][ $code ] = $row;
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_services( array $files, array &$resolved, array &$errors ): void {
	$slug_to_name = array();
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		$code = trim( $row['service_code'] ?? '' );
		if ( $code === '' ) {
			$num  = trim( $row['service_number'] ?? 'x' );
			$code = MRT_csv_slugify(
				( $row['timetable_code'] ?? 'tt' ) . '-' . $num . '-' . ( $row['end_station_code'] ?? 'end' )
			);
		}
		MRT_csv_register_code( 'service', $code, $code, $slug_to_name, $row, $errors );
		$resolved['services'][ $code ] = $row;
	}
}
