<?php
/**
 * Transfer wait limits and transfer-hub station priority for journey search.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimum minutes between arrival at hub and next departure.
 */
function MRT_journey_min_transfer_minutes(): int {
	$opts = MRT_get_plugin_settings();
	$min  = max( 0, (int) ( $opts['min_transfer_minutes'] ?? 0 ) );
	return max( 0, (int) apply_filters( 'mrt_min_transfer_minutes', $min ) );
}

/**
 * Maximum acceptable transfer wait (same calendar day).
 */
function MRT_journey_max_transfer_minutes(): int {
	$opts = MRT_get_plugin_settings();
	$max  = max( 0, (int) ( $opts['max_transfer_minutes'] ?? 120 ) );
	$max  = (int) apply_filters( 'mrt_max_transfer_minutes', $max );
	return max( MRT_journey_min_transfer_minutes(), $max );
}

/**
 * Minutes waiting at hub between arrival and next departure.
 */
function MRT_journey_transfer_wait_minutes( string $arrival_hhmm, string $departure_hhmm ): ?int {
	if ( ! MRT_validate_time_hhmm( $arrival_hhmm ) || ! MRT_validate_time_hhmm( $departure_hhmm ) ) {
		return null;
	}
	return MRT_format_duration_minutes( $arrival_hhmm, $departure_hhmm );
}

/**
 * Whether transfer wait is within min/max bounds.
 */
function MRT_journey_transfer_wait_is_valid( string $arrival_hhmm, string $departure_hhmm ): bool {
	$wait = MRT_journey_transfer_wait_minutes( $arrival_hhmm, $departure_hhmm );
	if ( $wait === null ) {
		return false;
	}
	return $wait >= MRT_journey_min_transfer_minutes() && $wait <= MRT_journey_max_transfer_minutes();
}

/**
 * Whether a service is a connecting bus (taxonomy or Selknä-style shuttle route).
 */
function MRT_journey_service_is_bus( int $service_id ): bool {
	if ( $service_id <= 0 ) {
		return false;
	}
	$terms = wp_get_post_terms( $service_id, 'mrt_train_type', array( 'fields' => 'slugs' ) );
	if ( ! is_wp_error( $terms ) && in_array( 'buss', $terms, true ) ) {
		return true;
	}
	$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
	return $route_id > 0 && MRT_journey_route_is_bus_shuttle( $route_id );
}

/**
 * Two-stop route where both ends are marked bus stops (Selknä–Fjällnora).
 */
function MRT_journey_route_is_bus_shuttle( int $route_id ): bool {
	$stations = MRT_get_route_stations( $route_id );
	if ( count( $stations ) !== 2 ) {
		return false;
	}
	foreach ( $stations as $station_id ) {
		if ( get_post_meta( (int) $station_id, 'mrt_station_bus_suffix', true ) !== '1' ) {
			return false;
		}
	}
	return true;
}

/**
 * Minimum wait before boarding the next leg (train→bus at bus hub uses 0 min).
 */
function MRT_journey_min_transfer_between_legs(
	int $hub_station_id,
	int $incoming_service_id,
	int $outgoing_service_id,
	int $search_min_minutes
): int {
	if (
		$hub_station_id > 0
		&& get_post_meta( $hub_station_id, 'mrt_station_bus_suffix', true ) === '1'
		&& MRT_journey_service_is_bus( $outgoing_service_id )
		&& ! MRT_journey_service_is_bus( $incoming_service_id )
	) {
		return 0;
	}
	return max( 0, $search_min_minutes );
}

/**
 * Whether wait between two legs satisfies min/max for that transfer type.
 */
function MRT_journey_transfer_wait_is_valid_between_services(
	string $arrival_hhmm,
	string $departure_hhmm,
	int $hub_station_id,
	int $incoming_service_id,
	int $outgoing_service_id,
	int $search_min_minutes
): bool {
	$wait = MRT_journey_transfer_wait_minutes( $arrival_hhmm, $departure_hhmm );
	if ( $wait === null ) {
		return false;
	}
	$min = MRT_journey_min_transfer_between_legs(
		$hub_station_id,
		$incoming_service_id,
		$outgoing_service_id,
		$search_min_minutes
	);
	$max = MRT_journey_max_transfer_minutes();
	return $wait >= $min && $wait <= $max;
}

/**
 * Sort key for transfer stations (lower = preferred). Selknä-style bus hubs rank first.
 *
 * @param int $station_id Station post ID
 */
function MRT_journey_transfer_station_priority( int $station_id ): int {
	$custom = get_post_meta( $station_id, 'mrt_transfer_priority', true );
	if ( $custom !== '' && $custom !== false && is_numeric( $custom ) ) {
		return (int) $custom;
	}
	$priority = 50;
	if ( get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1' ) {
		$priority = 0;
	}
	return (int) apply_filters( 'mrt_transfer_station_priority', $priority, $station_id );
}

/**
 * Station IDs that are route termini (start/end); cached per request.
 *
 * @return array<int, true>
 */
function MRT_journey_route_terminus_station_ids(): array {
	static $ids = null;
	if ( is_array( $ids ) ) {
		return $ids;
	}
	$ids    = array();
	$routes = get_posts(
		array(
			'post_type'      => 'mrt_route',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $routes as $route_id ) {
		MRT_journey_register_route_terminus_ids( $ids, (int) $route_id );
	}
	return $ids;
}

/**
 * @param array<int, true> $ids
 */
function MRT_journey_register_route_terminus_ids( array &$ids, int $route_id ): void {
	$ends = MRT_get_route_end_stations( $route_id );
	foreach ( array( 'start', 'end' ) as $key ) {
		$station_id = (int) ( $ends[ $key ] ?? 0 );
		if ( $station_id > 0 ) {
			$ids[ $station_id ] = true;
		}
	}
	$stations = MRT_get_route_stations( $route_id );
	if ( $stations === array() ) {
		return;
	}
	$ids[ (int) reset( $stations ) ] = true;
	$ids[ (int) end( $stations ) ]     = true;
}

/**
 * Whether passengers may change trains at this station in journey search.
 */
function MRT_journey_station_allows_transfer( int $station_id ): bool {
	if ( $station_id <= 0 ) {
		return false;
	}
	if ( get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1' ) {
		return true;
	}
	$custom = get_post_meta( $station_id, 'mrt_transfer_priority', true );
	if ( $custom !== '' && $custom !== false && is_numeric( $custom ) ) {
		return true;
	}
	if ( isset( MRT_journey_route_terminus_station_ids()[ $station_id ] ) ) {
		return true;
	}
	$title = get_the_title( $station_id );
	if ( $title !== '' && isset( MRT_journey_train_change_by_station()[ $title ] ) ) {
		return true;
	}
	return (bool) apply_filters( 'mrt_journey_station_allows_transfer', false, $station_id );
}

/**
 * Compare two transfer candidates for stable sort (priority, wait, departure).
 *
 * @param array{priority: int, wait: int, departure: string} $a
 * @param array{priority: int, wait: int, departure: string} $b
 */
function MRT_journey_compare_transfer_candidates( array $a, array $b ): int {
	if ( $a['priority'] !== $b['priority'] ) {
		return $a['priority'] <=> $b['priority'];
	}
	if ( $a['wait'] !== $b['wait'] ) {
		return $a['wait'] <=> $b['wait'];
	}
	return strcmp( $a['departure'], $b['departure'] );
}
