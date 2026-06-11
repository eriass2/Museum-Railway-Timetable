<?php
/**
 * Resolve directed routes from line + travel direction (LINES_REFACTOR Fas 4).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-csv.php';
require_once MRT_PATH . 'inc/import/csv/validate/validate-lines.php';
require_once MRT_PATH . 'inc/import/csv/import/import-errors.php';

/**
 * @return list<string>
 */
function MRT_line_station_codes( string $line_code, ?array $files = null ): array {
	if ( $files !== null ) {
		$from_csv = MRT_csv_ordered_line_station_codes( $files, $line_code );
		if ( $from_csv !== array() ) {
			return $from_csv;
		}
	}
	$entry = MRT_line_registry_entry( $line_code );
	$codes = $entry['station_codes'] ?? array();
	return is_array( $codes ) ? array_values( $codes ) : array();
}

/**
 * Which line terminus the service travels toward (D8 / Fas 4).
 *
 * @param list<string> $line_stations
 */
function MRT_csv_infer_toward_station_code( array $row, array $line_stations ): string {
	$explicit = trim( (string) ( $row['toward_station_code'] ?? '' ) );
	if ( $explicit !== '' ) {
		return $explicit;
	}
	$end   = trim( (string) ( $row['end_station_code'] ?? '' ) );
	$first = $line_stations[0] ?? '';
	$last  = $line_stations[ count( $line_stations ) - 1 ] ?? '';
	if ( $end !== '' && ( $end === $first || $end === $last ) ) {
		return $end;
	}
	$code = (string) ( $row['service_code'] ?? '' );
	if ( str_ends_with( $code, '-out' ) && $first !== '' ) {
		return $first;
	}
	if ( str_ends_with( $code, '-in' ) && $last !== '' ) {
		return $last;
	}
	return $end;
}

/**
 * @param list<string> $line_stations
 */
function MRT_line_route_code_for_toward( string $line_code, string $toward_station_code, array $line_stations ): string {
	if ( $line_code === 'linnes-uppsala' ) {
		return 'linnes-uppsala';
	}
	if ( $line_stations === array() || $toward_station_code === '' ) {
		return '';
	}
	$first = $line_stations[0];
	$last  = $line_stations[ count( $line_stations ) - 1 ];
	if ( $line_code === 'main' ) {
		if ( $toward_station_code === $last ) {
			return 'faringe-uppsala-ostra';
		}
		if ( $toward_station_code === $first ) {
			return 'uppsala-faringe';
		}
		return $toward_station_code === $first ? 'uppsala-faringe' : 'faringe-uppsala-ostra';
	}
	if ( count( $line_stations ) === 2 ) {
		if ( $toward_station_code === $last ) {
			return $first . '-' . $last;
		}
		if ( $toward_station_code === $first ) {
			return $last . '-' . $first;
		}
	}
	return '';
}

function MRT_line_toward_station_code_from_route( string $line_code, string $route_code ): string {
	$line_stations = MRT_line_station_codes( $line_code );
	if ( $line_stations === array() || $route_code === '' ) {
		return '';
	}
	$first = $line_stations[0];
	$last  = $line_stations[ count( $line_stations ) - 1 ];
	if ( $line_code === 'main' ) {
		if ( $route_code === 'faringe-uppsala-ostra' ) {
			return $last;
		}
		if ( $route_code === 'uppsala-faringe' ) {
			return $first;
		}
	}
	if ( $route_code === $first . '-' . $last ) {
		return $last;
	}
	if ( $route_code === $last . '-' . $first ) {
		return $first;
	}
	if ( $route_code === 'linnes-uppsala' ) {
		return $last;
	}
	return '';
}

/**
 * @param array<string, string> $routes_by_code
 */
function MRT_csv_resolve_service_route_code( array $row, ?array $files, array $routes_by_code ): string {
	$explicit = trim( (string) ( $row['route_code'] ?? '' ) );
	if ( $explicit !== '' ) {
		return $explicit;
	}
	$line_code = MRT_csv_resolve_service_line_code( $row, $routes_by_code );
	if ( $line_code === '' ) {
		return '';
	}
	$line_stations = MRT_line_station_codes( $line_code, $files );
	$toward        = MRT_csv_infer_toward_station_code( $row, $line_stations );
	return MRT_line_route_code_for_toward( $line_code, $toward, $line_stations );
}

function MRT_station_code_for_post_id( int $station_id ): string {
	if ( $station_id <= 0 ) {
		return '';
	}
	return trim( (string) get_post_meta( $station_id, 'mrt_station_code', true ) );
}

function MRT_route_post_id_from_code( string $route_code ): int {
	if ( $route_code === '' ) {
		return 0;
	}
	$posts = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_ROUTE,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'mrt_route_code',
			'meta_value'     => $route_code,
		)
	);
	return isset( $posts[0] ) ? (int) $posts[0] : 0;
}

function MRT_line_resolve_route_id( string $line_code, string $toward_station_code ): int {
	$line_stations = MRT_line_station_codes( $line_code );
	$route_code    = MRT_line_route_code_for_toward( $line_code, $toward_station_code, $line_stations );
	return MRT_route_post_id_from_code( $route_code );
}

/**
 * @param array<string, bool> $routes
 * @param array<string, string> $routes_branch
 */
function MRT_csv_validate_service_resolved_routes(
	array $files,
	array $routes,
	array $routes_branch,
	array &$errors
): void {
	foreach ( (array) ( $files['services.csv'] ?? array() ) as $row ) {
		if ( trim( (string) ( $row['route_code'] ?? '' ) ) !== '' ) {
			continue;
		}
		$resolved = MRT_csv_resolve_service_route_code( $row, $files, $routes_branch );
		if ( $resolved === '' ) {
			MRT_csv_add_row_error( $row, 'Could not resolve route from line_code and end_station_code.', $errors );
			continue;
		}
		if ( ! isset( $routes[ $resolved ] ) ) {
			MRT_csv_add_row_error( $row, "Resolved route_code \"{$resolved}\" is unknown.", $errors );
		}
	}
}
