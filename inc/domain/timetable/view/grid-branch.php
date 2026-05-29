<?php
/**
 * Branch shuttle detection (bus-only compact timetable groups).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bus-only routes with two or three stops use a compact branch table in Vue.
 *
 * @param array<string, mixed> $group Route group from MRT_group_services_by_route.
 */
function MRT_timetable_group_is_branch_shuttle( array $group ): bool {
	$stations = $group['stations'] ?? array();
	$count    = count( $stations );
	if ( $count < 2 || $count > 3 ) {
		return false;
	}

	$services = $group['services'] ?? array();
	if ( $services === array() ) {
		return false;
	}

	foreach ( $services as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( ! $train_type || $train_type->slug !== 'buss' ) {
			return false;
		}
	}

	return true;
}
