<?php
/**
 * Helper functions loader for Museum Railway Timetable
 *
 * Loads helper modules in dependency order:
 * datetime, stations, routes, utils, services, connections
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

$helpers_dir = __DIR__ . '/';

require_once MRT_PATH . 'inc/domain/datetime/datetime.php';
require_once MRT_PATH . 'inc/domain/station/stations.php';
require_once MRT_PATH . 'inc/domain/route/routes.php';
require_once $helpers_dir . 'helpers-utils.php';
require_once MRT_PATH . 'inc/domain/service/stop-times.php';
require_once MRT_PATH . 'inc/domain/service/connections.php';
