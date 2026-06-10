<?php
/**
 * Station meta used by timetable grid and train-change display.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key: station is the inbound origin when listed first on a timetable grid.
 */
function MRT_station_inbound_grid_origin_meta_key(): string {
	return 'mrt_station_inbound_grid_origin';
}

/**
 * Whether a station starts an inbound timetable grid (route terminus or explicit meta).
 */
function MRT_station_is_inbound_grid_origin( int $station_id, int $route_id = 0 ): bool {
	if ( $station_id <= 0 ) {
		return false;
	}
	if ( get_post_meta( $station_id, MRT_station_inbound_grid_origin_meta_key(), true ) === '1' ) {
		return true;
	}
	if ( $route_id > 0 ) {
		$end = (int) get_post_meta( $route_id, 'mrt_route_end_station', true );
		if ( $end > 0 && $station_id === $end ) {
			return true;
		}
	}
	return (bool) apply_filters( 'mrt_station_is_inbound_grid_origin', false, $station_id, $route_id );
}

/**
 * Resolve route ID from the first service in a timetable group.
 *
 * @param array<string, mixed> $rail_group
 */
function MRT_rail_group_route_id( array $rail_group ): int {
	foreach ( (array) ( $rail_group['services'] ?? array() ) as $service_data ) {
		$service    = $service_data['service'] ?? null;
		$service_id = 0;
		if ( $service instanceof WP_Post ) {
			$service_id = (int) $service->ID;
		} elseif ( is_object( $service ) && isset( $service->ID ) ) {
			$service_id = (int) $service->ID;
		}
		if ( $service_id <= 0 ) {
			continue;
		}
		$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
		if ( $route_id > 0 ) {
			return $route_id;
		}
	}
	return 0;
}
