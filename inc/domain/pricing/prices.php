<?php
/**
 * Public price matrix (option mrt_price_matrix)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once __DIR__ . '/price-schema.php';

/**
 * Ticket-type keys (rows in mockup price table)
 *
 * @return string[]
 */
function MRT_price_ticket_type_keys() {
	return MRT_price_schema_ticket_keys();
}

/**
 * Passenger category keys (columns)
 *
 * @return string[]
 */
function MRT_price_category_keys() {
	return MRT_price_schema_category_keys();
}

/**
 * Price zone keys.
 *
 * @return int[]
 */
function MRT_price_zone_keys() {
	return MRT_price_schema_zone_keys();
}

/**
 * Max zones used for fare lookup (2026 taxa: three price bands).
 *
 * @return int
 */
function MRT_price_zone_cap() {
	return MRT_price_schema_zone_cap();
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
 * Default station zones per Lennakatten taxa 2026 (see docs/PRICE_ZONES.md).
 * Only Gunsta and Almunge span two zones (boundary stations).
 *
 * @return array<string, int[]>
 */
function MRT_default_station_price_zones_by_title(): array {
	return array(
		'Uppsala Östra'   => array( 1 ),
		'Fyrislund'       => array( 1 ),
		'Årsta'           => array( 1 ),
		'Skölsta'         => array( 1 ),
		'Bärby'           => array( 1 ),
		'Gunsta'          => array( 1, 2 ),
		'Marielund'       => array( 2 ),
		'Lövstahagen'     => array( 2 ),
		'Selknä'          => array( 2 ),
		'Löt'             => array( 2 ),
		'Länna'           => array( 2 ),
		'Fjällnora'       => array( 2 ),
		'Almunge'         => array( 2, 3 ),
		'Moga'            => array( 3 ),
		'Faringe'         => array( 3 ),
		'Linnés Hammarby' => array( 3 ),
	);
}

require_once __DIR__ . '/station-zones.php';

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
	foreach ( MRT_get_all_stations() as $station_id ) {
		$zones = MRT_get_station_price_zones( (int) $station_id );
		if ( $zones !== array() ) {
			$map[ (int) $station_id ] = $zones;
		}
	}
	return $map;
}

/**
 * Flat afternoon return fares (tur och retur efter kl 15).
 *
 * @return array<string, int>
 */
function MRT_get_afternoon_return_prices() {
	return MRT_price_schema_afternoon_return_prices();
}

/**
 * Human labels for ticket-type rows (admin + public wizard)
 *
 * @return array<string, string>
 */
function MRT_price_ticket_type_labels() {
	return MRT_price_schema_ticket_labels();
}

/**
 * Human labels for passenger columns
 *
 * @return array<string, string>
 */
function MRT_price_category_labels() {
	return MRT_price_schema_category_labels();
}

require_once __DIR__ . '/price-rules.php';
