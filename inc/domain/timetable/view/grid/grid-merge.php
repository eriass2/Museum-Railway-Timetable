<?php
/**
 * Link branch shuttle groups to matching main-line groups (separate lists).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/grid-branch.php';
require_once __DIR__ . '/grid-connections.php';
require_once MRT_PATH . 'inc/domain/line/line-csv.php';
require_once MRT_PATH . 'inc/domain/route/route-meta.php';

/**
 * @param array<string, array<string, mixed>> $grouped_services
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_groups_link_branch_pairs( array $grouped_services ): array {
	$branch_keys = array();

	foreach ( $grouped_services as $key => $group ) {
		if ( MRT_timetable_group_is_branch_shuttle( $group ) ) {
			$branch_keys[] = $key;
		}
	}

	require_once MRT_PATH . 'inc/domain/service/overview-column.php';

	foreach ( $branch_keys as $branch_key ) {
		$branch   = $grouped_services[ $branch_key ];
		if ( MRT_timetable_group_is_standalone_overview_column_shuttle( $branch ) ) {
			continue;
		}
		$main_key = MRT_timetable_find_main_group_for_branch( $grouped_services, $branch, $branch_key );
		if ( $main_key === null ) {
			continue;
		}

		$rail_direction   = (string) ( $grouped_services[ $main_key ]['direction'] ?? '' );
		$branch_direction = (string) ( $branch['direction'] ?? '' );
		if ( $rail_direction !== '' && $branch_direction !== '' && $rail_direction !== $branch_direction ) {
			continue;
		}

		if ( ! isset( $grouped_services[ $main_key ]['paired_branches'] ) ) {
			$grouped_services[ $main_key ]['paired_branches'] = array();
		}
		$grouped_services[ $main_key ]['paired_branches'][] = $branch;
		$grouped_services[ $main_key ]['paired_branch']    = $grouped_services[ $main_key ]['paired_branches'][0];
		$grouped_services[ $branch_key ]['paired_rail']     = $grouped_services[ $main_key ];
	}

	return array_values( $grouped_services );
}

/**
 * Branch shuttle groups paired with a main-line group (supports multiple branches).
 *
 * @param array<string, mixed> $group Rail group from grouped services.
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_rail_paired_branches( array $group ): array {
	if ( ! empty( $group['paired_branches'] ) && is_array( $group['paired_branches'] ) ) {
		return array_values( $group['paired_branches'] );
	}
	if ( ! empty( $group['paired_branch'] ) && is_array( $group['paired_branch'] ) ) {
		return array( $group['paired_branch'] );
	}
	return array();
}

function MRT_timetable_group_branch_code( array $group ): string {
	$code = trim( (string) ( $group['branch_code'] ?? '' ) );
	if ( $code !== '' ) {
		return $code;
	}
	$route_id = (int) ( $group['route_id'] ?? 0 );
	return $route_id > 0 ? MRT_route_branch_code( $route_id ) : '';
}

function MRT_timetable_group_is_main_corridor( array $group ): bool {
	if ( MRT_timetable_group_is_branch_shuttle( $group ) ) {
		return false;
	}
	$code = MRT_timetable_group_branch_code( $group );
	return $code === '' || $code === 'main';
}

/**
 * @param array<string, array<string, mixed>> $grouped_services
 */
function MRT_timetable_find_main_group_for_branch( array $grouped_services, array $branch, string $branch_key ): ?string {
	$branch_station_ids = array_map( 'intval', (array) ( $branch['stations'] ?? array() ) );
	$line_code          = MRT_timetable_group_line_code( $branch );
	$junction_id        = MRT_line_junction_station_id_for_group( $branch, $line_code );
	if ( $junction_id > 0 ) {
		return MRT_timetable_find_main_group_by_line_junction(
			$grouped_services,
			$branch_key,
			$branch_station_ids,
			$junction_id
		);
	}
	$best_key   = null;
	$best_score = -1;

	foreach ( $grouped_services as $key => $group ) {
		if ( $key === $branch_key || ! MRT_timetable_group_is_main_corridor( $group ) ) {
			continue;
		}

		$main_stations = array_map( 'intval', (array) ( $group['stations'] ?? array() ) );
		$connector     = MRT_timetable_branch_junction_connector_direction( $branch_station_ids, $main_stations );
		if ( $connector !== '' && MRT_timetable_rail_grid_direction( $group ) !== $connector ) {
			continue;
		}
		$score = MRT_timetable_branch_main_pair_score( $main_stations, $branch_station_ids );
		if ( $score > $best_score ) {
			$best_score = $score;
			$best_key   = (string) $key;
		}
	}

	return $best_key;
}

/**
 * @param array<int, int> $branch_station_ids
 */
function MRT_timetable_find_main_group_by_line_junction(
	array $grouped_services,
	string $branch_key,
	array $branch_station_ids,
	int $junction_id
): ?string {
	foreach ( $grouped_services as $key => $group ) {
		if ( $key === $branch_key || ! MRT_timetable_group_is_main_corridor( $group ) ) {
			continue;
		}
		$main_stations = array_map( 'intval', (array) ( $group['stations'] ?? array() ) );
		if ( ! in_array( $junction_id, $main_stations, true ) ) {
			continue;
		}
		$connector = MRT_timetable_branch_junction_connector_direction( $branch_station_ids, $main_stations );
		if ( $connector !== '' && MRT_timetable_rail_grid_direction( $group ) !== $connector ) {
			continue;
		}
		return (string) $key;
	}
	return null;
}

/**
 * Prefer the main line whose travel direction matches the branch connector.
 *
 * @param array<int, int> $main_stations
 * @param array<int, int> $branch_station_ids
 */
function MRT_timetable_branch_main_pair_score( array $main_stations, array $branch_station_ids ): int {
	if ( count( $main_stations ) < 3 || count( $branch_station_ids ) < 2 ) {
		return -1;
	}

	$branch_start = $branch_station_ids[0];
	$branch_end   = (int) $branch_station_ids[ count( $branch_station_ids ) - 1 ];
	$start_on     = in_array( $branch_start, $main_stations, true );
	$end_on       = in_array( $branch_end, $main_stations, true );
	if ( ! $start_on && ! $end_on ) {
		return -1;
	}

	$main_len = count( $main_stations );
	if ( $start_on && ! $end_on ) {
		$idx = array_search( $branch_start, $main_stations, true );
		return $idx === false ? -1 : ( ( $main_len - 1 - (int) $idx ) * 1000 ) + $main_len;
	}
	if ( ! $start_on && $end_on ) {
		$idx = array_search( $branch_end, $main_stations, true );
		return $idx === false ? -1 : ( (int) $idx * 1000 ) + $main_len;
	}

	return $main_len;
}

/**
 * Outbound branches depart the junction; inbound branches arrive at it.
 *
 * @param array<int, int> $branch_station_ids
 * @param array<int, int> $main_stations
 */
function MRT_timetable_branch_junction_connector_direction( array $branch_station_ids, array $main_stations ): string {
	if ( count( $branch_station_ids ) < 2 ) {
		return '';
	}
	$branch_start = (int) $branch_station_ids[0];
	$branch_end   = (int) $branch_station_ids[ count( $branch_station_ids ) - 1 ];
	$start_on     = in_array( $branch_start, $main_stations, true );
	$end_on       = in_array( $branch_end, $main_stations, true );
	if ( $start_on && ! $end_on ) {
		return 'outbound';
	}
	if ( ! $start_on && $end_on ) {
		return 'inbound';
	}
	return '';
}
