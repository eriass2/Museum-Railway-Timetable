<?php
/**
 * REST route registration.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/rest/permissions.php';
require_once MRT_PATH . 'inc/infrastructure/rest/dashboard.php';
require_once MRT_PATH . 'inc/infrastructure/rest/timetables.php';
require_once MRT_PATH . 'inc/infrastructure/rest/stations-routes.php';
require_once MRT_PATH . 'inc/infrastructure/rest/stop-times.php';

/**
 * Register all plugin REST routes.
 */
function MRT_register_rest_routes(): void {
	MRT_rest_register_dashboard_routes();
	MRT_rest_register_timetable_routes();
	MRT_rest_register_stations_routes_routes();
	MRT_rest_register_stop_times_routes();
}

add_action( 'rest_api_init', 'MRT_register_rest_routes' );
