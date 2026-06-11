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

$rest_dir = MRT_PATH . 'inc/infrastructure/rest/';

require_once $rest_dir . 'shared/client-config.php';
require_once $rest_dir . 'shared/permissions.php';
require_once $rest_dir . 'shared/rest-log.php';

require_once $rest_dir . 'admin/dashboard.php';
require_once $rest_dir . 'admin/timetables.php';
require_once $rest_dir . 'admin/stations.php';
require_once $rest_dir . 'admin/routes.php';
require_once $rest_dir . 'admin/stop-times.php';
require_once $rest_dir . 'admin/settings-admin.php';
require_once $rest_dir . 'admin/train-types.php';
require_once $rest_dir . 'admin/import-export.php';
require_once $rest_dir . 'admin/operations.php';
require_once $rest_dir . 'admin/traffic-notices-admin.php';

require_once $rest_dir . 'public/journey-public.php';
require_once $rest_dir . 'public/timetable-public.php';
require_once $rest_dir . 'public/pricing-public.php';
require_once $rest_dir . 'public/traffic-notices-public.php';
require_once $rest_dir . 'public/traffic-disruptions-public.php';
require_once $rest_dir . 'public/wizard-feedback.php';

require_once $rest_dir . 'dev/dev-tools.php';

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
	MRT_rest_register_traffic_notices_public_routes();
	MRT_rest_register_traffic_disruptions_public_routes();
	MRT_rest_register_traffic_notices_admin_routes();
	MRT_rest_register_wizard_feedback_routes();
}

add_action( 'rest_api_init', 'MRT_register_rest_routes' );
