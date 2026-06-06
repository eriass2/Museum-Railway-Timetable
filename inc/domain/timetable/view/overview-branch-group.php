<?php
/**
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/overview-branch-trip-rows.php';

/**
 * @param array<string, mixed> $group
 * @return array<string, mixed>
 */
function MRT_timetable_branch_group_to_json( array $group, string $dateYmd ): array {
	$view = MRT_prepare_timetable_group_view( $group, $dateYmd );

	$connections = null;
	if ( ! empty( $group['paired_rail'] ) ) {
		$connections = MRT_build_rail_bus_connection_data( $group['paired_rail'], $group );
	}

	$from_station = $view['from_station'];
	$to_station   = $view['to_station'];
	if ( ! $from_station || ! $to_station ) {
		return array(
			'kind'       => 'branch',
			'routeLabel' => $view['route_label'],
			'fromLabel'  => '',
			'toLabel'    => '',
			'trips'      => array(),
		);
	}

	$mid        = MRT_timetable_branch_mid_station( $group );
	$mid_post   = $mid['post'] ?? null;
	$trips      = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$trips[] = MRT_timetable_branch_trip_json(
			$service_data,
			(int) $idx,
			$view,
			$from_station,
			$to_station,
			$connections,
			$mid_post instanceof WP_Post ? $mid_post : null
		);
	}

	$result = array(
		'kind'       => 'branch',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $from_station ) ),
		'toLabel'    => sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $to_station ) ),
		'trips'      => $trips,
	);
	if ( is_array( $mid ) && $mid['label'] !== '' ) {
		$result['midLabel'] = $mid['label'];
	}
	return $result;
}
