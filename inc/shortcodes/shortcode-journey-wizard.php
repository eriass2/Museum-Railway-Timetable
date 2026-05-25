<?php
/**
 * Shortcode: multi-step journey wizard [museum_journey_wizard]
 *
 * Legacy entry path; modules are also loaded from inc/shortcodes.php.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$journey_wizard_dir = MRT_PATH . 'inc/public/journey-wizard/';
require_once $journey_wizard_dir . 'attributes.php';
require_once $journey_wizard_dir . 'fields.php';
require_once $journey_wizard_dir . 'timetable.php';
require_once $journey_wizard_dir . 'steps.php';
require_once $journey_wizard_dir . 'shell.php';
