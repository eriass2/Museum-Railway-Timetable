<?php
/**
 * Journey normalize: labels
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_normalize_total_duration_from_legs( array $legs ) {
	if ( $legs === array() ) {
		return 0;
	}
	$first = $legs[0];
	$last  = $legs[ count( $legs ) - 1 ];
	$dep   = (string) ( $first['from_departure'] ?? '' );
	$arr   = (string) ( $last['to_arrival'] ?? '' );
	return MRT_format_duration_minutes( $dep, $arr );
}

function MRT_journey_multi_leg_train_type_label( array $legs ) {
	$tts = array();
	foreach ( $legs as $leg ) {
		$t = (string) ( $leg['train_type'] ?? '' );
		if ( $t !== '' ) {
			$tts[] = $t;
		}
	}
	$tts = array_values( array_unique( $tts ) );
	if ( count( $tts ) <= 1 ) {
		return (string) ( $tts[0] ?? '' );
	}
	return implode( ' / ', $tts );
}

function MRT_journey_multi_leg_service_label( array $item, array $legs ) {
	$titles = array();
	foreach ( $legs as $leg ) {
		$sid = (int) ( $leg['service_id'] ?? 0 );
		if ( $sid > 0 ) {
			$titles[] = get_the_title( $sid ) ?: ( '#' . $sid );
		}
	}
	if ( count( $legs ) === 2 ) {
		$transfer_id = (int) ( $item['transfer_station_id'] ?? 0 );
		$hub         = $transfer_id > 0 ? get_the_title( $transfer_id ) : '';
		if ( $hub !== '' && isset( $titles[0], $titles[1] ) ) {
			return sprintf(
				/* translators: 1: first service name, 2: transfer station name, 3: second service name */
				__( '%1$s · Change at %2$s · %3$s', 'museum-railway-timetable' ),
				$titles[0],
				$hub,
				$titles[1]
			);
		}
	}
	if ( count( $legs ) > 2 ) {
		$parts     = array();
		$leg_count = count( $legs );
		for ( $i = 0; $i < $leg_count; $i++ ) {
			if ( $i > 0 ) {
				$hub_id = (int) ( $legs[ $i - 1 ]['to_station_id'] ?? 0 );
				$hub    = $hub_id > 0 ? get_the_title( $hub_id ) : '';
				if ( $hub !== '' ) {
					$parts[] = sprintf(
						/* translators: %s: transfer station name */
						__( 'Change at %s', 'museum-railway-timetable' ),
						$hub
					);
				}
			}
			if ( isset( $titles[ $i ] ) ) {
				$parts[] = $titles[ $i ];
			}
		}
		return implode( ' · ', $parts );
	}
	return implode( ' · ', $titles );
}
