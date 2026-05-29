<?php
/**
 * Link branch shuttle groups to matching main-line groups (separate lists).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid-branch.php';

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

	foreach ( $branch_keys as $branch_key ) {
		$branch   = $grouped_services[ $branch_key ];
		$main_key = MRT_timetable_find_main_group_for_branch( $grouped_services, $branch, $branch_key );
		if ( $main_key === null ) {
			continue;
		}

		$rail_direction   = (string) ( $grouped_services[ $main_key ]['direction'] ?? '' );
		$branch_direction = (string) ( $branch['direction'] ?? '' );
		if ( $rail_direction !== '' && $branch_direction !== '' && $rail_direction !== $branch_direction ) {
			continue;
		}

		$grouped_services[ $main_key ]['paired_branch'] = $branch;
		$grouped_services[ $branch_key ]['paired_rail'] = $grouped_services[ $main_key ];
	}

	return array_values( $grouped_services );
}

/**
 * @param array<string, array<string, mixed>> $grouped_services
 */
function MRT_timetable_find_main_group_for_branch( array $grouped_services, array $branch, string $branch_key ): ?string {
	$branch_station_ids = array_map( 'intval', (array) ( $branch['stations'] ?? array() ) );
	$best_key           = null;
	$best_count         = 0;

	foreach ( $grouped_services as $key => $group ) {
		if ( $key === $branch_key || MRT_timetable_group_is_branch_shuttle( $group ) ) {
			continue;
		}

		$main_stations = array_map( 'intval', (array) ( $group['stations'] ?? array() ) );
		if ( count( $main_stations ) < 3 ) {
			continue;
		}

		$overlap = array_intersect( $branch_station_ids, $main_stations );
		if ( $overlap === array() ) {
			continue;
		}

		$branch_start = $branch_station_ids[0] ?? 0;
		$branch_end   = (int) ( $branch_station_ids[ count( $branch_station_ids ) - 1 ] ?? 0 );
		if ( ! in_array( $branch_start, $main_stations, true ) && ! in_array( $branch_end, $main_stations, true ) ) {
			continue;
		}

		if ( count( $main_stations ) > $best_count ) {
			$best_count = count( $main_stations );
			$best_key   = (string) $key;
		}
	}

	return $best_key;
}
