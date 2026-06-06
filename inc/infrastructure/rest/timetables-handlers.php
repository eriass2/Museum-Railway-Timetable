<?php
/**
 * REST timetables: request handlers
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_rest_list_timetables_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( array( 'items' => MRT_rest_list_timetables() ) );
}

function MRT_rest_create_timetable_handler( WP_REST_Request $request ) {
	$id = MRT_rest_create_timetable( (array) $request->get_json_params() );
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	return rest_ensure_response( MRT_rest_get_timetable_detail( (int) $id ) );
}

function MRT_rest_get_timetable_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$data = MRT_rest_get_timetable_detail( $id );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( $data );
}

function MRT_rest_update_timetable_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_rest_update_timetable( $id, (array) $request->get_json_params() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( MRT_rest_get_timetable_detail( $id ) );
}

function MRT_rest_timetable_overview_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$data = MRT_get_timetable_overview_data( $id );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( array( 'overview' => $data ) );
}

function MRT_rest_add_timetable_service_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_rest_add_timetable_service( $id, (array) $request->get_json_params() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

function MRT_rest_update_timetable_service_handler( WP_REST_Request $request ) {
	$timetable_id = (int) $request['id'];
	$service_id   = (int) $request['service_id'];
	$result       = MRT_rest_update_timetable_service(
		$timetable_id,
		$service_id,
		(array) $request->get_json_params()
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

function MRT_rest_remove_timetable_service_handler( WP_REST_Request $request ) {
	$service_id = (int) $request['service_id'];
	$result     = MRT_delete_service_post( $service_id );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( array( 'removed' => true ) );
}

function MRT_rest_delete_timetable_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_delete_timetable_post( $id );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( array( 'deleted' => true ) );
}

function MRT_rest_get_deviations_handler( WP_REST_Request $request ) {
	$id = (int) $request['id'];
	return rest_ensure_response(
		array(
			'rows'         => MRT_get_timetable_deviations_payload( $id ),
			'timetable_dates' => MRT_get_timetable_dates( $id ) ?: array(),
		)
	);
}

function MRT_rest_save_deviations_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$body = (array) $request->get_json_params();
	$rows = isset( $body['by_service'] ) && is_array( $body['by_service'] ) ? $body['by_service'] : array();
	MRT_apply_timetable_deviations( $id, $rows );
	return rest_ensure_response( array( 'saved' => true ) );
}

function MRT_rest_route_destinations_handler( WP_REST_Request $request ) {
	$route_id = (int) $request['id'];
	if ( $route_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid route.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	return rest_ensure_response(
		array(
			'destinations' => MRT_build_route_destinations_list( $route_id ),
		)
	);
}
