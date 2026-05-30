<?php
/**
 * REST: timetables.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/rest/timetables-data.php';
require_once MRT_PATH . 'inc/domain/admin/timetable-deviations.php';

/**
 * Register timetable routes.
 */
function MRT_rest_register_timetable_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_list_timetables_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'MRT_rest_create_timetable_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/(?P<id>\d+)',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_get_timetable_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_update_timetable_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/(?P<id>\d+)/overview',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_timetable_overview_handler',
			'permission_callback' => 'MRT_rest_can_read',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/(?P<id>\d+)/services',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'MRT_rest_add_timetable_service_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/(?P<id>\d+)/services/(?P<service_id>\d+)',
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => 'MRT_rest_remove_timetable_service_handler',
			'permission_callback' => 'MRT_rest_can_manage',
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/timetables/(?P<id>\d+)/deviations',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_get_deviations_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_save_deviations_handler',
				'permission_callback' => 'MRT_rest_can_edit_operations',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/routes/(?P<id>\d+)/destinations',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'MRT_rest_route_destinations_handler',
			'permission_callback' => 'MRT_rest_can_read',
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_list_timetables_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response( array( 'items' => MRT_rest_list_timetables() ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_create_timetable_handler( WP_REST_Request $request ) {
	$id = MRT_rest_create_timetable( (array) $request->get_json_params() );
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	return rest_ensure_response( MRT_rest_get_timetable_detail( (int) $id ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_get_timetable_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$data = MRT_rest_get_timetable_detail( $id );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( $data );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_update_timetable_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_rest_update_timetable( $id, (array) $request->get_json_params() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( MRT_rest_get_timetable_detail( $id ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_timetable_overview_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$data = MRT_get_timetable_overview_data( $id );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	return rest_ensure_response( $data );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_add_timetable_service_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_rest_add_timetable_service( $id, (array) $request->get_json_params() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_remove_timetable_service_handler( WP_REST_Request $request ) {
	$service_id = (int) $request['service_id'];
	if ( $service_id <= 0 ) {
		return new WP_Error( 'invalid', __( 'Invalid service.', 'museum-railway-timetable' ), array( 'status' => 400 ) );
	}
	delete_post_meta( $service_id, 'mrt_service_timetable_id' );
	return rest_ensure_response( array( 'removed' => true ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_get_deviations_handler( WP_REST_Request $request ) {
	$id = (int) $request['id'];
	return rest_ensure_response(
		array(
			'rows'         => MRT_get_timetable_deviations_payload( $id ),
			'timetable_dates' => MRT_get_timetable_dates( $id ) ?: array(),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_save_deviations_handler( WP_REST_Request $request ) {
	$id   = (int) $request['id'];
	$body = (array) $request->get_json_params();
	$rows = isset( $body['by_service'] ) && is_array( $body['by_service'] ) ? $body['by_service'] : array();
	MRT_apply_timetable_deviations( $id, $rows );
	return rest_ensure_response( array( 'saved' => true ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
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
