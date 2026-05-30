<?php
/**
 * Meta box save hooks (UI removed — Vue admin).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$meta_boxes_dir = MRT_PATH . 'inc/admin/meta-boxes/';

require_once $meta_boxes_dir . 'hooks.php';
require_once $meta_boxes_dir . 'station.php';
require_once $meta_boxes_dir . 'route.php';
require_once $meta_boxes_dir . 'timetable.php';
require_once $meta_boxes_dir . 'service-save.php';
