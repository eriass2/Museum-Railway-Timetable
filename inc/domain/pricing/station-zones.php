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
 * Effective zones: stored meta, else title default, else empty.
 *
 * @return int[]
 */
function MRT_get_station_price_zones( int $station_id ): array {
	$stored = MRT_get_station_price_zones_stored( $station_id );
	if ( $stored !== array() ) {
		return $stored;
	}
	$title    = get_the_title( $station_id );
	$defaults = MRT_default_station_price_zones_by_title();
	return $defaults[ $title ] ?? array();
}

/**
 * Persist zone list; empty list removes meta (revert to title defaults).
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
 * Whether zones are explicitly stored (not title fallback).
 */
function MRT_station_price_zones_is_custom( int $station_id ): bool {
	return MRT_get_station_price_zones_stored( $station_id ) !== array();
}
