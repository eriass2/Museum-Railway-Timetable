<?php
/**
 * Lennakatten reference data for dev import and tests (not runtime defaults).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/journey/train-change.php';

/**
 * Station price zones per Lennakatten taxa 2026 (see docs/PRICE_ZONES.md).
 *
 * @return array<string, int[]>
 */
function MRT_lennakatten_reference_station_price_zones_by_title(): array {
	return array(
		'Uppsala Östra'   => array( 1 ),
		'Fyrislund'       => array( 1 ),
		'Årsta'           => array( 1 ),
		'Skölsta'         => array( 1 ),
		'Bärby'           => array( 1 ),
		'Gunsta'          => array( 1, 2 ),
		'Marielund'       => array( 2 ),
		'Lövstahagen'     => array( 2 ),
		'Selknä'          => array( 2 ),
		'Löt'             => array( 2 ),
		'Länna'           => array( 2 ),
		'Fjällnora'       => array( 2 ),
		'Almunge'         => array( 2, 3 ),
		'Moga'            => array( 3 ),
		'Faringe'         => array( 3 ),
		'Linnés Hammarby' => array( 3 ),
	);
}

/**
 * Train-change rows at transfer stations (overview + journey hub hints).
 *
 * @return array<string, array<string, array{typeName: string, serviceNumber: string}>>
 */
function MRT_lennakatten_reference_train_change_maps_by_station_title(): array {
	/** @var list<array{station: string, from: string, type: string, to: string}> $rows */
	$rows = array(
		array(
			'station' => 'Marielund',
			'from'    => '71',
			'type'    => 'Dieseltåg',
			'to'      => '61',
		),
		array(
			'station' => 'Marielund',
			'from'    => '63',
			'type'    => 'Rälsbuss',
			'to'      => '97',
		),
		array(
			'station' => 'Marielund',
			'from'    => '60',
			'type'    => 'Ångtåg',
			'to'      => '74',
		),
		array(
			'station' => 'Marielund',
			'from'    => '96',
			'type'    => 'Dieseltåg',
			'to'      => '64',
		),
	);
	$map  = array();
	foreach ( $rows as $row ) {
		$map[ $row['station'] ][ $row['from'] ] = MRT_train_change_map_entry( $row['type'], $row['to'] );
	}

	return $map;
}

/**
 * Persist Lennakatten train-change maps onto imported stations (by title match).
 */
function MRT_lennakatten_seed_train_change_maps(): void {
	if ( ! function_exists( 'MRT_get_all_stations' ) ) {
		return;
	}
	$by_title = MRT_lennakatten_reference_train_change_maps_by_station_title();
	foreach ( MRT_get_all_stations() as $station_id ) {
		$id    = (int) $station_id;
		$title = get_the_title( $id );
		if ( $title === '' || ! isset( $by_title[ $title ] ) ) {
			continue;
		}
		MRT_update_station_train_change_map_meta( $id, $by_title[ $title ] );
	}
}
