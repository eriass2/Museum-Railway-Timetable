<?php
/**
 * Derive and persist a service end station from stop times.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/route/route-direction.php';
require_once MRT_PATH . 'inc/domain/service/timetable-trip-create.php';

/**
 * Whether a stop row carries an arrival or departure time.
 *
 * @param array<string, mixed> $stop Stop row from client or editor.
 */
function MRT_stop_row_has_time( array $stop ): bool {
	$arrival   = trim( (string) ( $stop['arrival'] ?? $stop['arrival_time'] ?? '' ) );
	$departure = trim( (string) ( $stop['departure'] ?? $stop['departure_time'] ?? '' ) );
	return $arrival !== '' || $departure !== '';
}

/**
 * Last station along the submitted stop list that has a time.
 *
 * @param array<int, array<string, mixed>> $stops Stop rows in route order.
 */
function MRT_derive_end_station_from_stop_rows( array $stops ): int {
	$last = 0;
	foreach ( $stops as $stop ) {
		if ( ! is_array( $stop ) ) {
			continue;
		}
		$station_id = (int) ( $stop['station_id'] ?? $stop['id'] ?? 0 );
		if ( $station_id <= 0 || ! MRT_stop_row_has_time( $stop ) ) {
			continue;
		}
		$last = $station_id;
	}
	return $last;
}

/**
 * Persist end station, direction and auto title for a service.
 */
function MRT_apply_service_end_station( int $service_id, int $end_station_id ): void {
	$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
	if ( $route_id <= 0 ) {
		return;
	}
	$direction = '';
	if ( $end_station_id > 0 ) {
		update_post_meta( $service_id, 'mrt_service_end_station_id', $end_station_id );
		$direction = MRT_calculate_direction_from_end_station( $route_id, $end_station_id );
		if ( $direction !== '' ) {
			update_post_meta( $service_id, 'mrt_direction', $direction );
		} else {
			delete_post_meta( $service_id, 'mrt_direction' );
		}
	} else {
		delete_post_meta( $service_id, 'mrt_service_end_station_id' );
		delete_post_meta( $service_id, 'mrt_direction' );
	}
	wp_update_post(
		array(
			'ID'         => $service_id,
			'post_title' => MRT_build_service_auto_title( $route_id, $end_station_id, $direction ),
		)
	);
}

/**
 * Derive end station from stop payload and persist on the service.
 *
 * @param array<int, array<string, mixed>> $stops Stop rows in route order.
 */
function MRT_sync_service_end_station_from_stops( int $service_id, array $stops ): void {
	MRT_apply_service_end_station( $service_id, MRT_derive_end_station_from_stop_rows( $stops ) );
}
