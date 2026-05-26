<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/infrastructure/post-types/register.php';
require_once MRT_PATH . 'inc/infrastructure/post-types/admin.php';
