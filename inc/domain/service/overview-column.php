<?php
/**
 * Timetable overview: standalone bus column (no train transfer).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid/grid-branch.php';

function MRT_service_has_overview_column( int $service_id ): bool {
	return (int) get_post_meta( $service_id, 'mrt_service_overview_column', true ) === 1;
}

function MRT_service_overview_pass_from_station_id( int $service_id ): int {
	return (int) get_post_meta( $service_id, 'mrt_service_overview_pass_from_station_id', true );
}

/**
 * @param array<string, mixed> $row services.csv row.
 */
function MRT_csv_apply_service_overview_column_from_row( int $service_id, array $row, array $station_maps ): void {
	$enabled = ! empty( $row['overview_column'] ) && (string) $row['overview_column'] !== '0';
	if ( $enabled ) {
		update_post_meta( $service_id, 'mrt_service_overview_column', 1 );
	} else {
		delete_post_meta( $service_id, 'mrt_service_overview_column' );
	}

	$pass_code = trim( (string) ( $row['overview_pass_from_station'] ?? '' ) );
	if ( $pass_code !== '' && isset( $station_maps['station'][ $pass_code ] ) ) {
		update_post_meta(
			$service_id,
			'mrt_service_overview_pass_from_station_id',
			(int) $station_maps['station'][ $pass_code ]
		);
	} else {
		delete_post_meta( $service_id, 'mrt_service_overview_pass_from_station_id' );
	}
}

/**
 * @param array<int, array<string, mixed>> $grouped_services
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_standalone_bus_entries_for_rail_group(
	array $grouped_services,
	array $rail_group
): array {
	$rail_direction = (string) ( $rail_group['direction'] ?? '' );
	$entries        = array();

	foreach ( $grouped_services as $group ) {
		if ( ! MRT_timetable_group_is_branch_shuttle( $group ) ) {
			continue;
		}
		if ( (string) ( $group['direction'] ?? '' ) !== $rail_direction ) {
			continue;
		}
		foreach ( (array) ( $group['services'] ?? array() ) as $service_data ) {
			$service = $service_data['service'] ?? null;
			if ( ! $service instanceof WP_Post || ! MRT_service_has_overview_column( (int) $service->ID ) ) {
				continue;
			}
			$entries[] = $service_data;
		}
	}

	return $entries;
}

/**
 * @param array<string, mixed> $group
 */
function MRT_timetable_group_is_standalone_overview_column_shuttle( array $group ): bool {
	if ( ! MRT_timetable_group_is_branch_shuttle( $group ) ) {
		return false;
	}
	foreach ( (array) ( $group['services'] ?? array() ) as $service_data ) {
		$service = $service_data['service'] ?? null;
		if ( $service instanceof WP_Post && MRT_service_has_overview_column( (int) $service->ID ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<int, WP_Post> $station_posts
 */
function MRT_timetable_standalone_bus_boarding_departure(
	array $service_data,
	array $station_posts
): string {
	$stops = $service_data['stop_times'] ?? array();
	foreach ( $station_posts as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		$stop = $stops[ (int) $station->ID ] ?? null;
		if ( ! is_array( $stop ) ) {
			continue;
		}
		$dep = MRT_stop_effective_departure( $stop );
		if ( $dep !== '' ) {
			return $dep;
		}
	}
	foreach ( $stops as $stop ) {
		if ( ! is_array( $stop ) ) {
			continue;
		}
		$dep = MRT_stop_effective_departure( $stop );
		if ( $dep !== '' ) {
			return $dep;
		}
	}
	return '';
}
