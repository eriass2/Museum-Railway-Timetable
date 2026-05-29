<?php
/**
 * Map PDF stop symbols to pickup/dropoff flags.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array{pickup_allowed: int, dropoff_allowed: int}
 */
function MRT_csv_symbol_to_flags( string $symbol ): array {
	$pu = ( $symbol === 'P' || $symbol === 'X' || $symbol === '' ) ? 1 : 0;
	$do = ( $symbol === 'X' || $symbol === '' ) ? 1 : 0;
	if ( $symbol === 'P' ) {
		$do = 0;
	}
	return array(
		'pickup_allowed'  => $pu,
		'dropoff_allowed' => $do,
	);
}

/**
 * Format hour/minute tuple as HH:MM or empty.
 *
 * @param array<int, int>|null $tuple
 */
function MRT_csv_format_time_tuple( ?array $tuple ): string {
	if ( $tuple === null || $tuple === array() ) {
		return '';
	}
	if ( count( $tuple ) >= 4 ) {
		return sprintf( '%02d:%02d', $tuple[0], $tuple[1] );
	}
	if ( count( $tuple ) >= 2 ) {
		return sprintf( '%02d:%02d', $tuple[0], $tuple[1] );
	}
	return '';
}

/**
 * Split time tuple into arrival and departure columns.
 *
 * @param array<int, int>|null $tuple
 * @return array{arrival: string, departure: string}
 */
function MRT_csv_split_time_tuple( ?array $tuple ): array {
	if ( $tuple === null || $tuple === array() ) {
		return array( 'arrival' => '', 'departure' => '' );
	}
	if ( count( $tuple ) >= 4 ) {
		return array(
			'arrival'   => sprintf( '%02d:%02d', $tuple[0], $tuple[1] ),
			'departure' => sprintf( '%02d:%02d', $tuple[2], $tuple[3] ),
		);
	}
	$t = sprintf( '%02d:%02d', $tuple[0], $tuple[1] );
	return array( 'arrival' => $t, 'departure' => $t );
}
