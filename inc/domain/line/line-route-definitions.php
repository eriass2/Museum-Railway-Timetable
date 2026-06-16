<?php
/**
 * Derive directed route definitions from line registry CSV (LINES Fas C).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-lines.php';

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @return array<string, string>
 */
function MRT_csv_station_name_by_code( array $files ): array {
	$names = array();
	foreach ( (array) ( $files['stations.csv'] ?? array() ) as $row ) {
		$code = trim( (string) ( $row['station_code'] ?? '' ) );
		if ( $code === '' ) {
			continue;
		}
		$names[ $code ] = trim( (string) ( $row['name'] ?? $code ) );
	}
	return $names;
}

function MRT_csv_route_branch_code_for_line( string $line_code, string $kind ): string {
	if ( $kind === 'main' ) {
		return 'main';
	}
	if ( $kind === 'branch' ) {
		return $line_code;
	}
	return '';
}

/**
 * @param list<string> $station_codes
 */
function MRT_csv_route_title_for_stations( array $station_codes, array $station_names ): string {
	if ( $station_codes === array() ) {
		return '';
	}
	$first = $station_names[ $station_codes[0] ] ?? $station_codes[0];
	$last  = $station_names[ $station_codes[ count( $station_codes ) - 1 ] ] ?? $station_codes[ count( $station_codes ) - 1 ];
	return $first . ' – ' . $last;
}

/**
 * @param list<string> $station_codes
 * @return array{route_code: string, title: string, start_station_code: string, end_station_code: string, branch_code: string, station_codes: list<string>}|null
 */
function MRT_csv_line_derived_route_def(
	string $line_code,
	string $kind,
	array $station_codes,
	string $toward_station_code,
	array $station_names
): ?array {
	$route_code = MRT_line_route_code_for_toward( $line_code, $toward_station_code, $station_codes );
	if ( $route_code === '' || $station_codes === array() ) {
		return null;
	}
	$ordered = $station_codes;
	if ( $toward_station_code === ( $station_codes[0] ?? '' ) ) {
		$ordered = array_reverse( $station_codes );
	}
	return array(
		'route_code'         => $route_code,
		'title'              => MRT_csv_route_title_for_stations( $ordered, $station_names ),
		'start_station_code' => $ordered[0],
		'end_station_code'   => $ordered[ count( $ordered ) - 1 ],
		'branch_code'        => MRT_csv_route_branch_code_for_line( $line_code, $kind ),
		'station_codes'      => $ordered,
	);
}

/**
 * @param array{route_code: string, title: string, start_station_code: string, end_station_code: string, branch_code: string, station_codes: list<string>} $def
 * @return array<string, string>
 */
function MRT_csv_line_derived_route_csv_row( array $def ): array {
	return array(
		'route_code'         => $def['route_code'],
		'title'              => $def['title'],
		'start_station_code' => $def['start_station_code'],
		'end_station_code'   => $def['end_station_code'],
		'branch_code'        => $def['branch_code'],
	);
}

/**
 * @param list<string> $station_codes
 * @param array<string, string> $station_names
 * @return array<string, array{row: array<string, string>, station_codes: list<string>}>
 */
function MRT_line_derived_route_defs_for_codes(
	string $line_code,
	string $kind,
	array $station_codes,
	array $station_names
): array {
	if ( $station_codes === array() ) {
		return array();
	}
	$defs         = array();
	$first        = $station_codes[0] ?? '';
	$last         = $station_codes[ count( $station_codes ) - 1 ] ?? '';
	$toward_codes = ( $line_code === 'linnes-uppsala' || $kind === 'pattern' ) ? array( $last ) : array( $last, $first );
	foreach ( $toward_codes as $toward ) {
		if ( $toward === '' ) {
			continue;
		}
		$def = MRT_csv_line_derived_route_def( $line_code, $kind, $station_codes, $toward, $station_names );
		if ( ! is_array( $def ) ) {
			continue;
		}
		$defs[ $def['route_code'] ] = array(
			'row'           => MRT_csv_line_derived_route_csv_row( $def ),
			'station_codes' => $def['station_codes'],
		);
	}
	return $defs;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @return array<string, array{row: array<string, string>, station_codes: list<string>}>
 */
function MRT_csv_line_derived_route_definitions( array $files ): array {
	if ( empty( $files['lines.csv'] ) || empty( $files['line_stations.csv'] ) ) {
		return array();
	}
	$names = MRT_csv_station_name_by_code( $files );
	$defs  = array();
	foreach ( (array) $files['lines.csv'] as $line_row ) {
		$line_code = trim( (string) ( $line_row['line_code'] ?? '' ) );
		if ( $line_code === '' ) {
			continue;
		}
		$kind          = sanitize_key( (string) ( $line_row['kind'] ?? '' ) );
		$station_codes = MRT_csv_ordered_line_station_codes( $files, $line_code );
		foreach ( MRT_line_derived_route_defs_for_codes( $line_code, $kind, $station_codes, $names ) as $code => $def ) {
			$defs[ $code ] = $def;
		}
	}
	return $defs;
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @return array<string, array<string, string>>
 */
function MRT_csv_line_derived_route_rows( array $files ): array {
	$rows = array();
	foreach ( MRT_csv_line_derived_route_definitions( $files ) as $code => $def ) {
		$rows[ $code ] = $def['row'];
	}
	return $rows;
}

function MRT_csv_package_has_line_routes( array $files ): bool {
	return ! empty( $files['lines.csv'] ) && ! empty( $files['line_stations.csv'] );
}
