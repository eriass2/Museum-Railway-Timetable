<?php
/**
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	$from_label = sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $from_station ) );
	$to_label   = sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $to_station ) );

	$station_ids = array_map( 'intval', (array) ( $group['stations'] ?? array() ) );
	$mid_station = null;
	$mid_label   = '';
	if ( count( $station_ids ) === 3 ) {
		$mid_post = get_post( $station_ids[1] );
		if ( $mid_post instanceof WP_Post ) {
			$mid_station = $mid_post;
			$mid_label   = sprintf(
				__( 'Från %s', 'museum-railway-timetable' ),
				MRT_get_station_display_name( $mid_post )
			);
		}
	}

	$trips = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$from_id    = (int) $from_station->ID;
		$to_id      = (int) $to_station->ID;
		$stop_times = $service_data['stop_times'] ?? array();
		$from_stop  = $stop_times[ $from_id ] ?? null;
		$to_stop    = $stop_times[ $to_id ] ?? null;
		$info       = $view['service_info'][ $idx ] ?? array();

		$mid_time = '';
		if ( $mid_station ) {
			$mid_stop = $stop_times[ (int) $mid_station->ID ] ?? null;
			$mid_time = is_array( $mid_stop ) ? MRT_format_stop_time_display( $mid_stop ) : '';
		}

		$train_labels = array();
		if ( $connections ) {
			$number = MRT_connection_service_number( $service_data );
			foreach ( $connections['bus_to_train'] as $row ) {
				if ( (string) $row['bus']['service_number'] === $number ) {
					foreach ( $row['trains'] as $train ) {
						$train_labels[] = array(
							'serviceNumber' => $train['service_number'],
							'timeDisplay'   => $train['time_display'],
						);
					}
					break;
				}
			}
		}

		$trips[] = array(
			'trip'             => (string) ( $info['service_number'] ?? '' ),
			'fromTime'         => MRT_format_stop_time_display( MRT_get_from_row_display_stop_time( $from_stop ) ),
			'toTime'           => MRT_format_stop_time_display( MRT_get_to_row_display_stop_time( $to_stop ) ),
			'midTime'          => $mid_time,
			'connectingTrains' => $train_labels,
		);
	}

	$result = array(
		'kind'       => 'branch',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => $from_label,
		'toLabel'    => $to_label,
		'trips'      => $trips,
	);
	if ( $mid_label !== '' ) {
		$result['midLabel'] = $mid_label;
	}
	return $result;
}

