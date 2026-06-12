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

require_once MRT_PATH . 'inc/domain/service/stop-time-modes.php';
require_once MRT_PATH . 'inc/domain/service/stop-time-display.php';

/**
 * Public display meta for one stop in journey detail (A3/J4).
 *
 * @param array<string, mixed> $row DB stop row or mapped subset.
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
	$row       = MRT_stop_time_row_with_defaults( $row );
	$arrival   = isset( $row['arrival_time'] ) && $row['arrival_time'] !== ''
		? (string) $row['arrival_time']
		: '';
	$departure = isset( $row['departure_time'] ) && $row['departure_time'] !== ''
		? (string) $row['departure_time']
		: '';
	$time_raw  = $time_preference === 'arrival'
		? ( $arrival !== '' ? $arrival : $departure )
		: ( $departure !== '' ? $departure : $arrival );
	$has_time    = $time_raw !== '';
	$approximate = ! empty( $row['approximate_time'] );
	$pickup_or   = MRT_stop_time_on_request_pickup( $row );
	$dropoff_or  = MRT_stop_time_on_request_dropoff( $row );
	$show_ca     = $approximate && ( $pickup_or || $dropoff_or );

	$restrictions       = MRT_stop_time_restriction_footnote_flags(
		$pickup_or,
		$dropoff_or,
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
		$time_label = $show_ca ? 'Ca ' . $formatted : $formatted;
		if ( $pickup_or && $dropoff_or ) {
			$time_label .= ' X';
		}
	}

	return array(
		'time_label'          => $time_label,
		'approximate_time'    => $approximate,
		'on_request_pickup'   => $on_request_pickup,
		'on_request_dropoff'  => $on_request_dropoff,
		'on_request_both'     => $on_request_both,
	);
}
