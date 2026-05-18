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
require_once MRT_PATH . 'inc/public/journey-planner/shortcode.php';
require_once MRT_PATH . 'inc/shortcodes/shortcode-journey-wizard.php';

add_shortcode( 'museum_timetable_month', 'MRT_render_shortcode_month' );
add_shortcode( 'museum_timetable_overview', 'MRT_render_shortcode_overview' );
add_shortcode( 'museum_journey_planner', 'MRT_render_shortcode_journey' );
add_shortcode( 'museum_journey_wizard', 'MRT_render_shortcode_journey_wizard' );
