<?php

declare(strict_types=1);

/**
 * Asset enqueuing loader for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base URL for plugin assets directory (trailing slash).
 */
function MRT_assets_base_url(): string {
	return MRT_URL . 'assets/';
}

require_once MRT_PATH . 'inc/assets/admin.php';
require_once MRT_PATH . 'inc/assets/frontend.php';
