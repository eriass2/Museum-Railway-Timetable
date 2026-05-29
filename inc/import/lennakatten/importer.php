<?php
/**
 * Lennakatten import – run logic (CSV fixture).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/import/csv/loader.php';

/**
 * Run the Lennakatten import from CSV fixture.
 *
 * @return string Success/error message
 */
function MRT_run_lennakatten_import() {
	$result = MRT_csv_import_package( MRT_csv_lennakatten_fixture_path(), 'merge' );
	if ( is_wp_error( $result ) ) {
		return $result->get_error_message();
	}
	return sprintf(
		/* translators: 1: station count, 2: route count, 3: timetable count, 4: services created */
		__( 'Import complete. Stations: %1$d, Routes: %2$d, Timetables: %3$d, Services: %4$d.', 'museum-railway-timetable' ),
		(int) ( $result['stations'] ?? 0 ),
		(int) ( $result['routes'] ?? 0 ),
		(int) ( $result['timetables'] ?? 0 ),
		(int) ( $result['services'] ?? 0 )
	);
}
