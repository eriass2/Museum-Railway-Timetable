<?php
/**
 * Lennakatten import – run logic (CSV fixture).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/import/csv/loader.php';
require_once MRT_PATH . 'inc/import/lennakatten/traffic-demo-data.php';

/**
 * Import mode for Lennakatten fixture (replace fixture-owned data).
 */
function MRT_lennakatten_import_mode(): string {
	return 'override';
}

/**
 * Run the Lennakatten CSV import and return raw stats.
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_run_lennakatten_import_package() {
	$result = MRT_csv_import_package( MRT_csv_lennakatten_fixture_path(), MRT_lennakatten_import_mode() );
	if ( ! is_wp_error( $result ) ) {
		MRT_lennakatten_apply_traffic_demo_data();
	}
	return $result;
}

/**
 * Run the Lennakatten import from CSV fixture.
 *
 * @return string Success/error message
 */
function MRT_run_lennakatten_import() {
	$result = MRT_run_lennakatten_import_package();
	if ( is_wp_error( $result ) ) {
		return $result->get_error_message();
	}
	return sprintf(
		/* translators: 1: station count, 2: route count, 3: timetable count, 4: service count */
		__( 'Re-import complete. Stations: %1$d, Routes: %2$d, Timetables: %3$d, Services: %4$d. Fixture data replaced; posts without a fixture code were removed.', 'museum-railway-timetable' ),
		(int) ( $result['stations'] ?? 0 ),
		(int) ( $result['routes'] ?? 0 ),
		(int) ( $result['timetables'] ?? 0 ),
		(int) ( $result['services'] ?? 0 )
	);
}
