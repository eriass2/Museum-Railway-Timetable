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

require_once MRT_PATH . 'inc/domain/service/stoptimes-persist.php';
require_once MRT_PATH . 'inc/domain/service/route-stoptimes-editor.php';
require_once MRT_PATH . 'inc/domain/service/service-end-station.php';

/**
 * Replace all stop times for a service.
 *
 * Old rows are removed before the new ones are inserted, inside a transaction.
 * The unique stop-sequence index would reject a second row with the same sequence.
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
	$wpdb->query( 'START TRANSACTION' );
	if ( ! MRT_delete_service_stoptime_rows( $wpdb, $service_id ) ) {
		$wpdb->query( 'ROLLBACK' );
		return new WP_Error( 'db_delete', __( 'Failed to replace stop times.', MRT_TEXT_DOMAIN ) );
	}
	foreach ( $prepared as $row ) {
		$inserted_id = MRT_insert_prepared_stoptime_for_save_all( $wpdb, $row, $service_id );
		if ( $inserted_id === false ) {
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'db_insert', __( 'Failed to save stop times.', MRT_TEXT_DOMAIN ) );
		}
	}
	$wpdb->query( 'COMMIT' );
	MRT_sync_service_end_station_from_stops( $service_id, $stops );
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
	$route_stations = MRT_get_route_stations( $route_id );
	$existing       = MRT_map_existing_stoptimes_by_station( $service_id );
	$stations       = MRT_build_stoptimes_station_rows( $route_stations, $existing );
	return array(
		'route_id' => $route_id,
		'stations' => $stations,
	);
}
