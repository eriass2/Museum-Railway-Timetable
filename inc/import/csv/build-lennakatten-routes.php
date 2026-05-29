<?php
/**
 * Lennakatten package – routes, timetables, services.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_train_type_rows(): array {
	$icon_map = array(
		'angtag'     => 'icons/steam.png',
		'ralsbuss'   => 'icons/railbus.png',
		'dieseltag'  => 'icons/diesel.png',
		'buss'       => 'icons/bus.png',
		'ang-diesel' => 'icons/diesel.png',
	);
	$rows     = array();
	foreach ( MRT_import_get_train_types() as $name => $slug ) {
		$rows[] = array(
			'slug'      => $slug,
			'name'      => $name,
			'icon_file' => $icon_map[ $slug ] ?? '',
		);
	}
	return $rows;
}

/**
 * @param array<string, string> $station_codes
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_route_rows( array $station_codes ): array {
	$defs = MRT_csv_lennakatten_route_definitions( $station_codes );
	$rows = array();
	foreach ( $defs as $code => $def ) {
		$rows[] = array(
			'route_code'         => $code,
			'title'              => $def['title'],
			'start_station_code' => $def['stations'][0],
			'end_station_code'   => $def['stations'][ count( $def['stations'] ) - 1 ],
		);
	}
	return $rows;
}

/**
 * @param array<string, string> $station_codes
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_route_station_rows( array $station_codes ): array {
	$rows = array();
	foreach ( MRT_csv_lennakatten_route_definitions( $station_codes ) as $code => $def ) {
		$seq = 1;
		foreach ( $def['stations'] as $sc ) {
			$rows[] = array(
				'route_code'   => $code,
				'sequence'     => (string) $seq,
				'station_code' => $sc,
			);
			++$seq;
		}
	}
	return $rows;
}

/**
 * @param array<string, string> $station_codes
 * @return array<string, array{title: string, stations: array<int, string>}>
 */
function MRT_csv_lennakatten_route_definitions( array $station_codes ): array {
	$rail = array(
		'Uppsala Östra',
		'Fyrislund',
		'Årsta',
		'Skölsta',
		'Bärby',
		'Gunsta',
		'Marielund',
		'Lövstahagen',
		'Selknä',
		'Löt',
		'Länna',
		'Almunge',
		'Moga',
		'Faringe',
	);
	$rail_codes = array();
	foreach ( $rail as $name ) {
		$rail_codes[] = $station_codes[ $name ];
	}
	$bus_names = array( 'Selknä', 'Fjällnora', 'Uppsala Östra' );
	$bus_codes = array();
	foreach ( $bus_names as $name ) {
		$bus_codes[] = $station_codes[ $name ];
	}
	return array(
		'uppsala-faringe'      => array(
			'title'    => 'Uppsala Östra – Faringe',
			'stations' => $rail_codes,
		),
		'faringe-uppsala-ostra' => array(
			'title'    => 'Faringe – Uppsala Östra',
			'stations' => array_reverse( $rail_codes ),
		),
		'selkna-uppsala-ostra' => array(
			'title'    => 'Selknä – Uppsala Östra',
			'stations' => $bus_codes,
		),
		'uppsala-ostra-selkna' => array(
			'title'    => 'Uppsala Östra – Selknä',
			'stations' => array_reverse( $bus_codes ),
		),
	);
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_timetable_rows(): array {
	return array(
		array(
			'timetable_code' => 'green',
			'title'          => 'GRÖN TIDTABELL 2026',
			'colour_type'    => 'green',
		),
		array(
			'timetable_code' => 'yellow',
			'title'          => 'GUL TIDTABELL 2026',
			'colour_type'    => 'yellow',
		),
	);
}

/**
 * @return array<int, array<string, string>>
 */
function MRT_csv_lennakatten_date_rows(): array {
	$rows = array();
	foreach ( MRT_import_get_green_timetable_dates() as $date ) {
		$rows[] = array( 'timetable_code' => 'green', 'date' => $date );
	}
	foreach ( MRT_import_get_yellow_timetable_dates() as $date ) {
		$rows[] = array( 'timetable_code' => 'yellow', 'date' => $date );
	}
	return $rows;
}

/**
 * @param array<string, array<int, array<string, string>>> $tables
 * @param array<string, string> $station_codes
 */
function MRT_csv_lennakatten_append_services( array &$tables, array $station_codes ): void {
	$train_slug = array_flip( MRT_import_get_train_types() );
	$sets       = array(
		'green'  => array(
			'out'     => array( 'fn' => 'MRT_import_get_services_out', 'route' => 'uppsala-faringe', 'end' => 'Faringe', 'suffix' => 'out' ),
			'in'      => array( 'fn' => 'MRT_import_get_services_in', 'route' => 'faringe-uppsala-ostra', 'end' => 'Uppsala Östra', 'suffix' => 'in' ),
			'bus_out' => array( 'fn' => 'MRT_import_get_green_bus_services_out', 'route' => 'selkna-uppsala-ostra', 'end' => 'Uppsala Östra', 'suffix' => 'bus-out' ),
			'bus_in'  => array( 'fn' => 'MRT_import_get_green_bus_services_in', 'route' => 'uppsala-ostra-selkna', 'end' => 'Selknä', 'suffix' => 'bus-in' ),
		),
		'yellow' => array(
			'out'     => array( 'fn' => 'MRT_import_get_yellow_services_out', 'route' => 'uppsala-faringe', 'end' => 'Faringe', 'suffix' => 'out' ),
			'in'      => array( 'fn' => 'MRT_import_get_yellow_services_in', 'route' => 'faringe-uppsala-ostra', 'end' => 'Uppsala Östra', 'suffix' => 'in' ),
			'bus_out' => array( 'fn' => 'MRT_import_get_yellow_bus_services_out', 'route' => 'selkna-uppsala-ostra', 'end' => 'Uppsala Östra', 'suffix' => 'bus-out' ),
			'bus_in'  => array( 'fn' => 'MRT_import_get_yellow_bus_services_in', 'route' => 'uppsala-ostra-selkna', 'end' => 'Selknä', 'suffix' => 'bus-in' ),
		),
	);
	$route_stations = MRT_csv_lennakatten_route_station_map( $station_codes );
	foreach ( $sets as $tt => $groups ) {
		foreach ( $groups as $group ) {
			$services = call_user_func( $group['fn'] );
			MRT_csv_lennakatten_add_service_group(
				$tables,
				$tt,
				$group,
				$services,
				$station_codes,
				$train_slug,
				$route_stations
			);
		}
	}
}

/**
 * @param array<string, string> $station_codes
 * @return array<string, array<int, string>>
 */
function MRT_csv_lennakatten_route_station_map( array $station_codes ): array {
	$map = array();
	foreach ( MRT_csv_lennakatten_route_definitions( $station_codes ) as $code => $def ) {
		$map[ $code ] = $def['stations'];
	}
	return $map;
}

/**
 * @param array<string, array<int, array<string, string>>> $tables
 * @param array<string, string> $station_codes
 * @param array<string, string> $train_slug
 * @param array<string, array<int, string>> $route_stations
 * @param array<int, array<int, mixed>> $services
 * @param array<string, string> $group
 */
function MRT_csv_lennakatten_add_service_group(
	array &$tables,
	string $timetable_code,
	array $group,
	array $services,
	array $station_codes,
	array $train_slug,
	array $route_stations
): void {
	$route_code = $group['route'];
	$stations   = $route_stations[ $route_code ] ?? array();
	foreach ( $services as $svc ) {
		$num          = (string) $svc[0];
		$train_name   = (string) $svc[1];
		$service_code = MRT_csv_slugify( "{$timetable_code}-{$num}-{$group['suffix']}" );
		$tables['services.csv'][] = array(
			'service_code'     => $service_code,
			'timetable_code'   => $timetable_code,
			'route_code'       => $route_code,
			'service_number'   => $num,
			'end_station_code' => $station_codes[ $group['end'] ],
			'title'            => '',
		);
		$slug = $train_slug[ $train_name ] ?? MRT_csv_slugify( $train_name );
		$tables['service_train_types.csv'][] = array(
			'service_code'    => $service_code,
			'train_type_slug' => $slug,
		);
		MRT_csv_lennakatten_append_stoptimes( $tables, $service_code, $stations, $svc[2], $svc[3] );
	}
}

/**
 * @param array<int, array<int, int>> $times
 * @param array<int, string> $symbols
 */
function MRT_csv_lennakatten_append_stoptimes(
	array &$tables,
	string $service_code,
	array $station_codes,
	array $times,
	array $symbols
): void {
	$seq = 1;
	foreach ( $station_codes as $i => $sc ) {
		$split = MRT_csv_split_time_tuple( isset( $times[ $i ] ) ? (array) $times[ $i ] : null );
		$flags = MRT_csv_symbol_to_flags( (string) ( $symbols[ $i ] ?? '' ) );
		$tables['stoptimes.csv'][] = array(
			'service_code'    => $service_code,
			'sequence'        => (string) $seq,
			'station_code'    => $sc,
			'arrival_time'    => $split['arrival'],
			'departure_time'  => $split['departure'],
			'pickup_allowed'  => (string) $flags['pickup_allowed'],
			'dropoff_allowed' => (string) $flags['dropoff_allowed'],
		);
		++$seq;
	}
}

/**
 * Write Lennakatten fixture to a directory.
 */
function MRT_csv_write_lennakatten_fixture( string $dir ): bool {
	$package = MRT_csv_build_lennakatten_package();
	if ( ! MRT_csv_write_manifest( $dir, $package['manifest'] ) ) {
		return false;
	}
	$columns = MRT_csv_fixture_column_headers();
	foreach ( $package['tables'] as $file => $rows ) {
		$headers = $columns[ $file ] ?? array();
		if ( $headers === array() || ! MRT_csv_write_file( trailingslashit( $dir ) . $file, $headers, $rows ) ) {
			return false;
		}
	}
	return true;
}

/**
 * @return array<string, array<int, string>>
 */
function MRT_csv_fixture_column_headers(): array {
	return array(
		'stations.csv'            => array( 'station_code', 'name', 'station_type', 'display_order', 'bus_stop_marker', 'lat', 'lng' ),
		'train_types.csv'           => array( 'slug', 'name', 'icon_file' ),
		'routes.csv'                => array( 'route_code', 'title', 'start_station_code', 'end_station_code' ),
		'route_stations.csv'        => array( 'route_code', 'sequence', 'station_code' ),
		'timetables.csv'            => array( 'timetable_code', 'title', 'colour_type' ),
		'timetable_dates.csv'       => array( 'timetable_code', 'date' ),
		'services.csv'              => array( 'service_code', 'timetable_code', 'route_code', 'service_number', 'end_station_code', 'title' ),
		'service_train_types.csv'   => array( 'service_code', 'train_type_slug' ),
		'stoptimes.csv'             => array( 'service_code', 'sequence', 'station_code', 'arrival_time', 'departure_time', 'pickup_allowed', 'dropoff_allowed' ),
	);
}
