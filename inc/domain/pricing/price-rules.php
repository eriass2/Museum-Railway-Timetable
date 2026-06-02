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
	bool $include_day = false
): array {
	$zones = MRT_zones_for_station_pair_ids( $from_id, $to_id );
	$trip  = MRT_price_matrix_for_trip( $trip_type, $zones, $outbound_departure, $inbound_departure );
	$day   = $include_day ? MRT_day_ticket_matrix( $zones ) : null;

	return array(
		'zones' => $zones,
		'trip'  => $trip,
		'day'   => $day,
	);
}
