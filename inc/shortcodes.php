<?php
/**
 * Shortcode registrations for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

require_once MRT_PATH . 'inc/shortcodes/shortcode-month.php';
require_once MRT_PATH . 'inc/shortcodes/shortcode-overview.php';
require_once MRT_PATH . 'inc/shortcodes/shortcode-journey.php';

add_shortcode('museum_timetable_month', 'MRT_render_shortcode_month');
add_shortcode('museum_timetable_overview', 'MRT_render_shortcode_overview');
add_shortcode('museum_journey_planner', 'MRT_render_shortcode_journey');
