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
	return '2';
}

/**
 * CSV filename per entity type.
 *
 * @return array<string, string>
 */
function MRT_csv_entity_files(): array {
	return array(
		'stations'            => 'stations.csv',
		'station_train_changes' => 'station_train_changes.csv',
		'train_types'         => 'train_types.csv',
		'lines'               => 'lines.csv',
		'line_stations'       => 'line_stations.csv',
		'branch_junctions'    => 'branch_junctions.csv',
		'routes'              => 'routes.csv',
		'route_stations'      => 'route_stations.csv',
		'timetables'          => 'timetables.csv',
		'timetable_dates'     => 'timetable_dates.csv',
		'services'            => 'services.csv',
		'service_train_types' => 'service_train_types.csv',
		'stoptimes'           => 'stoptimes.csv',
		'settings'            => 'settings.csv',
		'ticket_copy_notes'   => 'ticket_copy_notes.csv',
		'brand_tokens'        => 'brand_tokens.csv',
		'prices'              => 'prices.csv',
		'price_schema'        => 'price_schema.csv',
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
		'station_train_changes.csv' => array( 'station_code', 'from_service', 'type_name', 'to_service' ),
		'train_types.csv'           => array( 'slug', 'name' ),
		'lines.csv'                 => array( 'line_code', 'title', 'kind' ),
		'line_stations.csv'         => array( 'line_code', 'sequence', 'station_code' ),
		'branch_junctions.csv'      => array( 'line_code', 'junction_station_code', 'requires_transfer' ),
		'routes.csv'                => array( 'title' ),
		'route_stations.csv'        => array( 'route_code', 'sequence', 'station_code' ),
		'timetables.csv'            => array( 'title' ),
		'timetable_dates.csv'       => array( 'timetable_code', 'date' ),
		'services.csv'              => array( 'timetable_code', 'end_station_code' ),
		'service_train_types.csv'   => array( 'service_code', 'train_type_slug' ),
		'stoptimes.csv'             => array(
			'service_code',
			'sequence',
			'station_code',
			'ank_pickup_mode',
			'ank_dropoff_mode',
			'avg_pickup_mode',
			'avg_dropoff_mode',
		),
		'settings.csv'              => array( 'key', 'value' ),
		'ticket_copy_notes.csv'     => array( 'id', 'condition', 'text' ),
		'brand_tokens.csv'          => array( 'token', 'value' ),
		'prices.csv'                => array( 'ticket_type', 'category', 'zone' ),
		'price_schema.csv'          => array( 'kind', 'key' ),
	);
}

/**
 * Full CSV column headers for export (matches Lennakatten fixture layout).
 *
 * @return array<string, array<int, string>>
 */
function MRT_csv_export_column_headers(): array {
	return array(
		'stations.csv'            => array(
			'station_code',
			'name',
			'station_type',
			'display_order',
			'bus_stop_marker',
			'lat',
			'lng',
			'price_zones',
			'ticket_purchase_info',
		),
		'station_train_changes.csv' => array(
			'station_code',
			'from_service',
			'type_name',
			'to_service',
		),
		'train_types.csv'         => array( 'slug', 'name', 'icon_file' ),
		'lines.csv'               => array( 'line_code', 'title', 'kind', 'overview_corridor_after_station' ),
		'line_stations.csv'       => array( 'line_code', 'sequence', 'station_code' ),
		'branch_junctions.csv'    => array( 'line_code', 'junction_station_code', 'requires_transfer' ),
		'routes.csv'              => array( 'route_code', 'title', 'start_station_code', 'end_station_code', 'branch_code' ),
		'route_stations.csv'      => array( 'route_code', 'sequence', 'station_code' ),
		'timetables.csv'          => array( 'timetable_code', 'title', 'colour_type' ),
		'timetable_dates.csv'     => array( 'timetable_code', 'date' ),
		'services.csv'            => array(
			'service_code',
			'timetable_code',
			'route_code',
			'line_code',
			'service_number',
			'end_station_code',
			'title',
			'highlight_label',
			'highlight_color',
			'highlight_note',
			'overview_column',
			'toward_station_code',
		),
		'service_train_types.csv' => array( 'service_code', 'train_type_slug' ),
		'stoptimes.csv'           => array(
			'service_code',
			'sequence',
			'station_code',
			'arrival_time',
			'departure_time',
			'ank_pickup_mode',
			'ank_dropoff_mode',
			'avg_pickup_mode',
			'avg_dropoff_mode',
			'approximate_time',
			'in_service_timetable',
		),
		'settings.csv'            => array( 'key', 'value' ),
		'ticket_copy_notes.csv'   => array( 'id', 'condition', 'text', 'enabled' ),
		'brand_tokens.csv'        => array( 'token', 'value' ),
		'prices.csv'              => array( 'ticket_type', 'category', 'zone', 'amount_sek' ),
		'price_schema.csv'        => array( 'kind', 'key', 'label', 'value' ),
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
