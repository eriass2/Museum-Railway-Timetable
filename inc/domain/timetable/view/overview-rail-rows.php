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
function MRT_timetable_rail_group_to_json( array $group, string $dateYmd ): array {
	$view = MRT_prepare_timetable_group_view( $group, $dateYmd );

	$connection = null;
	if ( ! empty( $group['paired_branch'] ) ) {
		$connection = MRT_build_rail_bus_connection_data( $group, $group['paired_branch'] );
	}

	$from_label = $view['from_station']
		? sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $view['from_station'] ) )
		: '';
	$to_label   = $view['to_station']
		? sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $view['to_station'] ) )
		: '';

	return array(
		'kind'       => 'rail',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => $from_label,
		'toLabel'    => $to_label,
		'columns'    => MRT_timetable_overview_columns_json( $view ),
		'rows'       => MRT_timetable_overview_rail_rows_json(
			$view,
			$connection,
			! empty( $group['paired_branch'] ) ? $group['paired_branch'] : null
		),
	);
}

/**
 * @param array<string, mixed> $view
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_overview_columns_json( array $view ): array {
	$columns = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$info    = $view['service_info'][ $idx ];
		$service = $service_data['service'] ?? null;
		$tt      = $info['train_type'] ?? null;
		$default_tt = $info['default_train_type'] ?? null;
		$columns[]  = array(
			'serviceId'              => $service instanceof WP_Post ? (int) $service->ID : 0,
			'serviceNumber'        => (string) ( $info['service_number'] ?? '' ),
			'trainTypeName'          => $tt ? $tt->name : '',
			'trainTypeSlug'          => $tt ? $tt->slug : '',
			'iconKey'                => $tt ? MRT_get_train_type_symbol_key( $tt ) : 'diesel',
			'plannedTrainTypeName'   => $default_tt ? $default_tt->name : '',
			'isDeviation'            => ! empty( $info['is_deviation'] ),
			'deviationNotice'        => (string) ( $info['deviation_notice'] ?? '' ),
			'isSpecial'              => ! empty( $info['highlight_label'] ),
			'specialName'            => (string) ( $info['highlight_label'] ?? '' ),
			'highlightColor'         => (string) ( $info['highlight_color'] ?? '' ),
		);
	}
	return $columns;
}

/**
 * @param array<string, mixed>|null $connection
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_overview_rail_rows_json( array $view, ?array $connection, ?array $branch_group = null ): array {
	$station_posts = $view['station_posts'];
	$services      = $view['services_list'];
	$info          = $view['service_info'];
	$rows          = array();

	if ( $station_posts === array() ) {
		return $rows;
	}

	$grid_direction = is_array( $connection ) ? (string) ( $connection['direction'] ?? 'outbound' ) : 'outbound';

	$first = $station_posts[0];
	$rows[] = MRT_timetable_row_times_json(
		'from',
		sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $first ) ),
		$first->ID,
		$services,
		$info,
		true,
		false
	);

	$regular = array_slice( $station_posts, 1, -1 );

	foreach ( $regular as $station ) {
		$station_id   = (int) $station->ID;
		$at_junction  = MRT_timetable_station_is_bus_junction( $connection, $branch_group, $station_id );
		$bus_rows_pre = $at_junction && $grid_direction === 'inbound'
			? MRT_timetable_junction_bus_rows_json( $services, $info, $connection, $branch_group )
			: array();

		foreach ( $bus_rows_pre as $bus_row ) {
			$rows[] = $bus_row;
		}

		if ( MRT_station_row_has_arrival_departure_split( $station_id, $services ) ) {
			$rows[] = MRT_timetable_row_times_json(
				'arrival',
				sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $station ) ),
				$station_id,
				$services,
				$info,
				false,
				true
			);
			$transfer = MRT_timetable_train_change_row_json( $station, $services, $info );
			if ( $transfer !== null ) {
				$rows[] = $transfer;
			}
			$rows[] = MRT_timetable_row_times_json(
				'departure',
				sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $station ) ),
				$station_id,
				$services,
				$info,
				true,
				false
			);
			continue;
		}

		$rows[] = MRT_timetable_row_times_json(
			'station',
			MRT_get_station_display_name( $station ),
			$station_id,
			$services,
			$info,
			false,
			false
		);

		if ( $at_junction && $grid_direction !== 'inbound' ) {
			foreach ( MRT_timetable_junction_bus_rows_json( $services, $info, $connection, $branch_group ) as $bus_row ) {
				$rows[] = $bus_row;
			}
		}
	}

	$last = end( $station_posts );
	$rows[] = MRT_timetable_row_times_json(
		'to',
		sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $last ) ),
		$last->ID,
		$services,
		$info,
		false,
		true
	);

	return array_values( array_filter( $rows ) );
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @return array<string, mixed>
 */
function MRT_timetable_row_times_json(
	string $kind,
	string $label,
	int $station_id,
	array $services,
	array $info,
	bool $use_from_display,
	bool $use_to_display
): array {
	$cells = array();
	foreach ( $services as $idx => $service_data ) {
		unset( $idx, $info );
		$stop    = $service_data['stop_times'][ $station_id ] ?? null;
		$cells[] = MRT_timetable_time_cell_json( $stop, $use_from_display, $use_to_display );
	}

	return array(
		'kind'       => $kind,
		'label'      => $label,
		'stationId'  => $station_id,
		'cells'      => $cells,
	);
}

/**
 * @param array<string, mixed>|null $stop Stop row from service stop_times map.
 * @return array<string, mixed>
 */
function MRT_timetable_time_cell_json( $stop, bool $use_from_display = false, bool $use_to_display = false ): array {
	$cell = array( 'text' => MRT_timetable_time_cell_text( $stop, $use_from_display, $use_to_display ) );
	if ( ! is_array( $stop ) ) {
		$cell['edit'] = array(
			'arrival'        => '',
			'departure'      => '',
			'stopsHere'      => false,
			'pickupAllowed'  => true,
			'dropoffAllowed' => true,
		);
		return $cell;
	}
	$cell['edit'] = array(
		'arrival'        => (string) ( $stop['arrival_time'] ?? '' ),
		'departure'      => (string) ( $stop['departure_time'] ?? '' ),
		'stopsHere'      => true,
		'pickupAllowed'  => ! empty( $stop['pickup_allowed'] ),
		'dropoffAllowed' => ! empty( $stop['dropoff_allowed'] ),
	);
	return $cell;
}

/**
 * Build display text for one overview time cell (legacy helper split out).
 *
 * @param array<string, mixed>|null $stop
 * @param bool                      $use_from_display
 * @param bool                      $use_to_display
 */
function MRT_timetable_time_cell_text( $stop, bool $use_from_display, bool $use_to_display ): string {
	if ( ! is_array( $stop ) ) {
		return '—';
	}
	if ( $use_from_display ) {
		$display = MRT_get_from_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	if ( $use_to_display ) {
		$display = MRT_get_to_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	return MRT_format_stop_time_display( $stop );
}

/**
 * @return array<string, mixed>|null
 */
function MRT_timetable_train_change_row_json(
	WP_Post $station,
	array $services,
	array $info
): ?array {
	if ( $station->post_title !== 'Marielund' ) {
		return null;
	}

	$map = MRT_journey_train_change_by_station()['Marielund'] ?? array();

	$cells = array();
	foreach ( $services as $idx => $service_data ) {
		$number   = (string) ( $info[ $idx ]['service_number'] ?? '' );
		$transfer = $map[ $number ] ?? null;
		$cells[]  = array(
			'vehicles' => $transfer ? array( MRT_timetable_vehicle_json( $transfer['typeName'], $transfer['serviceNumber'] ) ) : array(),
		);
	}

	return array(
		'kind'  => 'trainChange',
		'label' => __( 'Tågbyte:', 'museum-railway-timetable' ),
		'cells' => $cells,
	);
}

/**
 * @return array{typeName: string, serviceNumber: string, iconKey: string, detail: string}
 */
function MRT_timetable_vehicle_json( string $type_name, string $service_number, string $detail = '' ): array {
	$term = MRT_get_train_type_term_by_label( $type_name );
	return array(
		'typeName'      => $type_name,
		'serviceNumber' => $service_number,
		'iconKey'       => $term ? MRT_get_train_type_symbol_key( $term ) : 'diesel',
		'detail'        => $detail,
	);
}
