<?php
/**
 * Meta box registration and module loading.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$meta_boxes_dir = MRT_PATH . 'inc/admin/meta-boxes/';

require_once $meta_boxes_dir . 'station.php';
require_once $meta_boxes_dir . 'route.php';
require_once $meta_boxes_dir . 'timetable.php';
require_once $meta_boxes_dir . 'timetable-services.php';
require_once $meta_boxes_dir . 'timetable-overview.php';
require_once $meta_boxes_dir . 'timetable-deviations-panel.php';
require_once $meta_boxes_dir . 'service-deviations.php';
require_once $meta_boxes_dir . 'timetable-workspace.php';
require_once $meta_boxes_dir . 'service.php';
require_once $meta_boxes_dir . 'service-save.php';
require_once $meta_boxes_dir . 'service-stoptimes.php';
require_once $meta_boxes_dir . 'hooks.php';

/**
 * Meta boxes: station + timetable post type.
 */
function MRT_register_meta_boxes_station_and_timetable(): void {
	add_meta_box(
		'mrt_station_details',
		__( 'Station Details', 'museum-railway-timetable' ),
		'MRT_render_station_meta_box',
		'mrt_station',
		'normal',
		'high'
	);

	add_meta_box(
		'mrt_timetable_workspace',
		__( 'Timetable', 'museum-railway-timetable' ),
		'MRT_render_timetable_workspace_box',
		'mrt_timetable',
		'normal',
		'high'
	);
}

/**
 * Meta boxes: route + service post types.
 */
function MRT_register_meta_boxes_route_and_service(): void {
	add_meta_box(
		'mrt_route_details',
		__( 'Route Details', 'museum-railway-timetable' ),
		'MRT_render_route_meta_box',
		'mrt_route',
		'normal',
		'high'
	);

	add_meta_box(
		'mrt_service_details',
		__( 'Service Details', 'museum-railway-timetable' ),
		'MRT_render_service_meta_box',
		'mrt_service',
		'normal',
		'high'
	);

	add_meta_box(
		'mrt_service_stoptimes',
		__( 'Stop Times', 'museum-railway-timetable' ),
		'MRT_render_service_stoptimes_box',
		'mrt_service',
		'normal',
		'default'
	);
}

/**
 * Add meta boxes for stations and services.
 */
function MRT_register_all_plugin_meta_boxes(): void {
	MRT_register_meta_boxes_station_and_timetable();
	MRT_register_meta_boxes_route_and_service();
}

add_action( 'add_meta_boxes', 'MRT_register_all_plugin_meta_boxes' );
