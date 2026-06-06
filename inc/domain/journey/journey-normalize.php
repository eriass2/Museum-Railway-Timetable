<?php
/**
 * Normalize journey results for JSON API / frontends.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/journey-normalize-labels.php';
require_once __DIR__ . '/journey-normalize-segments.php';
require_once __DIR__ . '/journey-normalize-api.php';
require_once __DIR__ . '/journey-normalize-filter.php';
