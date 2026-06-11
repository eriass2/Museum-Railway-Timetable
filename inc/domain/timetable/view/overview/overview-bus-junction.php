<?php
/**
 * Timetable overview bus JSON: junction rows
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function MRT_timetable_station_is_bus_junction( ?array $connection, ?array $branch_group, int $station_id ): bool {
	if ( ! is_array( $connection ) || ! is_array( $branch_group ) ) {
		return false;
	}
	if ( ! MRT_connection_has_any_buses( $connection ) ) {
		return false;
	}
	return (int) ( $connection['junction_id'] ?? 0 ) === $station_id;
}

function MRT_connection_has_any_buses( array $connection ): bool {
	foreach ( (array) ( $connection['train_to_bus'] ?? array() ) as $row ) {
		if ( ! empty( $row['buses'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array<int, array<string, mixed>> $info
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>|null $display_columns
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_collect_junction_bus_links(
	array $info,
	array $connection,
	array $branch_group,
	?array $display_columns
): array {
	$junction_id = (int) ( $connection['junction_id'] ?? 0 );
	$remote_label = MRT_timetable_bus_remote_station_label( $branch_group, $junction_id );
	if ( $junction_id <= 0 || $remote_label === '' ) {
		return array();
	}

	$columns = MRT_timetable_bus_column_targets( $info, $display_columns );
	$inbound = (string) ( $connection['direction'] ?? 'outbound' ) === 'inbound';
	$links   = array();

	foreach ( $columns as $target ) {
		$buses = MRT_connection_buses_for_train_number( $connection, $target['train_number'] );
		if ( $buses === array() ) {
			continue;
		}
		$bus      = $buses[0];
		$bus_data = MRT_find_bus_service_in_branch( $branch_group, (string) $bus['service_number'] );
		if ( ! is_array( $bus_data ) ) {
			continue;
		}
		$links[] = array(
			'column_index'   => (int) $target['column_index'],
			'bus'            => $bus,
			'bus_data'       => $bus_data,
			'connection'     => $connection,
			'branch_group'   => $branch_group,
			'junction_label' => (string) ( $connection['junction_label'] ?? '' ),
			'remote_label'   => $remote_label,
			'inbound'        => $inbound,
			'sort_time'      => MRT_timetable_bus_link_sort_time( $connection, $branch_group, $bus_data, $inbound ),
			'wait_minutes'   => MRT_timetable_junction_bus_link_wait_minutes( $connection, $target['train_number'], $bus ),
		);
	}

	return $links;
}

/**
 * @param array<int, array<string, mixed>> $info
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>|null $display_columns
 * @return array<int, array{column_index: int, train_number: string}>
 */
function MRT_timetable_bus_column_targets( array $info, ?array $display_columns ): array {
	$targets = array();
	if ( $display_columns === null ) {
		foreach ( $info as $idx => $row ) {
			$targets[] = array(
				'column_index'  => (int) $idx,
				'train_number'  => (string) ( $row['service_number'] ?? '' ),
			);
		}
		return $targets;
	}
	foreach ( $display_columns as $column_index => $column ) {
		$idx        = (int) $column['primary_idx'];
		$targets[]  = array(
			'column_index' => (int) $column_index,
			'train_number' => (string) ( $info[ $idx ]['service_number'] ?? '' ),
		);
	}
	return $targets;
}

/**
 * @param array<string, mixed> $bus_data
 */
function MRT_timetable_bus_link_sort_time(
	array $connection,
	array $branch_group,
	array $bus_data,
	bool $inbound
): string {
	$junction_id = (int) ( $connection['junction_id'] ?? 0 );
	$remote_id   = MRT_timetable_bus_remote_station_id( $branch_group, $junction_id );
	$stop_role   = $inbound ? 'remote' : 'junction';
	$station_id  = $stop_role === 'junction' ? $junction_id : $remote_id;
	$stop        = $bus_data['stop_times'][ $station_id ] ?? null;
	if ( ! is_array( $stop ) ) {
		return '';
	}
	return MRT_stop_effective_departure( $stop );
}

/**
 * @param array<int, array<string, mixed>> $links
 */
function MRT_timetable_sort_junction_bus_links( array $links ): array {
	usort(
		$links,
		static function ( array $a, array $b ): int {
			$a_time = (string) ( $a['sort_time'] ?? '' );
			$b_time = (string) ( $b['sort_time'] ?? '' );
			if ( $a_time === '' && $b_time === '' ) {
				return 0;
			}
			if ( $a_time === '' ) {
				return 1;
			}
			if ( $b_time === '' ) {
				return -1;
			}
			return strcmp( $a_time, $b_time );
		}
	);
	return $links;
}

/**
 * @param array<string, mixed> $link
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_junction_bus_row_pair_json( array $link, int $column_count ): array {
	$column_index = (int) $link['column_index'];
	$inbound      = ! empty( $link['inbound'] );
	$junction     = (string) ( $link['junction_label'] ?? '' );
	$remote       = (string) ( $link['remote_label'] ?? '' );

	if ( $inbound ) {
		$departure_label = MRT_from_place_label( $remote );
		$arrival_label   = MRT_to_place_label( $junction );
		$dep_role        = 'remote';
		$arr_role        = 'junction';
	} else {
		$departure_label = MRT_from_place_label( $junction );
		$arrival_label   = MRT_to_place_label( $remote );
		$dep_role        = 'junction';
		$arr_role        = 'remote';
	}

	$departure_cells = MRT_timetable_sparse_bus_cells(
		$column_count,
		$column_index,
		MRT_timetable_bus_time_cell_from_bus_data(
			$link['connection'],
			$link['branch_group'],
			$link['bus_data'],
			$dep_role,
			true,
			(string) $link['bus']['service_number']
		)
	);
	$arrival_cells = MRT_timetable_sparse_bus_cells(
		$column_count,
		$column_index,
		MRT_timetable_bus_time_cell_from_bus_data(
			$link['connection'],
			$link['branch_group'],
			$link['bus_data'],
			$arr_role,
			false,
			(string) $link['bus']['service_number']
		)
	);

	return array(
		array(
			'kind'  => 'busDeparture',
			'label' => $departure_label,
			'cells' => $departure_cells,
		),
		array(
			'kind'  => 'busArrival',
			'label' => $arrival_label,
			'cells' => $arrival_cells,
		),
	);
}

/**
 * @param array<string, mixed> $filled_cell
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_sparse_bus_cells( int $column_count, int $column_index, array $filled_cell ): array {
	$cells = array();
	for ( $i = 0; $i < $column_count; $i++ ) {
		$cells[] = $i === $column_index ? $filled_cell : array( 'text' => '—' );
	}
	return $cells;
}

/**
 * @param array<int, array<string, mixed>> $services
 * @param array<int, array<string, mixed>> $info
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}>|null $display_columns
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_junction_bus_rows_json(
	array $services,
	array $info,
	array $connection,
	array $branch_group,
	?array $display_columns = null
): array {
	unset( $services );
	$links         = MRT_timetable_collect_junction_bus_links( $info, $connection, $branch_group, $display_columns );
	$column_count  = $display_columns !== null ? count( $display_columns ) : count( $info );
	$links         = MRT_timetable_sort_junction_bus_links( $links );
	$rows          = array();

	foreach ( $links as $link ) {
		foreach ( MRT_timetable_junction_bus_row_pair_json( $link, $column_count ) as $row ) {
			$rows[] = $row;
		}
	}
	return $rows;
}

/**
 * @param array<int, array<string, mixed>> $info
 * @param array<int, array{primary_idx: int, continuation_idx: int|null, split_station_id: int}> $display_columns
 * @param array<int, array<string, mixed>> $paired_branches
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_junction_bus_rows_for_station(
	array $info,
	array $rail_group,
	array $paired_branches,
	int $station_id,
	array $display_columns
): array {
	$links          = array();
	$column_count   = count( $display_columns );
	$grid_direction = MRT_timetable_rail_grid_direction( $rail_group );

	foreach ( $paired_branches as $branch_group ) {
		if ( ! is_array( $branch_group ) ) {
			continue;
		}
		if ( ! MRT_timetable_branch_matches_junction_flow( $branch_group, $station_id, $grid_direction ) ) {
			continue;
		}
		$connection = MRT_build_rail_bus_connection_data( $rail_group, $branch_group );
		if ( ! MRT_timetable_station_is_bus_junction( $connection, $branch_group, $station_id ) ) {
			continue;
		}
		foreach ( MRT_timetable_collect_junction_bus_links( $info, $connection, $branch_group, $display_columns ) as $link ) {
			$links[] = $link;
		}
	}

	$rows = array();
	foreach ( MRT_timetable_sort_junction_bus_links( MRT_timetable_dedupe_junction_bus_links_by_column( $links ) ) as $link ) {
		foreach ( MRT_timetable_junction_bus_row_pair_json( $link, $column_count ) as $row ) {
			$rows[] = $row;
		}
	}
	return $rows;
}

/**
 * Outbound rail grids need buses that depart the junction; inbound grids need arrivals.
 */
function MRT_timetable_branch_matches_junction_flow(
	array $branch_group,
	int $junction_id,
	string $grid_direction
): bool {
	$stations = array_values( array_map( 'intval', (array) ( $branch_group['stations'] ?? array() ) ) );
	if ( $stations === array() || $junction_id <= 0 ) {
		return false;
	}
	$start = $stations[0];
	$end   = $stations[ count( $stations ) - 1 ];
	if ( $grid_direction === 'inbound' ) {
		return $end === $junction_id;
	}
	return $start === $junction_id;
}

/**
 * @param array{service_number: string, time_display: string, destination: string} $bus
 */
function MRT_timetable_junction_bus_link_wait_minutes(
	array $connection,
	string $train_number,
	array $bus
): int {
	foreach ( (array) ( $connection['train_to_bus'] ?? array() ) as $row ) {
		if ( (string) ( $row['train']['service_number'] ?? '' ) !== $train_number ) {
			continue;
		}
		$train_time = (string) ( $row['train']['time_display'] ?? '' );
		$bus_time   = (string) ( $bus['time_display'] ?? '' );
		if ( $train_time === '' || $bus_time === '' || $train_time === '—' || $bus_time === '—' ) {
			return PHP_INT_MAX;
		}
		$wait = MRT_journey_transfer_wait_minutes( $train_time, $bus_time );
		return $wait ?? PHP_INT_MAX;
	}
	return PHP_INT_MAX;
}

/**
 * @param array<int, array<string, mixed>> $links
 * @return array<int, array<string, mixed>>
 */
function MRT_timetable_dedupe_junction_bus_links_by_column( array $links ): array {
	$best = array();
	foreach ( $links as $link ) {
		$column_index = (int) ( $link['column_index'] ?? -1 );
		if ( $column_index < 0 ) {
			continue;
		}
		if (
			! isset( $best[ $column_index ] )
			|| MRT_timetable_junction_bus_link_precedes( $link, $best[ $column_index ] )
		) {
			$best[ $column_index ] = $link;
		}
	}
	return array_values( $best );
}

/**
 * @param array<string, mixed> $candidate
 * @param array<string, mixed> $incumbent
 */
function MRT_timetable_junction_bus_link_precedes( array $candidate, array $incumbent ): bool {
	$c_wait = (int) ( $candidate['wait_minutes'] ?? PHP_INT_MAX );
	$i_wait = (int) ( $incumbent['wait_minutes'] ?? PHP_INT_MAX );
	if ( $c_wait !== $i_wait ) {
		return $c_wait < $i_wait;
	}
	$c_time = (string) ( $candidate['sort_time'] ?? '' );
	$i_time = (string) ( $incumbent['sort_time'] ?? '' );
	if ( $c_time !== '' && $i_time !== '' && $c_time !== $i_time ) {
		return strcmp( $c_time, $i_time ) < 0;
	}
	return true;
}
