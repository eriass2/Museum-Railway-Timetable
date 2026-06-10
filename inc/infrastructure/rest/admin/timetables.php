<?php
/**
 * REST: timetables.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/timetables-data.php';
require_once MRT_PATH . 'inc/domain/admin/timetable-deviations.php';
require_once MRT_PATH . 'inc/domain/admin/delete-entities.php';
require_once __DIR__ . '/timetables-register.php';
require_once __DIR__ . '/timetables-handlers.php';
