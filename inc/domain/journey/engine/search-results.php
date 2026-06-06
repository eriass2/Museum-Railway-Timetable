<?php
/**
 * Journey search engine: results
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_journey_engine_build_result( array $legs ): array {
	if ( count( $legs ) <= 1 ) {
		return array(
			'connection_type'     => 'direct',
			'transfer_station_id' => null,
			'legs'                => $legs,
		);
	}
	return array(
		'connection_type'     => 'transfer',
		'transfer_station_id' => (int) ( $legs[0]['to_station_id'] ?? 0 ),
		'legs'                => $legs,
	);
}

function MRT_journey_engine_dedupe_key( array $legs ): string {
	$parts = array();
	foreach ( $legs as $leg ) {
		$parts[] = (int) ( $leg['service_id'] ?? 0 ) . ':' . (string) ( $leg['from_departure'] ?? '' );
	}
	return implode( '|', $parts );
}

function MRT_journey_engine_compare_results( array $a, array $b ): int {
	$legs_a = $a['legs'] ?? array();
	$legs_b = $b['legs'] ?? array();
	$dep_a  = (string) ( $legs_a[0]['from_departure'] ?? '' );
	$dep_b  = (string) ( $legs_b[0]['from_departure'] ?? '' );
	if ( $dep_a !== $dep_b ) {
		return strcmp( $dep_a, $dep_b );
	}
	$count_cmp = count( $legs_a ) <=> count( $legs_b );
	if ( $count_cmp !== 0 ) {
		return $count_cmp;
	}
	$hub_a = (int) ( $a['transfer_station_id'] ?? 0 );
	$hub_b = (int) ( $b['transfer_station_id'] ?? 0 );
	if ( $hub_a > 0 && $hub_b > 0 ) {
		$prio = MRT_journey_transfer_station_priority( $hub_a ) <=> MRT_journey_transfer_station_priority( $hub_b );
		if ( $prio !== 0 ) {
			return $prio;
		}
	}
	return strcmp( MRT_journey_engine_dedupe_key( $legs_a ), MRT_journey_engine_dedupe_key( $legs_b ) );
}

function MRT_journey_engine_append_result( array $results, array $seen, array $result ): array {
	$legs = $result['legs'] ?? array();
	if ( ! is_array( $legs ) || $legs === array() ) {
		return array( $results, $seen );
	}
	$key = MRT_journey_engine_dedupe_key( $legs );
	if ( isset( $seen[ $key ] ) ) {
		return array( $results, $seen );
	}
	$seen[ $key ]       = true;
	$results[]          = $result;
	return array( $results, $seen );
}
