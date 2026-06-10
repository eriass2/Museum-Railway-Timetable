<?php
/**
 * Route domain: direction
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_calculate_direction_from_end_station( $route_id, $end_station_id ) {
	if ( ! $route_id || ! $end_station_id ) {
		return '';
	}

	$end_stations   = MRT_get_route_end_stations( $route_id );
	$route_stations = MRT_get_route_stations( $route_id );
	if ( empty( $route_stations ) ) {
		return '';
	}
	$explicit_direction = MRT_route_direction_from_configured_endpoints( $end_stations, $end_station_id );
	if ( $explicit_direction !== '' ) {
		return $explicit_direction;
	}
	return MRT_route_direction_from_station_order( $route_stations, $end_stations, $end_station_id );
}

function MRT_route_direction_from_configured_endpoints( array $end_stations, $end_station_id ): string {
	if ( $end_stations['end'] == $end_station_id ) {
		return 'dit';
	}
	if ( $end_stations['start'] == $end_station_id ) {
		return 'från';
	}
	return '';
}

function MRT_route_direction_from_station_order( array $route_stations, array $end_stations, $end_station_id ): string {
	$end_station_index       = array_search( $end_station_id, $route_stations );
	$start_station_index     = array_search( $end_stations['start'], $route_stations );
	$route_end_station_index = array_search( $end_stations['end'], $route_stations );
	if ( $end_station_index === false ) {
		return '';
	}
	if ( $route_end_station_index !== false && $end_station_index > $route_end_station_index ) {
		return 'dit';
	}
	if ( $start_station_index !== false && $end_station_index < $start_station_index ) {
		return 'från';
	}
	$middle = count( $route_stations ) / 2;
	return $end_station_index >= $middle ? 'dit' : 'från';
}

function MRT_route_station_index( array $route_stations, $station_id ): ?int {
	$station_id = (int) $station_id;
	foreach ( $route_stations as $index => $id ) {
		if ( (int) $id === $station_id ) {
			return (int) $index;
		}
	}
	return null;
}

function MRT_route_leg_travels_towards_station( $route_id, $from_station_id, $to_station_id, $goal_station_id ): bool {
	$from_station_id = (int) $from_station_id;
	$to_station_id   = (int) $to_station_id;
	$goal_station_id = (int) $goal_station_id;
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $goal_station_id <= 0 ) {
		return false;
	}
	if ( $from_station_id === $to_station_id ) {
		return false;
	}
	if ( $to_station_id === $goal_station_id ) {
		return true;
	}
	if ( $from_station_id === $goal_station_id ) {
		return false;
	}
	if ( $route_id <= 0 ) {
		return true;
	}
	$route_stations = MRT_get_route_stations( (int) $route_id );
	if ( $route_stations === array() ) {
		return true;
	}
	$from_idx = MRT_route_station_index( $route_stations, $from_station_id );
	$to_idx   = MRT_route_station_index( $route_stations, $to_station_id );
	$goal_idx = MRT_route_station_index( $route_stations, $goal_station_id );
	if ( $from_idx === null || $to_idx === null || $goal_idx === null ) {
		return true;
	}
	$to_delta   = $to_idx - $from_idx;
	$goal_delta = $goal_idx - $from_idx;
	if ( $to_delta === 0 ) {
		return false;
	}
	return ( $to_delta > 0 && $goal_delta > 0 ) || ( $to_delta < 0 && $goal_delta < 0 );
}

function MRT_journey_transfer_overshoots_destination(
	int $route_id,
	int $from_station_id,
	int $transfer_station_id,
	int $goal_station_id
): bool {
	if ( $route_id <= 0 || $from_station_id <= 0 || $transfer_station_id <= 0 || $goal_station_id <= 0 ) {
		return false;
	}
	if ( $transfer_station_id === $goal_station_id || $from_station_id === $goal_station_id ) {
		return false;
	}
	$route_stations = MRT_get_route_stations( $route_id );
	if ( $route_stations === array() ) {
		return false;
	}
	$from_idx = MRT_route_station_index( $route_stations, $from_station_id );
	$xfer_idx = MRT_route_station_index( $route_stations, $transfer_station_id );
	$goal_idx = MRT_route_station_index( $route_stations, $goal_station_id );
	if ( $from_idx === null || $xfer_idx === null || $goal_idx === null ) {
		return false;
	}
	$min_idx = min( $from_idx, $xfer_idx );
	$max_idx = max( $from_idx, $xfer_idx );
	return $goal_idx > $min_idx && $goal_idx < $max_idx;
}
