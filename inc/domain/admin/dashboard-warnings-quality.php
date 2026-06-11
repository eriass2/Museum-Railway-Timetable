<?php
/**
 * Extended dashboard data-quality warning collectors.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/stop-times.php';
require_once MRT_PATH . 'inc/domain/journey/train-change.php';
require_once MRT_PATH . 'inc/domain/journey/journey-transfer-rules.php';
require_once MRT_PATH . 'inc/domain/line/line-csv.php';

/**
 * Station post IDs keyed by mrt_station_code (per request).
 *
 * @return array<string, int>
 */
function MRT_dashboard_station_ids_by_code(): array {
	$by_code  = array();
	$stations = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $stations as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		$code = trim( (string) get_post_meta( (int) $station->ID, 'mrt_station_code', true ) );
		if ( $code !== '' ) {
			$by_code[ $code ] = (int) $station->ID;
		}
	}
	return $by_code;
}

/**
 * Known service numbers (mrt_service_number) across all trips.
 *
 * @return array<string, true>
 */
function MRT_dashboard_collect_service_numbers(): array {
	$numbers  = array();
	$services = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $services as $service_id ) {
		$number = trim( (string) get_post_meta( (int) $service_id, 'mrt_service_number', true ) );
		if ( $number !== '' ) {
			$numbers[ $number ] = true;
		}
	}
	return $numbers;
}

/**
 * Lowercase train-type names and slugs for lookup.
 *
 * @return array<string, true>
 */
function MRT_dashboard_train_type_label_lookup(): array {
	$lookup = array();
	$terms  = get_terms(
		array(
			'taxonomy'   => MRT_TAXONOMY_TRAIN_TYPE,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $lookup;
	}
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$lookup[ strtolower( (string) $term->name ) ] = true;
		$lookup[ (string) $term->slug ]              = true;
	}
	return $lookup;
}

/**
 * @return array<int, true>
 */
function MRT_dashboard_rail_stop_station_ids(): array {
	$station_ids = array();
	$services    = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $services as $service_id ) {
		$service_id = (int) $service_id;
		if ( MRT_journey_service_is_bus( $service_id ) ) {
			continue;
		}
		foreach ( MRT_get_service_stop_times_ordered( $service_id ) as $row ) {
			$station_id = (int) ( $row['station_post_id'] ?? 0 );
			if ( $station_id > 0 ) {
				$station_ids[ $station_id ] = true;
			}
		}
	}
	return $station_ids;
}

/**
 * @param WP_Post $service Service post.
 */
function MRT_dashboard_service_warning_route( WP_Post $service ): string {
	$timetable_id = (int) get_post_meta( (int) $service->ID, 'mrt_service_timetable_id', true );
	return $timetable_id > 0 ? '#/timetables/' . $timetable_id : '#/timetables';
}

/**
 * @param array<int, int> $route_stations
 * @param array<int, int> $stop_ids
 * @return array{code: string, message: string, route: string}|null
 */
function MRT_dashboard_trip_stoptime_mismatch_warning(
	WP_Post $service,
	array $route_stations,
	array $stop_ids
): ?array {
	$route = MRT_dashboard_service_warning_route( $service );
	if ( count( $stop_ids ) !== count( $route_stations ) ) {
		return MRT_dashboard_warning_row(
			'trip_stoptimes_count_mismatch',
			sprintf(
				'Turen "%s" har %d stopptider men rutten har %d stationer.',
				$service->post_title,
				count( $stop_ids ),
				count( $route_stations )
			),
			$route
		);
	}
	$route_set = array_flip( $route_stations );
	foreach ( $stop_ids as $station_id ) {
		if ( $station_id <= 0 || isset( $route_set[ $station_id ] ) ) {
			continue;
		}
		return MRT_dashboard_warning_row(
			'trip_stoptimes_station_off_route',
			sprintf(
				'Turen "%s" har stopptid vid station som inte ingår i rutten.',
				$service->post_title
			),
			$route
		);
	}
	return null;
}

/**
 * Warnings when stop times count or stations do not match the trip route.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_trip_stoptimes_route_mismatch(): array {
	$warnings = array();
	$services = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$route_id = (int) get_post_meta( (int) $service->ID, 'mrt_service_route_id', true );
		if ( $route_id <= 0 ) {
			continue;
		}
		$route_stations = array_map( 'intval', MRT_get_route_stations( $route_id ) );
		if ( $route_stations === array() ) {
			continue;
		}
		$ordered = MRT_get_service_stop_times_ordered( (int) $service->ID );
		if ( $ordered === array() ) {
			continue;
		}
		$stop_ids = array();
		foreach ( $ordered as $row ) {
			$stop_ids[] = (int) ( $row['station_post_id'] ?? 0 );
		}
		$row = MRT_dashboard_trip_stoptime_mismatch_warning( $service, $route_stations, $stop_ids );
		if ( is_array( $row ) ) {
			$warnings[] = $row;
		}
	}
	return $warnings;
}

/**
 * @param array<string, true>              $service_numbers
 * @param array<string, true>              $type_labels
 * @param array<string, array<string, string>> $map
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_train_change_map_warnings_for_station(
	string $title,
	array $map,
	array $service_numbers,
	array $type_labels
): array {
	$warnings = array();
	$route    = '#/stations-routes';
	foreach ( $map as $incoming_number => $transfer ) {
		if ( ! isset( $service_numbers[ (string) $incoming_number ] ) ) {
			$warnings[] = MRT_dashboard_warning_row(
				'train_change_unknown_incoming',
				sprintf(
					'Byteskarta vid "%s" refererar okänt inkommande tågnummer %s.',
					$title,
					(string) $incoming_number
				),
				$route
			);
		}
		$out_number = (string) ( $transfer['serviceNumber'] ?? '' );
		if ( $out_number !== '' && ! isset( $service_numbers[ $out_number ] ) ) {
			$warnings[] = MRT_dashboard_warning_row(
				'train_change_unknown_outgoing',
				sprintf(
					'Byteskarta vid "%s" refererar okänt utgående tågnummer %s.',
					$title,
					$out_number
				),
				$route
			);
		}
		$type_name = (string) ( $transfer['typeName'] ?? '' );
		if ( $type_name !== '' && ! MRT_dashboard_train_type_exists( $type_name, $type_labels ) ) {
			$warnings[] = MRT_dashboard_warning_row(
				'train_change_unknown_type',
				sprintf(
					'Byteskarta vid "%s" refererar okänd tågtyp "%s".',
					$title,
					$type_name
				),
				$route
			);
		}
	}
	return $warnings;
}

/**
 * Warnings when train_change_map references missing trips or train types.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_train_change_map_invalid(): array {
	$warnings        = array();
	$service_numbers = MRT_dashboard_collect_service_numbers();
	$type_labels     = MRT_dashboard_train_type_label_lookup();
	$stations        = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_STATION,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $stations as $station ) {
		if ( ! $station instanceof WP_Post ) {
			continue;
		}
		$map = MRT_get_station_train_change_map( (int) $station->ID );
		if ( $map === array() ) {
			continue;
		}
		$warnings = array_merge(
			$warnings,
			MRT_dashboard_train_change_map_warnings_for_station(
				(string) $station->post_title,
				$map,
				$service_numbers,
				$type_labels
			)
		);
	}
	return $warnings;
}

/**
 * @param array<string, true> $lookup
 */
function MRT_dashboard_train_type_exists( string $type_name, array $lookup ): bool {
	$trim = trim( $type_name );
	if ( $trim === '' ) {
		return false;
	}
	return isset( $lookup[ strtolower( $trim ) ] ) || isset( $lookup[ sanitize_title( $trim ) ] );
}

/**
 * Warnings for transfer junctions without bus marker or train-change map.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_transfer_hub_unconfigured(): array {
	$registry = MRT_get_line_registry();
	if ( $registry === array() ) {
		return array();
	}
	$by_code  = MRT_dashboard_station_ids_by_code();
	$warnings = array();
	foreach ( $registry as $line_code => $entry ) {
		if ( ! is_array( $entry ) || empty( $entry['requires_transfer'] ) ) {
			continue;
		}
		$junction_code = trim( (string) ( $entry['junction_station_code'] ?? '' ) );
		if ( $junction_code === '' ) {
			continue;
		}
		$station_id = (int) ( $by_code[ $junction_code ] ?? 0 );
		if ( $station_id <= 0 ) {
			continue;
		}
		$has_bus = get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1';
		$has_map = MRT_get_station_train_change_map( $station_id ) !== array();
		if ( $has_bus || $has_map ) {
			continue;
		}
		$line_title = trim( (string) ( $entry['title'] ?? $line_code ) );
		$warnings[] = MRT_dashboard_warning_row(
			'transfer_hub_unconfigured',
			sprintf(
				'Knutpunkten "%s" (linje %s) saknar bussmarkering och tågbyteskarta — flerbenade resor kan misslyckas.',
				get_the_title( $station_id ) ?: $junction_code,
				$line_title
			),
			'#/stations-routes'
		);
	}
	return $warnings;
}

/**
 * Warnings for timetables that only have past traffic dates.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_timetables_no_upcoming_dates(): array {
	$datetime = MRT_get_current_datetime();
	$today    = gmdate( 'Y-m-d', $datetime['timestamp'] );
	$warnings = array();
	$timetables = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_TIMETABLE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'all',
		)
	);
	foreach ( $timetables as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$dates = MRT_get_timetable_dates( (int) $post->ID );
		if ( ! is_array( $dates ) || $dates === array() ) {
			continue;
		}
		$has_upcoming = false;
		foreach ( $dates as $date ) {
			if ( is_string( $date ) && $date >= $today ) {
				$has_upcoming = true;
				break;
			}
		}
		if ( $has_upcoming ) {
			continue;
		}
		$warnings[] = MRT_dashboard_warning_row(
			'timetable_no_upcoming_dates',
			sprintf(
				'Tidtabellen "%s" har inga kommande trafikdagar.',
				$post->post_title
			),
			'#/timetables/' . (int) $post->ID
		);
	}
	return $warnings;
}

/**
 * Warnings when a transfer-branch bus line lacks rail service at the junction.
 *
 * @return array<int, array{code: string, message: string, route: string}>
 */
function MRT_dashboard_warnings_bus_without_rail_junction(): array {
	$registry = MRT_get_line_registry();
	if ( $registry === array() ) {
		return array();
	}
	$by_code    = MRT_dashboard_station_ids_by_code();
	$rail_stops = MRT_dashboard_rail_stop_station_ids();
	$warnings   = array();
	foreach ( $registry as $line_code => $entry ) {
		if ( ! is_array( $entry ) || empty( $entry['requires_transfer'] ) ) {
			continue;
		}
		$junction_code = trim( (string) ( $entry['junction_station_code'] ?? '' ) );
		$junction_id   = (int) ( $by_code[ $junction_code ] ?? 0 );
		if ( $junction_id <= 0 || isset( $rail_stops[ $junction_id ] ) ) {
			continue;
		}
		if ( ! MRT_dashboard_branch_has_bus_service( (string) $line_code ) ) {
			continue;
		}
		$line_title = trim( (string) ( $entry['title'] ?? $line_code ) );
		$warnings[] = MRT_dashboard_warning_row(
			'bus_line_no_rail_junction',
			sprintf(
				'Busslinjen "%s" saknar anslutande tåg vid knutpunkten "%s".',
				$line_title,
				get_the_title( $junction_id ) ?: $junction_code
			),
			'#/timetables'
		);
	}
	return $warnings;
}

/**
 * Whether any published bus trip uses the given line_code.
 */
function MRT_dashboard_branch_has_bus_service( string $line_code ): bool {
	if ( $line_code === '' ) {
		return false;
	}
	$services = get_posts(
		array(
			'post_type'      => MRT_POST_TYPE_SERVICE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		)
	);
	foreach ( $services as $service_id ) {
		$service_id = (int) $service_id;
		$code       = trim( (string) get_post_meta( $service_id, MRT_service_line_code_meta_key(), true ) );
		if ( $code !== $line_code || ! MRT_journey_service_is_bus( $service_id ) ) {
			continue;
		}
		return true;
	}
	return false;
}
