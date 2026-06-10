<?php
/**
 * Trip price rules: matrix
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

function MRT_afternoon_return_price_matrix_flat(): array {
	return array( 'return' => MRT_get_afternoon_return_prices() );
}

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

function MRT_day_ticket_matrix( int $zones, ?array $full_matrix = null ): ?array {
	$full_matrix = $full_matrix ?? MRT_get_price_matrix();
	$day_row     = MRT_price_matrix_for_zone( $full_matrix, $zones )['day'] ?? array();
	if ( ! MRT_price_matrix_has_any_price( array( 'day' => $day_row ) ) ) {
		return null;
	}
	return array( 'day' => $day_row );
}

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
