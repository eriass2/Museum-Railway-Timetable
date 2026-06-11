<?php
/**
 * Import lines.csv into the line registry option.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-csv.php';

/**
 * @param array<string, array<int, array<string, string>>> $files
 */
function MRT_csv_import_lines( array $files ): int {
	$line_rows = (array) ( $files['lines.csv'] ?? array() );
	if ( $line_rows === array() ) {
		return 0;
	}
	$station_rows = MRT_csv_group_by_field( $files, 'line_stations.csv', 'line_code' );
	$registry     = array();
	foreach ( $line_rows as $row ) {
		$code = trim( (string) ( $row['line_code'] ?? '' ) );
		if ( $code === '' ) {
			continue;
		}
		$stations = array();
		foreach ( $station_rows[ $code ] ?? array() as $srow ) {
			$stations[] = $srow;
		}
		usort(
			$stations,
			static fn ( array $a, array $b ): int => (int) ( $a['sequence'] ?? 0 ) <=> (int) ( $b['sequence'] ?? 0 )
		);
		$codes = array();
		foreach ( $stations as $srow ) {
			$station_code = trim( (string) ( $srow['station_code'] ?? '' ) );
			if ( $station_code !== '' ) {
				$codes[] = $station_code;
			}
		}
		$registry[ $code ] = array(
			'title'         => sanitize_text_field( (string) ( $row['title'] ?? '' ) ),
			'kind'          => sanitize_key( (string) ( $row['kind'] ?? '' ) ),
			'station_codes' => $codes,
		);
	}
	MRT_set_line_registry( $registry );
	return count( $registry );
}
