<?php
/**
 * REST: stop times.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/stoptimes-save.php';

/**
 * Register stop-times routes.
 */
function MRT_rest_register_stop_times_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/services/(?P<id>\d+)/stop-times',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_get_stop_times_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_save_stop_times_handler',
				'permission_callback' => 'MRT_rest_stop_times_write_permission',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/services/(?P<id>\d+)/departure',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'MRT_rest_quick_departure_handler',
			'permission_callback' => 'MRT_rest_can_edit_operations',
		)
	);
}

/**
 * Allow manage_options full save; edit_posts only when body marks quick edit.
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_stop_times_write_permission( WP_REST_Request $request ): bool {
	if ( MRT_rest_can_manage() ) {
		return true;
	}
	if ( ! MRT_rest_can_edit_operations() ) {
		return false;
	}
	$body = (array) $request->get_json_params();
	return ! empty( $body['quick_edit'] );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_get_stop_times_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$data = MRT_get_service_stoptimes_editor_payload( $id );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( $data );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_save_stop_times_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$body = (array) $request->get_json_params();
	$stops = isset( $body['stops'] ) && is_array( $body['stops'] ) ? $body['stops'] : array();
	$result = MRT_save_service_stoptimes_bulk( $id, MRT_rest_normalize_stops_payload( $stops ) );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$data = MRT_get_service_stoptimes_editor_payload( $id );
	return rest_ensure_response( is_array( $data ) ? $data : array( 'saved' => true ) );
}

/**
 * @param array<int, array<string, mixed>> $stops Client rows.
 * @return array<int, array<string, mixed>>
 */
function MRT_rest_normalize_stops_payload( array $stops ): array {
	$normalized = array();
	foreach ( $stops as $stop ) {
		if ( ! is_array( $stop ) ) {
			continue;
		}
		$normalized[] = array(
			'station_id'  => (int) ( $stop['station_id'] ?? $stop['id'] ?? 0 ),
			'stops_here'  => ! empty( $stop['stops_here'] ) ? '1' : '0',
			'arrival'     => (string) ( $stop['arrival'] ?? $stop['arrival_time'] ?? '' ),
			'departure'   => (string) ( $stop['departure'] ?? $stop['departure_time'] ?? '' ),
			'pickup'      => ! empty( $stop['pickup'] ?? $stop['pickup_allowed'] ) ? '1' : '',
			'dropoff'     => ! empty( $stop['dropoff'] ?? $stop['dropoff_allowed'] ) ? '1' : '',
		);
	}
	return $normalized;
}

/**
 * Quick mobile edit: update first stopping departure only.
 *
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_quick_departure_handler( WP_REST_Request $request ) {
	$id         = (int) $request['id'];
	$body       = (array) $request->get_json_params();
	$departure  = isset( $body['departure'] ) ? sanitize_text_field( (string) $body['departure'] ) : '';
	if ( $departure !== '' && ! MRT_validate_time_hhmm( $departure ) ) {
		return new WP_Error( 'invalid_time', __( 'Invalid time format. Use HH:MM.', MRT_TEXT_DOMAIN ), array( 'status' => 400 ) );
	}
	$payload = MRT_get_service_stoptimes_editor_payload( $id );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}
	$stops = array();
	foreach ( $payload['stations'] as $row ) {
		$is_first = $stops === array();
		$stops[]  = array(
			'station_id' => (int) $row['id'],
			'stops_here' => $row['stops_here'] || $is_first ? '1' : '0',
			'arrival'    => (string) ( $row['arrival_time'] ?? '' ),
			'departure'  => $is_first ? $departure : (string) ( $row['departure_time'] ?? '' ),
			'pickup'     => ! empty( $row['pickup_allowed'] ) ? '1' : '',
			'dropoff'    => ! empty( $row['dropoff_allowed'] ) ? '1' : '',
		);
	}
	$result = MRT_save_service_stoptimes_bulk( $id, $stops );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( array( 'saved' => true ) );
}
