<?php
/**
 * Per-station price zone assignment (post meta + defaults).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key: array of 1–2 zone numbers (1–4).
 */
function MRT_station_price_zones_meta_key(): string {
	return 'mrt_station_price_zones';
}

/**
 * Sanitize zone list from admin/REST/CSV (max two zones, values 1–4).
 *
 * @param mixed $input Raw value.
 * @return int[]
 */
function MRT_sanitize_station_price_zones( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}
	$zones = array();
	foreach ( $input as $zone ) {
		$n = (int) $zone;
		if ( $n < 1 || $n > 4 || in_array( $n, $zones, true ) ) {
			continue;
		}
		$zones[] = $n;
		if ( count( $zones ) >= 2 ) {
			break;
		}
	}
	sort( $zones );
	return $zones;
}

/**
 * Parse CSV cell "1" or "1,2" into zone list.
 *
 * @return int[]
 */
function MRT_parse_station_price_zones_csv( string $raw ): array {
	$raw = trim( $raw );
	if ( $raw === '' ) {
		return array();
	}
	$parts = preg_split( '/[,;]+/', $raw ) ?: array();
	return MRT_sanitize_station_price_zones( array_map( 'trim', $parts ) );
}

/**
 * Stored zones for one station (empty when meta not set).
 *
 * @return int[]
 */
function MRT_get_station_price_zones_stored( int $station_id ): array {
	if ( $station_id <= 0 ) {
		return array();
	}
	$stored = get_post_meta( $station_id, MRT_station_price_zones_meta_key(), true );
	if ( ! is_array( $stored ) ) {
		return array();
	}
	return MRT_sanitize_station_price_zones( $stored );
}

/**
 * Effective zones: stored meta only (empty when not configured).
 *
 * @return int[]
 */
function MRT_get_station_price_zones( int $station_id ): array {
	return MRT_get_station_price_zones_stored( $station_id );
}

/**
 * Persist zone list; empty list removes meta.
 *
 * @param int[] $zones Zone numbers.
 */
function MRT_update_station_price_zones_meta( int $station_id, array $zones ): void {
	$clean = MRT_sanitize_station_price_zones( $zones );
	if ( $clean === array() ) {
		delete_post_meta( $station_id, MRT_station_price_zones_meta_key() );
		return;
	}
	update_post_meta( $station_id, MRT_station_price_zones_meta_key(), $clean );
}

/**
 * Whether zones are configured on the station.
 */
function MRT_station_price_zones_is_custom( int $station_id ): bool {
	return MRT_get_station_price_zones_stored( $station_id ) !== array();
}

/**
 * Count published stations without configured price zones.
 */
function MRT_count_stations_without_price_zones(): int {
	if ( ! function_exists( 'MRT_get_all_stations' ) ) {
		return 0;
	}
	$count = 0;
	foreach ( MRT_get_all_stations() as $station_id ) {
		if ( MRT_get_station_price_zones( (int) $station_id ) === array() ) {
			++$count;
		}
	}
	return $count;
}
