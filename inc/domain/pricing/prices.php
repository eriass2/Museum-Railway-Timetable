<?php
/**
 * Public price matrix (option mrt_price_matrix)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Ticket-type keys (rows in mockup price table)
 *
 * @return string[]
 */
function MRT_price_ticket_type_keys() {
	return array( 'single', 'return', 'day' );
}

/**
 * Passenger category keys (columns)
 *
 * @return string[]
 */
function MRT_price_category_keys() {
	return array( 'adult', 'child_4_15', 'child_0_3', 'student_senior' );
}

/**
 * Price zone keys.
 *
 * @return int[]
 */
function MRT_price_zone_keys() {
	return array( 1, 2, 3, 4 );
}

/**
 * Max zones used for fare lookup (2026 taxa: three price bands).
 *
 * @return int
 */
function MRT_price_zone_cap() {
	return 3;
}

/**
 * 2025 Lennakatten fare table from Taxa 2025.
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_builtin_price_matrix() {
	/** @var array<string, array<string, array<int, int|null>>> $matrix */
	$matrix = require __DIR__ . '/price-matrix-builtin.php';

	return $matrix;
}

/**
 * Default matrix.
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_default_price_matrix() {
	return MRT_get_builtin_price_matrix();
}

/**
 * Sanitize price matrix from settings form
 *
 * @param mixed $input Raw input
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_sanitize_price_matrix( $input ) {
	$out = MRT_get_default_price_matrix();
	if ( ! is_array( $input ) ) {
		return $out;
	}
	foreach ( MRT_price_ticket_type_keys() as $t ) {
		if ( ! isset( $input[ $t ] ) || ! is_array( $input[ $t ] ) ) {
			continue;
		}
		foreach ( MRT_price_category_keys() as $c ) {
			if ( ! array_key_exists( $c, $input[ $t ] ) ) {
				continue;
			}
			if ( is_array( $input[ $t ][ $c ] ) ) {
				foreach ( MRT_price_zone_keys() as $z ) {
					if ( ! array_key_exists( $z, $input[ $t ][ $c ] ) ) {
						continue;
					}
					$out[ $t ][ $c ][ $z ] = MRT_sanitize_price_value( $input[ $t ][ $c ][ $z ] );
				}
				continue;
			}

			$legacy = MRT_sanitize_price_value( $input[ $t ][ $c ] );
			foreach ( MRT_price_zone_keys() as $z ) {
				$out[ $t ][ $c ][ $z ] = $legacy;
			}
		}
	}
	return $out;
}

/**
 * Sanitize one price value from settings.
 *
 * @param mixed $value Raw value
 * @return int|null
 */
function MRT_sanitize_price_value( $value ) {
	if ( $value === '' || $value === null ) {
		return null;
	}
	$n = (int) $value;
	return ( $n >= 0 ) ? $n : null;
}

/**
 * Stored matrix merged with defaults
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_price_matrix() {
	$stored = get_option( 'mrt_price_matrix', array() );
	if ( ! is_array( $stored ) ) {
		return MRT_get_default_price_matrix();
	}
	return MRT_sanitize_price_matrix( $stored );
}

/**
 * Full zone matrix plus active trip and zone.
 *
 * @param array<string, mixed> $args trip => single|return|day, from_station_id, to_station_id
 * @return array<string, mixed> matrix, active_ticket_type, active_row
 */
function MRT_get_prices_for_context( $args = array() ) {
	$trip = isset( $args['trip'] ) ? sanitize_key( (string) $args['trip'] ) : 'single';
	if ( ! in_array( $trip, MRT_price_ticket_type_keys(), true ) ) {
		$trip = 'single';
	}
	$full  = MRT_get_price_matrix();
	$zones = MRT_price_zones_for_station_pair(
		(int) ( $args['from_station_id'] ?? 0 ),
		(int) ( $args['to_station_id'] ?? 0 )
	);
	return array(
		'matrix'             => $full,
		'active_ticket_type' => $trip,
		'active_zone'        => $zones,
		'active_row'         => MRT_price_matrix_for_zone( $full, $zones )[ $trip ],
	);
}

/**
 * Select one zone column from the stored zone matrix.
 *
 * @param array<string, array<string, array<int, int|null>>> $matrix Full zone matrix
 * @param int                                                $zones Number of zones
 * @return array<string, array<string, int|null>>
 */
function MRT_price_matrix_for_zone( array $matrix, int $zones ): array {
	$zone_key = max( 1, min( MRT_price_zone_cap(), $zones ) );
	$out      = array();
	foreach ( MRT_price_ticket_type_keys() as $t ) {
		$out[ $t ] = array();
		foreach ( MRT_price_category_keys() as $c ) {
			$out[ $t ][ $c ] = $matrix[ $t ][ $c ][ $zone_key ] ?? null;
		}
	}
	return $out;
}

/**
 * Default station zones. Boundary stations are listed in both neighboring zones.
 *
 * @return array<string, int[]>
 */
function MRT_default_station_price_zones_by_title(): array {
	return array(
		'Uppsala Östra'   => array( 1 ),
		'Fyrislund'       => array( 1 ),
		'Årsta'           => array( 1, 2 ),
		'Skölsta'         => array( 2 ),
		'Bärby'           => array( 2 ),
		'Gunsta'          => array( 2, 3 ),
		'Marielund'       => array( 3 ),
		'Lövstahagen'     => array( 3 ),
		'Selknä'          => array( 3 ),
		'Löt'             => array( 3 ),
		'Länna'           => array( 3 ),
		'Fjällnora'       => array( 3 ),
		'Almunge'         => array( 3, 4 ),
		'Moga'            => array( 4 ),
		'Faringe'         => array( 4 ),
		'Linnés Hammarby' => array( 4 ),
	);
}

/**
 * Station zones keyed by WordPress station ID for frontend localization.
 *
 * @return array<int, int[]>
 */
function MRT_get_station_price_zones_map(): array {
	$map = array();
	if ( ! function_exists( 'MRT_get_all_stations' ) ) {
		return $map;
	}
	$by_title = MRT_default_station_price_zones_by_title();
	foreach ( MRT_get_all_stations() as $station_id ) {
		$title = get_the_title( (int) $station_id );
		if ( isset( $by_title[ $title ] ) ) {
			$map[ (int) $station_id ] = $by_title[ $title ];
		}
	}
	return $map;
}

/**
 * Cheapest zone count between two station zone sets.
 *
 * Boundary station rule: a station on a zone line counts as either zone, while
 * passing the boundary means entering the next zone.
 *
 * @param int[] $from_zones Possible zones for origin
 * @param int[] $to_zones Possible zones for destination
 * @return int
 */
function MRT_price_zones_between_zone_sets( array $from_zones, array $to_zones ): int {
	$best = 4;
	foreach ( $from_zones as $from_zone ) {
		foreach ( $to_zones as $to_zone ) {
			$span = abs( (int) $to_zone - (int) $from_zone ) + 1;
			$best = min( $best, $span );
		}
	}
	return max( 1, min( 4, $best ) );
}

/**
 * Number of price zones for a station pair.
 *
 * @return int
 */
function MRT_price_zones_for_station_pair( int $from_station_id, int $to_station_id ): int {
	$map = MRT_get_station_price_zones_map();
	if ( ! isset( $map[ $from_station_id ], $map[ $to_station_id ] ) ) {
		return MRT_price_zone_cap();
	}
	return max( 1, min( MRT_price_zone_cap(), MRT_price_zones_between_zone_sets( $map[ $from_station_id ], $map[ $to_station_id ] ) ) );
}

/**
 * Flat afternoon return fares (tur och retur efter kl 15).
 *
 * @return array<string, int>
 */
function MRT_get_afternoon_return_prices() {
	return array(
		'adult'          => 160,
		'child_4_15'     => 60,
		'child_0_3'      => 0,
		'student_senior' => 140,
	);
}

/**
 * Parse HH:MM clock to minutes since midnight.
 *
 * @param string $hhmm Clock value
 * @return int|null
 */
function MRT_parse_trip_clock( $hhmm ) {
	if ( ! is_string( $hhmm ) || ! preg_match( '/^(\d{1,2}):(\d{2})$/', trim( $hhmm ), $m ) ) {
		return null;
	}
	return ( (int) $m[1] * 60 ) + (int) $m[2];
}

/**
 * Whether a return trip qualifies for the flat afternoon fare.
 *
 * @param string $trip_type single|return|day
 * @param string $outbound_departure Outbound origin departure (HH:MM)
 * @param string $inbound_departure  Return origin departure (HH:MM)
 * @return bool
 */
function MRT_qualifies_for_afternoon_return( $trip_type, $outbound_departure, $inbound_departure ) {
	if ( $trip_type !== 'return' ) {
		return false;
	}
	$threshold = 15 * 60;
	$out       = MRT_parse_trip_clock( (string) $outbound_departure );
	$in        = MRT_parse_trip_clock( (string) $inbound_departure );
	if ( $out === null || $in === null ) {
		return false;
	}
	return $out >= $threshold && $in >= $threshold;
}

/**
 * Human labels for ticket-type rows (admin + public wizard)
 *
 * @return array<string, string>
 */
function MRT_price_ticket_type_labels() {
	return array(
		'single' => __( 'Enkelbiljett', 'museum-railway-timetable' ),
		'return' => __( 'Returbiljett', 'museum-railway-timetable' ),
		'day'    => __( 'Dagskort', 'museum-railway-timetable' ),
	);
}

/**
 * Human labels for passenger columns
 *
 * @return array<string, string>
 */
function MRT_price_category_labels() {
	return array(
		'adult'          => __( 'Vuxen', 'museum-railway-timetable' ),
		'child_4_15'     => __( 'Barn 4–15', 'museum-railway-timetable' ),
		'child_0_3'      => __( 'Barn 0–6', 'museum-railway-timetable' ),
		'student_senior' => __( 'Student / pensionär', 'museum-railway-timetable' ),
	);
}
