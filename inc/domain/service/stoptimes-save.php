<?php
/**
 * Persist stop times for a service (bulk replace).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/ajax/stoptimes.php';

/**
 * Save all stop times for a service.
 *
 * @param int                      $service_id Service post ID.
 * @param array<int, array<string, mixed>> $stops Stop rows from client.
 * @return true|WP_Error
 */
function MRT_save_service_stoptimes_bulk( int $service_id, array $stops ) {
	if ( $service_id <= 0 ) {
		return new WP_Error( 'invalid_service', __( 'Invalid service ID.', MRT_TEXT_DOMAIN ) );
	}
	$prepared = MRT_prepare_stoptimes_for_save_all( $stops );
	if ( is_wp_error( $prepared ) ) {
		return $prepared;
	}

	global $wpdb;
	$inserted     = 0;
	$inserted_ids = array();
	foreach ( $prepared as $row ) {
		$inserted_id = MRT_insert_prepared_stoptime_for_save_all( $wpdb, $row, $service_id );
		if ( $inserted_id === false ) {
			MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, $inserted_ids );
			return new WP_Error( 'db_insert', __( 'Failed to save stop times.', MRT_TEXT_DOMAIN ) );
		}
		$inserted_ids[] = $inserted_id;
		++$inserted;
	}
	if ( ! MRT_delete_old_stoptimes_after_save_all( $wpdb, $service_id, $inserted_ids ) ) {
		MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, $inserted_ids );
		return new WP_Error( 'db_delete', __( 'Failed to replace stop times.', MRT_TEXT_DOMAIN ) );
	}
	return true;
}

/**
 * Build editable stop-time rows for a service.
 *
 * @param int $service_id Service ID.
 * @return array{route_id: int, stations: array<int, array<string, mixed>>}|WP_Error
 */
function MRT_get_service_stoptimes_editor_payload( int $service_id ) {
	$route_id = (int) get_post_meta( $service_id, 'mrt_service_route_id', true );
	if ( $route_id <= 0 ) {
		return new WP_Error( 'no_route', __( 'Trip has no route.', 'museum-railway-timetable' ) );
	}
	require_once MRT_PATH . 'inc/infrastructure/ajax/route-stations.php';
	$route_stations = MRT_get_route_stations( $route_id );
	$existing       = MRT_map_existing_stoptimes_by_station( $service_id );
	$stations       = MRT_build_stoptimes_station_rows( $route_stations, $existing );
	return array(
		'route_id' => $route_id,
		'stations' => $stations,
	);
}
