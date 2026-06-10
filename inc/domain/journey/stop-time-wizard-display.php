<?php
/**
 * Wizard timeline labels for stop times (Ca / X / behovsuppehåll).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public display meta for one stop in journey detail (A3/J4).
 *
 * @param array<string, mixed> $row DB stop row or mapped subset.
 * @param string               $time_preference `departure` (default) or `arrival` for timeline label.
 * @param bool                 $is_first_in_leg First stop in the journey slice (hide P footnote / pickup-only).
 * @param bool                 $is_last_in_leg  Last stop in the journey slice (trim redundant P at start; A behov kept at end).
 * @return array{
 *   time_label: string,
 *   approximate_time: bool,
 *   on_request_pickup: bool,
 *   on_request_dropoff: bool,
 *   on_request_both: bool
 * }
 */
function MRT_journey_stop_wizard_time_meta(
	array $row,
	string $time_preference = 'departure',
	bool $is_first_in_leg = false,
	bool $is_last_in_leg = false
): array {
	require_once MRT_PATH . 'inc/domain/service/stop-time-display.php';
	$pickup    = ! empty( $row['pickup_allowed'] );
	$dropoff   = ! empty( $row['dropoff_allowed'] );
	$arrival   = isset( $row['arrival_time'] ) && $row['arrival_time'] !== '' && $row['arrival_time'] !== null
		? (string) $row['arrival_time']
		: '';
	$departure = isset( $row['departure_time'] ) && $row['departure_time'] !== '' && $row['departure_time'] !== null
		? (string) $row['departure_time']
		: '';
	$time_raw  = $time_preference === 'arrival'
		? ( $arrival !== '' ? $arrival : $departure )
		: ( $departure !== '' ? $departure : $arrival );
	$has_time  = $time_raw !== '';
	$approximate = ! empty( $row['approximate_time'] );

	$restrictions       = MRT_stop_time_restriction_footnote_flags(
		$pickup,
		$dropoff,
		$has_time,
		$is_first_in_leg,
		$is_last_in_leg
	);
	$on_request_pickup  = $restrictions['pickup_restriction'];
	$on_request_dropoff = $restrictions['dropoff_restriction'];
	$on_request_both    = $restrictions['on_request_both'];

	$time_label = '—';
	if ( $on_request_both ) {
		$time_label = 'X';
	} elseif ( $has_time ) {
		$formatted  = MRT_format_time_display( $time_raw );
		$time_label = $approximate ? 'Ca ' . $formatted : $formatted;
	}

	return array(
		'time_label'          => $time_label,
		'approximate_time'    => $approximate,
		'on_request_pickup'   => $on_request_pickup,
		'on_request_dropoff'  => $on_request_dropoff,
		'on_request_both'     => $on_request_both,
	);
}
