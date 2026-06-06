<?php
/**
 * Admin help data module loader.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = MRT_PATH . 'inc/assets/data/admin-help/';
require_once $dir . 'sections.php';
require_once $dir . 'shortcodes.php';
require_once $dir . 'faq.php';
require_once $dir . 'price-zones.php';
