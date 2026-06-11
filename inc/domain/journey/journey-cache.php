<?php
/**
 * Unified journey wizard resource cache (server transients).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option key — bump to invalidate all journey wizard transients. */
const MRT_JOURNEY_CACHE_VERSION_OPTION = 'mrt_journey_calendar_cache_ver';

/** @deprecated Use MRT_JOURNEY_CACHE_VERSION_OPTION */
const MRT_JOURNEY_CALENDAR_CACHE_VERSION_OPTION = MRT_JOURNEY_CACHE_VERSION_OPTION;

/** Default TTL per resource (seconds). */
const MRT_JOURNEY_CACHE_DEFAULT_TTL = HOUR_IN_SECONDS;

/**
 * Current cache generation (invalidated on timetable data changes).
 */
function MRT_journey_cache_generation(): int {
	return max( 1, (int) get_option( MRT_JOURNEY_CACHE_VERSION_OPTION, 1 ) );
}

/**
 * @deprecated Use MRT_journey_cache_generation()
 */
function MRT_journey_calendar_cache_version(): int {
	return MRT_journey_cache_generation();
}

/**
 * Bump cache generation so existing transients are ignored.
 *
 * @param string|null $reason Dev-only invalidation reason.
 */
function MRT_journey_cache_bump_generation( ?string $reason = null ): void {
	update_option(
		MRT_JOURNEY_CACHE_VERSION_OPTION,
		MRT_journey_cache_generation() + 1,
		false
	);
	if ( $reason !== null && $reason !== '' && MRT_is_development_mode() ) {
		MRT_log( 'Journey cache generation bumped', array( 'reason' => $reason ), 'info' );
	}
}

/**
 * @deprecated Use MRT_journey_cache_bump_generation()
 */
function MRT_bump_journey_calendar_cache_version(): void {
	MRT_journey_cache_bump_generation( 'calendar_legacy_bump' );
}

/**
 * TTL for a cache resource.
 */
function MRT_journey_cache_ttl( string $resource ): int {
	$ttl = (int) apply_filters( 'mrt_journey_cache_ttl_' . $resource, MRT_JOURNEY_CACHE_DEFAULT_TTL );
	return max( 60, $ttl );
}

/**
 * Build transient key for a wizard REST resource.
 *
 * @param array<string, string|int> $params Stable param map (sorted internally).
 */
function MRT_journey_cache_key( string $resource, array $params ): string {
	$parts = array(
		(string) MRT_journey_cache_generation(),
		$resource,
	);
	ksort( $params );
	foreach ( $params as $name => $value ) {
		$parts[] = $name . '=' . (string) $value;
	}
	return 'mrt_jcache_' . md5( implode( '|', $parts ) );
}

/**
 * Read cached payload for a resource.
 *
 * @param array<string, string|int> $params Cache params.
 * @return array<string, mixed>|null
 */
function MRT_journey_cache_get( string $resource, array $params ): ?array {
	if ( ! function_exists( 'get_transient' ) ) {
		return null;
	}
	$key    = MRT_journey_cache_key( $resource, $params );
	$cached = get_transient( $key );
	return is_array( $cached ) ? $cached : null;
}

/**
 * Store payload in transient cache.
 *
 * @param array<string, string|int> $params Cache params.
 * @param array<string, mixed>      $payload Response or domain payload.
 */
function MRT_journey_cache_set( string $resource, array $params, array $payload ): void {
	if ( ! function_exists( 'set_transient' ) || $payload === array() ) {
		return;
	}
	$key = MRT_journey_cache_key( $resource, $params );
	set_transient( $key, $payload, MRT_journey_cache_ttl( $resource ) );
}

/**
 * Invalidate journey cache when plugin timetable data changes.
 *
 * @param int $post_id Post ID.
 */
function MRT_journey_cache_maybe_invalidate_on_save( int $post_id ): void {
	if ( $post_id <= 0 || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	$post_type = get_post_type( $post_id );
	if ( ! is_string( $post_type ) || ! in_array( $post_type, MRT_POST_TYPES, true ) ) {
		return;
	}
	MRT_journey_cache_bump_generation( 'save_post:' . $post_type );
}

/**
 * @deprecated Use MRT_journey_cache_maybe_invalidate_on_save()
 */
function MRT_journey_calendar_maybe_invalidate_on_save( int $post_id ): void {
	MRT_journey_cache_maybe_invalidate_on_save( $post_id );
}

add_action( 'save_post', 'MRT_journey_cache_maybe_invalidate_on_save', 20, 1 );

add_action(
	'updated_option',
	static function ( string $option ): void {
		if ( in_array( $option, array( 'mrt_price_matrix', 'mrt_public_notices' ), true ) ) {
			MRT_journey_cache_bump_generation( 'updated_option:' . $option );
		}
	},
	10,
	1
);
