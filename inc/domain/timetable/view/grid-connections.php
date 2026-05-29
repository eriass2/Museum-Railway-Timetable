<?php
/**
 * Rail ↔ branch bus connection matching and panels (separate timetables).
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array<string, mixed> $rail_group
 * @return array<int, array<string, mixed>>
 */
function MRT_rail_services_from_group( array $rail_group ): array {
	$services = array();
	foreach ( (array) ( $rail_group['services'] ?? array() ) as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( $train_type && $train_type->slug === 'buss' ) {
			continue;
		}
		$services[] = $service_data;
	}
	return $services;
}

/**
 * @param array<string, mixed> $branch_group
 * @return array<int, array<string, mixed>>
 */
function MRT_bus_services_from_group( array $branch_group ): array {
	$services = array();
	foreach ( (array) ( $branch_group['services'] ?? array() ) as $service_data ) {
		$train_type = $service_data['train_type'] ?? null;
		if ( $train_type && $train_type->slug === 'buss' ) {
			$services[] = $service_data;
		}
	}
	return $services;
}

/**
 * @param array<string, mixed> $rail_group
 */
function MRT_timetable_rail_grid_direction( array $rail_group ): string {
	$stations = (array) ( $rail_group['stations'] ?? array() );
	if ( $stations === array() ) {
		return 'outbound';
	}
	$first = get_post( (int) $stations[0] );
	if ( $first instanceof WP_Post && $first->post_title === 'Faringe' ) {
		return 'inbound';
	}
	return 'outbound';
}

/**
 * @param array<string, mixed> $rail_group
 * @param array<string, mixed> $branch_group
 */
function MRT_timetable_branch_junction_station_id( array $rail_group, array $branch_group ): int {
	$rail_ids   = array_map( 'intval', (array) ( $rail_group['stations'] ?? array() ) );
	$branch_ids = array_map( 'intval', (array) ( $branch_group['stations'] ?? array() ) );
	$shared     = array_values( array_intersect( $rail_ids, $branch_ids ) );
	if ( $shared === array() ) {
		return 0;
	}
	foreach ( $shared as $station_id ) {
		if ( get_post_meta( $station_id, 'mrt_station_bus_suffix', true ) === '1' ) {
			return (int) $station_id;
		}
	}
	return (int) $shared[0];
}

/**
 * @param array<string, mixed>|null $train_stop
 */
function MRT_train_connection_anchor_time( ?array $train_stop ): string {
	if ( ! $train_stop ) {
		return '';
	}
	$arrival   = MRT_stop_effective_arrival( $train_stop );
	$departure = MRT_stop_effective_departure( $train_stop );
	if ( $arrival !== '' && $departure !== '' ) {
		return $arrival;
	}
	return $arrival !== '' ? $arrival : $departure;
}

/**
 * @param array<string, mixed> $service_data
 */
function MRT_connection_service_number( array $service_data ): string {
	$service = $service_data['service'];
	$number  = (string) get_post_meta( $service->ID, 'mrt_service_number', true );
	return $number !== '' ? $number : (string) $service->ID;
}

/**
 * @param array<string, mixed> $bus_data
 */
function MRT_get_bus_connection_destination_label( array $bus_data ): string {
	$service = $bus_data['service'];
	$dest    = MRT_get_service_destination( $service->ID );
	if ( ! empty( $dest['end_station_id'] ) ) {
		$station = get_post( (int) $dest['end_station_id'] );
		if ( $station instanceof WP_Post ) {
			return MRT_get_station_display_name( $station );
		}
	}

	$ordered = MRT_get_service_stop_times_ordered( $service->ID );
	if ( $ordered === array() ) {
		return '';
	}
	$last_row = end( $ordered );
	$station  = get_post( (int) ( $last_row['station_post_id'] ?? 0 ) );
	if ( $station instanceof WP_Post ) {
		return MRT_get_station_display_name( $station );
	}

	return '';
}

/**
 * @param array<string, mixed> $bus_data
 * @return array{service_number: string, time_display: string, destination: string}
 */
function MRT_build_bus_link_entry( array $bus_data, array $stop ): array {
	return array(
		'service_number' => MRT_connection_service_number( $bus_data ),
		'time_display'   => MRT_format_stop_time_display( $stop ),
		'destination'    => MRT_get_bus_connection_destination_label( $bus_data ),
	);
}

/**
 * @param array<string, mixed> $train_data
 * @return array{service_number: string, time_display: string}
 */
function MRT_build_train_link_entry( array $train_data, array $stop ): array {
	return array(
		'service_number' => MRT_connection_service_number( $train_data ),
		'time_display'   => MRT_format_stop_time_display( $stop ),
	);
}

/**
 * @param array<string, mixed>|null $train_stop
 * @param array<int, array<string, mixed>> $bus_services
 * @return array<int, array{service_number: string, time_display: string, destination: string}>
 */
function MRT_buses_for_train_at_junction(
	?array $train_stop,
	string $grid_direction,
	int $junction_id,
	array $bus_services,
	int $limit = 6
): array {
	if ( ! $train_stop || $bus_services === array() ) {
		return array();
	}

	$matches = array();
	if ( $grid_direction === 'inbound' ) {
		$anchor = MRT_stop_effective_departure( $train_stop );
		if ( $anchor === '' ) {
			$anchor = MRT_stop_effective_arrival( $train_stop );
		}
		foreach ( $bus_services as $bus_data ) {
			$stop = $bus_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $stop ) ) {
				continue;
			}
			$bus_arr = MRT_stop_effective_arrival( $stop );
			if ( $bus_arr === '' || strcmp( $bus_arr, $anchor ) > 0 ) {
				continue;
			}
			$wait = MRT_journey_transfer_wait_minutes( $bus_arr, $anchor );
			if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $bus_arr, $anchor ) ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_bus_link_entry( $bus_data, $stop ),
				'wait'  => $wait,
			);
		}
	} else {
		$anchor = MRT_train_connection_anchor_time( $train_stop );
		if ( $anchor === '' ) {
			return array();
		}
		foreach ( $bus_services as $bus_data ) {
			$stop = $bus_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $stop ) ) {
				continue;
			}
			$bus_dep = MRT_stop_effective_departure( $stop );
			if ( $bus_dep === '' || strcmp( $bus_dep, $anchor ) < 0 ) {
				continue;
			}
			$wait = MRT_journey_transfer_wait_minutes( $anchor, $bus_dep );
			if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $anchor, $bus_dep ) ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_bus_link_entry( $bus_data, $stop ),
				'wait'  => $wait,
			);
		}
	}

	usort( $matches, fn( $a, $b ) => $a['wait'] <=> $b['wait'] );
	return array_map(
		fn( $row ) => $row['entry'],
		array_slice( $matches, 0, $limit )
	);
}

/**
 * @param array<string, mixed> $bus_stop
 * @param array<int, array<string, mixed>> $rail_services
 * @return array<int, array{service_number: string, time_display: string}>
 */
function MRT_trains_for_bus_at_junction(
	array $bus_stop,
	string $grid_direction,
	int $junction_id,
	array $rail_services,
	int $limit = 6
): array {
	if ( $rail_services === array() ) {
		return array();
	}

	$matches = array();
	if ( $grid_direction === 'inbound' ) {
		$bus_arr = MRT_stop_effective_arrival( $bus_stop );
		if ( $bus_arr === '' ) {
			return array();
		}
		foreach ( $rail_services as $train_data ) {
			$train_stop = $train_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $train_stop ) ) {
				continue;
			}
			$train_dep = MRT_stop_effective_departure( $train_stop );
			if ( $train_dep === '' || strcmp( $bus_arr, $train_dep ) > 0 ) {
				continue;
			}
			$wait = MRT_journey_transfer_wait_minutes( $bus_arr, $train_dep );
			if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $bus_arr, $train_dep ) ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_train_link_entry( $train_data, $train_stop ),
				'wait'  => $wait,
			);
		}
	} else {
		$bus_dep = MRT_stop_effective_departure( $bus_stop );
		if ( $bus_dep === '' ) {
			return array();
		}
		foreach ( $rail_services as $train_data ) {
			$train_stop = $train_data['stop_times'][ $junction_id ] ?? null;
			if ( ! is_array( $train_stop ) ) {
				continue;
			}
			$anchor = MRT_train_connection_anchor_time( $train_stop );
			if ( $anchor === '' || strcmp( $bus_dep, $anchor ) < 0 ) {
				continue;
			}
			$wait = MRT_journey_transfer_wait_minutes( $anchor, $bus_dep );
			if ( $wait === null || ! MRT_journey_transfer_wait_is_valid( $anchor, $bus_dep ) ) {
				continue;
			}
			$matches[] = array(
				'entry' => MRT_build_train_link_entry( $train_data, $train_stop ),
				'wait'  => $wait,
			);
		}
	}

	usort( $matches, fn( $a, $b ) => $a['wait'] <=> $b['wait'] );
	return array_map(
		fn( $row ) => $row['entry'],
		array_slice( $matches, 0, $limit )
	);
}

/**
 * @return array{
 *   junction_id: int,
 *   junction_label: string,
 *   direction: string,
 *   train_to_bus: array<int, array{train: array{service_number: string, time_display: string}, buses: array<int, array{service_number: string, time_display: string, destination: string}>>>,
 *   bus_to_train: array<int, array{bus: array{service_number: string, time_display: string, destination: string}, trains: array<int, array{service_number: string, time_display: string}>>
 * }
 */
function MRT_build_rail_bus_connection_data( array $rail_group, array $branch_group ): array {
	$junction_id = MRT_timetable_branch_junction_station_id( $rail_group, $branch_group );
	$direction   = MRT_timetable_rail_grid_direction( $rail_group );
	$junction    = $junction_id ? get_post( $junction_id ) : null;
	$label       = $junction instanceof WP_Post ? MRT_get_station_display_name( $junction ) : '';

	$rail_services = MRT_rail_services_from_group( $rail_group );
	$bus_services  = MRT_bus_services_from_group( $branch_group );

	$train_to_bus = array();
	foreach ( $rail_services as $train_data ) {
		$stop   = $train_data['stop_times'][ $junction_id ] ?? null;
		$buses  = MRT_buses_for_train_at_junction(
			is_array( $stop ) ? $stop : null,
			$direction,
			$junction_id,
			$bus_services
		);
		$train_to_bus[] = array(
			'train' => is_array( $stop ) ? MRT_build_train_link_entry( $train_data, $stop ) : array(
				'service_number' => MRT_connection_service_number( $train_data ),
				'time_display'   => '—',
			),
			'buses' => $buses,
		);
	}

	$bus_to_train = array();
	foreach ( $bus_services as $bus_data ) {
		$from_id = (int) ( $branch_group['stations'][0] ?? 0 );
		$stop    = $bus_data['stop_times'][ $junction_id ] ?? $bus_data['stop_times'][ $from_id ] ?? null;
		$trains  = is_array( $stop )
			? MRT_trains_for_bus_at_junction( $stop, $direction, $junction_id, $rail_services )
			: array();
		$bus_to_train[] = array(
			'bus'    => is_array( $stop )
				? array_merge( MRT_build_bus_link_entry( $bus_data, $stop ), array( 'destination' => MRT_get_bus_connection_destination_label( $bus_data ) ) )
				: array(
					'service_number' => MRT_connection_service_number( $bus_data ),
					'time_display'   => '—',
					'destination'    => MRT_get_bus_connection_destination_label( $bus_data ),
				),
			'trains' => $trains,
		);
	}

	return array(
		'junction_id'    => $junction_id,
		'junction_label' => $label,
		'direction'      => $direction,
		'train_to_bus'     => $train_to_bus,
		'bus_to_train'     => $bus_to_train,
	);
}

/**
 * @param array<string, mixed> $connection_data
 * @return array<int, array{service_number: string, time_display: string, destination: string}>
 */
function MRT_connection_buses_for_train_number( array $connection_data, string $train_number ): array {
	foreach ( $connection_data['train_to_bus'] as $row ) {
		if ( (string) $row['train']['service_number'] === $train_number ) {
			return $row['buses'];
		}
	}
	return array();
}

/**
 * @param array{service_number: string, time_display: string, destination: string} $bus
 */
function MRT_bus_transfer_detail_line( array $bus ): string {
	$parts = array();
	if ( $bus['time_display'] !== '' && $bus['time_display'] !== '—' ) {
		$parts[] = $bus['time_display'];
	}
	if ( ! empty( $bus['destination'] ) ) {
		$parts[] = '→ ' . $bus['destination'];
	}
	return implode( ' ', $parts );
}

/**
 * @param array<int, array{service_number: string, time_display: string, destination?: string}> $items
 */
function MRT_format_connection_service_list( array $items, string $kind ): string {
	if ( $items === array() ) {
		return '—';
	}

	$parts = array();
	foreach ( $items as $item ) {
		if ( $kind === 'buss' ) {
			$chunk = sprintf( __( 'Buss %s', 'museum-railway-timetable' ), $item['service_number'] );
		} else {
			$chunk = sprintf( __( 'Tåg %s', 'museum-railway-timetable' ), $item['service_number'] );
		}
		if ( ! empty( $item['time_display'] ) && $item['time_display'] !== '—' ) {
			$chunk .= ' ' . $item['time_display'];
		}
		if ( $kind === 'buss' && ! empty( $item['destination'] ) ) {
			$chunk .= ' → ' . $item['destination'];
		}
		$parts[] = $chunk;
	}

	return implode( '; ', $parts );
}
