<?php
/**
 * Import directed routes derived from lines.csv (LINES Fas C).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-route-definitions.php';

/**
 * @param array<string, array<int, array<string, string>>> $files
 * @param array<string, array<string, int>> $maps
 */
function MRT_csv_import_routes_from_lines( array $files, array &$maps ): int {
	if ( ! MRT_csv_package_has_line_routes( $files ) ) {
		return 0;
	}
	$meta  = MRT_csv_code_meta_keys()['routes'];
	$count = 0;
	foreach ( MRT_csv_line_derived_route_definitions( $files ) as $code => $def ) {
		if ( isset( $maps['route'][ $code ] ) ) {
			continue;
		}
		$row         = $def['row'];
		$title       = (string) ( $row['title'] ?? $code );
		$id          = MRT_csv_upsert_post_by_code( $code, MRT_POST_TYPE_ROUTE, $meta, $title );
		if ( $id <= 0 ) {
			continue;
		}
		$station_ids = MRT_csv_resolve_route_station_ids_from_codes( $def['station_codes'], $maps );
		update_post_meta( $id, 'mrt_route_stations', $station_ids );
		if ( $station_ids !== array() ) {
			update_post_meta( $id, 'mrt_route_start_station', $station_ids[0] );
			update_post_meta( $id, 'mrt_route_end_station', $station_ids[ count( $station_ids ) - 1 ] );
		}
		MRT_csv_apply_route_branch_from_row( (int) $id, $row );
		MRT_csv_save_post_code( (int) $id, $meta, $code );
		$maps['route'][ $code ] = (int) $id;
		++$count;
	}
	return $count;
}

/**
 * @param list<string> $station_codes
 * @param array<string, array<string, int>> $maps
 * @return array<int, int>
 */
function MRT_csv_resolve_route_station_ids_from_codes( array $station_codes, array $maps ): array {
	$ids = array();
	foreach ( $station_codes as $code ) {
		$code = trim( (string) $code );
		if ( $code !== '' && isset( $maps['station'][ $code ] ) ) {
			$ids[] = (int) $maps['station'][ $code ];
		}
	}
	return $ids;
}
