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
require_once MRT_PATH . 'inc/domain/timetable/view/grid-transfer-inline.php';

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
		'printKey'      => MRT_timetable_print_key_data(),
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

	return MRT_build_timetable_overview_payload(
		$services,
		$dateYmd,
		array(
			'scope'         => 'day',
			'timetableId'   => 0,
			'title'         => $title,
			'timetableType' => '',
			'typeBanner'    => array( 'label' => '' ),
			'emptyMessage'  => __( 'No services running on this date.', 'museum-railway-timetable' ),
		)
	);
}

/**
 * @return array<int, array{symbol: string, text: string}>
 */
function MRT_timetable_print_key_data(): array {
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
		array(
			'symbol' => 'Thun’s-expressen',
			'text'   => __(
				'Thun’s-expressen tar dig till och från klädvaruhuset Thun’s i Faringe.',
				'museum-railway-timetable'
			),
		),
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
		'rows'       => MRT_timetable_overview_rail_rows_json( $view, $connection ),
	);
}

/**
 * @param array<string, mixed> $view
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_overview_columns_json( array $view ): array {
	$columns = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$info = $view['service_info'][ $idx ];
		$tt   = $info['train_type'] ?? null;
		$columns[] = array(
			'serviceNumber' => (string) ( $info['service_number'] ?? '' ),
			'trainTypeName' => $tt ? $tt->name : '',
			'trainTypeSlug' => $tt ? $tt->slug : '',
			'iconKey'       => $tt ? MRT_get_train_type_symbol_key( $tt ) : 'diesel',
			'isSpecial'     => ! empty( $info['is_special'] ),
			'specialName'   => (string) ( $info['special_name'] ?? '' ),
		);
	}
	return $columns;
}

/**
 * @param array<string, mixed>|null $connection
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_overview_rail_rows_json( array $view, ?array $connection ): array {
	$station_posts = $view['station_posts'];
	$services      = $view['services_list'];
	$classes       = $view['service_classes'];
	$info          = $view['service_info'];
	$rows          = array();

	if ( $station_posts === array() ) {
		return $rows;
	}

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
	$rows[] = MRT_timetable_maybe_bus_connection_row_json( $first, $services, $info, $classes, $connection );

	$regular   = array_slice( $station_posts, 1, -1 );
	$direction = MRT_timetable_grid_direction( $regular );

	foreach ( $regular as $station ) {
		$station_id = (int) $station->ID;
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
		$rows[] = MRT_timetable_maybe_bus_connection_row_json( $station, $services, $info, $classes, $connection );
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
		$stop = $service_data['stop_times'][ $station_id ] ?? null;
		$text = '—';
		if ( is_array( $stop ) ) {
			if ( $use_from_display ) {
				$display = MRT_get_from_row_display_stop_time( $stop );
				$text    = MRT_format_stop_time_display( $display ?? $stop );
			} elseif ( $use_to_display ) {
				$display = MRT_get_to_row_display_stop_time( $stop );
				$text    = MRT_format_stop_time_display( $display ?? $stop );
			} else {
				$text = MRT_format_stop_time_display( $stop );
			}
		}
		$special = (string) ( $info[ $idx ]['special_name'] ?? '' );
		$cells[] = array(
			'text'        => $text,
			'specialName' => $special,
		);
	}

	return array(
		'kind'  => $kind,
		'label' => $label,
		'cells' => $cells,
	);
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
 * @return array<string, mixed>|null
 */
function MRT_timetable_maybe_bus_connection_row_json(
	WP_Post $station,
	array $services,
	array $info,
	array $classes,
	?array $connection
): ?array {
	if ( ! $connection ) {
		return null;
	}
	$junction_id = (int) ( $connection['junction_id'] ?? 0 );
	if ( $junction_id <= 0 || (int) $station->ID !== $junction_id ) {
		return null;
	}

	$bus_term = MRT_get_train_type_term_by_slug( 'buss' );
	$cells    = array();
	foreach ( $services as $idx => $service_data ) {
		$train_number = (string) ( $info[ $idx ]['service_number'] ?? '' );
		$buses        = MRT_connection_buses_for_train_number( $connection, $train_number );
		$vehicles     = array();
		foreach ( $buses as $bus ) {
			$vehicles[] = array(
				'typeName'      => __( 'Buss', 'museum-railway-timetable' ),
				'serviceNumber' => $bus['service_number'],
				'iconKey'       => $bus_term ? MRT_get_train_type_symbol_key( $bus_term ) : 'bus',
				'detail'        => MRT_bus_transfer_detail_line( $bus ),
			);
		}
		$cells[] = array( 'vehicles' => $vehicles );
	}

	return array(
		'kind'  => 'busConnection',
		'label' => __( 'Anslutningsbuss:', 'museum-railway-timetable' ),
		'cells' => $cells,
	);
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
	$from_label   = $from_station
		? sprintf( __( 'Från %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $from_station ) )
		: '';
	$to_label     = $to_station
		? sprintf( __( 'Till %s', 'museum-railway-timetable' ), MRT_get_station_display_name( $to_station ) )
		: '';

	$trips = array();
	foreach ( $view['services_list'] as $idx => $service_data ) {
		$from_id   = (int) $from_station->ID;
		$to_id     = (int) $to_station->ID;
		$stop_times = $service_data['stop_times'] ?? array();
		$from_stop  = $stop_times[ $from_id ] ?? null;
		$to_stop    = $stop_times[ $to_id ] ?? null;
		$info       = $view['service_info'][ $idx ] ?? array();

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
			'trip'          => (string) ( $info['service_number'] ?? '' ),
			'fromTime'      => MRT_format_stop_time_display( MRT_get_from_row_display_stop_time( $from_stop ) ),
			'toTime'        => MRT_format_stop_time_display( MRT_get_to_row_display_stop_time( $to_stop ) ),
			'connectingTrains' => $train_labels,
		);
	}

	return array(
		'kind'       => 'branch',
		'routeLabel' => $view['route_label'],
		'fromLabel'  => $from_label,
		'toLabel'    => $to_label,
		'trips'      => $trips,
	);
}
