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

require_once MRT_PATH . 'inc/domain/route/route-meta.php';

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
		$end_stations = MRT_get_route_end_stations( $route_id );
		$start_id     = (int) ( $end_stations['start'] ?? 0 );
		$end_id       = (int) ( $end_stations['end'] ?? 0 );
		if ( $start_id > 0 && $station_id === $start_id ) {
			return MRT_station_is_faringe_side_corridor_termius( $start_id, $end_id );
		}
		if ( $end_id > 0 && $station_id === $end_id ) {
			return true;
		}
	}
	return (bool) apply_filters( 'mrt_station_is_inbound_grid_origin', false, $station_id, $route_id );
}

/**
 * Faringe-side route start (higher display order) opens the inbound timetable grid.
 */
function MRT_station_is_faringe_side_corridor_termius( int $start_id, int $end_id ): bool {
	$start_order = (int) get_post_meta( $start_id, 'mrt_display_order', true );
	$end_order   = (int) get_post_meta( $end_id, 'mrt_display_order', true );
	if ( $start_order <= 0 || $end_order <= 0 ) {
		return false;
	}
	return $start_order > $end_order;
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
	$group_route_id = (int) ( $rail_group['route_id'] ?? 0 );
	return $group_route_id > 0 ? $group_route_id : 0;
}
