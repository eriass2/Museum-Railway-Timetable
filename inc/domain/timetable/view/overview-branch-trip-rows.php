<?php
/**
 * Branch shuttle trip rows for timetable overview JSON.
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $group
 * @return array{post: WP_Post, label: string}|null
 */
function MRT_timetable_branch_mid_station( array $group ): ?array {
	$station_ids = array_map( 'intval', (array) ( $group['stations'] ?? array() ) );
	if ( count( $station_ids ) !== 3 ) {
		return null;
	}
	$mid_post = get_post( $station_ids[1] );
	if ( ! $mid_post instanceof WP_Post ) {
		return null;
	}
	return array(
		'post'  => $mid_post,
		'label' => MRT_station_from_label( $mid_post ),
	);
}

/**
 * @param array<string, mixed> $service_data
 * @param array<string, mixed>|null $connections
 * @return array<int, array{serviceNumber: string, timeDisplay: string}>
 */
function MRT_timetable_branch_connecting_trains( array $service_data, ?array $connections ): array {
	if ( ! is_array( $connections ) ) {
		return array();
	}
	$number = MRT_connection_service_number( $service_data );
	foreach ( $connections['bus_to_train'] as $row ) {
		if ( (string) $row['bus']['service_number'] !== $number ) {
			continue;
		}
		$labels = array();
		foreach ( $row['trains'] as $train ) {
			$labels[] = array(
				'serviceNumber' => $train['service_number'],
				'timeDisplay'   => $train['time_display'],
			);
		}
		return $labels;
	}
	return array();
}

/**
 * @param array<string, mixed> $service_data
 * @param array<string, mixed> $view
 * @param array<string, mixed>|null $connections
 * @return array<string, mixed>
 */
function MRT_timetable_branch_trip_json(
	array $service_data,
	int $idx,
	array $view,
	WP_Post $from_station,
	WP_Post $to_station,
	?array $connections,
	?WP_Post $mid_station
): array {
	$from_id    = (int) $from_station->ID;
	$to_id      = (int) $to_station->ID;
	$stop_times = $service_data['stop_times'] ?? array();
	$from_stop  = $stop_times[ $from_id ] ?? null;
	$to_stop    = $stop_times[ $to_id ] ?? null;
	$info       = $view['service_info'][ $idx ] ?? array();

	$mid_time = '';
	if ( $mid_station instanceof WP_Post ) {
		$mid_stop = $stop_times[ (int) $mid_station->ID ] ?? null;
		$mid_time = is_array( $mid_stop ) ? MRT_format_stop_time_display( $mid_stop ) : '';
	}

	return array(
		'trip'             => (string) ( $info['service_number'] ?? '' ),
		'fromTime'         => MRT_format_stop_time_display( MRT_get_from_row_display_stop_time( $from_stop ) ),
		'toTime'           => MRT_format_stop_time_display( MRT_get_to_row_display_stop_time( $to_stop ) ),
		'midTime'          => $mid_time,
		'connectingTrains' => MRT_timetable_branch_connecting_trains( $service_data, $connections ),
	);
}
