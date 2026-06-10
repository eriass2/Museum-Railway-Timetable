<?php
/**
 * Stop time display symbols (P, A, Ca, X, |) for timetable overview and grid.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * P/A prefix when pickup or dropoff is restricted.
 */
function MRT_stop_time_restriction_prefix(
	array $stop_time,
	bool $hide_pickup_only = false,
	bool $hide_dropoff_only = false
): string {
	$pickup  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff = ! empty( $stop_time['dropoff_allowed'] );

	if ( $pickup && ! $dropoff && ! $hide_pickup_only ) {
		return 'P ';
	}
	if ( ! $pickup && $dropoff && ! $hide_dropoff_only ) {
		return 'A ';
	}
	return '';
}

/**
 * Clock time, X (on request), or empty when no time is shown.
 */
function MRT_stop_time_clock_fragment( array $stop_time ): string {
	$arrival   = (string) ( $stop_time['arrival_time'] ?? '' );
	$departure = (string) ( $stop_time['departure_time'] ?? '' );
	$pickup    = ! empty( $stop_time['pickup_allowed'] );
	$dropoff   = ! empty( $stop_time['dropoff_allowed'] );

	if ( $departure !== '' ) {
		return MRT_format_time_display( $departure );
	}
	if ( $arrival !== '' ) {
		return MRT_format_time_display( $arrival );
	}
	if ( $pickup && $dropoff ) {
		return 'X';
	}
	return '';
}

/**
 * @return array{0: string, 1: string} [restriction_prefix, time_fragment]
 */
function MRT_stop_time_prefix_and_time_parts(
	array $stop_time,
	bool $hide_pickup_only_symbol = false,
	bool $hide_dropoff_only_symbol = false
): array {
	$time_str = MRT_stop_time_clock_fragment( $stop_time );
	if ( $time_str === 'X' ) {
		return array( '', 'X' );
	}

	return array(
		MRT_stop_time_restriction_prefix( $stop_time, $hide_pickup_only_symbol, $hide_dropoff_only_symbol ),
		$time_str,
	);
}

/**
 * Format one stop time for timetable cells (P/A, Ca, X, |, —).
 *
 * @param array<string, mixed>|null $stop_time Stop row with arrival, departure, pickup/dropoff, approximate_time.
 * @param string                    $row_kind  Overview row kind (`from` hides P, `to` hides A).
 * @return string Formatted display (e.g. "P Ca 10.13", "X", "|", "—").
 */
function MRT_format_stop_time_display( ?array $stop_time, string $row_kind = '' ): string {
	if ( ! $stop_time ) {
		return '—';
	}

	$pickup_allowed  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff_allowed = ! empty( $stop_time['dropoff_allowed'] );
	if ( ! $pickup_allowed && ! $dropoff_allowed ) {
		return '|';
	}

	[$symbol_prefix, $time_str] = MRT_stop_time_prefix_and_time_parts(
		$stop_time,
		$row_kind === 'from',
		$row_kind === 'to'
	);

	if ( ! empty( $stop_time['approximate_time'] ) && $time_str !== '' && $time_str !== 'X' ) {
		return $symbol_prefix . 'Ca ' . $time_str;
	}

	return $symbol_prefix . $time_str;
}
