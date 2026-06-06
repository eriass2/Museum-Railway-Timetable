<?php
/**
 * Timetable train-change rows (e.g. ångtåg 71 → dieseltåg 61 at a transfer station).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Passenger-facing "mot …" label for a leg alighting station.
 */
function MRT_journey_leg_destination_label( int $to_station_id ): string {
	if ( $to_station_id <= 0 ) {
		return '';
	}
	$title = get_the_title( $to_station_id );
	return is_string( $title ) ? $title : '';
}

/**
 * Meta key: per-station train-change map (service number → transfer vehicle).
 */
function MRT_station_train_change_map_meta_key(): string {
	return 'mrt_station_train_change_map';
}

/**
 * @return array{typeName: string, serviceNumber: string}
 */
function MRT_train_change_map_entry( string $type_name, string $service_number ): array {
	return array(
		'typeName'      => $type_name,
		'serviceNumber' => $service_number,
	);
}

/**
 * @param mixed $input Raw meta value.
 * @return array<string, array{typeName: string, serviceNumber: string}>
 */
function MRT_sanitize_station_train_change_map( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}
	$map = array();
	foreach ( $input as $service_number => $transfer ) {
		if ( ! is_array( $transfer ) ) {
			continue;
		}
		$key = sanitize_text_field( (string) $service_number );
		if ( $key === '' ) {
			continue;
		}
		$type_name = sanitize_text_field( (string) ( $transfer['typeName'] ?? '' ) );
		$number    = sanitize_text_field( (string) ( $transfer['serviceNumber'] ?? '' ) );
		if ( $type_name === '' || $number === '' ) {
			continue;
		}
		$map[ $key ] = array(
			'typeName'      => $type_name,
			'serviceNumber' => $number,
		);
	}
	return $map;
}

/**
 * Stored train-change map for one station (empty when meta not set).
 *
 * @return array<string, array{typeName: string, serviceNumber: string}>
 */
function MRT_get_station_train_change_map_stored( int $station_id ): array {
	if ( $station_id <= 0 ) {
		return array();
	}
	$stored = get_post_meta( $station_id, MRT_station_train_change_map_meta_key(), true );
	if ( ! is_array( $stored ) ) {
		return array();
	}
	return MRT_sanitize_station_train_change_map( $stored );
}

/**
 * Effective train-change map for one station (stored meta only).
 *
 * @return array<string, array{typeName: string, serviceNumber: string}>
 */
function MRT_get_station_train_change_map( int $station_id, ?WP_Post $station_post = null ): array {
	unset( $station_post );
	return MRT_get_station_train_change_map_stored( $station_id );
}

/**
 * Persist train-change map; empty map removes meta.
 *
 * @param array<string, array{typeName: string, serviceNumber: string}> $map
 */
function MRT_update_station_train_change_map_meta( int $station_id, array $map ): void {
	$clean = MRT_sanitize_station_train_change_map( $map );
	if ( $clean === array() ) {
		delete_post_meta( $station_id, MRT_station_train_change_map_meta_key() );
		return;
	}
	update_post_meta( $station_id, MRT_station_train_change_map_meta_key(), $clean );
}
