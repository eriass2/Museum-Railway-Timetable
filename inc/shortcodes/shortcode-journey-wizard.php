<?php
/**
 * Shortcode: multi-step journey wizard [museum_journey_wizard]
 *
 * Attributes: ticket_url, hero_image (cover URL for step 1), hero_subtitle (optional line under title),
 * timetable_id / timetable (optional printed timetable shown below the search form).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$journey_wizard_dir = __DIR__ . '/journey-wizard/';
require_once $journey_wizard_dir . 'attributes.php';
require_once $journey_wizard_dir . 'fields.php';
require_once $journey_wizard_dir . 'timetable.php';
require_once $journey_wizard_dir . 'steps.php';
require_once $journey_wizard_dir . 'shell.php';
