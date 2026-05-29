<?php
/**
 * Read Lennakatten CSV fixture without WordPress database.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load CSV reader dependencies for fixture reads (no full import stack).
 */
function MRT_csv_ensure_fixture_read_deps(): void {
	static $loaded = false;
	if ( $loaded ) {
		return;
	}
	$dir = MRT_PATH . 'inc/import/csv/';
	require_once $dir . 'schema.php';
	require_once $dir . 'slugify.php';
	require_once $dir . 'reader.php';
	require_once $dir . 'package.php';
	$loaded = true;
}

/**
 * Path to bundled Lennakatten test fixture.
 */
function MRT_csv_lennakatten_fixture_path(): string {
	return MRT_PATH . 'testdata/fixtures/lennakatten';
}

/**
 * Loaded fixture package (cached).
 *
 * @return array<string, mixed>
 */
function MRT_csv_get_fixture_package(): array {
	static $package = null;
	if ( $package !== null ) {
		return $package;
	}
	MRT_csv_ensure_fixture_read_deps();
	$loaded = MRT_csv_load_package( MRT_csv_lennakatten_fixture_path() );
	$package = is_wp_error( $loaded ) ? array( 'files' => array() ) : $loaded;
	return $package;
}

/**
 * Traffic dates for one timetable in the fixture.
 *
 * @return array<int, string>
 */
function MRT_csv_fixture_timetable_dates( string $timetable_code ): array {
	$dates = array();
	foreach ( (array) ( MRT_csv_get_fixture_package()['files']['timetable_dates.csv'] ?? array() ) as $row ) {
		if ( ( $row['timetable_code'] ?? '' ) === $timetable_code ) {
			$dates[] = (string) $row['date'];
		}
	}
	sort( $dates );
	return $dates;
}

/**
 * GRÖN timetable dates from fixture.
 *
 * @return array<int, string>
 */
function MRT_csv_fixture_green_dates(): array {
	return MRT_csv_fixture_timetable_dates( 'green' );
}

/**
 * Timetable title from fixture.
 */
function MRT_csv_fixture_timetable_title( string $timetable_code ): string {
	foreach ( (array) ( MRT_csv_get_fixture_package()['files']['timetables.csv'] ?? array() ) as $row ) {
		if ( ( $row['timetable_code'] ?? '' ) === $timetable_code ) {
			return (string) ( $row['title'] ?? '' );
		}
	}
	return '';
}
