<?php
/**
 * Journey calendar month cache — thin wrappers over journey-cache facade.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build transient key for one calendar month query.
 */
function MRT_journey_calendar_month_cache_key(
	int $from_station_id,
	int $to_station_id,
	int $year,
	int $month,
	string $trip_type
): string {
	return MRT_journey_cache_key(
		'calendar.month',
		array(
			'from'      => (string) $from_station_id,
			'to'        => (string) $to_station_id,
			'year'      => (string) $year,
			'month'     => (string) $month,
			'trip_type' => $trip_type === 'return' ? 'return' : 'single',
		)
	);
}

/**
 * Read cached month payload if present.
 *
 * @return array<string, array{status: string, type: string}>|null
 */
function MRT_journey_calendar_month_cache_get( string $cache_key ): ?array {
	if ( ! function_exists( 'get_transient' ) ) {
		return null;
	}
	$cached = get_transient( $cache_key );
	if ( ! is_array( $cached ) ) {
		return null;
	}
	return $cached;
}

/**
 * Store month payload in transient cache.
 *
 * @param array<string, array{status: string, type: string}> $payload Calendar month.
 */
function MRT_journey_calendar_month_cache_set( string $cache_key, array $payload ): void {
	if ( ! function_exists( 'set_transient' ) || $payload === array() ) {
		return;
	}
	set_transient( $cache_key, $payload, MRT_journey_cache_ttl( 'calendar.month' ) );
}
