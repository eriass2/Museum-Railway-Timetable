<?php
/**
 * CSV package schema (format version 1).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @return string */
function MRT_csv_format_version(): string {
	return '1';
}

/**
 * Entity keys allowed in manifest.includes.
 *
 * @return array<int, string>
 */
function MRT_csv_entity_types(): array {
	return array(
		'stations',
		'train_types',
		'routes',
		'timetables',
		'services',
		'stoptimes',
		'settings',
		'prices',
	);
}

/**
 * CSV filename per entity type.
 *
 * @return array<string, string>
 */
function MRT_csv_entity_files(): array {
	return array(
		'stations'            => 'stations.csv',
		'train_types'         => 'train_types.csv',
		'routes'              => 'routes.csv',
		'route_stations'      => 'route_stations.csv',
		'timetables'          => 'timetables.csv',
		'timetable_dates'     => 'timetable_dates.csv',
		'services'            => 'services.csv',
		'service_train_types' => 'service_train_types.csv',
		'stoptimes'           => 'stoptimes.csv',
		'settings'            => 'settings.csv',
		'prices'              => 'prices.csv',
	);
}

/**
 * Required columns per CSV file (empty = file optional).
 *
 * @return array<string, array<int, string>>
 */
function MRT_csv_required_columns(): array {
	return array(
		'stations.csv'            => array( 'name' ),
		'train_types.csv'           => array( 'slug', 'name' ),
		'routes.csv'                => array( 'title' ),
		'route_stations.csv'        => array( 'route_code', 'sequence', 'station_code' ),
		'timetables.csv'            => array( 'title' ),
		'timetable_dates.csv'       => array( 'timetable_code', 'date' ),
		'services.csv'              => array( 'timetable_code', 'route_code', 'end_station_code' ),
		'service_train_types.csv'   => array( 'service_code', 'train_type_slug' ),
		'stoptimes.csv'             => array(
			'service_code',
			'sequence',
			'station_code',
			'pickup_allowed',
			'dropoff_allowed',
		),
		'settings.csv'              => array( 'key', 'value' ),
		'prices.csv'                => array( 'ticket_type', 'category', 'zone' ),
	);
}

/**
 * Post meta keys for stable CSV codes.
 *
 * @return array<string, string>
 */
function MRT_csv_code_meta_keys(): array {
	return array(
		'stations'   => 'mrt_station_code',
		'routes'     => 'mrt_route_code',
		'timetables' => 'mrt_timetable_code',
		'services'   => 'mrt_service_code',
	);
}
