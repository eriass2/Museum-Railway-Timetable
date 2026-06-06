<?php
/**
 * Trip price selection rules (zone span, afternoon return, matrix lookup).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/price-rules-zones.php';
require_once __DIR__ . '/price-rules-matrix.php';
