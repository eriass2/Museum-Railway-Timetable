<?php
/**
 * Lennakatten import – static data (stations, routes, services)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get stations data for import [name, display_order, bus_suffix?]
 *
 * @return array
 */
function MRT_import_get_stations_data() {
	return array(
		array( 'Uppsala Östra', 1 ),
		array( 'Fyrislund', 2 ),
		array( 'Årsta', 3 ),
		array( 'Skölsta', 4 ),
		array( 'Bärby', 5 ),
		array( 'Gunsta', 6 ),
		array( 'Marielund', 7 ),
		array( 'Lövstahagen', 8 ),
		array( 'Selknä', 9, true ),
		array( 'Löt', 10 ),
		array( 'Länna', 11 ),
		array( 'Almunge', 12 ),
		array( 'Moga', 13 ),
		array( 'Faringe', 14 ),
		array( 'Fjällnora', 15, true ),
		array( 'Linnés Hammarby', 16, true ),
	);
}

/**
 * Get train types for import [name => slug]
 *
 * @return array
 */
function MRT_import_get_train_types() {
	return array(
		'Ångtåg'     => 'angtag',
		'Rälsbuss'   => 'ralsbuss',
		'Dieseltåg'  => 'dieseltag',
		'Buss'       => 'buss',
		'Ång/diesel' => 'ang-diesel',
	);
}

/**
 * Get timetable dates for GRÖN
 *
 * @return array
 */
function MRT_import_get_timetable_dates() {
	return MRT_import_get_green_timetable_dates();
}

/**
 * Get timetable dates for GRÖN (lördagar).
 *
 * @return array<int, string>
 */
function MRT_import_get_green_timetable_dates(): array {
	$dates = array( '2026-05-30', '2026-05-31', '2026-06-06', '2026-06-13', '2026-06-20', '2026-07-04', '2026-07-11', '2026-07-18', '2026-08-01', '2026-08-08', '2026-08-15', '2026-09-05', '2026-09-12', '2026-09-19', '2026-09-26' );
	sort( $dates );
	return $dates;
}

/**
 * Get timetable dates for GUL (fredagar, lågtrafik).
 *
 * Based on traffic-day codes C and D in Tidtabellsboken del B.
 *
 * @return array<int, string>
 */
function MRT_import_get_yellow_timetable_dates(): array {
	$dates = array(
		'2026-05-29',
		'2026-06-05',
		'2026-06-12',
		'2026-06-26',
		'2026-07-03',
		'2026-07-10',
		'2026-07-17',
		'2026-07-24',
		'2026-07-31',
		'2026-08-07',
		'2026-08-14',
		'2026-08-21',
		'2026-08-28',
		'2026-09-04',
		'2026-09-11',
		'2026-09-18',
		'2026-09-25',
	);
	sort( $dates );
	return $dates;
}

/**
 * Get services Uppsala → Faringe [num, train_type, times, symbols]
 *
 * @return array
 */
function MRT_import_get_services_out() {
	return array(
		array( '71', 'Ångtåg', array( array( 10, 0 ), array( 10, 3 ), array( 10, 5 ), array( 10, 9 ), array( 10, 23 ), array( 10, 24 ), array( 10, 35, 10, 45 ), array( 10, 46 ), array( 10, 50 ), array( 10, 54 ), array( 10, 57 ), array( 11, 10 ), array( 11, 14 ), array( 11, 25 ) ), array( 'P', 'P', 'X', '', 'X', '', '', 'P', '', 'X', '', '', 'X', '' ) ),
		array( '93', 'Rälsbuss', array( array( 11, 10 ), array( 11, 13 ), array( 11, 15 ), array( 11, 18 ), array( 11, 28 ), array( 11, 29 ), array( 11, 37 ), array( 11, 42 ), array( 11, 43 ), array( 11, 47 ), array( 11, 50 ), array( 11, 54 ), array( 12, 4 ), array( 12, 7 ), array( 12, 17 ) ), array( 'P', 'P', 'X', '', 'X', '', '', 'X', '', '', 'X', '', '', 'X', '' ) ),
		array( '75', 'Ångtåg', array( array( 12, 38 ), array( 12, 41 ), array( 12, 43 ), array( 12, 47 ), array( 13, 0 ), array( 13, 1 ), array( 13, 10, 13, 32 ), array( 13, 33 ), array( 13, 37 ), array( 13, 41 ), array( 13, 47 ), array( 14, 0 ), array( 14, 4 ), array( 14, 15 ) ), array( 'P', 'P', 'X', '', 'X', '', '', 'X', '', '', 'X', '', '', 'X', '' ) ),
		array( '63', 'Dieseltåg', array( array( 14, 10 ), array( 14, 13 ), array( 14, 15 ), array( 14, 19 ), array( 14, 30 ), array( 14, 31 ), array( 14, 40, 15, 10 ), array( 15, 11 ), array( 15, 15 ), array( 15, 18 ), array( 15, 21 ), array( 15, 31 ), array( 15, 34 ), array( 15, 43 ) ), array( 'P', 'P', 'X', '', 'X', '', '', 'X', '', '', 'X', '', '', 'X', '' ) ),
		array( '65', 'Dieseltåg', array( array( 15, 55 ), array( 15, 58 ), array( 16, 0 ), array( 16, 4 ), array( 16, 13 ), array( 16, 14 ), array( 16, 23, 17, 0 ), array( 17, 1 ), array( 17, 4 ), array( 17, 8 ), array( 17, 11 ), array( 17, 22 ), array( 17, 26 ), array( 17, 37 ) ), array( 'P', 'P', 'X', '', 'X', '', '', 'X', '', '', 'X', '', '', 'X', '' ) ),
		array( '79', 'Ång/diesel', array( array( 18, 7 ), array( 18, 10 ), array( 18, 12 ), array( 18, 16 ), array( 18, 25 ), array( 18, 26 ), array( 18, 35, 18, 50 ), array( 18, 51 ), array( 18, 54 ), array( 18, 57 ), array( 19, 1 ), array( 19, 12 ), array( 19, 16 ), array( 19, 27 ) ), array( 'X', 'X', 'X', '', 'X', '', '', 'X', '', '', 'X', '', '', 'X', '' ) ),
	);
}

/**
 * Get services Faringe → Uppsala
 *
 * @return array
 */
function MRT_import_get_services_in() {
	return array(
		array( '70', 'Ångtåg', array( array( 7, 55 ), array( 8, 2 ), array( 8, 14 ), array( 8, 25 ), array( 8, 27 ), array( 8, 31 ), array( 8, 34 ), array( 8, 38, 8, 53 ), array( 8, 58 ), array( 9, 1 ), array( 9, 8 ), array( 9, 12 ), array( 9, 14 ), array( 9, 23 ) ), array( 'X', 'X', '', 'X', 'X', 'X', 'X', '', 'X', '', 'X', 'X', 'X', '' ) ),
		array( '60', 'Dieseltåg', array( array( 9, 40 ), array( 9, 47 ), array( 9, 57 ), array( 10, 8 ), array( 10, 10 ), array( 10, 14 ), array( 10, 17 ), array( 10, 20, 11, 45 ), array( 11, 50 ), array( 11, 53 ), array( 12, 0 ), array( 12, 4 ), array( 12, 6 ), array( 12, 17 ) ), array( 'X', 'X', '', 'X', 'X', 'X', 'X', '', 'X', '', 'X', 'X', 'X', '' ) ),
		array( '62', 'Dieseltåg', array( array( 12, 27 ), array( 12, 34 ), array( 12, 41 ), array( 12, 54 ), array( 12, 56 ), array( 13, 1 ), array( 13, 4 ), array( 13, 7, 13, 15 ), array( 13, 20 ), array( 13, 23 ), array( 13, 30 ), array( 13, 34 ), array( 13, 36 ), array( 13, 47 ) ), array( 'X', 'X', '', '', '', 'X', 'X', '', 'X', '', '', 'X', 'X', '' ) ),
		array( '96', 'Rälsbuss', array( array( 14, 25 ), array( 14, 31 ), array( 14, 36 ), array( 14, 46 ), array( 14, 47 ), array( 14, 52 ), array( 14, 55 ), array( 14, 58, 15, 5 ), array( 15, 10 ), array( 15, 13 ), array( 15, 20 ), array( 15, 24 ), array( 15, 26 ), array( 15, 37 ) ), array( 'X', 'X', '', 'X', 'X', 'X', 'X', '', 'X', '', '', '', 'X', '' ) ),
		array( '78', 'Ång/diesel', array( array( 16, 13 ), array( 16, 20 ), array( 16, 28 ), array( 16, 41 ), array( 16, 43 ), array( 16, 48 ), array( 16, 51 ), array( 16, 55, 17, 15 ), array( 17, 20 ), array( 17, 23 ), array( 17, 30 ), array( 17, 34 ), array( 17, 36 ), array( 17, 47 ) ), array( 'X', 'X', '', 'X', 'X', 'X', 'X', '', 'X', '', '', '', 'X', '' ) ),
	);
}

/**
 * Get GUL Friday services Uppsala → Faringe.
 *
 * @return array<int, array<int, mixed>>
 */
function MRT_import_get_yellow_services_out(): array {
	return array(
		array( '101', 'Rälsbuss', array( array( 16, 45 ), array( 16, 48 ), array( 16, 50 ), array( 16, 53 ), array( 17, 3 ), array( 17, 4 ), array( 17, 10 ), array( 17, 11 ), array( 17, 14 ), array( 17, 17 ), array( 17, 23 ), array( 17, 33 ), array( 17, 36 ), array( 17, 45 ) ), array( 'P', 'P', 'P', 'X', '', 'X', 'X', 'X', 'X', 'X', '', '', 'X', '' ) ),
		array( '103', 'Rälsbuss', array( array( 21, 35 ), array( 21, 38 ), array( 21, 40 ), array( 21, 43 ), array( 21, 50 ), array( 21, 51 ), array( 21, 58 ), array( 21, 59 ), array( 22, 2 ), array( 22, 5 ), array( 22, 8 ), array( 22, 18 ), array( 22, 21 ), array( 22, 32 ) ), array( 'P', 'P', 'P', 'X', '', 'X', 'X', 'X', 'X', 'X', 'X', '', 'X', '' ) ),
	);
}

/**
 * Get GUL Friday services Faringe → Uppsala.
 *
 * @return array<int, array<int, mixed>>
 */
function MRT_import_get_yellow_services_in(): array {
	return array(
		array( '100', 'Rälsbuss', array( array( 15, 30 ), array( 15, 36 ), array( 15, 42 ), array( 15, 52 ), array( 15, 53 ), array( 15, 57 ), array( 16, 0 ), array( 16, 3 ), array( 16, 8 ), array( 16, 10 ), array( 16, 16 ), array( 16, 19 ), array( 16, 21 ), array( 16, 30 ) ), array( 'X', 'X', 'X', 'X', 'X', 'X', 'X', 'X', 'X', '', 'X', 'X', 'X', '' ) ),
		array( '102', 'Rälsbuss', array( array( 20, 10 ), array( 20, 16 ), array( 20, 22 ), array( 20, 37 ), array( 20, 38 ), array( 20, 42 ), array( 20, 45 ), array( 20, 47 ), array( 20, 51 ), array( 20, 54 ), array( 21, 0 ), array( 21, 3 ), array( 21, 5 ), array( 21, 15 ) ), array( 'X', 'X', 'X', '', 'X', 'X', 'X', 'X', 'X', '', 'X', 'X', 'X', '' ) ),
	);
}

/**
 * Get importable timetable definitions.
 *
 * New reference PDFs in the same shape should generally become another item here:
 * type, title, dates, outbound services, inbound services.
 *
 * @return array<string, array<string, mixed>>
 */
function MRT_import_get_timetable_definitions(): array {
	return array(
		'green'  => array(
			'title'        => 'GRÖN TIDTABELL 2026',
			'label'        => __( 'GRÖN', 'museum-railway-timetable' ),
			'dates'        => MRT_import_get_green_timetable_dates(),
			'services_out' => MRT_import_get_services_out(),
			'services_in'  => MRT_import_get_services_in(),
		),
		'yellow' => array(
			'title'        => 'GUL TIDTABELL 2026',
			'label'        => __( 'GUL', 'museum-railway-timetable' ),
			'dates'        => MRT_import_get_yellow_timetable_dates(),
			'services_out' => MRT_import_get_yellow_services_out(),
			'services_in'  => MRT_import_get_yellow_services_in(),
		),
	);
}
