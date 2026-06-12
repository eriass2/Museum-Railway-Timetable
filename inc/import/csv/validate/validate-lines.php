<?php
/**
 * Line CSV validation (Fas 1 — lines.csv + line_stations.csv).
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
 * @param array<string, mixed> $resolved
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_resolve_lines( array $files, array &$resolved, array &$errors ): void {
	foreach ( (array) ( $files['lines.csv'] ?? array() ) as $row ) {
		$code = trim( (string) ( $row['line_code'] ?? '' ) );
		if ( $code === '' ) {
			MRT_csv_add_row_error( $row, 'line_code is required.', $errors );
			continue;
		}
		if ( trim( (string) ( $row['title'] ?? '' ) ) === '' ) {
			MRT_csv_add_row_error( $row, 'Line title is required.', $errors );
			continue;
		}
		$kind = trim( (string) ( $row['kind'] ?? '' ) );
		if ( $kind === '' || ! MRT_csv_line_kind_is_valid( $kind ) ) {
			MRT_csv_add_row_error( $row, 'Invalid line kind (expected main, branch, or pattern).', $errors );
			continue;
		}
		$corridor = trim( (string) ( $row['overview_corridor_after_station'] ?? '' ) );
		if ( $kind === 'pattern' && $corridor === '' ) {
			MRT_csv_add_row_error( $row, 'Pattern lines require overview_corridor_after_station.', $errors );
		}
		if ( $kind !== 'pattern' && $corridor !== '' ) {
			MRT_csv_add_row_error( $row, 'overview_corridor_after_station applies only to pattern lines.', $errors );
		}
		$resolved['lines'][ $code ] = $row;
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, bool> $lines
 * @param array<string, bool> $stations
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_line_station_rows( array $files, array $lines, array $stations, array &$errors ): void {
	$seen = array();
	foreach ( (array) ( $files['line_stations.csv'] ?? array() ) as $row ) {
		$line_code = trim( (string) ( $row['line_code'] ?? '' ) );
		$station   = trim( (string) ( $row['station_code'] ?? '' ) );
		if ( $line_code !== '' && ! isset( $lines[ $line_code ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown line_code \"{$line_code}\".", $errors );
		}
		if ( $station !== '' && ! isset( $stations[ $station ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown station_code \"{$station}\".", $errors );
		}
		if ( $line_code !== '' ) {
			$seq = (int) ( $row['sequence'] ?? 0 );
			$key = $line_code . '#' . $seq;
			if ( isset( $seen[ $key ] ) ) {
				MRT_csv_add_row_error( $row, 'Duplicate sequence for line.', $errors );
			}
			$seen[ $key ] = true;
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, bool> $lines
 * @param array<string, string> $routes_branch branch_code per route_code
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_service_line_codes(
	array $files,
	array $lines,
	array $routes_branch,
	array &$errors
): void {
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		$explicit = trim( (string) ( $row['line_code'] ?? '' ) );
		$resolved = MRT_csv_resolve_service_line_code( $row, $routes_branch );
		if ( $explicit !== '' && $explicit !== $resolved && $resolved !== '' ) {
			MRT_csv_add_row_error( $row, "line_code \"{$explicit}\" does not match route mapping.", $errors );
		}
		$line_code = $explicit !== '' ? $explicit : $resolved;
		if ( $line_code === '' ) {
			continue;
		}
		if ( ! isset( $lines[ $line_code ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown line_code \"{$line_code}\".", $errors );
		}
	}
}

/**
 * @param array<string, bool> $lines
 * @param array<string, bool> $stations
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_branch_junction_rows(
	array $files,
	array $lines,
	array $stations,
	array &$errors
): void {
	$main_stations = MRT_csv_ordered_line_station_codes( $files, 'main' );
	foreach ( (array) ( $files['branch_junctions.csv'] ?? array() ) as $row ) {
		$line_code = trim( (string) ( $row['line_code'] ?? '' ) );
		$junction  = trim( (string) ( $row['junction_station_code'] ?? '' ) );
		if ( $line_code !== '' && ! isset( $lines[ $line_code ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown line_code \"{$line_code}\".", $errors );
		}
		if ( $junction !== '' && ! isset( $stations[ $junction ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown junction_station_code \"{$junction}\".", $errors );
		}
		if ( $line_code === '' || $junction === '' ) {
			continue;
		}
		$line_kind = trim( (string) ( $lines[ $line_code ]['kind'] ?? '' ) );
		if ( $line_kind !== '' && $line_kind !== 'branch' ) {
			MRT_csv_add_row_error( $row, 'branch_junctions applies only to branch lines.', $errors );
		}
		$branch_stations = MRT_csv_ordered_line_station_codes( $files, $line_code );
		if ( $branch_stations !== array() && ! in_array( $junction, $branch_stations, true ) ) {
			MRT_csv_add_row_error( $row, 'junction_station_code must appear on the branch line.', $errors );
		}
		if ( $main_stations !== array() && ! in_array( $junction, $main_stations, true ) ) {
			MRT_csv_add_row_error( $row, 'junction_station_code must appear on main line.', $errors );
		}
	}
}

/**
 * Main corridor line must list the same stations as faringe-uppsala-ostra route.
 *
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_pattern_line_corridor_stations(
	array $files,
	array $lines,
	array $stations,
	array &$errors
): void {
	$main_stations = MRT_csv_ordered_line_station_codes( $files, 'main' );
	foreach ( (array) ( $files['lines.csv'] ?? array() ) as $row ) {
		$code = trim( (string) ( $row['line_code'] ?? '' ) );
		if ( $code === '' || ( $row['kind'] ?? '' ) !== 'pattern' ) {
			continue;
		}
		$corridor = trim( (string) ( $row['overview_corridor_after_station'] ?? '' ) );
		if ( $corridor === '' ) {
			continue;
		}
		if ( ! isset( $stations[ $corridor ] ) ) {
			MRT_csv_add_row_error( $row, "Unknown overview_corridor_after_station \"{$corridor}\".", $errors );
			continue;
		}
		if ( $main_stations !== array() && ! in_array( $corridor, $main_stations, true ) ) {
			MRT_csv_add_row_error( $row, 'overview_corridor_after_station must appear on main line.', $errors );
		}
		$line_stations = MRT_csv_ordered_line_station_codes( $files, $code );
		$route_rows    = MRT_csv_ordered_route_station_codes( $files, $code );
		if ( $route_rows !== array() && $line_stations !== array() && $line_stations !== $route_rows ) {
			MRT_csv_add_row_error( $row, 'pattern line_stations must match route_stations order.', $errors );
		}
	}
}

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<int, array{file: string, line: int, message: string}> $errors
 */
function MRT_csv_validate_main_line_station_order( array $files, array &$errors ): void {
	if ( empty( $files['lines.csv'] ) || empty( $files['line_stations.csv'] ) ) {
		return;
	}
	$has_main = false;
	foreach ( (array) $files['lines.csv'] as $row ) {
		if ( ( $row['line_code'] ?? '' ) === 'main' ) {
			$has_main = true;
			break;
		}
	}
	if ( ! $has_main ) {
		return;
	}
	$line_stations = MRT_csv_ordered_line_station_codes( $files, 'main' );
	$route_rows    = MRT_csv_ordered_route_station_codes( $files, 'faringe-uppsala-ostra' );
	if ( $route_rows === array() || $line_stations === array() ) {
		return;
	}
	if ( $line_stations !== $route_rows ) {
		$errors[] = MRT_csv_error(
			'line_stations.csv',
			0,
			'main line_stations must match faringe-uppsala-ostra route_stations order.'
		);
	}
}

/**
 * @return list<string>
 */
function MRT_csv_ordered_line_station_codes( array $files, string $line_code ): array {
	$rows = array();
	foreach ( (array) ( $files['line_stations.csv'] ?? array() ) as $row ) {
		if ( ( $row['line_code'] ?? '' ) !== $line_code ) {
			continue;
		}
		$rows[] = $row;
	}
	usort(
		$rows,
		static fn ( array $a, array $b ): int => (int) ( $a['sequence'] ?? 0 ) <=> (int) ( $b['sequence'] ?? 0 )
	);
	$codes = array();
	foreach ( $rows as $row ) {
		$code = trim( (string) ( $row['station_code'] ?? '' ) );
		if ( $code !== '' ) {
			$codes[] = $code;
		}
	}
	return $codes;
}

/**
 * @param array<string, mixed> $resolved
 * @return array<string, string>
 */
function MRT_csv_routes_branch_codes( array $resolved ): array {
	$map = array();
	foreach ( (array) ( $resolved['routes'] ?? array() ) as $code => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$map[ (string) $code ] = trim( (string) ( $row['branch_code'] ?? '' ) );
	}
	return $map;
}

/**
 * @return list<string>
 */
function MRT_csv_ordered_route_station_codes( array $files, string $route_code ): array {
	$rows = array();
	foreach ( (array) ( $files['route_stations.csv'] ?? array() ) as $row ) {
		if ( ( $row['route_code'] ?? '' ) !== $route_code ) {
			continue;
		}
		$rows[] = $row;
	}
	usort(
		$rows,
		static fn ( array $a, array $b ): int => (int) ( $a['sequence'] ?? 0 ) <=> (int) ( $b['sequence'] ?? 0 )
	);
	$codes = array();
	foreach ( $rows as $row ) {
		$code = trim( (string) ( $row['station_code'] ?? '' ) );
		if ( $code !== '' ) {
			$codes[] = $code;
		}
	}
	return $codes;
}
