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

require_once MRT_PATH . 'inc/infrastructure/wordpress/helpers-utils.php';

/**
 * Public display meta for one stop in journey detail (A3/J4).
 *
 * @param array<string, mixed> $row DB stop row or mapped subset.
 * @param string               $time_preference `departure` (default) or `arrival` for timeline label.
 * @return array{
 *   time_label: string,
 *   approximate_time: bool,
 *   on_request_pickup: bool,
 *   on_request_dropoff: bool,
 *   on_request_both: bool
 * }
 */
function MRT_journey_stop_wizard_time_meta( array $row, string $time_preference = 'departure' ): array {
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

	$on_request_pickup  = $pickup && ! $dropoff;
	$on_request_dropoff = ! $pickup && $dropoff;
	$on_request_both    = $pickup && $dropoff && ! $has_time;

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
