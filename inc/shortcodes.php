<?php
/**
 * Shortcode registrations for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/public/month-calendar/shortcode.php';
require_once MRT_PATH . 'inc/public/timetable-overview/shortcode.php';

$mrt_journey_wizard_dir = MRT_PATH . 'inc/public/journey-wizard/';
require_once $mrt_journey_wizard_dir . 'debug-fixtures.php';
require_once $mrt_journey_wizard_dir . 'fields.php';
require_once $mrt_journey_wizard_dir . 'timetable.php';
require_once $mrt_journey_wizard_dir . 'steps.php';
require_once $mrt_journey_wizard_dir . 'shell.php';

add_shortcode( 'museum_timetable_month', 'MRT_render_shortcode_month' );
add_shortcode( 'museum_timetable_overview', 'MRT_render_shortcode_overview' );
add_shortcode( 'museum_journey_wizard', 'MRT_render_shortcode_journey_wizard' );
