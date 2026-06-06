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
 * Bundled title defaults for Lennakatten (used when station meta is not set).
 *
 * @return array<string, array<string, array{typeName: string, serviceNumber: string}>>
 */
function MRT_default_train_change_maps_by_station_title(): array {
	/** @var array<string, array<string, array{typeName: string, serviceNumber: string}>> $map */
	$map = array(
		'Marielund' => array(
			'71' => array(
				'typeName'      => 'Dieseltåg',
				'serviceNumber' => '61',
			),
			'63' => array(
				'typeName'      => 'Rälsbuss',
				'serviceNumber' => '97',
			),
			'60' => array(
				'typeName'      => 'Ångtåg',
				'serviceNumber' => '74',
			),
			'96' => array(
				'typeName'      => 'Dieseltåg',
				'serviceNumber' => '64',
			),
		),
	);
	return $map;
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
 * Effective train-change map for one station (stored meta, else title defaults).
 *
 * @return array<string, array{typeName: string, serviceNumber: string}>
 */
function MRT_get_station_train_change_map( int $station_id, ?WP_Post $station_post = null ): array {
	if ( $station_id <= 0 ) {
		return array();
	}
	$stored = get_post_meta( $station_id, MRT_station_train_change_map_meta_key(), true );
	if ( is_array( $stored ) && $stored !== array() ) {
		return MRT_sanitize_station_train_change_map( $stored );
	}
	return MRT_train_change_map_for_station_title( MRT_station_title_for_lookup( $station_id, $station_post ) );
}

/**
 * Resolve station title for config lookup (post object or stored post).
 */
function MRT_station_title_for_lookup( int $station_id, ?WP_Post $station_post = null ): string {
	if ( $station_post instanceof WP_Post && (int) $station_post->ID === $station_id && $station_post->post_title !== '' ) {
		return $station_post->post_title;
	}
	return get_the_title( $station_id );
}

/**
 * @return array<string, array{typeName: string, serviceNumber: string}>
 */
function MRT_train_change_map_for_station_title( string $title ): array {
	if ( $title === '' ) {
		return array();
	}
	return MRT_default_train_change_maps_by_station_title()[ $title ] ?? array();
}

/**
 * Train-change mappings keyed by transfer station title (legacy accessor).
 *
 * @return array<string, array<string, array{typeName: string, serviceNumber: string}>>
 */
function MRT_journey_train_change_by_station(): array {
	return MRT_default_train_change_maps_by_station_title();
}
