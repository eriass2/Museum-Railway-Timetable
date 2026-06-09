<?php
/**
 * REST timetables: route registration
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => 'MRT_rest_delete_timetable_handler',
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
			'permission_callback' => 'MRT_rest_can_read_public',
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
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'MRT_rest_update_timetable_service_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => 'MRT_rest_remove_timetable_service_handler',
				'permission_callback' => 'MRT_rest_can_manage',
			),
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
