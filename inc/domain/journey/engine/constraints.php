<?php
/**
 * Journey search constraints (hub, direction, overshoot, transfer wait).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maximum transfers allowed in one journey (default 2 = three legs).
 */
function MRT_journey_engine_max_transfers(): int {
	$max = MRT_plugin_max_transfers();
	return max( 0, (int) apply_filters( 'mrt_journey_max_transfers', $max ) );
}

/**
 * Whether a leg endpoint may be used as an intermediate transfer point.
 */
function MRT_journey_constraint_intermediate_hub( int $station_id ): bool {
	return MRT_journey_station_allows_transfer( $station_id );
}

/**
 * Whether a service leg from→to is allowed towards the journey goal.
 */
function MRT_journey_constraint_leg_direction(
	int $service_id,
	int $leg_from,
	int $leg_to,
	int $goal_station_id
): bool {
	$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
	if ( $route_id <= 0 ) {
		return true;
	}
	return MRT_route_leg_travels_towards_station( $route_id, $leg_from, $leg_to, $goal_station_id );
}

/**
 * Whether a leg passes the goal without alighting there (same route).
 */
function MRT_journey_constraint_leg_overshoots(
	int $service_id,
	int $leg_from,
	int $leg_to,
	int $goal_station_id
): bool {
	if ( $leg_to === $goal_station_id ) {
		return false;
	}
	$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
	if ( $route_id <= 0 ) {
		return false;
	}
	return MRT_journey_transfer_overshoots_destination( $route_id, $leg_from, $leg_to, $goal_station_id );
}

/**
 * Whether pickup/dropoff flags allow travelling leg_from→leg_to on a service.
 *
 * @param array<int, array<string, mixed>> $ordered Ordered stop rows
 */
function MRT_journey_constraint_stop_permissions(
	array $ordered,
	int $from_idx,
	int $to_idx
): bool {
	$from = $ordered[ $from_idx ] ?? null;
	$to   = $ordered[ $to_idx ] ?? null;
	if ( ! is_array( $from ) || ! is_array( $to ) ) {
		return false;
	}
	if ( (int) ( $from['pickup_allowed'] ?? 0 ) !== 1 ) {
		return false;
	}
	return (int) ( $to['dropoff_allowed'] ?? 0 ) === 1;
}

/**
 * Whether wait between legs satisfies min/max transfer settings.
 */
function MRT_journey_constraint_transfer_wait( string $arrival_hhmm, string $departure_hhmm ): bool {
	return MRT_journey_transfer_wait_is_valid( $arrival_hhmm, $departure_hhmm );
}

/**
 * Whether an edge from current station to next stop is valid in search.
 */
function MRT_journey_constraint_edge_allowed(
	int $service_id,
	int $leg_from,
	int $leg_to,
	int $goal_station_id
): bool {
	if ( ! MRT_journey_constraint_leg_direction( $service_id, $leg_from, $leg_to, $goal_station_id ) ) {
		return false;
	}
	if ( $leg_to === $goal_station_id ) {
		return true;
	}
	if ( ! MRT_journey_constraint_intermediate_hub( $leg_to ) ) {
		return false;
	}
	return ! MRT_journey_constraint_leg_overshoots( $service_id, $leg_from, $leg_to, $goal_station_id );
}
