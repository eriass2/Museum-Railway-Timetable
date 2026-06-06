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
 * Lennakatten reference fare table (Taxa 2026) — dev/tests only, not runtime default.
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_builtin_price_matrix() {
	/** @var array<string, array<string, array<int, int|null>>> $matrix */
	$matrix = require __DIR__ . '/price-matrix-builtin.php';

	return $matrix;
}

/**
 * Empty matrix shaped by the current price schema (all cells null).
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_build_empty_price_matrix(): array {
	$matrix = array();
	foreach ( MRT_price_ticket_type_keys() as $ticket_type ) {
		$matrix[ $ticket_type ] = array();
		foreach ( MRT_price_category_keys() as $category ) {
			$matrix[ $ticket_type ][ $category ] = array();
			foreach ( MRT_price_zone_keys() as $zone ) {
				$matrix[ $ticket_type ][ $category ][ $zone ] = null;
			}
		}
	}
	return $matrix;
}

/**
 * Default matrix for new installs (empty until configured or imported).
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_default_price_matrix() {
	return MRT_build_empty_price_matrix();
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
