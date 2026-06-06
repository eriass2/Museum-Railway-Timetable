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

require_once MRT_PATH . 'inc/infrastructure/rest/client-config.php';
require_once MRT_PATH . 'inc/infrastructure/rest/permissions.php';
require_once MRT_PATH . 'inc/infrastructure/rest/dashboard.php';
require_once MRT_PATH . 'inc/infrastructure/rest/timetables.php';
require_once MRT_PATH . 'inc/infrastructure/rest/stations.php';
require_once MRT_PATH . 'inc/infrastructure/rest/routes.php';
require_once MRT_PATH . 'inc/infrastructure/rest/stop-times.php';
require_once MRT_PATH . 'inc/infrastructure/rest/journey-public.php';
require_once MRT_PATH . 'inc/infrastructure/rest/timetable-public.php';
require_once MRT_PATH . 'inc/infrastructure/rest/pricing-public.php';
require_once MRT_PATH . 'inc/infrastructure/rest/settings-admin.php';
require_once MRT_PATH . 'inc/infrastructure/rest/train-types.php';
require_once MRT_PATH . 'inc/infrastructure/rest/import-export.php';
require_once MRT_PATH . 'inc/infrastructure/rest/dev-tools.php';
require_once MRT_PATH . 'inc/infrastructure/rest/operations.php';

/**
 * Register all plugin REST routes.
 */
function MRT_register_rest_routes(): void {
	MRT_rest_register_dashboard_routes();
	MRT_rest_register_timetable_routes();
	MRT_rest_register_station_routes();
	MRT_rest_register_route_routes();
	MRT_rest_register_stop_times_routes();
	MRT_rest_register_journey_public_routes();
	MRT_rest_register_timetable_public_routes();
	MRT_rest_register_pricing_public_routes();
	MRT_rest_register_settings_routes();
	MRT_rest_register_train_type_routes();
	MRT_rest_register_import_export_routes();
	MRT_rest_register_dev_tools_routes();
	MRT_rest_register_operations_routes();
}

add_action( 'rest_api_init', 'MRT_register_rest_routes' );
