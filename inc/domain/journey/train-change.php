<?php
/**
 * Timetable train-change rows (e.g. ångtåg 71 → dieseltåg 61 at Marielund).
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
 * Train-change mappings keyed by transfer station title (PDF tidtabellsöversikt).
 *
 * @return array<string, array<string, array<string, string>>>
 */
function MRT_journey_train_change_by_station(): array {
	/** @var array<string, array<string, array<string, string>>> $map */
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
