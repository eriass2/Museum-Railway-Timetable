<?php
/**
 * REST: train types.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/train-type/rest-format.php';

/**
 * Register train type routes.
 */
function MRT_rest_register_train_type_routes(): void {
	register_rest_route(
		MRT_REST_NAMESPACE,
		'/train-types',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'MRT_rest_list_train_types_handler',
				'permission_callback' => 'MRT_rest_can_read',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'MRT_rest_create_train_type_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);

	register_rest_route(
		MRT_REST_NAMESPACE,
		'/train-types/(?P<id>\d+)',
		array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_update_train_type_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => 'MRT_rest_delete_train_type_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_list_train_types_handler( WP_REST_Request $request ) {
	unset( $request );
	return rest_ensure_response(
		array(
			'items'     => MRT_rest_list_train_types(),
			'icon_keys' => MRT_train_type_icon_keys(),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_create_train_type_handler( WP_REST_Request $request ) {
	$id = MRT_rest_create_train_type( (array) $request->get_json_params() );
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	$term = get_term( (int) $id, MRT_TAXONOMY_TRAIN_TYPE );
	return rest_ensure_response( $term instanceof WP_Term ? MRT_rest_format_train_type( $term ) : array( 'id' => $id ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_update_train_type_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = MRT_rest_update_train_type( $id, (array) $request->get_json_params() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$term = get_term( $id, MRT_TAXONOMY_TRAIN_TYPE );
	return rest_ensure_response( $term instanceof WP_Term ? MRT_rest_format_train_type( $term ) : array( 'id' => $id ) );
}

/**
 * @param WP_REST_Request $request Request.
 */
function MRT_rest_delete_train_type_handler( WP_REST_Request $request ) {
	$id     = (int) $request['id'];
	$result = wp_delete_term( $id, MRT_TAXONOMY_TRAIN_TYPE );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( array( 'deleted' => true ) );
}
