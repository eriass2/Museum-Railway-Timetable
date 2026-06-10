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
	require_once $journey_dir . 'journey-transfer-rules.php';
	require_once $journey_dir . 'journey-calendar.php';
	require_once $journey_dir . 'journey-calendar-cache.php';
	require_once $journey_dir . 'journey-return.php';
	require_once $journey_dir . 'train-change.php';
	require_once $journey_dir . 'journey-multi-leg.php';
	require_once $journey_dir . 'engine/search.php';
	require_once MRT_PATH . 'inc/domain/pricing/prices.php';
	require_once $journey_dir . 'journey-notice.php';
	require_once $journey_dir . 'journey-normalize.php';
	require_once $journey_dir . 'journey-scoring.php';
	require_once $journey_dir . 'request-params.php';
	require_once $journey_dir . 'public-handlers.php';
}

/**
 * Load timetable overview/grid domain modules.
 */
function MRT_load_timetable_view_domain_modules(): void {
	$view_dir = MRT_PATH . 'inc/domain/timetable/view/';
	require_once $view_dir . 'prepare.php';
	require_once $view_dir . 'group-view.php';
	require_once $view_dir . 'grid/grid-branch.php';
	require_once $view_dir . 'grid/grid-connections.php';
	require_once $view_dir . 'grid/grid-merge.php';
	require_once $view_dir . 'overview/overview-data.php';
	require_once MRT_PATH . 'inc/domain/timetable/timetable-pages.php';
}

/**
 * Load all domain modules and shared infrastructure helpers.
 */
function MRT_load_domain_modules(): void {
	require_once MRT_PATH . 'inc/infrastructure/wordpress/log.php';
	require_once MRT_PATH . 'inc/infrastructure/wordpress/helpers-utils.php';
	require_once MRT_PATH . 'inc/domain/datetime/datetime.php';
	require_once MRT_PATH . 'inc/domain/station/stations.php';
	require_once MRT_PATH . 'inc/domain/station/station-timetable-meta.php';
	require_once MRT_PATH . 'inc/domain/route/direction-labels.php';
	require_once MRT_PATH . 'inc/domain/route/routes.php';
	require_once MRT_PATH . 'inc/domain/service/services.php';
	require_once MRT_PATH . 'inc/domain/service/highlight.php';
	require_once MRT_PATH . 'inc/domain/service/stop-times.php';
	require_once MRT_PATH . 'inc/domain/service/stop-time-modes.php';
	require_once MRT_PATH . 'inc/domain/service/stop-time-display.php';
	require_once MRT_PATH . 'inc/domain/service/stoptimes-persist.php';
	require_once MRT_PATH . 'inc/domain/service/route-stoptimes-editor.php';
	require_once MRT_PATH . 'inc/domain/service/timetable-trip-create.php';
	require_once MRT_PATH . 'inc/domain/service/service-end-station.php';
	require_once MRT_PATH . 'inc/domain/service/timetable-trip-fields.php';
	require_once MRT_PATH . 'inc/domain/service/connections.php';
	require_once MRT_PATH . 'inc/domain/route/destinations.php';
	require_once MRT_PATH . 'inc/domain/timetable/timetable-type.php';
	MRT_load_journey_domain_modules();
	MRT_load_timetable_view_domain_modules();
}
