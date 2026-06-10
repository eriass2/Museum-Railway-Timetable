<?php
/**
 * Service module loader.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$service_dir = MRT_PATH . 'inc/domain/service/';
require_once $service_dir . 'service-train-type.php';
require_once $service_dir . 'service-by-date.php';
require_once $service_dir . 'service-connections-query.php';
