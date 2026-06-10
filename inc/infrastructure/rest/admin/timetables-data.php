<?php
/**
 * REST timetable serializers and mutations.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/service/timetable-trip-update.php';
require_once MRT_PATH . 'inc/domain/route/destinations.php';
require_once __DIR__ . '/timetables-data-list.php';
require_once __DIR__ . '/timetables-data-write.php';
