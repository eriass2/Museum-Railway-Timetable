<?php
/**
 * Hardcoded journey wizard presets for development UI pages.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve Lennakatten demo station pair (after import).
 *
 * @return array{from: int, to: int, from_title: string, to_title: string}
 */
function MRT_debug_lennakatten_station_pair(): array {
	$pair = array(
		'from'        => 0,
		'to'          => 0,
		'from_title'  => 'Uppsala Östra',
		'to_title'    => 'Faringe',
	);
	foreach ( MRT_get_all_stations() as $station_id ) {
		$title = get_the_title( (int) $station_id );
		if ( $title === $pair['from_title'] ) {
			$pair['from'] = (int) $station_id;
		}
		if ( $title === $pair['to_title'] ) {
			$pair['to'] = (int) $station_id;
		}
	}
	return $pair;
}

/**
 * Example traffic date from Lennakatten import.
 */
function MRT_debug_lennakatten_sample_date(): string {
	$dates = function_exists( 'MRT_import_get_timetable_dates' ) ? MRT_import_get_timetable_dates() : array();
	return ! empty( $dates[0] ) ? (string) $dates[0] : '2026-05-30';
}

/**
 * @return array<string, mixed>
 */
function MRT_debug_sample_direct_connection(): array {
	return array(
		'service_id'          => 90001,
		'service_name'        => 'Tåg 101',
		'train_type'          => 'Ångtåg',
		'train_type_slug'     => 'angtag',
		'train_type_icon'     => 'steam',
		'connection_type'     => 'direct',
		'from_departure'      => '10:15',
		'to_arrival'          => '11:42',
		'duration_minutes'    => 87,
		'notice'              => '',
		'from_station_id'     => 0,
		'to_station_id'       => 0,
		'legs'                => array(
			array(
				'service_id'       => 90001,
				'service_name'     => 'Tåg 101',
				'train_type'       => 'Ångtåg',
				'train_type_slug'  => 'angtag',
				'train_type_icon'  => 'steam',
				'from_departure'   => '10:15',
				'to_arrival'       => '11:42',
				'destination'      => 'Faringe',
			),
		),
	);
}

/**
 * @return array<string, mixed>
 */
function MRT_debug_sample_transfer_connection(): array {
	return array(
		'service_id'          => 0,
		'service_name'        => __( 'Transfer via Selknä', 'museum-railway-timetable' ),
		'train_type'          => '',
		'connection_type'     => 'transfer',
		'from_departure'      => '09:40',
		'to_arrival'          => '12:05',
		'duration_minutes'    => 145,
		'notice'              => '',
		'legs'                => array(
			array(
				'service_id'      => 90002,
				'service_name'    => 'Tåg 201',
				'train_type'      => 'Rälsbuss',
				'train_type_slug' => 'ralsbuss',
				'train_type_icon' => 'railbus',
				'from_departure'  => '09:40',
				'to_arrival'      => '10:22',
			),
			array(
				'service_id'      => 90003,
				'service_name'    => 'Tåg 305',
				'train_type'      => 'Dieseltåg',
				'train_type_slug' => 'dieseltag',
				'train_type_icon' => 'diesel',
				'from_departure'  => '10:48',
				'to_arrival'      => '12:05',
			),
		),
	);
}

/**
 * Presets keyed by debug shortcode attribute (development only).
 *
 * @return array<string, array<string, mixed>>
 */
function MRT_journey_wizard_debug_presets(): array {
	$pair  = MRT_debug_lennakatten_station_pair();
	$date  = MRT_debug_lennakatten_sample_date();
	$direct = MRT_debug_sample_direct_connection();
	$xfer   = MRT_debug_sample_transfer_connection();
	$return = MRT_debug_sample_direct_connection();
	$return['from_departure'] = '14:10';
	$return['to_arrival']     = '15:35';
	$return['duration_minutes'] = 85;

	return array(
		'date'     => array(
			'step'           => 'date',
			'tripType'       => 'single',
			'from'           => $pair['from'],
			'to'             => $pair['to'],
			'fromTitle'      => $pair['from_title'],
			'toTitle'        => $pair['to_title'],
			'date'           => '',
			'calendarDays'   => array( $date => 'ok' ),
			'calendarYear'   => (int) gmdate( 'Y', strtotime( $date . ' UTC' ) ),
			'calendarMonth'  => (int) gmdate( 'n', strtotime( $date . ' UTC' ) ),
		),
		'outbound' => array(
			'step'                 => 'outbound',
			'tripType'             => 'single',
			'from'                 => $pair['from'],
			'to'                   => $pair['to'],
			'fromTitle'            => $pair['from_title'],
			'toTitle'              => $pair['to_title'],
			'date'                 => $date,
			'outboundConnections'  => array( $direct, $xfer ),
		),
		'return'   => array(
			'step'                 => 'return',
			'tripType'             => 'return',
			'from'                 => $pair['from'],
			'to'                   => $pair['to'],
			'fromTitle'            => $pair['from_title'],
			'toTitle'              => $pair['to_title'],
			'date'                 => $date,
			'outbound'             => $direct,
			'returnConnections'    => array( $return, $xfer ),
		),
		'summary'  => array(
			'step'      => 'summary',
			'tripType'  => 'return',
			'from'      => $pair['from'],
			'to'        => $pair['to'],
			'fromTitle' => $pair['from_title'],
			'toTitle'   => $pair['to_title'],
			'date'      => $date,
			'outbound'  => $direct,
			'inbound'   => $return,
		),
	);
}
