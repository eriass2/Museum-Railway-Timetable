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
	$junctions    = MRT_csv_branch_junctions_by_line( $files );
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
		$kind  = sanitize_key( (string) ( $row['kind'] ?? '' ) );
		$entry = array(
			'title'         => sanitize_text_field( (string) ( $row['title'] ?? '' ) ),
			'kind'          => $kind,
			'station_codes' => $codes,
		);
		$corridor_after = trim( (string) ( $row['overview_corridor_after_station'] ?? '' ) );
		if ( $corridor_after !== '' ) {
			$entry['overview_corridor_after_station_code'] = $corridor_after;
		}
		$junction = $junctions[ $code ] ?? null;
		if ( is_array( $junction ) ) {
			$entry['junction_station_code'] = (string) ( $junction['junction_station_code'] ?? '' );
			$entry['requires_transfer']     = (bool) ( $junction['requires_transfer'] ?? false );
		} elseif ( $kind === 'pattern' ) {
			$entry['requires_transfer'] = false;
		}
		$registry[ $code ] = $entry;
	}
	MRT_set_line_registry( $registry );
	return count( $registry );
}

/**
 * @return array<string, array{junction_station_code: string, requires_transfer: bool}>
 */
function MRT_csv_branch_junctions_by_line( array $files ): array {
	$map = array();
	foreach ( (array) ( $files['branch_junctions.csv'] ?? array() ) as $row ) {
		$line_code = trim( (string) ( $row['line_code'] ?? '' ) );
		if ( $line_code === '' ) {
			continue;
		}
		$map[ $line_code ] = array(
			'junction_station_code' => trim( (string) ( $row['junction_station_code'] ?? '' ) ),
			'requires_transfer'     => in_array( strtolower( (string) ( $row['requires_transfer'] ?? '' ) ), array( '1', 'true', 'yes' ), true ),
		);
	}
	return $map;
}
