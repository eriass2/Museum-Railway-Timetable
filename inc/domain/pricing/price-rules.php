<?php
/**
 * Trip price selection rules (zone span, afternoon return, matrix lookup).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clamp geographic zone span to fare bands (max three zones in 2026 taxa).
 */
function MRT_pricing_zone_count( int $zones ): int {
	if ( $zones <= 0 ) {
		return MRT_price_zone_cap();
	}
	return max( 1, min( MRT_price_zone_cap(), $zones ) );
}

/**
 * Parse HH:MM departure time to minutes from midnight.
 */
function MRT_parse_trip_clock_minutes( string $hhmm ): ?int {
	if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', trim( $hhmm ), $matches ) ) {
		return null;
	}
	return ( (int) $matches[1] * 60 ) + (int) $matches[2];
}

/**
 * Afternoon return fare applies when both legs depart at or after 15:00.
 */
function MRT_qualifies_for_afternoon_return(
	string $trip_type,
	string $outbound_departure,
	string $inbound_departure,
	int $threshold_minutes = 900
): bool {
	if ( $trip_type !== 'return' ) {
		return false;
	}
	$out_minutes = MRT_parse_trip_clock_minutes( $outbound_departure );
	$in_minutes  = MRT_parse_trip_clock_minutes( $inbound_departure );
	if ( $out_minutes === null || $in_minutes === null ) {
		return false;
	}
	return $out_minutes >= $threshold_minutes && $in_minutes >= $threshold_minutes;
}

/**
 * @param array<int, int[]> $station_zones_map Station post ID => zone numbers.
 */
function MRT_zones_for_station_pair( int $from_id, int $to_id, array $station_zones_map ): int {
	$from_zones = $station_zones_map[ $from_id ] ?? array();
	$to_zones   = $station_zones_map[ $to_id ] ?? array();
	$best       = MRT_price_zone_cap();
	if ( $from_zones === array() || $to_zones === array() ) {
		return $best;
	}
	foreach ( $from_zones as $from_zone ) {
		foreach ( $to_zones as $to_zone ) {
			$span = abs( (int) $to_zone - (int) $from_zone ) + 1;
			$best = min( $best, $span );
		}
	}
	return MRT_pricing_zone_count( $best );
}

/**
 * Count distinct fare zones along a station path (union of each stop's zones).
 *
 * @param int[]               $station_ids       Ordered station post IDs on the journey.
 * @param array<int, int[]>   $station_zones_map Station post ID => zone numbers.
 */
function MRT_zones_for_station_path( array $station_ids, array $station_zones_map ): int {
	$seen = array();
	foreach ( $station_ids as $station_id ) {
		$sid = (int) $station_id;
		if ( $sid <= 0 ) {
			continue;
		}
		foreach ( $station_zones_map[ $sid ] ?? array() as $zone ) {
			$seen[ (int) $zone ] = true;
		}
	}
	if ( $seen === array() ) {
		return MRT_price_zone_cap();
	}
	return MRT_pricing_zone_count( count( $seen ) );
}

/**
 * @param array<int, array<string, mixed>> $legs Each leg: service_id, from_station_id, to_station_id.
 * @return int[]
 */
function MRT_collect_journey_leg_station_ids( array $legs ): array {
	$ids   = array();
	$seen  = array();
	foreach ( $legs as $leg ) {
		if ( ! is_array( $leg ) ) {
			continue;
		}
		$service_id = (int) ( $leg['service_id'] ?? 0 );
		$from_id    = (int) ( $leg['from_station_id'] ?? 0 );
		$to_id      = (int) ( $leg['to_station_id'] ?? 0 );
		if ( $service_id <= 0 || $from_id <= 0 || $to_id <= 0 ) {
			continue;
		}
		$detail = MRT_get_connection_journey_detail( $service_id, $from_id, $to_id );
		foreach ( (array) ( $detail['stops'] ?? array() ) as $stop ) {
			$sid = (int) ( $stop['station_id'] ?? 0 );
			if ( $sid <= 0 || isset( $seen[ $sid ] ) ) {
				continue;
			}
			$seen[ $sid ] = true;
			$ids[]        = $sid;
		}
	}
	return $ids;
}

/**
 * @param array<int, array<string, mixed>> $legs Journey legs with service and station ids.
 * @param array<int, int[]>|null           $station_zones_map Optional zone map.
 */
function MRT_zones_for_journey_legs( array $legs, ?array $station_zones_map = null ): int {
	$station_zones_map = $station_zones_map ?? MRT_get_station_price_zones_map();
	$station_ids       = MRT_collect_journey_leg_station_ids( $legs );
	if ( $station_ids === array() ) {
		return MRT_price_zone_cap();
	}
	return MRT_zones_for_station_path( $station_ids, $station_zones_map );
}

/**
 * Resolve fare zone count for wizard pricing (route-based when legs given).
 *
 * @param array<int, array<string, mixed>>|null $outbound_legs Outbound legs.
 * @param array<int, array<string, mixed>>|null $inbound_legs  Return legs (when return trip).
 */
function MRT_zones_for_trip_price(
	int $from_id,
	int $to_id,
	?array $outbound_legs = null,
	?array $inbound_legs = null
): int {
	if ( $outbound_legs !== null && $outbound_legs !== array() ) {
		$zones = MRT_zones_for_journey_legs( $outbound_legs );
		if ( $inbound_legs !== null && $inbound_legs !== array() ) {
			$zones = max( $zones, MRT_zones_for_journey_legs( $inbound_legs ) );
		}
		return MRT_pricing_zone_count( $zones );
	}
	return MRT_zones_for_station_pair_ids( $from_id, $to_id );
}

/**
 * Parse JSON journey legs from REST query param.
 *
 * @return array<int, array<string, mixed>>|null
 */
function MRT_parse_trip_price_legs_param( string $raw ): ?array {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return null;
	}
	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return null;
	}
	$legs = array();
	foreach ( $decoded as $leg ) {
		if ( ! is_array( $leg ) ) {
			continue;
		}
		$service_id = (int) ( $leg['service_id'] ?? 0 );
		$from_id    = (int) ( $leg['from_station_id'] ?? 0 );
		$to_id      = (int) ( $leg['to_station_id'] ?? 0 );
		if ( $service_id <= 0 || $from_id <= 0 || $to_id <= 0 ) {
			continue;
		}
		$legs[] = array(
			'service_id'       => $service_id,
			'from_station_id'  => $from_id,
			'to_station_id'    => $to_id,
		);
	}
	return $legs === array() ? null : $legs;
}

/**
 * Zone span for two stations using the configured station-zone map.
 */
function MRT_zones_for_station_pair_ids( int $from_id, int $to_id ): int {
	return MRT_zones_for_station_pair( $from_id, $to_id, MRT_get_station_price_zones_map() );
}

/**
 * @param array<string, array<string, int|null>> $matrix Flat ticket-type matrix.
 */
function MRT_price_matrix_has_any_price( array $matrix ): bool {
	foreach ( MRT_price_ticket_type_keys() as $ticket_type ) {
		foreach ( MRT_price_category_keys() as $category ) {
			$value = $matrix[ $ticket_type ][ $category ] ?? null;
			if ( $value !== null && $value !== '' ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * @return array<string, array<string, int|null>>
 */
function MRT_afternoon_return_price_matrix_flat(): array {
	return array( 'return' => MRT_get_afternoon_return_prices() );
}

/**
 * Select trip price matrix for wizard summary.
 *
 * @param array<string, array<string, array<int, int|null>>>|null $full_matrix
 * @return array{matrix: array<string, array<string, int|null>>, activeType: string, isAfternoonReturn: bool}|null
 */
function MRT_price_matrix_for_trip(
	string $trip_type,
	int $zones,
	string $outbound_departure = '',
	string $inbound_departure = '',
	?array $full_matrix = null
): ?array {
	$full_matrix    = $full_matrix ?? MRT_get_price_matrix();
	$is_afternoon   = MRT_qualifies_for_afternoon_return( $trip_type, $outbound_departure, $inbound_departure );
	$matrix         = $is_afternoon ? MRT_afternoon_return_price_matrix_flat() : MRT_price_matrix_for_zone( $full_matrix, $zones );
	if ( ! MRT_price_matrix_has_any_price( $matrix ) ) {
		return null;
	}
	return array(
		'matrix'            => $matrix,
		'activeType'        => $trip_type === 'return' ? 'return' : 'single',
		'isAfternoonReturn' => $is_afternoon,
	);
}

/**
 * @param array<string, array<string, array<int, int|null>>>|null $full_matrix
 * @return array{day: array<string, int|null>}|null
 */
function MRT_day_ticket_matrix( int $zones, ?array $full_matrix = null ): ?array {
	$full_matrix = $full_matrix ?? MRT_get_price_matrix();
	$day_row     = MRT_price_matrix_for_zone( $full_matrix, $zones )['day'] ?? array();
	if ( ! MRT_price_matrix_has_any_price( array( 'day' => $day_row ) ) ) {
		return null;
	}
	return array( 'day' => $day_row );
}

/**
 * REST/API payload for wizard trip prices.
 *
 * @return array<string, mixed>
 */
function MRT_trip_prices_response(
	int $from_id,
	int $to_id,
	string $trip_type,
	string $outbound_departure = '',
	string $inbound_departure = '',
	bool $include_day = false,
	?array $outbound_legs = null,
	?array $inbound_legs = null
): array {
	$zones = MRT_zones_for_trip_price(
		$from_id,
		$to_id,
		$outbound_legs,
		$trip_type === 'return' ? $inbound_legs : null
	);
	$trip  = MRT_price_matrix_for_trip( $trip_type, $zones, $outbound_departure, $inbound_departure );
	$day   = $include_day ? MRT_day_ticket_matrix( $zones ) : null;

	return array(
		'zones' => $zones,
		'trip'  => $trip,
		'day'   => $day,
	);
}
