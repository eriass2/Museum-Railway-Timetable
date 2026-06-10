<?php
/**
 * Transient cache for journey calendar months (Fas 2 performance).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option key — bump to invalidate all calendar month transients. */
const MRT_JOURNEY_CALENDAR_CACHE_VERSION_OPTION = 'mrt_journey_calendar_cache_ver';

/** TTL for cached calendar months (seconds). */
const MRT_JOURNEY_CALENDAR_CACHE_TTL = HOUR_IN_SECONDS;

/**
 * Current cache generation (invalidated on timetable data changes).
 */
function MRT_journey_calendar_cache_version(): int {
	return max( 1, (int) get_option( MRT_JOURNEY_CALENDAR_CACHE_VERSION_OPTION, 1 ) );
}

/**
 * Bump cache version so existing transients are ignored.
 */
function MRT_bump_journey_calendar_cache_version(): void {
	update_option(
		MRT_JOURNEY_CALENDAR_CACHE_VERSION_OPTION,
		MRT_journey_calendar_cache_version() + 1,
		false
	);
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
	$parts = array(
		(string) MRT_journey_calendar_cache_version(),
		(string) $from_station_id,
		(string) $to_station_id,
		(string) $year,
		(string) $month,
		$trip_type === 'return' ? 'return' : 'single',
	);
	return 'mrt_jcal_' . md5( implode( '|', $parts ) );
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
	set_transient( $cache_key, $payload, MRT_JOURNEY_CALENDAR_CACHE_TTL );
}

/**
 * Invalidate calendar cache when plugin timetable data changes.
 *
 * @param int $post_id Post ID.
 */
function MRT_journey_calendar_maybe_invalidate_on_save( int $post_id ): void {
	if ( $post_id <= 0 || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	$post_type = get_post_type( $post_id );
	if ( ! is_string( $post_type ) || ! in_array( $post_type, MRT_POST_TYPES, true ) ) {
		return;
	}
	MRT_bump_journey_calendar_cache_version();
}

add_action( 'save_post', 'MRT_journey_calendar_maybe_invalidate_on_save', 20, 1 );

add_action(
	'updated_option',
	static function ( string $option ): void {
		if ( in_array( $option, array( 'mrt_price_matrix', 'mrt_public_notices' ), true ) ) {
			MRT_bump_journey_calendar_cache_version();
		}
	},
	10,
	1
);
