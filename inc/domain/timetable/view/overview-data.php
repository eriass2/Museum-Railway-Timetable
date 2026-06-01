<?php
/**
 * Timetable overview payload for Vue (JSON).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRT_PATH . 'inc/domain/timetable/view/grid-merge.php';

/**
 * @param array<int, WP_Post> $services
 * @param array<string, mixed> $meta timetableId, title, timetableType, scope, typeBanner|null
 * @return array<string, mixed>|WP_Error
 */
function MRT_build_timetable_overview_payload( array $services, string $dateYmd, array $meta ) {
	if ( $services === array() ) {
		$message = isset( $meta['emptyMessage'] ) ? (string) $meta['emptyMessage'] : __( 'No trips found.', 'museum-railway-timetable' );
		return new WP_Error( 'empty', $message );
	}

	$grouped = MRT_group_services_by_route( $services, $dateYmd );
	if ( empty( $grouped ) ) {
		$message = isset( $meta['emptyMessage'] ) ? (string) $meta['emptyMessage'] : __( 'No valid trips found.', 'museum-railway-timetable' );
		return new WP_Error( 'empty', $message );
	}

	$grouped = MRT_timetable_groups_link_branch_pairs( $grouped );
	usort( $grouped, 'MRT_sort_timetable_groups_source_order' );

	$groups = array();
	foreach ( $grouped as $group ) {
		if ( MRT_timetable_group_is_branch_shuttle( $group ) && ! empty( $group['paired_rail'] ) ) {
			continue;
		}
		$groups[] = MRT_timetable_overview_group_to_json( $group, $dateYmd );
	}

	$tt     = (string) ( $meta['timetableType'] ?? '' );
	$banner = $meta['typeBanner'] ?? null;
	if ( $banner === null && $tt !== '' ) {
		$banner = MRT_timetable_type_banner_text( $tt );
	}

	return array(
		'scope'         => (string) ( $meta['scope'] ?? 'timetable' ),
		'timetableId'   => (int) ( $meta['timetableId'] ?? 0 ),
		'title'         => (string) ( $meta['title'] ?? '' ),
		'dateYmd'       => $dateYmd,
		'timetableType' => $tt,
		'typeBanner'    => is_array( $banner ) ? $banner : array( 'label' => '' ),
		'printKey'      => MRT_timetable_print_key_data( $services, $dateYmd ),
		'iconUrls'      => MRT_train_type_icon_urls(),
		'groups'        => $groups,
	);
}

/**
 * @return array<string, mixed>|WP_Error
 */
function MRT_get_timetable_overview_data( int $timetable_id, ?string $dateYmd = null ) {
	if ( $timetable_id <= 0 ) {
		return new WP_Error( 'invalid_timetable', __( 'Invalid timetable.', 'museum-railway-timetable' ) );
	}

	if ( $dateYmd === null ) {
		$datetime = MRT_get_current_datetime();
		$dateYmd  = $datetime['date'];
	}

	$services = MRT_get_services_for_timetable( $timetable_id );
	$tt       = (string) get_post_meta( $timetable_id, 'mrt_timetable_type', true );

	return MRT_build_timetable_overview_payload(
		$services,
		$dateYmd,
		array(
			'scope'         => 'timetable',
			'timetableId'   => $timetable_id,
			'title'         => get_the_title( $timetable_id ),
			'timetableType' => $tt,
			'emptyMessage'  => __( 'No trips in this timetable.', 'museum-railway-timetable' ),
		)
	);
}

/**
 * All services running on one calendar day (month view day panel).
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_get_timetable_day_data( string $dateYmd, string $train_type_slug = '' ) {
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return new WP_Error( 'invalid_date', __( 'Invalid date.', 'museum-railway-timetable' ) );
	}

	$service_ids = MRT_services_running_on_date( $dateYmd, $train_type_slug );
	$services    = MRT_get_services_by_post_ids( $service_ids );

	$title = sprintf(
		/* translators: %s: formatted date */
		__( 'Timetable for %s', 'museum-railway-timetable' ),
		date_i18n( get_option( 'date_format' ), strtotime( $dateYmd ) )
	);

	$tt = MRT_dominant_timetable_type_for_date( $dateYmd );

	return MRT_build_timetable_overview_payload(
		$services,
		$dateYmd,
		array(
			'scope'         => 'day',
			'timetableId'   => 0,
			'title'         => $title,
			'timetableType' => $tt,
			'emptyMessage'  => __( 'No services running on this date.', 'museum-railway-timetable' ),
		)
	);
}

/**
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_data( array $services = array(), string $dateYmd = '' ): array {
	return array_merge(
		MRT_timetable_print_key_base_rows(),
		MRT_timetable_print_key_highlight_rows( $services ),
		MRT_timetable_print_key_deviation_rows( $services, $dateYmd )
	);
}

/**
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_base_rows(): array {
	return array(
		array(
			'symbol' => 'X',
			'text'   => __(
				'Stannar vid av- och påstigning när någon resenär ska på eller av.',
				'museum-railway-timetable'
			),
		),
		array(
			'symbol' => 'P',
			'text'   => __(
				'Stannar endast vid påstigning när någon resenär ska på.',
				'museum-railway-timetable'
			),
		),
		array(
			'symbol' => '*',
			'text'   => __(
				'Busshållplats; anslutande bussar visas i egen tabell i tidtabellen.',
				'museum-railway-timetable'
			),
		),
	);
}

/**
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_highlight_rows( array $services ): array {
	$rows = array();
	$seen = array();
	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$highlight = MRT_get_service_highlight( (int) $service->ID );
		if ( $highlight === null || isset( $seen[ $highlight['label'] ] ) ) {
			continue;
		}
		$seen[ $highlight['label'] ] = true;
		$rows[]                      = array(
			'symbol' => $highlight['label'],
			'text'   => $highlight['note'] !== '' ? $highlight['note'] : $highlight['label'],
		);
	}
	return $rows;
}

/**
 * Print-key rows for train-type deviations and date-specific notices.
 *
 * @param array<int, WP_Post> $services
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_deviation_rows( array $services, string $dateYmd ): array {
	if ( $dateYmd === '' || ! MRT_validate_date( $dateYmd ) || $services === array() ) {
		return array();
	}

	$rows               = array();
	$has_type_deviation = false;

	foreach ( $services as $service ) {
		if ( ! $service instanceof WP_Post ) {
			continue;
		}
		$row = MRT_timetable_deviation_print_key_row( $service, $dateYmd );
		if ( $row === null ) {
			continue;
		}
		if ( ! empty( $row['has_type_deviation'] ) ) {
			$has_type_deviation = true;
		}
		unset( $row['has_type_deviation'] );
		$rows[] = $row;
	}

	if ( $has_type_deviation ) {
		array_unshift(
			$rows,
			array(
				'symbol' => '†',
				'text'   => __(
					'Deviation from planned train type on the selected day.',
					'museum-railway-timetable'
				),
			)
		);
	}

	return $rows;
}

/**
 * @return array{symbol: string, text: string, has_type_deviation: bool}|null
 */
function MRT_timetable_deviation_print_key_row( WP_Post $service, string $dateYmd ): ?array {
	$service_id   = (int) $service->ID;
	$type_dev     = MRT_service_has_train_type_deviation( $service_id, $dateYmd );
	$notice       = MRT_get_service_notice_for_date( $service_id, $dateYmd );
	if ( ! $type_dev && $notice === '' ) {
		return null;
	}

	$number = (string) get_post_meta( $service_id, 'mrt_service_number', true );
	if ( $number === '' ) {
		$number = (string) $service_id;
	}

	$parts = array();
	if ( $type_dev ) {
		$default   = MRT_get_service_default_train_type( $service_id );
		$effective = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
		if ( $effective instanceof WP_Term ) {
			$parts[] = MRT_format_train_type_deviation_text( $effective, $default );
		}
	}
	if ( $notice !== '' ) {
		$parts[] = $notice;
	}

	return array(
		'symbol'              => $type_dev ? $number . '†' : $number,
		'text'                => implode( ' ', $parts ),
		'has_type_deviation'  => $type_dev,
	);
}

/**
 * @return array{label: string}
 */
function MRT_timetable_type_banner_text( string $type ): array {
	$labels = array(
		'green'  => 'GRÖN TIDTABELL',
		'red'    => 'RÖD TIDTABELL',
		'yellow' => 'GUL TIDTABELL',
		'orange' => 'ORANGE TIDTABELL',
		'blue'   => 'BLÅ TIDTABELL',
	);
	$key    = strtolower( $type );
	$label  = $labels[ $key ] ?? strtoupper( $type );
	return array( 'label' => $label );
}

/**
 * @param array<string, mixed> $group
 * @return array<string, mixed>
 */
function MRT_timetable_overview_group_to_json( array $group, string $dateYmd ): array {
	if ( MRT_timetable_group_is_branch_shuttle( $group ) ) {
		return MRT_timetable_branch_group_to_json( $group, $dateYmd );
	}
	return MRT_timetable_rail_group_to_json( $group, $dateYmd );
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

	$map = array(
		'71' => array( 'typeName' => 'Dieseltåg', 'serviceNumber' => '61' ),
		'63' => array( 'typeName' => 'Rälsbuss', 'serviceNumber' => '97' ),
		'60' => array( 'typeName' => 'Ångtåg', 'serviceNumber' => '74' ),
		'96' => array( 'typeName' => 'Dieseltåg', 'serviceNumber' => '64' ),
	);

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

/**
 * @param array<string, mixed>|null $connection
 * @param array<string, mixed>|null $branch_group
 */
function MRT_timetable_station_is_bus_junction( ?array $connection, ?array $branch_group, int $station_id ): bool {
	if ( ! is_array( $connection ) || ! is_array( $branch_group ) ) {
		return false;
	}
	if ( ! MRT_connection_has_any_buses( $connection ) ) {
		return false;
	}
	return (int) ( $connection['junction_id'] ?? 0 ) === $station_id;
}

/**
 * @param array<string, mixed> $connection
 */
function MRT_connection_has_any_buses( array $connection ): bool {
	foreach ( (array) ( $connection['train_to_bus'] ?? array() ) as $row ) {
		if ( ! empty( $row['buses'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_junction_bus_rows_json(
	array $services,
	array $info,
	array $connection,
	array $branch_group
): array {
	$junction_id    = (int) ( $connection['junction_id'] ?? 0 );
	$junction_label = (string) ( $connection['junction_label'] ?? '' );
	$remote_label   = MRT_timetable_bus_remote_station_label( $branch_group, $junction_id );
	if ( $junction_label === '' || $remote_label === '' ) {
		return array();
	}

	$inbound = (string) ( $connection['direction'] ?? 'outbound' ) === 'inbound';
	if ( $inbound ) {
		return array(
			MRT_timetable_bus_time_row_json(
				'busDeparture',
				sprintf( __( 'Från %s', 'museum-railway-timetable' ), $remote_label ),
				$services,
				$info,
				$connection,
				$branch_group,
				'remote',
				true
			),
			MRT_timetable_bus_time_row_json(
				'busArrival',
				sprintf( __( 'Till %s', 'museum-railway-timetable' ), $junction_label ),
				$services,
				$info,
				$connection,
				$branch_group,
				'junction',
				false
			),
		);
	}

	return array(
		MRT_timetable_bus_time_row_json(
			'busDeparture',
			sprintf( __( 'Från %s', 'museum-railway-timetable' ), $junction_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'junction',
			true
		),
		MRT_timetable_bus_time_row_json(
			'busArrival',
			sprintf( __( 'Till %s', 'museum-railway-timetable' ), $remote_label ),
			$services,
			$info,
			$connection,
			$branch_group,
			'remote',
			false
		),
	);
}

/**
 * @param array<string, mixed> $branch_group
 */
function MRT_timetable_bus_remote_station_label( array $branch_group, int $junction_id ): string {
	$remote_id = MRT_timetable_bus_remote_station_id( $branch_group, $junction_id );
	if ( $remote_id <= 0 ) {
		return '';
	}
	$station = get_post( $remote_id );
	if ( ! $station ) {
		return '';
	}
	return MRT_get_station_display_name( $station );
}

/**
 * @param array<string, mixed> $branch_group
 */
function MRT_timetable_bus_remote_station_id( array $branch_group, int $junction_id ): int {
	foreach ( (array) ( $branch_group['stations'] ?? array() ) as $station_id ) {
		$id = (int) $station_id;
		if ( $id > 0 && $id !== $junction_id ) {
			return $id;
		}
	}
	return 0;
}

/**
 * @param array<string, mixed> $branch_group
 * @return array<string, mixed>|null
 */
function MRT_find_bus_service_in_branch( array $branch_group, string $service_number ): ?array {
	foreach ( (array) ( $branch_group['services'] ?? array() ) as $service_data ) {
		if ( MRT_connection_service_number( $service_data ) === $service_number ) {
			return $service_data;
		}
	}
	return null;
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @return array<string, mixed>
 */
function MRT_timetable_bus_time_row_json(
	string $kind,
	string $label,
	array $services,
	array $info,
	array $connection,
	array $branch_group,
	string $stop_role,
	bool $use_departure
): array {
	$cells = array();
	foreach ( $services as $idx => $service_data ) {
		unset( $service_data );
		$train_number = (string) ( $info[ $idx ]['service_number'] ?? '' );
		$cells[]      = MRT_timetable_bus_time_cell_json(
			$train_number,
			$connection,
			$branch_group,
			$stop_role,
			$use_departure
		);
	}

	return array(
		'kind'  => $kind,
		'label' => $label,
		'cells' => $cells,
	);
}

/**
 * @param array<string, mixed> $connection
 * @param array<string, mixed> $branch_group
 * @return array<string, mixed>
 */
function MRT_timetable_bus_time_cell_json(
	string $train_number,
	array $connection,
	array $branch_group,
	string $stop_role,
	bool $use_departure
): array {
	$buses = MRT_connection_buses_for_train_number( $connection, $train_number );
	if ( $buses === array() ) {
		return array( 'text' => '—' );
	}

	$bus       = $buses[0];
	$bus_data  = MRT_find_bus_service_in_branch( $branch_group, (string) $bus['service_number'] );
	$stop      = MRT_timetable_bus_stop_for_role( $connection, $branch_group, $bus_data, $stop_role );
	$cell      = array(
		'text'             => MRT_timetable_bus_stop_display_time( $stop, $use_departure ),
		'busServiceNumber' => (string) $bus['service_number'],
	);
	return $cell;
}

/**
 * @param array<string, mixed>             $connection
 * @param array<string, mixed>             $branch_group
 * @param array<string, mixed>|null        $bus_data
 * @return array<string, mixed>|null
 */
function MRT_timetable_bus_stop_for_role(
	array $connection,
	array $branch_group,
	?array $bus_data,
	string $stop_role
): ?array {
	if ( ! is_array( $bus_data ) ) {
		return null;
	}
	$junction_id = (int) ( $connection['junction_id'] ?? 0 );
	$remote_id   = MRT_timetable_bus_remote_station_id( $branch_group, $junction_id );
	$station_id  = $stop_role === 'junction' ? $junction_id : $remote_id;
	if ( $station_id <= 0 ) {
		return null;
	}
	$stop = $bus_data['stop_times'][ $station_id ] ?? null;
	return is_array( $stop ) ? $stop : null;
}

/**
 * @param array<string, mixed>|null $stop
 */
function MRT_timetable_bus_stop_display_time( $stop, bool $use_departure ): string {
	if ( ! is_array( $stop ) ) {
		return '—';
	}
	if ( $use_departure ) {
		$display = MRT_get_from_row_display_stop_time( $stop );
		return MRT_format_stop_time_display( $display ?? $stop );
	}
	$display = MRT_get_to_row_display_stop_time( $stop );
	return MRT_format_stop_time_display( $display ?? $stop );
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
	$from_label   = $from_station
		? sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $from_station ) )
		: '';
	$to_label     = $to_station
		? sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $to_station ) )
		: '';

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
