<?php
/**
 * Transfer wait limits and transfer-hub station priority for journey search.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimum minutes between arrival at hub and next departure.
 */
function MRT_journey_min_transfer_minutes(): int {
	$opts = MRT_get_plugin_settings();
	$min  = max( 0, (int) ( $opts['min_transfer_minutes'] ?? 5 ) );
	return max( 0, (int) apply_filters( 'mrt_min_transfer_minutes', $min ) );
}

/**
 * Maximum acceptable transfer wait (same calendar day).
 */
function MRT_journey_max_transfer_minutes(): int {
	$opts = MRT_get_plugin_settings();
	$max  = max( 0, (int) ( $opts['max_transfer_minutes'] ?? 120 ) );
	$max  = (int) apply_filters( 'mrt_max_transfer_minutes', $max );
	return max( MRT_journey_min_transfer_minutes(), $max );
}

/**
 * Minutes waiting at hub between arrival and next departure.
 */
function MRT_journey_transfer_wait_minutes( string $arrival_hhmm, string $departure_hhmm ): ?int {
	if ( ! MRT_validate_time_hhmm( $arrival_hhmm ) || ! MRT_validate_time_hhmm( $departure_hhmm ) ) {
		return null;
	}
	return MRT_format_duration_minutes( $arrival_hhmm, $departure_hhmm );
}

/**
 * Whether transfer wait is within min/max bounds.
 */
function MRT_journey_transfer_wait_is_valid( string $arrival_hhmm, string $departure_hhmm ): bool {
	$wait = MRT_journey_transfer_wait_minutes( $arrival_hhmm, $departure_hhmm );
	if ( $wait === null ) {
		return false;
	}
	return $wait >= MRT_journey_min_transfer_minutes() && $wait <= MRT_journey_max_transfer_minutes();
}

/**
 * Sort key for transfer stations (lower = preferred). Selknä-style bus hubs rank first.
 *
 * @param int $station_id Station post ID
 */
function MRT_journey_transfer_station_priority( int $station_id ): int {
	$custom = get_post_meta( $station_id, 'mrt_transfer_priority', true );
	if ( $custom !== '' && $custom !== false && is_numeric( $custom ) ) {
		return (int) $custom;
	}
	$priority = 50;
	if ( get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1' ) {
		$priority = 0;
	}
	return (int) apply_filters( 'mrt_transfer_station_priority', $priority, $station_id );
}

/**
 * Compare two transfer candidates for stable sort (priority, wait, departure).
 *
 * @param array{priority: int, wait: int, departure: string} $a
 * @param array{priority: int, wait: int, departure: string} $b
 */
function MRT_journey_compare_transfer_candidates( array $a, array $b ): int {
	if ( $a['priority'] !== $b['priority'] ) {
		return $a['priority'] <=> $b['priority'];
	}
	if ( $a['wait'] !== $b['wait'] ) {
		return $a['wait'] <=> $b['wait'];
	}
	return strcmp( $a['departure'], $b['departure'] );
}
