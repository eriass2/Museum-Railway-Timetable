<?php
/**
 * Build Lennakatten CSV package rows from reference-data.php.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array{manifest: array<string, mixed>, tables: array<string, array<int, array<string, string>>>}
 */
function MRT_csv_build_lennakatten_package(): array {
	require_once MRT_PATH . 'inc/import/lennakatten/reference-data.php';
	$station_codes = MRT_csv_lennakatten_station_codes();
	$tables        = array(
		'stations.csv'            => MRT_csv_lennakatten_station_rows( $station_codes ),
		'train_types.csv'         => MRT_csv_lennakatten_train_type_rows(),
		'routes.csv'              => MRT_csv_lennakatten_route_rows( $station_codes ),
		'route_stations.csv'      => MRT_csv_lennakatten_route_station_rows( $station_codes ),
		'timetables.csv'          => MRT_csv_lennakatten_timetable_rows(),
		'timetable_dates.csv'     => MRT_csv_lennakatten_date_rows(),
		'services.csv'            => array(),
		'service_train_types.csv' => array(),
		'stoptimes.csv'           => array(),
	);
	MRT_csv_lennakatten_append_services( $tables, $station_codes );
	$manifest = array(
		'format_version' => MRT_csv_format_version(),
		'exported_at'    => gmdate( 'c' ),
		'plugin_version' => defined( 'MRT_VERSION' ) ? MRT_VERSION : '0.0.0',
		'locale'         => 'sv_SE',
		'includes'       => array(
			'stations',
			'train_types',
			'routes',
			'timetables',
			'services',
			'stoptimes',
		),
	);
	return array( 'manifest' => $manifest, 'tables' => $tables );
}

/**
 * @return array<string, string> name => code
 */
function MRT_csv_lennakatten_station_codes(): array {
	$codes = array();
	foreach ( MRT_import_get_stations_data() as $row ) {
		$name          = (string) $row[0];
		$codes[ $name ] = MRT_csv_slugify( $name );
	}
	return $codes;
}

/**
 * @param array<string, string> $station_codes
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_station_rows( array $station_codes ): array {
	$rows = array();
	foreach ( MRT_import_get_stations_data() as $row ) {
		$name = (string) $row[0];
		$rows[] = array(
			'station_code'    => $station_codes[ $name ],
			'name'            => $name,
			'station_type'    => '',
			'display_order'   => (string) (int) $row[1],
			'bus_stop_marker' => ! empty( $row[2] ) ? '1' : '0',
			'lat'             => '',
			'lng'             => '',
		);
	}
	return $rows;
}
