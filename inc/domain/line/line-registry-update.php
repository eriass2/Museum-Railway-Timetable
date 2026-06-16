<?php
/**
 * Admin updates to line registry (station order + derived route sync).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/line/line-csv.php';
require_once MRT_PATH . 'inc/domain/line/line-route-definitions.php';
require_once MRT_PATH . 'inc/domain/line/line-route-resolve.php';
require_once MRT_PATH . 'inc/domain/line/line-rest-format.php';
require_once MRT_PATH . 'inc/domain/route/route-meta.php';
require_once MRT_PATH . 'inc/import/csv/entity-upsert.php';
require_once MRT_PATH . 'inc/import/csv/codes-store.php';
require_once MRT_PATH . 'inc/import/csv/schema.php';

/**
 * @param array<int, mixed> $station_ids
 * @return list<string>|WP_Error
 */
function MRT_station_codes_from_ids( array $station_ids ) {
	$codes = array();
	$seen  = array();
	foreach ( array_values( array_filter( array_map( 'intval', $station_ids ) ) ) as $station_id ) {
		if ( $station_id <= 0 ) {
			continue;
		}
		$post = get_post( $station_id );
		$post_type = is_object( $post ) ? (string) ( $post->post_type ?? '' ) : '';
		if ( $post_type !== MRT_POST_TYPE_STATION ) {
			return new WP_Error(
				'mrt_invalid_station',
				__( 'One or more stations could not be found.', 'museum-railway-timetable' ),
				array( 'status' => 400 )
			);
		}
		$code = MRT_station_code_for_post_id( $station_id );
		if ( $code === '' ) {
			return new WP_Error(
				'mrt_station_missing_code',
				__( 'Each station on a line must have a station code.', 'museum-railway-timetable' ),
				array( 'status' => 400 )
			);
		}
		if ( isset( $seen[ $code ] ) ) {
			continue;
		}
		$seen[ $code ]  = true;
		$codes[] = $code;
	}
	if ( count( $codes ) < 2 ) {
		return new WP_Error(
			'mrt_line_min_stations',
			__( 'A line must include at least two stations.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	return $codes;
}

/**
 * @param list<string> $station_codes
 * @return array<string, string>
 */
function MRT_line_station_names_by_code( array $station_codes ): array {
	$names = array();
	foreach ( $station_codes as $code ) {
		$code = trim( (string) $code );
		if ( $code === '' ) {
			continue;
		}
		$station_id     = MRT_station_post_id_from_station_code( $code );
		$names[ $code ] = $station_id > 0 ? (string) get_the_title( $station_id ) : $code;
	}
	return $names;
}

/**
 * @param array{row: array<string, string>, station_codes: list<string>} $def
 */
function MRT_apply_line_derived_route_def( array $def ): int {
	$row        = $def['row'];
	$route_code = trim( (string) ( $row['route_code'] ?? '' ) );
	if ( $route_code === '' ) {
		return 0;
	}
	$station_ids = array();
	foreach ( $def['station_codes'] as $station_code ) {
		$station_code = trim( (string) $station_code );
		if ( $station_code === '' ) {
			continue;
		}
		$station_id = MRT_station_post_id_from_station_code( $station_code );
		if ( $station_id > 0 ) {
			$station_ids[] = $station_id;
		}
	}
	if ( $station_ids === array() ) {
		return 0;
	}
	$title    = (string) ( $row['title'] ?? $route_code );
	$meta_key = MRT_csv_code_meta_keys()['routes'];
	$route_id = MRT_route_post_id_from_code( $route_code );
	if ( $route_id <= 0 ) {
		$route_id = MRT_csv_upsert_post_by_code( $route_code, MRT_POST_TYPE_ROUTE, $meta_key, $title );
	}
	if ( $route_id <= 0 ) {
		return 0;
	}
	wp_update_post(
		array(
			'ID'         => $route_id,
			'post_title' => $title,
		)
	);
	update_post_meta( $route_id, 'mrt_route_stations', $station_ids );
	MRT_update_route_terminus_station_meta( $route_id, (int) $station_ids[0], 'mrt_route_start_station' );
	MRT_update_route_terminus_station_meta(
		$route_id,
		(int) $station_ids[ count( $station_ids ) - 1 ],
		'mrt_route_end_station'
	);
	MRT_csv_apply_route_branch_from_row( $route_id, $row );
	MRT_csv_save_post_code( $route_id, $meta_key, $route_code );
	return $route_id;
}

/**
 * @return int|WP_Error Number of routes synced.
 */
function MRT_sync_line_derived_routes( string $line_code ) {
	$line_code = trim( $line_code );
	$entry     = MRT_line_registry_entry( $line_code );
	if ( $entry === array() ) {
		return new WP_Error(
			'mrt_unknown_line',
			__( 'Line not found.', 'museum-railway-timetable' ),
			array( 'status' => 404 )
		);
	}
	$station_codes = $entry['station_codes'] ?? array();
	if ( ! is_array( $station_codes ) || $station_codes === array() ) {
		return new WP_Error(
			'mrt_line_no_stations',
			__( 'Line has no stations.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	$kind  = sanitize_key( (string) ( $entry['kind'] ?? '' ) );
	$names = MRT_line_station_names_by_code( $station_codes );
	$count = 0;
	foreach ( MRT_line_derived_route_defs_for_codes( $line_code, $kind, $station_codes, $names ) as $def ) {
		if ( MRT_apply_line_derived_route_def( $def ) > 0 ) {
			++$count;
		}
	}
	return $count;
}

/**
 * @param array<int, mixed> $station_ids
 * @return true|WP_Error
 */
function MRT_update_line_registry_stations( string $line_code, array $station_ids ) {
	$line_code = trim( $line_code );
	$codes     = MRT_station_codes_from_ids( $station_ids );
	if ( is_wp_error( $codes ) ) {
		return $codes;
	}
	$registry = MRT_get_line_registry();
	if ( ! isset( $registry[ $line_code ] ) || ! is_array( $registry[ $line_code ] ) ) {
		return new WP_Error(
			'mrt_unknown_line',
			__( 'Line not found.', 'museum-railway-timetable' ),
			array( 'status' => 404 )
		);
	}
	$kind = sanitize_key( (string) ( $registry[ $line_code ]['kind'] ?? '' ) );
	if ( $kind === 'branch' && count( $codes ) !== 2 ) {
		return new WP_Error(
			'mrt_line_branch_station_count',
			__( 'Transfer branches must have exactly two stations.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	if ( $kind === 'pattern' && count( $codes ) !== 2 ) {
		return new WP_Error(
			'mrt_line_pattern_station_count',
			__( 'Direct patterns must have exactly two stations.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	$names = MRT_line_station_names_by_code( $codes );
	$defs  = MRT_line_derived_route_defs_for_codes( $line_code, $kind, $codes, $names );
	if ( $defs === array() ) {
		return new WP_Error(
			'mrt_line_invalid_structure',
			__( 'Could not derive routes for this line structure.', 'museum-railway-timetable' ),
			array( 'status' => 400 )
		);
	}
	$registry[ $line_code ]['station_codes'] = $codes;
	MRT_set_line_registry( $registry );
	$synced = MRT_sync_line_derived_routes( $line_code );
	if ( is_wp_error( $synced ) ) {
		return $synced;
	}
	return true;
}
