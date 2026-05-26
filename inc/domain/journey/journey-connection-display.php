<?php
/**
 * Display helpers for journey connection times (wizard cards, tests).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Departure display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_departure_display( array $conn ): string {
	$dep = $conn['from_departure'] ?: $conn['from_arrival'];
	return $dep ? MRT_format_time_display( $dep ) : '—';
}

/**
 * Arrival display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_arrival_display( array $conn ): string {
	$arr = $conn['to_arrival'] ?: $conn['to_departure'];
	return $arr ? MRT_format_time_display( $arr ) : '—';
}

/**
 * Destination display text for a connection row.
 *
 * @param array<string, mixed> $conn Connection row
 */
function MRT_journey_connection_destination_display( array $conn ): string {
	if ( ! empty( $conn['destination'] ) ) {
		return (string) $conn['destination'];
	}
	return ! empty( $conn['direction'] ) ? (string) $conn['direction'] : '—';
}
