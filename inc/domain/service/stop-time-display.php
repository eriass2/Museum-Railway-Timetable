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
 * P/A footnote flags for wizard timeline (endpoint-aware, behovsuppehåll preserved at destination).
 *
 * Hides redundant pickup-only (P) at the boarding stop, but keeps dropoff-only (A) at the
 * alighting stop — e.g. Selknä on rälsbuss where passengers must contact staff to alight.
 *
 * @return array{pickup_restriction: bool, dropoff_restriction: bool, on_request_both: bool}
 */
function MRT_stop_time_restriction_footnote_flags(
	bool $pickup,
	bool $dropoff,
	bool $has_time,
	bool $is_first_in_leg = false,
	bool $is_last_in_leg = false
): array {
	$both_no_time = $pickup && $dropoff && ! $has_time;
	$pickup_only  = $pickup && ! $dropoff;
	$dropoff_only = ! $pickup && $dropoff;

	if ( $both_no_time ) {
		return array(
			'pickup_restriction'  => ! $is_first_in_leg,
			'dropoff_restriction' => ! $is_last_in_leg,
			'on_request_both'     => true,
		);
	}

	return array(
		'pickup_restriction'  => $pickup_only && ! $is_first_in_leg,
		'dropoff_restriction' => $dropoff_only,
		'on_request_both'     => false,
	);
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
 * @param string                    $row_kind  Overview row kind: `from` hides P, `to` hides A, `departure`/`arrival` keep mid-trip symbols.
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
		// P/A before Ca; Ca immediately before the time digits.
		return $symbol_prefix . 'Ca ' . $time_str;
	}

	return $symbol_prefix . $time_str;
}
