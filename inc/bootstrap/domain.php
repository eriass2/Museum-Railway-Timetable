<?php
/**
 * Domain and shared helper module loading.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load journey domain modules (order matches former journey-loader.php).
 */
function MRT_load_journey_domain_modules(): void {
	$journey_dir = MRT_PATH . 'inc/domain/journey/';
	require_once $journey_dir . 'journey-detail.php';
	require_once $journey_dir . 'journey-calendar.php';
	require_once $journey_dir . 'journey-return.php';
	require_once $journey_dir . 'journey-multi-leg.php';
	require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	require_once $journey_dir . 'journey-notice.php';
	require_once $journey_dir . 'journey-connections-table.php';
	require_once $journey_dir . 'journey-normalize.php';
}

/**
 * Load timetable overview/grid domain modules.
 */
function MRT_load_timetable_view_domain_modules(): void {
	$view_dir = MRT_PATH . 'inc/domain/timetable/view/';
	require_once $view_dir . 'prepare.php';
	require_once $view_dir . 'grid.php';
	require_once $view_dir . 'overview.php';
}

/**
 * Load all domain modules and shared infrastructure helpers.
 */
function MRT_load_domain_modules(): void {
	require_once MRT_PATH . 'inc/infrastructure/wordpress/helpers-utils.php';
	require_once MRT_PATH . 'inc/domain/datetime/datetime.php';
	require_once MRT_PATH . 'inc/domain/station/stations.php';
	require_once MRT_PATH . 'inc/domain/route/routes.php';
	require_once MRT_PATH . 'inc/domain/service/services.php';
	require_once MRT_PATH . 'inc/domain/service/stop-times.php';
	require_once MRT_PATH . 'inc/domain/service/connections.php';
	MRT_load_journey_domain_modules();
	MRT_load_timetable_view_domain_modules();
}
