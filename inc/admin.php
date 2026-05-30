<?php
/**
 * Admin – menu, settings, dashboard, tools.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/admin/app.php';
require_once MRT_PATH . 'inc/admin/menu.php';
require_once MRT_PATH . 'inc/admin/tools/clear-db.php';
require_once MRT_PATH . 'inc/admin/admin-list.php';
require_once MRT_PATH . 'inc/admin/tools/demo-page.php';
require_once MRT_PATH . 'inc/admin/tools/component-debug-pages.php';
require_once MRT_PATH . 'inc/admin/tools/dev-navigation.php';
require_once MRT_PATH . 'inc/admin/tools/dev-cli.php';
require_once MRT_PATH . 'inc/admin/tools/import-lennakatten.php';
require_once MRT_PATH . 'inc/admin/tools/timetable-pages.php';
require_once MRT_PATH . 'inc/admin/meta-boxes.php';
